<?php

namespace Webkul\Pos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankForm extends FormRequest
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
        return [
            'name'     => ['required', 'max:50'],
            'email'    => ['required', 'email'],
            'phone'    => ['required', 'numeric'],
            'status'   => ['boolean'],
            'address'  => ['required', 'max:250'],
            'agent_id' => ['required', 'exists:pos_users,id'],
        ];
    }
}
