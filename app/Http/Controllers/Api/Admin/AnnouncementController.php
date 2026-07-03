<?php

namespace App\Http\Controllers\Api\Admin;

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
        $announcements = Announcement::with('user')->latest()->get();
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
            'target_role' => 'required|string|in:all,guru,siswa',
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
            'content' => $request->content,
            'target_role' => $request->target_role,
            'is_active' => $request->is_active ?? true,
            'user_id' => auth()->id()
        ]);

        if ($announcement->is_active) {
            $usersQuery = User::with('role');
            if ($announcement->target_role !== 'all') {
                $usersQuery->whereHas('role', function($q) use ($announcement) {
                    $q->where('name', $announcement->target_role);
                });
            } else {
                $usersQuery->whereHas('role', function($q) {
                    $q->whereIn('name', ['guru', 'siswa']);
                });
            }
            $users = $usersQuery->get();
            
            $notificationsData = [];
            $now = Carbon::now();
            foreach ($users as $u) {
                $role = $u->role ? $u->role->name : 'siswa';
                $notificationsData[] = [
                    'user_id' => $u->id,
                    'title' => 'Pengumuman Baru',
                    'message' => 'Ada pengumuman baru: ' . $announcement->title,
                    'type' => 'announcement',
                    'link' => '/' . $role . '/announcements',
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
        return response()->json([
            'success' => true,
            'data' => $announcement->load('user')
        ]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'content' => 'string',
            'target_role' => 'string|in:all,guru,siswa',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $announcement->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil diperbarui',
            'data' => $announcement
        ]);
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil dihapus'
        ]);
    }
}
