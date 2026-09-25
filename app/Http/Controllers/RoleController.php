<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Show the form for creating a database-backed role.
     */
    public function create()
    {
        $this->ensureSuperAdmin();

        return view('backend.roles.create', [
            'modules' => $this->modules(),
        ]);
    }

    /**
     * Store a new role and its default permissions.
     */
    public function store(Request $request)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => ['boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'permissions' => $this->normalizePermissions(
                is_array($validated['permissions'] ?? null) ? $validated['permissions'] : [],
            ),
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['role' => $role->only(['name', 'permissions', 'is_active'])], 201);
        }

        return redirect()
            ->route('users.create')
            ->with('success', "Role {$role->name} created successfully.")
            ->with('selected_role', $role->name);
    }

    /**
     * Delete only unused roles; protect the built-in administrator role.
     */
    public function destroy(Role $role)
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'Only a Super Admin may delete roles.');
        abort_if($role->name === 'Super Admin', 422, 'The Super Admin role cannot be deleted.');
        abort_if($role->users()->exists(), 422, 'This role is assigned to accounts. Assign those accounts a different role before deleting it.');
        $role->delete();

        return response()->json(['message' => 'Role deleted successfully.']);
    }

    private function ensureSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'You are not allowed to create roles.');
    }

    /**
     * Convert checkbox input into a complete permission matrix.
     *
     * @param  array<string, mixed>  $submitted
     * @return array<string, array<string, bool>>
     */
    private function normalizePermissions(array $submitted): array
    {
        $permissions = [];

        foreach ($this->modules() as $module) {
            foreach (['view', 'add', 'edit', 'delete'] as $action) {
                $permissions[$module['key']][$action] = (bool) data_get(
                    $submitted,
                    "{$module['key']}.{$action}",
                    false,
                );
            }
        }

        return $permissions;
    }

    /**
     * Modules shared with the user permission matrix.
     *
     * @return list<array{key: string, name: string}>
     */
    private function modules(): array
    {
        return [
            ['key' => 'dashboard', 'name' => 'Dashboard'],
            ['key' => 'banners', 'name' => 'Banner Management'],
            ['key' => 'users', 'name' => 'User Management'],
            ['key' => 'contacts', 'name' => 'Contact Us'],
            ['key' => 'newsletters', 'name' => 'Newsletter'],
        ];
    }
}
