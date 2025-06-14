<?php
declare(strict_types=1);

namespace App\Core;

/**
 * @property-read ?array $db
 */
class Config
{
    protected array $config;
    public function __construct(array $env)
    {
        $this->config = [
            'db' => [
                'driver' => $env['DB_DRIVER']
                ,
                'host' => $env['DB_HOST']
                ,
                'user' => $env['DB_USER']
                ,
                'pass' => $env['DB_PASS']
                ,
                'database' => $env['DB_DATABASE']
            ],
            'paths' => [
                'app' => BASE_PATH . '/app',
                'public' => BASE_PATH . '/public',
                'config' => BASE_PATH . '/config',
                'storage' => BASE_PATH . '/storage',
                'views' => BASE_PATH . '/resources/views',
                'routes' => BASE_PATH . '/routes',
            ]
        ];
    }

    public function __get(string $name)
    {
        return $this->config[$name];
    }

    public function getPath(string $key): string
    {
        return $this->config['paths'][$key] ?? '';
    }
}
