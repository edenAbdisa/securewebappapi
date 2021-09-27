<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::routes(function ($router) {
            $router->forAccessTokens();
            $router->forPersonalAccessTokens();
            $router->forTransientTokens();
        });
        Passport::tokensCan([
            'user' => 'Access user endpoint',
            'admin' => 'Access admin endpoint'
        ]);
        Passport::setDefaultScope([
            'user',
        ]);
        $this->commands([
            \Laravel\Passport\Console\InstallCommand::class,
            \Laravel\Passport\Console\ClientCommand::class,
            \Laravel\Passport\Console\KeysCommand::class,
        ]);
        Passport::enableImplicitGrant();
        $expireAt = \Carbon\Carbon::now()->addDays(7);
        Passport::tokensExpireIn($expireAt);
        Passport::refreshTokensExpireIn($expireAt);
        Passport::personalAccessTokensExpireIn($expireAt);
        // Passport::tokensExpireIn(\Carbon\Carbon::now()->addMinutes(10));
        // Passport::refreshTokensExpireIn(\Carbon\Carbon::now()->addDays(1));
        // Passport::personalAccessTokensExpireIn(\Carbon\Carbon::now()->addDays(1));

        //
    }
}
