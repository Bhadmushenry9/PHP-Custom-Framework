<?php
declare(strict_types=1);

namespace App\Core;

use App\Enums\HttpMethod;
use App\Exception\RouteNotFoundException;
use Illuminate\Container\Container;

class Router
{
    protected array $routes = [];
    protected array $middlewareRegistry = [];

    public function __construct(protected Container $container) {}

    public function get(string $route, callable|array $action, array $middleware = []): self
    {
        return $this->register(HttpMethod::GET, $route, $action, $middleware);
    }

    public function post(string $route, callable|array $action, array $middleware = []): self
    {
        return $this->register(HttpMethod::POST, $route, $action, $middleware);
    }

    public function put(string $route, callable|array $action, array $middleware = []): self
    {
        return $this->register(HttpMethod::PUT, $route, $action, $middleware);
    }

    public function patch(string $route, callable|array $action, array $middleware = []): self
    {
        return $this->register(HttpMethod::PATCH, $route, $action, $middleware);
    }

    public function delete(string $route, callable|array $action, array $middleware = []): self
    {
        return $this->register(HttpMethod::DELETE, $route, $action, $middleware);
    }

    public function options(string $route, callable|array $action, array $middleware = []): self
    {
        return $this->register(HttpMethod::OPTIONS, $route, $action, $middleware);
    }

    public function head(string $route, callable|array $action, array $middleware = []): self
    {
        return $this->register(HttpMethod::HEAD, $route, $action, $middleware);
    }

    public function trace(string $route, callable|array $action, array $middleware = []): self
    {
        return $this->register(HttpMethod::TRACE, $route, $action, $middleware);
    }

    public function connect(string $route, callable|array $action, array $middleware = []): self
    {
        return $this->register(HttpMethod::CONNECT, $route, $action, $middleware);
    }

    public function register(HttpMethod $method, string $route, callable|array $action, array $middleware = []): self
    {
        $route = rtrim($route, '/') ?: '/';
        $this->routes[$method->value][$route] = [
            'action' => $action,
            'middleware' => $middleware,
        ];
        return $this;
    }

    public function registerMiddleware(string $alias, callable $middleware): void
    {
        $this->middlewareRegistry[$alias] = $middleware;
    }

    public function resolve(string $requestUri, HttpMethod $requestMethod): mixed
    {
        $path = parse_url($requestUri, PHP_URL_PATH);
        $method = $requestMethod->value;
        $routes = $this->routes[$method] ?? [];

        if (isset($routes[$path])) {
            return $this->runMiddlewareAndDispatch($routes[$path]);
        }

        foreach ($routes as $route => $config) {
            $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $route);
            $pattern = "#^" . rtrim($pattern, '/') . "$#";

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                return $this->runMiddlewareAndDispatch($config, $matches);
            }
        }

        throw new RouteNotFoundException("No route found for $method $path");
    }

    protected function runMiddlewareAndDispatch(array $config, array $params = []): mixed
    {
        $handler = fn() => $this->dispatch($config['action'], $params);
        $middlewares = array_reverse($config['middleware'] ?? []);

        foreach ($middlewares as $alias) {
            if (!isset($this->middlewareRegistry[$alias])) {
                throw new \RuntimeException("Middleware [$alias] not registered.");
            }

            $next = $handler;
            $middleware = $this->middlewareRegistry[$alias];
            $handler = fn() => $middleware($next);
        }

        return $handler();
    }

    protected function dispatch(callable|array $action, array $params = []): mixed
    {
        if (is_callable($action)) {
            return call_user_func_array($action, $params);
        }

        [$class, $method] = $action;

        if (!class_exists($class)) {
            throw new RouteNotFoundException("Controller class $class not found.");
        }

        $controller = $this->container->get($class);

        if (!method_exists($controller, $method)) {
            throw new RouteNotFoundException("Method $method not found in controller $class.");
        }

        return call_user_func_array([$controller, $method], $params);
    }

    public function routes(): array
    {
        return $this->routes;
    }
}
