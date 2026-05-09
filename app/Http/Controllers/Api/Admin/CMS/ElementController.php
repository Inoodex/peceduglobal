<?php

namespace App\Http\Controllers\Api\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreElementRequest;
use App\Http\Resources\Admin\BlockElementResource;
use App\Models\BlockElement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ElementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BlockElement::with('pageBlock.page');

        if ($request->has('country_id')) {
            $query->whereHas('pageBlock.page', function($q) use ($request) {
                $q->where('country_id', $request->country_id);
            });
        }

        if ($request->has('block_id')) {
            $query->where('page_block_id', $request->block_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('element_title', 'like', "%{$search}%")
                  ->orWhere('element_body', 'like', "%{$search}%");
            });
        }

        $elements = $query->latest()->paginate(15);
        return response()->json([
            'success' => true,
            'data' => BlockElementResource::collection($elements),
        ], Response::HTTP_OK);
    }

    public function store(StoreElementRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image_path')) {
            $validated['image_path'] = $request->file('image_path')->store('elements/images', 'public');
        }

        $element = BlockElement::create($validated);

        return response()->json([
            'success' => true,
            'data' => new BlockElementResource($element),
            'message' => 'Element created successfully.'
        ], Response::HTTP_CREATED);
    }

    public function show(BlockElement $element): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new BlockElementResource($element),
        ], Response::HTTP_OK);
    }

    public function update(StoreElementRequest $request, BlockElement $element): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image_path')) {
            if ($element->image_path && Storage::disk('public')->exists($element->image_path)) {
                Storage::disk('public')->delete($element->image_path);
            }
            $validated['image_path'] = $request->file('image_path')->store('elements/images', 'public');
        }

        $element->update($validated);

        return response()->json([
            'success' => true,
            'data' => new BlockElementResource($element),
            'message' => 'Element updated successfully.'
        ], Response::HTTP_OK);
    }

    public function destroy(BlockElement $element): JsonResponse
    {
        $element->delete();
        return response()->json([
            'success' => true,
            'message' => 'Element deleted successfully.'
        ], Response::HTTP_OK);
    }
}
