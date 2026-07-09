<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'User role not found'
            ], 403);
        }

        $role = $user->role->name;
        
        $query = Announcement::with('user')
            ->where('is_active', true);

        // Admins see all active announcements, others see targeted ones
        if ($role !== 'admin') {
            $query->where(function($q) use ($role, $user) {
                $q->where('target_role', 'all')
                  ->orWhere('target_role', $role);
                  
                if ($role === 'siswa') {
                    $q->orWhere(function($sq) use ($user) {
                        $sq->where('target_role', 'guru_classes')
                           ->whereHas('user', function($uq) use ($user) {
                                // check if the announcement creator teaches this student
                                $uq->whereHas('schedules', function($sch) use ($user) {
                                    $sch->whereIn('class_id', $user->classes()->pluck('classes.id'));
                                });
                           });
                    });
                }
            });
        }

        $announcements = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $announcements
        ]);
    }
}
