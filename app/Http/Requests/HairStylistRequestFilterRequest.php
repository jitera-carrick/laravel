<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class HairStylistRequestFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'service_details' => 'sometimes|string|max:500',
            'preferred_date' => 'sometimes|date',
            'status' => 'sometimes|in:pending,approved,rejected',
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer|min:1|max:100',
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
            'service_details.string' => 'The service details must be a string.',
            'service_details.max' => 'Service details cannot exceed 500 characters.',
            'preferred_date.date' => 'Invalid date format.',
            'status.in' => 'Invalid status value.',
            'page.integer' => 'Page must be a number greater than 0.',
            'page.min' => 'Page must be a number greater than 0.',
            'limit.integer' => 'Limit must be a number.',
            'limit.min' => 'The limit must be at least 1.',
            'limit.max' => 'The limit may not be greater than 100.',
        ];
    }
}
