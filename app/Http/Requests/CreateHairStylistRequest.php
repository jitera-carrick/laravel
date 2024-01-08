
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Rules\ValidServiceType;
use App\Rules\ValidStatus;

class CreateHairStylistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Check if the user is authenticated and if the user_id matches the authenticated user's id
        return Auth::check() && Auth::id() == $this->get('user_id');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'requested_date' => 'required|date|after:now',
            'service_type' => ['required', new ValidServiceType()],
            'status' => ['sometimes', new ValidStatus()],
            'additional_notes' => 'sometimes|string',
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
            'user_id.required' => 'The user_id field is required and must exist in the users table.',
            'requested_date.required' => 'The requested date is required.',
            'requested_date.date' => 'The requested date must be a valid date.',
            'requested_date.after' => 'The requested date must be a date after now.',
            'service_type.required' => 'The service type is required.',
            'status.sometimes' => 'The status field is optional.',
            'additional_notes.sometimes' => 'The additional notes field is optional.',
            'additional_notes.string' => 'The additional notes must be a string.',
        ];
    }
}
