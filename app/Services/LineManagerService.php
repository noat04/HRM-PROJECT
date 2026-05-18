<?php
namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Exception;
use Illuminate\Support\Facades\Auth;

class LineManagerService
{
    /**
     * Lấy ID của Trưởng phòng đang đăng nhập
     */
    private function getManagerId()
    {
        return Auth::user()->employee->id;
    }

    /**
     * 1. XEM HỒ SƠ CẤP DƯỚI (Tự động ẩn thông tin nhạy cảm)
     */
    public function getTeamMemberProfile($employeeId)
    {
        // Chỉ lấy nhân viên nếu manager_id của họ khớp với ID của Trưởng phòng này
        $employee = Employee::where('id', $employeeId)
                            ->where('manager_id', $this->getManagerId())
                            ->first();

        if (!$employee) {
            throw new Exception("Bạn không có quyền xem hồ sơ của nhân viên này hoặc nhân viên không tồn tại.");
        }

        // Tự động ẩn các trường nhạy cảm bằng hàm makeHidden() của Eloquent
        $employee->makeHidden([
            'bank_account_number', 
            'bank_name', 
            'basic_salary', // Giả sử bạn có cột lương
            'identity_card_number'
        ]);

        return $employee;
    }

    /**
     * 2. XEM BÁO CÁO CHẤM CÔNG CỦA TEAM
     */
    public function getTeamAttendanceReport($startDate, $endDate)
    {
        // Dùng whereHas để lọc qua bảng quan hệ: Chỉ lấy chấm công của những người là lính của Manager này
        return Attendance::with('employee:id,full_name,employee_code') // Eager load để tối ưu truy vấn
            ->whereHas('employee', function ($query) {
                $query->where('manager_id', $this->getManagerId());
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * 3. PHÊ DUYỆT ĐƠN NGHỈ PHÉP / TĂNG CA
     */
    public function reviewLeaveRequest($requestId, $action)
    {
        // Đảm bảo Manager chỉ có thể duyệt đơn của lính mình
        $leaveRequest = LeaveRequest::whereHas('employee', function ($query) {
            $query->where('manager_id', $this->getManagerId());
        })->find($requestId);

        if (!$leaveRequest) {
            throw new Exception("Không tìm thấy yêu cầu hoặc bạn không có thẩm quyền duyệt đơn này.");
        }

        // Kiểm tra xem đơn đã được duyệt/từ chối trước đó chưa
        if ($leaveRequest->status !== 'pending') {
            throw new Exception("Đơn này đã được xử lý trước đó.");
        }

        // Cập nhật trạng thái
        $status = $action === 'approve' ? 'approved' : 'rejected';
        
        $leaveRequest->update([
            'status' => $status,
            'approved_by' => $this->getManagerId(),
            'approved_at' => now(),
        ]);

        // Ghi Log (Có thể dùng Spatie Activitylog như bạn đã setup)
        activity()
            ->causedBy(Auth::user())
            ->performedOn($leaveRequest)
            ->log("Line Manager {$action} leave request");

        return $leaveRequest;
    }
}