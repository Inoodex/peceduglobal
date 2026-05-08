<?php

namespace App\Http\Controllers\Api\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePageRequest;
use App\Http\Resources\Admin\CMS\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index(): JsonResponse
    {
        $pages = Page::with(['country', 'parent', 'children'])
                    ->latest()
                    ->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => PageResource::collection($pages),
            'meta' => [
                'current_page' => $pages->currentPage(),
                'last_page' => $pages->lastPage(),
                'total' => $pages->total(),
            ]
        ], Response::HTTP_OK);
    }

    public function store(StorePageRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        // Auto-generate slug from title if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title']);
        }
        
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('pages/thumbnails', 'public');
        }

        $page = Page::create($validated);

        return response()->json([
            'success' => true,
            'data' => new PageResource($page->load(['country', 'parent'])),
            'message' => 'Page created successfully.'
        ], Response::HTTP_CREATED);
    }

    public function show(Page $page): JsonResponse
    {
        $page->load(['country', 'parent', 'children', 'blocks.elements']);
        
        return response()->json([
            'success' => true,
            'data' => new PageResource($page),
        ], Response::HTTP_OK);
    }

    public function update(StorePageRequest $request, Page $page): JsonResponse
    {
        $validated = $request->validated();
        
        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title'], $page->id);
        }
        
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('pages/thumbnails', 'public');
        }

        $page->update($validated);

        return response()->json([
            'success' => true,
            'data' => new PageResource($page->load(['country', 'parent'])),
            'message' => 'Page updated successfully.'
        ], Response::HTTP_OK);
    }

    public function destroy(Page $page): JsonResponse
    {
        $page->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully.'
        ], Response::HTTP_OK);
    }

    /**
     * Generate unique slug from title
     */
    private function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        $query = Page::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
            $query = Page::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }
}
