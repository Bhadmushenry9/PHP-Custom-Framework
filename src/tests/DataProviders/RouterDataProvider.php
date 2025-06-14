<?php
namespace Tests\DataProviders;

use App\Enums\HttpMethod;

class RouterDataProvider
{
    public static function routeNotFoundCases(): array
    {
        return [
            ['/unregistered', HttpMethod::GET],
            ['/users', HttpMethod::DELETE], // not defined
            ['/wrong-path', HttpMethod::POST],
            ['/users/100', HttpMethod::POST], // wrong method for dynamic route
        ];
    }

    public static function httpMethodsData(): array
    {
        return [
            [HttpMethod::GET],
            [HttpMethod::POST],
            [HttpMethod::PUT],
            [HttpMethod::PATCH],
            [HttpMethod::DELETE],
            [HttpMethod::OPTIONS],
            [HttpMethod::HEAD],
            [HttpMethod::TRACE],
            [HttpMethod::CONNECT],
        ];
    }
}
