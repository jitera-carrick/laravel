<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Shop;

class ShopPolicy
{
    /**
     * Determine if the given user can update the given shop.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Shop  $shop
     * @return bool
     */
    public function update(User $user, Shop $shop)
    {
        return $user->id === $shop->user_id; // User must be the owner of the shop to update it
    }
}
