
<?php

namespace App\Services;

use App\Models\Shop;
use Exception;

class ShopService
{
    public function updateShop(int $shop_id, string $name, string $address): bool
    {
        $shop = Shop::find($shop_id);
        if (!$shop) {
            return false;
        }
        $shop->name = $name;
        $shop->address = $address;
        return $shop->save();
    }
}
