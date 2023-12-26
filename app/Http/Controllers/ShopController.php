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

    // ... other methods in the controller ...
}
