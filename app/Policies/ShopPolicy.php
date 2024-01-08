
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Shop;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShopPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Shop $shop)
    {
        // Assuming 'update-shop' is a permission set in the auth config or user has 'admin' role
        return $user->user_type === 'admin' || $user->can('update-shop');
    }
}
