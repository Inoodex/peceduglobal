<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatSettingController extends Controller
{
    /**
     * Display the current chat settings.
     */
    public function index()
    {
        $settings = ChatSetting::all();
        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Update or create chat settings.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            foreach ($request->settings as $key => $value) {
                ChatSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Chat settings updated successfully!',
                'data' => ChatSetting::all()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating settings: ' . $e->getMessage(),
            ], 500);
        }
    }
}
