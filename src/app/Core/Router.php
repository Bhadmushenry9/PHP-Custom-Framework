<?php
declare(strict_types=1);

namespace App\Core;

use App\Exception\RouteNotFoundException;

class Router
{
    protected array $routes = [];

    public function get(string $route, callable|array $action): self
    {
        return $this->register('GET', $route, $action);
    }

    public function post(string $route, callable|array $action): self
    {
        return $this->register('POST', $route, $action);
    }

    public function put(string $route, callable|array $action): self
    {
        return $this->register('PUT', $route, $action);
    }

    public function patch(string $route, callable|array $action): self
    {
        return $this->register('PATCH', $route, $action);
    }

    public function delete(string $route, callable|array $action): self
    {
        return $this->register('DELETE', $route, $action);
    }

    public function options(string $route, callable|array $action): self
    {
        return $this->register('OPTIONS', $route, $action);
    }

    public function register(string $method, string $route, callable|array $action): self
    {
        $route = rtrim($route, '/') ?: '/'; // Normalize trailing slash
        $this->routes[strtoupper(string: $method)][$route] = $action;
        return $this;
    }

    public function resolve(string $requestUri, string $requestMethod): mixed
    {
        $path = parse_url($requestUri, PHP_URL_PATH);
        $method = strtoupper($requestMethod);
        $routes = $this->routes[$method] ?? [];

        // Match static routes first
        if (isset($routes[$path])) {
            return $this->dispatch($routes[$path]);
        }

        // Check dynamic routes with parameters
        foreach ($routes as $route => $action) {
            $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $route);
            $pattern = "#^" . rtrim($pattern, '/') . "$#";

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remove full match
                return $this->dispatch($action, $matches);
            }
        }

        throw new RouteNotFoundException("No route found for $method $path");
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

        $controller = new $class;

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
