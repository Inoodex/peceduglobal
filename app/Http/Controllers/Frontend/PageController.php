<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PageController extends Controller
{
    /**
     * Get list of all active pages for navigation.
     */
    public function index(): JsonResponse
    {
        $pages = Page::where('is_active', true)
            ->get(['id', 'title', 'slug', 'page_type']);

        return response()->json([
            'success' => true,
            'data' => $pages,
        ], Response::HTTP_OK);
    }

    /**
     * Fetch the "About Us" page content.
     */
    public function about(): JsonResponse
    {
        return $this->getPageData('about');
    }

    /**
     * Fetch the "Why Choose Us" page content.
     */
    public function whyChooseUs(): JsonResponse
    {
        return $this->getPageData('why_choose_us');
    }

    /**
     * Fetch the "Services" page content.
     */
    public function services(): JsonResponse
    {
        return $this->getPageData('services');
    }

    /**
     * Fetch the "Statistics" page content.
     */
    public function statistics(): JsonResponse
    {
        return $this->getPageData('statistics');
    }

    /**
     * Fetch the "Comparison" page content.
     */
    public function getComparison(): JsonResponse
    {
        return $this->getPageData('comparison');
    }

    /**
     * Fetch the "About the Company" page content.
     */
    public function getAboutCompany(): JsonResponse
    {
        return $this->getPageData('about_the_company');
    }

    /**
     * Fetch the "FAQ" page content.
     */
    public function getFaqs(): JsonResponse
    {
        return $this->getPageData('faq');
    }

    /**
     * Fetch countries for the navbar "Study Abroad" dropdown.
     */
    public function getCountriesForNavbar(): JsonResponse
    {
        $countries = Country::orderBy('name', 'asc')->get(['id', 'name', 'iso_code']);

        return response()->json([
            'success' => true,
            'data' => $countries,
        ], Response::HTTP_OK);
    }

    /**
     * Fetch popular countries with their guide page title for the navbar.
     */
    public function getPopularDestinations(): JsonResponse
    {
        $countries = Country::where('is_popular', true)
            ->with(['guidePage' => function($query) {
                $query->select('id', 'country_id', 'title', 'slug');
            }])
            ->get(['id', 'name', 'iso_code', 'thumbnail']);

        $data = $countries->map(function($country) {
            return [
                'id' => $country->id,
                'name' => $country->name,
                'iso_code' => $country->iso_code,
                'thumbnail' => $country->thumbnail ? (str_starts_with($country->thumbnail, '/storage/') ? $country->thumbnail : '/storage/' . $country->thumbnail) : null,
                'page_title' => $country->guidePage?->title,
                'page_slug' => $country->guidePage?->slug,
            ];
        });

        return response()->json([
            'success' => true,
            'popular_destinations' => $data,
        ], Response::HTTP_OK);
    }

    /**
     * Fetch the country guide page based on country ID.
     */
    public function getCountryGuide($countryId): JsonResponse
    {
        $page = Page::where('page_type', 'country_guide')
            ->where('country_id', $countryId)
            ->where('is_active', true)
            ->with(['blocks' => function($query) {
                $query->orderBy('sort_order', 'asc');
            }, 'blocks.elements'])
            ->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Country guide page not found for this country.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Attach universities to specific blocks if they exist
        foreach ($page->blocks as $block) {
            if ($block->block_type === 'partners' || $block->block_type === 'university_list') {
                $block->universities = \App\Models\University::where('country_id', $countryId)
                    ->where(function($q) {
                        $q->where('is_partner', true)->orWhere('is_popular', true);
                    })
                    ->get();
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'title' => $page->title,
                'page_type' => $page->page_type,
                'blocks' => $page->blocks,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Fetch a country-specific page by its type and country ID.
     */
    public function getCountryPageByType($countryId, $pageType): JsonResponse
    {
        $page = Page::where('page_type', $pageType)
            ->where('country_id', $countryId)
            ->where('is_active', true)
            ->with(['blocks' => function($query) {
                $query->orderBy('sort_order', 'asc');
            }, 'blocks.elements'])
            ->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found for this country and type.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Attach universities to specific blocks if they exist
        foreach ($page->blocks as $block) {
            if ($block->block_type === 'partners' || $block->block_type === 'university_list') {
                $block->universities = \App\Models\University::where('country_id', $countryId)
                    ->where(function($q) {
                        $q->where('is_partner', true)->orWhere('is_popular', true);
                    })
                    ->get();
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'title' => $page->title,
                'page_type' => $page->page_type,
                'blocks' => $page->blocks,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Fetch the university guide page based on university ID.
     */
    public function getUniversityGuide($universityId): JsonResponse
    {
        $page = Page::where('page_type', 'university_guide')
            ->where('university_id', $universityId)
            ->where('is_active', true)
            ->with(['blocks' => function($query) {
                $query->orderBy('sort_order', 'asc');
            }, 'blocks.elements'])
            ->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'University guide page not found for this university.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Attach courses to specific blocks if they exist
        foreach ($page->blocks as $block) {
            if ($block->block_type === 'course_list') {
                $block->courses = \App\Models\Course::where('university_id', $universityId)
                    ->with('courseLevel')
                    ->get();
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'title' => $page->title,
                'page_type' => $page->page_type,
                'blocks' => $page->blocks,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Fetch a page dynamically by its slug.
     */
    public function show(string $slug): JsonResponse
    {
        $page = Page::where('slug', $slug)
            ->where('is_active', true)
            ->with(['blocks' => function($query) {
                $query->orderBy('sort_order', 'asc');
            }, 'blocks.elements'])
            ->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'title' => $page->title,
                'page_type' => $page->page_type,
                'slug' => $page->slug,
                'blocks' => $page->blocks,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Fetch a page dynamically by its internal page_type.
     */
    public function showByType(string $type): JsonResponse
    {
        return $this->getPageData($type);
    }

    /**
     * Helper method to fetch page data by type.
     */
    private function getPageData(string $pageType): JsonResponse
    {
        $page = Page::where('page_type', $pageType)
            ->where('is_active', true)
            ->with(['blocks' => function($query) {
                $query->orderBy('sort_order', 'asc');
            }, 'blocks.elements'])
            ->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => ucfirst(str_replace('_', ' ', $pageType)) . ' page not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'title' => $page->title,
                'page_type' => $page->page_type,
                'blocks' => $page->blocks,
            ],
        ], Response::HTTP_OK);
    }
}
