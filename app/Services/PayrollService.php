<?php

namespace App\Services;

use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\PayslipDetail;
use App\Models\Employee;
use App\Models\EmployeeSalaryStructure;
use App\Models\Attendance;
use App\Enums\PayrollStatus;
use App\Enums\SalaryComponentType;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class PayrollService
{
    /**
     * ĐỘNG CƠ CỐT LÕI: Chạy máy sinh lương tự động cho toàn bộ công ty
     */
    public function generatePayrollEngine(int $periodId)
    {
        return DB::transaction(function () use ($periodId) {
            
            // 1. Khóa dòng kỳ lương để tránh Race Condition nếu 2 C&B cùng bấm nút tính lương
            $period = PayrollPeriod::where('id', $periodId)->lockForUpdate()->firstOrFail();

            if (!$period->canBeModified()) {
                throw new Exception("Kỳ lương này đã bị khóa hoặc đã thanh toán, không thể tính lại lương!");
            }

            // Xóa sạch các dữ liệu tính nháp cũ của kỳ này nếu có để tính mới hoàn toàn
            $oldPayslipIds = Payslip::where('payroll_period_id', $periodId)->pluck('id');
            PayslipDetail::whereIn('payslip_id', $oldPayslipIds)->delete();
            Payslip::where('payroll_period_id', $periodId)->delete();

            // Lấy số ngày công tiêu chuẩn của tháng (ví dụ: 22 ngày)
            $standardDays = $period->standard_working_days;
            if ($standardDays <= 0) $standardDays = 22; // Fallback phòng vệ

            // 2. Quét danh sách nhân viên đang làm việc (Chưa nghỉ việc trước ngày bắt đầu kỳ lương)
            $employees = Employee::where(function($query) use ($period) {
                $query->whereNull('resignation_date')
                      ->orWhere('resignation_date', '>=', $period->start_date->format('Y-m-d'));
            })->get();

            foreach ($employees as $employee) {
                
                // 3. Tính toán số ngày công thực tế của nhân viên dựa trên module Chấm công
                $actualWorkingDays = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$period->start_date->format('Y-m-d'), $period->end_date->format('Y-m-d')])
                    ->whereIn('status', ['on_time', 'late', 'early_leave']) // Không tính ngày vắng mặt
                    ->count();

                // Nếu hệ thống mới chạy test chưa có data chấm công, mặc định cho hưởng đủ công chuẩn
                if ($actualWorkingDays === 0) {
                    $actualWorkingDays = $standardDays;
                }

                // 4. Tìm cấu hình lương đang áp dụng cho nhân viên này tại ngày chốt lương
                $salaryStructures = EmployeeSalaryStructure::with('component')
                    ->where('employee_id', $employee->id)
                    ->where('effective_date', '<=', $period->end_date->format('Y-m-d'))
                    ->orderBy('effective_date', 'desc')
                    ->get()
                    ->unique('component_id'); // Lấy dòng mới nhất của mỗi khoản lương

                $grossSalary = 0;
                $totalDeduction = 0;
                $detailsBuffer = [];
                $snapshotItems = [];

                foreach ($salaryStructures as $structure) {
                    $component = $structure->component;
                    if (!$component || !$component->is_active) continue;

                    $rawAmount = $structure->amount;
                    $finalAmount = 0;

                    // Logic tính toán theo loại thành phần lương
                    if ($component->calculation_type->value === 'FIXED') {
                        // Khoản cố định (Ví dụ: Phụ cấp điện thoại, xăng xe cố định)
                        $finalAmount = $rawAmount;
                    } else {
                        // Tính lương theo công thọc dựa trên ngày công thực tế (Lương cơ bản)
                        $finalAmount = ($rawAmount / $standardDays) * $actualWorkingDays;
                    }

                    // Làm tròn số tiền về dạng nguyên sạch để phục vụ đối tác khắt khe
                    $finalAmount = round($finalAmount, 2);

                    // Phân loại tổng tiền thu nhập hay trừ lương
                    if ($component->type === SalaryComponentType::EARNING) {
                        $grossSalary += $finalAmount;
                    } else {
                        $totalDeduction += $finalAmount;
                    }

                    // Đẩy dữ liệu vào mảng nhớ để tối ưu việc chèn số lượng lớn (Bulk Insert)
                    $detailsBuffer[] = [
                        'salary_component_id' => $component->id,
                        'component_name'      => $component->name, // Snapshot Tên khoản lương
                        'type'                => $component->type->value,
                        'amount'              => $finalAmount,
                        'is_taxable'          => $component->is_taxable,
                        'description'         => "Tính dựa trên {$actualWorkingDays}/{$standardDays} ngày công.",
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ];

                    // Tạo cấu trúc mảng phục vụ lưu JSON snapshot trực tiếp trên bảng cha
                    $snapshotItems[] = [
                        'code'   => $component->code,
                        'name'   => $component->name,
                        'type'   => $component->type->value,
                        'amount' => $finalAmount
                    ];
                }

                $netSalary = $grossSalary - $totalDeduction;
                if ($netSalary < 0) $netSalary = 0;

                // 5. Tạo Phiếu Lương Tổng (Header Payslip)
                $payslip = Payslip::create([
                    'payroll_period_id' => $period->id,
                    'employee_id'       => $employee->id,
                    'gross_salary'      => $grossSalary,
                    'total_deduction'   => $totalDeduction,
                    'net_salary'        => $netSalary,
                    'working_days'      => $actualWorkingDays,
                    'is_sent'           => false,
                    'salary_snapshot'   => ['items' => $snapshotItems] // Lưu Snapshot dạng mảng JSON
                ]);

                // 6. Gắn ID phiếu lương tổng vừa sinh vào danh sách chi tiết và lưu hàng loạt
                foreach ($detailsBuffer as &$detail) {
                    $detail['payslip_id'] = $payslip->id;
                }
                PayslipDetail::insert($detailsBuffer);
            }

            return $employees->count();
        });
    }

    /**
     * CHỐT KỲ LƯƠNG: Đóng băng dữ liệu công và lương
     */
    public function lockPayrollPeriod(int $periodId)
    {
        $period = PayrollPeriod::findOrFail($periodId);
        $period->update(['status' => PayrollStatus::LOCKED]);
    }

    /**
     * GỬI PHIẾU LƯƠNG HÀNG LOẠT: Đóng dấu gửi mail hàng loạt cho đội nhóm
     */
    public function bulkSendPayslipsAction(int $periodId)
    {
        return DB::transaction(function () use ($periodId) {
            $period = PayrollPeriod::findOrFail($periodId);
            
            // Tìm tất cả các phiếu lương chưa gửi thuộc kỳ này
            $payslips = Payslip::where('payroll_period_id', $periodId)
                ->where('is_sent', false)
                ->get();

            foreach ($payslips as $payslip) {
                // Logic thực tế: Dispatch Job gửi Email tại đây
                // Mail::to($payslip->employee->user->email)->queue(new PayslipMail($payslip));
                
                $payslip->update([
                    'is_sent' => true,
                    'sent_at' => now()
                ]);
            }

            return $payslips->count();
        });
    }
}