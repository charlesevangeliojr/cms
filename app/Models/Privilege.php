<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Privilege extends Model
{
    protected $fillable = ['name'];

    public function rolePrivileges(): HasMany
    {
        return $this->hasMany(RolePrivilege::class);
    }

    public function userPrivileges(): HasMany
    {
        return $this->hasMany(UserPrivilege::class);
    }
}
