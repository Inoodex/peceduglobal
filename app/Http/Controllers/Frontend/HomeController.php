<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\HeroSlider;
use App\Http\Resources\Frontend\HeroSliderResource;
use App\Http\Resources\Frontend\UniversityResource;
use App\Models\University;
use App\Models\Page;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // 1. Fetch Hero Sliders
        $sliders = HeroSlider::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        // 2. Fetch Partner Universities
        $partners = University::with('country')
            ->where('is_partner', true)
            ->get();

        // 3. Fetch Popular Destinations & Universities
        $popularCountries = \App\Models\Country::where('is_popular', true)->get();
        $popularUniversities = University::with('country')
            ->where('is_popular', true)
            ->limit(8)
            ->get();

        // 4. Fetch Latest Blogs
        $latestBlogs = \App\Models\BlogPost::with(['category', 'author'])
            ->where('status', 'published')
            ->latest('published_at')
            ->limit(3)
            ->get();

        // 5. Fetch the single "home" page_type which contains all home blocks (about, why_choose_us, services, statistics etc.)
        $homePage = Page::where('page_type', 'home')
            ->where('is_active', true)
            ->with(['blocks' => function($query) {
                $query->orderBy('sort_order', 'asc');
            }, 'blocks.elements'])
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'hero_sliders' => HeroSliderResource::collection($sliders),
                'partners' => UniversityResource::collection($partners),
                'popular_destinations' => [
                    'countries' => \App\Http\Resources\Admin\CountryResource::collection($popularCountries),
                    'universities' => UniversityResource::collection($popularUniversities),
                ],
                'latest_blogs' => \App\Http\Resources\Frontend\BlogResource::collection($latestBlogs),
                
                // Single unified "home" page config containing all configured blocks & elements
                'home_page' => $homePage ? [
                    'title'     => $homePage->title,
                    'page_type' => $homePage->page_type,
                    'blocks'    => $homePage->blocks
                ] : null
            ],
        ], 200);
    }
}
