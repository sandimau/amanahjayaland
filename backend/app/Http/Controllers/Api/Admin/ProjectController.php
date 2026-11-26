<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Project::latest()->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('projects', 'public');
        } else {
            unset($data['cover_image']);
        }

        return response()->json(Project::create($data), 201);
    }

    public function show(Project $project): JsonResponse
    {
        return response()->json($project->load('images'));
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $data = $this->validated($request, $project);
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('projects', 'public');
        } else {
            unset($data['cover_image']);
        }
        $oldImage = $project->cover_image;
        $project->update($data);
        if (isset($data['cover_image']) && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return response()->json($project->refresh());
    }

    public function destroy(Project $project): JsonResponse
    {
        $images = $project->images()->pluck('image_path')->all();
        $cover = $project->cover_image;
        $project->delete();
        Storage::disk('public')->delete(array_filter([...$images, $cover]));

        return response()->json(null, 204);
    }

    private function validated(Request $request, ?Project $project = null): array
    {
        $rules = $project ? ['sometimes'] : ['required'];

        return $request->validate([
            'name' => [...$rules, 'string', 'max:255'],
            'slug' => [...$rules, 'string', 'alpha_dash', 'max:255', Rule::unique('projects')->ignore($project?->id)],
            'summary' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_published' => ['sometimes', 'boolean'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }
}
