
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
            'image_path' => [
                'required',
                'string',
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
            'image_path.required' => 'The image path is required.',
            'image_path.string' => 'The image path must be a string.',
        ];
    }
}
