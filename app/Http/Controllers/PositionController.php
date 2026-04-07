<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Position;
use Inertia\Inertia;
use App\Http\Requests\Crud\Position\PositionRequest;
class PositionController extends Controller
{
    public function index(Request $request){
        // 1. KHỞI TẠO QUERY BUILDER (Đổi tên thành restoreQuery và bỏ ->get())
        $query = Position::query();
        $restoreQuery = Position::onlyTrashed(); 

        // 2. LẤY DỮ LIỆU ĐỔ VÀO BỘ LỌC (DROPDOWN) CHO VUE
        $code = Position::select('code')->whereNotNull('code')->distinct()->get();
        $level = Position::select('level')->whereNotNull('level')->distinct()->get();
        $salary_min = Position::select('salary_min')->whereNotNull('salary_min')->distinct()->get();
        $salary_max = Position::select('salary_max')->whereNotNull('salary_max')->distinct()->get();
        
        // 3. LỌC THEO TỪ KHÓA TÌM KIẾM
        if ($request->has('search') && $request->search != '') {
            $searchTerm = "%{$request->search}%";
            
            $query->where('name', 'like', $searchTerm);
            // 👇 ĐÃ SỬA: Biến này giờ đã tồn tại và hợp lệ
            $restoreQuery->where('name', 'like', $searchTerm); 
        }

        // 4. LỌC THEO MÃ (code)
        if ($request->filled('code')) {
            $codeArray = explode(',', $request->code);
            
            $query->whereIn('code', $codeArray);
            $restoreQuery->whereIn('code', $codeArray);
        }

        // 5. LỌC THEO CẤP BẬC (level)
        if ($request->filled('level')) {
            $levelArray = explode(',', $request->level);
            
            $query->whereIn('level', $levelArray);
            $restoreQuery->whereIn('level', $levelArray);
        }

        if ($request->filled('min_salary')) {
            $query->where('salary_min', '>=', $request->min_salary);
            $restoreQuery->where('salary_min', '>=', $request->min_salary);
        }

        // 2. Nếu người dùng nhập "Lương đến..."
        if ($request->filled('max_salary')) {
            $query->where('salary_max', '<=', $request->max_salary);
            $restoreQuery->where('salary_max', '<=', $request->max_salary);
        }

        // 👇 6. GỬI TOÀN BỘ SANG FRONTEND (Inertia)
        return Inertia::render('Positions/Index', [
            'positions' => $query->latest()->paginate(10)->withQueryString(), // Nên để 10 thay vì 3 cho bảng thực tế
            
            // 👇 ĐÃ SỬA: Bọc lại mảng data và gọi ->get() ở phút cuối cùng
            'restore' => ['data' => $restoreQuery->latest()->get()],  
            
            // 👇 ĐÃ SỬA: Nhớ gửi đủ tham số filter về để Vue giữ trạng thái ô check
            'filters' => $request->only(['search', 'code', 'level', 'min_salary', 'max_salary']),
            
            // 👇 ĐÃ SỬA: Bơm đủ các danh sách lựa chọn sang cho Vue vẽ Dropdown
            'code' => $code,
            'level' => $level,
            'salary_min' => $salary_min,
            'salary_max' => $salary_max,
            
            // 👇 ĐÃ SỬA: Giữ trạng thái của tab Thùng rác khi F5
            'is_trashed' => $request->is_trashed === 'true' ? 'true' : 'false'
        ]);
    }

    public function create() {
        return Inertia::render('Positions/Create');
    }

    public function store(PositionRequest $request) {
        $validated = $request->validated();
        Position::create($validated);
        return redirect()->route('positions.index')->with('success', 'Tuyệt vời! Đã thêm chức vụ thành công.');
    }

    public function edit($id) {
        return Inertia::render('Positions/Edit', [
            'position' => Position::findOrFail($id)
        ]);
    }

    public function update(PositionRequest $request, $id) {
        $position = Position::findOrFail($id);
        $validated = $request->validated();
        $position->update($validated);
        return redirect()->route('positions.index')->with('success', 'Tuyệt vời! Đã thêm chức vụ thành công.');
    }

    public function destroy($id) {
        Position::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Tuyệt vời! Đã xóa chức vụ thành công.');
    }

    public function search(Request $request) {
        $query = Position::query();
        if ($request->has('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }
        return Inertia::render('Positions/Index', [
            'positions' => $query->get()
        ]);
    }

    public function show($id) {
       try{
        $position = Position::findOrFail($id);
        return Inertia::render('Positions/Show', [
            'position' => $position
        ]);
       }catch (\Illuminate\Database\QueryException $e) {
            // Bắt lỗi Database (ví dụ: đang có nhân viên tham chiếu đến ID phòng ban này)
            return redirect()->back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    public function restore($id) {
        $position = Position::withTrashed()->findOrFail($id);
        $position->restore();
        return redirect()->back()->with('success', 'Tuyệt vời! Đã khôi phục chức vụ thành công.');
    }

    public function forceDelete($id) {
        $position = Position::withTrashed()->findOrFail($id);
        $position->forceDelete();
        return redirect()->back()->with('success', 'Tuyệt vời! Đã xóa chức vụ thành công.');
    }

    public function bulkDelete(Request $request) {
        $ids = $request->ids;
        Position::whereIn('id', $ids)->delete();
        return redirect()->back()->with('success', 'Tuyệt vời! Đã xóa chức vụ thành công.');
    }

    public function bulkRestore(Request $request) {
        $ids = $request->ids;
        Position::whereIn('id', $ids)->restore();
        return redirect()->back()->with('success', 'Tuyệt vời! Đã khôi phục chức vụ thành công.');
    }
    
}
