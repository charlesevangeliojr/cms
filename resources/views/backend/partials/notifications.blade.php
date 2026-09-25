@php
    $notifications = collect(['success' => session('success'), 'error' => session('error'), 'info' => session('info')])
        ->filter(fn ($message) => filled($message))
        ->map(fn ($message, $type) => ['type' => $type, 'message' => $message])->values()->all();
    if ($errors->any()) {
        $notifications[] = ['type' => 'error', 'message' => implode("\n", $errors->all())];
    }
@endphp
<div id="notification-data" hidden data-notifications='@json($notifications)'></div>
<dialog id="notification-modal" aria-labelledby="notification-title" aria-describedby="notification-message" class="w-[calc(100%-2rem)] max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white p-0 text-slate-900 shadow-2xl shadow-slate-950/20 backdrop:bg-slate-950/55 backdrop:backdrop-blur-sm">
    <div class="relative px-6 py-8 text-center sm:px-8">
        <div id="notification-icon" aria-hidden="true" class="mx-auto flex h-16 w-16 items-center justify-center rounded-full ring-8 text-3xl font-bold"></div>
        <h2 id="notification-title" class="mt-6 text-xl font-bold tracking-tight"></h2>
        <p id="notification-message" class="mx-auto mt-2 max-w-sm whitespace-pre-line break-words text-sm leading-6 text-slate-600"></p>
        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
            <button id="notification-cancel" type="button" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-200">Cancel</button>
            <button id="notification-confirm" type="button" class="inline-flex min-h-11 items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-4">OK</button>
        </div>
    </div>
</dialog>
<script>
(function () {
    if (!window.cmsNotificationsInstalled) {
        window.cmsNotificationsInstalled = true;
        const queue = [];
        let current = null;
        const appearances = {
            success: { title: 'Success', icon: '\u2713', iconClass: 'bg-emerald-100 text-emerald-600 ring-emerald-50', titleClass: 'text-emerald-950', buttonClass: 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-200' },
            error: { title: 'Something needs attention', icon: '!', iconClass: 'bg-rose-100 text-rose-600 ring-rose-50', titleClass: 'text-rose-950', buttonClass: 'bg-rose-600 hover:bg-rose-700 focus:ring-rose-200' },
            info: { title: 'Notice', icon: 'i', iconClass: 'bg-sky-100 text-sky-600 ring-sky-50', titleClass: 'text-sky-950', buttonClass: 'bg-sky-600 hover:bg-sky-700 focus:ring-sky-200' },
            delete: { title: 'Delete this item?', icon: '!', iconClass: 'bg-rose-100 text-rose-600 ring-rose-50', titleClass: 'text-rose-950', buttonClass: 'bg-rose-600 hover:bg-rose-700 focus:ring-rose-200' }
        };
        function advance() {
            const dialog = document.getElementById('notification-modal');
            if (current || !queue.length || !dialog) return;
            current = queue.shift();
            const appearance = appearances[current.confirm ? 'delete' : current.type] || appearances.info;
            const icon = document.getElementById('notification-icon');
            const title = document.getElementById('notification-title');
            const confirm = document.getElementById('notification-confirm');
            icon.textContent = appearance.icon;
            icon.className = `mx-auto flex h-16 w-16 items-center justify-center rounded-full ring-8 text-3xl font-bold ${appearance.iconClass}`;
            title.textContent = appearance.title;
            title.className = `mt-6 text-xl font-bold tracking-tight ${appearance.titleClass}`;
            document.getElementById('notification-message').textContent = current.message;
            const cancel = document.getElementById('notification-cancel');
            cancel.hidden = !current.confirm;
            confirm.textContent = current.confirm ? 'Delete' : 'OK';
            confirm.className = `inline-flex min-h-11 items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-4 ${appearance.buttonClass}`;
            dialog.showModal();
            (current.confirm ? cancel : confirm).focus();
        }
        function finish(accepted) {
            if (!current) return;
            const pending = current;
            current = null;
            document.getElementById('notification-modal')?.close();
            pending.resolve(accepted);
            advance();
        }
        window.showNotification = (message, type = 'info', confirm = false) => new Promise(resolve => {
            queue.push({message: String(message), type, confirm, resolve});
            advance();
        });
        document.addEventListener('click', event => {
            if (event.target.closest('#notification-confirm')) finish(true);
            if (event.target.closest('#notification-cancel')) finish(false);
        });
        document.addEventListener('cancel', event => {
            if (event.target.id === 'notification-modal') { event.preventDefault(); finish(false); }
        }, true);
        const pendingForms = new WeakSet();
        document.addEventListener('submit', async event => {
            const form = event.target;
            if (!form.matches('form[data-confirm]')) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            if (pendingForms.has(form)) return;
            pendingForms.add(form);
            const submitter = event.submitter;
            const accepted = await window.showNotification(form.dataset.confirm, 'info', true);
            pendingForms.delete(form);
            if (!accepted || !form.isConnected) return;
            const message = form.dataset.confirm;
            delete form.dataset.confirm;
            try { form.requestSubmit(submitter || undefined); }
            finally { form.dataset.confirm = message; }
        }, true);
        document.addEventListener('turbo:before-cache', () => {
            queue.splice(0).forEach(item => item.resolve(false));
            finish(false);
        });
        window.initializeNotificationModals = () => {
            const data = document.getElementById('notification-data');
            if (!data || data.dataset.initialized) return;
            data.dataset.initialized = 'true';
            JSON.parse(data.dataset.notifications || '[]').forEach(item => window.showNotification(item.message, item.type));
            ['role-status', 'role-create-error'].forEach(id => {
                const element = document.getElementById(id);
                if (!element) return;
                new MutationObserver(() => {
                    if (element.textContent.trim()) window.showNotification(element.textContent, id.includes('error') ? 'error' : 'info');
                }).observe(element, {childList: true, characterData: true, subtree: true});
            });
        };
        document.addEventListener('DOMContentLoaded', window.initializeNotificationModals);
        document.addEventListener('turbo:load', window.initializeNotificationModals);
    }
    if (document.readyState !== 'loading') window.initializeNotificationModals();
})();
</script>
