<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayrollPeriodController extends Controller
{
    public function index()
    {
        $payrollPeriods = PayrollPeriod::latest()->paginate(10);
        
        // Gọi thêm thuộc tính tính toán (standard_working_days) để gửi sang Vue
        $payrollPeriods->getCollection()->append('standard_working_days');

        return Inertia::render('PayrollPeriods/Index', [
            'payrollPeriods' => $payrollPeriods,
        ]);
    }

    public function create()
    {
        return Inertia::render('PayrollPeriods/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:payroll_periods,code',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'payment_date' => 'nullable|date|after_or_equal:end_date',
            'status' => 'required|string', // Giả sử Enum lưu dưới dạng string
        ]);

        PayrollPeriod::create($request->all());

        return redirect()->route('payroll-periods.index')->with('success', 'Tạo kỳ lương mới thành công!');
    }

    public function show(PayrollPeriod $payrollPeriod)
    {
        $payrollPeriod->append('standard_working_days');

        return Inertia::render('PayrollPeriods/Show', [
            'payrollPeriod' => $payrollPeriod,
        ]);
    }

    public function edit(PayrollPeriod $payrollPeriod)
    {
        return Inertia::render('PayrollPeriods/Edit', [
            'payrollPeriod' => $payrollPeriod,
        ]);
    }

    public function update(Request $request, PayrollPeriod $payrollPeriod)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:payroll_periods,code,' . $payrollPeriod->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'payment_date' => 'nullable|date|after_or_equal:end_date',
            'status' => 'required|string',
        ]);

        $payrollPeriod->update($request->all());

        return redirect()->route('payroll-periods.index')->with('success', 'Cập nhật kỳ lương thành công!');
    }

    public function destroy(PayrollPeriod $payrollPeriod)
    {
        // Tùy logic doanh nghiệp: Nếu không phải bản Nháp (draft) thì cấm xóa
        // if ($payrollPeriod->status->value !== 'draft') {
        //     return back()->with('error', 'Chỉ được phép xóa kỳ lương ở trạng thái Nháp!');
        // }

        $payrollPeriod->delete();

        return redirect()->route('payroll-periods.index')->with('success', 'Đã xóa kỳ lương!');
    }
}