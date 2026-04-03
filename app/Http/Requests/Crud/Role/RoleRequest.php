<?php

namespace App\Http\Requests\Crud\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // 👇 1. BẮT BUỘC IMPORT RULE

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // 👇 2. Lấy ID của vai trò đang sửa từ URL (Sẽ là null nếu đang ở form Tạo mới)
        $roleId = $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // 👇 3. BÍ KÍP Ở ĐÂY: Sẽ báo trùng, nhưng BỎ QUA cái role đang sửa!
                Rule::unique('roles', 'name')->ignore($roleId),
                'regex:/^[\pL\s\-\_]+$/u', // Chỉ chữ cái, khoảng trắng, gạch ngang, gạch dưới
            ],
            'display_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\pL\s\-\_]+$/u',
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
                // 👇 Mình đã thêm dấu phẩy (\,) và dấu chấm (\.) để gõ được câu mô tả dài
                'regex:/^[\pL\s\-\_\,\.]+$/u', 
            ],
            'permission_ids' => 'nullable|array', 
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên vai trò.',
            'name.unique' => 'Tên vai trò đã tồn tại.',
            'name.regex' => 'Tên vai trò không hợp lệ (chỉ cho phép chữ cái, gạch ngang, gạch dưới, khoảng trắng).',
            
            'display_name.required' => 'Vui lòng nhập tên hiển thị.',
            'display_name.max' => 'Tên hiển thị không được vượt quá 255 ký tự.',
            'display_name.regex' => 'Tên hiển thị không hợp lệ (chỉ cho phép chữ cái, gạch ngang, gạch dưới, khoảng trắng).',
            
            'description.max' => 'Mô tả không được vượt quá 255 ký tự.',
            'description.regex' => 'Mô tả không hợp lệ (chỉ cho phép chữ cái, số, dấu chấm, phẩy, khoảng trắng).',
        ];
    }
}