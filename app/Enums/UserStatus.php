<?php


namespace App\Enums;

// String Backed Enum (Enum có giá trị là chuỗi)
enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case BANNED = 'banned';

    // (Tuỳ chọn) Helper method để lấy text hiển thị ra giao diện
    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Đang hoạt động',
            self::INACTIVE => 'Ngừng kích hoạt',
            self::BANNED => 'Bị khóa',
        };
    }

    // (Tuỳ chọn) Helper method để lấy màu cho badge (Bootstrap/Tailwind)
    public function color(): string
    {
        return match($this) {
            self::ACTIVE => 'success', // xanh lá
            self::INACTIVE => 'secondary', // xám
            self::BANNED => 'danger', // đỏ
        };
    }
}