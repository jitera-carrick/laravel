
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
        // Authorization logic can be added here if needed
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
            'request_id' => [
                'required',
                'integer',
                Rule::exists('requests', 'id')
            ],
            'image_id' => [
                'required',
                'integer',
                Rule::exists('request_images', 'id')->where(function ($query) {
                    $query->where('request_id', $this->request_id);
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
            'request_id.exists' => 'The selected request does not exist.',
            'image_id.required' => 'The image ID is required.',
            'image_id.integer' => 'The image ID must be an integer.',
            'image_id.exists' => 'The selected image does not exist or is not associated with the given request.',
        ];
    }
}
