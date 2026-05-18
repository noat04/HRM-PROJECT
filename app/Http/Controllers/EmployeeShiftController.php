<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EmployeeShiftService;
use App\Models\EmployeeShift;
use App\Models\Employee;
use App\Models\Shift;
use Exception;
use Inertia\Inertia;

class EmployeeShiftController extends Controller
{
    protected $shiftService;

    public function __construct(EmployeeShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    // Hiển thị danh sách lịch sử phân ca
    public function index(Request $request)
    {
        $query = EmployeeShift::with(['employee:id,full_name,employee_code', 'shift:id,name,start_time,end_time']);

        // Tìm kiếm theo tên hoặc mã nhân viên
        if ($request->filled('search')) {
            $searchTerm = "%{$request->search}%";
            $query->whereHas('employee', function ($q) use ($searchTerm) {
                $q->where('full_name', 'like', $searchTerm)
                  ->orWhere('employee_code', 'like', $searchTerm);
            });
        }

        $assignments = $query->latest('start_date')->paginate(10)->withQueryString();

        // Lấy danh sách rút gọn phục vụ cho ô Select ở Form tạo mới
        $employees = Employee::select('id', 'full_name', 'employee_code')->whereNull('resignation_date')->get();
        $shifts = Shift::select('id', 'name', 'start_time', 'end_time')->get();

        return Inertia::render('EmployeeShift/Index', [
            'assignments' => $assignments,
            'employees'   => $employees,
            'shifts'      => $shifts,
            'filters'     => $request->only('search')
        ]);
    }

    // Lưu phân ca mới
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'shift_id'    => 'required|exists:shifts,id',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date',
        ]);

        try {
            $this->shiftService->assignShift($validated);
            return redirect()->back()->with('success', 'Phân ca làm việc cho nhân viên thành công!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // Xóa lịch sử phân ca (Thu hồi lệnh phân ca)
    public function destroy($id)
    {
        $assignment = EmployeeShift::findOrFail($id);
        $assignment->delete();

        return redirect()->back()->with('success', 'Đã hủy lệnh phân ca thành công.');
    }
}