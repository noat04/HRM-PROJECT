<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\DB;   
use App\Models\User;
use Spatie\Permission\Models\Role;   
use Inertia\Inertia;
// 👇 BẮT BUỘC THÊM DÒNG NÀY ĐỂ XÓA CACHE
use Spatie\Permission\PermissionRegistrar; 

class UserController extends Controller
{
    public function index(Request $request) 
    {
        $query = User::with('roles');
    
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
        }

        return Inertia::render('Users/Index', [
            'users' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search'])
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
        // 1. ĐÃ FIX LỖI VALIDATION SẠCH SẼ
        $validated = $request->validate([
            'name'     => 'required|string|max:255|unique:users,name',
            'email'    => 'required|string|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:255', // TUYỆT ĐỐI KHÔNG ĐỂ UNIQUE PASSWORD
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role_ids' => 'nullable|array', // Phải khai báo để hệ thống nhận diện mảng
        ], [
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min'      => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'avatar.image'      => 'Ảnh đại diện phải là file ảnh.',
            'name.required'     => 'Tên không được để trống.',
            'email.unique'      => 'Email đã tồn tại.',
        ]); 

        DB::transaction(function () use ($request, $validated) {
            
            $validated['password'] = Hash::make($validated['password']);

            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => $validated['password'],
            ]);

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $filename = time() . '_user_' . $user->id . '.jpg';
                $directory = storage_path('app/public/avatars');
                $path = $directory . '/' . $filename;

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                Image::read($file)->cover(256, 256)->toJpeg(80)->save($path);
                $user->update(['avatar' => 'avatars/' . $filename]);
            }

            // 🔥 BÍ KÍP TỐI THƯỢNG: ÉP LƯU QUYỀN BẰNG ELOQUENT THUẦN (BỎ QUA SPATIE)
            $roleIds = $request->input('role_ids', []);
            // Ép kiểu tất cả về mảng số nguyên để tránh lỗi
            $roleIds = is_array($roleIds) ? $roleIds : [$roleIds]; 
            $cleanRoleIds = array_filter($roleIds, fn($val) => !is_null($val) && $val !== '');
            
            if (!empty($cleanRoleIds)) {
                // Dùng thẳng quan hệ roles() để chọc thẳng ID vào bảng model_has_roles
                $user->roles()->sync($cleanRoleIds);
            }
        });

        // Xóa Cache của Spatie để nó không bị ngáo
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
        // 1. ĐÃ FIX LỖI VALIDATION SẠCH SẼ
        $validated = $request->validate([
            'name'     => 'required|string|max:255|unique:users,name,' . $user->id,
            'email'    => 'required|string|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|max:255', // Khi sửa thì pass có thể để trống
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role_ids' => 'nullable|array', 
        ]); 

        DB::transaction(function () use ($request, $validated, $user) {

            if (empty($validated['password'])) {
                unset($validated['password']); 
            } else {
                $validated['password'] = Hash::make($validated['password']);
            }

            if ($request->hasFile('avatar')) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                $file = $request->file('avatar');
                $filename = time() . '_user_' . $user->id . '.jpg';
                $directory = storage_path('app/public/avatars');
                $path = $directory . '/' . $filename;

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                Image::read($file)->cover(256, 256)->toJpeg(80)->save($path);
                $validated['avatar'] = 'avatars/' . $filename;
            }

            $user->update($validated);

            // 🔥 BÍ KÍP TỐI THƯỢNG: ÉP LƯU QUYỀN BẰNG ELOQUENT THUẦN (BỎ QUA SPATIE)
            $roleIds = $request->input('role_ids', []);
            $roleIds = is_array($roleIds) ? $roleIds : [$roleIds]; 
            $cleanRoleIds = array_filter($roleIds, fn($val) => !is_null($val) && $val !== '');
            
            $user->roles()->sync($cleanRoleIds);

        });

        // Xóa Cache của Spatie
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('users.index')->with('success', 'Tuyệt vời! Đã cập nhật người dùng thành công.');
    }

    public function destroy(User $user) 
    {
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        
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
        return redirect()->route('users.index')->with('success', 'Tuyệt vời! Đã khôi phục người dùng thành công.');
    }

    // Hàm cập nhật trạng thái (Active/Inactive)
    public function updateStatus(Request $request, User $user) 
    {
        // Kiểm tra dữ liệu gửi lên chỉ được phép là 'active' hoặc 'inactive'
        $validated = $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        // Cập nhật trạng thái
        $user->update([
            'status' => $validated['status']
        ]);

        // Trả về trang cũ kèm thông báo
        return back()->with('success', 'Tuyệt vời! Đã cập nhật trạng thái tài khoản.');
    }
}