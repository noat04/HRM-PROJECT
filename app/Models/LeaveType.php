<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'is_paid',
        'days_allowed',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
        'days_allowed' => 'integer',
    ];

    // --- HELPER METHODS (Dùng cho UI/Logic) ---

    /**
     * Hiển thị label: "Có lương" hoặc "Không lương"
     */
    public function getPaymentLabelAttribute(): string
    {
        return $this->is_paid ? 'Có hưởng lương' : 'Không hưởng lương';
    }

    /**
     * Màu sắc badge (Bootstrap/Tailwind)
     */
    public function getColorAttribute(): string
    {
        // Có lương -> Xanh lá, Không lương -> Xám, Nghỉ ốm -> Vàng
        if (!$this->is_paid) return 'secondary';
        if ($this->code === 'SICK') return 'warning';
        return 'success';
    }
}