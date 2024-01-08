<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
        // Example: return Auth::check();
        return true; // Assuming all authenticated users can update a shop
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'shop_id' => 'required|integer|exists:shops,id',
            'shop_info' => 'required|string',
            // 'name' => 'required|string|max:255', // Removed as per the new requirement
            // 'address' => 'required|string|max:500', // Removed as per the new requirement
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
            'shop_id.required' => 'Invalid shop ID.',
            'shop_id.integer' => 'Invalid shop ID.',
            'shop_id.exists' => 'Invalid shop ID.',
            'shop_info.required' => 'Invalid shop information.',
            'shop_info.string' => 'Invalid shop information.',
            // 'name.required' => 'The name field is required.', // Removed as per the new requirement
            // 'name.string' => 'The name must be a string.', // Removed as per the new requirement
            // 'name.max' => 'The name may not be greater than 255 characters.', // Removed as per the new requirement
            // 'address.required' => 'The address field is required.', // Removed as per the new requirement
            // 'address.string' => 'The address must be a string.', // Removed as per the new requirement
            // 'address.max' => 'The address may not be greater than 500 characters.', // Removed as per the new requirement
        ];
    }
}
