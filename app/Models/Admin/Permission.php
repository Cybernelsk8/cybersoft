<?php

namespace App\Models\Admin;

use App\Traits\Searchable;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use Searchable;

    protected $fillable = [
        'name',
        'module',
        'alias',
        'guard_name',
    ];
}
