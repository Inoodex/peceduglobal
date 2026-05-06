<?php

namespace App\Http\Controllers\Api\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreElementRequest;
use App\Http\Resources\Admin\BlockElementResource;
use App\Models\BlockElement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ElementController extends Controller
{
    public function store(StoreElementRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $element = BlockElement::create($validated);

        return response()->json([
            'success' => true,
            'data' => new BlockElementResource($element),
            'message' => 'Element created successfully.'
        ], Response::HTTP_CREATED);
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
