<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->validate(['per_page' => ['sometimes', 'integer', 'min:1', 'max:24']])['per_page'] ?? 12;

        return response()->json(Project::where('is_published', true)->orderByDesc('id')->paginate($perPage));
    }

    public function show(string $slug): JsonResponse
    {
        return response()->json(Project::where('is_published', true)->where('slug', $slug)->with('images')->firstOrFail());
    }

    public function gallery(Request $request): JsonResponse
    {
        $perPage = $request->validate(['per_page' => ['sometimes', 'integer', 'min:1', 'max:24']])['per_page'] ?? 24;

        return response()->json(\App\Models\GalleryImage::whereHas('project', fn ($query) => $query->where('is_published', true))
            ->with('project:id,name,slug')->orderBy('sort_order')->latest('id')->paginate($perPage));
    }
}
