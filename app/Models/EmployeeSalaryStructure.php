<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class EmployeeSalaryStructure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'component_id',
        'amount',
        'effective_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'effective_date' => 'date',
    ];

    // --- RELATIONSHIPS ---

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function component()
    {
        return $this->belongsTo(SalaryComponent::class, 'component_id');
    }

    // --- LOGIC TRUY VẤN (SCOPES) ---

    /**
     * Scope: Lấy mức lương đang áp dụng tại thời điểm $date.
     * Logic: Lấy bản ghi có effective_date <= $date VÀ mới nhất (gần $date nhất).
     */
    public function scopeActiveAt(Builder $query, $date)
    {
        return $query->where('effective_date', '<=', $date)
                     ->orderBy('effective_date', 'desc');
    }

    // --- HELPER METHODS ---

    /**
     * Format tiền tệ hiển thị (Vd: 10,000,000)
     */
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 0, ',', '.');
    }
}