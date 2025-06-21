<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Config;
use Illuminate\Routing\Router;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\VerifyCsrfToken;
use App\Contracts\ServiceProviderInterface;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class RouteServiceProvider implements ServiceProviderInterface
{
    public function __construct(
        protected Router $router,
        protected Config $config
    ) {}

    public function register(): void
    {
        $this->registerMiddlewareAliases();
        $this->registerMiddlewareGroups();
    }

    public function boot(): void
    {
        $this->mapWebRoutes();
        $this->mapApiRoutes();
    }

    protected function registerMiddlewareAliases(): void
    {
        $this->router->aliasMiddleware('auth', Authenticate::class);
        $this->router->aliasMiddleware('session', StartSession::class);
        $this->router->aliasMiddleware('csrf', VerifyCsrfToken::class);
        $this->router->aliasMiddleware('share.errors', ShareErrorsFromSession::class);
    }

    protected function registerMiddlewareGroups(): void
    {
        $this->router->middlewareGroup('web', [
            'session',
            'csrf',
            'share.errors',
        ]);

        $this->router->middlewareGroup('auth', [
            'web',
            'auth',
        ]);
    }

    protected function mapWebRoutes(): void
    {
        $routesPath = $this->config->getPath('routes') . '/web.php';

        if (!file_exists($routesPath)) {
            throw new \RuntimeException("Web route file not found: $routesPath");
        }

        require $routesPath;
    }

    protected function mapApiRoutes(): void
    {
        $routesPath = $this->config->getPath('routes') . '/api.php';

        if (!file_exists($routesPath)) {
            throw new \RuntimeException("API route file not found: $routesPath");
        }

        require $routesPath;
    }
}
