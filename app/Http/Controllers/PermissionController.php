<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// 👇 SỬA QUAN TRỌNG NHẤT: Import Model tùy chỉnh của bạn (thay vì của Spatie)
use App\Models\Permission; 
use Inertia\Inertia;
use Spatie\Permission\PermissionRegistrar;
use App\Http\Requests\Crud\Permission\PermissionResquet;

class PermissionController extends Controller
{
    public function index(Request $request)
    { 
        $query = Permission::query();
        // 👇 KHỞI TẠO BIẾN CHO THÙNG RÁC
        $restoreQuery = Permission::onlyTrashed();

         if ($request->has('search') && $request->search != '') {
            $searchTerm = "%{$request->search}%";
            
            // Lọc cho bảng chính
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('group', 'like', $searchTerm);
            });
            
            // Lọc cho bảng thùng rác
            $restoreQuery->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('group', 'like', $searchTerm);
            });
        }

        if ($request->filled('group')) {
            $groupArray = explode(',', $request->group);
            
            $query->whereIn('group', $groupArray);
            $restoreQuery->whereIn('group', $groupArray);
        }

        return Inertia::render('Permissions/Index', [
            'permissions' => $query->latest()->paginate(10)->withQueryString(),
            // 👇 GỌI GET() Ở ĐÂY LUÔN
            'restore' => ['data' => $restoreQuery->latest()->get()], 
            'filters' => $request->only(['search', 'group']),
            'is_trashed' => $request->is_trashed === 'true' ? 'true' : 'false'
        ]);
    }

    public function create()
    {
        return Inertia::render('Permissions/Create');
    }

    public function store(PermissionResquet $request)
    {
        $validated = $request->validated();

        Permission::create([
            'name' => $validated['name'],
            'group' => $validated['group'],
            'guard_name' => 'web'
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('permissions.index')->with('success', 'Đã tạo quyền hạn mới thành công!');
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        
        return Inertia::render('Permissions/Edit', [
            'permission' => $permission
        ]);
    }

    public function update(PermissionResquet $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $validated = $request->validated();

        $permission->update($validated);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('permissions.index')->with('success', 'Cập nhật quyền hạn thành công!');
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->roles()->detach();
        $permission->delete();

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('permissions.index')->with('success', 'Đã xóa quyền hạn thành công!');
    }

    public function restore($id)
    {
        $permission = Permission::withTrashed()->findOrFail($id);
        $permission->restore();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('permissions.index')->with('success', 'Đã khôi phục quyền hạn thành công!');
    }

    public function forceDelete($id)
    {
        $permission = Permission::withTrashed()->findOrFail($id);
        $permission->roles()->detach();
        $permission->forceDelete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('permissions.index')->with('success', 'Đã xóa vĩnh viễn quyền hạn thành công!');
    }
    
    public function bulkDelete(Request $request){
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:permissions,id', 
        ]);

        // 👇 ĐÃ SỬA: Xóa hàng loạt trực tiếp từ model Permission (Không cần check avatar hay admin)
        Permission::whereIn('id', $request->ids)->delete();

        // Xóa cache của Spatie sau khi xóa quyền
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', 'Tuyệt vời! Đã xóa thành công ' . count($request->ids) . ' quyền hạn.');
    }

    public function bulkRestore(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:permissions,id', 
        ]);

        Permission::whereIn('id', $request->ids)->restore();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', 'Tuyệt vời! Đã khôi phục thành công ' . count($request->ids) . ' quyền hạn.');
    }
}