<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LineManagerService;
use App\Services\LeaveRequestService; 
use Exception;

class LineManagerController extends Controller
{
    protected $managerService;
    // 👇 1. BỔ SUNG KHAI BÁO BIẾN NÀY
    protected $leaveService;    
    // Inject Service vào Controller
    public function __construct(LineManagerService $managerService, LeaveRequestService $leaveService)
    {
        $this->managerService = $managerService;
        $this->leaveService = $leaveService; 
    }

    // API: Xem chi tiết lính
    public function showTeamMember($id)
    {
        try {
            $employee = $this->managerService->getTeamMemberProfile($id);
            return response()->json(['success' => true, 'data' => $employee]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }
    }

   public function indexTeam(Request $request)
    {
        $user = auth()->user();

        // BƯỚC PHÒNG VỆ: Chặn lỗi sập web nếu tài khoản chưa có hồ sơ nhân viên
        if (!$user || !$user->employee) {
            return redirect()->back()->with('error', 'Tài khoản của bạn chưa được liên kết với hồ sơ nhân sự nào trên hệ thống!');
        }

        // Lấy ID an toàn (Lúc này chắc chắn sẽ lấy được)
        $managerId = $user->employee->id;

        $query = \App\Models\Employee::with(['department', 'position']) 
            ->where('manager_id', $managerId);

        // Lọc tìm kiếm
        if ($request->filled('search')) {
            $searchTerm = "%{$request->search}%";
            $query->where(function($q) use ($searchTerm) {
                $q->where('full_name', 'like', $searchTerm)
                  ->orWhere('employee_code', 'like', $searchTerm);
            });
        }

        $employees = $query->latest()->paginate(10)->withQueryString();

        // Ẩn dữ liệu nhạy cảm cho danh sách
        $employees->getCollection()->transform(function ($employee) {
            return $employee->makeHidden(['basic_salary', 'bank_account_number', 'bank_name', 'identity_card_number']);
        });

        return inertia('Manager/Team/Index', [
            'employees' => $employees,
            'filters' => $request->only('search')
        ]);
    }

    // public function indexLeaves(Request $request)
    // {
    //     $user = auth()->user();

    //     if (!$user || !$user->employee) {
    //         return redirect()->back()->with('error', 'Tài khoản của bạn chưa được liên kết với hồ sơ nhân sự!');
    //     }

    //     $managerId = $user->employee->id;

    //     // Lấy danh sách đơn nghỉ phép của nhân viên thuộc quyền quản lý
    //     // Cần eager load 'employee' và 'leaveType' (nếu bạn có bảng loại nghỉ phép)
    //     $query = \App\Models\LeaveRequest::with(['employee:id,full_name,employee_code', 'leaveType'])
    //         ->whereHas('employee', function ($q) use ($managerId) {
    //             $q->where('manager_id', $managerId);
    //         });

    //     // Lọc theo trạng thái
    //    // ... Đoạn code lọc status phía trên giữ nguyên ...
        
    //     if ($request->filled('status')) {
    //         $statusArray = explode(',', $request->status);
    //         $query->whereIn('status', $statusArray);
    //     } else {
    //         // 👇 ĐÃ SỬA: Thay thế hàm FIELD bằng cấu trúc CASE WHEN tương thích hoàn toàn với PostgreSQL
    //         $query->orderByRaw("
    //             CASE status 
    //                 WHEN 'pending' THEN 1 
    //                 WHEN 'approved' THEN 2 
    //                 WHEN 'rejected' THEN 3 
    //                 ELSE 4 
    //             END ASC
    //         ");
    //     }

    //     // Nhớ giữ nguyên đoạn đằng sau
    //     $leaveRequests = $query->latest('created_at')->paginate(10)->withQueryString();
        

    //     return inertia('Manager/Leaves/Index', [
    //         'leaveRequests' => $leaveRequests,
    //         'filters' => $request->only(['status'])
    //     ]);
    // }

    public function teamAttendance(Request $request)
    {
        $user = auth()->user();

        // 1. Kiểm tra an toàn
        if (!$user || !$user->employee) {
            return redirect()->back()->with('error', 'Tài khoản của bạn chưa được liên kết với hồ sơ nhân sự!');
        }

        $managerId = $user->employee->id;

        // 2. Lấy tham số ngày tháng (Mặc định là từ đầu tháng đến hiện tại)
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // 3. Xây dựng truy vấn (Lưu ý: 'employee' cần được 'load' để truy cập được manager_id)
        $query = \App\Models\Attendance::with(['employee:id,full_name,employee_code'])
            ->whereHas('employee', function ($q) use ($managerId) {
                $q->where('manager_id', $managerId);
            })
            ->whereBetween('date', [$startDate, $endDate]);

        // Lọc theo tên/mã nhân viên nếu có tìm kiếm
        if ($request->filled('search')) {
            $searchTerm = "%{$request->search}%";
            $query->whereHas('employee', function($q) use ($searchTerm) {
                $q->where('full_name', 'like', $searchTerm)
                  ->orWhere('employee_code', 'like', $searchTerm);
            });
        }

        // 4. Sắp xếp ngày mới nhất lên đầu
        $attendances = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();

        return inertia('Manager/Attendance/Index', [
            'attendances' => $attendances,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'search' => $request->search ?? ''
            ]
        ]);
    }

    // Giao diện danh sách đơn chờ duyệt của lính
    public function indexLeaves(Request $request)
    {
        $managerId = auth()->user()->employee->id;

        $query = \App\Models\LeaveRequest::with(['employee', 'leaveType'])
            ->where('manager_id', $managerId);

        // Sắp xếp Đơn 'pending' lên đầu bằng cấu hình CASE WHEN chuẩn Postgres bạn vừa sửa hôm trước
        $query->orderByRaw("
            CASE status 
                WHEN 'pending' THEN 1 
                WHEN 'approved' THEN 2 
                WHEN 'rejected' THEN 3 
                ELSE 4 
            END ASC
        ")->orderBy('created_at', 'desc');

        $leaveRequests = $query->paginate(10);

        return inertia('Manager/Leaves/Index', [
            'leaveRequests' => $leaveRequests
        ]);
    }

    // Gửi Action duyệt/từ chối từ nút bấm của Sếp
    public function approveLeave(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:255'
        ]);

        try {
            $this->leaveService->reviewRequest($id, $request->status, $request->rejection_reason);
            return redirect()->back()->with('success', 'Đã xử lý đơn xin nghỉ phép thành công.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}