<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPrivilege extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'privilege_id', 'page_id', 'is_allowed'];

    protected function casts(): array
    {
        return ['is_allowed' => 'boolean'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function privilege(): BelongsTo { return $this->belongsTo(Privilege::class); }
    public function page(): BelongsTo { return $this->belongsTo(Page::class); }
}
