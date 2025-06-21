<?php
declare(strict_types=1);

use Illuminate\Container\Container;

/**
 * Globally access the service container.
 */
if (!function_exists('app')) {
    function app(string $abstract = null)
    {
        $container = Container::getInstance();

        return $abstract ? $container->get($abstract) : $container;
    }
}

/**
 * Access the session via Illuminate\Session.
 */
if (!function_exists('session')) {
    function session(string $key = null, mixed $default = null)
    {
        $driver = app('session');
        return $key ? $driver->get($key, $default) : $driver;
    }
}

/**
 * Return the CSRF token string.
 */
if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        $session = app('session');

        if (!$session->isStarted()) {
            $session->start();
        }

        $token = $session->get('_token');

        if (empty($token) || !is_string($token)) {
            $token = bin2hex(random_bytes(32));
            $session->put('_token', $token);
        }

        return $token;
    }
}

/**
 * Render a hidden input field with the CSRF token.
 */
if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}
