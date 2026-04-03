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
        $restore = User::with('roles')->onlyTrashed()->get();
        $query = User::with('roles');
    
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
        }

        return Inertia::render('Users/Index', [
            'users' => $query->paginate(10)->withQueryString(),
            'restore' => $restore,
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

}