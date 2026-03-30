<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Kiểm tra nếu người dùng đã đăng nhập
        if (auth()->check()) {
            $user = auth()->user();
            
            // 2. Kiểm tra nếu tài khoản bị khóa (is_active = 0)
            if ($user->status == 'lock') {
                // Đăng xuất người dùng
                auth()->logout();
                
                // Xóa session
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Chuyển hướng về trang đăng nhập kèm thông báo lỗi
                return redirect()->route('login')
                    ->with('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Quản trị viên.');
            }
        }

        return $next($request);
    }
}
