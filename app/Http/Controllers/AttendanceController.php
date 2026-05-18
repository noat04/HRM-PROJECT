<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AttendanceService;
use Exception;
use Inertia\Inertia;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    // 1. HIỂN THỊ MÀN HÌNH CHẤM CÔNG
    public function index()
    {
        $user = auth()->user();
        $employee = $user->employee;
        // 👇 CHÈN DÒNG NÀY VÀO ĐỂ KIỂM TRA
        // dd([
        //    'current_user_id' => $user->id,
        //    'linked_employee' => $employee
        // ]);
        // 👇 BƯỚC PHÒNG VỆ: Chặn lỗi sập web
        // 👇 ĐÃ SỬA: Thay đổi ->dashboard() thành ->route('dashboard')
        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'Tài khoản của bạn chưa được liên kết với hồ sơ nhân sự để sử dụng tính năng Chấm công!');
        }
        
        // Lấy lịch sử chấm công của tháng này
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', now()->month)
            ->orderBy('date', 'desc')
            ->paginate(15);

        // Lấy trạng thái chấm công của ngày hôm nay
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', now()->format('Y-m-d'))
            ->first();

        return Inertia::render('Employees/Attendance/Index', [
            'attendances' => $attendances,
            'todayAttendance' => $todayAttendance
        ]);
    }

    // 2. XỬ LÝ CHECK-IN
    public function checkIn(Request $request)
    {
        $employee = auth()->user()->employee;
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Lỗi: Không tìm thấy hồ sơ nhân viên!');
        }

        try {
            $this->attendanceService->checkIn($employee, $request->ip());
            return redirect()->back()->with('success', 'Check-in thành công! Chúc bạn ngày mới làm việc hiệu quả.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // 3. XỬ LÝ CHECK-OUT
    public function checkOut(Request $request)
    {
        $employee = auth()->user()->employee;
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Lỗi: Không tìm thấy hồ sơ nhân viên!');
        }
        
        try {
            $this->attendanceService->checkOut($employee);
            return redirect()->back()->with('success', 'Check-out thành công! Nghỉ ngơi thôi nào.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}