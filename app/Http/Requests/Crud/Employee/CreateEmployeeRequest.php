<?php

namespace App\Http\Requests\Crud\Employee; // 👈 Sửa thành thế này

use App\Concerns\PasswordValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required',
            'user_id' => 'required',
            'position_id' => 'required',
            'department_id' => 'required',
            'manager_id' => 'nullable',
            'employee_code' => 'required',
            'avatar' => 'nullable',
            'gender' => 'required',
            'birthday' => 'required',
            'address' => 'required',
            'join_date' => 'required',
            'resignation_date' => 'nullable',
            'bank_account_number' => 'nullable',
            'bank_name' => 'nullable',
            'status' => [
                'required',
                'in:probation,official,resigned,paused',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập tên nhân viên.',
            'user_id.required' => 'Vui lòng chọn tài khoản đăng nhập.',
            'position_id.required' => 'Vui lòng chọn chức vụ.',
            'department_id.required' => 'Vui lòng chọn phòng ban.',
            'employee_code.required' => 'Vui lòng nhập mã nhân viên.',
            'gender.required' => 'Vui lòng chọn giới tính.',
            'birthday.required' => 'Vui lòng chọn ngày sinh.',
            'address.required' => 'Vui lòng nhập địa chỉ.',
            'join_date.required' => 'Vui lòng chọn ngày bắt đầu làm việc.',
            'status.required' => 'Vui lòng chọn trạng thái.',
        ];
    }
}
