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
    protected ?array $pendingPermissions = null;
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
        'role_id',
        'contact',
        'contact_country',
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
            'role_id' => 'integer',
            'is_active' => 'boolean',
            'is_protected' => 'boolean',
            'permissions' => 'array',
        ];
    }

    /**
     * Complete permission matrix for full-access CMS administrators,
     * built from the cms page registry.
     *
     * @return array<string, array<string, bool>>
     */
    public static function fullAccessPermissions(): array
    {
        $actions = array_fill_keys(array_keys(config('cms.privileges', [])), true);

        $permissions = [];
        foreach (array_keys(config('cms.pages', [])) as $page) {
            $permissions[$page] = $actions;
        }

        return $permissions;
    }

    /**
     * Get the publicly accessible avatar URL.
     */
    protected function avatarUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::get(
            function (mixed $value, array $attributes): ?string {
                $path = $attributes['avatar_path'] ?? null;

                if (! is_string($path) || $path === '' || str_contains($path, '..')) {
                    return null;
                }

                return '/uploads/avatars/'.basename($path);
            },
        );
    }

    /**
     * Database role assigned to this user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Legacy alias kept while views/tests migrate to the role() relation.
     */
    public function roleRecord(): BelongsTo
    {
        return $this->role();
    }

    public function userPrivileges(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserPrivilege::class)->orderBy('id');
    }

    public function isSuperAdmin(): bool
    {
        if ($this->relationLoaded('role')) {
            return $this->getRelation('role')?->name === 'Super Admin';
        }

        if ($this->relationLoaded('roleRecord')) {
            return $this->getRelation('roleRecord')?->name === 'Super Admin';
        }

        return $this->role()->where('name', 'Super Admin')->exists();
    }

    /**
     * Permissions in effect: custom stored ones, or the database role defaults.
     */
    public function effectivePermissions(): array
    {
        $role = $this->relationLoaded('role')
            ? $this->getRelation('role')
            : ($this->relationLoaded('roleRecord')
                ? $this->getRelation('roleRecord')
                : $this->role()->first());

        $overrides = $this->userPrivileges()->with(['page', 'privilege'])->get();
        if ($overrides->isEmpty()) {
            return $role?->permissions ?? self::fullAccessPermissions();
        }

        // Once user-specific settings exist, they are the complete effective
        // matrix. Missing rows therefore remain denied instead of falling back
        // to the role, matching the existing permission form semantics.
        $permissions = [];
        foreach (Page::query()->orderBy('id')->pluck('slug') as $page) {
            foreach (Privilege::query()->orderBy('id')->pluck('name') as $privilege) {
                $permissions[$page][$privilege] = false;
            }
        }
        foreach ($overrides as $override) {
            $permissions[$override->page->slug][$override->privilege->name] = $override->is_allowed;
        }
        return $permissions;
    }

    public function getPermissionsAttribute(): array
    {
        return $this->effectivePermissions();
    }

    public function setPermissionsAttribute($value): void
    {
        $this->pendingPermissions = is_array($value) ? $value : [];
    }

    public function syncPermissions(array $permissions): void
    {
        $pages = Page::query()->orderBy('id')->pluck('id', 'slug');
        $privileges = Privilege::query()->orderBy('id')->pluck('id', 'name');
        $rows = [];
        foreach ($permissions as $page => $actions) {
            foreach ($actions as $action => $allowed) {
                if (isset($pages[$page], $privileges[$action])) {
                    $rows[] = [
                        'user_id' => $this->id,
                        'page_id' => $pages[$page],
                        'privilege_id' => $privileges[$action],
                        'is_allowed' => (bool) $allowed,
                    ];
                }
            }
        }
        $this->userPrivileges()->delete();
        if ($rows) {
            UserPrivilege::query()->insert($rows);
        }
    }

    protected static function booted(): void
    {
        static::saved(function (self $user): void {
            if (isset($user->pendingPermissions)) {
                $user->syncPermissions($user->pendingPermissions);
                unset($user->pendingPermissions);
            }
        });
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
     * First admin route the user is allowed to view, following the
     * cms page registry order.
     */
    public function landingRouteName(): ?string
    {
        foreach (config('cms.pages', []) as $module => $page) {
            if ($this->canAccess($module, 'view')) {
                return $page['route'];
            }
        }

        return null;
    }
}
