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

    // This method is updated to handle the new requirement
    public function updateShopInfo($shopId, $shopInfo)
    {
        try {
            $shop = Shop::findOrFail($shopId);
            // Assuming $shopInfo is a JSON string that needs to be converted to an array
            $infoArray = json_decode($shopInfo, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid shop information.");
            }
            // Validate the $infoArray to contain 'name' and 'address'
            if (!isset($infoArray['name']) || !isset($infoArray['address'])) {
                throw new Exception("Invalid shop information.");
            }
            $shop->update($infoArray);
            return [
                'status' => 200,
                'message' => 'Shop information has been successfully updated.'
            ];
        } catch (Exception $e) {
            // Determine the appropriate response code based on the exception message
            $responseCode = 500;
            $errorMessage = $e->getMessage();
            if ($errorMessage === "Invalid shop information.") {
                $responseCode = 422;
            } elseif ($errorMessage === "No query results for model [App\\Models\\Shop].") {
                $responseCode = 400;
            }
            return [
                'status' => $responseCode,
                'message' => 'Error updating shop: ' . $errorMessage
            ];
        }
    }
}
