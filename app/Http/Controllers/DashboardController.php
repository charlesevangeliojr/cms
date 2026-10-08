<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\HomeBannerImage;
use App\Models\NewsletterSubscriber;
use App\Models\PageBanner;
use App\Models\User;
use App\Services\GoogleAnalyticsRealtime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard — latest at top for all lists.
     */
    public function index(GoogleAnalyticsRealtime $analytics)
    {
        $totalUsers = User::count();
        $recentUsers = User::with('role')->latest('id')->take(5)->get();

        $totalBanners = HomeBannerImage::count() + PageBanner::count();
        $activeBanners = HomeBannerImage::where('is_active', true)->count() + PageBanner::where('is_active', true)->count();
        $recentBanners = HomeBannerImage::with('banner')->latest('id')->take(5)->get()
            ->concat(PageBanner::latest('id')->take(5)->get())->sortByDesc('created_at')->take(5)->values();

        $totalContacts = ContactMessage::count();
        $unreadContacts = ContactMessage::where('is_read', false)->count();
        $recentContacts = ContactMessage::latest('id')->take(5)->get();

        $totalNewsletters = NewsletterSubscriber::count();
        $activeNewsletters = NewsletterSubscriber::where('is_active', true)->count();
        $recentNewsletters = NewsletterSubscriber::latest('id')->take(5)->get();

        $uploadCount = 0;
        $uploadPath = public_path('uploads');
        if (File::isDirectory($uploadPath)) {
            foreach (File::allFiles($uploadPath) as $file) {
                if ($file->getFilename() !== '.gitkeep') {
                    $uploadCount++;
                }
            }
        }

        $pages = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'About', 'url' => '/about'],
        ];

        return view('backend.pages.dashboard', [
            'totalUsers' => $totalUsers,
            'recentUsers' => $recentUsers,
            'totalBanners' => $totalBanners,
            'activeBanners' => $activeBanners,
            'recentBanners' => $recentBanners,
            'totalContacts' => $totalContacts,
            'unreadContacts' => $unreadContacts,
            'recentContacts' => $recentContacts,
            'totalNewsletters' => $totalNewsletters,
            'activeNewsletters' => $activeNewsletters,
            'recentNewsletters' => $recentNewsletters,
            'uploadCount' => $uploadCount,
            'pages' => $pages,
            'pageCount' => count($pages),
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
            'environment' => config('app.env'),
            'dbDriver' => config('database.default'),
            'timezone' => config('app.timezone'),
            'analyticsRealtime' => $analytics->dashboardData(),
        ]);
    }

    public function traffic(GoogleAnalyticsRealtime $analytics, Request $request)
    {
        $validated = $request->validate(['range' => ['sometimes', 'string', 'in:30m,today,7d,30d,month']]);

        return response()->json($analytics->dashboardData($validated['range'] ?? '30m'));
    }
}
