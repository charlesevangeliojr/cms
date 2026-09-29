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
        $users = User::with('role')->orderByDesc('created_at')->paginate(10);

        return view('backend.users.index', compact('users'));
    }

    /**
     * Show create user form.
     */
    public function create()
    {
        $roleDefinitions = $this->activeRoleDefinitions();
        $roles = $roleDefinitions->pluck('name', 'id');
        $modules = $this->getModules();
        $actions = $this->getActions();
        $checkedPermissions = old('permissions', []);
        $roleDefaults = $roleDefinitions
            ->mapWithKeys(fn (Role $role) => [$role->id => $role->permissions ?? []])
            ->all();
        $takenEmails = User::pluck('email')->map(fn ($email) => strtolower($email))->values()->all();

        return view('backend.users.create', compact('roles', 'modules', 'actions', 'checkedPermissions', 'roleDefaults', 'takenEmails'));
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
            'role_id' => ['required', 'integer', $this->activeRoleRule()],
            'contact' => ['nullable', 'string', 'max:20', Rule::when($request->filled('contact_country'), ['regex:/^\\d{1,10}$/'])],
            'contact_country' => ['nullable', 'string', 'max:5'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => ['boolean'],
        ]);

        $this->ensureCanManageAccess($request);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'contact' => $validated['contact'] ?? null,
            'contact_country' => $validated['contact_country'] ?? null,
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
        $this->ensureCanManageAccess(request(), $user, false);
        $roleDefinitions = $this->activeRoleDefinitions();
        $roles = $roleDefinitions->pluck('name', 'id');
        $modules = $this->getModules();
        $actions = $this->getActions();
        $checkedPermissions = $user->effectivePermissions();
        $roleDefaults = $roleDefinitions
            ->mapWithKeys(fn (Role $role) => [$role->id => $role->permissions ?? []])
            ->all();
        $takenEmails = User::where('id', '!=', $user->id)->pluck('email')->map(fn ($email) => strtolower($email))->values()->all();

        return view('backend.users.edit', compact('roles', 'modules', 'actions', 'user', 'checkedPermissions', 'roleDefaults', 'takenEmails'));
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
            'role_id' => ['required', 'integer', $this->activeRoleRule()],
            'contact' => ['nullable', 'string', 'max:20', Rule::when($request->filled('contact_country'), ['regex:/^\\d{1,10}$/'])],
            'contact_country' => ['nullable', 'string', 'max:5'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => ['boolean'],
        ]);

        $this->ensureCanManageAccess($request, $user);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $validated['role_id'];
        $user->contact = $validated['contact'] ?? null;
        $user->contact_country = $validated['contact_country'] ?? null;
        $user->is_active = $user->is_protected ? true : $request->boolean('is_active');
        $user->permissions = $this->normalizePermissions($request);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('users.edit', $user)->with('success', 'User updated successfully.');
    }

    /**
     * Delete the user from the database.
     */
    public function destroy(User $user)
    {
        $this->ensureCanManageAccess(request(), $user, false);
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

    /** Prevent delegated account managers from taking over more privileged accounts. */
    private function ensureCanManageAccess(Request $request, ?User $target = null, bool $checkSubmitted = true): void
    {
        $actor = $request->user();
        if ($actor->isSuperAdmin()) {
            return;
        }

        abort_if($target && ($target->isSuperAdmin() || $target->is_protected), 403, 'Only a Super Admin may manage this account.');
        if ($checkSubmitted) {
            $submittedRoleId = $request->integer('role_id');
            $submittedIsSuperAdmin = $submittedRoleId
                ? Role::whereKey($submittedRoleId)->where('name', 'Super Admin')->exists()
                : false;
            abort_if($submittedIsSuperAdmin, 403, 'Only a Super Admin may assign this role.');
        }

        $matrices = $target ? [$target->effectivePermissions()] : [];
        if ($checkSubmitted) {
            $matrices[] = $this->normalizePermissions($request);
        }
        foreach ($matrices as $permissions) {
            foreach ($permissions as $module => $actions) {
                foreach ($actions as $action => $enabled) {
                    abort_if($enabled && ! $actor->canAccess($module, $action), 403, 'You cannot manage access beyond your own permissions.');
                }
            }
        }
    }

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
        return Rule::exists('roles', 'id')->where('is_active', true);
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
            foreach (array_keys(config('cms.privileges', [])) as $action) {
                $permissions[$module['key']][$action] = ! empty($submitted[$module['key']][$action]);
            }
        }

        return $permissions;
    }

    /**
     * Shared modules for permission matrix, from the cms page registry.
     */
    private function getModules()
    {
        $modules = [];

        foreach (config('cms.pages', []) as $key => $page) {
            $modules[] = ['key' => $key, 'name' => $page['name'], 'description' => $page['description']];
        }

        return $modules;
    }

    private function getActions()
    {
        $actions = [];

        foreach (config('cms.privileges', []) as $key => $description) {
            $actions[] = ['key' => $key, 'name' => ucfirst($key), 'description' => $description];
        }

        return $actions;
    }
}
