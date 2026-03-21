<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\AttendanceStatus;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'shift_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'late_minutes',
        'early_minutes',
        'ip_address',
        'device_info',
        'note',
    ];

    // 1. Ép kiểu dữ liệu
    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'status' => AttendanceStatus::class, // Auto cast sang Enum object
        'late_minutes' => 'integer',
        'early_minutes' => 'integer',
    ];

    // --- RELATIONSHIPS ---

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Ca làm việc (Snapshot)
     * Đây là ca làm việc ĐƯỢC GHI NHẬN lúc check-in, không phải ca hiện tại.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    // --- COMPUTED ATTRIBUTES (TÍNH TOÁN) ---

    /**
     * Tính tổng giờ làm thực tế (Actual Work Hours)
     * Trả về số giờ (Float). Vd: 8.5 giờ
     */
    public function getWorkHoursAttribute(): float
    {
        if (!$this->check_in || !$this->check_out) {
            return 0;
        }

        // Tính hiệu số phút giữa check-out và check-in
        $minutes = $this->check_in->diffInMinutes($this->check_out);
        
        return round($minutes / 60, 2);
    }

    /**
     * Helper check xem có bị phạt không
     */
    public function getHasPenaltyAttribute(): bool
    {
        return $this->late_minutes > 0 || $this->early_minutes > 0;
    }
}