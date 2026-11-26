<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);
        $data['image_path'] = $request->file('image')->store('gallery', 'public');
        unset($data['image']);

        return response()->json(GalleryImage::create($data), 201);
    }

    public function destroy(GalleryImage $image): JsonResponse
    {
        $path = $image->image_path;
        $image->delete();
        Storage::disk('public')->delete($path);

        return response()->json(null, 204);
    }
}
