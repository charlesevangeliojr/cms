<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class BannerController extends Controller
{
    /**
     * Display a listing of banners from the database.
     */
    public function index(Request $request)
    {
        $searchValue = $request->query('q');
        $search = is_string($searchValue) ? trim($searchValue) : '';
        $statusValue = $request->query('status');
        $status = in_array($statusValue, ['active', 'inactive'], true) ? $statusValue : null;

        $banners = Banner::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('backend.banners.index', [
            'banners' => $banners,
            'totalBanners' => Banner::count(),
            'activeBanners' => Banner::where('is_active', true)->count(),
            'inactiveBanners' => Banner::where('is_active', false)->count(),
            'search' => $search,
            'status' => $status,
        ]);
    }

    /**
     * Show the form for creating a banner.
     */
    public function create()
    {
        return view('backend.banners.create');
    }

    /**
     * Store a banner in the database.
     */
    public function store(Request $request)
    {
        $validated = $this->validateBanner($request);
        $image = $validated['image'];
        unset($validated['image']);
        $imagePath = $this->storeImage($image);

        try {
            Banner::create([
                ...$validated,
                'image_path' => $imagePath,
                'is_active' => $request->boolean('is_active'),
            ]);
        } catch (Throwable $exception) {
            $this->deleteImage($imagePath);

            throw $exception;
        }

        return redirect()
            ->route('banners.index')
            ->with('success', 'Banner created successfully.');
    }

    /**
     * Show the form for editing a banner.
     */
    public function edit(Banner $banner)
    {
        return view('backend.banners.edit', [
            'banner' => $banner,
        ]);
    }

    /**
     * Update a banner in the database.
     */
    public function update(Request $request, Banner $banner)
    {
        $validated = $this->validateBanner($request, $banner);
        unset($validated['image']);
        $oldImagePath = $banner->image_path;
        $newImagePath = $oldImagePath;

        if ($request->hasFile('image')) {
            $newImagePath = $this->storeImage($request->file('image'));
        }

        try {
            $banner->update([
                ...$validated,
                'image_path' => $newImagePath,
                'is_active' => $request->boolean('is_active'),
            ]);
        } catch (Throwable $exception) {
            if ($newImagePath !== $oldImagePath) {
                $this->deleteImage($newImagePath);
            }

            throw $exception;
        }

        if ($newImagePath !== $oldImagePath) {
            $this->deleteImage($oldImagePath);
        }

        return redirect()
            ->route('banners.index')
            ->with('success', 'Banner updated successfully.');
    }

    /**
     * Delete a banner from the database.
     */
    public function destroy(Banner $banner)
    {
        $imagePath = $banner->image_path;
        $banner->delete();
        $this->deleteImage($imagePath);

        return redirect()
            ->route('banners.index')
            ->with('success', 'Banner deleted successfully.');
    }

    /**
     * Validate banner data submitted by the admin.
     *
     * @return array<string, mixed>
     */
    private function validateBanner(Request $request, ?Banner $banner = null): array
    {
        $imageRules = $banner
            ? ['nullable']
            : ['required'];

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'image' => [...$imageRules, 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
    }

    /**
     * Persist a validated banner image and return its relative path.
     * Automatically center-crops to 16:6 ratio.
     */
    private function storeImage(UploadedFile $image): string
    {
        $extension = strtolower($image->guessExtension() ?: 'jpg');
        $filename = Str::uuid()->toString().'.'.$extension;
        $disk = Storage::disk('banners');

        $croppedTempPath = $this->cropToSixteenSix($image->getPathname(), $extension);

        if ($croppedTempPath !== null && file_exists($croppedTempPath)) {
            $stored = $disk->put($filename, file_get_contents($croppedTempPath));
            @unlink($croppedTempPath);

            if ($stored === false) {
                throw new RuntimeException('The banner image could not be stored.');
            }

            return $filename;
        }

        $path = $disk->putFileAs('', $image, $filename);

        if ($path === false) {
            throw new RuntimeException('The banner image could not be stored.');
        }

        return $path;
    }

    /**
     * Center-crop an image file to 16:6 ratio. Returns temp file path or null on failure.
     */
    private function cropToSixteenSix(string $sourcePath, string $extension): ?string
    {
        if (! function_exists('imagecrop') || ! file_exists($sourcePath)) {
            return null;
        }

        $extension = strtolower($extension);
        $src = match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($sourcePath),
            'png' => @imagecreatefrompng($sourcePath),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : null,
            default => @imagecreatefromstring((string) file_get_contents($sourcePath)),
        };

        if (! $src) {
            // Fallback via string
            $data = @file_get_contents($sourcePath);
            $src = $data !== false ? @imagecreatefromstring($data) : null;
            if (! $src) {
                return null;
            }
        }

        $width = imagesx($src);
        $height = imagesy($src);

        if ($width <= 0 || $height <= 0) {
            imagedestroy($src);
            return null;
        }

        $targetRatio = 16 / 6;
        $currentRatio = $width / $height;

        // Already close enough to 16:6, keep as is but still ensure ratio
        if (abs($currentRatio - $targetRatio) < 0.005) {
            // Optionally downscale if too large
            $cropped = $src;
            $cropW = $width;
            $cropH = $height;
        } else {
            if ($currentRatio > $targetRatio) {
                // Wider than 16:6 -> crop width
                $cropW = (int) round($height * $targetRatio);
                $cropH = $height;
                $x = (int) floor(($width - $cropW) / 2);
                $y = 0;
            } else {
                // Taller than 16:6 -> crop height
                $cropW = $width;
                $cropH = (int) round($width / $targetRatio);
                $x = 0;
                $y = (int) floor(($height - $cropH) / 2);
            }

            // Guard tiny images where computed crop exceeds source or becomes zero
            if ($cropW <= 0 || $cropH <= 0 || $cropW > $width || $cropH > $height || $x < 0 || $y < 0) {
                imagedestroy($src);
                return null;
            }

            $cropped = @imagecrop($src, ['x' => $x, 'y' => $y, 'width' => $cropW, 'height' => $cropH]);

            if (! $cropped) {
                imagedestroy($src);
                return null;
            }

            imagedestroy($src);
        }

        // Downscale to max 1600x600 if larger (preserves 16:6)
        $finalW = imagesx($cropped);
        $finalH = imagesy($cropped);
        $maxW = 1600;
        $maxH = 600;

        if ($finalW > $maxW || $finalH > $maxH) {
            $dst = imagecreatetruecolor($maxW, $maxH);

            // Preserve transparency for PNG/WebP
            if (in_array($extension, ['png', 'webp'], true)) {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefilledrectangle($dst, 0, 0, $maxW, $maxH, $transparent);
            }

            imagecopyresampled($dst, $cropped, 0, 0, 0, 0, $maxW, $maxH, $finalW, $finalH);
            imagedestroy($cropped);
            $cropped = $dst;
        }

        $tmpPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.Str::uuid()->toString().'.'.$extension;

        $saved = match ($extension) {
            'jpg', 'jpeg' => @imagejpeg($cropped, $tmpPath, 90),
            'png' => @imagepng($cropped, $tmpPath),
            'webp' => function_exists('imagewebp') ? @imagewebp($cropped, $tmpPath, 90) : @imagejpeg($cropped, $tmpPath, 90),
            default => @imagejpeg($cropped, $tmpPath, 90),
        };

        imagedestroy($cropped);

        if (! $saved || ! file_exists($tmpPath)) {
            return null;
        }

        return $tmpPath;
    }

    /**
     * Delete a banner image when it is managed by the banner disk.
     */
    private function deleteImage(?string $imagePath): void
    {
        if (! $imagePath || str_contains($imagePath, '..')) {
            return;
        }

        Storage::disk('banners')->delete($imagePath);
    }
}
