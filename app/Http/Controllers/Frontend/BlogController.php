<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Http\Resources\Frontend\BlogResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BlogController extends Controller
{
    /**
     * Display a listing of all published blog posts.
     */
    public function index()
    {
        $blogs = BlogPost::with(['category', 'author'])
            ->where('status', 'published')
            ->latest('published_at')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => BlogResource::collection($blogs)->response()->getData(true),
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified blog post by slug.
     */
    public function show($slug)
    {
        $blog = BlogPost::with(['category', 'author'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (!$blog) {
            return response()->json([
                'success' => false,
                'message' => 'Blog post not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => new BlogResource($blog),
        ], Response::HTTP_OK);
    }
}
