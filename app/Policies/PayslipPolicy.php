<?php

namespace App\Policies;

use App\Models\Payslip;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PayslipPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Payslip $payslip): bool
    {
        // 1. Nếu là Super Admin hoặc HR thì luôn có quyền xem mọi phiếu lương
        if ($user->hasRole(['Super Admin', 'HR Manager'])) {
            return true;
        }

        // 2. Nếu là nhân viên thường, chỉ cho xem nếu user_id của họ khớp với user_id của hồ sơ nhân viên
        // (Giả định bảng 'employees' của bạn có cột 'user_id' để liên kết với tài khoản đăng nhập)
        return $user->id === $payslip->employee->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Payslip $payslip): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Payslip $payslip): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Payslip $payslip): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Payslip $payslip): bool
    {
        return false;
    }
}
