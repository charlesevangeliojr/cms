<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = ['blog_category_id', 'blog_author_id', 'title', 'slug', 'content', 'excerpt', 'image_path', 'article_image_path', 'is_visible', 'published_at'];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'published_at' => 'datetime'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function seo(): HasOne
    {
        return $this->hasOne(BlogPostSeo::class);
    }

    public function authorProfile(): BelongsTo
    {
        return $this->belongsTo(BlogAuthor::class, 'blog_author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag');
    }

    public function getAuthorAttribute(): string
    {
        return $this->authorProfile?->name ?? 'Editorial Team';
    }

    /** @return list<string> */
    public function getTagsAttribute(): array
    {
        $tags = $this->relationLoaded('tags') ? $this->getRelation('tags') : $this->tags()->get();

        return $tags->pluck('name')->all();
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_visible', true)->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function getPublicUrlAttribute(): string
    {
        return route('blog.show', ['category' => $this->category->slug, 'post' => $this->slug]);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('uploads/blogs/'.basename($this->image_path)) : null;
    }

    public function getArticleImageUrlAttribute(): ?string
    {
        return $this->article_image_path ? asset('uploads/blogs/'.basename($this->article_image_path)) : null;
    }

    public function getImageAltAttribute(): string
    {
        return $this->search_description;
    }

    public function getSearchDescriptionAttribute(): string
    {
        return $this->seo?->meta_description ?: Str::limit($this->excerpt ?: html_entity_decode(strip_tags($this->content), ENT_QUOTES, 'UTF-8'), 160, '');
    }
}
