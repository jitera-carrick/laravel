
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\ValidUser;

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
        $rules = [
            'details' => 'required|string',
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'user_id' => ['required', 'integer', new ValidUser],
            'area' => 'required|string',
            'menu' => 'required|string',
            'hair_concerns' => 'nullable|string',
            'priority' => 'required|string',
        ];
        return $rules;
    }
}
