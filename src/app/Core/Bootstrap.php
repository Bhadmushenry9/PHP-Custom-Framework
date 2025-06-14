<?php

declare(strict_types=1);

namespace App\Core;

use App\Providers\AppServiceProvider;
use App\Providers\RouteServiceProvider;
use Dotenv\Dotenv;

class Bootstrap
{
    protected Config $config;
    public function __construct(
        protected string $basePath, 
        protected Router $router, 
        protected Container $container
    )
    {
        $this->loadEnv();
        $this->loadDefines();
        $this->initConfig();
        $this->registerProviders();
        $this->initDb();
    }

    private function loadEnv(): void
    {
        if (file_exists($this->basePath . '/.env')) {
            Dotenv::createImmutable($this->basePath)->safeLoad();
        }
    }

    private function loadDefines(): void
    {
        require_once $this->basePath . '/includes/defines.constant.php';
    }

    private function initConfig(): void
    {
        $this->config = new Config($_ENV);
        ConfigManager::set($this->config);
    }

    private function initDb(): void
    {
        DB::instance($this->config->db);
    }

    private function registerProviders(): void
    {
        $providers = [
            new AppServiceProvider($this->container),
            new RouteServiceProvider($this->router, $this->config),
        ];

        foreach ($providers as $provider) {
            if (method_exists($provider, 'register')) {
                $provider->register();
            }

            if (method_exists($provider, 'boot')) {
                $provider->boot();
            }
        }
    }

    public function getConfig(): Config
    {
        return $this->config;
    }
}
