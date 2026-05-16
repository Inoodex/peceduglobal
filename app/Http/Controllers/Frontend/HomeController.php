<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\HeroSlider;
use App\Http\Resources\Frontend\HeroSliderResource;
use App\Http\Resources\Frontend\UniversityResource;
use App\Models\University;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $sliders = HeroSlider::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $partners = University::with('country')
            ->where('is_partner', true)
            ->get();

        $latestBlogs = \App\Models\BlogPost::with(['category', 'author'])
            ->where('status', 'published')
            ->latest('published_at')
            ->limit(3)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'hero_sliders' => HeroSliderResource::collection($sliders),
                'partners' => UniversityResource::collection($partners),
                'latest_blogs' => \App\Http\Resources\Frontend\BlogResource::collection($latestBlogs)
            ],
        ], 200);
    }
}


