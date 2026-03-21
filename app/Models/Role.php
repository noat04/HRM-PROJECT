<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
// Kế thừa class Role của Spatie thay vì Model mặc định của Laravel
use Spatie\Permission\Models\Role as SpatieRole; 

class Role extends SpatieRole
{
    use HasFactory, SoftDeletes;

    /**
     * Các trường cho phép insert dữ liệu hàng loạt (Mass Assignment)
     */
    protected $fillable = [
        'name',
        'guard_name',
        'display_name', // Cột tùy chỉnh bạn thêm
        'description',  // Cột tùy chỉnh bạn thêm
    ];

    // BẠN KHÔNG CẦN VIẾT RELATIONSHIP Ở ĐÂY
    // Thư viện SpatieRole đã tự động viết sẵn hàm:
    // public function permissions() { return $this->belongsToMany(...); }
    // public function users() { return $this->morphedByMany(...); }
}