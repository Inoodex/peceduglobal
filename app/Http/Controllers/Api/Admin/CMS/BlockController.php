<?php

namespace App\Http\Controllers\Api\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBlockRequest;
use App\Http\Resources\Admin\CMS\BlockResource;
use App\Models\PageBlock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BlockController extends Controller
{
    public function index(): JsonResponse
    {
        $blocks = PageBlock::with(['elements', 'page'])->latest()->paginate(15);
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
