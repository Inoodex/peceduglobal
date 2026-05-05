<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePageRequest;
use App\Http\Resources\Admin\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PageController extends Controller
{
    public function index(): JsonResponse
    {
        $pages = Page::latest()->paginate(15);
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
        
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('pages/thumbnails', 'public');
        }

        $page = Page::create($validated);

        return response()->json([
            'success' => true,
            'data' => new PageResource($page),
            'message' => 'Page created successfully.'
        ], Response::HTTP_CREATED);
    }

    public function show(Page $page): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new PageResource($page),
        ], Response::HTTP_OK);
    }

    public function update(StorePageRequest $request, Page $page): JsonResponse
    {
        $validated = $request->validated();
        
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('pages/thumbnails', 'public');
        }

        $page->update($validated);

        return response()->json([
            'success' => true,
            'data' => new PageResource($page),
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
}
