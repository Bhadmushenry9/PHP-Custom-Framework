<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Config;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Routing\Router;
use Illuminate\Redis\RedisManager;
use Illuminate\Session\SessionManager;
use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Encryption\Encrypter;
use App\Contracts\PaymentGatewayInterface;
use App\Contracts\ServiceProviderInterface;
use App\Services\PaymentGateway\StripePayment;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Config\Repository as ConfigRepository;

class AppServiceProvider implements ServiceProviderInterface
{
    public function __construct(
        protected Container $container,
        protected Config $config,
        protected Request $request,
        protected ?Router $router = null
    ) {
    }

    public function register(): void
    {
        // Bind app interfaces
        $this->container->bind(PaymentGatewayInterface::class, StripePayment::class);
        $this->container->instance(Request::class, $this->request);

        // Register core Illuminate services
        $this->registerIlluminateConfig();
        $this->registerEncrypter();
        $this->registerRedis();
        $this->registerCache();
        $this->registerSession();
        $this->registerPresenceVerifier();
        // Bind session store to the request instance for $request->session() support
        $this->bindSessionToRequest();
    }

    protected function registerIlluminateConfig(): void
    {
        // Convert string "null" to actual null for Redis password
        $redisPassword = $this->config->redis['password'];
        if (is_string($redisPassword) && strtolower($redisPassword) === 'null') {
            $redisPassword = null;
        }

        $sessionDriver = $this->config->session['driver'];
        $sessionConnection = $this->config->session['connection'];

        $cachePath = $this->config->session['files'];
        $sessionPath = $this->config->session['files'];

        $configArray = [
            'app.key' => $this->config->app['key'],

            // Cache config
            'cache' => [
                'default' => $this->config->cache['default'],
                'prefix' => $this->config->cache['prefix'],
                'stores' => [
                    'redis' => [
                        'driver' => 'redis',
                        'connection' => 'default',
                    ],
                    'file' => [
                        'driver' => 'file',
                        'path' => $this->config->cache['stores']['file']['path'],
                    ],
                    'array' => [
                        'driver' => 'array',
                    ],
                ],
            ],

            // Redis config
            'redis' => [
                'default' => [
                    'host' => $this->config->redis['host'],
                    'password' => $redisPassword,
                    'port' => $this->config->redis['port'],
                    'database' => $this->config->redis['database'],
                ],
            ],

            // Session config
            'session' => [
                'driver' => $sessionDriver,
                'connection' => $sessionConnection,
                'lifetime' => $this->config->session['lifetime'],
                'files' => $sessionPath,
                'cookie' => $this->config->session['cookie'],
                'path' => $this->config->session['path'],
                'domain' => $this->config->session['domain'],
                'secure' => filter_var($this->config->session['secure'], FILTER_VALIDATE_BOOLEAN),
                'http_only' => filter_var($this->config->session['http_only'], FILTER_VALIDATE_BOOLEAN),
                'same_site' => $this->config->session['same_site'],
                'lottery' => [2, 100],
            ],
        ];

        $this->container->singleton('config', fn() => new ConfigRepository($configArray));
    }

    protected function registerEncrypter(): void
    {
        $this->container->singleton('encrypter', function ($container) {
            $key = $container->get('config')->get('app.key');

            if (str_starts_with($key, 'base64:')) {
                $key = base64_decode(substr($key, 7));
            } else {
                $key = base64_decode($key);
            }

            if ($key === false || strlen($key) !== 32) {
                throw new \RuntimeException('Invalid encryption key length. Must be 32 bytes for AES-256-CBC.');
            }

            return new Encrypter($key, 'AES-256-CBC');
        });
    }

    protected function registerRedis(): void
    {
        $this->container->singleton('redis', function () {
            $redisConfig = $this->container->get('config')->get('redis');

            return new RedisManager(null, 'phpredis', $redisConfig);
        });
    }

    protected function registerCache(): void
    {
        $this->container->singleton('cache', function ($container) {
            return new CacheManager($container);
        });
    }

    protected function registerSession(): void
    {
        $this->container->singleton(SessionManager::class, function ($container) {
            return new SessionManager($container);
        });

        $this->container->singleton(Store::class, function ($container) {
            $manager = $container->get(SessionManager::class);
            $driver = $container->get('config')->get('session.driver', 'file');
            $store = $manager->driver($driver);

            if (!$store->isStarted()) {
                $store->start();
            }

            return $store;
        });

        // Aliases for easier access to session store
        $this->container->alias(Store::class, 'session');
    }

    protected function bindSessionToRequest(): void
    {
        $session = $this->container->get('session');
        $this->request->setLaravelSession($session);
    }

    protected function registerPresenceVerifier(): void
    {
        $this->container->singleton('db', function () {
            $capsule = new Capsule;
            $capsule->addConnection($this->config->db);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            return $capsule->getDatabaseManager();
        });

        $this->container->singleton('validation.presence', function ($container) {
            return new DatabasePresenceVerifier($container->get('db'));
        });

        $this->container->extend('validator', function ($validator, $container) {
            $validator->setPresenceVerifier($container->get('validation.presence'));
            return $validator;
        });
    }

    public function boot(): void
    {
        // no-op
    }
}
