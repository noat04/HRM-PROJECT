<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalaryComponentController extends Controller
{
    public function index()
    {
        $salaryComponents = SalaryComponent::latest()->paginate(10);
        
        return Inertia::render('SalaryComponents/Index', [
            'salaryComponents' => $salaryComponents,
        ]);
    }

    public function create()
    {
        return Inertia::render('SalaryComponents/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:salary_components,code',
            'type' => 'required|string',
            'calculation_type' => 'required|string',
            'default_value' => 'nullable|numeric|min:0',
            'is_taxable' => 'required|boolean',
            'is_active' => 'required|boolean',
            'description' => 'nullable|string',
        ]);

        SalaryComponent::create($request->all());

        return redirect()->route('salary-components.index')->with('success', 'Thêm thành phần lương thành công!');
    }

    // LƯU Ý QUAN TRỌNG: Tên biến phải là $salaryComponent để khớp với Route
    public function show(SalaryComponent $salaryComponent)
    {
        return Inertia::render('SalaryComponents/Show', [
            'salaryComponent' => $salaryComponent,
        ]);
    }

    public function edit(SalaryComponent $salaryComponent)
    {
        return Inertia::render('SalaryComponents/Edit', [
            'salaryComponent' => $salaryComponent,
        ]);
    }

    public function update(Request $request, SalaryComponent $salaryComponent)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:salary_components,code,' . $salaryComponent->id,
            'type' => 'required|string',
            'calculation_type' => 'required|string',
            'default_value' => 'nullable|numeric|min:0',
            'is_taxable' => 'required|boolean',
            'is_active' => 'required|boolean',
            'description' => 'nullable|string',
        ]);

        $salaryComponent->update($request->all());

        return redirect()->route('salary-components.index')->with('success', 'Cập nhật thành công!');
    }

    public function destroy(SalaryComponent $salaryComponent)
    {
        $salaryComponent->delete();

        return redirect()->route('salary-components.index')->with('success', 'Đã xóa thành phần lương!');
    }
}