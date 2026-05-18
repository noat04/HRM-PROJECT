<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Enums\LeaveStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class LeaveRequestService
{
    /**
     * LOGIC: Nhân viên đăng ký nghỉ phép
     */
    public function createRequest(array $data, int $employeeId, int $managerId)
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $year = $startDate->year;

        // 1. Tính tổng số ngày nghỉ thực tế (Ví dụ đơn giản: Khoảng cách ngày + 1)
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // 2. Chống Race Condition ngay từ lúc tạo: Sử dụng Transaction để kiểm tra số dư phép
        return DB::transaction(function () use ($data, $employeeId, $managerId, $startDate, $endDate, $totalDays, $year) {
            
            // Kiểm tra trùng lịch bằng Scope Overlapping bạn đã viết
            if (LeaveRequest::overlapping($employeeId, $data['start_date'], $data['end_date'])->exists()) {
                throw new Exception("Bạn đã có một đơn xin nghỉ phép khác trùng lịch vào khoảng thời gian này!");
            }

            // Tìm số dư phép của năm đó và Khóa dòng dữ liệu lại để đọc giá trị chính xác nhất
            $balance = LeaveBalance::where('employee_id', $employeeId)
                ->where('leave_type_id', $data['leave_type_id'])
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (!$balance || !$balance->hasEnoughBalance($totalDays)) {
                throw new Exception("Số dư ngày phép của bạn không đủ để thực hiện yêu cầu này (Cần: {$totalDays} ngày).");
            }

            // Tạo đơn ở trạng thái PENDING (Chờ duyệt) - Chưa trừ phép trực tiếp, sếp duyệt mới trừ
            return LeaveRequest::create([
                'employee_id' => $employeeId,
                'leave_type_id' => $data['leave_type_id'],
                'manager_id' => $managerId,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'total_days' => $totalDays,
                'reason' => $data['reason'],
                'status' => LeaveStatus::PENDING,
            ]);
        });
    }

    /**
     * KỸ THUẬT PHÒNG PHỎNG VẤN: Xử lý duyệt đơn chống Race Condition tuyệt đối
     */
    public function reviewRequest(int $requestId, string $status, ?string $rejectionReason = null)
    {
        // Sử dụng Database Transaction
        return DB::transaction(function () use ($requestId, $status, $rejectionReason) {
            
            // 1. Khóa bản ghi đơn phép này lại, không cho tiến trình khác chỉnh sửa cùng lúc
            $request = LeaveRequest::where('id', $requestId)->lockForUpdate()->firstOrFail();

            // Nếu đơn đã được duyệt hoặc từ chối trước đó bởi một quản lý khác -> Chặn lại ngay
            if ($request->status !== LeaveStatus::PENDING) {
                throw new Exception("Đơn xin nghỉ phép này đã được xử lý từ trước!");
            }

            $year = Carbon::parse($request->start_date)->year;

            // 2. Nếu Sếp Bấm DUYỆT (Approved)
            if ($status === 'approved') {
                // Khóa dòng số dư phép của nhân viên để đảm bảo tính toán an toàn
                $balance = LeaveBalance::where('employee_id', $request->employee_id)
                    ->where('leave_type_id', $request->leave_type_id)
                    ->where('year', $year)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Kiểm tra lại một lần nữa phòng trường hợp nhân viên vừa tiêu hết phép ở một đơn khác
                if (!$balance->hasEnoughBalance($request->total_days)) {
                    throw new Exception("Không thể phê duyệt! Số dư ngày phép của nhân viên hiện tại không đủ.");
                }

                // Cập nhật số ngày đã dùng (Hàm boot() của LeaveBalance sẽ tự trừ remaining_days)
                $balance->used_days += $request->total_days;
                $balance->save();

                $request->status = LeaveStatus::APPROVED;
            } 
            // 3. Nếu Sếp Bấm TỪ CHỐI (Rejected)
            else {
                $request->status = LeaveStatus::REJECTED;
                $request->rejection_reason = $rejectionReason;
            }

            $request->responded_at = now();
            $request->save();

            return $request;
        });
    }
}