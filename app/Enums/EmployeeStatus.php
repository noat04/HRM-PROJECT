<?php

namespace App\Enums;

enum EmployeeStatus: string
{
    case PROBATION = 'probation'; // Thử việc
    case OFFICIAL = 'official';   // Chính thức
    case RESIGNED = 'resigned';   // Đã nghỉ
    case PAUSED = 'paused';       // Tạm hoãn HĐ (Nghỉ thai sản...)

    public function label(): string
    {
        return match($this) {
            self::PROBATION => 'Thử việc',
            self::OFFICIAL => 'Chính thức',
            self::RESIGNED => 'Đã nghỉ việc',
            self::PAUSED => 'Tạm hoãn',
        };
    }
}