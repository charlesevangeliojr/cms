(() => {
    const initialized = new WeakSet();

    function closeCountryDropdown(dropdown) {
        const list = dropdown.querySelector('[data-country-list]');
        const button = dropdown.querySelector('[data-country-button]');
        const chevron = dropdown.querySelector('[data-country-chevron]');
        if (!list || list.classList.contains('hidden')) return;
        list.classList.add('hidden');
        button?.setAttribute('aria-expanded', 'false');
        chevron?.classList.remove('rotate-180');
    }

    function filterCountryOptions(dropdown, term) {
        const query = term.trim().toLowerCase();
        dropdown.querySelectorAll('[data-country-option]').forEach((option) => {
            const match = query === '' || (option.dataset.name || '').includes(query);
            option.closest('li')?.classList.toggle('hidden', !match);
        });
    }

    function selectCountryCode(dropdown, code, iso) {
        const input = dropdown.querySelector('[data-country-input]');
        const label = dropdown.querySelector('[data-country-code]');
        const flag = dropdown.querySelector('[data-country-flag]');
        if (input) input.value = code;
        if (label) label.textContent = code;
        if (flag) {
            if (iso) {
                flag.src = 'https://flagcdn.com/w40/' + iso + '.png';
                flag.style.display = '';
            } else {
                flag.style.display = 'none';
            }
        }
        dropdown.querySelectorAll('[data-country-option]').forEach((option) => {
            const active = option.dataset.code === code && (!iso || option.dataset.iso === iso);
            option.classList.toggle('bg-indigo-50', active);
            option.classList.toggle('font-semibold', active);
            option.classList.toggle('text-indigo-700', active);
            option.querySelector('[data-country-check]')?.classList.toggle('hidden', !active);
            option.closest('li')?.setAttribute('aria-selected', String(active));
        });
        const search = dropdown.querySelector('[data-country-search]');
        if (search) {
            search.value = '';
            filterCountryOptions(dropdown, '');
        }
        closeCountryDropdown(dropdown);
    }

    function initializeCountryDropdowns() {
        if (document.documentElement.dataset.countryGlobalBound !== 'true') {
            document.documentElement.dataset.countryGlobalBound = 'true';
            document.addEventListener('click', (e) => {
                document.querySelectorAll('[data-country-dropdown]').forEach((dropdown) => {
                    if (!dropdown.contains(e.target)) closeCountryDropdown(dropdown);
                });
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') document.querySelectorAll('[data-country-dropdown]').forEach(closeCountryDropdown);
            });
        }
        document.querySelectorAll('[data-country-dropdown]').forEach((dropdown) => {
            const button = dropdown.querySelector('[data-country-button]');
            const list = dropdown.querySelector('[data-country-list]');
            const chevron = dropdown.querySelector('[data-country-chevron]');
            if (!button || !list) return;
            if (dropdown.dataset.countryInitialized !== 'true') {
                dropdown.dataset.countryInitialized = 'true';
                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const willOpen = list.classList.contains('hidden');
                    document.querySelectorAll('[data-country-dropdown]').forEach((other) => {
                        if (other !== dropdown) closeCountryDropdown(other);
                    });
                    list.classList.toggle('hidden', !willOpen);
                    button.setAttribute('aria-expanded', String(willOpen));
                    chevron?.classList.toggle('rotate-180', willOpen);
                });
                dropdown.querySelectorAll('[data-country-option]').forEach((option) => {
                    option.addEventListener('click', (e) => {
                        e.stopPropagation();
                        selectCountryCode(dropdown, option.dataset.code, option.dataset.iso);
                    });
                });
                dropdown.querySelector('[data-country-search]')?.addEventListener('input', (e) => {
                    filterCountryOptions(dropdown, e.target.value);
                });
            }
        });
    }

    function initializeContactNumberInputs() {
        document.querySelectorAll('#contact[inputmode="numeric"]').forEach((input) => {
            if (input.dataset.contactNumberInitialized === 'true') return;
            input.dataset.contactNumberInitialized = 'true';
            input.addEventListener('input', () => {
                input.value = input.value.replace(/\D/g, '').slice(0, 10);
            });
        });
    }

    function initializePublicForms() {
        document.querySelectorAll('form[data-ajax-form]').forEach((form) => {
            if (form.dataset.ajaxFormInitialized === 'true') return;
            form.dataset.ajaxFormInitialized = 'true';
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                if (form.dataset.submitting === 'true') return;
                form.dataset.submitting = 'true';
                const submitButton = form.querySelector('button[type="submit"]');
                const originalLabel = submitButton ? submitButton.textContent : null;
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = form.id === 'newsletter-form' ? 'Subscribing...' : 'Sending...';
                }
                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                        },
                        body: new FormData(form),
                    });
                    const data = await response.json().catch(() => ({}));
                    if (response.ok) {
                        window.showNotification?.(data.message || 'Submitted successfully.', 'success');
                        form.reset();
                    } else if (response.status === 422) {
                        const errors = data.errors || {};
                        const messages = Object.values(errors).flat();
                        window.showNotification?.(messages.join('\n') || data.message || 'Please check your input.', 'error');
                    } else if (response.status === 429) {
                        window.showNotification?.(data.message || 'Too many attempts. Please try again later.', 'error');
                    } else {
                        window.showNotification?.(data.message || 'Something went wrong. Please try again.', 'error');
                    }
                } catch (error) {
                    window.showNotification?.('Network error. Please check your connection and try again.', 'error');
                } finally {
                    delete form.dataset.submitting;
                    if (submitButton) {
                        submitButton.disabled = false;
                        if (originalLabel !== null) submitButton.textContent = originalLabel;
                    }
                }
            });
        });
    }

    function initializeSite() {
        document.querySelectorAll('[data-hero]').forEach((hero) => {
            if (initialized.has(hero)) return;
            initialized.add(hero);
            const slides = [...hero.querySelectorAll('[data-slide]')];
            const controls = hero.querySelector('[data-hero-controls]');
            if (slides.length < 2 || !controls) return;
            const dots = [...hero.querySelectorAll('[data-slide-to]')];
            const status = hero.querySelector('[data-hero-status]');
            let current = 0;
            function show(index) {
                current = (index + slides.length) % slides.length;
                slides.forEach((slide, i) => { slide.hidden = i !== current; });
                dots.forEach((dot, i) => { dot.setAttribute('aria-pressed', String(i === current)); });
                status.textContent = `Banner ${current + 1} of ${slides.length}: ${slides[current].querySelector('h1').textContent}`;
            }
            controls.hidden = false;
            hero.querySelector('[data-previous]').addEventListener('click', () => show(current - 1));
            hero.querySelector('[data-next]').addEventListener('click', () => show(current + 1));
            dots.forEach((dot, index) => dot.addEventListener('click', () => show(index)));
            hero.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                    event.preventDefault();
                    show(current + (event.key === 'ArrowRight' ? 1 : -1));
                }
            });
        });
        document.querySelectorAll('.site-mobile-nav').forEach((menu) => {
            if (initialized.has(menu)) return;
            initialized.add(menu);
            menu.addEventListener('click', (event) => {
                if (event.target.closest('a')) menu.open = false;
            }, { capture: true });
            menu.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    menu.open = false;
                    menu.querySelector('summary').focus();
                }
            });
        });
        initializeCountryDropdowns();
        initializeContactNumberInputs();
        initializePublicForms();
    }
    document.addEventListener('DOMContentLoaded', initializeSite);
    document.addEventListener('turbo:load', initializeSite);
    initializeSite();
})();
