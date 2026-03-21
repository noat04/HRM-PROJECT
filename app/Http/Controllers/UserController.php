<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // 👇 Import Storage để xóa ảnh cũ
use Intervention\Image\Laravel\Facades\Image; // 👇 Import thư viện ảnh
use App\Models\User;
use Inertia\Inertia;

class UserController extends Controller
{
    // Request $request: Đây giống như một "người đưa thư". 
    // Bất cứ dữ liệu gì người dùng gõ trên giao diện (ví dụ: gõ chữ "admin" vào ô tìm kiếm)
    //  đều sẽ được đóng gói và chuyển cho biến $request này giữ.
     public function index(Request $request) {
        //truy vấn (Query Builder).
        // Thay vì lấy toàn bộ dữ liệu ngay lập tức (User::all()), 
        // chúng ta chỉ chuẩn bị câu lệnh SQL để chờ xử lý.
        $query = User::query();
    
        //if ($request->has('search')...):
        // Kiểm tra xem trên ô tìm kiếm người dùng có gõ gì không.
        // Nếu có thì mới chạy đoạn code bên trong.
        if ($request->has('search') && $request->search != '') {
            // Laravel viết câu lệnh LIKE trong SQL.
            // orWhere giúp hệ thống mở rộng phạm vi: 
            // "Tìm xem cột Tên có chữ này không, HOẶC (or) cột Email có chứa chữ này không".
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
        }

        //Gửi dữ liệu sang Frontend (Vue.js) 
        // resources/js/pages/Users/Index.vue
        return Inertia::render('Users/Index', [
            // BẮT BUỘC: Tên biến phải là 'users' và dùng paginate()
            // withQueryString() sẽ tự động móc nối từ khóa vào link, biến nó thành ?
            // Ví dụ: Khi bạn gõ "admin" vào ô tìm kiếm, 
            // với QueryString() sẽ tự động biến URL thành /users?search=admin
            // Giữ lại từ khóa trên ô Input
            'users' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search'])
        ]);
    }

    // Hàm này dùng để hiển thị giao diện tạo mới
    public function create() {
        return Inertia::render('Users/Create');
    }

    public function store(Request $request){
            $validated = $request->validate([
                "name"=>"required | string | max:255 | unique:users,name",
                "email"=>"required | string | max:255 | unique:users,email",
                "password"=>"required | string | max:255 | unique:users,password",
                // Kiểm tra avatar phải là file ảnh (jpg, png...), tối đa 2MB
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'password'=> 'required | string | max:255 | unique:users,password',
                
                
            ],  [
                'password' => 'Mật khẩu không được để trống.',
                'password.max' => 'Mật khẩu không được vượt quá 255 ký tự.',
                'password.unique' => 'Mật khẩu đã tồn tại.',
                'avatar.image' => 'Ảnh đại diện phải là file ảnh.',
                'avatar.mimes' => 'Ảnh đại diện phải là file ảnh (jpg, png, gif).',
                'avatar.max' => 'Ảnh đại diện không được vượt quá 2MB.',
                'name.required' => 'Tên không được để trống.',
                'name.max' => 'Tên không được vượt quá 255 ký tự.',
                'name.unique' => 'Tên đã tồn tại.',
                'email.required' => 'Email không được để trống.',
                'email.max' => 'Email không được vượt quá 255 ký tự.',
                'email.unique' => 'Email đã tồn tại.',
                'email.email' => 'Email không hợp lệ.'
            ]); 

        // ==========================================
        // LOGIC XỬ LÝ ẢNH CHUYÊN NGHIỆP
        // ==========================================
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            
            // 1. Đặt tên file mới (Ví dụ: 1710753000_user_5.jpg)
            $filename = time() . '_user_' . $user->id . '.jpg';
            
            // 2. Tạo đường dẫn tuyệt đối đến thư mục lưu trữ
            $directory = storage_path('app/public/avatars');
            $path = $directory . '/' . $filename;

            // Đảm bảo thư mục avatars đã tồn tại, nếu chưa thì tạo mới
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // 3. Dùng Intervention để Xử lý ảnh
            Image::read($file)
                ->cover(256, 256) // Cắt ảnh lấy phần trung tâm với kích thước chuẩn 256x256
                ->toJpeg(80)      // Ép tất cả ảnh thành đuôi JPG và nén chất lượng xuống 80%
                ->save($path);    // Lưu vào ổ cứng

            // 4. Xóa ảnh cũ (nếu có) để tiết kiệm dung lượng Server
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // 5. Cập nhật đường dẫn mới vào mảng dữ liệu để lưu xuống Database
            $validated['avatar'] = 'avatars/' . $filename;
        }
            User::create($validated);
            return redirect()->route('users.index')->with('success', 'Tuyệt vời! Đã thêm người dùng thành công.');
    }

    public function edit(User $user) {
        return Inertia::render('Users/Edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user) {
        $validated = $request->validate([
            "name"=>"required | string | max:255 | unique:users,name," . $user->id,
            "email"=>"required | string | max:255 | unique:users,email," . $user->id,
            "password"=>"required | string | max:255 | unique:users,password," . $user->id,
            // Kiểm tra avatar phải là file ảnh (jpg, png...), tối đa 2MB
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password'=> 'required | string | max:255 | unique:users,password,' . $user->id,
            
            
        ],  [
            'password' => 'Mật khẩu không được để trống.',
            'password.max' => 'Mật khẩu không được vượt quá 255 ký tự.',
            'password.unique' => 'Mật khẩu đã tồn tại.',
            'avatar.image' => 'Ảnh đại diện phải là file ảnh.',
            'avatar.mimes' => 'Ảnh đại diện phải là file ảnh (jpg, png, gif).',
            'avatar.max' => 'Ảnh đại diện không được vượt quá 2MB.',
            'name.required' => 'Tên không được để trống.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên đã tồn tại.',
            'email.required' => 'Email không được để trống.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'email.unique' => 'Email đã tồn tại.',
            'email.email' => 'Email không hợp lệ.'
        ]); 

        // Xử lý lưu ảnh
        if ($request->hasFile('avatar')) {
            // Lưu ảnh vào thư mục 'storage/app/public/avatars'
            $path = $request->file('avatar')->store('avatars', 'public');
            // Gán đường dẫn vào mảng để lưu xuống Database
            $validated['avatar'] = $path; 
        }
        $user->update($validated);
        return redirect()->route('users.index')->with('success', 'Tuyệt vời! Đã cập nhật người dùng thành công.');
    }

    public function destroy(User $user) {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Tuyệt vời! Đã xóa người dùng thành công.');
    }

    public function show($id) {
        //Lấy ảnh đại diện của người dùng
        try{
            $user = User::findOrFail($id);
            return Inertia::render('Users/Show', [
                'user' => $user,
            ]);
        }catch(\Illuminate\Database\QueryException $e){
            return redirect()->route('users.index')->with('error', 'Lỗi hệ thống! Vui lòng thử lại sau.');
        }
    }

    public function restore($id) {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        return redirect()->route('users.index')->with('success', 'Tuyệt vời! Đã khôi phục người dùng thành công.');
    }

}
