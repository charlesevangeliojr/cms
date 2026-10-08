(() => {
    const form = document.querySelector('form[action*="/admin/page-banners"]');
    if (!form) return;

    const input = form.querySelector('#banner-image');
    const editor = form.querySelector('[data-page-crop-editor]');
    const image = editor?.querySelector('[data-page-crop-image]');
    const error = editor?.querySelector('[data-page-crop-error]');
    const status = form.querySelector('[data-page-crop-status]');
    const saveButton = form.querySelector('button[type="submit"]');
    let cropper = null;
    let objectUrl = null;
    let cropApplied = false;
    let saving = false;

    function destroyCropper() {
        cropper?.destroy();
        cropper = null;
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = null;
    }

    function openCropper() {
        destroyCropper();
        error.textContent = '';
        error.hidden = true;
        const file = input.files?.[0];
        if (!file) return;
        cropApplied = false;
        status.hidden = true;
        editor.hidden = false;
        objectUrl = URL.createObjectURL(file);
        image.onload = () => {
            cropper = new Cropper(image, {
                aspectRatio: 16 / 3,
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
        image.src = objectUrl;
        editor.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    input.addEventListener('change', openCropper);
    editor.querySelector('[data-page-crop-cancel]')?.addEventListener('click', () => {
        destroyCropper();
        editor.hidden = true;
        input.value = '';
        cropApplied = false;
    });

    form.addEventListener('submit', async event => {
        if (!input.files?.length || cropApplied) return;
        event.preventDefault();
        if (saving) return;
        if (!cropper) {
            openCropper();
            error.textContent = 'Wait for the image preview, adjust the crop if needed, then save again.';
            error.hidden = false;
            return;
        }

        saving = true;
        saveButton.disabled = true;
        saveButton.textContent = 'Cropping…';
        const canvas = cropper.getCroppedCanvas({ width: 1600, height: 300, imageSmoothingQuality: 'high', fillColor: '#fff' });
        if (!canvas) {
            error.textContent = 'Could not crop this image. Please try again.';
            error.hidden = false;
            saving = false;
            saveButton.disabled = false;
            saveButton.textContent = 'Save';
            return;
        }

        const toBlob = quality => new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', quality));
        let blob = await toBlob(0.86);
        if (blob && blob.size > 2 * 1024 * 1024) blob = await toBlob(0.68);
        if (!blob || blob.size > 2 * 1024 * 1024) {
            error.textContent = 'The cropped image must be under 2 MB. Adjust the crop and try again.';
            error.hidden = false;
            saving = false;
            saveButton.disabled = false;
            saveButton.textContent = 'Save';
            return;
        }

        const originalName = input.files[0].name.replace(/\.[^.]+$/, '') || 'page-banner';
        const transfer = new DataTransfer();
        transfer.items.add(new File([blob], `${originalName}-cropped.jpg`, { type: 'image/jpeg', lastModified: Date.now() }));
        input.files = transfer.files;
        cropApplied = true;
        destroyCropper();
        editor.hidden = true;
        status.textContent = 'Crop complete. Saving banner…';
        status.hidden = false;
        form.submit();
    });
})();
