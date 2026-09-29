<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolePrivilege extends Model
{
    public $timestamps = false;

    protected $fillable = ['role_id', 'privilege_id', 'page_id'];

    public function role(): BelongsTo { return $this->belongsTo(Role::class); }
    public function privilege(): BelongsTo { return $this->belongsTo(Privilege::class); }
    public function page(): BelongsTo { return $this->belongsTo(Page::class); }
}
