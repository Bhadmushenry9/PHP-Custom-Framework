<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;

class VerifyCsrfToken
{
    public function handle($request, Closure $next)
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $token = $request->input('_token') ?? $request->header('X-CSRF-TOKEN');

            $session = app('session');

            if (!$session->isStarted()) {
                $session->start();
            }

            $sessionToken = $session->get('_token');

            if (!$token || !$sessionToken || !hash_equals((string) $sessionToken, (string) $token)) {
                http_response_code(419);
                echo 'CSRF token mismatch.';
                exit;
            }
        }

        return $next($request);
    }
}