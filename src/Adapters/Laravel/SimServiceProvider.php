<?php

namespace Sim\Adapters\Laravel;

use Illuminate\Support\ServiceProvider;

class SimServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(){
        $this->app->bind(\Sim\Environment::class, function(){
            return new \Sim\Environment();
        });
    }
}