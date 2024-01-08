<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateHairStylistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // The user needs to be authenticated to create a hair stylist request.
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
            'details' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'request_image_id' => 'nullable|exists:request_images,id',
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
            'details.required' => 'Details are required.',
            'user_id.required' => 'The user_id field is required.',
            'user_id.exists' => 'User not found.',
            'request_image_id.exists' => 'Request image not found.',
        ];
    }
}
