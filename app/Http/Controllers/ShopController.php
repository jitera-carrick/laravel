<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    // Existing methods in the controller ...

    /**
     * Update shop information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateShopInformation(Request $request)
    {
        // Perform an authorization check
        $this->authorize('update', Shop::class);

        // Retrieve the shop by its ID
        $shop = Shop::find($request->route('id'));
        if (!$shop) {
            return response()->json(['message' => 'Shop not found'], 404);
        }

        // Validate the request
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ])->validate();

        // Update the shop's information
        $shop->update($validatedData);

        // Update the "updated_at" column with the current timestamp
        $shop->touch();

        // Return a JSON response with a success message and the updated shop information
        return response()->json([
            'message' => 'Shop information updated successfully',
            'shop' => $shop,
        ]);
    }

    /**
     * Update the authenticated user's shop information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateUserShop(Request $request)
    {
        // Authenticate the user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'shop_name' => 'required|string|max:255',
            'shop_description' => 'required|string|max:1000',
        ], [
            'shop_name.required' => 'The shop name is required.',
            'shop_description.max' => 'The shop description cannot exceed 1000 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Retrieve the user's shop and update it
        $shop = $user->shop; // Assuming the User model has a 'shop' relationship
        if (!$shop) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $shop->update([
            'name' => $request->input('shop_name'),
            'description' => $request->input('shop_description'),
        ]);

        // Update the "updated_at" column with the current timestamp
        $shop->touch();

        // Return the response
        return response()->json([
            'status' => 200,
            'message' => 'Shop information updated successfully.',
            'shop' => $shop->fresh(),
        ]);
    }

    // ... other methods in the controller ...
}
