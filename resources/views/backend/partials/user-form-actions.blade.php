<div class="mt-6 flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:px-6">
    <p class="text-sm text-gray-500">Review account details and module permissions before saving.</p>
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('users.index') }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
        <button id="user-form-save" type="submit" class="rounded-xl bg-indigo-900 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-800">{{ isset($user) ? 'Save Changes' : 'Create User' }}</button>
    </div>
</div>
<script>
(function () {
    const form = document.getElementById('user-account-form');
    form.addEventListener('submit', event => {
        const roleEditor = document.getElementById('role-create-fields');
        if (roleEditor && !roleEditor.hidden) {
            event.preventDefault();
            document.getElementById('role-create-error').textContent = 'Create the role first, or choose Select Existing Role before saving the user.';
        }
    });
    const password = form.querySelector('[name="password"]');
    const confirmation = form.querySelector('[name="password_confirmation"]');
    function checkPasswords() {
        confirmation.setCustomValidity(password.value !== confirmation.value ? 'Passwords must match.' : '');
    }
    password.addEventListener('input', checkPasswords);
    confirmation.addEventListener('input', checkPasswords);
})();
</script>
