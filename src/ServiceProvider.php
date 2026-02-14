<?php

namespace JordJD\LaravelPasswordSecurityAudit;

use JordJD\LaravelPasswordSecurityAudit\Console\Commands\PasswordAudit;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PasswordAudit::class,
            ]);
        }
    }
}