<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCountryRequest;
use App\Http\Resources\Admin\CountryResource;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CountryController extends Controller
{
    public function index(): JsonResponse
    {
        $countries = Country::paginate(15);
        return response()->json([
            'success' => true,
            'data' => CountryResource::collection($countries),
        ], Response::HTTP_OK);
    }

    public function store(StoreCountryRequest $request): JsonResponse
    {
        $validated = $request->validated();
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('countries/thumbnails', 'public');
        }
        if (empty($validated['slug']) && !empty($validated['name'])) {
            $slug = Str::slug($validated['name']);
            $originalSlug = $slug;
            $count = 1;
            while (Country::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            $validated['slug'] = $slug;
        }
        $country = Country::create($validated);
        return response()->json([
            'success' => true,
            'data' => new CountryResource($country),
            'message' => 'Country created successfully.'
        ], Response::HTTP_CREATED);
    }

    public function show(Country $country): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new CountryResource($country),
        ], Response::HTTP_OK);
    }

    public function update(StoreCountryRequest $request, Country $country): JsonResponse
    {
        $validated = $request->validated();
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('countries/thumbnails', 'public');
        }
        if (empty($validated['slug']) && !empty($validated['name'])) {
            $slug = Str::slug($validated['name']);
            $originalSlug = $slug;
            $count = 1;
            while (Country::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            $validated['slug'] = $slug;
        }
        $country->update($validated);
        return response()->json([
            'success' => true,
            'data' => new CountryResource($country),
            'message' => 'Country updated successfully.'
        ], Response::HTTP_OK);
    }

    public function destroy(Country $country): JsonResponse
    {
        $country->delete();
        return response()->json([
            'success' => true,
            'message' => 'Country deleted successfully.'
        ], Response::HTTP_OK);
    }
}
