(() => {
    const initialized = new WeakSet();
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
    }
    document.addEventListener('DOMContentLoaded', initializeSite);
    document.addEventListener('turbo:load', initializeSite);
    initializeSite();
})();
