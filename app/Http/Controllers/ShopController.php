<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateShopRequest;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ShopResource;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    public function updateShop(UpdateShopRequest $request, $id): JsonResponse
    {
        $shop = Shop::findOrFail($id);
        if (!Auth::user()->can('update', $shop)) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $shopService = new ShopService();
        return $shopService->updateShop($id, $request->validated());
    }

    public function updateShopInfo(UpdateShopRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|integer|exists:shops,id',
            'shop_info' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $shopId = $request->input('shop_id');
        $shop = Shop::findOrFail($shopId);
        if (!Auth::user()->can('update', $shop)) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $shopService = new ShopService();
        try {
            $updatedShop = $shopService->updateShopInfo($shopId, $request->input('shop_info'));
            return response()->json([
                'status' => 200,
                'message' => 'Shop information has been successfully updated.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating shop: ' . $e->getMessage()], 500);
        }
    }

    // ... other methods ...
}
