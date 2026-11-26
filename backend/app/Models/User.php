<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const MENUS = [
        'projects' => 'Proyek',
        'content' => 'Konten Website',
        'settings' => 'Pengaturan',
        'users' => 'User',
        'messages' => 'Pesan',
    ];

    public const MENU_ROUTES = [
        'projects' => 'admin.index',
        'content' => 'admin.content.index',
        'settings' => 'admin.settings.edit',
        'users' => 'admin.users.index',
        'messages' => 'admin.messages.index',
    ];

    public function canAccessMenu(string $menu): bool
    {
        return array_key_exists($menu, self::MENUS)
            && ($this->menu_permissions === null || in_array($menu, $this->menu_permissions, true));
    }

    public function adminHomeRoute(): string
    {
        foreach (self::MENU_ROUTES as $menu => $route) {
            if ($this->canAccessMenu($menu)) return $route;
        }

        return 'home';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'menu_permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'menu_permissions' => 'array',
    ];
}
