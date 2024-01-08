
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
    public function updateShop(UpdateShopRequest $request, $id): JsonResponse
    {
        $shop = Shop::findOrFail($id);
        if (!Auth::user()->can('update', $shop)) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $shopService = new ShopService();
        return $shopService->updateShop($id, $request->validated());
    }

    // ... other methods ...
}
