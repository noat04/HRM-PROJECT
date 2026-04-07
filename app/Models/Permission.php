<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivityCustom;

class Permission extends SpatiePermission
{
    use SoftDeletes, LogsActivityCustom;

    protected $fillable = [
        'name',
        'guard_name',
        'group',
    ];
}