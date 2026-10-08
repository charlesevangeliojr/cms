<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageBanner extends Model
{
    protected $fillable = ['banner_page_id', 'image_path', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(BannerPage::class, 'banner_page_id');
    }

    protected function title(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->page?->name);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): string => '/'.ltrim($this->image_path, '/'));
    }
}
