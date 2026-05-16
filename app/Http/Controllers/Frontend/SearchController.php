<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\Course;
use App\Models\BlogPost;
use App\Http\Resources\Frontend\UniversityResource;
use App\Http\Resources\Frontend\CourseResource;
use App\Http\Resources\Frontend\BlogResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SearchController extends Controller
{
    /**
     * Perform a global search across universities, courses, and blogs.
     */
    public function search(Request $request)
    {
        $query = $request->query('q');

        if (empty($query)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'universities' => [],
                    'courses' => [],
                    'blogs' => [],
                ],
            ]);
        }

        // 1. Search Universities
        $universities = University::with('country')
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orWhere('location', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        // 2. Search Courses
        $courses = Course::with(['university', 'courseLevel'])
            ->where('name', 'like', "%{$query}%")
            ->orWhere('requirements', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        // 3. Search Blogs
        $blogs = BlogPost::with(['category', 'author'])
            ->where('status', 'published')
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'universities' => UniversityResource::collection($universities),
                'courses' => CourseResource::collection($courses),
                'blogs' => BlogResource::collection($blogs),
            ],
        ], Response::HTTP_OK);
    }
}
