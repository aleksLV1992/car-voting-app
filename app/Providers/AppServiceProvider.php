<?php

namespace App\Providers;

use App\Models\Car;
use App\Models\Vote;
use App\Policies\CarPolicy;
use App\Policies\VotePolicy;
use App\Repositories\CarRepository;
use App\Repositories\CarRepositoryInterface;
use App\Repositories\VoteRepository;
use App\Repositories\VoteRepositoryInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CarRepositoryInterface::class, CarRepository::class);
        $this->app->bind(VoteRepositoryInterface::class, VoteRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Car::class, CarPolicy::class);
        Gate::policy(Vote::class, VotePolicy::class);
    }
}
