
<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateShopRequest;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    // ... other methods ...

    /**
     * Update the specified shop in the database.
     *
     * @param  \App\Http\Requests\UpdateShopRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateShop(UpdateShopRequest $request): JsonResponse
    {
        $shopId = $request->route('shop');
        $shop = Shop::find($shopId);

        if (!$shop) {
            return response()->json(['message' => 'Shop not found.'], 404);
        }

        if (!Auth::user()->can('update', $shop)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $shop->update($request->validated());

        return response()->json([
            'message' => 'Shop updated successfully.',
            'shop' => $shop
        ]);
    }

    // ... other methods ...
}
