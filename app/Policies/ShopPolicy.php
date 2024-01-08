
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Shop;

class ShopPolicy
{
    /**
     * Determine if the user can update the shop.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Shop  $shop
     * @return bool
     */
    public function update(User $user, Shop $shop)
    {
        // Implement your logic to determine if the user can update the shop
        // For example, check if the user is the owner of the shop
        return $user->id === $shop->user_id;
    }
}
