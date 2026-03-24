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
        $query = Employee::query();
        $departments = Department::all();
        $positions = Position::all();
        if($request->search){
            $query->where('full_name', 'like', "%{$request->search}%");
        }
        return Inertia::render('Employees/Index', [
            'employees' => $query->paginate(3)->withQueryString(),
            'departments' => $departments,
            'positions' => $positions,
            'filters' => $request->only(['search'])
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
        $employee->delete();

        return redirect()->route('employees.index');
    }
}