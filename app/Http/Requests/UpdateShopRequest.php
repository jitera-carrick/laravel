
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateShopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Assuming the user needs to have a specific role or permission to update shop information
        $user = Auth::user();
        return $user && $user->can('update-shop');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'required|integer|exists:shops,id',
            'name' => 'required|string',
            'address' => 'required|string',
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
            'id.required' => 'The shop ID is required.',
            'id.integer' => 'The shop ID must be an integer.',
            'id.exists' => 'The selected shop ID does not exist.',
            'name.required' => 'The name field is required.',
            'address.required' => 'The address field is required.',
        ];
    }
}
