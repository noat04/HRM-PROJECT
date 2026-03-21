<?php

namespace App\Enums;

enum SalaryComponentType: string
{
    case EARNING = 'earning';       // Thu nhập (Cộng vào lương)
    case DEDUCTION = 'deduction';   // Khấu trừ (Trừ khỏi lương)

    public function label(): string
    {
        return match($this) {
            self::EARNING => 'Thu nhập',
            self::DEDUCTION => 'Khấu trừ',
        };
    }
}