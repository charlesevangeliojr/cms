<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteMetadata extends Model
{
    protected $fillable = ['site_name', 'title', 'description', 'image', 'favicon', 'keywords', 'og_title', 'og_description'];

    protected function casts(): array
    {
        return ['keywords' => 'array'];
    }

    /** Return the singleton settings record, creating it from safe defaults if needed. */
    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'site_name' => config('metadata.defaults.site_name', config('app.name')),
            'title' => config('metadata.defaults.title', config('app.name')),
            'description' => config('metadata.defaults.description', ''),
            'image' => config('metadata.defaults.image', 'images/cms-logo.png'),
        ]);
    }
}
