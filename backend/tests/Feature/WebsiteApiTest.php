<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_projects_are_visible(): void
    {
        Project::create(['name' => 'Terbit', 'slug' => 'terbit', 'is_published' => true]);
        Project::create(['name' => 'Draf', 'slug' => 'draf', 'is_published' => false]);

        $this->getJson('/api/projects')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/projects/draf')->assertNotFound();
        $this->getJson('/api/projects/terbit')->assertOk()->assertJsonPath('name', 'Terbit');
        $this->getJson('/api/projects?per_page=1')->assertOk()->assertJsonPath('per_page', 1);
        $this->getJson('/api/projects?per_page=100')->assertUnprocessable();
    }

    public function test_contact_form_is_validated_and_saved(): void
    {
        $this->postJson('/api/contact', ['name' => 'A'])->assertUnprocessable();
        $this->postJson('/api/contact', [
            'name' => 'Aman', 'email' => 'aman@example.com', 'message' => 'Saya ingin bertanya mengenai proyek.',
        ])->assertCreated();
        $this->assertDatabaseCount('contact_messages', 1);
    }

    public function test_gallery_only_includes_photos_from_published_projects(): void
    {
        $published = Project::create(['name' => 'Terbit', 'slug' => 'terbit', 'is_published' => true]);
        $draft = Project::create(['name' => 'Draf', 'slug' => 'draf']);
        $published->images()->create(['image_path' => 'gallery/terbit.jpg', 'caption' => 'Foto terbit']);
        $draft->images()->create(['image_path' => 'gallery/draf.jpg', 'caption' => 'Foto draf']);

        $this->getJson('/api/gallery?per_page=1')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.caption', 'Foto terbit')
            ->assertJsonPath('data.0.project.slug', 'terbit');
        $this->getJson('/api/gallery?per_page=99')->assertUnprocessable();
    }

    public function test_admin_routes_require_authentication(): void
    {
        $this->postJson('/api/admin/projects', ['name' => 'X', 'slug' => 'x'])->assertUnauthorized();
        $this->getJson('/api/admin/messages')->assertUnauthorized();
    }

    public function test_admin_can_manage_projects_with_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('admin')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->postJson('/api/admin/projects', ['name' => 'Proyek Baru', 'slug' => 'proyek-baru', 'is_published' => true], $headers)
            ->assertCreated();
        $this->getJson('/api/projects/proyek-baru')->assertOk();
        $this->postJson('/api/admin/logout', [], $headers)->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
