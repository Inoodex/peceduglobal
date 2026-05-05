<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\StoreBlogCategoryRequest;
use App\Http\Requests\Blog\UpdateBlogCategoryRequest;
use App\Http\Resources\BlogCategoryResource;
use App\Models\BlogCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BlogCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = BlogCategory::withCount('posts')->orderBy('display_order')->get();
        return response()->json([
            'success' => true,
            'data' => BlogCategoryResource::collection($categories),
            'message' => 'Categories retrieved successfully.'
        ], Response::HTTP_OK);
    }

    public function store(StoreBlogCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Auto-generate slug from name if not provided
        if (empty($validated['slug']) && !empty($validated['name'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        // Convert status to boolean
        $validated['status'] = ($validated['status'] ?? 'active') === 'active' ? 1 : 0;

        $category = BlogCategory::create($validated);

        return response()->json([
            'success' => true,
            'data' => new BlogCategoryResource($category),
            'message' => 'Category created successfully.'
        ], Response::HTTP_CREATED);
    }

    public function show(BlogCategory $blogCategory): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new BlogCategoryResource($blogCategory),
            'message' => 'Category retrieved successfully.'
        ], Response::HTTP_OK);
    }

    public function update(UpdateBlogCategoryRequest $request, BlogCategory $blogCategory): JsonResponse
    {
        $validated = $request->validated();

        // Auto-generate slug from name if not provided
        if (empty($validated['slug']) && !empty($validated['name'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        // Convert status to boolean
        $validated['status'] = ($validated['status'] ?? 'active') === 'active' ? 1 : 0;

        $blogCategory->update($validated);

        return response()->json([
            'success' => true,
            'data' => new BlogCategoryResource($blogCategory),
            'message' => 'Category updated successfully.'
        ], Response::HTTP_OK);
    }

    public function destroy(BlogCategory $blogCategory): JsonResponse
    {
        $blogCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.'
        ], Response::HTTP_OK);
    }
}
