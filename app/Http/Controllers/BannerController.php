<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Display a listing of banners.
     */
    public function index()
    {
        // Mock banner data
        $banners = [
            [
                'id' => 1,
                'title' => 'Autumn Promotional Hero Banner',
                'image_url' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?auto=format&fit=crop&w=1200&q=80',
                'position' => 'Homepage Hero',
                'target_url' => '/promotions/autumn',
                'status' => 'Active',
                'clicks' => 1420,
                'created_at' => '2026-09-01',
            ],
            [
                'id' => 2,
                'title' => 'Black Friday Early Bird Discount',
                'image_url' => 'https://images.unsplash.com/photo-1607083206869-4c7672e72a8a?auto=format&fit=crop&w=1200&q=80',
                'position' => 'Sidebar Top',
                'target_url' => '/black-friday',
                'status' => 'Active',
                'clicks' => 890,
                'created_at' => '2026-09-10',
            ],
            [
                'id' => 3,
                'title' => 'New Product Collection Announcement',
                'image_url' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=1200&q=80',
                'position' => 'Footer Banner',
                'target_url' => '/collections/new',
                'status' => 'Inactive',
                'clicks' => 310,
                'created_at' => '2026-08-15',
            ],
        ];

        return view('backend.banners.index', compact('banners'));
    }
}
