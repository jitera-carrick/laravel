<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateShopRequest;
use App\Http\Requests\UpdateShopInformationRequest;
use App\Models\Shop;
use App\Policies\ShopPolicy;
use App\Services\ShopService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    public function updateShop(UpdateShopRequest $request): JsonResponse
    {
        $shop = Shop::findOrFail($request->id);
        if (!ShopPolicy::update(Auth::user(), $shop)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        $shopService = new ShopService();
        $shopService->updateShop($request->id, $request->name, $request->address);
        return response()->json(['message' => 'Shop information has been updated.']);
    }

    public function updateShopInfo(UpdateShopInformationRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'shop_name' => 'required',
            'address' => 'required',
            'contact_number' => 'required|regex:/^\+?[1-9]\d{1,14}$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            $shop = Shop::findOrFail($request->id);
            if (!ShopPolicy::update(Auth::user(), $shop)) {
                return response()->json(['message' => 'Unauthorized.'], 401);
            }
            $shopService = new ShopService();
            $shopService->updateShop($request->id, $request->shop_name, $request->address, $request->contact_number);
            return response()->json(['message' => 'Shop information updated successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An unexpected error occurred on the server.'], 500);
        }
    }
}
