<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case ON_TIME = 'on_time';           // Đúng giờ
    case LATE = 'late';                 // Đi muộn
    case EARLY_LEAVE = 'early_leave';   // Về sớm
    case LATE_AND_EARLY = 'late_early'; // Vừa muộn vừa về sớm
    case ABSENT = 'absent';             // Vắng mặt (Không check-in)
    case MISSING_CHECKOUT = 'missing_checkout'; // Quên check-out

    public function label(): string
    {
        return match($this) {
            self::ON_TIME => 'Đúng giờ',
            self::LATE => 'Đi muộn',
            self::EARLY_LEAVE => 'Về sớm',
            self::LATE_AND_EARLY => 'Muộn & Về sớm',
            self::ABSENT => 'Vắng mặt',
            self::MISSING_CHECKOUT => 'Thiếu check-out',
        };
    }
    
    public function color(): string
    {
        return match($this) {
            self::ON_TIME => 'success',
            self::LATE, self::EARLY_LEAVE, self::LATE_AND_EARLY => 'warning',
            self::ABSENT, self::MISSING_CHECKOUT => 'danger',
        };
    }
}