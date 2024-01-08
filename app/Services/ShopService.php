<?php

namespace App\Services;

use App\Models\Shop;
use Exception;
use Illuminate\Support\Facades\Validator;

class ShopService
{
    public function updateShop($shopId, $newName, $newAddress, $newContactNumber)
    {
        // Validate the input parameters
        $validator = Validator::make([
            'shop_name' => $newName,
            'address' => $newAddress,
            'contact_number' => $newContactNumber,
        ], [
            'shop_name' => 'required',
            'address' => 'required',
            'contact_number' => 'required|regex:/^\+?[1-9]\d{1,14}$/',
        ]);

        if ($validator->fails()) {
            // You can throw an exception or handle the error messages as per your application's needs
            throw new Exception($validator->errors()->first());
        }

        $shop = Shop::find($shopId);
        if (!$shop) {
            throw new Exception("Shop not found.");
        }

        $shop->name = $newName;
        $shop->address = $newAddress;
        $shop->contact_number = $newContactNumber;
        $shop->save();
    }
}
