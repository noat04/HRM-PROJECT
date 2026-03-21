<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Position;
use App\Models\LeaveType;
use App\Models\SalaryComponent;
use App\Models\Shift;
use App\Enums\SalaryComponentType;
use App\Enums\SalaryCalculationType;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo Ca làm việc chuẩn (Hành chính)
       // 1. Tạo Ca làm việc chuẩn (Hành chính)
        Shift::firstOrCreate(['code' => 'OFFICE'], [
            'name' => 'Ca Hành Chính',
            'start_time' => '08:00:00',
            'end_time' => '17:30:00',
            'grace_period' => 15, // Cho phép đi muộn 15p
            
            // 👇 THÊM DÒNG NÀY (Các ngày làm việc từ Thứ 2 đến Thứ 6)
            'work_days' => json_encode(['Mon', 'Tue', 'Wed', 'Thu', 'Fri']), 
        ]);

        // 2. Tạo Phòng ban
        $board = Department::firstOrCreate(['name' => 'Ban Giám Đốc']);
        Department::firstOrCreate(['name' => 'Phòng Nhân Sự (HR)', 'parent_id' => $board->id]);
        Department::firstOrCreate(['name' => 'Phòng Công Nghệ (IT)', 'parent_id' => $board->id]);

        // 3. Tạo Chức vụ
       // 3. Tạo Chức vụ (Bổ sung thêm trường 'code')
        Position::firstOrCreate(
            ['code' => 'DIR'], 
            ['name' => 'Giám Đốc']
        );
        
        Position::firstOrCreate(
            ['code' => 'MGR'], 
            ['name' => 'Trưởng Phòng']
        );
        
        Position::firstOrCreate(
            ['code' => 'EMP'], 
            ['name' => 'Nhân Viên']
        );

        // 4. Tạo Loại nghỉ phép
        LeaveType::firstOrCreate(['code' => 'ANNUAL'], [
            'name' => 'Nghỉ phép năm',
            'is_paid' => true,
            'days_allowed' => 12
        ]);
        LeaveType::firstOrCreate(['code' => 'UNPAID'], [
            'name' => 'Nghỉ không lương',
            'is_paid' => false,
            'days_allowed' => 0 // Không giới hạn
        ]);

        // 5. Tạo Các khoản lương cơ bản
        SalaryComponent::firstOrCreate(['code' => 'BASIC_SALARY'], [
            'name' => 'Lương cơ bản',
            'type' => 'earning', // Thay bằng Enum nếu bạn đã tạo: SalaryComponentType::EARNING->value
            'calculation_type' => 'fixed',
            'is_taxable' => true
        ]);
        SalaryComponent::firstOrCreate(['code' => 'LUNCH_ALLOWANCE'], [
            'name' => 'Phụ cấp ăn trưa',
            'type' => 'earning',
            'calculation_type' => 'fixed',
            'default_value' => 730000,
            'is_taxable' => false
        ]);
    }
}