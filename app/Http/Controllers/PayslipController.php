<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;
// Thêm dòng này ở trên cùng file
use Illuminate\Support\Facades\Gate;

class PayslipController extends Controller
{
    public function index(Request $request)
    {
        // Khởi tạo truy vấn
        $query = Payslip::with(['employee', 'payrollPeriod'])->latest();

        // 👇 LOGIC LỌC DỮ LIỆU THÔNG MINH
        // Nếu không phải là Super Admin hoặc HR, thì chỉ lấy phiếu lương CỦA CHÍNH MÌNH
        if (!$request->user()->hasRole(['Super Admin', 'hr'])) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
        }

        $payslips = $query->paginate(10);
            
        return Inertia::render('Payslips/Index', [
            'payslips' => $payslips,
            'payrollPeriods' => \App\Models\PayrollPeriod::latest()->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Payslips/Create', [
            'employees' => Employee::all(),
            'payrollPeriods' => PayrollPeriod::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'payroll_period_id' => 'required|exists:payroll_periods,id',
            'employee_id' => 'required|exists:employees,id',
            'working_days' => 'required|numeric|min:0',
            'gross_salary' => 'required|numeric|min:0',
            'total_deduction' => 'required|numeric|min:0',
            'net_salary' => 'required|numeric|min:0',
        ]);

        $data = $request->all();
        // Mặc định tạo snapshot rỗng nếu nhập tay (Thực tế sẽ được gen từ logic tính lương)
        $data['salary_snapshot'] = ['items' => []]; 

        Payslip::create($data);

        return redirect()->route('payslips.index')->with('success', 'Tạo phiếu lương thành công!');
    }

    public function show(Payslip $payslip)
    {
        // 👇 VỆ SĨ ĐỨNG CANH CỬA Ở ĐÂY
        // Nó sẽ tự động gọi hàm view() trong PayslipPolicy. Nếu return false, nó ném luôn lỗi 403 ra mặt!
        Gate::authorize('view', $payslip);

        $payslip->load(['employee', 'payrollPeriod', 'details']);

        return Inertia::render('Payslips/Show', [
            'payslip' => $payslip,
        ]);
    }

    public function edit(Payslip $payslip)
    {
        return Inertia::render('Payslips/Edit', [
            'payslip' => $payslip,
            'employees' => Employee::all(),
            'payrollPeriods' => PayrollPeriod::latest()->get(),
        ]);
    }

    public function update(Request $request, Payslip $payslip)
    {
        $request->validate([
            'payroll_period_id' => 'required|exists:payroll_periods,id',
            'employee_id' => 'required|exists:employees,id',
            'working_days' => 'required|numeric|min:0',
            'gross_salary' => 'required|numeric|min:0',
            'total_deduction' => 'required|numeric|min:0',
            'net_salary' => 'required|numeric|min:0',
        ]);

        $payslip->update($request->all());

        return redirect()->route('payslips.index')->with('success', 'Cập nhật phiếu lương thành công!');
    }

    public function destroy(Payslip $payslip)
    {
        $payslip->delete();

        return redirect()->route('payslips.index')->with('success', 'Đã xóa phiếu lương!');
    }

    /**
     * HÀM SINH LƯƠNG TỰ ĐỘNG (PAYROLL ENGINE)
     */
    public function generate(Request $request)
    {
        $request->validate([
            'payroll_period_id' => 'required|exists:payroll_periods,id'
        ]);

        $period = PayrollPeriod::findOrFail($request->payroll_period_id);
        $employees = Employee::all(); 

        $generatedCount = 0;

        foreach ($employees as $employee) {
            // Bỏ qua nếu đã có phiếu lương trong kỳ này
            if (Payslip::where('payroll_period_id', $period->id)->where('employee_id', $employee->id)->exists()) {
                continue;
            }

            // Lấy cấu trúc lương mới nhất
            $structures = \App\Models\EmployeeSalaryStructure::with('component')
                ->where('employee_id', $employee->id)
                ->where('effective_date', '<=', $period->end_date)
                ->orderBy('effective_date', 'desc')
                ->get();

            $latestStructures = $structures->unique('component_id');

            if ($latestStructures->isEmpty()) continue;

            $gross = 0;
            $deduction = 0;
            $snapshotItems = [];

            foreach ($latestStructures as $structure) {
                $component = $structure->component;
                $isEarning = $component->type === 'earning' || (isset($component->type->value) && $component->type->value === 'earning');

                if ($isEarning) {
                    $gross += $structure->amount;
                } else {
                    $deduction += $structure->amount;
                }

                $snapshotItems[] = [
                    'code' => $component->code,
                    'name' => $component->name,
                    'type' => $isEarning ? 'earning' : 'deduction',
                    'amount' => $structure->amount,
                ];
            }

            $net = $gross - $deduction;

            // 1. TẠO PHIẾU LƯƠNG CHÍNH (PAYSLIP)
            $payslip = Payslip::create([
                'payroll_period_id' => $period->id,
                'employee_id' => $employee->id,
                'working_days' => $period->standard_working_days ?? 22,
                'gross_salary' => $gross,
                'total_deduction' => $deduction,
                'net_salary' => max(0, $net),
                'salary_snapshot' => ['items' => $snapshotItems], // Vẫn giữ snapshot để dự phòng
                'is_sent' => false,
            ]);

            // 👇 2. TẠO TỰ ĐỘNG CÁC DÒNG CHI TIẾT (PAYSLIP DETAIL) VÀO DATABASE
            foreach ($latestStructures as $structure) {
                $component = $structure->component;
                $isEarning = $component->type === 'earning' || (isset($component->type->value) && $component->type->value === 'earning');

                \App\Models\PayslipDetail::create([
                    'payslip_id' => $payslip->id,
                    'salary_component_id' => $component->id,
                    'component_name' => $component->name,
                    'type' => $isEarning ? 'earning' : 'deduction',
                    'amount' => $structure->amount,
                    'is_taxable' => $component->is_taxable ?? false,
                    'description' => 'Tạo tự động từ cấu trúc lương',
                ]);
            }

            $generatedCount++;
        }

        return redirect()->back()->with('success', "Đã chạy tính lương tự động và tạo chi tiết cho {$generatedCount} phiếu lương!");
    }
}