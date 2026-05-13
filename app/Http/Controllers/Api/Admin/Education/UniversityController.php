<?php

namespace App\Http\Controllers\Api\Admin\Education;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Education\StoreUniversityRequest;
use App\Http\Resources\Admin\Education\UniversityResource;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class UniversityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 100), 1), 500);
        $query = University::with('country');
        if ($request->filled('country_id')) {
            $query->where('country_id', (int) $request->query('country_id'));
        }
        $universities = $query->paginate($perPage);
        return response()->json([
            'success' => true,
            'data' => UniversityResource::collection($universities),
        ], Response::HTTP_OK);
    }

    public function store(StoreUniversityRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        // Auto-generate slug
        $validated['slug'] = Str::slug($validated['name']);
        
        // Ensure slug is unique
        $originalSlug = $validated['slug'];
        $count = 1;
        while (University::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count++;
        }

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('universities/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            $validated['banner'] = $request->file('banner')->store('universities/banners', 'public');
        }

        $university = University::create($validated);
        return response()->json([
            'success' => true,
            'data' => new UniversityResource($university),
            'message' => 'University created successfully.'
        ], Response::HTTP_CREATED);
    }

    public function show(University $university): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new UniversityResource($university->load('country')),
        ], Response::HTTP_OK);
    }

    public function update(StoreUniversityRequest $request, University $university): JsonResponse
    {
        $validated = $request->validated();
        
        // Update slug if name changed
        if (isset($validated['name']) && $validated['name'] !== $university->name) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Ensure unique slug
            $originalSlug = $validated['slug'];
            $count = 1;
            while (University::where('slug', $validated['slug'])->where('id', '!=', $university->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $count++;
            }
        }

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('universities/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            $validated['banner'] = $request->file('banner')->store('universities/banners', 'public');
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
