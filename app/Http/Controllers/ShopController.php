
<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateShopRequest;
use App\Models\Shop;
use App\Policies\ShopPolicy;
use App\Services\ShopService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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
}
