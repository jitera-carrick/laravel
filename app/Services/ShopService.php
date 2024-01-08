
<?php

namespace App\Services;

use App\Models\Shop;
use Exception;

class ShopService
{
    public function updateShop($id, $name, $address)
    {
        try {
            $shop = Shop::findOrFail($id);
            $shop->name = $name;
            $shop->address = $address;
            $shop->save();
            
            return ['success' => true, 'message' => 'Shop updated successfully.'];
        } catch (Exception $e) {
            throw $e;
        }
    }
}
