<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'total_days',
        'used_days',
        'remaining_days',
    ];

    protected $casts = [
        'total_days' => 'float',
        'used_days' => 'float',
        'remaining_days' => 'float',
        'year' => 'integer',
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

    // --- AUTOMATION LOGIC (TỰ ĐỘNG TÍNH TOÁN) ---

    /**
     * Boot method: Tự động chạy mỗi khi Create hoặc Update
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($balance) {
            // Luôn đảm bảo: Còn lại = Tổng - Đã dùng
            $balance->remaining_days = $balance->total_days - $balance->used_days;
        });
    }

    // --- HELPER METHODS ---

    /**
     * Kiểm tra xem có đủ phép để xin không?
     * @param float $daysRequesting
     * @return bool
     */
    public function hasEnoughBalance(float $daysRequesting): bool
    {
        return $this->remaining_days >= $daysRequesting;
    }
}