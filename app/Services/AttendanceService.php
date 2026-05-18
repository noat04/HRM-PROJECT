<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Exception;

class AttendanceService
{
    /**
     * LOGIC CHECK-IN THÔNG MINH (Kết hợp lịch phân ca)
     */
    public function checkIn(Employee $employee, $ipAddress)
    {
        $today = now()->format('Y-m-d');
        $now = now();

        // 1. Kiểm tra xem hôm nay đã chấm công chưa
        $existingAttendance = Attendance::where('employee_id', $employee->id)
                                        ->where('date', $today)
                                        ->first();

        if ($existingAttendance) {
            throw new \Exception("Bạn đã chấm công (Check-in) ngày hôm nay rồi!");
        }

        // 2. Lấy ca làm việc được phân
        $employeeShift = \App\Models\EmployeeShift::activeAt($employee->id, $today)->first();

        if (!$employeeShift || !$employeeShift->shift) {
            throw new \Exception("Lỗi: Bạn chưa được HR phân ca làm việc cho ngày hôm nay!");
        }

        $shift = $employeeShift->shift;
        $shiftStartTime = \Carbon\Carbon::parse($today . ' ' . $shift->start_time);
        
        // ==========================================================
        // 👇 BỔ SUNG: CẤU HÌNH KHÓA CHẶN CHECK-IN QUÁ SỚM
        // ==========================================================
        $maxEarlyMinutes = 60; // Quy định: Chỉ cho phép check-in trước ca tối đa 60 phút
        $earliestAllowedTime = $shiftStartTime->copy()->subMinutes($maxEarlyMinutes);

        if ($now->lessThan($earliestAllowedTime)) {
            throw new \Exception("Chưa đến thời gian ghi danh! Đối với " . $shift->name . ", bạn chỉ có thể check-in sớm tối đa " . $maxEarlyMinutes . " phút (Từ lúc " . $earliestAllowedTime->format('H:i') . ").");
        }
        // ==========================================================

        $gracePeriod = 15; // Cho phép trễ 15 phút
        $lateMinutes = 0;
        $status = 'on_time';

        // 3. Tính toán đi muộn
        if ($now->greaterThan($shiftStartTime->copy()->addMinutes($gracePeriod))) {
            $rawLateMinutes = $shiftStartTime->diffInMinutes($now);
            $lateMinutes = (int) round($rawLateMinutes);
            $status = 'late';
        }

        // 4. Lưu Database
        return Attendance::create([
            'employee_id'  => $employee->id,
            'shift_id'     => $shift->id,
            'date'         => $today,
            'check_in'     => $now,
            'late_minutes' => $lateMinutes,
            'status'       => $status,
            'ip_address'   => $ipAddress
        ]);
    }

   /**
     * LOGIC CHECK-OUT (Giờ ra)
     */
    public function checkOut(Employee $employee)
    {
        $today = now()->format('Y-m-d');
        $now = now();

        // 1. Tìm bản ghi chấm công hôm nay
        $attendance = Attendance::where('employee_id', $employee->id)
                                ->where('date', $today)
                                ->first();

        if (!$attendance) {
            throw new \Exception("Bạn chưa Check-in ngày hôm nay!");
        }

        if ($attendance->check_out) {
            throw new \Exception("Bạn đã Check-out ngày hôm nay rồi!");
        }

        // Lấy ca làm việc để biết giờ về chuẩn
        $employeeShift = \App\Models\EmployeeShift::activeAt($employee->id, $today)->first();
        $shiftEndTime = $employeeShift && $employeeShift->shift 
            ? \Carbon\Carbon::parse($today . ' ' . $employeeShift->shift->end_time)
            : \Carbon\Carbon::parse($today . ' 17:30:00');

        $earlyMinutes = 0;
        $status = $attendance->status; // Mặc định giữ nguyên trạng thái lúc Check-in (on_time hoặc late)

        // 2. Tính toán số phút về sớm
        if ($now->lessThan($shiftEndTime)) {
            $earlyMinutes = (int) round($now->diffInMinutes($shiftEndTime));
            
            // 👇 ĐÃ BỔ SUNG: Nếu về sớm, cập nhật trạng thái thành 'early_leave'
            // (Bạn có thể giữ nguyên 'late' nếu sáng đi muộn chiều về sớm tùy quy định công ty)
            $status = 'early_leave'; 
        }

        // 3. Cập nhật vào Database
        $attendance->update([
            'check_out'     => $now,
            'early_minutes' => $earlyMinutes,
            'status'        => $status, // 💡 Ghi đè trạng thái mới vào đây
        ]);

        return $attendance;
    }
}