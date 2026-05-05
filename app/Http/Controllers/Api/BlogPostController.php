<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\StoreBlogPostRequest;
use App\Http\Requests\Blog\UpdateBlogPostRequest;
use App\Http\Resources\BlogPostResource;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class BlogPostController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = BlogPost::with(['author', 'reviewer', 'category'])
            ->orderBy('published_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => BlogPostResource::collection($posts),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
            'message' => 'Posts retrieved successfully.'
        ], Response::HTTP_OK);
    }

    public function store(StoreBlogPostRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Set author_id from authenticated user
        $validated['author_id'] = auth()->id();

        // Generate slug from title if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['title']);
        }

        $post = BlogPost::create($validated);

        return response()->json([
            'success' => true,
            'data' => new BlogPostResource($post),
            'message' => 'Post created successfully.'
        ], Response::HTTP_CREATED);
    }

    public function show(BlogPost $blogPost): JsonResponse
    {
        $blogPost->load(['author', 'reviewer', 'category']);

        return response()->json([
            'success' => true,
            'data' => new BlogPostResource($blogPost),
            'message' => 'Post retrieved successfully.'
        ], Response::HTTP_OK);
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $blogPost): JsonResponse
    {
        $validated = $request->validated();

        // Generate slug from title if not provided
        if (empty($validated['slug']) && !empty($validated['title'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['title']);
        }

        $blogPost->update($validated);

        return response()->json([
            'success' => true,
            'data' => new BlogPostResource($blogPost),
            'message' => 'Post updated successfully.'
        ], Response::HTTP_OK);
    }

    public function destroy(BlogPost $blogPost): JsonResponse
    {
        $blogPost->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully.'
        ], Response::HTTP_OK);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Store in public disk under blog-images folder
            $path = $file->storeAs('blog-images', $filename, 'public');

            // Generate full URL
            $url = Storage::url($path);

            return response()->json([
                'success' => true,
                'url' => $url,
                'message' => 'Image uploaded successfully.'
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image provided.'
        ], Response::HTTP_BAD_REQUEST);
    }
}
