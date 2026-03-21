<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\SalaryComponentType;
use Illuminate\Database\Eloquent\Builder;

class PayslipDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'payslip_id',
        'salary_component_id',
        'component_name',
        'type',
        'amount',
        'is_taxable',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_taxable' => 'boolean',
        'type' => SalaryComponentType::class, // Auto cast sang Enum
    ];

    // --- RELATIONSHIPS ---

    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }

    public function salaryComponent()
    {
        return $this->belongsTo(SalaryComponent::class);
    }

    // --- SCOPES (BỘ LỌC) ---

    /**
     * Chỉ lấy các khoản THU NHẬP
     */
    public function scopeEarnings(Builder $query)
    {
        return $query->where('type', SalaryComponentType::EARNING);
    }

    /**
     * Chỉ lấy các khoản KHẤU TRỪ
     */
    public function scopeDeductions(Builder $query)
    {
        return $query->where('type', SalaryComponentType::DEDUCTION);
    }

    // --- HELPER ---

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0, ',', '.');
    }
}