<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService; // Import class Đầu bếp

class AuthController extends Controller
{
    // 1. Khai báo 1 biến chứa Service
    protected $authService;

    // 2. DEPENDENCY INJECTION (Tiêm qua Constructor)
    // Laravel Service Container sẽ tự động đọc tham số này và khởi tạo AuthService cho bạn
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * API: POST /login
     */
    public function login(Request $request)
    {
        // validate() là hàm của Laravel dùng để kiểm tra dữ liệu hợp lệ hay không.
        // Nếu không hợp lệ:
            // Laravel tự động trả về lỗi 422
            // trả về message lỗi JSON hoặc redirect về form.
        // Nếu hợp lệ:
            // dữ liệu sẽ được trả về và gán vào $credentials.
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Gọi Đầu bếp ra làm việc
        $result = $this->authService->login($credentials);

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => $result
        ]);
    }

    /**
     * API: POST /logout
     */
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công'
        ]);
    }

    /**
     * API: GET /me
     */
    public function me(Request $request)
    {
        $result = $this->authService->getMe($request->user());

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
}