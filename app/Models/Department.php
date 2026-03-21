<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'parent_id', 'manager_id', 'description', 'level'];

    // 1. Quan hệ với cha (Parent)
    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    // 2. Quan hệ với con (Children)
    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    // 3. Đệ quy: Lấy toàn bộ cây con cháu (Grandchildren...)
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    // 4. Quan hệ với Trưởng phòng
    public function manager()
    {
        // Dùng chuỗi 'App\Models\Employee' thay vì Employee::class 
        // để tránh lỗi import vòng lặp nếu file Employee chưa load (tùy PHP version)
        return $this->belongsTo(\App\Models\Employee::class, 'manager_id');
    }
    
    // 5. Quan hệ với Nhân viên thuộc phòng này
    public function employees()
    {
        return $this->hasMany(\App\Models\Employee::class);
    }
}