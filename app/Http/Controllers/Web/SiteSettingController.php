<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.site-settings', ['settings' => array_merge(
            SiteSetting::DEFAULTS, SiteSetting::pluck('value', 'key')->all(),
        )]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kpr_price' => ['required', 'integer', 'min:10000000', 'max:10000000000000'],
            'kpr_down_payment' => ['required', 'numeric', 'min:0', 'max:90'],
            'kpr_interest' => ['required', 'numeric', 'min:0', 'max:30'],
            'kpr_term' => ['required', Rule::in([5, 10, 15, 20, 25])],
            'contact_services' => ['required', 'string', 'max:1000', function ($attribute, $value, $fail) {
                $lines = preg_split('/\r\n|\r|\n/', trim($value));
                if (count($lines) > 10 || count($lines) < 1 || collect($lines)->contains(fn ($line) => trim($line) === '' || mb_strlen($line) > 100)) {
                    $fail('Isi 1–10 layanan, satu nama per baris (maksimal 100 karakter).');
                }
            }],
        ]);

        foreach ($data as $key => $value) {
            if ($key === 'contact_services') $value = implode("\n", array_map('trim', preg_split('/\r\n|\r|\n/', trim($value))));
            SiteSetting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        return back()->with('success', 'Pengaturan KPR berhasil disimpan.');
    }
}
