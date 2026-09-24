<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
        $status = in_array($statusValue, ['active', 'inactive'], true)
            ? $statusValue
            : null;

        $banners = Banner::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhere('target_url', 'like', "%{$search}%");
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
        return view('backend.banners.create', [
            'positions' => Banner::POSITIONS,
        ]);
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
            'positions' => Banner::POSITIONS,
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
            'image' => [...$imageRules, 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'position' => ['required', 'string', Rule::in(Banner::POSITIONS)],
            'target_url' => [
                'required',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $isInternalUrl = is_string($value)
                        && str_starts_with($value, '/')
                        && ! str_starts_with($value, '//');
                    $parsedUrl = is_string($value) ? parse_url($value) : false;
                    $isHttpUrl = is_array($parsedUrl)
                        && in_array(strtolower($parsedUrl['scheme'] ?? ''), ['http', 'https'], true)
                        && filled($parsedUrl['host'] ?? '');

                    if (! $isInternalUrl && ! $isHttpUrl) {
                        $fail('The target URL must be an internal path or an absolute HTTP/HTTPS URL.');
                    }
                },
            ],
        ]);
    }

    /**
     * Persist a validated banner image and return its relative path.
     */
    private function storeImage(UploadedFile $image): string
    {
        $extension = strtolower($image->guessExtension() ?: 'jpg');
        $filename = Str::uuid()->toString().'.'.$extension;
        $disk = Storage::disk('banners');
        $path = $disk->putFileAs('', $image, $filename);

        if ($path === false) {
            throw new RuntimeException('The banner image could not be stored.');
        }

        return $path;
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
