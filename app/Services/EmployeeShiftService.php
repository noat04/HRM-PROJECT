<?php

namespace App\Services;

use App\Models\EmployeeShift;
use Carbon\Carbon;
use Exception;
use DB;

class EmployeeShiftService
{
    /**
     * Logic HR phân ca làm việc mới cho Nhân viên
     */
    public function assignShift(array $data)
    {
        return DB::transaction(function () use ($data) {
            $employeeId = $data['employee_id'];
            $newShiftId = $data['shift_id'];
            $startDate  = Carbon::parse($data['start_date']);
            $endDate    = $data['end_date'] ? Carbon::parse($data['end_date']) : null;

            // 1. Kiểm tra xem ngày kết thúc có hợp lệ không (nếu có)
            if ($endDate && $endDate->lessThan($startDate)) {
                throw new Exception("Ngày kết thúc ca không được nhỏ hơn ngày bắt đầu ca!");
            }

            // 2. Tìm ca đang chạy của nhân viên tại ngày bắt đầu ca mới
            $activeShift = EmployeeShift::activeAt($employeeId, $startDate->format('Y-m-d'))->first();

            if ($activeShift) {
                // Nếu ca cũ là ca vô thời hạn (end_date là null) -> Tự động đóng ca cũ lại vào ngày hôm trước
                if (is_null($activeShift->end_date)) {
                    $activeShift->update([
                        'end_date' => $startDate->copy()->subDay()->format('Y-m-d')
                    ]);
                } else {
                    // Nếu ca cũ có ngày kết thúc cố định -> Báo lỗi xung đột lịch trình
                    throw new Exception("Xung đột lịch trình! Nhân viên đang có ca làm việc cố định kéo dài đến ngày " . $activeShift->end_date->format('d/m/Y'));
                }
            }

            // 3. Kiểm tra xem có ca nào trong tương lai bị trùng lặp không
            $futureConflict = EmployeeShift::where('employee_id', $employeeId)
                ->where('start_date', '>=', $startDate->format('Y-m-d'))
                ->exists();

            if ($futureConflict) {
                throw new Exception("Không thể phân ca! Đã tồn tại cấu hình phân ca của nhân viên này trong tương lai.");
            }

            // 4. Tạo bản ghi phân ca mới
            return EmployeeShift::create([
                'employee_id' => $employeeId,
                'shift_id'    => $newShiftId,
                'start_date'  => $startDate->format('Y-m-d'),
                'end_date'    => $endDate ? $endDate->format('Y-m-d') : null,
            ]);
        });
    }
}