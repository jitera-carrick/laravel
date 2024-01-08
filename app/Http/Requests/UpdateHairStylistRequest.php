
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Enums\StatusEnum; // Assuming StatusEnum exists and is in the App\Enums namespace

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
        if (!$user || !$this->requestBelongsToUser($user->id, $this->request('request_id'))) {
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
        $request = \App\Models\Request::find($requestId); // Updated to use the correct Request model
        return $request && $request->user_id === $userId;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $userId = Auth::id(); // Get the authenticated user's ID

        return [
            'user_id' => ['required', 'integer', 'exists:users,id', Rule::exists('users', 'id')->where(function ($query) {
                return $query->where('id', Auth::id());
            })],
            'request_id' => 'required|integer|exists:requests,id',
            'area' => 'required|string',
            'menu' => 'required|string',
            'hair_concerns' => 'nullable|string|max:3000',
            'image_paths' => 'nullable|array',
            'image_paths.*' => 'nullable|file|mimes:png,jpg,jpeg|max:5120',
            'details' => 'required|string',
            'id' => [
                'required',
                Rule::exists('hair_stylist_requests', 'id')->where(function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }),
            ],
            'status' => ['required', Rule::in(StatusEnum::getValues())],
            'request_image_id' => 'sometimes|exists:request_images,id',
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
            'user_id.required' => 'The user ID is required.',
            'user_id.integer' => 'The user ID must be an integer.',
            'user_id.exists' => 'The selected user ID does not exist.',
            'request_id.required' => 'The request ID is required.',
            'request_id.integer' => 'The request ID must be an integer.',
            'request_id.exists' => 'The selected request ID is invalid.',
            'area.required' => 'The area field is required.',
            'menu.required' => 'The menu field is required.',
            'hair_concerns.max' => 'The hair concerns may not be greater than 3000 characters.',
            'image_paths.array' => 'The image paths must be an array.',
            'image_paths.*.file' => 'Each image path must be a file.',
            'image_paths.*.mimes' => 'Each file must be of type: png, jpg, jpeg.',
            'image_paths.*.max' => 'Each file may not be greater than 5MB.',
            'details.required' => 'The details field is required.',
            'id.required' => 'The ID is required.',
            'id.exists' => 'The selected ID does not exist or does not belong to the user.',
            'status.required' => 'The status field is required.',
            'status.in' => 'The selected status is invalid.',
            'request_image_id.exists' => 'The selected request image ID is invalid.',
        ];
    }
}
