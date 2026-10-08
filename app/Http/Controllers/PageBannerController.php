<?php

namespace App\Http\Controllers;

use App\Models\BannerPage;
use App\Models\PageBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageBannerController extends Controller
{
    public function index(Request $request)
    {
        $searchValue = $request->query('q');
        $search = is_string($searchValue) ? trim($searchValue) : '';
        $status = in_array($request->query('status'), ['active', 'inactive'], true) ? $request->query('status') : null;
        $target = is_string($request->query('target')) && in_array($request->query('target'), ['about', 'blogs', 'contact'], true) ? $request->query('target') : null;

        $banners = PageBanner::with('page')
            ->when($search !== '', fn ($query) => $query->whereHas('page', fn ($query) => $query->where('name', 'like', "%{$search}%")))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($target, fn ($query) => $query->whereHas('page', fn ($query) => $query->where('slug', $target)))
            ->latest('id')->paginate(10)->withQueryString();

        return view('backend.banners.page-banners.index', [
            'banners' => $banners,
            'search' => $search,
            'status' => $status,
            'target' => $target,
            'pageBannerPages' => BannerPage::whereIn('slug', ['about', 'blogs', 'contact'])->orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return view('backend.banners.page-banners.form', ['banner' => new PageBanner, 'pageBannerPages' => BannerPage::whereIn('slug', ['about', 'blogs', 'contact'])->orderBy('id')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateBanner($request);
        $image = $data['image'];
        unset($data['image']);
        $data['image_path'] = $this->uploadImage($image);
        $data['is_active'] = $request->boolean('is_active');
        PageBanner::create($data);

        return redirect()->route('page-banners.index')->with('success', 'Page banner created successfully.');
    }

    public function edit(PageBanner $pageBanner)
    {
        return view('backend.banners.page-banners.form', ['banner' => $pageBanner, 'pageBannerPages' => BannerPage::whereIn('slug', ['about', 'blogs', 'contact'])->orderBy('id')->get()]);
    }

    public function update(Request $request, PageBanner $pageBanner)
    {
        $data = $this->validateBanner($request, $pageBanner);
        $image = $data['image'] ?? null;
        unset($data['image']);
        $oldPath = $pageBanner->image_path;
        if ($image) $data['image_path'] = $this->uploadImage($image);
        $data['is_active'] = $request->boolean('is_active');
        $pageBanner->update($data);
        if ($image) $this->deleteImage($oldPath);

        return redirect()->route('page-banners.index')->with('success', 'Page banner updated successfully.');
    }

    public function destroy(PageBanner $pageBanner)
    {
        $path = $pageBanner->image_path;
        $pageBanner->delete();
        $this->deleteImage($path);

        return redirect()->route('page-banners.index')->with('success', 'Page banner deleted successfully.');
    }

    private function validateBanner(Request $request, ?PageBanner $banner = null): array
    {
        return $request->validate([
            'banner_page_id' => ['required', 'integer', Rule::exists('banner_pages', 'id')->whereIn('slug', ['about', 'blogs', 'contact'])],
            'image' => [$banner ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function uploadImage(\Illuminate\Http\UploadedFile $image): string
    {
        $directory = public_path('uploads/page-banners');
        File::ensureDirectoryExists($directory);
        $filename = Str::uuid().'.'.$image->extension();
        $image->move($directory, $filename);

        return 'uploads/page-banners/'.$filename;
    }

    private function deleteImage(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/page-banners/') && ! str_contains($path, '..')) {
            File::delete(public_path($path));
        }
    }
}
