<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Http\Resources\Frontend\UniversityResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UniversityController extends Controller
{
    /**
     * Display a listing of universities with filters.
     */
    public function index(Request $request)
    {
        $query = University::with('country');

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by country slug
        if ($request->has('country')) {
            $query->whereHas('country', function($q) use ($request) {
                $q->where('slug', $request->country);
            });
        }

        // Filter by country ID
        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        // Filter by popular
        if ($request->has('popular')) {
            $query->where('is_popular', true);
        }

        // Filter by partner
        if ($request->has('partner')) {
            $query->where('is_partner', true);
        }

        $universities = $query->latest()->paginate($request->query('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => UniversityResource::collection($universities)->response()->getData(true),
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified university details.
     */
    public function show($slug)
    {
        $university = University::with(['country', 'courses' => function($query) {
            $query->with(['courseLevel']);
        }])
        ->where('slug', $slug)
        ->first();

        if (!$university) {
            return response()->json([
                'success' => false,
                'message' => 'University not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => new UniversityResource($university),
        ], Response::HTTP_OK);
    }
}
