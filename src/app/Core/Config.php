<?php
declare(strict_types=1);

namespace App\Core;

/**
 * @property-read ?array $db
 * @property-read ?array $mailer
 */
class Config
{
    protected array $config;
    public function __construct(array $env)
    {
        $this->config = [
            'db' => [
                'driver' => $env['DB_DRIVER'],
                'host' => $env['DB_HOST'],
                'username' => $env['DB_USER'],
                'password' => $env['DB_PASS'],
                'database' => $env['DB_DATABASE'],
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
            ],
            'paths' => [
                'public' => BASE_PATH . '/public',
                'config' => BASE_PATH . '/config',
                'storage' => BASE_PATH . '/storage',
                'views' => BASE_PATH . '/resources/views',
                'routes' => BASE_PATH . '/routes',
            ],
            'mailer' => [
                'dns' => $env['MAILER_DNS']
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
