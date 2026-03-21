<?php

namespace App\Enums;

enum SalaryCalculationType: string
{
    case FIXED = 'fixed';       // Số tiền cố định (Vd: Phụ cấp ăn 730k)
    case PERCENTAGE = 'percentage'; // Tính theo % (Vd: BHXH 8%)
    case FORMULA = 'formula';   // Công thức phức tạp (Vd: Thuế TNCN bậc thang)
}