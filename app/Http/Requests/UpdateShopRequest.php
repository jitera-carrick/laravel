<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Authorization logic goes here
        return true; // Assuming all authenticated users can update shops
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'shop_name' => 'required|string', // Updated field name to match requirement
            'address' => 'required|string',
            'contact_number' => 'required|regex:/^(\+\d{1,3}[- ]?)?\d{10}$/',
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'shop_name.required' => 'Shop name is required.', // Updated error message to match requirement
            'shop_name.string' => 'The shop name must be a string.',
            'address.required' => 'Address is required.', // Updated error message to match requirement
            'address.string' => 'The shop address must be a string.',
            'contact_number.required' => 'Contact number is required.',
            'contact_number.regex' => 'Invalid contact number.', // Updated error message to match requirement
        ];
    }
}
