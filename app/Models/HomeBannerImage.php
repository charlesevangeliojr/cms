<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeBannerImage extends Model
{
    protected $fillable = ['home_banner_id', 'image_path', 'is_active', 'position'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'position' => 'integer'];
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(HomeBanner::class, 'home_banner_id');
    }

    public function getTitleAttribute(): ?string
    {
        return $this->banner?->title;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->banner?->description;
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): string => '/'.ltrim($this->image_path, '/'));
    }
}
