<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    /**
     * Verify if email exists in database
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $userExists = User::where('email', $request->email)->exists();

        if ($userExists) {
            return response()->json([
                'success' => true,
                'message' => 'Email yang Anda masukkan benar'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Email tidak terdaftar di sistem kami'
        ], 404);
    }

    /**
     * Process reset password confirmation
     */
    public function processReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak terdaftar'
            ], 404);
        }

        // Karena aplikasi ini belum memiliki konfigurasi SMTP sungguhan, 
        // kita melakukan simulasi pencatatan permintaan reset password ke Log.
        Log::info('Permintaan reset password untuk email: ' . $user->email);

        // Kirim notifikasi ke semua admin
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        if ($adminRole) {
            $admins = User::where('role_id', $adminRole->id)->get();
            $notifications = [];
            foreach ($admins as $admin) {
                $notifications[] = [
                    'user_id' => $admin->id,
                    'title' => 'Permintaan Reset Password',
                    'message' => "Pengguna dengan email {$user->email} ({$user->name}) meminta reset password.",
                    'type' => 'system',
                    'link' => '/admin/users',
                    'is_read' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($notifications)) {
                \Illuminate\Support\Facades\DB::table('notifications')->insert($notifications);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Permintaan reset password telah dikirim ke Admin/sistem. Silakan cek email atau hubungi administrator Anda.'
        ]);
    }
}
