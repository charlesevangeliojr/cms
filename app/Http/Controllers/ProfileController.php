<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;
use RuntimeException;

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
            'contact' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->contact = $validated['contact'] ?? null;

        if ($request->boolean('remove_avatar') && $user->avatar_path) {
            Storage::disk('avatars')->delete($user->avatar_path);
            $user->avatar_path = null;
        } elseif ($request->hasFile('avatar')) {
            $oldAvatar = $user->avatar_path;
            $newPath = $this->storeAvatar($request->file('avatar'));
            $user->avatar_path = $newPath;
            if ($oldAvatar) {
                Storage::disk('avatars')->delete($oldAvatar);
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
            Storage::disk('avatars')->delete($user->avatar_path);
            $user->avatar_path = null;
            $user->save();
        }

        return redirect()->route('profile.edit')->with('success', 'Avatar removed.');
    }

    private function storeAvatar(UploadedFile $file): string
    {
        $extension = strtolower($file->guessExtension() ?: 'jpg');
        $filename = Str::uuid()->toString().'.'.$extension;

        $cropped = $this->cropToSquare($file->getPathname(), $extension);
        if ($cropped && file_exists($cropped)) {
            $stored = Storage::disk('avatars')->put($filename, file_get_contents($cropped));
            @unlink($cropped);
            if ($stored) {
                return $filename;
            }
        }

        $path = Storage::disk('avatars')->putFileAs('', $file, $filename);
        if ($path === false) {
            throw new RuntimeException('Avatar could not be stored.');
        }
        return $path;
    }

    private function cropToSquare(string $sourcePath, string $extension): ?string
    {
        if (! function_exists('imagecrop') || ! file_exists($sourcePath)) {
            return null;
        }
        $extension = strtolower($extension);
        $src = match ($extension) {
            'jpg','jpeg' => @imagecreatefromjpeg($sourcePath),
            'png' => @imagecreatefrompng($sourcePath),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : null,
            default => @imagecreatefromstring((string) file_get_contents($sourcePath)),
        };
        if (! $src) {
            $data = @file_get_contents($sourcePath);
            $src = $data !== false ? @imagecreatefromstring($data) : null;
            if (! $src) return null;
        }
        $w = imagesx($src);
        $h = imagesy($src);
        if ($w <= 0 || $h <= 0) { imagedestroy($src); return null; }

        $size = min($w, $h);
        $x = (int) floor(($w - $size) / 2);
        $y = (int) floor(($h - $size) / 2);

        $cropped = @imagecrop($src, ['x'=>$x,'y'=>$y,'width'=>$size,'height'=>$size]);
        if (!$cropped) { imagedestroy($src); return null; }
        imagedestroy($src);

        // Resize to 512x512 if larger
        $max = 512;
        if ($size > $max) {
            $dst = imagecreatetruecolor($max, $max);
            if (in_array($extension, ['png','webp'], true)) {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $transparent = imagecolorallocatealpha($dst, 0,0,0,127);
                imagefilledrectangle($dst,0,0,$max,$max,$transparent);
            }
            imagecopyresampled($dst, $cropped, 0,0,0,0,$max,$max,$size,$size);
            imagedestroy($cropped);
            $cropped = $dst;
        }

        $tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.Str::uuid()->toString().'.'.$extension;
        $saved = match($extension) {
            'jpg','jpeg' => @imagejpeg($cropped, $tmp, 90),
            'png' => @imagepng($cropped, $tmp),
            'webp' => function_exists('imagewebp') ? @imagewebp($cropped,$tmp,90) : @imagejpeg($cropped,$tmp,90),
            default => @imagejpeg($cropped,$tmp,90),
        };
        imagedestroy($cropped);
        return ($saved && file_exists($tmp)) ? $tmp : null;
    }
}
