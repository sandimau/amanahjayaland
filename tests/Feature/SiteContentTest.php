<?php

namespace Tests\Feature;

use App\Models\SiteContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_requires_login_and_serves_all_six_pages(): void
    {
        $this->get('/admin/konten')->assertRedirect('/login');
        $this->postJson('/admin/konten', [])->assertUnauthorized();
        $this->actingAs(User::factory()->create());

        foreach (SiteContent::PAGES as $page) {
            $this->get('/admin/konten?page='.$page)->assertOk()->assertSee('cms-frame');
        }
    }

    public function test_admin_can_edit_and_restore_page_text(): void
    {
        $this->actingAs(User::factory()->create());
        $content = [
            'page' => 'home', 'selector' => 'body > div:nth-of-type(1) > h1:nth-of-type(1)',
            'kind' => 'text', 'node_index' => 0, 'label' => 'Judul beranda',
            'value' => 'Hunian Impian Anda',
        ];

        $this->postJson('/admin/konten', $content)->assertOk();
        $this->postJson('/admin/konten', [...$content, 'value' => 'Judul diperbarui'])->assertOk();
        $this->assertDatabaseCount('site_contents', 1);
        $this->getJson('/api/site-content?page=home')->assertOk()->assertJsonPath('0.value', 'Judul diperbarui');
        $this->getJson('/api/site-content?page=contact')->assertOk()->assertExactJson([]);
        $this->postJson('/admin/konten', [
            'page' => 'global', 'selector' => '.ajl-footer > div:nth-of-type(1)',
            'kind' => 'text', 'node_index' => 0, 'value' => 'Teks semua halaman',
        ])->assertOk();
        $this->getJson('/api/site-content?page=contact')->assertOk()->assertJsonPath('0.value', 'Teks semua halaman');
        $this->deleteJson('/admin/konten/'.SiteContent::firstOrFail()->id)->assertOk();
        $this->getJson('/api/site-content?page=home')->assertJsonCount(1);
    }

    public function test_invalid_pages_urls_and_uploads_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        $this->getJson('/api/site-content?page=missing')->assertUnprocessable();
        $this->postJson('/admin/konten', [
            'page' => 'home', 'selector' => 'body > a', 'kind' => 'link',
            'node_index' => 0, 'value' => 'javascript:alert(1)',
        ])->assertUnprocessable();
        $this->postJson('/admin/konten', [
            'page' => 'home', 'selector' => 'body > a', 'kind' => 'link',
            'node_index' => 0, 'value' => '/kontak',
        ])->assertOk();
        $this->postJson('/admin/konten', [
            'page' => 'contact', 'selector' => 'body > input', 'kind' => 'placeholder',
            'node_index' => 0, 'value' => 'Masukkan nama Anda',
        ])->assertOk();
        $this->getJson('/api/site-content?page=contact')->assertJsonPath('0.value', 'Masukkan nama Anda');
        $this->assertDatabaseCount('site_contents', 2);

        Storage::fake('public');
        $url = $this->post('/admin/konten/gambar', ['image' => UploadedFile::fake()->image('banner.jpg')])
            ->assertOk()->assertJsonStructure(['url'])->json('url');
        $this->assertStringStartsWith('/storage/site/', $url);
        $this->assertCount(1, Storage::disk('public')->files('site'));
        $this->postJson('/admin/konten', [
            'page' => 'home', 'selector' => 'body > img', 'kind' => 'image',
            'node_index' => 0, 'value' => $url,
        ])->assertOk();
        $this->deleteJson('/admin/konten/'.SiteContent::where('kind', 'image')->firstOrFail()->id)->assertOk();
        $this->assertCount(0, Storage::disk('public')->files('site'));
    }

    public function test_kpr_defaults_can_be_updated_only_by_admin(): void
    {
        $this->getJson('/api/site-settings')->assertOk()->assertJsonPath('kpr_price', '500000000');
        $this->get('/admin/pengaturan')->assertRedirect('/login');
        $this->actingAs(User::factory()->create());
        $this->get('/admin/pengaturan')->assertOk()->assertSee('Harga rumah awal');
        $this->put('/admin/pengaturan', [
            'kpr_price' => 800000000, 'kpr_down_payment' => 25,
            'kpr_interest' => 8.5, 'kpr_term' => 20, 'contact_services' => "Desain\nRenovasi",
        ])->assertRedirect()->assertSessionHas('success');
        $this->getJson('/api/site-settings')->assertJsonPath('kpr_price', '800000000')
            ->assertJsonPath('kpr_term', '20')->assertJsonPath('contact_services', "Desain\nRenovasi");
        $this->put('/admin/pengaturan', [
            'kpr_price' => 100, 'kpr_down_payment' => 25,
            'kpr_interest' => 8.5, 'kpr_term' => 20, 'contact_services' => 'Desain',
        ])->assertSessionHasErrors('kpr_price');
    }
}
