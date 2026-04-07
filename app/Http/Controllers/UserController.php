<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\DB;   
use App\Models\User;
use Spatie\Permission\Models\Role;   
use Inertia\Inertia;
use Spatie\Permission\PermissionRegistrar; 

// ĐÃ XÓA TOÀN BỘ IMPORT CỦA INTERVENTION IMAGE

class UserController extends Controller
{
    public function index(Request $request) 
    {
        // 1. Tạo 2 luồng truy vấn (Query Builder) TÁCH BIỆT
        $query = User::with('roles')->whereNull('deleted_at'); // Bảng chính
        $restoreQuery = User::with('roles')->onlyTrashed();    // Bảng thùng rác
        $roles = Role::all();

        // 2. LỌC THEO TỪ KHÓA TÌM KIẾM
        if ($request->has('search') && $request->search != '') {
            $searchTerm = "%{$request->search}%";
            
            // Lọc cho bảng chính
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm);
            });
            
            // Lọc cho bảng thùng rác
            $restoreQuery->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm);
            });
        }

        // 3. LỌC THEO NHIỀU TRẠNG THÁI (Tick Checkbox)
        if ($request->filled('status')) {
            $statusArray = explode(',', $request->status);
            
            $query->whereIn('status', $statusArray);
            $restoreQuery->whereIn('status', $statusArray);
        }

        // 4. LỌC THEO NHIỀU VAI TRÒ (Tick Checkbox)
        if ($request->filled('role')) {
            $roleArray = explode(',', $request->role);
            
            // Closure (hàm ẩn danh) để lọc vai trò, dùng chung cho cả 2
            $roleFilter = function($q) use ($roleArray) {
                $q->whereIn('roles.id', $roleArray); 
            };

            $query->whereHas('roles', $roleFilter);
            $restoreQuery->whereHas('roles', $roleFilter);
        }

        // 5. Trả kết quả về cho Vue
        return Inertia::render('Users/Index', [
            'users' => $query->latest()->paginate(10)->withQueryString(),
            'restore' => $restoreQuery->latest()->get(), // Nhớ gọi ->get() ở bước cuối cùng
            'roles' => $roles,
            'filters' => $request->only(['search', 'status', 'role']),
            'is_trashed' => $request->is_trashed === 'true' ? 'true' : 'false'
        ]);
    }

    public function create() 
    {
        $roles = Role::all(); 

        return Inertia::render('Users/Create', [
            'roles' => $roles 
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255|unique:users,name',
            'email'    => 'required|string|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:255', 
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role_ids' => 'nullable|array', 
        ]); 

        DB::transaction(function () use ($request, $validated) {
            
            $validated['password'] = Hash::make($validated['password']);

            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => $validated['password'],
            ]);

            // 🔥 SỬ DỤNG UPLOAD GỐC CỦA LARAVEL
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                // Lấy đuôi file gốc (vd: .jpg, .png)
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_user_' . $user->id . '.' . $extension;
                
                // Laravel tự động lưu vào storage/app/public/avatars
                $path = $file->storeAs('avatars', $filename, 'public');
                
                $user->update(['avatar' => $path]);
            }

            $roleIds = $request->input('role_ids', []);
            $roleIds = is_array($roleIds) ? $roleIds : [$roleIds]; 
            $cleanRoleIds = array_filter($roleIds, fn($val) => !is_null($val) && $val !== '');
            
            if (!empty($cleanRoleIds)) {
                $user->roles()->sync($cleanRoleIds);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('users.index')->with('success', 'Tuyệt vời! Đã thêm người dùng thành công.');
    }

    public function edit(User $user) 
    {
        $roles = Role::all();
        $user->load('roles'); 

        return Inertia::render('Users/Edit', [
            'user' => array_merge($user->toArray(), [
                'role_ids' => $user->roles->pluck('id')->toArray(),
            ]),
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user) 
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255|unique:users,name,' . $user->id,
            'email'    => 'required|string|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|max:255', 
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role_ids' => 'nullable|array', 
        ]); 

        DB::transaction(function () use ($request, $validated, $user) {

            if (empty($validated['password'])) {
                unset($validated['password']); 
            } else {
                $validated['password'] = Hash::make($validated['password']);
            }

            // 🔥 SỬ DỤNG UPLOAD GỐC CỦA LARAVEL
            if ($request->hasFile('avatar')) {
                // Xóa ảnh cũ nếu có
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                $file = $request->file('avatar');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_user_' . $user->id . '.' . $extension;
                
                $path = $file->storeAs('avatars', $filename, 'public');
                $validated['avatar'] = $path;
            }

            $user->update($validated);

            $roleIds = $request->input('role_ids', []);
            $roleIds = is_array($roleIds) ? $roleIds : [$roleIds]; 
            $cleanRoleIds = array_filter($roleIds, fn($val) => !is_null($val) && $val !== '');
            
            $user->roles()->sync($cleanRoleIds);

        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('users.index')->with('success', 'Tuyệt vời! Đã cập nhật người dùng thành công.');
    }

    public function destroy(User $user) 
    {
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->status = 'inactive';
        $user->save();
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Tuyệt vời! Đã xóa người dùng thành công.');
    }

    public function show($id) 
    {
        try {
            $user = User::with('roles')->findOrFail($id);
            return Inertia::render('Users/Show', [
                'user' => $user,
            ]);
        } catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('users.index')->with('error', 'Không tìm thấy người dùng này!');
        }
    }

    public function restore($id) 
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        $user->status = 'active';
        $user->save();
        return redirect()->route('users.index')->with('success', 'Tuyệt vời! Đã khôi phục người dùng thành công.');
    }

    public function updateStatus(Request $request, User $user) 
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $user->update([
            'status' => $validated['status']
        ]);

        return back()->with('success', 'Tuyệt vời! Đã cập nhật trạng thái tài khoản.');
    }
    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->forceDelete();
        return redirect()->route('users.index')->with('success', 'Tuyệt vời! Đã xóa vĩnh viễn người dùng thành công.');
    }

    public function bulkDelete(Request $request)
    {
        // Kiểm tra xem có gửi mảng ids lên không
        $request->validate([
            'ids'   => 'required|array',
            // 👇 ĐÃ SỬA: Đổi employees thành users
            'ids.*' => 'integer|exists:users,id', 
        ]);

        // Tránh trường hợp Admin tự "chém" chính mình
        $filteredIds = array_filter($request->ids, function($id) {
            return $id !== auth()->id();
        });

        if (empty($filteredIds)) {
            return back()->with('error', 'Không thể xóa tài khoản bạn đang đăng nhập!');
        }

        // Đảm bảo xóa ảnh đại diện trong Storage (Nếu có)
        $usersToDelete = User::whereIn('id', $filteredIds)->get();
        foreach ($usersToDelete as $user) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
        }

        // Thực thi xóa hàng loạt bằng 1 câu Query duy nhất
        User::whereIn('id', $filteredIds)->delete();

        return back()->with('success', 'Tuyệt vời! Đã xóa thành công ' . count($filteredIds) . ' người dùng.');
    }

    public function bulkRestore(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:users,id',
        ]);

        $restoredCount = 0;
        foreach ($request->ids as $id) {
            $user = User::withTrashed()->find($id);
            if ($user) {
                $user->restore();
                $user->status = 'active';
                $user->save();
                $restoredCount++;
            }
        }

        return back()->with('success', "Tuyệt vời! Đã khôi phục thành công {$restoredCount} người dùng.");
    }

}