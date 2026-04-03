<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Position;
use Inertia\Inertia;
use App\Http\Requests\Crud\Position\PositionRequest;
class PositionController extends Controller
{
    public function index(Request $request){
        $restore = Position::onlyTrashed()->get();
        $query = Position::query();

        if($request->has('search') && $request->search != '') {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return Inertia::render('Positions/Index', [
            'positions' => $query->paginate(3)->withQueryString(),
            'restore' => $restore,  
            'filters' => $request->only(['search'])
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
    
}
