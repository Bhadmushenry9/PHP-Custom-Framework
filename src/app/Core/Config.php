<?php
declare(strict_types=1);

namespace App\Core;

/**
 * @property-read ?array $db
 * @property-read ?array $mailer
 * @property-read ?array $session
 * @property-read ?array $app
 * @property-read ?array $redis
 * @property-read ?array $cache
 */
class Config
{
    protected array $config;

    public function __construct(array $env)
    {
        $this->config = [
            'app' => [
                'key' => str_starts_with($env['APP_KEY'], 'base64:')
                    ? base64_decode(substr($env['APP_KEY'], 7))
                    : $env['APP_KEY'],
            ],
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
            'mailer' => [
                'dns' => $env['MAILER_DNS'],
            ],
            'session' => [
                'driver' => $env['SESSION_DRIVER'],
                'connection' => $env['SESSION_CONNECTION'],
                'lifetime' => (int) ($env['SESSION_LIFETIME']),
                'files' => STORAGE_PATH . '/framework/sessions',
                'cookie' => $env['SESSION_COOKIE'],
                'path' => $env['SESSION_PATH'],
                'domain' => $env['SESSION_DOMAIN'] !== 'null' ? $env['SESSION_DOMAIN'] : null,
                'secure' => filter_var($env['SESSION_SECURE'], FILTER_VALIDATE_BOOLEAN),
                'http_only' => filter_var($env['SESSION_HTTP_ONLY'], FILTER_VALIDATE_BOOLEAN),
                'same_site' => $env['SESSION_SAME_SITE'],
            ],
            'redis' => [
                'host' => $env['REDIS_HOST'],
                'password' => $env['REDIS_PASSWORD'] !== 'null' ? $env['REDIS_PASSWORD'] : null,
                'port' => $env['REDIS_PORT'],
                'database' => (int) ($env['REDIS_DB']),
            ],

            'cache' => [
                'default' => $env['CACHE_DRIVER'],
                'prefix' => $env['CACHE_PREFIX'],
                'stores' => [
                    'file' => [
                        'driver' => 'file',
                        'path' => STORAGE_PATH . '/framework/cache/data',
                    ],
                    'redis' => [
                        'driver' => 'redis',
                        'connection' => $env['CACHE_REDIS_CONNECTION'],
                    ],
                    'array' => [
                        'driver' => 'array',
                    ],
                ],
            ],

            'paths' => [
                'public' => BASE_PATH . '/public',
                'config' => BASE_PATH . '/config',
                'storage' => BASE_PATH . '/storage',
                'views' => BASE_PATH . '/resources/views',
                'routes' => BASE_PATH . '/routes',
            ],
        ];
    }

    public function __get(string $name)
    {
        return $this->config[$name] ?? null;
    }

    public function getPath(string $key): string
    {
        return $this->config['paths'][$key] ?? '';
    }
}