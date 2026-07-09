<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and create token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau Password salah'
            ], 401);
        }

        $user = Auth::user(); // Get the authenticated user

        if (!$user->is_active) {
            // If the user is not active, log them out and return an error
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Your account is not active.'
            ], 403);
        }

        // Load role relationship
        $user->load('role');

        $settings = \App\Models\Setting::first();
        if ($settings && $settings->is_maintenance && $user->role->name !== 'admin') {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Aplikasi sedang dalam pemeliharaan (maintenance mode).'
            ], 503);
        }

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->name,
                    'role_display' => $user->role->display_name,
                    'photo' => $user->photo,
                ],
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('role');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
                'role_display' => $user->role->display_name,
                'photo' => $user->photo,
                'nis' => $user->nis,
                'nip' => $user->nip,
                'phone' => $user->phone,
                'address' => $user->address,
            ],
        ]);
    }
}
