<?php

namespace App\Providers;

use App\Repositories\Lead\EloquentLeadRepository;
use App\Repositories\Lead\LeadRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public array $bindings = [
        LeadRepositoryInterface::class => EloquentLeadRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        // Bindings are automatically registered from $bindings property
        // Add manual bindings here if needed for complex scenarios
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
