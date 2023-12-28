<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Requests\UpdateShopRequest; // Import the UpdateShopRequest
use App\Models\User;
use App\Models\Shop; // Import the Shop model
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyEmailNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException; // Import the AuthorizationException

class UserController extends Controller
{
    // ... other methods ...

    public function updateProfile(UpdateUserProfileRequest $request): JsonResponse
    {
        // Existing updateProfile method code...
    }

    public function updateUserProfile(UpdateUserProfileRequest $request): JsonResponse
    {
        // Existing updateUserProfile method code...
    }

    // New method for updating shop information
    public function updateShop(UpdateShopRequest $request): JsonResponse
    {
        // Retrieve the shop with the given ID
        $shop = Shop::findOrFail($request->id);

        // Authorize the update using ShopPolicy
        try {
            $this->authorize('update', $shop);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'This action is unauthorized.'
            ], 403);
        }

        // Validate the new information provided by the user to ensure it is in the correct format and meets any business constraints.
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        // Update the shop with the validated data
        $shop->name = $validatedData['name'];
        $shop->address = $validatedData['address'];
        $shop->updated_at = Carbon::now(); // Set the "updated_at" column to the current datetime

        $shop->save();

        // Return a success response
        return response()->json([
            'message' => 'Shop updated successfully.',
            'shop' => $shop
        ], 200);
    }
}
