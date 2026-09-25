<fieldset>
    <legend class="mb-2 text-sm font-semibold text-gray-900">Assign Role *</legend>
    <div class="flex rounded-xl bg-stone-100 p-1">
        <button type="button" id="existing-role-tab" onclick="setRoleMode(false)" aria-pressed="true" class="min-w-0 flex-1 rounded-lg bg-indigo-950 px-3 py-2 text-xs font-semibold text-white">Select Existing Role</button>
        @if (auth()->user()?->isSuperAdmin())
            <button type="button" id="create-role-tab" onclick="setRoleMode(true)" aria-pressed="false" class="min-w-0 flex-1 rounded-lg px-3 py-2 text-xs font-semibold text-gray-600">Create New Role</button>
        @endif
    </div>
    <p class="mt-2 text-xs text-gray-500">A role is a reusable set of permissions, such as Editor or Content Manager.</p>
    @if (auth()->user()?->isSuperAdmin())
        <div id="role-create-fields" hidden class="mt-4 space-y-3">
            <label for="new-role-name" class="block text-sm font-semibold">New Role Name *</label>
            <input id="new-role-name" type="text" maxlength="255" placeholder="e.g. Content Editor" class="w-full rounded-xl border border-gray-300 px-4 py-2.5" onkeydown="if(event.key === 'Enter'){ event.preventDefault(); document.getElementById('create-role-save').click(); }">
            <p class="text-xs text-gray-500">Choose the default access in the Module Permissions table below, then create the role. Your account details stay here.</p>
            <button id="create-role-save" type="button" onclick="saveRoleDialog(this)" data-create-url="{{ route('roles.store') }}" class="rounded-xl bg-indigo-900 px-4 py-2 text-sm font-semibold text-white">Create and select role</button>
            <p id="role-create-error" role="alert" class="text-sm text-red-700"></p>
        </div>
        <p id="role-status" role="status" aria-live="polite" class="mt-2 text-sm text-gray-700"></p>

        <dialog id="role-dialog" aria-labelledby="role-dialog-title" aria-describedby="role-dialog-help" class="w-[calc(100%-2rem)] max-w-2xl max-h-[85vh] overflow-y-auto rounded-2xl border-0 p-0 shadow-xl backdrop:bg-black/50" oncancel="if(this.dataset.busy === 'true') event.preventDefault()">
            <div class="p-5 sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <h2 id="role-dialog-title" class="text-xl font-bold text-gray-900">Create New Role</h2>
                    <button type="button" data-role-close onclick="closeRoleDialog()" aria-label="Close role dialog" class="rounded-lg px-3 py-2 text-gray-600">Close</button>
                </div>
                <p id="role-dialog-help" class="mt-2 text-sm text-gray-500"></p>
                <p id="role-dialog-error" role="alert" class="mt-4 text-sm text-red-700"></p>
                <div class="mt-5 flex flex-wrap justify-end gap-3">
                    <button type="button" data-role-close onclick="closeRoleDialog()" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold">Cancel</button>
                    <button id="role-dialog-save" type="button" onclick="saveRoleDialog(this)" data-create-url="{{ route('roles.store') }}" data-delete-url="{{ route('roles.destroy', ['role' => '__ROLE__']) }}" class="rounded-xl bg-indigo-900 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">Create and select role</button>
                </div>
            </div>
        </dialog>
    @endif
