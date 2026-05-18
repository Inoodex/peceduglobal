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

        // 5. Fetch all active pages of types (home, about, why_choose_us, services, statistics, comparison, about_the_company, faq) to load all homepage sections in one call
        $pageTypes = ['home', 'about', 'why_choose_us', 'services', 'statistics', 'comparison', 'about_the_company', 'faq'];
        
        $homePages = Page::whereIn('page_type', $pageTypes)
            ->where('is_active', true)
            ->with(['blocks' => function($query) {
                $query->orderBy('sort_order', 'asc');
            }, 'blocks.elements'])
            ->orderBy('id', 'asc') // Order by creation sequence
            ->get();

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
                
                // Return all these active homepage section pages with their blocks & elements
                'home_pages' => $homePages->map(function ($page) {
                    return [
                        'id'        => $page->id,
                        'title'     => $page->title,
                        'page_type' => $page->page_type,
                        'slug'      => $page->slug,
                        'blocks'    => $page->blocks
                    ];
                })
            ],
        ], 200);
    }
}
