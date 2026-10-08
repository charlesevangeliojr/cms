<?php

namespace App\Http\Controllers;

use App\Models\HomeBanner;
use App\Models\PageBanner;

class PageController extends Controller
{
    /**
     * Mock data for the static frontend pages.
     * Later this can be swapped for real Eloquent queries
     * without touching the blade files.
     */
    private function mockData(): array
    {
        return [
            'site' => [
                'name' => 'CMS Template',
                'tagline' => '',
            ],
            'nav' => [
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'About', 'url' => '/about'],
                ['label' => 'Blog', 'url' => '/blogs'],
            ],
            'home' => [
                'heading' => 'Home',
                'text' => 'Welcome to the CMS Template.',
                'cta' => ['label' => 'Go to About', 'url' => '/about'],
                'features' => [
                    ['title' => 'Fast navigation', 'text' => 'Turbo Drive swaps pages with no full reload.'],
                    ['title' => 'Tailwind styling', 'text' => 'Utility-first classes, dark-mode ready.'],
                    ['title' => 'Mock driven', 'text' => 'This data comes from the controller, easy to swap for a model later.'],
                ],
            ],
            'about' => [
                'heading' => 'About',
                'text' => 'Turbo Drive intercepted this navigation — no full page reload.',
                'cta' => ['label' => 'Back to Home', 'url' => '/'],
                'stats' => [
                    ['value' => '11.x', 'label' => 'Laravel'],
                    ['value' => '8.3', 'label' => 'PHP'],
                    ['value' => 'MySQL', 'label' => 'Database'],
                ],
            ],
        ];
    }

    public function home()
    {
        $data = $this->mockData();
        $homeBanner = HomeBanner::where('is_active', true)->with(['images' => fn ($query) => $query->where('is_active', true)->with('banner')])->first();
        $banners = $homeBanner?->images ?? collect();

        return view('frontend.pages.home', [
            'site' => $data['site'],
            'nav' => $data['nav'],
            'page' => $data['home'],
            'banners' => $banners,
        ]);
    }

    public function about()
    {
        $data = $this->mockData();
        $aboutPage = \App\Models\BannerPage::where('slug', 'about')->where('is_active', true)->firstOrFail();
        $about = $data['about'];
        $about['heading'] = $aboutPage->title ?: $aboutPage->name;
        $about['text'] = $aboutPage->content ?? '';
        $banners = PageBanner::where('is_active', true)->whereHas('page', fn ($query) => $query->where('slug', 'about'))->with('page')->latest('id')->get();

        return view('frontend.pages.about', [
            'site' => $data['site'],
            'nav' => $data['nav'],
            'page' => $about,
            'banners' => $banners,
        ]);
    }

    public function contact()
    {
        $data = $this->mockData();
        $page = \App\Models\BannerPage::where('slug', 'contact')->where('is_active', true)->firstOrFail();
        $banners = PageBanner::where('is_active', true)->whereHas('page', fn ($query) => $query->where('slug', 'contact'))->with('page')->latest('id')->get();

        return view('frontend.pages.contact', [
            'site' => $data['site'],
            'nav' => $data['nav'],
            'page' => $page,
            'banners' => $banners,
        ]);
    }
}
