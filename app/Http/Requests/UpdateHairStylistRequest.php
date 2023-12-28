<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateHairStylistRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Check if the user is logged in and the request_id belongs to the user
        $user = Auth::user();
        if (!$user || !$this->requestBelongsToUser($user->id, $this->request_id)) {
            return false;
        }
        return true;
    }

    /**
     * Check if the request belongs to the user.
     *
     * @param int $userId
     * @param int $requestId
     * @return bool
     */
    protected function requestBelongsToUser($userId, $requestId)
    {
        // Assuming RequestModel exists and has a relationship with User
        // Replace RequestModel with the actual model name
        $request = RequestModel::find($requestId);
        return $request && $request->user_id === $userId;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'request_id' => 'required|integer|exists:requests,id',
            'user_id' => 'required|integer|exists:users,id',
            'area' => ['nullable', 'string', Rule::requiredIf(function () {
                return $this->area !== null && trim($this->area) === '';
            })],
            'menu' => ['nullable', 'string', Rule::requiredIf(function () {
                return $this->menu !== null && trim($this->menu) === '';
            })],
            'hair_concerns' => 'nullable|string|max:3000',
            'image_paths' => 'sometimes|array',
            'image_paths.*' => 'sometimes|string',
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
            'area.required' => 'The area field is required when provided and cannot be empty.',
            'menu.required' => 'The menu field is required when provided and cannot be empty.',
            'hair_concerns.max' => 'The hair concerns may not be greater than 3000 characters.',
        ];
    }
}
