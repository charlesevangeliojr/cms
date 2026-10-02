<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

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
            function (mixed $value, array $attributes): ?string {
                $path = $attributes['image_path'] ?? null;

                if (! is_string($path) || $path === '' || str_contains($path, '..')) {
                    return null;
                }

                return '/uploads/banners/'.basename($path);
            },
        );
    }
}
