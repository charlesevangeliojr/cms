(() => {
    const uploadForm = document.getElementById('home-banner-image-upload');
    if (!uploadForm) return;

    const fileInput = uploadForm.querySelector('input[type="file"]');
    const editor = uploadForm.querySelector('[data-crop-editor]');
    const cropImage = editor?.querySelector('[data-crop-image]');
    const cropNext = editor?.querySelector('[data-crop-next]');
    const cropError = editor?.querySelector('[data-crop-error]');
    const cropStatus = uploadForm.querySelector('[data-crop-status]');
    let sourceFiles = [];
    let croppedFiles = [];
    let cropIndex = 0;
    let cropper = null;
    let objectUrl = null;

    function clearCropper() {
        cropper?.destroy();
        cropper = null;
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = null;
    }

    function showCropError(message) {
        cropError.textContent = message;
        cropError.hidden = !message;
    }

    function loadCrop() {
        clearCropper();
        showCropError('');
        objectUrl = URL.createObjectURL(sourceFiles[cropIndex]);
        editor.querySelector('[data-crop-count]').textContent = `Image ${cropIndex + 1} of ${sourceFiles.length}: ${sourceFiles[cropIndex].name}`;
        cropNext.textContent = cropIndex === sourceFiles.length - 1 ? 'Crop and upload' : 'Crop and continue';
        cropImage.onload = () => {
            cropper = new Cropper(cropImage, {
                aspectRatio: 16 / 6,
                viewMode: 1,
                autoCropArea: 0.95,
                responsive: true,
                background: false,
                movable: true,
                zoomable: true,
                rotatable: false,
                scalable: false,
            });
        };
        cropImage.src = objectUrl;
    }

    fileInput.addEventListener('change', () => {
        clearCropper();
        editor.hidden = true;
        cropStatus.hidden = true;
        sourceFiles = Array.from(fileInput.files || []);
        if (!sourceFiles.length) return;
        croppedFiles = new Array(sourceFiles.length);
        cropIndex = 0;
        editor.hidden = false;
        loadCrop();
        editor.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    editor.querySelector('[data-crop-reset]')?.addEventListener('click', () => cropper?.reset());
    editor.querySelector('[data-crop-cancel]')?.addEventListener('click', () => {
        clearCropper();
        editor.hidden = true;
        croppedFiles = [];
    });

    cropNext?.addEventListener('click', () => {
        if (!cropper) return;
        cropNext.disabled = true;
        const canvas = cropper.getCroppedCanvas({ width: 1600, height: 600, imageSmoothingQuality: 'high', fillColor: '#fff' });
        if (!canvas) {
            cropNext.disabled = false;
            showCropError('Could not crop this image. Please try again.');
            return;
        }

        const toBlobAtQuality = quality => new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', quality));
        (async () => {
            let blob = await toBlobAtQuality(0.86);
            if (blob && blob.size > 2 * 1024 * 1024) blob = await toBlobAtQuality(0.68);
            if (!blob || blob.size > 2 * 1024 * 1024) {
                cropNext.disabled = false;
                showCropError('This crop is still larger than 2 MB. Adjust the crop and try again.');
                return;
            }

            const name = sourceFiles[cropIndex].name.replace(/\.[^.]+$/, '') || 'home-banner';
            croppedFiles[cropIndex] = new File([blob], `${name}-cropped.jpg`, { type: 'image/jpeg', lastModified: Date.now() });
            clearCropper();
            cropIndex++;
            if (cropIndex < sourceFiles.length) {
                loadCrop();
                cropNext.disabled = false;
                return;
            }

            editor.hidden = true;
            cropStatus.textContent = `Uploading ${croppedFiles.length} cropped image${croppedFiles.length === 1 ? '' : 's'}…`;
            cropStatus.hidden = false;
            fileInput.disabled = true;
            // Keep request payloads below common PHP post_max_size settings.
            const batchSize = 2;
            for (let start = 0; start < croppedFiles.length; start += batchSize) {
                const body = new FormData();
                body.append('_token', uploadForm.querySelector('input[name="_token"]')?.value || '');
                croppedFiles.slice(start, start + batchSize).forEach(file => body.append('images[]', file, file.name));
                try {
                    const response = await fetch(uploadForm.action, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': uploadForm.querySelector('input[name="_token"]')?.value || '' },
                        body,
                    });
                    if (!response.ok) throw new Error('An image batch could not be uploaded. Check that each image is under 2 MB, then try again.');
                } catch (error) {
                    cropStatus.textContent = `${error.message || 'Upload failed.'} Refresh the page before retrying to avoid adding duplicates.`;
                    cropStatus.className = 'mt-3 text-sm font-medium text-rose-700';
                    fileInput.disabled = false;
                    return;
                }
                const completed = Math.min(start + batchSize, croppedFiles.length);
                cropStatus.textContent = `Uploaded ${completed} of ${croppedFiles.length} images…`;
            }
            cropStatus.textContent = 'Images added.';
            cropStatus.className = 'mt-3 text-sm font-medium text-emerald-700';
            window.location.reload();
        })().catch(() => {
            cropNext.disabled = false;
            showCropError('Could not process the image. Please try again.');
        });
    });

    const token = uploadForm.querySelector('input[name="_token"]')?.value;
    const bannerVisibility = document.querySelector('[data-home-banner-visibility]');
    bannerVisibility?.addEventListener('change', async () => {
        const previous = !bannerVisibility.checked;
        const error = document.querySelector('[data-home-banner-visibility-error]');
        bannerVisibility.disabled = true;
        if (error) error.textContent = '';
        try {
            const response = await fetch(bannerVisibility.dataset.url, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ is_active: bannerVisibility.checked ? 1 : 0 }),
            });
            if (!response.ok) throw new Error('Could not update Home Banner visibility. Please try again.');
            const result = await response.json();
            bannerVisibility.checked = Boolean(result.is_active);
            window.showNotification?.(result.message, 'success');
        } catch (exception) {
            bannerVisibility.checked = previous;
            if (error) error.textContent = exception.message || 'Could not save.';
        } finally {
            bannerVisibility.disabled = false;
        }
    });

    document.querySelectorAll('[data-image-visibility]').forEach(checkbox => {
        checkbox.addEventListener('change', async () => {
            const previous = !checkbox.checked;
            checkbox.disabled = true;
            const error = checkbox.closest('div')?.querySelector('[data-visibility-error]');
            if (error) error.textContent = '';
            try {
                const response = await fetch(checkbox.dataset.url, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ is_active: checkbox.checked ? 1 : 0 }),
                });
                if (!response.ok) throw new Error('Could not save this image setting.');
                const result = await response.json();
                checkbox.checked = Boolean(result.is_active);
                const label = checkbox.parentElement.querySelector('[data-visibility-label]');
                if (label) label.textContent = checkbox.checked ? 'Shown' : 'Hidden';
                window.showNotification?.(result.message, 'success');
            } catch (exception) {
                checkbox.checked = previous;
                if (error) error.textContent = exception.message || 'Could not save.';
            } finally {
                checkbox.disabled = false;
            }
        });
    });

    const grid = document.querySelector('[data-home-image-grid]');
    if (!grid) return;
    const reorderUrl = grid.dataset.reorderUrl;

    async function saveImageOrder() {
        const ids = Array.from(grid.querySelectorAll('[data-image-id]')).map(card => card.dataset.imageId);
        const response = await fetch(reorderUrl, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ image_ids: ids }),
        });
        if (!response.ok) throw new Error('Could not save the slide order. Please refresh and try again.');
        return response.json();
        grid.querySelectorAll('[data-image-id]').forEach((card, index) => {
            card.querySelector('[data-slide-number]').textContent = `Slide ${index + 1}`;
            card.querySelector('[data-order-up]').disabled = index === 0;
            card.querySelector('[data-order-down]').disabled = index === ids.length - 1;
        });
    }

    grid.addEventListener('click', async event => {
        const up = event.target.closest('[data-order-up]');
        const down = event.target.closest('[data-order-down]');
        if (!up && !down) return;
        const card = event.target.closest('[data-image-id]');
        const sibling = up ? card.previousElementSibling : card.nextElementSibling;
        if (!sibling) return;
        const oldOrder = Array.from(grid.children);
        if (up) grid.insertBefore(card, sibling);
        else grid.insertBefore(sibling, card);
        grid.querySelectorAll('[data-image-id]').forEach((item, index) => {
            item.querySelector('[data-slide-number]').textContent = `Slide ${index + 1}`;
        });
        grid.querySelectorAll('[data-order-up]').forEach((button, index) => button.disabled = index === 0);
        grid.querySelectorAll('[data-order-down]').forEach((button, index, list) => button.disabled = index === list.length - 1);
        try {
            const result = await saveImageOrder();
            window.showNotification?.(result.message, 'success');
        } catch (error) {
            oldOrder.forEach(item => grid.appendChild(item));
            grid.querySelectorAll('[data-image-id]').forEach((item, index) => {
                item.querySelector('[data-slide-number]').textContent = `Slide ${index + 1}`;
                item.querySelector('[data-order-up]').disabled = index === 0;
                item.querySelector('[data-order-down]').disabled = index === grid.children.length - 1;
            });
            const orderStatus = grid.closest('section')?.querySelector('[data-order-status]');
            if (orderStatus) orderStatus.textContent = error.message;
        }
    });
})();
