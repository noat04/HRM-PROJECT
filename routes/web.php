<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LeaveBalanceController;
use App\Http\Controllers\SalaryComponentController;
use App\Http\Controllers\EmployeeSalaryStructureController;
use App\Http\Controllers\PayrollPeriodController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\PermissionController;
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// BỌC TẤT CẢ BẰNG AUTH VÀ CHECK STATUS (KIll Switch)
Route::middleware(['auth', CheckUserStatus::class])->group(function () {
    
    // ==========================================
    // 1. NHÓM CHỈ DÀNH CHO SUPER ADMIN
    // ==========================================
    Route::middleware(['role:Super Admin'])->group(function () {
        Route::put('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status')->middleware('permission:system_manage_users');
        Route::put('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore')->middleware('permission:system_manage_users');
        Route::delete('users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete')->middleware('permission:system_manage_users');
        Route::delete('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete')->middleware('permission:system_manage_users');
        Route::put('users/bulk-restore', [UserController::class, 'bulkRestore'])->name('users.bulk-restore')->middleware('permission:system_manage_users');
        Route::resource('users', UserController::class)->middleware('permission:system_manage_users');
        
        Route::resource('roles', RoleController::class)->middleware('permission:system_manage_roles');
        Route::put('roles/{id}/restore', [RoleController::class, 'restore'])->name('roles.restore')->middleware('permission:system_manage_roles');
        Route::delete('roles/{id}/force-delete', [RoleController::class, 'forceDelete'])->name('roles.force-delete')->middleware('permission:system_manage_roles');

        Route::put('permissions/{id}/restore', [PermissionController::class, 'restore'])->name('permissions.restore')->middleware('permission:system_manage_permission');
        Route::delete('permissions/{id}/force-delete', [PermissionController::class, 'forceDelete'])->name('permissions.force-delete')->middleware('permission:system_manage_permission');
        Route::delete('permissions/bulk-delete', [PermissionController::class, 'bulkDelete'])->name('permissions.bulk-delete')->middleware('permission:system_manage_permission');
        Route::put('permissions/bulk-restore', [PermissionController::class, 'bulkRestore'])->name('permissions.bulk-restore')->middleware('permission:system_manage_permission');
        Route::resource('permissions', PermissionController::class)->middleware('permission:system_manage_permission');
    });

    // ==========================================
    // 2. NHÓM CHỈ DÀNH CHO HR MANAGER (Cấu hình hệ thống)
    // ==========================================
    Route::middleware(['role:HR Manager'])->group(function () {
        Route::put('departments/{id}/restore', [DepartmentController::class, 'restore'])->name('departments.restore')->middleware('permission:department_manage');
        Route::delete('departments/{id}/force-delete', [DepartmentController::class, 'forceDelete'])->name('departments.force-delete')->middleware('permission:department_manage');
        Route::delete('departments/bulk-delete', [DepartmentController::class, 'bulkDelete'])->name('departments.bulk-delete')->middleware('permission:department_manage');
        Route::put('departments/bulk-restore', [DepartmentController::class, 'bulkRestore'])->name('departments.bulk-restore')->middleware('permission:department_manage');

        Route::resource('departments', DepartmentController::class)->middleware('permission:department_manage');

        Route::put('positions/{id}/restore', [PositionController::class, 'restore'])->name('positions.restore')->middleware('permission:position_manage');
        Route::delete('positions/{id}/force-delete', [PositionController::class, 'forceDelete'])->name('positions.force-delete')->middleware('permission:position_manage');
        Route::delete('positions/bulk-delete', [PositionController::class, 'bulkDelete'])->name('positions.bulk-delete')->middleware('permission:position_manage');
        Route::put('positions/bulk-restore', [PositionController::class, 'bulkRestore'])->name('positions.bulk-restore')->middleware('permission:position_manage');
        Route::resource('positions', PositionController::class)->middleware('permission:position_manage');

        
        Route::resource('shifts', ShiftController::class)->middleware('permission:shift_manage');
        
        // Quản lý loại nghỉ phép
        Route::prefix('leaves')->name('leaves.')->group(function () {
            Route::resource('types', LeaveTypeController::class)->middleware('permission:leave_type_manage');
        });

        // Quản lý thành phần lương & Kỳ lương
        Route::resource('salary-components', SalaryComponentController::class)->parameters(['salary-components' => 'salaryComponent'])->middleware('permission:salary_component_manage');
        Route::resource('payroll-periods', PayrollPeriodController::class)->parameters(['payroll-periods' => 'payrollPeriod'])->middleware('permission:payroll_period_manage');
        
        // Lệnh chạy sinh lương tự động (Chỉ HR được chạy)
        Route::post('payslips/generate', [PayslipController::class, 'generate'])->name('payslips.generate')->middleware('permission:payslip_calculate');
    });

    // ==========================================
    // 3. NHÓM DÙNG CHUNG (Cả HR Manager và Employee đều được truy cập)
    // ==========================================
    Route::middleware(['role:HR Manager|Employee|Super Admin'])->group(function () {
        
        // Xem danh sách nhân viên (Sẽ được lọc trong Controller)
        Route::resource('employees', EmployeeController::class)->middleware('permission:employee_manage');
        Route::put('employees/{id}/restore', [EmployeeController::class, 'restore'])->name('employees.restore')->middleware('permission:employee_manage');
        Route::delete('employees/{id}/force-delete', [EmployeeController::class, 'forceDelete'])->name('employees.force-delete')->middleware('permission:employee_manage');

        // Yêu cầu nghỉ phép & Số dư nghỉ phép
        Route::prefix('leaves')->name('leaves.')->group(function () {
            Route::resource('requests', LeaveRequestController::class)->parameters(['requests' => 'leaveRequest']);
            Route::resource('balances', LeaveBalanceController::class);
        });

        // Cơ cấu lương & Phiếu lương cá nhân
        Route::resource('salary-structures', EmployeeSalaryStructureController::class)->parameters(['salary-structures' => 'salaryStructure']);
        Route::resource('payslips', PayslipController::class)->parameters(['payslips' => 'payslip']);
    });
});

require __DIR__.'/settings.php';