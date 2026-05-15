<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PageController extends Controller
{
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
     * Fetch countries for the navbar "Study Abroad" dropdown.
     */
    public function getCountriesForNavbar(): JsonResponse
    {
        $countries = \App\Models\Country::orderBy('name', 'asc')->get(['id', 'name', 'iso_code']);

        return response()->json([
            'success' => true,
            'data' => $countries,
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
