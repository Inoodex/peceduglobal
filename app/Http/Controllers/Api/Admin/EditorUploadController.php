<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditorUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|file|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['jpeg', 'png', 'jpg', 'gif', 'svg', 'webp'];

            if (!in_array($extension, $allowedExtensions)) {
                return response()->json([
                    'message' => 'The image field must be a file of type: jpeg, png, jpg, gif, svg, webp.',
                    'errors' => [
                        'image' => ['The image field must be a file of type: jpeg, png, jpg, gif, svg, webp, web.']
                    ]
                ], 422);
            }

            $filename = Str::random(20) . '.' . $extension;
            $path = $file->storeAs('uploads/editor', $filename, 'public');
            
            return response()->json([
                'url' => asset('storage/' . $path),
                'message' => 'Image uploaded successfully'
            ]);
        }

        return response()->json(['message' => 'No image uploaded'], 400);
    }
}
