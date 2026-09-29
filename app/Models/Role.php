<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected ?array $pendingPermissions = null;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'is_active',
        // Accepted by the compatibility setter and persisted to role_privileges.
        'permissions',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Users assigned to this role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function rolePrivileges(): HasMany
    {
        return $this->hasMany(RolePrivilege::class);
    }

    public function getPermissionsAttribute(): array
    {
        $matrix = [];
        foreach (Page::query()->orderBy('id')->pluck('slug') as $page) {
            foreach (Privilege::query()->orderBy('id')->pluck('name') as $privilege) {
                $matrix[$page][$privilege] = false;
            }
        }
        foreach ($this->rolePrivileges()->with(['page', 'privilege'])->get() as $grant) {
            $matrix[$grant->page->slug][$grant->privilege->name] = true;
        }
        return $matrix;
    }

    public function setPermissionsAttribute($value): void
    {
        $this->pendingPermissions = is_array($value) ? $value : [];
    }

    public function syncPermissions(array $permissions): void
    {
        $pages = Page::query()->pluck('id', 'slug');
        $privileges = Privilege::query()->pluck('id', 'name');
        $rows = [];
        foreach ($permissions as $page => $actions) {
            foreach ($actions as $action => $allowed) {
                if ($allowed && isset($pages[$page], $privileges[$action])) {
                    $rows[] = ['role_id' => $this->id, 'page_id' => $pages[$page], 'privilege_id' => $privileges[$action]];
                }
            }
        }
        $this->rolePrivileges()->delete();
        if ($rows) {
            RolePrivilege::query()->insert($rows);
        }
    }

    protected static function booted(): void
    {
        static::saved(function (self $role): void {
            if (isset($role->pendingPermissions)) {
                $role->syncPermissions($role->pendingPermissions);
                unset($role->pendingPermissions);
            }
        });
    }
}
