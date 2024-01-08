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
            'user_id' => 'required|integer|exists:users,id',
            'requested_date' => 'required|date|after:now',
            'service_type' => ['required', new ValidServiceType()],
            'status' => ['required', new ValidStatus()],
            'additional_notes' => 'sometimes|string|max:1000',
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
            'user_id.required' => 'User not found.',
            'user_id.integer' => 'The user_id must be an integer.',
            'user_id.exists' => 'User not found.',
            'requested_date.required' => 'Invalid date format.',
            'requested_date.date' => 'Invalid date format.',
            'requested_date.after' => 'The requested date must be a date after now.',
            'service_type.required' => 'Invalid service type.',
            'status.required' => 'Invalid status.',
            'additional_notes.sometimes' => 'The additional notes field is optional.',
            'additional_notes.string' => 'The additional notes must be a string.',
            'additional_notes.max' => 'Additional notes too long.',
        ];
    }
}
