<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\SalaryComponentType;
use App\Enums\SalaryCalculationType;
use Illuminate\Database\Eloquent\Builder;

class SalaryComponent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'calculation_type',
        'default_value',
        'is_taxable',
        'is_active',
        'description',
    ];

    protected $casts = [
        'type' => SalaryComponentType::class,
        'calculation_type' => SalaryCalculationType::class,
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
        'default_value' => 'decimal:2',
    ];

    // --- SCOPES (BỘ LỌC THƯỜNG DÙNG) ---

    /**
     * Lấy danh sách các khoản THU NHẬP (Earnings)
     */
    public function scopeEarnings(Builder $query)
    {
        return $query->where('type', SalaryComponentType::EARNING);
    }

    /**
     * Lấy danh sách các khoản KHẤU TRỪ (Deductions)
     */
    public function scopeDeductions(Builder $query)
    {
        return $query->where('type', SalaryComponentType::DEDUCTION);
    }

    /**
     * Lấy danh sách các khoản ĐANG HOẠT ĐỘNG
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }
}