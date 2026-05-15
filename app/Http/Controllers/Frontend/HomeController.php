<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\HeroSlider;
use App\Http\Resources\Frontend\HeroSliderResource;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $sliders = HeroSlider::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'hero_sliders' => HeroSliderResource::collection($sliders)
            ],
        ], 200);
    }
}


