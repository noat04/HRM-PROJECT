<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\LeaveBalance;
use App\Models\Employee;
use App\Models\LeaveType;

class LeaveBalanceController extends Controller
{
    public function index()
    {
        $leaveBalances =LeaveBalance::paginate(10);
        return Inertia::render('LeaveBalances/Index', [
            'leaveBalances' => $leaveBalances,
        ]);
    }

    public function create()
    {
        $employees = Employee::all();
        $leaveTypes = LeaveType::all();
        return Inertia::render('LeaveBalances/Create', [
            'employees' => $employees,
            'leaveTypes' => $leaveTypes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'leave_type_id' => 'required',
            'year' => 'required',
            'total_days' => 'required',
            'used_days' => 'required',
            'remaining_days' => 'required',
        ]);

        LeaveBalance::create($request->all());

        return redirect()->route('leaves.balances.index');
    }

   // BẮT BUỘC SỬA: Đổi $leaveBalance thành $balance
    public function show(LeaveBalance $balance)
    {
        return Inertia::render('LeaveBalances/Show', [
            'leaveBalance' => $balance, // Trả về Vue vẫn giữ tên leaveBalance cho khỏi lỗi giao diện
        ]);
    }

    public function edit(LeaveBalance $balance)
    {
        $employees = Employee::all();
        $leaveTypes = LeaveType::all();
        return Inertia::render('LeaveBalances/Edit', [
            'leaveBalance' => $balance, 
            'employees' => $employees,
            'leaveTypes' => $leaveTypes,
        ]);
    }

    public function update(Request $request, LeaveBalance $balance)
    {
        $request->validate([
             'employee_id' => 'required',
            'leave_type_id' => 'required',
            'year' => 'required',
            'total_days' => 'required',
            'used_days' => 'required',
            'remaining_days' => 'required',
        ]);

        $balance->update($request->all());

        return redirect()->route('leaves.balances.index');
    }

    public function destroy(LeaveBalance $balance)
    {
        $balance->delete();

        return redirect()->route('leaves.balances.index');
    }
}
