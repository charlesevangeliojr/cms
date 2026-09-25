@extends('backend.layouts.sidebar')

@section('title', 'My Profile')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" referrerpolicy="no-referrer" />
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">My Profile</h2>
            <p class="text-sm text-gray-500">Update your name, contact, avatar and password.</p>
        </div>
        <a href="{{ route('users.index') }}" class="hidden sm:inline-flex items-center justify-center gap-2 h-10 rounded-xl border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Back to Users</a>
    </div>


    @if ($errors->any())
        <x-alert type="error">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <form id="profile-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:items-stretch items-start" data-turbo="false">
        @csrf
        @method('PUT')

        {{-- Avatar Card --}}
        <div class="lg:col-span-1 flex">
            <div class="flex flex-col h-full w-full rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 shrink-0">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-gray-700">Profile picture</h3>
                    <p class="text-xs text-gray-500 mt-1">Square 1:1 — drag to choose, 512×512</p>
                </div>
                <div class="p-6 space-y-4 flex flex-col flex-1 justify-between">
                    <div class="flex flex-col items-center gap-4">
                        @if($user->avatar_url)
                            <img id="avatar-preview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-32 w-32 rounded-full object-cover border border-gray-200 shadow-sm">
                        @else
                            <div id="avatar-preview" class="h-32 w-32 rounded-full bg-indigo-600 flex items-center justify-center text-3xl font-bold text-white">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <img id="avatar-preview-img" src="" alt="" class="hidden h-32 w-32 rounded-full object-cover border border-gray-200 shadow-sm">
                        @endif
                        <div class="text-center">
                            <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->role }}</p>
                            <p class="text-xs text-gray-400">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div>
                        <label for="avatar" class="mb-1.5 block text-sm font-semibold text-gray-900">New avatar <span class="font-normal text-gray-500">1:1 editable</span></label>
                        <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp"
                               class="block w-full cursor-pointer rounded-xl border border-gray-300 bg-gray-50 text-sm text-gray-600 file:mr-4 file:cursor-pointer file:border-0 file:bg-gray-200 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-gray-700 hover:file:bg-gray-100">
                        <p class="mt-1.5 text-xs text-gray-400">JPG, PNG, WebP up to 2 MB. After selecting, drag/zoom to choose 1:1 crop — circular preview.</p>
                        <div id="avatarCropperModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
                            <div class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm" onclick="closeAvatarCropper()"></div>
                            <div class="relative w-full max-w-lg rounded-xl bg-white shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                                <div class="flex items-center justify-between bg-indigo-600 px-4 py-3 shrink-0">
                                    <h3 class="text-sm font-semibold text-white">Adjust crop — drag/zoom (1:1 locked)</h3>
                                    <div class="flex items-center gap-2">
                                        <button type="button" id="avatar-cropper-reset" class="rounded bg-white/20 px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/30">Reset</button>
                                        <button type="button" onclick="closeAvatarCropper()" class="rounded bg-white/20 p-1.5 text-white hover:bg-white/30">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex-1 overflow-hidden bg-gray-900 flex items-center justify-center p-4">
                                    <img id="avatar-cropper-image" alt="Crop" class="max-w-full max-h-[60vh] block">
                                </div>
                                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between shrink-0">
                                    <p class="text-xs text-gray-500">Drag to reposition • Scroll to zoom • 512×512 output</p>
                                    <div class="flex gap-2">
                                        <button type="button" onclick="closeAvatarCropper()" class="h-10 rounded-xl border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
                                        <button type="button" onclick="closeAvatarCropper()" class="h-10 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white hover:bg-indigo-500">Done</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($user->avatar_url)
                            <div class="mt-3 flex items-center gap-2">
                                <span class="text-xs text-gray-500">Current file: {{ basename($user->avatar_path) }}</span>
                                <button type="submit" form="delete-avatar-form" class="rounded-xl border border-rose-200 bg-white px-3 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50">Remove</button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Details Card --}}
        <div class="lg:col-span-2 flex">
            <div class="flex flex-col h-full w-full rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between shrink-0">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-gray-700">Account details</h3>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 border border-gray-200 px-3 py-1 text-xs font-semibold text-gray-600">{{ $user->role }}</span>
                </div>
                <div class="p-6 space-y-5 flex flex-col flex-1">
                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-semibold text-gray-900">Full Name *</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255"
                               class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-semibold text-gray-900">Email Address *</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255"
                               class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    @include('backend.partials.account-contact')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="password" class="mb-1.5 block text-sm font-semibold text-gray-900">New Password <span class="font-normal text-gray-400">(leave blank to keep)</span></label>
                            <input id="password" name="password" type="password" placeholder="••••••••"
                                   class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-gray-900">Confirm Password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="••••••••"
                                   class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2 mt-auto">
                        <a href="{{ route('users.index') }}" class="inline-flex items-center justify-center gap-2 h-10 rounded-xl border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Cancel</a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 h-10 rounded-xl bg-indigo-600 px-6 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if($user->avatar_url)
        <form id="delete-avatar-form" data-confirm="Remove your avatar? This cannot be undone." method="POST" action="{{ route('profile.avatar.destroy') }}" data-turbo="false" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" referrerpolicy="no-referrer"></script>
<script>
function closeAvatarCropper(){
    document.getElementById('avatarCropperModal')?.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}
(function(){
    const input = document.getElementById('avatar');
    const modal = document.getElementById('avatarCropperModal');
    const cropImg = document.getElementById('avatar-cropper-image');
    const resetBtn = document.getElementById('avatar-cropper-reset');
    const form = document.getElementById('profile-form');
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
        if(!file || !file.type.startsWith('image/')) return;
        originalFile = file;
        destroyCropper();
        objectUrl = URL.createObjectURL(file);
        cropImg.src = objectUrl;
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        let preview = document.getElementById('avatar-preview');
        if(preview && preview.tagName !== 'IMG'){
            const img = document.getElementById('avatar-preview-img');
            if(img){ img.src = objectUrl; img.classList.remove('hidden'); preview.classList.add('hidden'); }
        } else if(preview){ preview.src = objectUrl; }

        cropImg.onload = function(){
            cropper = new Cropper(cropImg, {
                aspectRatio: 1,
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
        };
    });

    resetBtn?.addEventListener('click', function(){ if(cropper) cropper.reset(); });

    document.addEventListener('keydown', (e) => { if(e.key === 'Escape') closeAvatarCropper(); });

    form?.addEventListener('submit', function(e){
        if(isSubmitting) return;
        if(!cropper || !originalFile) return;
        if(cropper.getImageData().naturalWidth < 10 || cropper.getImageData().naturalHeight < 10) return;
        e.preventDefault();
        const canvas = cropper.getCroppedCanvas({ width: 512, height: 512, imageSmoothingQuality: 'high', fillColor: '#fff' });
        if(!canvas){ form.submit(); return; }
        const mime = originalFile.type || 'image/jpeg';
        canvas.toBlob(function(blob){
            if(!blob){ form.submit(); return; }
            const ext = mime === 'image/png' ? 'png' : (mime === 'image/webp' ? 'webp' : 'jpg');
            const croppedFile = new File([blob], originalFile.name.replace(/\.[^.]+$/, '') + '.' + ext, {type: mime, lastModified: Date.now()});
            const dt = new DataTransfer();
            dt.items.add(croppedFile);
            input.files = dt.files;
            isSubmitting = true;
            form.submit();
        }, mime, 0.92);
    });
})();
</script>
@endsection
