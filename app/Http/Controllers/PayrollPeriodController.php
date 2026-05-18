<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Services\PayrollService; // 💡 Nạp động cơ tính lương động vào
use App\Enums\PayrollStatus;      // Nạp Enum trạng thái kỳ lương
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Exception;

class PayrollPeriodController extends Controller
{
    protected $payrollService;

    /**
     * Sử dụng Dependency Injection để nạp Service xử lý lương
     */
    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Hiển thị danh sách tất cả các kỳ lương kèm số công chuẩn
     */
    public function index()
    {
        $payrollPeriods = PayrollPeriod::withCount('payslips')
            ->latest('start_date')
            ->paginate(10);
        
        // Gọi thuộc tính tính toán ngày công chuẩn (loại bỏ thứ 7, CN) để gửi sang Vue
        $payrollPeriods->getCollection()->append('standard_working_days');

        return Inertia::render('PayrollPeriods/Index', [
            'payrollPeriods' => $payrollPeriods,
            'statuses'       => PayrollStatus::cases(), // Gửi danh sách trạng thái để frontend làm bộ lọc
        ]);
    }

    /**
     * Màn hình khởi tạo kỳ lương mới
     */
    public function create()
    {
        return Inertia::render('PayrollPeriods/Create');
    }

    /**
     * Lưu đợt tính lương mới vào hệ thống
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'code'         => 'required|string|max:255|unique:payroll_periods,code',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'payment_date' => 'nullable|date|after_or_equal:end_date',
            'status'       => 'required|string', 
        ]);

        PayrollPeriod::create($request->all());

        return redirect()->route('payroll-periods.index')->with('success', 'Khởi tạo kỳ lương mới thành công!');
    }

    /**
     * Xem bảng tổng hợp phiếu lương chi tiết của đợt này
     */
    public function show(PayrollPeriod $payrollPeriod)
    {
        $payrollPeriod->append('standard_working_days');

        // Lấy danh sách phiếu lương của từng nhân viên thuộc kỳ này để hiển thị dạng bảng (Grid)
        $payslips = Payslip::with(['employee:id,full_name,employee_code'])
            ->where('payroll_period_id', $payrollPeriod->id)
            ->paginate(20);

        return Inertia::render('PayrollPeriods/Show', [
            'payrollPeriod' => $payrollPeriod,
            'payslips'      => $payslips, // Truyền bảng bảng kê lương sang Vue cho C&B kiểm tra
        ]);
    }

    /**
     * Màn hình chỉnh sửa thông tin kỳ lương
     */
    public function edit(PayrollPeriod $payrollPeriod)
    {
        // 🛡 Phòng vệ: Nếu kỳ lương đã chốt thì không cho vào trang sửa
        if (!$payrollPeriod->canBeModified()) {
            return redirect()->route('payroll-periods.index')->with('error', 'Kỳ lương này đã đóng băng, không thể chỉnh sửa!');
        }

        return Inertia::render('PayrollPeriods/Edit', [
            'payrollPeriod' => $payrollPeriod,
        ]);
    }

    /**
     * Cập nhật thông tin kỳ lương (Có chặn bảo vệ)
     */
    public function update(Request $request, PayrollPeriod $payrollPeriod)
    {
        // 🛡 Phòng vệ: Chặn sửa ngầm bằng công cụ bên ngoài khi kỳ lương đã LOCKED
        if (!$payrollPeriod->canBeModified()) {
            return redirect()->back()->with('error', 'Kỳ lương này đã chốt, không được phép thay đổi dữ liệu cấu hình!');
        }

        $request->validate([
            'name'         => 'required|string|max:255',
            'code'         => 'required|string|max:255|unique:payroll_periods,code,' . $payrollPeriod->id,
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'payment_date' => 'nullable|date|after_or_equal:end_date',
            'status'       => 'required|string',
        ]);

        $payrollPeriod->update($request->all());

        return redirect()->route('payroll-periods.index')->with('success', 'Cập nhật kỳ lương thành công!');
    }

    /**
     * Xóa đợt lương nháp (Chặn tuyệt đối nếu không phải bản DRAFT)
     */
    public function destroy(PayrollPeriod $payrollPeriod)
    {
        // 🛡 Khóa an toàn: Chỉ được xóa đợt lương nháp, tránh làm biến mất lịch sử kế toán công ty
        if (!$payrollPeriod->canBeModified()) {
            return redirect()->back()->with('error', 'Chỉ được phép xóa kỳ lương ở trạng thái Nháp (Draft)!');
        }

        $payrollPeriod->delete();

        return redirect()->route('payroll-periods.index')->with('success', 'Đã xóa kỳ lương khỏi hệ thống hoàn toàn!');
    }

    // =========================================================================
    // 👇 CÁC ACTION NÂNG CAO ĐẶC THÙ DÀNH RIÊNG CHO CHUYÊN VIÊN C&B SPECIALIST
    // =========================================================================

    /**
     * ACTION: Chạy máy sinh lương tự động dựa trên ngày công chấm công thực tế
     */
    public function generatePayroll(Request $request, $id)
    {
        try {
            $totalCalculated = $this->payrollService->generatePayrollEngine((int) $id);
            
            return redirect()->back()->with(
                'success', 
                "Hệ thống vận hành thành công! Đã kết toán phiếu lương cho {$totalCalculated} nhân viên toàn công ty."
            );
        } catch (Exception $e) {
            return redirect()->back()->with('error', "Lỗi trong quá trình tính lương: " . $e->getMessage());
        }
    }

    /**
     * ACTION: Khóa kỳ lương - Niêm phong dữ liệu, cấm sửa đổi bảng công và đơn từ phép
     */
    public function lockPeriod($id)
    {
        try {
            $this->payrollService->lockPayrollPeriod((int) $id);
            return redirect()->back()->with('success', 'Đã đóng băng đợt tính lương này. Dữ liệu công trạng chính thức được niêm phong!');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * ACTION: Phát hành gửi Email bảng lương (Payslip) hàng loạt tới hòm thư nhân viên
     */
    public function bulkSendPayslips($id)
    {
        try {
            $totalSent = $this->payrollService->bulkSendPayslipsAction((int) $id);
            return redirect()->back()->with('success', "Đã xếp hàng gửi thành công {$totalSent} phiếu lương tới Email cá nhân của từng nhân sự!");
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    

}