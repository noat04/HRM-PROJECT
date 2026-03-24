<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\LeaveType;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::paginate(10);
        return Inertia::render('LeaveTypes/Index', [
            'leaveTypes' => $leaveTypes,
        ]);
    }

    public function create()
    {
        return Inertia::render('LeaveTypes/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:leave_types,name',
            'code' => 'required|string|max:255|unique:leave_types,code',
            'is_paid' => 'required|boolean',
            'days_allowed' => 'required|integer',
            'description' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        LeaveType::create($request->all());

        return redirect()->route('leaves.types.index');
    }

   // SỬA Ở ĐÂY: Đổi LeaveType $leaveType -> LeaveType $type
    public function show(LeaveType $type)
    {
        return Inertia::render('LeaveTypes/Show', [
            'leaveType' => $type,
        ]);
    }

    // SỬA Ở ĐÂY
    public function edit(LeaveType $type)
    {
        return Inertia::render('LeaveTypes/Edit', [
            'leaveType' => $type,
        ]);
    }

    // SỬA Ở ĐÂY
    public function update(Request $request, LeaveType $type)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'is_paid' => 'required|boolean',
            'days_allowed' => 'required|integer',
            'description' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $type->update($request->all());

        return redirect()->route('leaves.types.index');
    }

    // SỬA Ở ĐÂY
    public function destroy(LeaveType $type)
    {
        $type->delete();

        return redirect()->route('leaves.types.index');
    }
}
