<?php

namespace App\Http\Controllers;

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
                'tagline' => 'Turbo Drive ON — no full refresh',
            ],
            'nav' => [
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'About', 'url' => '/about'],
            ],
            'home' => [
                'heading' => 'Home',
                'text' => 'Tailwind is working. Click About — Turbo swaps the page with no full refresh.',
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

        return view('frontend.pages.home', [
            'site' => $data['site'],
            'nav' => $data['nav'],
            'page' => $data['home'],
        ]);
    }

    public function about()
    {
        $data = $this->mockData();

        return view('frontend.pages.about', [
            'site' => $data['site'],
            'nav' => $data['nav'],
            'page' => $data['about'],
        ]);
    }
}
