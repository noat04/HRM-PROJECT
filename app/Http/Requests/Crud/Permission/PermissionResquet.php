<?php

namespace App\Http\Requests\Crud\Permission;

use Illuminate\Foundation\Http\FormRequest;
// 👇 Import thêm Rule để dùng cho update (Bí kíp ở phần LƯU Ý bên dưới)
use Illuminate\Validation\Rule;

class PermissionResquet extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Lấy ID của permission nếu đang ở trang Update (để bỏ qua check unique)
        $permissionId = $this->route('permission');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z_]+$/',
                Rule::unique('permissions', 'name')->ignore($permissionId),
            ],
            'group' => 'required|string|in:all,admin,manager,staff',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên quyền hạn.',
            'name.max' => 'Tên quyền hạn không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên quyền hạn này đã tồn tại.',
            'name.regex' => 'Tên quyền hạn chỉ được chứa chữ cái thường và dấu gạch dưới (VD: view_users).',
            
            'group.required' => 'Vui lòng nhập nhóm quyền hạn.',
            // 👇 THÊM CÂU THÔNG BÁO NÀY
            'group.in' => 'Nhóm quyền hạn không hợp lệ. Chỉ chấp nhận: all, admin, manager, staff.',
        ];
    }
}