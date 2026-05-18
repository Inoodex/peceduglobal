<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\HeroSlider;
use App\Models\University;
use App\Models\Page;
use App\Models\Country;
use App\Models\BlogPost;
use App\Http\Resources\Frontend\HeroSliderResource;
use App\Http\Resources\Frontend\UniversityResource;
use App\Http\Resources\Admin\CountryResource;
use App\Http\Resources\Frontend\BlogResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    /**
     * Fetch all homepage data.
     */
    public function index(Request $request): JsonResponse
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
        $popularCountries = Country::where('is_popular', true)->get();
        $popularUniversities = University::with('country')
            ->where('is_popular', true)
            ->limit(8)
            ->get();

        // 4. Fetch Latest Blogs
        $latestBlogs = BlogPost::with(['category', 'author'])
            ->where('status', 'published')
            ->latest('published_at')
            ->limit(3)
            ->get();

        // 5. Fetch all active CMS Pages grouped by type
        $groupedPages = $this->getGroupedPages();

        return response()->json([
            'success' => true,
            'home' => array_merge([
                'hero_sliders' => HeroSliderResource::collection($sliders),
                'partners' => UniversityResource::collection($partners),
                'popular_destinations' => [
                    'countries' => CountryResource::collection($popularCountries),
                    'universities' => UniversityResource::collection($popularUniversities),
                ],
                'latest_blogs' => BlogResource::collection($latestBlogs),
            ], $groupedPages),
        ], 200);
    }

    /**
     * Fetch all active CMS pages by specified types and group them into keys.
     */
    private function getGroupedPages(): array
    {
        $pageTypes = ['home', 'about', 'why_choose_us', 'services', 'statistics', 'comparison', 'about_the_company', 'faq'];

        $allPages = Page::whereIn('page_type', $pageTypes)
            ->where('is_active', true)
            ->with(['blocks' => function($query) {
                $query->orderBy('sort_order', 'asc');
            }, 'blocks.elements'])
            ->get();

        $groupedPages = [];
        foreach ($pageTypes as $type) {
            if ($type === 'home') {
                // Return multiple home page blocks as an array under 'home_page' key
                $groupedPages['home_page'] = $allPages->where('page_type', 'home')->map(function ($page) {
                    return [
                        'id'        => $page->id,
                        'title'     => $page->title,
                        'slug'      => $page->slug,
                        'blocks'    => $page->blocks
                    ];
                })->values();
            } else {
                // Return single object for specific section page types
                $page = $allPages->where('page_type', $type)->first();
                $groupedPages[$type] = $page ? [
                    'id'        => $page->id,
                    'title'     => $page->title,
                    'slug'      => $page->slug,
                    'blocks'    => $page->blocks
                ] : null;
            }
        }

        return $groupedPages;
    }

    // if need dynamic
    //      public function index(Request $request): JsonResponse
    // {
    //     // 1. Fetch Hero Sliders
    //     $sliders = HeroSlider::where('is_active', true)
    //         ->orderBy('sort_order', 'asc')
    //         ->get();

    //     // 2. Fetch Partner Universities
    //     $partners = University::with('country')
    //         ->where('is_partner', true)
    //         ->get();

    //     // 3. Fetch Popular Destinations & Universities
    //     $popularCountries = Country::where('is_popular', true)->get();
    //     $popularUniversities = University::with('country')
    //         ->where('is_popular', true)
    //         ->limit(8)
    //         ->get();

    //     // 4. Fetch Latest Blogs
    //     $latestBlogs = BlogPost::with(['category', 'author'])
    //         ->where('status', 'published')
    //         ->latest('published_at')
    //         ->limit(3)
    //         ->get();

    //     // 5. Fetch all active CMS Pages grouped by type
    //     $groupedPages = $this->getGroupedPages();

    //     return response()->json([
    //         'success' => true,
    //         'home' => array_merge([
    //             'hero_sliders' => HeroSliderResource::collection($sliders),
    //             'partners' => UniversityResource::collection($partners),
    //             'popular_destinations' => [
    //                 'countries' => CountryResource::collection($popularCountries),
    //                 'universities' => UniversityResource::collection($popularUniversities),
    //             ],
    //             'latest_blogs' => BlogResource::collection($latestBlogs),
    //         ], $groupedPages),
    //     ], 200);
    // }

    
    // private function getGroupedPages(): array
    // {
    //     // Exclude static policies and country-specific guides from the main homepage payload
    //     $excludeTypes = ['country_guide', 'privacy', 'terms'];

    //     $allPages = Page::where('is_active', true)
    //         ->whereNotIn('page_type', $excludeTypes)
    //         ->whereNull('parent_id')
    //         ->whereNull('country_id')
    //         ->with(['blocks' => function($query) {
    //             $query->orderBy('sort_order', 'asc');
    //         }, 'blocks.elements'])
    //         ->get();

       
    //     $pageTypes = $allPages->pluck('page_type')->unique();

    //     $groupedPages = [];
    //     foreach ($pageTypes as $type) {
    //         if (empty($type)) {
    //             continue;
    //         }

    //         if ($type === 'home') {
    //             // Return multiple home page blocks as an array under 'home_page' key
    //             $groupedPages['home_page'] = $allPages->where('page_type', 'home')->map(function ($page) {
    //                 return [
    //                     'id'        => $page->id,
    //                     'title'     => $page->title,
    //                     'slug'      => $page->slug,
    //                     'blocks'    => $page->blocks
    //                 ];
    //             })->values();
    //         } else {
    //             // Return single object for other section page types (about, why_choose_us, services, etc.)
    //             $page = $allPages->where('page_type', $type)->first();
    //             $groupedPages[$type] = $page ? [
    //                 'id'        => $page->id,
    //                 'title'     => $page->title,
    //                 'slug'      => $page->slug,
    //                 'blocks'    => $page->blocks
    //             ] : null;
    //         }
    //     }

    //     return $groupedPages;
    // }
}
