(() => {
    const init = () => {
        document.querySelectorAll('[data-metadata-form]').forEach(form => {
            if (form.dataset.initialized) return;
            form.dataset.initialized = 'true';
            const keywords = form.querySelector('[data-keywords]');
            const add = form.querySelector('[data-add-keyword]');
            let nextId = keywords.children.length;
            const updateKeywordControls = () => { add.disabled = keywords.children.length >= 30; };
            const title = form.querySelector('[data-preview-title]');
            const siteName = form.querySelector('[data-preview-site-name]');
            const description = form.querySelector('[data-preview-description]');
            const ogTitle = form.querySelector('[data-preview-og-title]');
            const ogDescription = form.querySelector('[data-preview-og-description]');
            const searchTitle = form.querySelector('[data-preview-search-title]');
            const searchDescription = form.querySelector('[data-preview-search-description]');
            const socialTitle = form.querySelector('[data-preview-social-title]');
            const socialDescription = form.querySelector('[data-preview-social-description]');
            const updatePreview = () => {
                searchTitle.textContent = `${title.value.trim() || 'Your page title'} | ${siteName.value.trim() || 'Your site name'}`;
                searchDescription.textContent = description.value.trim() || 'Your meta description preview will appear here.';
                socialTitle.textContent = ogTitle.value.trim() || title.value.trim() || 'Your page title';
                socialDescription.textContent = ogDescription.value.trim() || description.value.trim() || 'Your social description preview will appear here.';
                form.querySelectorAll('[data-character-count]').forEach(counter => {
                    const field = form.querySelector(`#${counter.dataset.characterCount}`);
                    counter.textContent = field.value.length;
                    counter.parentElement.classList.toggle('text-rose-600', field.value.length >= field.maxLength);
                });
            };
            form.querySelectorAll('[data-preview-title], [data-preview-site-name], [data-preview-description], [data-preview-og-title], [data-preview-og-description]').forEach(field => field.addEventListener('input', updatePreview));
            updatePreview();
            add.addEventListener('click', () => {
                if (keywords.children.length >= 30) return;
                const row = document.createElement('div');
                row.dataset.keywordRow = '';
                row.className = 'flex items-center gap-2';
                const input = document.createElement('input');
                input.id = `keyword-${nextId++}`;
                input.name = 'keywords[]';
                input.maxLength = 80;
                input.placeholder = 'Enter meta keyword';
                input.setAttribute('aria-label', 'Meta keyword');
                input.className = 'min-w-0 flex-1 rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.dataset.removeKeyword = '';
                remove.textContent = 'Remove';
                remove.setAttribute('aria-label', 'Remove keyword');
                remove.className = 'rounded-lg px-3 py-2 text-sm font-semibold text-gray-500 hover:bg-rose-50 hover:text-rose-700';
                row.append(input, remove);
                keywords.append(row);
                updateKeywordControls();
                input.focus();
            });
            keywords.addEventListener('click', event => {
                const button = event.target.closest('[data-remove-keyword]');
                if (!button) return;
                const row = button.closest('[data-keyword-row]');
                if (keywords.children.length === 1) row.querySelector('input').value = '';
                else row.remove();
                keywords.querySelectorAll('input').forEach((input, index) => {
                    input.id = `keyword-${index}`;
                    input.setAttribute('aria-label', `Meta keyword ${index + 1}`);
                });
                keywords.querySelector('input').focus();
                nextId = keywords.children.length;
                updateKeywordControls();
            });
            updateKeywordControls();
            form.querySelectorAll('[data-image-input]').forEach(input => {
                let previewUrl;
                const preview = input.parentElement.querySelector('[data-image-preview]');
                const status = input.parentElement.querySelector('[data-upload-status]');
                    const original = preview.src;
                    input.addEventListener('change', () => {
                    if (previewUrl) URL.revokeObjectURL(previewUrl);
                    const file = input.files[0];
                    previewUrl = file ? URL.createObjectURL(file) : null;
                    preview.src = previewUrl || original;
                    if (input.id === 'image') form.querySelector('[data-social-card-image]').src = preview.src;
                    status.textContent = file ? `Selected: ${file.name}. Save settings to upload.` : 'Current image';
                });
                document.addEventListener('turbo:before-cache', () => {
                    if (previewUrl) URL.revokeObjectURL(previewUrl);
                }, { once: true });
            });
        });
    };
    init();
    document.addEventListener('turbo:load', init);
})();
