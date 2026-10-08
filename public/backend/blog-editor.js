(() => {
    function initialize() {
        const form = document.querySelector('[data-blog-form]');
        if (!form || form.dataset.initialized) return;
        form.dataset.initialized = 'true';
        const source = form.querySelector('#blog-content');
        let editor = null;
        // Parse saved/pasted markup into a small allowlist before using it in the editor.
        function safeHtml(html) {
            const parsed = new DOMParser().parseFromString(html, 'text/html');
            const allowed = new Set(['P', 'DIV', 'BR', 'H2', 'H3', 'H4', 'STRONG', 'B', 'EM', 'I', 'U', 'S', 'UL', 'OL', 'LI', 'BLOCKQUOTE', 'PRE', 'CODE', 'A', 'HR', 'FIGURE', 'FIGCAPTION', 'TABLE', 'THEAD', 'TBODY', 'TFOOT', 'TR', 'TH', 'TD']);
            const discard = new Set(['SCRIPT', 'STYLE', 'IFRAME', 'OBJECT', 'EMBED', 'SVG', 'MATH', 'FORM', 'INPUT', 'TEXTAREA', 'BUTTON', 'SELECT', 'TEMPLATE']);
            function copy(node, parent) {
                if (node.nodeType === Node.TEXT_NODE) { parent.append(document.createTextNode(node.textContent)); return; }
                if (node.nodeType !== Node.ELEMENT_NODE || discard.has(node.tagName)) return;
                let dest = parent;
                if (allowed.has(node.tagName)) {
                    dest = document.createElement(node.tagName.toLowerCase());
                    if (node.tagName === 'A') {
                        const href = (node.getAttribute('href') || '').trim();
                        if (/^https?:\/\//i.test(href) || /^\/(?!\/)/.test(href) && !/[\\\s\x00-\x1f]/.test(href)) dest.setAttribute('href', href);
                    }
                    parent.append(dest);
                }
                Array.from(node.childNodes).forEach(child => copy(child, dest));
            }
            const container = document.createElement('div');
            Array.from(parsed.body.childNodes).forEach(node => copy(node, container));
            return container.innerHTML;
        }
        const title = form.querySelector('#blog-title'), slug = form.querySelector('#blog-slug'), seoTitle = form.querySelector('#seo-title'), description = form.querySelector('#meta-description'), excerpt = form.querySelector('#blog-excerpt'), category = form.querySelector('#blog-category');
        const slugify = value => value.normalize('NFKD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 180);
        function updateSeo() {
            const prefix = 'blogs/' + (category.selectedOptions[0]?.dataset.slug || 'news') + '/';
            form.querySelector('[data-slug-prefix]').textContent = prefix;
            form.querySelector('[data-seo-url]').textContent = window.location.origin + '/' + prefix + slugify(slug.value || title.value);
            form.querySelector('[data-seo-title]').textContent = seoTitle.value || title.value || 'Your blog post title';
            const contentText = editor ? editor.getData().replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim() : source.value.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
            form.querySelector('[data-seo-description]').textContent = description.value || excerpt.value || contentText.slice(0, 160) || 'Add a title and description to preview your search engine listing.';
            form.querySelector('[data-title-count]').textContent = seoTitle.value.length + ' of 70 characters used';
            form.querySelector('[data-description-count]').textContent = description.value.length + ' of 160 characters used';
        }
        [title, slug, seoTitle, description, excerpt, category].forEach(input => input.addEventListener('input', updateSeo));
        updateSeo();
        source.value = safeHtml(source.value);
        if (window.ClassicEditor) {
            window.ClassicEditor.create(source, {
                toolbar: [
                    'heading', '|', 'bold', 'italic', 'link', '|',
                    'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                    'blockQuote', 'insertTable', 'undo', 'redo',
                ],
                heading: { options: [
                    { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                    { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                    { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
                ] },
                table: { contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'] },
            }).then(instance => {
                editor = instance;
                form.classList.add('blog-editor-ready');
                instance.model.document.on('change:data', () => {
                    source.value = safeHtml(instance.getData());
                    form.querySelector('[data-content-error]').hidden = !!instance.getData().replace(/<[^>]*>/g, '').trim();
                    updateSeo();
                });
                updateSeo();
            }).catch(error => console.error('CKEditor could not be initialized.', error));
        }
        form.addEventListener('submit', event => {
            if (editor) source.value = safeHtml(editor.getData());
            const text = source.value.replace(/<[^>]*>/g, '').trim();
            if (!text) {
                event.preventDefault(); editor?.focus();
                form.querySelector('[data-content-error]').hidden = false;
            }
        });
        form.querySelectorAll('[data-image-drop]').forEach(drop => {
            const imageInput = drop.querySelector('input[type="file"]');
            const preview = drop.querySelector('[data-image-preview]');
            const imageError = drop.querySelector('[data-image-error]');
            let previewUrl;
            const setImageError = message => {
                if (!imageError) return;
                imageError.textContent = message;
                imageError.hidden = !message;
            };
            const acceptImage = file => {
                if (!file) return;
                if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                    setImageError('Choose a JPG, PNG, or WebP image.');
                    imageInput.value = '';
                    return;
                }
                if (file.size > Number(imageInput.dataset.maxBytes || 2097152)) {
                    setImageError('The image must be 2 MB or smaller.');
                    imageInput.value = '';
                    return;
                }
                setImageError('');
                if (previewUrl) URL.revokeObjectURL(previewUrl);
                previewUrl = URL.createObjectURL(file);
                preview.src = previewUrl;
                preview.hidden = false;
            };
            imageInput.addEventListener('change', () => acceptImage(imageInput.files[0]));
            drop.addEventListener('dragover', event => { event.preventDefault(); drop.classList.add('is-dragging'); });
            drop.addEventListener('dragleave', () => drop.classList.remove('is-dragging'));
            drop.addEventListener('drop', event => {
                event.preventDefault();
                drop.classList.remove('is-dragging');
                const file = event.dataTransfer.files[0];
                if (file) {
                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    imageInput.files = transfer.files;
                    imageInput.dispatchEvent(new Event('change'));
                }
            });
        });
    }
    initialize();
    document.addEventListener('turbo:load', initialize);
})();
