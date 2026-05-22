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

        if ($request->boolean('all')) {
            $blocks = $query->orderBy('sort_order', 'asc')->latest()->get();
            return response()->json([
                'success' => true,
                'data' => BlockResource::collection($blocks),
            ], Response::HTTP_OK);
        }

        $perPage = $request->input('per_page', 15);
        $blocks = $query->orderBy('sort_order', 'asc')->latest()->paginate($perPage);
        return response()->json([
            'success' => true,
            'data' => BlockResource::collection($blocks),
            'meta' => [
                'current_page' => $blocks->currentPage(),
                'last_page' => $blocks->lastPage(),
                'total' => $blocks->total(),
                'per_page' => $blocks->perPage(),
                'from' => $blocks->firstItem(),
                'to' => $blocks->lastItem(),
            ]
        ], Response::HTTP_OK);
    }

    public function updateOrder(Request $request): JsonResponse
    {
        $orders = $request->input('orders'); // Expecting array of {id, sort_order}

        if (!is_array($orders)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid orders data provided.'
            ], Response::HTTP_BAD_REQUEST);
        }

        foreach ($orders as $order) {
            PageBlock::where('id', $order['id'])->update(['sort_order' => $order['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Blocks order updated successfully.'
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
