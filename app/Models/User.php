<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'contact',
        'is_active',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    /**
     * Default module permissions per role.
     * Used when a user has no custom permissions stored.
     * Modules: dashboard, banners, users. Actions: view, add, edit, delete.
     */
    public const ROLE_PERMISSIONS = [
        'Super Admin' => [],
        'Content Manager' => [
            'dashboard' => ['view' => true],
            'banners' => ['view' => true, 'add' => true, 'edit' => true],
            'users' => ['view' => true],
        ],
        'Editor' => [
            'dashboard' => ['view' => true],
            'banners' => ['view' => true, 'edit' => true],
        ],
        'Viewer / Analyst' => [
            'dashboard' => ['view' => true],
            'banners' => ['view' => true],
        ],
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === 'Super Admin';
    }

    /**
     * Permissions in effect: custom stored ones, or the role defaults.
     */
    public function effectivePermissions(): array
    {
        if (is_array($this->permissions)) {
            return $this->permissions;
        }

        return self::ROLE_PERMISSIONS[$this->role] ?? [];
    }

    /**
     * Check module access, e.g. canAccess('banners', 'delete').
     */
    public function canAccess(string $module, string $action): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $perms = $this->effectivePermissions();

        return ! empty($perms[$module][$action]);
    }
}
