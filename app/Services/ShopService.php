
<?php

namespace App\Services;

use App\Models\Shop;
use Exception;

class ShopService
{
    public function updateShop($shop_id, $name, $address)
    {
        $shop = Shop::find($shop_id);
        if (!$shop) {
            throw new Exception("Shop not found.");
        }
        $shop->name = $name;
        $shop->address = $address;
        return $shop->save();
    }
}
