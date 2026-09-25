<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
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
        'avatar_path',
        'is_active',
        'is_protected',
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
            'is_protected' => 'boolean',
            'permissions' => 'array',
        ];
    }

    /**
     * Complete permission matrix for full-access CMS administrators.
     *
     * @return array<string, array<string, bool>>
     */
    public static function fullAccessPermissions(): array
    {
        $actions = [
            'view' => true,
            'add' => true,
            'edit' => true,
            'delete' => true,
        ];

        return [
            'dashboard' => $actions,
            'banners' => $actions,
            'users' => $actions,
            'contacts' => $actions,
            'newsletters' => $actions,
        ];
    }

    /**
     * Get the publicly accessible avatar URL.
     */
    protected function avatarUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::get(
            fn (mixed $value, array $attributes): ?string => ! empty($attributes['avatar_path'])
                ? \Illuminate\Support\Facades\Storage::disk('avatars')->url($attributes['avatar_path'])
                : null,
        );
    }

    /**
     * Database role definition assigned to this user.
     */
    public function roleRecord(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role', 'name');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'Super Admin';
    }

    /**
     * Permissions in effect: custom stored ones, or the database role defaults.
     */
    public function effectivePermissions(): array
    {
        if (is_array($this->permissions)) {
            return $this->permissions;
        }

        $role = $this->relationLoaded('roleRecord')
            ? $this->getRelation('roleRecord')
            : $this->roleRecord()->first();

        return $role?->permissions ?? [];
    }

    /**
     * Check module access, e.g. canAccess('banners', 'delete').
     */
    public function canAccess(string $module, string $action): bool
    {
        $permissions = $this->effectivePermissions();

        return ! empty($permissions[$module][$action]);
    }

    /**
     * First admin route the user is allowed to view.
     */
    public function landingRouteName(): ?string
    {
        foreach (['dashboard' => 'dashboard', 'contacts' => 'contacts.index', 'newsletters' => 'newsletters.index', 'banners' => 'banners.index', 'users' => 'users.index'] as $module => $route) {
            if ($this->canAccess($module, 'view')) {
                return $route;
            }
        }

        return null;
    }
}
