<?php

namespace App\Http\Requests\Crud\Position;

use Illuminate\Foundation\Http\FormRequest;

class PositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => [
                'required',
                'string',
                'max:255',
                'unique:positions,name',
                'regex:/^[\pL\s\-\_]+$/u',
            ],
            'code'        => [
                'required',
                'string',
                'max:50',
                'unique:positions,code',
                'regex:/^[A-Z0-9\-\_]+$/',
            ],
            'level'       => 'required|integer|min:0',
            'salary_min'  => 'nullable|numeric|min:0',
            'salary_max'  => 'nullable|numeric|gte:salary_min',
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Vui lòng nhập tên chức vụ.',
            'name.max'           => 'Tên chức vụ không được vượt quá 255 ký tự.',
            'name.unique'        => 'Tên chức vụ này đã tồn tại.',
            'name.regex'         => 'Tên chức vụ không hợp lệ, không được chứa các ký tự đặc biệt (chỉ cho phép chữ cái, dấu phẩy, khoảng trắng).',
            
            'code.required'      => 'Vui lòng nhập mã chức vụ.',
            'code.unique'        => 'Mã chức vụ này đã tồn tại.',
            'code.regex'         => 'Mã chức vụ chỉ được chứa chữ cái in hoa, số hoặc ký tự - và _.',
            
            'level.required'     => 'Vui lòng chọn cấp bậc.',
            'level.integer'      => 'Cấp bậc phải là số nguyên.',
            'level.min'          => 'Cấp bậc phải lớn hơn hoặc bằng 0.',
            
            'salary_min.numeric' => 'Mức lương tối thiểu phải là số.',
            'salary_min.min'     => 'Mức lương tối thiểu không được âm.',
            
            'salary_max.numeric' => 'Mức lương tối đa phải là số.',
            'salary_max.gte'     => 'Mức lương tối đa phải lớn hơn hoặc bằng mức lương tối thiểu.',
        ];
    }
}
