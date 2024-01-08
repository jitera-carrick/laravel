
<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
// Add the Hash facade import
use Illuminate\Support\Facades\Hash;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];
    // No changes needed in the policies array

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // No changes needed in the boot method for most cases
        // If custom logic is required for password hashing, it would be added here
        // For example, if you wanted to use a different hashing algorithm:
        // Hash::extend('custom_driver', function () {
        //     return new CustomHasher;
        // });
    }
}
