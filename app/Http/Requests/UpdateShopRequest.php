<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Assuming there's an 'update-shop' policy registered for Shop model
        return $this->user()->can('update-shop', $this->route('shop'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'shop_id' => [
                'required',
                Rule::exists('shops', 'id'), // Assuming 'shops' is the table name for Shop model
            ],
            'name' => [
                'required',
                'string',
                'max:255', // Assuming the business rule for name is a maximum of 255 characters
                // Add any other business rules for the name format here
            ],
            'address' => [
                'required',
                'string',
                // Add any other validation rules for the address here
            ],
        ];
    }
}
