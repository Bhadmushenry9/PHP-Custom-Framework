<?php
namespace Tests\DataProviders;

class RouterDataProvider
{
    public static function routeNotFoundCases(): array
    {
        return [
            ['/unregistered', 'GET'],
            ['/users', 'DELETE'], // not defined
            ['/wrong-path', 'POST'],
            ['/users/100', 'POST'], // wrong method for dynamic route
        ];
    }

    public static function httpMethodsData(): array
    {
        return [
            ['GET'],
            ['POST'],
            ['PUT'],
            ['PATCH'],
            ['DELETE'],
            ['OPTIONS'],
        ];
    }
}
