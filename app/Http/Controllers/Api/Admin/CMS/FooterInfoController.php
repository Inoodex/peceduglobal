<?php

namespace App\Http\Controllers\Api\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\FooterInfo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class FooterInfoController extends Controller
{
    public function get()
    {
        $info = FooterInfo::firstOrCreate([]);
        return response()->json([
            'success' => true,
            'data' => $info
        ]);
    }

    public function update(Request $request)
    {
        $info = FooterInfo::first();
        if (!$info) $info = FooterInfo::create([]);

        $validated = $request->validate([
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'copyright' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048'
        ]);

        if ($request->hasFile('logo')) {
            if ($info->logo) {
                $oldPath = str_replace('/storage/', '', $info->logo);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('logo')->store('settings', 'public');
            $validated['logo'] = '/storage/' . $path;
        }

        $info->update($validated);

        return response()->json([
            'success' => true,
            'data' => $info,
            'message' => 'Footer Info updated successfully.'
        ]);
    }
}
