<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InquiryController extends Controller
{
    /**
     * Display a listing of inquiries.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $status = $request->query('status');
        $type = $request->query('type');

        $query = Inquiry::query();

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $inquiries = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $inquiries,
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified inquiry.
     */
    public function show(Inquiry $inquiry)
    {
        return response()->json([
            'success' => true,
            'data' => $inquiry,
        ], Response::HTTP_OK);
    }

    /**
     * Update the status or admin notes of an inquiry.
     */
    public function update(Request $request, Inquiry $inquiry)
    {
        $validated = $request->validate([
            'status' => 'nullable|string|in:new,contacted,pending,closed',
            'admin_notes' => 'nullable|string',
        ]);

        $inquiry->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Inquiry updated successfully.',
            'data' => $inquiry,
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified inquiry.
     */
    public function destroy(Inquiry $inquiry)
    {
        $inquiry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inquiry deleted successfully.',
        ], Response::HTTP_OK);
    }
}
