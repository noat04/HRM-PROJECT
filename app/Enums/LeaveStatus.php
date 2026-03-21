<?php

namespace App\Enums;

enum LeaveStatus: string
{
    case PENDING = 'pending';     // Chờ duyệt
    case APPROVED = 'approved';   // Đã duyệt
    case REJECTED = 'rejected';   // Từ chối
    case CANCELLED = 'cancelled'; // Hủy (Người xin tự hủy trước khi duyệt)

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Chờ duyệt',
            self::APPROVED => 'Đã duyệt',
            self::REJECTED => 'Từ chối',
            self::CANCELLED => 'Đã hủy',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning', // Vàng
            self::APPROVED => 'success', // Xanh lá
            self::REJECTED => 'danger',  // Đỏ
            self::CANCELLED => 'secondary', // Xám
        };
    }
}