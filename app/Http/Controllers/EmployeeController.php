<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Crud\Employee\CreateEmployeeRequest;
use App\Http\Requests\Crud\Employee\UpdateEmployeeRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        // 1. TẠO LUỒNG TRUY VẤN (TUYỆT ĐỐI KHÔNG DÙNG ->get() Ở ĐÂY)
        $query = Employee::query();
        $restoreQuery = Employee::onlyTrashed();

        // 2. LẤY DỮ LIỆU ĐỔ VÀO BỘ LỌC
        $departments = Department::all();
        $positions = Position::all();

        // 3. LỌC THEO TỪ KHÓA (Tìm theo Tên hoặc Mã NV)
        if ($request->filled('search')) {
            $searchTerm = "%{$request->search}%";
            
            // Hàm ẩn danh (Closure) để gom nhóm điều kiện OR (WHERE name LIKE %..% OR code LIKE %..%)
            $query->where(function($q) use ($searchTerm) {
                $q->where('full_name', 'like', $searchTerm)
                  ->orWhere('employee_code', 'like', $searchTerm);
            });
            
            $restoreQuery->where(function($q) use ($searchTerm) {
                $q->where('full_name', 'like', $searchTerm)
                  ->orWhere('employee_code', 'like', $searchTerm);
            });
        }

        // 4. LỌC THEO PHÒNG BAN
        if ($request->filled('department_id')) {
            $deptArray = explode(',', $request->department_id);
            $query->whereIn('department_id', $deptArray);
            $restoreQuery->whereIn('department_id', $deptArray);
        }

        // 5. LỌC THEO CHỨC VỤ
        if ($request->filled('position_id')) {
            $posArray = explode(',', $request->position_id);
            $query->whereIn('position_id', $posArray);
            $restoreQuery->whereIn('position_id', $posArray);
        }

        // 6. LỌC THEO GIỚI TÍNH
        if ($request->filled('gender')) {
            $genderArray = explode(',', $request->gender);
            $query->whereIn('gender', $genderArray);
            $restoreQuery->whereIn('gender', $genderArray);
        }

        // 7. LỌC THEO TRẠNG THÁI
        if ($request->filled('status')) {
            $statusArray = explode(',', $request->status);
            $query->whereIn('status', $statusArray);
            $restoreQuery->whereIn('status', $statusArray);
        }

        // 8. TRẢ KẾT QUẢ VỀ FRONTEND
        return Inertia::render('Employees/Index', [
            'employees' => $query->latest()->paginate(10)->withQueryString(),
            'restore' => ['data' => $restoreQuery->latest()->get()], // Bọc mảng data chuẩn chỉ
            'departments' => $departments,
            'positions' => $positions,
            // Khai báo đủ các tham số để Vue nhận diện và giữ trạng thái checkbox
            'filters' => $request->only(['search', 'department_id', 'position_id', 'gender', 'status']),
            'is_trashed' => $request->is_trashed === 'true' ? 'true' : 'false'
        ]);
    }

    public function create()
    {
        $employees = Employee::all();
        $departments = Department::all();
        $positions = Position::all();
        $users = User::all();
        return Inertia::render('Employees/Create', [
            'employees' => $employees,
            'departments' => $departments,
            'positions' => $positions,
            'users' => $users,
        ]);
    }

    public function store(CreateEmployeeRequest $request)
    {
        $validated = $request->validated();

        Employee::create($validated);

        return redirect()->route('employees.index');
    }

    public function show(Employee $employee)
    {
        return Inertia::render('Employees/Show', [
            'employee' => $employee,
        ]);
    }

    public function edit(Employee $employee)
    {
        $employees = Employee::all();
        $departments = Department::all();
        $positions = Position::all();
        $users = User::all();
        return Inertia::render('Employees/Edit', [
            'employee' => $employee,
            'employees' => $employees,
            'departments' => $departments,
            'positions' => $positions,
            'users' => $users,
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $validated = $request->validated();

        $employee->update($validated);

        return redirect()->route('employees.index');
    }

    public function destroy(Employee $employee)
    {
        $employee->status = 'paused';
        $user = User::find($employee->user_id);
        $user->status = 'inactive';
        $user->save();
        $employee->save();
        $employee->delete();

        return redirect()->route('employees.index');
    }

    public function restore($id)
    {
        $employee = Employee::withTrashed()->find($id);
        if ($employee) {
            $employee->status = 'probation';
            $user = User::find($employee->user_id);
            $user->status = 'active';
            $user->save();
            $employee->save();
            $employee->restore();
        }
        return redirect()->route('employees.index');
    }

    public function forceDelete($id)
    {
        $employee = Employee::withTrashed()->find($id);
        if ($employee) {
            $employee->forceDelete();
        }
        return redirect()->route('employees.index');
    }

    // ... Các hàm khác giữ nguyên ...

    public function bulkDelete(Request $request) {
        $request->validate(['ids' => 'required|array']);
        
        // 1. Lấy danh sách nhân viên cần xóa
        $employees = Employee::whereIn('id', $request->ids)->get();
        
        foreach ($employees as $employee) {
            // 2. Cập nhật trạng thái
            $employee->status = 'paused'; // Hoặc 'resigned' tùy logic của bạn
            $employee->save();

            // 3. Khóa tài khoản User tương ứng
            if ($employee->user_id) {
                $user = User::find($employee->user_id);
                if ($user) {
                    $user->status = 'inactive';
                    $user->save();
                }
            }

            // 4. Xóa mềm (Có kích hoạt Event để ghi Log)
            $employee->delete();
        }

        return redirect()->back()->with('success', 'Tuyệt vời! Đã xóa thành công ' . count($request->ids) . ' nhân viên.');
    }

    public function bulkRestore(Request $request) {
        $request->validate(['ids' => 'required|array']);
        
        $employees = Employee::onlyTrashed()->whereIn('id', $request->ids)->get();
        
        foreach ($employees as $employee) {
            // 1. Khôi phục trạng thái
            $employee->status = 'probation'; // Hoặc trạng thái mặc định của bạn
            $employee->save();

            // 2. Mở khóa tài khoản User
            if ($employee->user_id) {
                $user = User::find($employee->user_id);
                if ($user) {
                    $user->status = 'active';
                    $user->save();
                }
            }

            // 3. Khôi phục (Có kích hoạt Event)
            $employee->restore();
        }

        return redirect()->back()->with('success', 'Tuyệt vời! Đã khôi phục thành công ' . count($request->ids) . ' nhân viên.');
    }

    public function bulkForceDelete(Request $request) {
        $request->validate(['ids' => 'required|array']);
        
        // Xóa vĩnh viễn thì có thể dùng query builder cho nhanh
        // Lưu ý: Tùy logic hệ thống, bạn có muốn xóa luôn cả bảng User không?
        // Nếu có, bạn phải lấy danh sách user_id ra và xóa trước.
        Employee::onlyTrashed()->whereIn('id', $request->ids)->forceDelete();
        
        return redirect()->back()->with('success', 'Tuyệt vời! Đã xóa vĩnh viễn ' . count($request->ids) . ' nhân viên.');
    }
}
