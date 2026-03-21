<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache của Spatie trước khi seed
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Tạo danh sách Permissions (Quyền)
        $permissions = [
            'employee.view', 'employee.create', 'employee.update', 'employee.delete',
            'department.manage', 'position.manage',
            'attendance.check_in', 'attendance.manage',
            'leave.create', 'leave.approve', 'leave.manage',
            'payroll.calculate', 'payroll.view'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Tạo Role và gán quyền
        // Role: Nhân viên bình thường
        $employeeRole = Role::firstOrCreate(['name' => 'Employee']);
        $employeeRole->givePermissionTo(['employee.view', 'attendance.check_in', 'leave.create']);

        // Role: HR Manager
        $hrRole = Role::firstOrCreate(['name' => 'HR Manager']);
        $hrRole->givePermissionTo([
            'employee.view', 'employee.create', 'employee.update', 
            'department.manage', 'position.manage', 
            'attendance.manage', 'leave.manage'
        ]);

        // Role: Super Admin (Sẽ có toàn quyền, thường xử lý qua Gate::before)
        Role::firstOrCreate(['name' => 'Super Admin']);
    }
}