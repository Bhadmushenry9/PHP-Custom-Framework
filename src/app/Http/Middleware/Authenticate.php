<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthManager;

class Authenticate
{
    public function __construct(protected AuthManager $auth)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $guard = $this->auth->guard();

        if (!$guard->check()) {
            // redirect guest to login
            return redirect('/login');
        }

        return $next($request);
    }
}