<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\PayrollStatus;
use Carbon\Carbon;

class PayrollPeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'start_date',
        'end_date',
        'status',
        'payment_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'date',
        'status' => PayrollStatus::class, // Auto cast sang Enum
    ];

    // --- RELATIONSHIPS ---

    // Một kỳ lương có nhiều phiếu lương (Payslips)
    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    // --- LOGIC KIỂM TRA (BUSINESS LOGIC) ---

    /**
     * Kiểm tra xem kỳ lương này còn cho phép sửa đổi không?
     * (Dùng để chặn sửa chấm công, thêm đơn nghỉ phép...)
     */
    public function canBeModified(): bool
    {
        // Chỉ cho phép sửa khi còn là NHÁP
        return $this->status === PayrollStatus::DRAFT;
    }

    /**
     * Kiểm tra một ngày cụ thể có nằm trong kỳ lương này không?
     */
    public function containsDate($date): bool
    {
        $targetDate = Carbon::parse($date);
        return $targetDate->between($this->start_date, $this->end_date);
    }
    
    /**
     * Tính tổng số công chuẩn trong tháng (Standard Working Days)
     * Vd: Tháng có 22 ngày công chuẩn (trừ T7, CN).
     * Hàm này có thể phức tạp hơn tùy cấu hình Shifts.
     */
    public function getStandardWorkingDaysAttribute()
    {
        // 👇 Xóa chữ "Carbon" đi, chỉ giữ lại biến $date
        return $this->start_date->diffInDaysFiltered(function ($date) {
            return !$date->isWeekend();
        }, $this->end_date) + 1; 
    }
}