<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\HeroSlider;
use App\Models\Page;
use App\Http\Resources\Frontend\HeroSliderResource;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // 1. Fetch Hero Sliders
        $sliders = HeroSlider::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        // 2. Fetch Homepage Content (using page_type for stability)
        $homePage = Page::where('page_type', 'home')
            ->where('is_active', true)
            ->with(['blocks.elements'])
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'hero_sliders' => HeroSliderResource::collection($sliders),
                'home_content' => $homePage ? [
                    'title' => $homePage->title,
                    'blocks' => $homePage->blocks,
                ] : null,
            ],
        ], 200);
    }
}


