<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Support\BlogHtml;

class PublicBlogController extends Controller
{
    public function index(?BlogCategory $category = null)
    {
        $posts = BlogPost::published()->with(['category', 'seo'])->when($category?->exists, fn ($query) => $query->where('blog_category_id', $category->id))
            ->orderByDesc('published_at')->orderByDesc('id')->paginate(9);

        return view('frontend.pages.blog', ['posts' => $posts, 'category' => $category, 'categories' => BlogCategory::orderBy('id')->get()]);
    }

    public function show(BlogCategory $category, string $post)
    {
        $post = BlogPost::published()->with(['category', 'seo', 'authorProfile'])->where('blog_category_id', $category->id)->where('slug', $post)->firstOrFail();

        return view('frontend.pages.blog-post', ['post' => $post, 'safeContent' => BlogHtml::clean($post->content)]);
    }

    public function sitemap()
    {
        return response()->view('frontend.pages.sitemap', ['posts' => BlogPost::published()->with('category')->orderBy('id')->get()], 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
