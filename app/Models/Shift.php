<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Shift extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'start_time',
        'end_time',
        'grace_period',
        'work_days',
        'status',
        'description',
    ];

    // 1. Ép kiểu dữ liệu (Casting)
    protected $casts = [
        'grace_period' => 'integer',
        'work_days' => 'array', // Tự động chuyển JSON DB -> Mảng PHP
    ];

    // --- LOGIC NGHIỆP VỤ (BUSINESS LOGIC) ---

    /**
     * Ca này có làm việc qua đêm không? (Vd: 22:00 -> 06:00 sáng hôm sau)
     * Logic: Nếu Giờ kết thúc < Giờ bắt đầu -> Là qua đêm.
     */
    public function getIsOvernightAttribute(): bool
    {
        return $this->end_time < $this->start_time;
    }

    /**
     * Tính tổng số giờ làm việc (Duration)
     * Vd: 08:00 -> 17:00 là 9 tiếng.
     */
    public function getTotalHoursAttribute(): float
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        // Nếu là ca qua đêm, cộng thêm 1 ngày vào giờ kết thúc để trừ ra số dương
        if ($this->is_overnight) {
            $end->addDay();
        }

        return round($start->diffInMinutes($end) / 60, 2);
    }

    /**
     * Format hiển thị đẹp: "08:00 - 17:00"
     */
    public function getWorkingTimeLabelAttribute(): string
    {
        // Cắt bỏ số giây (08:00:00 -> 08:00)
        $start = substr($this->start_time, 0, 5);
        $end = substr($this->end_time, 0, 5);
        return "{$start} - {$end}";
    }
}