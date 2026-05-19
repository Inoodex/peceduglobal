<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Get setting configuration
     */
    public function get(): JsonResponse
    {
        $setting = Setting::firstOrCreate([], [
            'site_name' => 'PecEduGlobal',
            'mail_mailer' => 'smtp',
        ]);

        return response()->json([
            'success' => true,
            'data' => $setting
        ], Response::HTTP_OK);
    }

    /**
     * Update setting configuration
     */
    public function update(Request $request): JsonResponse
    {
        $setting = Setting::first();
        if (!$setting) {
            $setting = Setting::create([]);
        }

        $group = $request->input('group', 'all');

        // \Log::info('Setting update request:', [
        //     'group' => $group,
        //     'files' => array_map(function($file) {
        //         return [
        //             'name' => $file->getClientOriginalName(),
        //             'mime' => $file->getClientMimeType(),
        //             'error' => $file->getError(),
        //             'isValid' => $file->isValid(),
        //             'size' => $file->getSize()
        //         ];
        //     }, $request->allFiles()),
        //     'all' => $request->except(['logo', 'favicon'])
        // ]);

        $rules = [];
        if ($group === 'general') {
            $rules = [
                'site_name' => 'nullable|string|max:255',
                'contact_email' => 'nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:255',
                'contact_address' => 'nullable|string',
                'map_url' => 'nullable|string',
            ];
        } elseif ($group === 'email') {
            $rules = [
                'mail_mailer' => 'nullable|string|max:255',
                'mail_host' => 'nullable|string|max:255',
                'mail_port' => 'nullable|string|max:255',
                'mail_username' => 'nullable|string|max:255',
                'mail_password' => 'nullable|string|max:255',
                'mail_encryption' => 'nullable|string|max:255',
                'mail_from_address' => 'nullable|email|max:255',
                'mail_from_name' => 'nullable|string|max:255',
            ];
        } elseif ($group === 'branding') {
            $rules = [];
            if ($request->hasFile('logo')) {
                $rules['logo'] = 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048';
            }
            if ($request->hasFile('favicon')) {
                $rules['favicon'] = 'required|image|mimes:jpeg,png,jpg,gif,svg,webp,ico|max:1024';
            }
        } else {
            $rules = [
                // General Settings
                'site_name' => 'nullable|string|max:255',
                'contact_email' => 'nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:255',
                'contact_address' => 'nullable|string',
                'map_url' => 'nullable|string',

                // Email Settings
                'mail_mailer' => 'nullable|string|max:255',
                'mail_host' => 'nullable|string|max:255',
                'mail_port' => 'nullable|string|max:255',
                'mail_username' => 'nullable|string|max:255',
                'mail_password' => 'nullable|string|max:255',
                'mail_encryption' => 'nullable|string|max:255',
                'mail_from_address' => 'nullable|email|max:255',
                'mail_from_name' => 'nullable|string|max:255',
            ];

            if ($request->hasFile('logo')) {
                $rules['logo'] = 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048';
            }
            if ($request->hasFile('favicon')) {
                $rules['favicon'] = 'required|image|mimes:jpeg,png,jpg,gif,svg,webp,ico|max:1024';
            }
        }

        $validated = $request->validate($rules);

        // Process files if present (only for branding or all groups)
        if ($group === 'branding' || $group === 'all') {
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($setting->logo) {
                    $oldPath = str_replace('/storage/', '', $setting->logo);
                    Storage::disk('public')->delete($oldPath);
                }
                $path = $request->file('logo')->store('settings', 'public');
                $validated['logo'] = '/storage/' . $path;
            }

            if ($request->hasFile('favicon')) {
                // Delete old favicon if exists
                if ($setting->favicon) {
                    $oldPath = str_replace('/storage/', '', $setting->favicon);
                    Storage::disk('public')->delete($oldPath);
                }
                $path = $request->file('favicon')->store('settings', 'public');
                $validated['favicon'] = '/storage/' . $path;
            }
        }

        $setting->update($validated);

        return response()->json([
            'success' => true,
            'data' => $setting,
            'message' => 'Settings updated successfully.'
        ], Response::HTTP_OK);
    }
}
