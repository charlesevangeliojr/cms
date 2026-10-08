<?php

namespace App\Http\Controllers;

use App\Models\HomeBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HomeBannerController extends Controller
{
    public function index()
    {
        $banner = HomeBanner::with('images')->first() ?? HomeBanner::create([
            'title' => 'Welcome',
            'description' => 'Discover more and stay connected.',
            'is_active' => true,
        ]);
        $banner->load('images');

        return view('backend.banners.home-banners.index', compact('banner'));
    }

    public function update(Request $request)
    {
        $banner = HomeBanner::firstOrFail();
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        $banner->update([
            'title' => $data['title'],
            'description' => $data['description'],
        ]);

        return redirect()->route('home-banners.index')->with('success', 'Home banner details updated.');
    }

    public function updateVisibility(Request $request)
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $banner = HomeBanner::firstOrFail();
        $banner->update(['is_active' => (bool) $data['is_active']]);

        return response()->json([
            'success' => true,
            'is_active' => $banner->is_active,
            'message' => $banner->is_active ? 'Home Banner is now visible.' : 'Home Banner is now hidden.',
        ]);
    }

    public function storeImages(Request $request)
    {
        $banner = HomeBanner::firstOrFail();
        $data = $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $directory = public_path('uploads/home-banners');
        File::ensureDirectoryExists($directory);
        $position = ((int) $banner->images()->max('position')) + 1;

        foreach ($data['images'] as $image) {
            $filename = Str::uuid().'.'.$image->extension();
            $image->move($directory, $filename);
            $banner->images()->create([
                'image_path' => 'uploads/home-banners/'.$filename,
                'is_active' => true,
                'position' => $position++,
            ]);
        }

        if ($request->expectsJson()) {
            session()->flash('success', 'Home banner images added.');
            return response()->json(['success' => true, 'added' => count($data['images'])]);
        }

        return redirect()->route('home-banners.index')->with('success', 'Home banner images added.');
    }

    public function destroyImage(\App\Models\HomeBannerImage $image)
    {
        $path = $image->image_path;
        $image->delete();
        $this->deleteImage($path);

        return redirect()->route('home-banners.index')->with('success', 'Home banner image removed.');
    }

    public function updateImageStatus(Request $request, \App\Models\HomeBannerImage $image)
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);
        $image->update(['is_active' => (bool) $data['is_active']]);
        $message = $image->is_active ? 'Home banner image is now shown.' : 'Home banner image is now hidden.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'is_active' => $image->is_active, 'message' => $message]);
        }

        return redirect()->route('home-banners.index')->with('success', $message);
    }

    public function reorderImages(Request $request)
    {
        $banner = HomeBanner::firstOrFail();
        $data = $request->validate([
            'image_ids' => ['required', 'array', 'size:'.$banner->images()->count()],
            'image_ids.*' => ['required', 'integer', 'distinct', 'exists:home_banner_images,id'],
        ]);
        $validIds = $banner->images()->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $submittedIds = collect($data['image_ids'])->map(fn ($id) => (int) $id)->sort()->values()->all();
        if ($validIds !== $submittedIds) {
            return response()->json(['message' => 'The image list changed. Refresh and try again.'], 422);
        }

        foreach ($data['image_ids'] as $position => $id) {
            $banner->images()->whereKey($id)->update(['position' => $position]);
        }

        return response()->json(['success' => true, 'message' => 'Home banner slide order updated.']);
    }

    private function deleteImage(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/home-banners/') && ! str_contains($path, '..')) {
            File::delete(public_path($path));
        }
    }
}
