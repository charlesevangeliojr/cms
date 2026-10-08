<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BannerPage extends Model
{
    protected $fillable = ['name', 'slug', 'title', 'content', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function banners(): HasMany
    {
        return $this->hasMany(PageBanner::class);
    }
}
