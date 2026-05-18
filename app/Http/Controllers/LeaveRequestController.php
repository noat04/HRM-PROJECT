<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Services\LeaveRequestService; 
use Illuminate\Support\Facades\Auth;
use Exception;

class LeaveRequestController extends Controller
{
    protected $leaveService;

    public function __construct(LeaveRequestService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    /**
     * Hiển thị danh sách đơn phép
     */
    public function index()
    {
        $currentUser = Auth::user();
        $currentEmployee = $currentUser->employee;

        $query = LeaveRequest::with(['employee:id,full_name,employee_code', 'leaveType:id,name']);

        // BẢO MẬT LOGIC: Nếu là Nhân viên thường, CHỈ cho phép xem đơn của chính mình
        if (!$currentUser->hasAnyRole(['HR Manager', 'Super Admin'])) {
            $query->where('employee_id', $currentEmployee?->id);
        }

        $leaveRequests = $query->latest()->paginate(10);

        return Inertia::render('LeaveRequests/Index', [
            'leaveRequests' => $leaveRequests,
        ]);
    }

    /**
     * Màn hình tạo đơn mới (Đã bổ sung giá trị mặc định)
     */
    public function create()
    {
        $currentUser = Auth::user();
        $currentEmployee = $currentUser->employee;

        // Load danh sách phục vụ cho ô Select
        $employees = Employee::select('id', 'full_name', 'employee_code')->get();
        $managers = Employee::select('id', 'full_name', 'employee_code')->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();

        return Inertia::render('LeaveRequests/Create', [
            'employees' => $employees,
            'managers' => $managers,
            'leaveTypes' => $leaveTypes,
            
            // 👇 BỔ SUNG QUAN TRỌNG: Truyền dữ liệu mặc định của tài khoản đang login xuống Vue
            'default_employee_id' => $currentEmployee ? $currentEmployee->id : null,
            'default_manager_id'  => $currentEmployee ? $currentEmployee->manager_id : null,
        ]);
    }

    /**
     * Lưu đơn xin nghỉ phép mới (Thắt chặt bảo mật)
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();
        $currentEmployee = $currentUser->employee;

        // 🔐 KHÓA BẢO MẬT: Nếu là Nhân viên thường, ép buộc lấy ID của chính họ và Sếp của họ
        // Tránh trường hợp dùng F12 sửa ID để nộp đơn hộ người khác hoặc chọn sai Sếp
        if (!$currentUser->hasAnyRole(['HR Manager', 'Super Admin'])) {
            if (!$currentEmployee) {
                return redirect()->back()->with('error', 'Tài khoản chưa được liên kết với hồ sơ nhân sự!');
            }
            if (!$currentEmployee->manager_id) {
                return redirect()->back()->with('error', 'Hồ sơ của bạn chưa được gán Quản lý trực tiếp để duyệt đơn. Vui lòng liên hệ HR!');
            }

            $request->merge([
                'employee_id' => $currentEmployee->id,
                'manager_id'  => $currentEmployee->manager_id,
                'status'      => 'pending' // Nhân viên tạo thì luôn là Chờ duyệt
            ]);
        }

        $validated = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'manager_id'    => 'required|exists:employees,id',
            'start_date'    => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string|max:1000',
            'status'        => 'required|string',
        ]);

        try {
            $this->leaveService->createRequest(
                $validated, 
                $request->employee_id, 
                $request->manager_id
            );

            return redirect()->route('leaves.requests.index')->with('success', 'Tạo đơn xin nghỉ phép thành công.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Màn hình chỉnh sửa đơn phép
     */
    public function edit(LeaveRequest $leaveRequest)
    {
        $managers = Employee::select('id', 'full_name', 'employee_code')->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        
        return Inertia::render('LeaveRequests/Edit', [
            'leaveRequest' => $leaveRequest,
            'managers'     => $managers, 
            'leaveTypes'   => $leaveTypes,
        ]);
    }

    /**
     * Cập nhật thông tin đơn phép
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $validated = $request->validate([
            'employee_id'      => 'required|exists:employees,id',
            'leave_type_id'    => 'required|exists:leave_types,id',
            'manager_id'       => 'required|exists:employees,id',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'reason'           => 'required|string',
            'status'           => 'required|string',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:255', 
        ]);

        try {
            if ($request->filled('status') && $request->status !== $leaveRequest->status->value) {
                $this->leaveService->reviewRequest($leaveRequest->id, $request->status, $request->rejection_reason);
            } else {
                $leaveRequest->update($validated);
            }

            return redirect()->route('leaves.requests.index')->with('success', 'Cập nhật đơn phép thành công.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Xem chi tiết đơn phép
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['employee', 'leaveType', 'manager']);
        return Inertia::render('LeaveRequests/Show', [
            'leaveRequest' => $leaveRequest,
        ]);
    }

    /**
     * Xóa đơn phép
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        $leaveRequest->delete();
        return redirect()->route('leaves.requests.index')->with('success', 'Đã xóa đơn xin nghỉ phép thành công.');
    }
}