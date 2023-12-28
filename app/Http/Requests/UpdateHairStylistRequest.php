<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
            'area' => 'sometimes|string',
            'menu' => 'sometimes|string',
            'hair_concerns' => 'sometimes|string|max:65535', // Text type can hold up to 65535 characters
            'image_paths' => 'sometimes|array',
            'image_paths.*' => 'sometimes|string', // Validate each item in the array if image_paths is provided
        ];
    }
}
