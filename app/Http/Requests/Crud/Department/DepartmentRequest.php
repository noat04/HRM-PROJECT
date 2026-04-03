<?php

namespace App\Http\Requests\Crud\Department;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'parent_id'   => 'nullable|integer', // Có thể để nullable nếu là phòng ban gốc (chưa có cha), nếu bắt buộc thì đổi lại thành required
            'manager_id'  => 'nullable|integer', // Có thể thêm rule exists:users,id hoặc exists:employees,id
            'level'       => 'required|integer|min:0',
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Vui lòng nhập tên phòng ban.',
            'name.max'           => 'Tên phòng ban không được vượt quá 255 ký tự.',
            'parent_id.required' => 'Vui lòng chọn phòng ban cha.',
            'level.required'     => 'Vui lòng chọn cấp bậc.',
            'level.integer'      => 'Cấp bậc phải là số.',
        ];
    }
}
