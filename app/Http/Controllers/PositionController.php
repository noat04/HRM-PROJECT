<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Position;
use Inertia\Inertia;

class PositionController extends Controller
{
    public function index(Request $request){
        $query = Position::query();

        if($request->has('search') && $request->search != '') {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return Inertia::render('Positions/Index', [
            'positions' => $query->paginate(3)->withQueryString(),
            'filters' => $request->only(['search'])
        ]);

    }

    public function create() {
        return Inertia::render('Positions/Create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:positions,name',
            'description' => 'nullable|string',
            'code'=> 'nullable|string|unique:positions,code',
            'level'=> 'required|integer',
            'salary_min' => 'nullable|numeric',
            'salary_max' => 'nullable|numeric',
        ], [
            'name.required' => 'Vui lòng nhập tên chức vụ.',
            'name.unique' => 'Tên chức vụ này đã tồn tại! Vui lòng chọn tên khác.',
            'code.unique' => 'Mã chức vụ này đã tồn tại! Vui lòng chọn mã khác.',
            'code.required' => 'Vui lòng nhập mã chức vụ.',
            'description.required' => 'Vui lòng nhập mô tả chức vụ.',
            'level.required' => 'Vui lòng nhập cấp bậc.',
            'salary_min.required' => 'Vui lòng nhập mức lương tối thiểu.',
            'salary_max.required' => 'Vui lòng nhập mức lương tối đa.',
        ]);
        Position::create($validated);
        return redirect()->route('positions.index')->with('success', 'Tuyệt vời! Đã thêm chức vụ thành công.');
    }

    public function edit($id) {
        return Inertia::render('Positions/Edit', [
            'position' => Position::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id) {
        $position = Position::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:positions,name,' . $id,
            'description' => 'nullable|string',
            'code'=> 'nullable|string|unique:positions,code,' . $id,
            'level'=> 'required|integer',
            'salary_min' => 'nullable|numeric',
            'salary_max' => 'nullable|numeric',
        ], [
            'name.required' => 'Vui lòng nhập tên chức vụ.',
            'name.unique' => 'Tên chức vụ này đã tồn tại! Vui lòng chọn tên khác.',
            'code.unique' => 'Mã chức vụ này đã tồn tại! Vui lòng chọn mã khác.',
            'code.required' => 'Vui lòng nhập mã chức vụ.',
            'description.required' => 'Vui lòng nhập mô tả chức vụ.',
            'level.required' => 'Vui lòng nhập cấp bậc.',
            'salary_min.required' => 'Vui lòng nhập mức lương tối thiểu.',
            'salary_max.required' => 'Vui lòng nhập mức lương tối đa.',
        ]);
        $position->update($validated);
        return redirect()->route('positions.index')->with('success', 'Tuyệt vời! Đã cập nhật chức vụ thành công.');
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
    
}
