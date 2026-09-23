<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\File;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     *
     * Gathers real stats (users, uploads, system info) and passes them
     * to the backend.dashboard view. Frontend pages are listed here so
     * the "Pages" card and quick links stay in one place.
     */
    public function index()
    {
        $totalUsers = User::count();
        $recentUsers = User::orderByDesc('created_at')->take(5)->get();

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
