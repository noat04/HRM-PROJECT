<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\LeaveStatus;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'manager_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'rejection_reason',
        'responded_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => LeaveStatus::class,
        'total_days' => 'float',
        'responded_at' => 'datetime',
    ];

    // --- RELATIONSHIPS ---

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Người duyệt đơn (Snapshot)
     */
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    // --- VALIDATION LOGIC (SCOPES) ---

    /**
     * Kiểm tra xem nhân viên này có đơn nào trùng ngày không?
     * Logic: (StartA <= EndB) and (EndA >= StartB)
     * * Cách dùng: 
     * if (LeaveRequest::overlapping($empId, $start, $end)->exists()) { // Báo lỗi }
     */
    public function scopeOverlapping(Builder $query, int $employeeId, $startDate, $endDate)
    {
        return $query->where('employee_id', $employeeId)
                     ->where('status', '!=', LeaveStatus::REJECTED) // Không tính đơn đã bị từ chối
                     ->where('status', '!=', LeaveStatus::CANCELLED) // Không tính đơn đã hủy
                     ->where(function ($q) use ($startDate, $endDate) {
                         $q->whereBetween('start_date', [$startDate, $endDate])
                           ->orWhereBetween('end_date', [$startDate, $endDate])
                           ->orWhere(function ($sub) use ($startDate, $endDate) {
                               $sub->where('start_date', '<=', $startDate)
                                   ->where('end_date', '>=', $endDate);
                           });
                     });
    }
}