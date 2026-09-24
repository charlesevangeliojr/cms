<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * List real users from the database.
     */
    public function index()
    {
        $users = User::orderByDesc('created_at')->paginate(10);

        return view('backend.users.index', compact('users'));
    }

    /**
     * Show create user form.
     */
    public function create()
    {
        $roleDefinitions = $this->activeRoleDefinitions();
        $roles = $roleDefinitions->pluck('name');
        $modules = $this->getModules();
        $actions = $this->getActions();
        $checkedPermissions = old('permissions', []);
        $roleDefaults = $roleDefinitions
            ->mapWithKeys(fn (Role $role) => [$role->name => $role->permissions ?? []])
            ->all();

        return view('backend.users.create', compact('roles', 'modules', 'actions', 'checkedPermissions', 'roleDefaults'));
    }

    /**
     * Store a new user in the database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', $this->activeRoleRule()],
            'contact' => ['nullable', 'string', 'max:20'],
            'permissions' => ['nullable', 'array'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'contact' => $validated['contact'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'permissions' => $this->normalizePermissions($request),
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show edit user form with the real user.
     */
    public function edit(User $user)
    {
        $roleDefinitions = $this->activeRoleDefinitions();
        $roles = $roleDefinitions->pluck('name');
        $modules = $this->getModules();
        $actions = $this->getActions();
        $checkedPermissions = $user->effectivePermissions();
        $roleDefaults = $roleDefinitions
            ->mapWithKeys(fn (Role $role) => [$role->name => $role->permissions ?? []])
            ->all();

        return view('backend.users.edit', compact('roles', 'modules', 'actions', 'user', 'checkedPermissions', 'roleDefaults'));
    }

    /**
     * Update the user in the database.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', $this->activeRoleRule()],
            'contact' => ['nullable', 'string', 'max:20'],
            'permissions' => ['nullable', 'array'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->contact = $validated['contact'] ?? null;
        $user->is_active = $request->boolean('is_active');
        $user->permissions = $this->normalizePermissions($request);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Delete the user from the database.
     */
    public function destroy(User $user)
    {
        if ($user->is_protected) {
            return redirect()->route('users.index')->with('error', 'This account is protected and cannot be deleted.');
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        if (User::count() <= 1) {
            return redirect()->route('users.index')->with('error', 'You cannot delete the last remaining user.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Active role definitions used by the role selector and role defaults.
     */
    private function activeRoleDefinitions()
    {
        return Role::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Validation rule for a role that is active in the database.
     */
    private function activeRoleRule()
    {
        return Rule::exists('roles', 'name')->where('is_active', true);
    }

    /**
     * Convert submitted checkboxes into a complete permission matrix.
     *
     * Unchecked boxes are absent from the request, so every missing
     * module/action is stored explicitly as false. This preserves an
     * intentionally cleared dashboard matrix instead of falling back to
     * the selected role's defaults.
     *
     * @return array<string, array<string, bool>>
     */
    private function normalizePermissions(Request $request): array
    {
        $submitted = $request->input('permissions', []);
        $permissions = [];

        foreach ($this->getModules() as $module) {
            foreach (['view', 'add', 'edit', 'delete'] as $action) {
                $permissions[$module['key']][$action] = ! empty($submitted[$module['key']][$action]);
            }
        }

        return $permissions;
    }

    /**
     * Shared modules for permission matrix.
     */
    private function getModules()
    {
        return [
            ['key' => 'dashboard', 'name' => 'Dashboard', 'description' => 'Main overview and shortcuts.'],
            ['key' => 'banners', 'name' => 'Banner Management', 'description' => 'Promotional banners and placements.'],
            ['key' => 'users', 'name' => 'User Management', 'description' => 'Accounts, roles, and access.'],
            ['key' => 'contacts', 'name' => 'Contact Us', 'description' => 'Inbox for contact form messages.'],
            ['key' => 'newsletters', 'name' => 'Newsletter', 'description' => 'Newsletter subscribers and audience.'],
        ];
    }

    private function getActions()
    {
        return [
            ['key' => 'view', 'name' => 'View', 'description' => 'Open and read pages.'],
            ['key' => 'add', 'name' => 'Add', 'description' => 'Create new records.'],
            ['key' => 'edit', 'name' => 'Edit', 'description' => 'Change existing records.'],
            ['key' => 'delete', 'name' => 'Delete', 'description' => 'Permanently remove records.'],
        ];
    }
}
