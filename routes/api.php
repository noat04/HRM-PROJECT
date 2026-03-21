<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Nơi đây đăng ký tất cả các endpoint của dự án.
| Mặc định, các route ở đây sẽ có tiền tố là /api (được cấu hình trong bootstrap/app.php hoặc RouteServiceProvider)
*/

Route::prefix('v1')->group(function () {
    
    // ==========================================
    // NHÓM PUBLIC (Không cần đăng nhập)
    // ==========================================
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
    });

    // ==========================================
    // NHÓM PROTECTED (Bắt buộc phải có Token)
    // ==========================================
    Route::middleware('auth:sanctum')->group(function () {
        
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            // Route::put('/password', [AuthController::class, 'changePassword']); // Nếu bạn đã viết hàm này trong Controller
        });

        // Tương lai bạn sẽ viết các Route quản lý nhân sự ở đây
        // Route::apiResource('employees', EmployeeController::class);
        // Route::apiResource('departments', DepartmentController::class);
    });

});


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
