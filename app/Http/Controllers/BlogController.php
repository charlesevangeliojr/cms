<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogAuthor;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Support\BlogHtml;
use App\Support\BlogPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $search = is_string($request->query('q')) ? trim($request->query('q')) : '';
        $status = $request->query('status');
        $fromValue = $request->query('from');
        $toValue = $request->query('to');
        $from = is_string($fromValue) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromValue) && strtotime($fromValue) ? $fromValue : '';
        $to = is_string($toValue) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $toValue) && strtotime($toValue) ? $toValue : '';
        $dateFilter = $from !== '' || $to !== '';
        $posts = BlogPost::with(['category', 'authorProfile'])->when($search, function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('excerpt', 'like', '%'.$search.'%')
                    ->orWhere('content', 'like', '%'.$search.'%')
                    ->orWhere('author', 'like', '%'.$search.'%');
            });
        })
            ->when(in_array($status, ['visible', 'hidden'], true), fn ($query) => $query->where('is_visible', $status === 'visible'))
            ->when($from !== '', fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to !== '', fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest('id')->paginate(12)->withQueryString();

        return view('backend.blogs.index', compact('posts', 'search', 'status', 'from', 'to', 'dateFilter'));
    }

    public function categories()
    {
        return view('backend.blogs.categories', [
            'categories' => BlogCategory::withCount('posts')->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $name = is_string($request->input('name')) ? trim($request->input('name')) : $request->input('name');
        $request->merge(['name' => $name, 'slug' => is_string($name) ? Str::slug($name) : '']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:120', 'alpha_dash', Rule::unique('blog_categories', 'slug')],
        ], [
            'slug.required' => 'Choose a category name that contains letters or numbers.',
            'slug.unique' => 'A category with this name already exists.',
        ]);

        BlogCategory::create(['name' => $validated['name'], 'slug' => $validated['slug']]);

        return redirect()->route('blog-categories.index')->with('success', 'Blog category created successfully.');
    }

    public function destroyCategory(BlogCategory $category)
    {
        if (BlogCategory::count() <= 1) {
            return redirect()->route('blog-categories.index')->with('error', 'Keep at least one category so new blog posts can be published.');
        }

        if ($category->posts()->exists()) {
            return redirect()->route('blog-categories.index')->with('error', 'This category cannot be deleted while it contains blog posts. Reassign or delete those posts first.');
        }

        $category->delete();

        return redirect()->route('blog-categories.index')->with('success', 'Blog category deleted successfully.');
    }

    public function create()
    {
        return view('backend.blogs.form', [
            'blog' => new BlogPost,
            'categories' => BlogCategory::orderBy('id')->get(),
            'availableTags' => BlogTag::orderBy('name')->pluck('name'),
        ]);
    }

    public function edit(BlogPost $blog)
    {
        $blog->load('seo');
        return view('backend.blogs.form', [
            'blog' => $blog,
            'categories' => BlogCategory::orderBy('id')->get(),
            'availableTags' => BlogTag::orderBy('name')->pluck('name'),
        ]);
    }

    public function downloadPdf(BlogPost $blog)
    {
        $blog->loadMissing(['category', 'authorProfile', 'seo']);
        $filename = \Illuminate\Support\Str::slug($blog->title).'.pdf';

        return response(BlogPdf::render($blog), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function store(Request $request)
    {
        $this->save($request, new BlogPost);

        return redirect()->route('blogs.index')->with('success', 'Blog post created successfully.');
    }

    public function update(Request $request, BlogPost $blog)
    {
        $this->save($request, $blog);

        return redirect()->route('blogs.edit', $blog)->with('success', 'Blog post updated successfully.');
    }

    private function save(Request $request, BlogPost $blog): void
    {
        $handle = $request->input('slug') ?: $request->input('title', '');
        if (is_string($handle)) {
            $request->merge(['slug' => Str::slug($handle)]);
        }
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:180', Rule::unique('blog_posts', 'slug')->ignore($blog->id)],
            'content' => ['required', 'string', 'max:200000'],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'author' => ['required', 'string', 'max:255'],
            'blog_category_id' => ['required', 'integer', 'exists:blog_categories,id'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'article_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_visible' => ['required', 'boolean'],
            'remove_image' => ['nullable', 'boolean'],
            'remove_article_image' => ['nullable', 'boolean'],
        ]);
        $data['content'] = BlogHtml::clean($data['content']);
        if (trim(html_entity_decode(strip_tags($data['content']), ENT_QUOTES, 'UTF-8')) === '') {
            throw ValidationException::withMessages(['content' => 'Please add content to your blog post.']);
        }
        $authorName = trim($data['author']);
        $tagNames = array_values(array_unique(array_filter(array_map('trim', explode(',', $data['tags'] ?? '')))));
        unset($data['author'], $data['tags']);
        $data['is_visible'] = $request->boolean('is_visible');
        $data['published_at'] = $blog->published_at ?? ($data['is_visible'] ? now() : null);
        $seoData = [
            'seo_title' => $data['seo_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
        ];
        unset($data['seo_title'], $data['meta_description']);
        unset($data['image'], $data['article_image'], $data['remove_image'], $data['remove_article_image']);
        $oldPaths = ['image_path' => $blog->image_path, 'article_image_path' => $blog->article_image_path];
        $newPaths = [
            'image_path' => $request->boolean('remove_image') ? null : $oldPaths['image_path'],
            'article_image_path' => $request->boolean('remove_article_image') ? null : $oldPaths['article_image_path'],
        ];
        foreach (['image' => 'image_path', 'article_image' => 'article_image_path'] as $field => $pathKey) {
            if ($request->hasFile($field)) {
                $upload = $request->file($field);
                $name = Str::uuid().'.'.$upload->extension();
                $upload->storeAs('', $name, 'blogs');
                $newPaths[$pathKey] = 'uploads/blogs/'.$name;
            }
        }
        try {
            DB::transaction(function () use ($blog, $data, $newPaths, $seoData, $authorName, $tagNames) {
                $author = BlogAuthor::firstOrCreate(['name' => $authorName]);
                $blog->fill([...$data, 'blog_author_id' => $author->id, ...$newPaths])->save();
                $tagIds = collect($tagNames)->map(function ($name) {
                    $existing = BlogTag::whereRaw('LOWER(name) = ?', [Str::lower($name)])->first();

                    return ($existing ?? BlogTag::create(['name' => $name]))->id;
                })->unique()->values()->all();
                $blog->tags()->sync($tagIds);
                if (filled($seoData['seo_title']) || filled($seoData['meta_description'])) {
                    $blog->seo()->updateOrCreate([], $seoData);
                } else {
                    $blog->seo()->delete();
                }
            });
        } catch (Throwable $exception) {
            foreach ($newPaths as $pathKey => $newPath) {
                if ($newPath !== $oldPaths[$pathKey]) $this->deleteImage($newPath);
            }
            throw $exception;
        }
        foreach ($newPaths as $pathKey => $newPath) {
            if ($oldPaths[$pathKey] !== $newPath) $this->deleteImage($oldPaths[$pathKey]);
        }
    }

    public function destroy(BlogPost $blog)
    {
        $paths = [$blog->image_path, $blog->article_image_path];
        $blog->delete();
        foreach ($paths as $path) $this->deleteImage($path);

        return redirect()->route('blogs.index')->with('success', 'Blog post deleted successfully.');
    }

    private function deleteImage(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/blogs/') && ! str_contains($path, '..')) {
            Storage::disk('blogs')->delete(basename($path));
        }
    }
}
