<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopInformationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Assuming there is a method to check if the authenticated user is the owner of the shop
        // This method should be implemented in the User model or a dedicated Policy/Service
        return auth()->check() && auth()->user()->isShopOwner();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'shop_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact_number' => 'required|regex:/^(\+\d{1,3}[- ]?)?\d{10}$/',
        ];
    }

    /**
     * Get the custom messages for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'shop_name.required' => 'Shop name is required.',
            'address.required' => 'Address is required.',
            'contact_number.required' => 'Invalid contact number.',
            'contact_number.regex' => 'Invalid contact number.',
        ];
    }
}
