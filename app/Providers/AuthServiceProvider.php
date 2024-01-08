
<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // $this->registerPolicies();

        $this->app->bind(\Tymon\JWTAuth\Contracts\JWTSubject::class, \App\Models\User::class);
        $this->app->bind(\Tymon\JWTAuth\Contracts\Providers\Auth::class, \Tymon\JWTAuth\Providers\Auth\Illuminate::class);
        $this->app->bind(\Tymon\JWTAuth\Contracts\Providers\JWT::class, \Tymon\JWTAuth\Providers\JWT\Namshi::class);
        $this->app->bind(\Tymon\JWTAuth\Contracts\Providers\Storage::class, \Tymon\JWTAuth\Providers\Storage\Illuminate::class);
        $this->app->bind(\Tymon\JWTAuth\Contracts\Providers\User::class, \Tymon\JWTAuth\Providers\User\EloquentUserAdapter::class);
        $this->app->bind(\Tymon\JWTAuth\Contracts\Manager::class, \Tymon\JWTAuth\Manager::class);
        $this->app->bind(\Tymon\JWTAuth\Contracts\JWTProvider::class, \Tymon\JWTAuth\JWTManager::class);
        $this->app->bind(\Tymon\JWTAuth\Contracts\Http\Parser\Parser::class, \Tymon\JWTAuth\Http\Parser\Parser::class);
        $this->app->bind(\Tymon\JWTAuth\Contracts\Validators\PayloadValidator::class, \Tymon\JWTAuth\Validators\PayloadValidator::class);
        $this->app->bind(\Tymon\JWTAuth\Contracts\Blacklist::class, \Tymon\JWTAuth\Blacklist::class);
        $this->app->bind(\Tymon\JWTAuth\Contracts\Claims\Factory::class, \Tymon\JWTAuth\Claims\Factory::class);

        //
    }
}
