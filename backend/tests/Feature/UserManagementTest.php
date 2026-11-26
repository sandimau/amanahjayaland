<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_management_requires_login(): void
    {
        $this->get('/admin/pengguna')->assertRedirect('/login');
        $this->get('/admin/pengguna/baru')->assertRedirect('/login');
        $this->post('/admin/pengguna', [])->assertRedirect('/login');
        $this->get('/admin/pengguna/1/edit')->assertRedirect('/login');
        $this->put('/admin/pengguna/1', [])->assertRedirect('/login');
    }

    public function test_admin_can_add_and_edit_user_without_changing_password(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/admin/pengguna')->assertOk()->assertSee('Tambah User');
        $this->get('/admin/pengguna/baru')->assertOk();

        $this->post('/admin/pengguna', [
            'name' => 'Admin Baru', 'email' => 'baru@example.com',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
            'menu_permissions' => ['projects', 'users'],
        ])->assertRedirect(route('admin.users.index'));
        $user = User::where('email', 'baru@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('rahasia123', $user->password));

        $this->get(route('admin.users.edit', $user))->assertOk()->assertSee('baru@example.com');
        $this->put(route('admin.users.update', $user), [
            'name' => 'Admin Diperbarui', 'email' => 'baru@example.com',
            'password' => '', 'password_confirmation' => '',
            'menu_permissions' => ['projects', 'users'],
        ])->assertRedirect(route('admin.users.index'));
        $this->assertSame('Admin Diperbarui', $user->fresh()->name);
        $this->assertTrue(Hash::check('rahasia123', $user->fresh()->password));

        $this->put(route('admin.users.update', $user), [
            'name' => 'Admin Diperbarui', 'email' => 'baru@example.com',
            'password' => 'katasandi456', 'password_confirmation' => 'katasandi456',
            'menu_permissions' => ['messages'],
        ])->assertRedirect(route('admin.users.index'));
        $this->assertTrue(Hash::check('katasandi456', $user->fresh()->password));
        $this->assertSame(['messages'], $user->fresh()->menu_permissions);
    }

    public function test_user_validation_rejects_duplicate_email_and_unconfirmed_password(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $this->post('/admin/pengguna', [
            'name' => 'Duplikat', 'email' => $admin->email,
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
            'menu_permissions' => ['projects'],
        ])->assertSessionHasErrors('email');
        $this->post('/admin/pengguna', [
            'name' => 'Baru', 'email' => 'beda@example.com',
            'password' => 'rahasia123', 'password_confirmation' => 'salah456',
            'menu_permissions' => ['projects'],
        ])->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_restricted_user_only_sees_and_accesses_granted_menus(): void
    {
        $manager = User::factory()->create();
        $this->actingAs($manager);
        $this->post('/admin/pengguna', [
            'name' => 'Editor Konten', 'email' => 'editor@example.com',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
            'menu_permissions' => ['content', 'messages'],
        ])->assertRedirect(route('admin.users.index'));
        $editor = User::where('email', 'editor@example.com')->firstOrFail();
        $this->assertSame(['content', 'messages'], $editor->menu_permissions);
        $this->actingAs($editor);
        $this->get('/home')->assertRedirect(route('admin.content.index'));
        $this->get('/login')->assertRedirect(route('admin.content.index'));
        $this->get('/admin/konten')->assertOk()->assertSee('>Konten Website</a>', false)
            ->assertSee('>Pesan</a>', false)->assertDontSee('>Proyek</a>', false)
            ->assertDontSee('>Pengaturan</a>', false)->assertDontSee('>User</a>', false);
        $this->get('/admin/pesan')->assertOk();
        $this->get('/admin')->assertForbidden();
        $this->post('/admin/proyek', ['name' => 'Dilarang', 'slug' => 'dilarang'])->assertForbidden();
        $project = Project::create(['name' => 'Uji', 'slug' => 'uji']);
        $this->post('/admin/proyek/'.$project->id.'/foto', [])->assertForbidden();
        $this->get('/admin/pengaturan')->assertForbidden();
        $this->put('/admin/pengaturan', [])->assertForbidden();
        $this->get('/admin/pengguna')->assertForbidden();
        $this->post('/admin/pengguna', [])->assertForbidden();
        $this->get('/admin/pengguna/'.$manager->id.'/edit')->assertForbidden();
        $this->put('/admin/pengguna/'.$manager->id, [])->assertForbidden();
        $this->assertDatabaseCount('users', 2);
    }

    public function test_user_permissions_are_validated_and_self_management_cannot_be_removed(): void
    {
        $manager = User::factory()->create();
        $this->actingAs($manager);
        $this->post('/admin/pengguna', [
            'name' => 'Kosong', 'email' => 'kosong@example.com',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
            'menu_permissions' => [],
        ])->assertSessionHasErrors('menu_permissions');
        $this->post('/admin/pengguna', [
            'name' => 'Tidak Valid', 'email' => 'invalid@example.com',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
            'menu_permissions' => ['users', 'unknown'],
        ])->assertSessionHasErrors('menu_permissions.1');
        $this->put(route('admin.users.update', $manager), [
            'name' => $manager->name, 'email' => $manager->email,
            'menu_permissions' => ['content'],
        ])->assertSessionHasErrors('menu_permissions');
        $this->assertNull($manager->fresh()->menu_permissions);
    }

    public function test_api_token_cannot_bypass_menu_permissions(): void
    {
        $editor = User::factory()->create(['menu_permissions' => ['content']]);
        $token = $editor->createToken('uji')->plainTextToken;

        $this->withToken($token)->getJson('/api/admin/projects')->assertForbidden();
        $this->withToken($token)->postJson('/api/admin/projects', [
            'name' => 'Tidak Boleh', 'slug' => 'tidak-boleh',
        ])->assertForbidden();
        $this->withToken($token)->getJson('/api/admin/messages')->assertForbidden();
    }
}
