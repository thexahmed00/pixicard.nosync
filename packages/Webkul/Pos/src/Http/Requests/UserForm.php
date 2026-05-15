<?php

namespace Webkul\Pos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    public function prepareForValidation()
    {
        $this->merge([
            'status' => $this->status ?? 0,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'outlet_id' => ['required', 'exists:pos_outlets,id'],
            'username'  => ['required', 'alpha_dash', 'unique:pos_users,username'],
            'email'     => ['email', 'unique:pos_users,email'],
            'password'  => ['required', 'confirmed', 'min:6'],
            'firstname' => ['required', 'alpha'],
            'lastname'  => ['required', 'alpha'],
            'status'    => ['boolean'],
            'avatar.*'  => ['nullable', 'mimes:bmp,jpeg,jpg,png,webp'],
        ];

        if ($this->method() == 'PUT') {
            $rules['username'] = ['required', 'alpha_dash', 'unique:pos_users,username,'.$this->route('id')];
            $rules['email'] = ['email', 'unique:pos_users,email,'.$this->route('id')];
            $rules['password'] = ['confirmed', 'min:6'];
        }

        return $rules;
    }
}
