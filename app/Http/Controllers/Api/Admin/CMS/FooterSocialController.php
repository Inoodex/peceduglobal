<?php

namespace App\Http\Controllers\Api\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\FooterSocial;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FooterSocialController extends Controller
{
    public function index()
    {
        $socials = FooterSocial::orderBy('serial_no', 'asc')->get();
        return response()->json([
            'success' => true,
            'data' => $socials
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string',
            'url' => 'required|string|max:255',
            'serial_no' => 'nullable|integer',
            'status' => 'nullable|boolean'
        ]);

        $validated['serial_no'] = $validated['serial_no'] ?? 0;
        $validated['status'] = $validated['status'] ?? true;

        $social = FooterSocial::create($validated);

        return response()->json([
            'success' => true,
            'data' => $social,
            'message' => 'Social link added successfully.'
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $social = FooterSocial::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string',
            'url' => 'required|string|max:255',
            'serial_no' => 'nullable|integer',
            'status' => 'nullable|boolean'
        ]);

        $social->update($validated);

        return response()->json([
            'success' => true,
            'data' => $social,
            'message' => 'Social link updated successfully.'
        ]);
    }

    public function destroy($id)
    {
        $social = FooterSocial::findOrFail($id);
        $social->delete();

        return response()->json([
            'success' => true,
            'message' => 'Social link deleted successfully.'
        ]);
    }
}
