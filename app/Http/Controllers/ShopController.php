
<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateShopRequest;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ShopController extends Controller
{
    /**
     * Update the specified shop in storage.
     *
     * @param  \App\Http\Requests\UpdateShopRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateShopRequest $request): JsonResponse
    {
        try {
            $request->authorize();

            $shop = Shop::findOrFail($request->validated()['id']);
            $shop->fill($request->validated(['name', 'address']));
            $shop->save();

            return response()->json(['message' => 'Shop information updated successfully.']);
        } catch (\Throwable $e) {
            Log::error('Shop update failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update shop information.'], 500);
        }
    }
}
