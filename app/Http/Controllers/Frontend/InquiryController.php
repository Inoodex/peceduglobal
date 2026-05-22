<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class InquiryController extends Controller
{
    /**
     * Store a new inquiry from the public form.
     */
    public function store(Request $request)
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'type' => 'nullable|string|max:50',
            'additional_info' => 'nullable', // allow array or JSON string when multipart/form-data
            'additional_info_file' => 'nullable',
        ];

        if ($request->hasFile('additional_info_file')) {
            $rules['additional_info_file'] = 'file|mimes:pdf,doc,docx,jpg,jpeg,png,webp|max:5120'; // max 5MB
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $additionalInfo = $request->additional_info ?? [];

        // If additional_info was sent as a JSON string (common with multipart/form-data), decode it
        if (is_string($additionalInfo)) {
            $decoded = json_decode($additionalInfo, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $additionalInfo = $decoded;
            }
        }

        $additionalInfoFileName = null;
        $additionalInfoFilePath = null;

        // Handle uploaded file for additional info (if any)
        if ($request->hasFile('additional_info_file')) {
            $file = $request->file('additional_info_file');
            $path = $file->store('inquiries', 'public');
            $additionalInfoFileName = $file->getClientOriginalName();
            $additionalInfoFilePath = $path;

            // store public URL and path in the additional info array for compatibility
            $additionalInfo['uploaded_file'] = Storage::url($path);
            $additionalInfo['uploaded_file_path'] = $path;
        }

        // Automatically resolve IDs to names and replace the keys
        if (isset($additionalInfo['university_id'])) {
            $uni = \App\Models\University::find($additionalInfo['university_id']);
            if ($uni) $additionalInfo['preferred_university'] = $uni->name;
            unset($additionalInfo['university_id']);
        }
        if (isset($additionalInfo['course_id'])) {
            $course = \App\Models\Course::find($additionalInfo['course_id']);
            if ($course) $additionalInfo['preferred_course'] = $course->name;
            unset($additionalInfo['course_id']);
        }
        if (isset($additionalInfo['country_id'])) {
            $country = \App\Models\Country::find($additionalInfo['country_id']);
            if ($country) $additionalInfo['preferred_country'] = $country->name;
            unset($additionalInfo['country_id']);
        }

        $inquiry = Inquiry::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'type' => $request->type ?? 'general',
            'additional_info' => $additionalInfo,
            'additional_info_file_name' => $additionalInfoFileName,
            'additional_info_file_path' => $additionalInfoFilePath,
            'status' => 'new',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your inquiry has been submitted successfully. We will contact you soon.',
            'data' => $inquiry,
        ], Response::HTTP_CREATED);
    }
}
