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
        // Vì Vue của bạn đang định nghĩa restore: { data: Array }, nên ta bọc nó vào ['data' => ...]
        $restore = Permission::onlyTrashed()->get();
        
        $query = Permission::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return Inertia::render('Permissions/Index', [
            'permissions' => $query->latest()->paginate(10)->withQueryString(),
            'restore' => ['data' => $restore], // Bọc lại cho khớp với Frontend
            'filters' => $request->only(['search'])
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

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('permissions.index')->with('success', 'Cập nhật quyền hạn thành công!');
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

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
}