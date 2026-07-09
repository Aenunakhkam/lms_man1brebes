<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        if (!$settings) {
            $settings = Setting::create([
                'app_name' => 'LMS MAN 1 BREBES',
                'app_developer' => 'Team IT MAN 1 Brebes',
                'app_version' => '1.0.0'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $settings = Setting::first();
        if (!$settings) {
            $settings = new Setting();
        }

        $validated = $request->validate([
            'school_name' => 'nullable|string|max:255',
            'school_address' => 'nullable|string',
            'school_phone' => 'nullable|string|max:20',
            'school_email' => 'nullable|email|max:255',
            'app_name' => 'required|string|max:255',
            'app_developer' => 'nullable|string|max:255',
            'app_version' => 'nullable|string|max:50',
            'school_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'academic_year' => 'nullable|string|max:50',
            'semester' => 'nullable|string|max:50',
            'is_maintenance' => 'nullable|boolean',
            'maintenance_message' => 'nullable|string',
        ]);

        if ($request->hasFile('school_logo')) {
            // Delete old logo if exists
            if ($settings->school_logo) {
                Storage::delete('public/' . $settings->school_logo);
            }
            $path = $request->file('school_logo')->store('logos', 'public');
            $validated['school_logo'] = $path;
        }

        $settings->fill($validated);
        $settings->save();

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan berhasil diperbarui',
            'data' => $settings
        ]);
    }

    public function removeLogo()
    {
        $settings = Setting::first();
        if ($settings && $settings->school_logo) {
            Storage::delete('public/' . $settings->school_logo);
            $settings->school_logo = null;
            $settings->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logo berhasil dihapus'
        ]);
    }
}
