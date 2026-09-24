<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    public const POSITIONS = [
        'Homepage Hero',
        'Sidebar Top',
        'Footer Banner',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'image_path',
        'position',
        'target_url',
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
            'clicks' => 'integer',
        ];
    }

    /**
     * Get the publicly accessible image URL.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(
            fn (?string $value): ?string => $value
                ? Storage::disk('banners')->url($value)
                : null,
        );
    }
}
