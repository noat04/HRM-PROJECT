<?php

namespace App\Http\Controllers;

use App\Models\EmployeeSalaryStructure;
use App\Models\Employee;
use App\Models\SalaryComponent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeSalaryStructureController extends Controller
{
    public function index()
    {
        // Nạp sẵn quan hệ employee và component để hiển thị tên ra bảng
        $salaryStructures = EmployeeSalaryStructure::with(['employee', 'component'])
            ->latest()
            ->paginate(10);
            
        return Inertia::render('EmployeeSalaryStructures/Index', [
            'salaryStructures' => $salaryStructures,
        ]);
    }

    public function create()
    {
        $employees = Employee::all();
        $components = SalaryComponent::active()->get(); // Chỉ lấy các thành phần đang hoạt động

        return Inertia::render('EmployeeSalaryStructures/Create', [
            'employees' => $employees,
            'components' => $components,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'component_id' => 'required|exists:salary_components,id',
            'amount' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
        ]);

        EmployeeSalaryStructure::create($request->all());

        return redirect()->route('salary-structures.index')->with('success', 'Thêm cơ cấu lương thành công!');
    }

    public function show(EmployeeSalaryStructure $salaryStructure)
    {
        $salaryStructure->load(['employee', 'component']);

        return Inertia::render('EmployeeSalaryStructures/Show', [
            'salaryStructure' => $salaryStructure,
        ]);
    }

    public function edit(EmployeeSalaryStructure $salaryStructure)
    {
        $employees = Employee::all();
        $components = SalaryComponent::active()->get();

        return Inertia::render('EmployeeSalaryStructures/Edit', [
            'salaryStructure' => $salaryStructure,
            'employees' => $employees,
            'components' => $components,
        ]);
    }

    public function update(Request $request, EmployeeSalaryStructure $salaryStructure)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'component_id' => 'required|exists:salary_components,id',
            'amount' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
        ]);

        $salaryStructure->update($request->all());

        return redirect()->route('salary-structures.index')->with('success', 'Cập nhật cơ cấu lương thành công!');
    }

    public function destroy(EmployeeSalaryStructure $salaryStructure)
    {
        $salaryStructure->delete();

        return redirect()->route('salary-structures.index')->with('success', 'Đã xóa cơ cấu lương!');
    }
}