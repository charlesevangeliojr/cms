<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        return view('backend.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'contact' => ['nullable', 'string', 'max:20', Rule::when($request->filled('contact_country'), ['regex:/^\\d{1,10}$/'])],
            'contact_country' => ['nullable', 'string', 'max:5'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->contact = $validated['contact'] ?? null;
        $user->contact_country = $validated['contact_country'] ?? null;

        if ($request->boolean('remove_avatar') && $user->avatar_path) {
            $this->deleteAvatar($user->avatar_path);
            $user->avatar_path = null;
        } elseif ($request->hasFile('avatar')) {
            $oldAvatar = $user->avatar_path;
            $newPath = $this->uploadImage($request->file('avatar'));
            $user->avatar_path = $newPath;
            if ($oldAvatar) {
                $this->deleteAvatar($oldAvatar);
            }
        }

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }

    public function destroyAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar_path) {
            $this->deleteAvatar($user->avatar_path);
            $user->avatar_path = null;
            $user->save();
        }

        return redirect()->route('profile.edit')->with('success', 'Avatar removed.');
    }

    private function uploadImage(UploadedFile $image): string
    {
        $directory = public_path('uploads/avatars');

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = 'avatar_'.time().'_'.Str::random(10).'.'.strtolower($image->getClientOriginalExtension() ?: ($image->guessExtension() ?: 'jpg'));

        $image->move($directory, $filename);

        return 'uploads/avatars/'.$filename;
    }

    private function deleteAvatar(?string $avatarPath): void
    {
        if (! $avatarPath || str_contains($avatarPath, '..')) {
            return;
        }

        $fullPath = public_path('uploads/avatars').DIRECTORY_SEPARATOR.basename($avatarPath);

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
