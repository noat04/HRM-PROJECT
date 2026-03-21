<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Support\Facades\Hash;

class UserAndEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo tài khoản User
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@larahrm.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'), // Mật khẩu mặc định
                // 'is_active' => true
            ]
        );

        // Gán quyền cao nhất
        $adminUser->assignRole('Super Admin');

        // 2. Lấy ID phòng ban và chức vụ
        $itDept = Department::where('name', 'Phòng Công Nghệ (IT)')->first();
        $directorPos = Position::where('name', 'Giám Đốc')->first();

        // 3. Tạo Hồ sơ nhân viên (Employee) tương ứng cho tài khoản này
        Employee::firstOrCreate(
            ['user_id' => $adminUser->id],
            [
                'employee_code' => 'EMP-00001',
                'full_name' => 'Super Admin',
                'gender' => 'male',
                'join_date' => now()->format('Y-m-d'),
                'department_id' => $itDept->id ?? null,
                'position_id' => $directorPos->id ?? null,
            ]
        );
    }
}