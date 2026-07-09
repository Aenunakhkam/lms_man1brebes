<?php

namespace App\Http\Controllers\Api\Guru;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('user')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $announcements
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required',
            'target_role' => 'required|string|in:all,siswa,guru_classes',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->input('content'),
            'target_role' => $request->target_role,
            'is_active' => $request->is_active ?? true,
            'user_id' => auth()->id()
        ]);

        if ($announcement->is_active) {
            $usersQuery = User::with('role');

            if ($announcement->target_role === 'guru_classes') {
                $teacherId = auth()->id();
                // Get students who are in the classes taught by this teacher
                $usersQuery->whereHas('role', function ($q) {
                    $q->where('name', 'siswa');
                })->whereHas('classes', function ($q) use ($teacherId) {
                    $q->whereIn('class_id', function ($query) use ($teacherId) {
                        $query->select('class_id')->from('schedules')->where('teacher_id', $teacherId);
                    });
                });
            } else if ($announcement->target_role === 'siswa') {
                $usersQuery->whereHas('role', function ($q) {
                    $q->where('name', 'siswa');
                });
            } else {
                // 'all'
                $usersQuery->whereHas('role', function ($q) {
                    $q->whereIn('name', ['guru', 'siswa']);
                });
            }

            $users = $usersQuery->get();

            $notificationsData = [];
            $now = Carbon::now();
            foreach ($users as $u) {
                $notificationsData[] = [
                    'user_id' => $u->id,
                    'title' => 'Pengumuman Baru dari Guru',
                    'message' => 'Ada pengumuman baru: ' . $announcement->title,
                    'type' => 'announcement',
                    'link' => '/siswa/announcements',
                    'is_read' => false,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
            if (count($notificationsData) > 0) {
                DB::table('notifications')->insert($notificationsData);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil dibuat',
            'data' => $announcement
        ]);
    }

    public function show(Announcement $announcement)
    {
        if ($announcement->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $announcement->load('user')
        ]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        if ($announcement->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'content' => 'string',
            'target_role' => 'string|in:all,siswa,guru_classes',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $announcement->update($request->only(['title', 'content', 'target_role', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil diperbarui',
            'data' => $announcement
        ]);
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $announcement->delete();
        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil dihapus'
        ]);
    }
}
