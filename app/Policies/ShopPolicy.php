
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
        // Implement permission check logic here
        // Return true if the user is authorized, otherwise return false
    }
}
