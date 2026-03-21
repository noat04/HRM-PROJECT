<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * 1. Xử lý Đăng nhập (Login)
     */
    public function login(array $credentials): array
    {
        // Tìm user theo email
        $user = User::where('email', $credentials['email'])->first();

        // Kiểm tra User có tồn tại và Mật khẩu có khớp không
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            // Ném lỗi về cho Controller xử lý (HTTP 401)
            throw ValidationException::withMessages([
                'email' => ['Email hoặc mật khẩu không chính xác.'],
            ]);
        }

        // Kiểm tra tài khoản có bị khóa không (Cột is_active ta vừa thêm)
        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ HR.'],
            ]);
        }

        // Xóa token cũ (Tùy chọn: Nếu muốn giới hạn 1 thiết bị đăng nhập)
        // $user->tokens()->delete();

        // Tạo Token mới bằng Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(), // Lấy role đầu tiên
            ]
        ];
    }

    /**
     * 2. Xử lý Đăng xuất (Logout)
     */
    public function logout(User $user): bool
    {
        // Xóa Token hiện tại đang được sử dụng để gọi API này
        return $user->currentAccessToken()->delete();
    }

    /**
     * 3. Lấy thông tin User hiện tại (Me)
     */
    public function getMe(User $user): array
    {
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            // Gọi Spatie để lấy danh sách mảng các quyền (vd: ['view_salary', 'check_in'])
            'permissions' => $user->getAllPermissions()->pluck('name'),
            // Lấy thêm Role
            'roles' => $user->getRoleNames(),
            // Load thông tin hồ sơ nhân viên (Nếu User này là Nhân viên)
            'employee' => $user->employee, 
        ];
    }

    /**
     * 4. Đổi mật khẩu (Change Password)
     */
    public function changePassword(User $user, array $data): bool
    {
        // 1. Kiểm tra mật khẩu cũ có đúng không
        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Mật khẩu hiện tại không chính xác.'],
            ]);
        }

        // 2. Cập nhật mật khẩu mới (nhớ mã hóa Hash)
        $user->password = Hash::make($data['new_password']);
        $user->save();

        // 3. (Tùy chọn) Đăng xuất khỏi các thiết bị khác sau khi đổi mật khẩu
        // $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return true;
    }
}
