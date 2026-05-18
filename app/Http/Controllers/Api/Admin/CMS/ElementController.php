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

        $perPage = $request->input('per_page', 15);
        $elements = $query->latest()->paginate($perPage);
        return response()->json([
            'success' => true,
            'data' => BlockElementResource::collection($elements),
            'meta' => [
                'current_page' => $elements->currentPage(),
                'last_page' => $elements->lastPage(),
                'total' => $elements->total(),
                'per_page' => $elements->perPage(),
                'from' => $elements->firstItem(),
                'to' => $elements->lastItem(),
            ]
        ], Response::HTTP_OK);
    }

    public function store(StoreElementRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $file) {
                $paths[] = $file->store('elements/images', 'public');
            }
            $validated['image_paths'] = $paths;
        }

        unset($validated['images']);
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

        if ($request->hasFile('images')) {
            // Delete old images
            if ($element->image_paths) {
                foreach ($element->image_paths as $oldPath) {
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
            }
            
            $paths = [];
            foreach ($request->file('images') as $file) {
                $paths[] = $file->store('elements/images', 'public');
            }
            $validated['image_paths'] = $paths;
        }

        unset($validated['images']);
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
