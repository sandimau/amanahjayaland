<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebsitePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_original_design_pages_are_served(): void
    {
        $this->assertFileExists(public_path('original-assets/brand-fallback.svg'));
        Project::create(['name' => 'Proyek Terbit', 'slug' => 'proyek-terbit', 'is_published' => true]);
        Project::create(['name' => 'Proyek Draf', 'slug' => 'proyek-draf']);

        $this->get('/')->assertOk()->assertHeader('content-type', 'text/html; charset=UTF-8')
            ->assertHeader('cache-control', 'max-age=600, public')
            ->assertCookieMissing(config('session.cookie'));
        $this->get('/layanan')->assertOk();
        $this->get('/tentang-kami')->assertOk();
        $this->get('/galeri')->assertOk();
        $this->get('/proyek/proyek-terbit')->assertOk();
        $this->get('/proyek/proyek-draf')->assertNotFound();
        $this->get('/detail-proyek')->assertRedirect('/galeri');
        $this->get('/kontak')->assertOk();
    }

    public function test_admin_requires_login_and_can_manage_projects(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/home')->assertRedirect('/login');
        $user = User::factory()->create();
        $this->withSession(['url.intended' => 'http://localhost/home']);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/admin');
        $this->get('/home')->assertRedirect('/admin');
        $this->get('/login')->assertRedirect('/admin');
        $this->post('/admin/proyek', ['name' => 'Rumah Baru', 'slug' => 'rumah-baru', 'is_published' => '1'])
            ->assertRedirect();
        $this->assertDatabaseHas('projects', ['slug' => 'rumah-baru', 'is_published' => 1]);
        $project = Project::firstOrFail();
        $this->put('/admin/proyek/'.$project->id, ['name' => 'Rumah Baru', 'slug' => 'rumah-baru'])
            ->assertRedirect();
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'is_published' => 0]);
        $this->post('/logout')->assertRedirect('/');
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_admin_can_upload_project_and_gallery_images(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $this->post('/admin/proyek', [
            'name' => 'Taman Kota', 'slug' => 'taman-kota', 'is_published' => '1',
            'cover_image' => UploadedFile::fake()->image('cover.jpg'),
        ])->assertRedirect();

        $project = Project::firstOrFail();
        Storage::disk('public')->assertExists($project->cover_image);
        $this->post('/admin/proyek/'.$project->id.'/foto', [
            'image' => UploadedFile::fake()->image('photo.jpg'), 'caption' => 'Area taman',
        ])->assertRedirect();
        $this->get('/proyek/taman-kota')->assertOk();
        $this->get('/galeri')->assertOk();
        $image = $project->images()->firstOrFail();
        $this->delete('/admin/foto/'.$image->id)->assertRedirect();
        Storage::disk('public')->assertMissing($image->image_path);
    }
}
