<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = \App\Models\Setting::first();
        
        if ($settings && $settings->is_maintenance) {
            $user = $request->user();
            if ($user && $user->loadMissing('role')->role->name !== 'admin') {
                return response()->json([
                    'success' => false,
                    'maintenance' => true,
                    'maintenance_message' => $settings->maintenance_message,
                    'message' => 'Aplikasi sedang dalam pemeliharaan (maintenance mode).'
                ], 503);
            }
        }
        
        return $next($request);
    }
}
