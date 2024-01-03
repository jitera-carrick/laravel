
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Shop;

class ShopPolicy
{
    /**
     * Determine if the given shop can be updated by the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Shop  $shop
     * @return bool
     */
    public function update(User $user, Shop $shop): bool
    {
        // Implement your logic to determine if $user can update $shop
        // For example, check if user is the owner of the shop
        return $user->id === $shop->user_id;
    }
}
