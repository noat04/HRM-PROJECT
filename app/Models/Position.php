<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use HasFactory, SoftDeletes;

    // 1. Khai báo các cột được phép gán dữ liệu (Mass Assignment)
    protected $fillable = [
        'name',
        'code',
        'level',
        'salary_min',
        'salary_max',
        'description',
    ];

    // 2. Cast dữ liệu (Ép kiểu)
    // Quan trọng: Ép kiểu decimal về float/integer để tính toán trong PHP
    protected $casts = [
        'level' => 'integer',
        'salary_min' => 'decimal:2', // Giữ 2 số thập phân
        'salary_max' => 'decimal:2',
    ];

    // --- RELATIONSHIPS (MỐI QUAN HỆ) ---

    /**
     * Chức vụ này có bao nhiêu nhân viên?
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    // --- HELPER METHODS (HÀM TIỆN ÍCH) ---

    /**
     * Format lương min tiền Việt (Vd: 10,000,000 VNĐ)
     */
    public function getFormattedSalaryMinAttribute()
    {
        return number_format($this->salary_min, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Format lương max tiền Việt
     */
    public function getFormattedSalaryMaxAttribute()
    {
        return number_format($this->salary_max, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Check xem chức vụ này có quyền quản lý chức vụ kia không?
     * @param Position $otherPosition
     * @return bool
     */
    public function isHigherLevelThan(Position $otherPosition)
    {
        return $this->level > $otherPosition->level;
    }
}