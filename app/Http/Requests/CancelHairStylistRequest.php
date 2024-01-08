<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CancelHairStylistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Only an authenticated user can cancel a request
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
            'request_id' => [
                'required',
                'integer',
                Rule::exists('hair_stylist_requests', 'id')->where(function ($query) {
                    $query->where('user_id', Auth::id());
                }),
            ],
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
            'request_id.required' => 'The request ID is required.',
            'request_id.integer' => 'The request ID must be an integer.',
            'request_id.exists' => 'Request not found.', // Updated error message as per requirement
        ];
    }
}
