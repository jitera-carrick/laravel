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
            $shop = Shop::findOrFail($request->input('shop_id'));

            // Ensure the user has permission to update the shop
            $this->authorize('update', $shop);

            // Validate the new name and address
            $validatedData = $request->validated();

            // Update the shop's information
            $shop->fill($validatedData);
            $shop->save();

            return response()->json([
                'message' => 'Shop updated successfully.',
                'shop' => $shop
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Shop not found.'
            ], 404);
        }
    }

    // ... other methods ...
}
