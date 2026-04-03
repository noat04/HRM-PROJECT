<?php

namespace App\Http\Requests\Crud\User;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'     => 'required|string|max:255|unique:users,name',
            'email'    => [
                'required',
                'string',
                'max:255',
                'unique:users,email',
                Rule::unique('users', 'email')->ignore($this->user->id),
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ],
            'password' => 'required|string|min:8|max:255', 
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'   => 'required|in:active,inactive',

        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên người dùng.',
            'name.unique' => 'Tên người dùng đã tồn tại.',
            'name.max' => 'Tên người dùng không được vượt quá 255 ký tự.',
            
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email đã tồn tại.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'email.regex' => 'Email không hợp lệ.',
            
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.max' => 'Mật khẩu không được vượt quá 255 ký tự.',
            
            'avatar.image' => 'Ảnh đại diện phải là file ảnh.',
            'avatar.mimes' => 'Ảnh đại diện phải có định dạng jpeg, png, jpg, gif.',
            'avatar.max' => 'Ảnh đại diện không được vượt quá 2MB.',
            
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ];
    }
}
