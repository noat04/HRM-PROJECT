<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia; 
use App\Http\Requests\Crud\Department\DepartmentRequest;
use App\Models\Employee;
// Nhớ import Inertia

class DepartmentController extends Controller
{
    public function index(Request $request) {
        // 1. TẠO LUỒNG TRUY VẤN (TUYỆT ĐỐI KHÔNG DÙNG ->get() Ở ĐÂY)
        $query = Department::query();
        $restoreQuery = Department::onlyTrashed();

        // 2. LẤY DỮ LIỆU ĐỔ VÀO BỘ LỌC (DROPDOWN) CHO VUE
        $parent_id = Department::whereNull('parent_id')->get();
        $manager_id = Employee::all(); // Lấy danh sách nhân viên để chọn làm quản lý
        // Lấy danh sách các cấp bậc (level) hiện có, bỏ trùng lặp (distinct)
        $level = Department::select('level')->whereNotNull('level')->distinct()->get(); 

        // 3. LỌC THEO TỪ KHÓA TÌM KIẾM
        if ($request->has('search') && $request->search != '') {
            $searchTerm = "%{$request->search}%";
            
            $query->where('name', 'like', $searchTerm);
            $restoreQuery->where('name', 'like', $searchTerm);
        }

        // 4. LỌC THEO PHÒNG BAN CHA (parent_id)
        if ($request->filled('parent_id')) {
            $parentArray = explode(',', $request->parent_id);
            
            $query->whereIn('parent_id', $parentArray);
            $restoreQuery->whereIn('parent_id', $parentArray);
        }

        // 5. LỌC THEO QUẢN LÝ (manager_id) - Là cột trực tiếp, dùng whereIn
        if ($request->filled('manager_id')) {
            $managerArray = explode(',', $request->manager_id);
            
            $query->whereIn('manager_id', $managerArray);
            $restoreQuery->whereIn('manager_id', $managerArray);
        }

        // 6. LỌC THEO CẤP BẬC (level) - Là cột trực tiếp, dùng whereIn
        if ($request->filled('level')) {
            $levelArray = explode(',', $request->level);
            
            $query->whereIn('level', $levelArray);
            $restoreQuery->whereIn('level', $levelArray);
        }

        // 7. TRẢ KẾT QUẢ VỀ FRONTEND
        return Inertia::render('Departments/Index', [
            'departments' => $query->paginate(10)->withQueryString(),
            // 👇 GỌI ->get() Ở BƯỚC CUỐI CÙNG CHO THÙNG RÁC
            'restore' => ['data' => $restoreQuery->latest()->get()], 
            'filters' => $request->only(['search','parent_id','manager_id','level']),
            'parent_id' => $parent_id,
            'manager_id' => $manager_id,
            'level' => $level,
            'is_trashed' => $request->is_trashed === 'true' ? 'true' : 'false'
        ]);
    }

    //ĐIỀU HƯỚNG ĐẾN TRANG TẠO PHÒNG BAN
    public function create() {
        $departments = Department::all();
        $employees = Employee::all();
        return Inertia::render('Departments/Create', [
            'departments' => $departments,
            'employees' => $employees
        ]);
    }

    //LƯU THÔNG TIN PHÒNG BAN
    //Quy trình nhận dữ liệu từ form -> kiểm tra tính hợp lệ -> lưu vào cơ sở dữ liệu -> thông báo kết quả.
    public function store(DepartmentRequest $request) {
        //Hàm validate() này hoạt động như một "trạm kiểm lâm". Nó nhận vào 2 mảng
        // Mảng 1: Các quy tắc kiểm tra (Rules)
        // Mảng 2: Thông báo lỗi tùy chỉnh (Custom Messages) .Lý do Nếu dữ liệu vi phạm các quy tắc ở trên, Laravel mặc định sẽ báo lỗi bằng tiếng Anh
        
        $validated = $request->validated();
        //Nếu dữ liệu hợp lệ, Laravel sẽ trả về một mảng $validated chứa dữ liệu đã được làm sạch.
        // Sau khi kiểm tra xong, nếu mọi thứ "sạch", chúng ta mới cho phép tạo dữ liệu.
        // $validated: Dữ liệu đã được kiểm tra và làm sạch.
        // Department::create(...): Hàm này sẽ tự động tạo một bản ghi mới trong bảng departments.

        //Nếu dữ liệu KHÔNG hợp lệ
        //Laravel sẽ tự động dừng hàm này và trả lỗi về cho giao diện.
        //Lấy toàn bộ các câu lỗi (Ví dụ: "Vui lòng nhập tên phòng ban.")
        //đóng gói nhét vào Session của trình duyệt.
        //<p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
        
        Department::create($validated);
        return redirect()->route('departments.index')->with('success', 'Tuyệt vời! Đã thêm phòng ban thành công.');
    }

