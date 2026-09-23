<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Fixed set of assignable roles (reference data, no roles table yet).
     */
    public const ROLES = [
        'Super Admin',
        'Content Manager',
        'Editor',
        'Viewer / Analyst',
    ];

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
        $roles = self::ROLES;
        $modules = $this->getModules();

        // All-checked map for Super Admin + role defaults, used by the
        // role-select JS to pre-check the permission matrix.
        $all = [];
        foreach ($modules as $module) {
            $all[$module['key']] = ['view' => true, 'add' => true, 'edit' => true, 'delete' => true];
        }
        $roleDefaults = array_merge(['Super Admin' => $all], User::ROLE_PERMISSIONS);

        return view('backend.users.create', compact('roles', 'modules', 'roleDefaults'));
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
            'role' => ['required', 'string', Rule::in(self::ROLES)],
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
            'permissions' => $validated['permissions'] ?? null,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show edit user form with the real user.
     */
    public function edit(User $user)
    {
        $roles = self::ROLES;
        $modules = $this->getModules();
        $effectivePermissions = $user->effectivePermissions();

        return view('backend.users.edit', compact('roles', 'modules', 'user', 'effectivePermissions'));
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
            'role' => ['required', 'string', Rule::in(self::ROLES)],
            'contact' => ['nullable', 'string', 'max:20'],
            'permissions' => ['nullable', 'array'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->contact = $validated['contact'] ?? null;
        $user->is_active = $request->boolean('is_active');
        $user->permissions = $validated['permissions'] ?? null;

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
     * Shared modules for permission matrix.
     */
    private function getModules()
    {
        return [
            ['key' => 'dashboard', 'name' => 'Dashboard'],
            ['key' => 'banners', 'name' => 'Banner Management'],
            ['key' => 'users', 'name' => 'User Management'],
        ];
    }
}
