<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LeaveBalanceController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::put('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');
    Route::put('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');


    Route::resource('departments', DepartmentController::class);
    Route::resource('users', UserController::class);
    Route::resource('positions', PositionController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('shifts', ShiftController::class);

    //CẤU HÌNH NHÓM ROUTE CHO LEAVES
    Route::prefix('leaves')->name('leaves.')->group(function () {
        
        // 1. Quản lý các loại nghỉ phép (CRUD)
        // Đường dẫn: /leaves/types -> Tên route: leaves.types.index, leaves.types.create...
        //Nó sẽ tự động tạo ra 7 đường dẫn CRUD, và đối với những đường dẫn cần truyền ID, nó sẽ tự động cắt chữ "s" của từ 'types' để biến thành danh từ số ít làm tham số (parameter).
        Route::resource('types', LeaveTypeController::class);
        
        // 2. Quản lý đơn xin nghỉ phép (CRUD)
        // Đường dẫn: /leaves/requests -> Tên route: leaves.requests.index...
        Route::resource('requests', LeaveRequestController::class);
        
        // 3. Quản lý số dư ngày nghỉ của nhân viên
        // Đường dẫn: /leaves/balances -> Tên route: leaves.balances.index...
        // Thường thì balance chỉ cần xem (index, show) và cập nhật (edit, update), bạn có thể dùng except/only
        Route::resource('balances', LeaveBalanceController::class)->except(['create', 'store', 'destroy']);
        
    });
});


require __DIR__.'/settings.php';
