<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\GalleryImage;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'projects' => Project::latest()->paginate(10),
            'messageCount' => ContactMessage::count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.project-form', ['project' => new Project]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_published'] = $request->boolean('is_published');
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('projects', 'public');
        }
        $project = Project::create($data);

        return redirect()->route('admin.projects.edit', $project)->with('success', 'Proyek berhasil dibuat.');
    }

    public function edit(Project $project): View
    {
        return view('admin.project-form', ['project' => $project->load('images')]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $this->validated($request, $project);
        $data['is_published'] = $request->boolean('is_published');
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('projects', 'public');
        }
        $oldCover = $project->cover_image;
        $project->update($data);
        if (isset($data['cover_image']) && $oldCover) {
            Storage::disk('public')->delete($oldCover);
        }

        return back()->with('success', 'Proyek berhasil diperbarui.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $paths = $project->images()->pluck('image_path')->all();
        $paths[] = $project->cover_image;
        $project->delete();
        Storage::disk('public')->delete(array_filter($paths));

        return redirect()->route('admin.index')->with('success', 'Proyek berhasil dihapus.');
    }

    public function addImage(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $project->images()->create([
            'image_path' => $request->file('image')->store('gallery', 'public'),
            'caption' => $data['caption'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return back()->with('success', 'Foto berhasil ditambahkan.');
    }

    public function removeImage(GalleryImage $image): RedirectResponse
    {
        $path = $image->image_path;
        $image->delete();
        Storage::disk('public')->delete($path);

        return back()->with('success', 'Foto berhasil dihapus.');
    }

    public function messages(): View
    {
        return view('admin.messages', ['messages' => ContactMessage::latest()->paginate(15)]);
    }

    public function deleteMessage(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return back()->with('success', 'Pesan berhasil dihapus.');
    }

    private function validated(Request $request, ?Project $project = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:255', Rule::unique('projects')->ignore($project?->id)],
            'summary' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }
}
