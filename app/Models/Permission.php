<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Spatie\Activitylog\Traits\LogsActivity;


class Permission extends SpatiePermission
{
    use SoftDeletes;

    // Chỉ cần các cột do user nhập hoặc thao tác tay
    protected $fillable = [
        'name',
        'guard_name',
        'group', 
    ];

    // BẮT BUỘC: Cấu hình cho Spatie Activitylog biết cần ghi log những gì
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            // Tự động ghi log tất cả các cột trong fillable nếu có thay đổi
            ->logFillable()
            // Chỉ ghi log khi dữ liệu thực sự có sự khác biệt
            ->logOnlyDirty()
            // Đặt tên cho nhật ký này để dễ tìm
            ->useLogName('permission'); 
    }
}