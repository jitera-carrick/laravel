
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User; // Added line

class CreateStylistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Assuming all authenticated users can create a stylist request
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Other existing validation rules... (Comment updated)
            'stylist_preferences' => 'required|string',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'required|date_format:H:i',
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'user_id' => 'required|exists:users,id', // No change in this line
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'user_id.required' => 'User not found.',
            'user_id.exists' => 'User not found.',
            'preferred_date.required' => 'Invalid date format.',
            'preferred_date.date' => 'Invalid date format.',
            'preferred_time.required' => 'Invalid time format.',
            'preferred_time.date_format' => 'Invalid time format.',
            'stylist_preferences.required' => 'Stylist preferences are required.',
        ];
    }
}
