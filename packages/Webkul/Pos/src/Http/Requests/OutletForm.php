<?php

namespace Webkul\Pos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OutletForm extends FormRequest
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
            'name'                 => ['required', 'max:50'],
            'email'                => ['required', 'email', 'max:50'],
            'phone'                => ['required', 'numeric'],
            'website'              => ['required', 'max:50'],
            'customer_care_number' => ['required', 'numeric'],
            'gst_number'           => ['required', 'max:50'],
            'status'               => ['boolean'],
            'address'              => ['required', 'max:250'],
            'state'                => ['required', 'max:50'],
            'country'              => ['required', 'max:50'],
            'city'                 => ['required', 'max:50'],
            'postcode'             => ['required', 'max:50'],
            'receipt_id'           => ['required', 'exists:pos_receipts,id'],
            'inventory_source_id'  => ['required', 'unique:pos_outlets,inventory_source_id'],
            'low_stock_qty'        => ['required', 'numeric'],
        ];

        if ($this->method() == 'PUT') {
            $rules['inventory_source_id'] = ['required', 'unique:pos_outlets,inventory_source_id,'.$this->route('id')];
        }

        return $rules;
    }
}
