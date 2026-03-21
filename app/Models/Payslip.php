<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payslip extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'gross_salary',
        'total_deduction',
        'net_salary',
        'working_days',
        'is_sent',
        'sent_at',
        'salary_snapshot', // <-- JSON data
    ];

    protected $casts = [
        'gross_salary' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'working_days' => 'float',
        'is_sent' => 'boolean',
        'sent_at' => 'datetime',
        'salary_snapshot' => 'array', // Tự động chuyển JSON -> Mảng PHP
    ];

    // --- RELATIONSHIPS ---

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Nếu bạn vẫn dùng bảng chi tiết riêng (payslip_details) để query SQL
    public function details()
    {
        return $this->hasMany(PayslipDetail::class);
    }

    // --- HELPER METHODS (FORMATTING) ---

    public function getFormattedNetSalaryAttribute()
    {
        return number_format($this->net_salary, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Lấy chi tiết lương từ JSON Snapshot (An toàn hơn query bảng con)
     * Vd: $payslip->getComponentValue('BASIC_SALARY')
     */
    public function getComponentValue($code)
    {
        // Giả sử cấu trúc JSON: ['items' => [['code' => 'BASIC', 'amount' => 1000]]]
        $items = $this->salary_snapshot['items'] ?? [];
        
        foreach ($items as $item) {
            if (($item['code'] ?? '') === $code) {
                return $item['amount'];
            }
        }
        return 0;
    }
}