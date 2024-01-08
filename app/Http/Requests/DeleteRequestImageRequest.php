<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteRequestImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // The user must be authenticated and must be the owner of the hair stylist request.
        // This logic is assumed to be handled elsewhere, e.g., in a policy or middleware.
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
            'hair_stylist_request_id' => [
                'required',
                'integer',
                'exists:hair_stylist_requests,id',
            ],
            'image_path' => [
                'required',
                'string',
                Rule::exists('request_images', 'image_path')->where(function ($query) {
                    $query->where('hair_stylist_request_id', $this->hair_stylist_request_id);
                }),
            ],
        ];
    }

    public function messages()
    {
        return [
            'hair_stylist_request_id.required' => 'The hair stylist request ID is required.',
            'hair_stylist_request_id.integer' => 'The hair stylist request ID must be a valid integer.',
            'hair_stylist_request_id.exists' => 'The hair stylist request does not exist.',
            'image_path.required' => 'The image path is required.',
            'image_path.string' => 'The image path must be a string.',
            'image_path.exists' => 'The image path is invalid or does not belong to the specified request.',
        ];
    }
}
