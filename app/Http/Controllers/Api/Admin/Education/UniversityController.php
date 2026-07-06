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
        $query = University::with('country')->latest();
        if ($request->filled('country_id')) {
            $query->where('country_id', (int) $request->query('country_id'));
        }
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('country', function ($qCountry) use ($search) {
                      $qCountry->where('name', 'like', "%{$search}%");
                  });
            });
        }
        $universities = $query->paginate($perPage);
        return response()->json([
            'success' => true,
            'data' => UniversityResource::collection($universities),
            'pagination' => [
                'total' => $universities->total(),
                'current_page' => $universities->currentPage(),
                'last_page' => $universities->lastPage(),
                'per_page' => $universities->perPage(),
                'from' => $universities->firstItem(),
                'to' => $universities->lastItem(),
            ],
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
            $path = $request->file('logo')->store('universities/logos', 'public');
            $validated['logo'] = '/storage/' . $path;
        }

        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('universities/banners', 'public');
            $validated['banner'] = '/storage/' . $path;
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
            $path = $request->file('logo')->store('universities/logos', 'public');
            $validated['logo'] = '/storage/' . $path;
        }

        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('universities/banners', 'public');
            $validated['banner'] = '/storage/' . $path;
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