    //ĐIỀU HƯỚNG ĐẾN TRANG CẬP NHẬT PHÒNG BAN
    public function edit($id) {
        $employees = Employee::all();
        //findOrFail($id): Hàm này sẽ đi thẳng vào cơ sở dữ liệu, tìm bản ghi có ID khớp với $id.
        // Nếu tìm thấy, nó trả về toàn bộ dữ liệu của phòng ban đó.
        // Nếu không tìm thấy, nó sẽ tự động dừng chương trình và báo lỗi 404 (Not Found).
        return Inertia::render('Departments/Edit', [
            'department' => Department::findOrFail($id),
            'employees' => $employees
        ]);
    }

    //CẬP NHẬT THÔNG TIN PHÒNG BAN
    public function update(DepartmentRequest $request, $id) {
        $department = Department::findOrFail($id);
        $validated = $request->validated();
        //Nếu dữ liệu hợp lệ, Laravel sẽ trả về một mảng $validated chứa dữ liệu đã được làm sạch.
        // Sau khi kiểm tra xong, nếu mọi thứ "sạch", chúng ta mới cho phép tạo dữ liệu.
        // $validated: Dữ liệu đã được kiểm tra và làm sạch.
        // $department->update(...): Hàm này sẽ cập nhật lại bản ghi hiện tại trong bảng departments.
        $department->update($validated);
        return redirect()->route('departments.index')->with('success', 'Tuyệt vời! Đã cập nhật phòng ban thành công.');
    }

    //XÓA PHÒNG BAN
    public function destroy($id) {
        Department::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Tuyệt vời! Đã xóa phòng ban thành công.');
    }

    //PHƯƠNG THỨC TÌM KIẾM PHÒNG BAN
    public function search(Request $request) {
        $query = Department::query();
        if ($request->has('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }
        return Inertia::render('Departments/Index', [
            'departments' => $query->get()
        ]);
    }

    //ĐIỀU HƯỚNG SANG TRANG XEM CHI TIẾT PHÒNG BAN
    public function show($id) {
        try {
            // 1. Chỉ tìm và lấy thông tin phòng ban, TUYỆT ĐỐI KHÔNG có chữ ->delete() ở đây
            $department = Department::findOrFail($id);
            
            // 2. Chuyển hướng sang một file Vue mới chuyên dùng để hiển thị chi tiết
            return Inertia::render('Departments/Show', [
                'department' => $department
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Bắt lỗi Database (ví dụ: đang có nhân viên tham chiếu đến ID phòng ban này)
            return redirect()->route('departments.index')->with('error', 'Không thể xóa! Phòng ban này đang chứa dữ liệu nhân viên.');
            
        } catch (\Exception $e) {
            // Bắt các lỗi hệ thống khác
            return redirect()->route('departments.index')->with('error', 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau.');
        }
    }

    //PHƯƠNG THỨC KHÔI PHỤC PHÒNG BAN
    public function restore($id) {
        $department = Department::withTrashed()->findOrFail($id);
        $department->restore();
        return redirect()->back()->with('success', 'Tuyệt vời! Đã khôi phục phòng ban thành công.');
    }
    public function forceDelete($id) {
        $department = Department::withTrashed()->findOrFail($id);
        $department->forceDelete();
        return redirect()->back()->with('success', 'Tuyệt vời! Đã xóa vĩnh viễn phòng ban thành công.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:departments,id', 
        ]);

        Department::whereIn('id', $request->ids)->delete();

        return back()->with('success', 'Tuyệt vời! Đã xóa thành công ' . count($request->ids) . ' phòng ban.');
    }

    public function bulkRestore(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:departments,id', 
        ]);

        Department::whereIn('id', $request->ids)->restore();

        return back()->with('success', 'Tuyệt vời! Đã khôi phục thành công ' . count($request->ids) . ' phòng ban.');
    }
}
