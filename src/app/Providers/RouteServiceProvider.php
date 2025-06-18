<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ServiceProviderInterface;
use App\Core\Config;
use App\Core\Router;

class RouteServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected Router $router, protected Config $config)
    {
    }

    public function register(): void
    {
        $this->registerMiddleware();
    }

    public function boot(): void
    {
        $this->mapWebRoutes();
        $this->mapApiRoutes();
    }

    protected function registerMiddleware(): void
    {
        $this->router->registerMiddleware('auth', function ($next) {
            if (!isset($_SESSION['user'])) {
                header("Location: /login");
                exit;
            }
            return $next();
        });

        $this->router->registerMiddleware('csrf', function ($next) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($_POST['_token'] ?? '' !== ($_SESSION['_token'] ?? '')) {
                    http_response_code(403);
                    echo 'CSRF validation failed';
                    exit;
                }
            }
            return $next();
        });
    }

    protected function mapWebRoutes(): void
    {
        $routesPath = $this->config->getPath('routes') . '/web.php';

        if (!file_exists($routesPath)) {
            throw new \RuntimeException("Route file not found: $routesPath");
        }

        $router = $this->router;
        (function () use ($router) {
            require $this->config->getPath('routes') . '/web.php';
        })();
    }
    protected function mapApiRoutes(): void
    {
        $routesPath = $this->config->getPath('routes') . '/api.php';

        if (!file_exists($routesPath)) {
            throw new \RuntimeException("Route file not found: $routesPath");
        }

        $router = $this->router;
        (function () use ($router) {
            require $this->config->getPath('routes') . '/api.php';
        })();
    }
}
