@php($editing = isset($banner) && $banner->exists)

@if ($errors->any())
    <x-alert type="error">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" referrerpolicy="no-referrer" />

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-700">Banner Details</h3>
        </div>
        <div class="space-y-5 p-6">
            <div>
                <label for="title" class="mb-1.5 block text-sm font-semibold text-gray-900">Title *</label>
                <input id="title" name="title" type="text" value="{{ old('title', $banner->title ?? '') }}" required maxlength="255"
                       placeholder="e.g. Autumn Promotional Hero Banner"
                       class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label for="description" class="mb-1.5 block text-sm font-semibold text-gray-900">Body / Description *</label>
                <textarea id="description" name="description" rows="6" required maxlength="5000"
                          placeholder="Write the banner body or description..."
                          class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('description', $banner->description ?? '') }}</textarea>
                <p class="mt-1.5 text-xs text-gray-400">Max 5000 characters.</p>
            </div>

            <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-4">
                <div>
                    <p class="text-sm font-semibold text-gray-900">Active Banner</p>
                    <p class="mt-0.5 text-xs text-gray-500">Inactive banners are hidden from the public site.</p>
                </div>
                <label class="inline-flex cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" aria-label="Active banner" name="is_active" value="1" class="peer sr-only"
                           @checked(old('is_active', $banner->is_active ?? true))>
                    <span class="flex h-6 w-6 items-center justify-center rounded bg-gray-200 text-white transition peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500 peer-focus-visible:ring-offset-2 peer-checked:bg-emerald-600 peer-checked:[&_svg]:opacity-100">
                        <svg class="h-4 w-4 opacity-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </span>
                </label>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-700">Banner Image — 16:6</h3>
        </div>
        <div class="space-y-5 p-6">
            @if ($editing && $banner->image_path)
                <div>
                    <p class="mb-2 text-sm font-semibold text-gray-900">Current image — 16:6</p>
                    @if($banner->image_url)
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}"
                             class="aspect-[16/6] w-full rounded-xl border border-gray-200 object-cover shadow-sm" onerror="this.onerror=null;this.src='https://via.placeholder.com/640x240?text=No+Image';">
                    @else
                        <div class="aspect-[16/6] w-full rounded-xl border border-gray-200 bg-gray-100 flex items-center justify-center text-xs text-gray-400">No image</div>
                    @endif
                </div>
            @endif

            <div>
                <label for="image" class="mb-1.5 block text-sm font-semibold text-gray-900">
                    {{ $editing ? 'Replacement image' : 'Banner image *' }} <span class="font-normal text-gray-500">16:6 editable crop</span>
                </label>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" @required(! $editing)
                       class="block w-full cursor-pointer rounded-xl border border-gray-300 bg-gray-50 text-sm text-gray-600 file:mr-4 file:cursor-pointer file:border-0 file:bg-gray-200 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-gray-700 hover:file:bg-gray-100">
                <p class="mt-1.5 text-xs text-gray-400">JPG, PNG, or WebP up to 2 MB. After selecting, drag/zoom to choose what part is kept — locked to 16:6. Output is 1600×600.{{ $editing ? ' Leave empty to keep the current image.' : '' }}</p>

                <div id="cropper-wrap" class="hidden mt-4 overflow-hidden rounded-xl border border-indigo-200 bg-gray-900 shadow-sm">
                    <div class="flex items-center justify-between bg-indigo-600 px-3 py-2">
                        <p class="text-xs font-semibold text-white">Adjust crop — drag to move, scroll/pinch to zoom, drag handles to resize (16:6 locked)</p>
                        <button type="button" id="cropper-reset" class="rounded-xl bg-white/20 px-2 py-1 text-xs font-semibold text-white hover:bg-white/30">Reset</button>
                    </div>
                    <div class="max-h-[360px] overflow-hidden bg-gray-900">
                        <img id="cropper-image" alt="Crop" class="max-w-full block">
                    </div>
                    <p class="bg-gray-50 px-3 py-2 text-xs text-gray-500">Preview below is exactly 16:6 — what you see is what will be uploaded.</p>
                </div>

                <div id="image-preview-wrap" class="hidden mt-3">
                    <p class="mb-1 text-xs font-semibold text-gray-600">Fallback preview — 16:6</p>
                    <img id="image-preview" alt="Preview" class="aspect-[16/6] w-full rounded-xl border border-gray-200 object-cover shadow-sm">
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" referrerpolicy="no-referrer"></script>
<script>
(function(){
    const input = document.getElementById('image');
    const wrap = document.getElementById('cropper-wrap');
    const cropImg = document.getElementById('cropper-image');
    const resetBtn = document.getElementById('cropper-reset');
    const fallbackWrap = document.getElementById('image-preview-wrap');
    const fallbackImg = document.getElementById('image-preview');
    let cropper = null;
    let originalFile = null;
    let objectUrl = null;
    let isSubmitting = false;

    function destroyCropper(){
        if(cropper){ try{ cropper.destroy(); }catch(e){} cropper=null; }
        if(objectUrl){ URL.revokeObjectURL(objectUrl); objectUrl=null; }
    }

    input?.addEventListener('change', function(e){
        const file = e.target.files && e.target.files[0];
        if(!file){
            wrap?.classList.add('hidden');
            fallbackWrap?.classList.add('hidden');
            destroyCropper();
            originalFile=null;
            return;
        }
        if(!file.type.startsWith('image/')){
            return;
        }
        originalFile = file;
        destroyCropper();
        objectUrl = URL.createObjectURL(file);
        cropImg.src = objectUrl;
        wrap.classList.remove('hidden');
        fallbackWrap.classList.add('hidden');

        cropImg.onload = function(){
            // Init cropper after image loaded
            cropper = new Cropper(cropImg, {
                aspectRatio: 16/6,
                viewMode: 1,
                autoCropArea: 1,
                movable: true,
                zoomable: true,
                rotatable: false,
                scalable: false,
                responsive: true,
                background: false,
                highlight: true,
                guides: true,
                center: true,
            });
            // Also show fallback preview for comparison (optional)
            if(fallbackImg){
                fallbackImg.src = objectUrl;
            }
        };
    });

    resetBtn?.addEventListener('click', function(){
        if(cropper){ cropper.reset(); }
    });

    // Intercept form submit to inject cropped file
    const form = input ? input.closest('form') : null;
    if(form){
        form.addEventListener('submit', function(e){
            if(isSubmitting) return; // allow second submit
            if(!cropper || !originalFile){
                return; // no crop needed (e.g. edit without new image) or backend auto-crop fallback
            }
            // If file is tiny (e.g. test 1x1) cropper may not be useful - let backend handle
            if(cropper.getImageData().naturalWidth < 16 || cropper.getImageData().naturalHeight < 6){
                return;
            }
            e.preventDefault();
            const canvas = cropper.getCroppedCanvas({
                width: 1600,
                height: 600,
                imageSmoothingQuality: 'high',
                fillColor: '#fff'
            });
            if(!canvas){
                form.submit();
                return;
            }
            const mime = originalFile.type || 'image/jpeg';
            canvas.toBlob(function(blob){
                if(!blob){
                    form.submit();
                    return;
                }
                const ext = mime === 'image/png' ? 'png' : (mime === 'image/webp' ? 'webp' : 'jpg');
                const croppedFile = new File([blob], originalFile.name.replace(/\.[^.]+$/, '') + '.' + ext, {type: mime, lastModified: Date.now()});
                const dt = new DataTransfer();
                dt.items.add(croppedFile);
                input.files = dt.files;
                isSubmitting = true;
                // Clean up cropper before submit to avoid re-intercept
                // Don't destroy objectUrl yet - file already replaced
                form.submit();
            }, mime, 0.92);
        });
    }
})();
</script>
