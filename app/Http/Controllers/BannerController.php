<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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
        $imagePath = $this->uploadImage($image);

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
            $newImagePath = $this->uploadImage($request->file('image'));
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

    private function uploadImage(UploadedFile $image): string
    {
        $directory = public_path('uploads/banners');

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = 'banner_'.time().'_'.Str::random(10).'.'.strtolower($image->getClientOriginalExtension() ?: ($image->guessExtension() ?: 'jpg'));

        $image->move($directory, $filename);

        return 'uploads/banners/'.$filename;
    }

    /**
     * Delete a banner image from public/uploads/banners.
     * Accepts both legacy filenames and uploads/banners/... paths.
     */
    private function deleteImage(?string $imagePath): void
    {
        if (! $imagePath || str_contains($imagePath, '..')) {
            return;
        }

        $fullPath = public_path('uploads/banners').DIRECTORY_SEPARATOR.basename($imagePath);

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