</fieldset>
<script>
function handleRoleSelection() {
    const select = document.getElementById('role');
    const button = document.getElementById('delete-role-button');
    if (button) {
        button.hidden = !select.value;
        button.disabled = select.value === 'Super Admin';
        button.title = button.disabled ? 'The Super Admin role cannot be deleted.' : 'Delete this role';
    }
}
function setRoleMode(creating) {
    const panel = document.getElementById('role-create-fields');
    if (!panel) return;
    const select = document.getElementById('role');
    if (creating && panel.hidden) {
        panel.savedPermissions = Array.from(document.querySelectorAll('.perm-check')).map(box => box.checked);
    }
    if (!creating && !panel.hidden && panel.savedPermissions) {
        document.querySelectorAll('.perm-check').forEach((box, i) => box.checked = panel.savedPermissions[i]);
        syncRowToggles(); syncColumnToggles(); syncFullAccessToggle();
    }
    panel.hidden = !creating;
    document.getElementById('role-select-fields').hidden = creating;
    select.required = !creating;
    document.getElementById('user-form-save').disabled = creating;
    ['existing-role-tab', 'create-role-tab'].forEach((id, i) => {
        const tab = document.getElementById(id);
        const active = creating === (i === 1);
        tab.setAttribute('aria-pressed', String(active));
        tab.style.background = active ? '#29264f' : 'transparent';
        tab.style.color = active ? 'white' : '#4b5563';
    });
    if (creating) document.getElementById('new-role-name').focus();
    handleRoleSelection();
}function openRoleDialog(mode) {
    const dialog = document.getElementById('role-dialog');
    const select = document.getElementById('role');
    const status = document.getElementById('role-status');
    if (mode === 'delete' && (!select.value || select.value === 'Super Admin')) {
        status.textContent = select.value ? 'The Super Admin role cannot be deleted.' : 'Select the role you want to delete first.';
        return;
    }
    dialog.dataset.mode = mode;
    dialog.dataset.role = select.value;
    document.getElementById('role-dialog-title').textContent = mode === 'create' ? 'Create New Role' : 'Delete role?';
    document.getElementById('role-dialog-help').textContent = mode === 'create'
        ? 'Give the role a name and choose its default permissions. Your account details will stay on this page. The role is saved immediately; save the user separately.'
        : `Delete "${select.value}" permanently? Roles assigned to any account cannot be deleted. Your account details will stay on this page.`;

    document.getElementById('role-dialog-save').textContent = mode === 'create' ? 'Create and select role' : 'Delete role permanently';
    document.getElementById('role-dialog-error').textContent = '';
    dialog.showModal();
    if (mode === 'create') document.getElementById('new-role-name').focus();
    else dialog.querySelector('[data-role-close]').focus();
}
function closeRoleDialog() {
    const dialog = document.getElementById('role-dialog');
    if (dialog.dataset.busy !== 'true') dialog.close();
}
async function saveRoleDialog(button) {
    const dialog = document.getElementById('role-dialog');
    if (dialog.dataset.busy === 'true') return;
    const form = button.closest('form');
    const creating = button.id === 'create-role-save';
    const error = document.getElementById(creating ? 'role-create-error' : 'role-dialog-error');
    const name = document.getElementById('new-role-name');
    if (creating && !name.value.trim()) {
        error.textContent = 'Enter a role name, for example Content Editor.';
        name.focus();
        return;
    }
    const payload = new FormData();
    payload.append('_token', form.querySelector('[name="_token"]').value);
    if (creating) {
        payload.append('name', name.value.trim());
        payload.append('is_active', '1');
        form.querySelectorAll('.perm-check:checked').forEach(box => payload.append(box.name, '1'));
    } else payload.append('_method', 'DELETE');
    const buttons = Array.from(form.querySelectorAll('button'));
    buttons.forEach(button => button.disabled = true);
    dialog.dataset.busy = 'true';
    error.textContent = '';
    const label = button.textContent;
    button.textContent = creating ? 'Creating role...' : 'Deleting role...';
    try {
        const url = creating ? button.dataset.createUrl : button.dataset.deleteUrl.replace('__ROLE__', encodeURIComponent(dialog.dataset.role));
        const response = await fetch(url, { method: 'POST', body: payload, headers: { Accept: 'application/json' } });
        const data = await response.json();
        if (!response.ok) {
            error.textContent = Object.values(data.errors || {}).flat().join(' ') || data.message || 'Unable to save changes.';
            return;
        }
        const select = document.getElementById('role');
        const defaults = JSON.parse(select.dataset.roleDefaults || '{}');
        if (creating) {
            defaults[data.role.name] = data.role.permissions;
            select.add(new Option(data.role.name, data.role.name), select.querySelector('optgroup'));
            select.value = data.role.name;
            name.value = '';
            document.getElementById('role-create-fields').savedPermissions = null;
            setRoleMode(false);
        } else {
            Array.from(select.options).filter(option => option.value === dialog.dataset.role).forEach(option => option.remove());
            delete defaults[dialog.dataset.role];
            select.value = '';
            select.dataset.lastRole = '';
        }
        select.dataset.roleDefaults = JSON.stringify(defaults);
        if (creating) select.dispatchEvent(new Event('change', { bubbles: true }));
        document.getElementById('role-status').textContent = creating ? 'Role created and selected. Review the account permissions, then save the user.' : 'Role deleted. Select another role before saving the user.';
        dialog.close();
        handleRoleSelection();
    } catch (failure) {
        error.textContent = 'Unable to confirm the change. Your account details are still here. Check your connection before trying again.';
    } finally {
        dialog.dataset.busy = 'false';
        buttons.forEach(button => button.disabled = false);
        document.getElementById('user-form-save').disabled = !document.getElementById('role-create-fields').hidden;
        handleRoleSelection();
        button.textContent = label;
    }
}
</script>
