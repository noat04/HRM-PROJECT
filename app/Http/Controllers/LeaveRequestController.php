<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Auth;
class LeaveRequestController extends Controller
{
    public function index()
    {
        $leaveRequests = LeaveRequest::paginate(10);
        return Inertia::render('LeaveRequests/Index', [
            'leaveRequests' => $leaveRequests,
        ]);
    }

    public function create()
    {
        $employees = Employee::all();
        $managers = Employee::where('manager_id', Auth::id())->get();
        $leaveTypes = LeaveType::all();
        return Inertia::render('LeaveRequests/Create', [
            'employees' => $employees,
            'managers' => $managers,
            'leaveTypes' => $leaveTypes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'manager_id' => 'required', // Bắt buộc phải có người duyệt đơn
            'leave_type_id' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_days' => 'required|numeric|min:1',
            'reason' => 'required|string',
            'status' => 'required',
        ]);

        LeaveRequest::create($request->all());

        return redirect()->route('leaves.requests.index');
    }

    // 👇 1. Đổi tên biến thành $leaveRequest
    public function show(LeaveRequest $leaveRequest)
    {
        return Inertia::render('LeaveRequests/Show', [
            'leaveRequest' => $leaveRequest,
        ]);
    }

    // 👇 2. Đổi tên biến thành $leaveRequest
    public function edit(LeaveRequest $leaveRequest)
    {
        $managers = Employee::where('manager_id', Auth::id())->get();
        $leaveTypes = LeaveType::all();
        
        return Inertia::render('LeaveRequests/Edit', [
            'leaveRequest' => $leaveRequest,
            'managers' => $managers, // 👈 QUAN TRỌNG: Bạn đã quên dòng này ở code cũ
            'leaveTypes' => $leaveTypes,
        ]);
    }

    // 👇 3. Đổi tên biến thành $leaveRequest
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'employee_id' => 'required',
            'leave_type_id' => 'required',
            'manager_id' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_days' => 'required|numeric|min:1',
            'reason' => 'required',
            'status' => 'required',
            // Lưu ý: Không nên bắt buộc rejection_reason khi vừa mới update trừ khi status là rejected
        ]);

        $leaveRequest->update($request->all());

        return redirect()->route('leaves.requests.index'); // Chú ý: Code cũ của bạn viết sai chính tả chỗ này (leaves.equests)
    }

    // 👇 4. Đổi tên biến thành $leaveRequest
    public function destroy(LeaveRequest $leaveRequest)
    {
        $leaveRequest->delete();

        return redirect()->route('leaves.requests.index');
    }
}
