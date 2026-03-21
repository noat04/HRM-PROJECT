<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    // Mở rộng fillable để cho phép lưu cột 'group'
    protected $fillable = [
        'name',
        'guard_name',
        'group', // <--- QUAN TRỌNG: Phải thêm dòng này
        'updated_at',
        'created_at'
    ];
}
