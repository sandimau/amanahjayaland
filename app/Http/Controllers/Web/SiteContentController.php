<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SiteContentController extends Controller
{
    public function index(Request $request): View
    {
        $page = $request->query('page', 'home');
        abort_unless(in_array($page, SiteContent::PAGES, true), 404);

        return view('admin.site-content', [
            'page' => $page,
            'pages' => [
                'home' => ['Beranda', route('home')],
                'services' => ['Layanan', route('services')],
                'about' => ['Tentang Kami', route('about')],
                'contact' => ['Kontak', route('contact')],
                'gallery' => ['Galeri', route('gallery')],
                'project' => ['Detail Proyek (contoh)', asset('original-design/project-standalone.html')],
            ],
            'contents' => SiteContent::whereIn('page', ['global', $page])->latest('updated_at')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'page' => ['required', Rule::in([...SiteContent::PAGES, 'global'])],
            'selector' => ['required', 'string', 'max:1024', 'not_regex:/[<\r\n]/'],
            'kind' => ['required', Rule::in(SiteContent::KINDS)],
            'node_index' => ['required', 'integer', 'min:0', 'max:200'],
            'label' => ['nullable', 'string', 'max:150'],
            'value' => ['present', 'nullable', 'string', 'max:10000'],
        ]);
        $data['value'] ??= '';

        if (in_array($data['kind'], ['text', 'placeholder'], true)) {
            abort_if(str_contains($data['value'], "\0"), 422);
        } else {
            abort_if($data['value'] === '', 422, 'Tujuan tautan atau gambar tidak boleh kosong.');
            $this->validateUrl($data['value'], $data['kind']);
        }

        $content = SiteContent::updateOrCreate(
            [
                'page' => $data['page'], 'selector' => $data['selector'],
                'kind' => $data['kind'], 'node_index' => $data['node_index'],
            ],
            ['label' => $data['label'] ?? null, 'value' => $data['value']],
        );

        return response()->json($content);
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate(['image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']]);
        $path = $request->file('image')->store('site', 'public');

        return response()->json(['url' => '/storage/'.$path]);
    }

    public function destroy(SiteContent $content): JsonResponse
    {
        $value = $content->value;
        $content->delete();

        if (str_starts_with($value, '/storage/site/')) {
            $path = 'site/'.basename((string) parse_url($value, PHP_URL_PATH));
            if (! SiteContent::where('value', $value)->exists()) Storage::disk('public')->delete($path);
        }

        return response()->json(['message' => 'Konten dikembalikan ke desain awal.']);
    }

    private function validateUrl(string $url, string $kind): void
    {
        $isLocal = str_starts_with($url, '/') && ! str_starts_with($url, '//') && ! str_contains($url, '\\');
        $isAnchor = $kind === 'link' && preg_match('/^#[a-zA-Z0-9_-]+$/', $url);
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $isWeb = in_array($scheme, ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL);
        $isContact = $kind === 'link' && in_array($scheme, ['mailto', 'tel'], true);

        if (! $isLocal && ! $isAnchor && ! $isWeb && ! $isContact) {
            abort(422, 'Gunakan URL relatif (/...), HTTPS, email (mailto:), atau telepon (tel:).');
        }
    }
}
