<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class FilterArticlesRequest extends FormRequest
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
            'title' => 'sometimes|string|max:200', // Updated max length to 200 as per requirement
            'date' => 'sometimes|date', // Changed to 'date' to validate date format
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer|min:1|max:100', // Updated to include max:100 as per requirement
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
            'title.string' => 'The title must be a string.',
            'title.max' => 'You cannot input more than 200 characters.', // Updated error message as per requirement
            'date.date' => 'Wrong date format.', // Updated error message as per requirement
            'page.integer' => 'Page must be a number.', // Updated error message as per requirement
            'page.min' => 'Page must be greater than 0.', // Updated error message as per requirement
            'limit.integer' => 'Limit must be a number.', // Updated error message as per requirement
            'limit.min' => 'The limit must be at least 1.', // Added error message for minimum limit
            'limit.max' => 'The limit must not be greater than 100.', // Added error message for maximum limit
        ];
    }
}
