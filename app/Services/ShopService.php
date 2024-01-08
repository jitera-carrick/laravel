
<?php

namespace App\Services;

use App\Models\Shop;
use Exception;

class ShopService
{
    public function updateShop($shopId, $newName, $newAddress)
    {
        $shop = Shop::find($shopId);
        if (!$shop) {
            throw new Exception("Shop not found.");
        }

        $shop->name = $newName;
        $shop->address = $newAddress;
        $shop->save();
    }
}
