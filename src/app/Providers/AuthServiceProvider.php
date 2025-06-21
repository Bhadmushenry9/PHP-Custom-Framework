<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ServiceProviderInterface;
use App\Model\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Container\Container;
use Illuminate\Session\SessionManager;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Http\Request;

class AuthServiceProvider implements ServiceProviderInterface
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function register(): void
    {
        // Setup database connection (Eloquent)
        $this->container->singleton('db', function ($container) {
            $capsule = new Capsule;
            // Assuming your config stores DB under 'db' key, adjust as needed
            $capsule->addConnection($container->get('config')->db);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            return $capsule->getDatabaseManager();
        });

        // Setup User Provider (Eloquent)
        $this->container->singleton('auth.provider', function ($container) {
            // Ensure 'hash' service is bound, e.g. with HashManager
            return new EloquentUserProvider(
                $container->make('hash'),
                User::class
            );
        });

        // Setup AuthManager
        $this->container->singleton('auth', function ($container) {
            return new AuthManager($container);
        });

        // Setup default guard (session guard)
        $this->container->singleton('auth.driver', function ($container) {
            /** @var SessionManager $sessionManager */
            $sessionManager = $container->get(SessionManager::class);
            $session = $sessionManager->driver();

            $provider = $container->get('auth.provider');
            $request = $container->make(Request::class);

            $guard = new SessionGuard('web', $provider, $session, $request);

            // If you want to bind cookie jar or events, you can do it here:
            if ($container->bound('cookie')) {
                $guard->setCookieJar($container->get('cookie'));
            }
            if ($container->bound('events')) {
                $guard->setDispatcher($container->get('events'));
            }

            return $guard;
        });

        // Aliases for convenience
        $this->container->alias('auth', AuthManager::class);
        $this->container->alias('auth.driver', SessionGuard::class);
    }

    public function boot(): void
    {
        // You can place any post-registration logic here if needed
    }
}
