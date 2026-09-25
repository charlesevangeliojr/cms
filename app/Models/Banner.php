<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'image_path',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the publicly accessible image URL.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(
            fn (mixed $value, array $attributes): ?string => ! empty($attributes['image_path'])
                ? Storage::disk('banners')->url($attributes['image_path'])
                : null,
        );
    }
}
