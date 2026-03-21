<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role; 
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

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

        // public function store(Request $request)
        // {
        //     $validated = $request->validate([
        //         'name' => 'required|string|max:191|unique:roles,name',
        //         'display_name' => 'required|string|max:255',
        //         'description' => 'nullable|string|max:255',
        //         // Sửa lại thành permission_ids cho giống form Vue
        //         'permission_ids' => 'nullable|array', 
        //         // So sánh theo 'id' thay vì 'name'
        //         'permission_ids.*' => 'exists:permissions,id', 
        //     ]);

        //     DB::transaction(function () use ($validated) {
        //         $role = Role::create([
        //             'name' => $validated['name'],
        //             // Đã fix lỗi gõ nhầm 'name' 2 lần ở đây
        //             'display_name' => $validated['display_name'], 
        //             'description' => $validated['description'],
        //             'guard_name' => 'web',
        //         ]);

        //         if (!empty($validated['permission_ids'])) {
        //             $role->syncPermissions($validated['permission_ids']);
        //         }
        //     });

        //     return redirect()->route('roles.index')
        //         ->with('success', 'Đã khởi tạo vai trò và phân quyền thành công!');
        // }
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

            // 🔥 BÍ KÍP TỐI THƯỢNG
            $permissionIds = $request->input('permission_ids', []);
            $permissions = Permission::whereIn('id', $permissionIds)->get();
            
            $role->syncPermissions($permissions);
        });

        return redirect()->route('roles.index')->with('success', 'Đã khởi tạo vai trò và phân quyền thành công!');
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

            // 🔥 BÍ KÍP TỐI THƯỢNG: Lấy mảng ID từ form, truy vấn ra các Object Permission và Sync
            $permissionIds = $request->input('permission_ids', []);
            $permissions = Permission::whereIn('id', $permissionIds)->get();
            
            $role->syncPermissions($permissions); 
            
        });

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
                'permissions' => $role->permissions->pluck('id')->toArray(), 
            ],
            'permissions' => $permissions,
        ]);
    }

        // public function update(Request $request, $id)
        // {
        //     // 1. Sửa lại các keys validation
        //     $validated = $request->validate([
        //         'display_name'   => 'required|string|max:255',
        //         'description'    => 'nullable|string|max:255',
        //         'permission_ids' => 'nullable|array', // Đón đúng tên biến Vue gửi lên
        //         'permission_ids.*'=> 'exists:permissions,id', // Kiểm tra theo ID
        //     ]);

        //     $role = Role::findOrFail($id);

        //     if ($role->name === 'admin') {
        //         return back()->with('error', 'Không được phép chỉnh sửa vai trò Quản trị viên hệ thống!');
        //     }

        //     DB::transaction(function () use ($role, $validated) {
                
        //         $role->update([
        //             'display_name' => $validated['display_name'],
        //             'description'  => $validated['description'],
        //         ]);

        //         // 2. Lấy đúng biến permission_ids ra để sync
        //         $permissions = $validated['permission_ids'] ?? [];
        //         $role->syncPermissions($permissions); 
                
        //     });

        //     return redirect()->route('roles.index')->with('success', 'Cập nhật vai trò thành công!');
        // }

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