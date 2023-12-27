<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreYourSpecificRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Assuming the user is authorized to make this request
        // You can add your authorization logic here
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'area_id' => [
                'required',
                'integer',
                Rule::exists('areas', 'id'), // Ensure the area_id exists in the areas table
            ],
            'menu_id' => [
                'required',
                'integer',
                Rule::exists('menus', 'id'), // Ensure the menu_id exists in the menus table
            ],
            'hair_concerns' => 'string|max:3000', // Validate hair_concerns as a string with a max length of 3000
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'area_id.required' => 'The area selection is required.',
            'area_id.integer' => 'The area selection must be a valid integer.',
            'area_id.exists' => 'The selected area is invalid.',
            'menu_id.required' => 'The menu selection is required.',
            'menu_id.integer' => 'The menu selection must be a valid integer.',
            'menu_id.exists' => 'The selected menu is invalid.',
            'hair_concerns.string' => 'Hair concerns must be a string.',
            'hair_concerns.max' => 'Hair concerns may not be greater than 3000 characters.',
        ];
    }
}
