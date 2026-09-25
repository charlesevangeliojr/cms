<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Support\Facades\File;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard — latest at top for all lists.
     */
    public function index()
    {
        $totalUsers = User::count();
        $recentUsers = User::latest('id')->take(5)->get();

        $totalBanners = Banner::count();
        $activeBanners = Banner::where('is_active', true)->count();
        $recentBanners = Banner::latest('id')->take(5)->get();

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

        return view('backend.dashboard', [
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
        ]);
    }
}
