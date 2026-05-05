<?php

namespace App\Http\Controllers\Api\Admin\Education;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Education\StoreScholarshipRequest;
use App\Http\Resources\Admin\Education\ScholarshipResource;
use App\Models\Scholarship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ScholarshipController extends Controller
{
    public function index(): JsonResponse
    {
        $scholarships = Scholarship::paginate(15);
        return response()->json([
            'success' => true,
            'data' => ScholarshipResource::collection($scholarships),
        ], Response::HTTP_OK);
    }

    public function store(StoreScholarshipRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $scholarship = Scholarship::create($validated);
        return response()->json([
            'success' => true,
            'data' => new ScholarshipResource($scholarship),
            'message' => 'Scholarship created successfully.'
        ], Response::HTTP_CREATED);
    }

    public function update(StoreScholarshipRequest $request, Scholarship $scholarship): JsonResponse
    {
        $validated = $request->validated();
        $scholarship->update($validated);
        return response()->json([
            'success' => true,
            'data' => new ScholarshipResource($scholarship),
            'message' => 'Scholarship updated successfully.'
        ], Response::HTTP_OK);
    }

    public function destroy(Scholarship $scholarship): JsonResponse
    {
        $scholarship->delete();
        return response()->json([
            'success' => true,
            'message' => 'Scholarship deleted successfully.'
        ], Response::HTTP_OK);
    }
}
