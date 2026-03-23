<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role; 
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
// 👇 1. BẮT BUỘC IMPORT DÒNG NÀY ĐỂ TỰ XÓA CACHE
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function create()
    {
        $permissions = Permission::all();

        return Inertia::render('Roles/Create', [
            'permissions' => $permissions,
        ]);
    }

   public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'permission_ids' => 'nullable|array', 
        ]);

        DB::transaction(function () use ($request, $validated) {
            $role = Role::create([
                'name' => $validated['name'],
                'display_name' => $validated['display_name'], 
                'description' => $validated['description'],
                'guard_name' => 'web',
            ]);

            // 🔥 BÍ KÍP TỐI THƯỢNG: Lọc sạch các giá trị null/rỗng trước khi lưu
            $rawIds = $request->input('permission_ids', []);
            $cleanIds = array_filter($rawIds); // Máy lọc rác tự động của PHP
            
            $role->permissions()->sync($cleanIds);
        });

        // Tự động dọn dẹp bộ nhớ tạm của Spatie
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')
            ->with('success', 'Đã khởi tạo vai trò và phân quyền thành công!');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'display_name'   => 'required|string|max:255',
            'description'    => 'nullable|string|max:255',
            'permission_ids' => 'nullable|array',
        ]);

        $role = Role::findOrFail($id);

        if ($role->name === 'admin') {
            return back()->with('error', 'Không được phép chỉnh sửa vai trò Quản trị viên hệ thống!');
        }

        DB::transaction(function () use ($role, $request, $validated) {
            
            $role->update([
                'display_name' => $validated['display_name'],
                'description'  => $validated['description'],
            ]);

            // 🔥 BÍ KÍP TỐI THƯỢNG: Lọc sạch các giá trị null/rỗng trước khi lưu
            $rawIds = $request->input('permission_ids', []);
            // Thay vì: $cleanIds = array_filter($rawIds);
            // Dùng hàm này để chỉ lọc ra những giá trị không rỗng (null hoặc chuỗi rỗng)
            $cleanIds = array_filter($rawIds, fn($val) => !is_null($val) && $val !== '');
            $role->permissions()->sync($cleanIds); 
            
        });

        // Tự động dọn dẹp bộ nhớ tạm của Spatie
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', 'Cập nhật vai trò thành công!');
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all();

        return Inertia::render('Roles/Edit', [
            // ÉP KIỂU DỮ LIỆU ĐỂ TRUYỀN XUỐNG VUE
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                // Trích xuất từ mảng Object thành mảng mảng ID (vd: [1, 2, 5]) 
                // thì hàm form.permission_ids.includes(perm.id) của Vue mới hoạt động được!
                'permissions' => $role->permissions
                        ->pluck('id')
                        ->map(fn($id) => (int)$id)
                        ->toArray(),
            ],
            'permissions' => $permissions,
        ]);
    }


    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'admin') {
            return back()->with('error', 'Không thể xóa vai trò Quản trị viên!');
        }

        $role->delete();

        return back()->with('success', 'Đã xóa vai trò thành công!');
    }

    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all();

        return Inertia::render('Roles/Show', [
            'role' => $role,
            'permissions' => $permissions,
        ]);
    }
}