<?php

namespace App\Http\Controllers\Api\Admin\Education;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Education\StoreUniversityRequest;
use App\Http\Resources\Admin\Education\UniversityResource;
use App\Models\University;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UniversityController extends Controller
{
    public function index(): JsonResponse
    {
        $universities = University::paginate(15);
        return response()->json([
            'success' => true,
            'data' => UniversityResource::collection($universities),
        ], Response::HTTP_OK);
    }

    public function store(StoreUniversityRequest $request): JsonResponse
    {
        $validated = $request->validated();
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('universities/logos', 'public');
        }
        $university = University::create($validated);
        return response()->json([
            'success' => true,
            'data' => new UniversityResource($university),
            'message' => 'University created successfully.'
        ], Response::HTTP_CREATED);
    }

    public function update(StoreUniversityRequest $request, University $university): JsonResponse
    {
        $validated = $request->validated();
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('universities/logos', 'public');
        }
        $university->update($validated);
        return response()->json([
            'success' => true,
            'data' => new UniversityResource($university),
            'message' => 'University updated successfully.'
        ], Response::HTTP_OK);
    }

    public function destroy(University $university): JsonResponse
    {
        $university->delete();
        return response()->json([
            'success' => true,
            'message' => 'University deleted successfully.'
        ], Response::HTTP_OK);
    }
}
