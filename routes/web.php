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
        Route::put('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');
        Route::put('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);

        Route::put('roles/{id}/restore', [RoleController::class, 'restore'])->name('roles.restore');
    });

    // ==========================================
    // 2. NHÓM CHỈ DÀNH CHO HR MANAGER (Cấu hình hệ thống)
    // ==========================================
    Route::middleware(['role:HR Manager'])->group(function () {
        Route::resource('departments', DepartmentController::class);
        Route::resource('positions', PositionController::class);
        Route::resource('shifts', ShiftController::class);
        
        // Quản lý loại nghỉ phép
        Route::prefix('leaves')->name('leaves.')->group(function () {
            Route::resource('types', LeaveTypeController::class);
        });

        // Quản lý thành phần lương & Kỳ lương
        Route::resource('salary-components', SalaryComponentController::class)->parameters(['salary-components' => 'salaryComponent']);
        Route::resource('payroll-periods', PayrollPeriodController::class)->parameters(['payroll-periods' => 'payrollPeriod']);
        
        // Lệnh chạy sinh lương tự động (Chỉ HR được chạy)
        Route::post('payslips/generate', [PayslipController::class, 'generate'])->name('payslips.generate');
    });

    // ==========================================
    // 3. NHÓM DÙNG CHUNG (Cả HR Manager và Employee đều được truy cập)
    // ==========================================
    Route::middleware(['role:HR Manager|Employee'])->group(function () {
        
        // Xem danh sách nhân viên (Sẽ được lọc trong Controller)
        Route::resource('employees', EmployeeController::class);

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