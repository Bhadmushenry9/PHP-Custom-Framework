<?php
declare(strict_types=1);

namespace App\Providers;

use Illuminate\Container\Container;

abstract class BaseServiceProvider
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register bindings or services into the container.
     */
    public function register(): void
    {
        // To be overridden by child classes
    }

    /**
     * Perform any booting logic after all services are registered.
     */
    public function boot(): void
    {
        // To be overridden by child classes
    }
}
