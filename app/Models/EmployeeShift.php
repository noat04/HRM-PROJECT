<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // Import Builder
use Carbon\Carbon;

class EmployeeShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'shift_id',
        'start_date',
        'end_date',
    ];

    // 1. Ép kiểu Date (Quan trọng để so sánh ngày tháng)
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // --- RELATIONSHIPS ---

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    // --- LOGIC "GHI ĐIỂM": QUERIES ---

    /**
     * Scope: Tìm ca làm việc đang active tại một ngày cụ thể.
     * Cách dùng: EmployeeShift::activeAt($employeeId, '2023-10-25')->first();
     */
    public function scopeActiveAt(Builder $query, int $employeeId, $date)
    {
        // Logic: 
        // 1. Đúng nhân viên đó
        // 2. Ngày start <= Ngày cần tìm
        // 3. (Ngày end >= Ngày cần tìm HOẶC Ngày end là NULL)
        
        return $query->where('employee_id', $employeeId)
                     ->where('start_date', '<=', $date)
                     ->where(function ($q) use ($date) {
                         $q->where('end_date', '>=', $date)
                           ->orWhereNull('end_date');
                     })
                     ->orderBy('start_date', 'desc'); // Lấy cái mới nhất nếu lỡ có trùng
    }
}