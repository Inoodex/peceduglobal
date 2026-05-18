<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class HeroSliderController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $sliders = HeroSlider::orderBy('sort_order', 'asc')->paginate($perPage);
        return response()->json([
            'success' => true,
            'data' => $sliders->items(),
            'meta' => [
                'current_page' => $sliders->currentPage(),
                'last_page' => $sliders->lastPage(),
                'total' => $sliders->total(),
                'per_page' => $sliders->perPage(),
                'from' => $sliders->firstItem(),
                'to' => $sliders->lastItem(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:50',
            'button_url' => 'nullable|string|max:255',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:2048', // 2MB Max
            'floating_images' => 'nullable|array|max:8',
            'floating_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:2048' // Multiple images
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->only(['title', 'subtitle', 'button_text', 'button_url', 'sort_order', 'is_active']);
            
            // Handle boolean properly
            $data['is_active'] = $request->has('is_active') ? filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN) : true;

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('sliders', $filename, 'public');
                $data['background_image'] = '/storage/sliders/' . $filename;
            }

            // Handle multiple floating images
            $floatingImages = [];
            if ($request->hasFile('floating_images')) {
                foreach ($request->file('floating_images') as $file) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('sliders/floating', $filename, 'public');
                    $floatingImages[] = '/storage/sliders/floating/' . $filename;
                }
            }
            $data['floating_images'] = $floatingImages;

            $slider = HeroSlider::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Slider created successfully',
                'data' => $slider
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $slider = HeroSlider::findOrFail($id);
        return response()->json(['success' => true, 'data' => $slider]);
    }

    public function update(Request $request, $id)
    {
        $slider = HeroSlider::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:50',
            'button_url' => 'nullable|string|max:255',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:2048',
            'new_floating_images' => 'nullable|array',
            'new_floating_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:2048',
            'retained_floating_images' => 'nullable|array' // Array of URLs of images to keep
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Custom validation to ensure total floating images do not exceed 8
        $retainedCount = is_array($request->input('retained_floating_images')) ? count($request->input('retained_floating_images')) : 0;
        $newCount = $request->hasFile('new_floating_images') ? count($request->file('new_floating_images')) : 0;
        
        if (($retainedCount + $newCount) > 8) {
            return response()->json([
                'success' => false, 
                'errors' => ['floating_images' => ['You can upload a maximum of 8 floating images combined.']]
            ], 422);
        }

        try {
            $data = $request->only(['title', 'subtitle', 'button_text', 'button_url', 'sort_order', 'is_active']);
            
             // Handle boolean properly
             if ($request->has('is_active')) {
                 $data['is_active'] = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
             }

            if ($request->hasFile('image')) {
                // Delete old image
                if ($slider->background_image) {
                    $oldPath = str_replace('/storage/', '', $slider->background_image);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $file = $request->file('image');
                $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('sliders', $filename, 'public');
                $data['background_image'] = '/storage/sliders/' . $filename;
            }

            // Handle floating images update
            $currentFloatingImages = $slider->floating_images ?? [];
            $retainedImages = $request->input('retained_floating_images', []);
            
            // Delete removed floating images from storage
            $imagesToDelete = array_diff($currentFloatingImages, $retainedImages);
            foreach ($imagesToDelete as $oldImage) {
                $oldPath = str_replace('/storage/', '', $oldImage);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $finalFloatingImages = $retainedImages;

            // Upload new floating images
            if ($request->hasFile('new_floating_images')) {
                foreach ($request->file('new_floating_images') as $file) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('sliders/floating', $filename, 'public');
                    $finalFloatingImages[] = '/storage/sliders/floating/' . $filename;
                }
            }

            $data['floating_images'] = $finalFloatingImages;

            $slider->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Slider updated successfully',
                'data' => $slider
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $slider = HeroSlider::findOrFail($id);
            
            // Delete image
            if ($slider->background_image) {
                $oldPath = str_replace('/storage/', '', $slider->background_image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            
            // Delete floating images
            if (!empty($slider->floating_images)) {
                foreach ($slider->floating_images as $floatingImage) {
                    $oldPath = str_replace('/storage/', '', $floatingImage);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
            }

            $slider->delete();

            return response()->json([
                'success' => true,
                'message' => 'Slider deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
