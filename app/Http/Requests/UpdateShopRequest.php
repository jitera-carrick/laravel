
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
            'id' => 'required|integer',
            'name' => 'required|string',
            'address' => 'required|string',
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
            'id.required' => 'The shop ID is required.',
            'id.integer' => 'The shop ID must be an integer.',
            'name.required' => 'The shop name is required.',
            'name.string' => 'The shop name must be a string.',
            'address.required' => 'The shop address is required.',
            'address.string' => 'The shop address must be a string.',
        ];
    }
}
