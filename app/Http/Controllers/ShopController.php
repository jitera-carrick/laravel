<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateShopRequest;
use App\Models\Shop;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    // ... other methods ...

    public function updateShop(UpdateShopRequest $request): JsonResponse
    {
        try {
            // Retrieve the authenticated user
            $user = Auth::user();

            // Retrieve the shop instance by the authenticated user's ID
            $shop = Shop::where('user_id', $user->id)->firstOrFail();

            // Ensure the user has permission to update the shop
            $this->authorize('update', $shop);

            // Validate the new name and address
            $validatedData = $request->validated();

            // Update the shop's information
            $shop->fill([
                'name' => $validatedData['shop_name'],
                'description' => $validatedData['shop_description'],
            ]);
            $shop->save();

            return response()->json([
                'status' => 200,
                'message' => 'Shop information updated successfully.',
                'shop' => [
                    'shop_name' => $shop->name,
                    'shop_description' => $shop->description,
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Shop not found.'
            ], 404);
        }
    }

    // ... other methods ...
}
