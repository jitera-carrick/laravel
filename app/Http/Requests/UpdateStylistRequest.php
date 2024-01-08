<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStylistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Assuming all authenticated users can update a stylist request
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
            'id' => 'required|integer|exists:stylist_requests,id',
            'preferred_date' => 'required|date|after:today',
            'preferred_time' => 'required|date_format:H:i',
            'stylist_preferences' => 'required|string',
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
            'id.exists' => 'Stylist request not found.',
            'id.integer' => 'Wrong format.',
            'preferred_date.date' => 'Invalid date format.',
            'preferred_time.date_format' => 'Invalid time format.',
            'stylist_preferences.required' => 'Stylist preferences are required.',
        ];
    }
}
