
<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateShopRequest;
use App\Services\ShopService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ShopController extends Controller
{
    public function updateShop(UpdateShopRequest $request): JsonResponse
    {
        if (!Gate::allows('update', $request->shop)) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $shopService = new ShopService();
        $success = $shopService->updateShop($request->shop->id, $request->validated()['name'], $request->validated()['address']);

        return $success
            ? response()->json(['message' => 'Shop updated successfully.'], 200)
            : response()->json(['message' => 'Failed to update shop.'], 500);
    }
}
