<?php

namespace App\Enums;

enum PayrollStatus: string
{
    case DRAFT = 'draft';       // Nháp: Đang tính toán, HR còn sửa được
    case LOCKED = 'locked';     // Đã chốt: Số liệu cố định, cấm sửa chấm công
    case PAID = 'paid';         // Đã thanh toán: Tiền đã về tài khoản nhân viên

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Nháp (Đang tính)',
            self::LOCKED => 'Đã chốt sổ',
            self::PAID => 'Đã chi trả',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'secondary', // Xám
            self::LOCKED => 'warning',  // Vàng/Cam
            self::PAID => 'success',    // Xanh lá
        };
    }
}