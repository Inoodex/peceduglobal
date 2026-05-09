<?php

namespace App\Http\Controllers\Api\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBlockRequest;
use App\Http\Resources\Admin\CMS\BlockResource;
use App\Models\PageBlock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BlockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PageBlock::with(['elements', 'page']);

        if ($request->has('country_id')) {
            $query->whereHas('page', function($q) use ($request) {
                $q->where('country_id', $request->country_id);
            });
        }

        if ($request->has('page_id')) {
            $query->where('page_id', $request->page_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('block_type', 'like', "%{$search}%")
                  ->orWhere('section_title', 'like', "%{$search}%");
            });
        }

        $blocks = $query->latest()->paginate(15);
        return response()->json([
            'success' => true,
            'data' => BlockResource::collection($blocks),
        ], Response::HTTP_OK);
    }

    public function store(StoreBlockRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $block = PageBlock::create($validated);

        return response()->json([
            'success' => true,
            'data' => new BlockResource($block),
            'message' => 'Block created successfully.'
        ], Response::HTTP_CREATED);
    }

    public function destroy(PageBlock $block): JsonResponse
    {
        $block->delete();
        return response()->json([
            'success' => true,
            'message' => 'Block deleted successfully.'
        ], Response::HTTP_OK);
    }

    public function show(PageBlock $block): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new BlockResource($block),
        ], Response::HTTP_OK);
    }

    public function update(StoreBlockRequest $request, PageBlock $block): JsonResponse
    {
        $validated = $request->validated();
        $block->update($validated);

        return response()->json([
            'success' => true,
            'data' => new BlockResource($block),
            'message' => 'Block updated successfully.'
        ], Response::HTTP_OK);
    }
}
