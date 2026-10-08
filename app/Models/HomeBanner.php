<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeBanner extends Model
{
    protected $fillable = ['title', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function images(): HasMany
    {
        return $this->hasMany(HomeBannerImage::class)->orderBy('position')->orderBy('id');
    }
}
