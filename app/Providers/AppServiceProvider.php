<?php

namespace App\Providers;

use App\Models\Pricing;
use App\Models\ServiceAccount;
use App\Models\User;
use App\Policies\PricingPolicy;
use App\Policies\ServiceAccountPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register authorization policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(ServiceAccount::class, ServiceAccountPolicy::class);
        Gate::policy(Pricing::class, PricingPolicy::class);
    }
}
