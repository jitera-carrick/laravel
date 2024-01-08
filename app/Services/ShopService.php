
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
            $shop->update(['name' => $name, 'address' => $address]);
            return 'Shop updated successfully.';
        } catch (Exception $e) {
            return 'Error updating shop: ' . $e->getMessage();
        }
    }
}
