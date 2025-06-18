<?php
declare(strict_types=1);
namespace App;

use App\Contracts\LoggerInterface;
use App\Contracts\ServiceProviderInterface;
use App\Core\Config;
use App\Core\Router;
use App\Enums\HttpMethod;
use App\Exception\RouteNotFoundException;
use App\Exception\ViewNotFoundException;
use App\Providers\AppServiceProvider;
use App\Providers\LoggerServiceProvider;
use App\Providers\RouteServiceProvider;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class App
{
    protected Config $config;
    protected string $basePath;

    public function __construct(
        protected Container $container,
        protected ?Router $router = null,
        protected ?array $request = [],
    ) {
        $this->basePath = dirname(__DIR__);
    }

    public function boot(): static
    {
        $this->loadEnv();
        $this->loadDefines();
        $this->initConfig();
        $this->registerProviders();
        $this->initDb();

        return $this;
    }

    public function run(): void
    {
        try {
            echo $this->router->resolve(
                $this->request['uri'],
                HttpMethod::tryFrom($this->request['method'])
            );
        } catch (RouteNotFoundException | ViewNotFoundException $e) {
            http_response_code(404);
            echo View::make('errors/404');
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
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
    }

    private function initDb(): void
    {
        $capsule = new Capsule;

        $capsule->addConnection($this->config->db);

        $capsule->setEventDispatcher(new Dispatcher($this->container));

        $capsule->setAsGlobal();

        $capsule->bootEloquent();
    }

    private function registerProviders(): void
    {
        $loggerProvider = new LoggerServiceProvider($this->container);
        $loggerProvider->register();
        $loggerProvider->boot();

        /** @var LoggerInterface $logger */
        $logger = $this->container->get(LoggerInterface::class);

        $providers = [
            new AppServiceProvider($this->container),
            new RouteServiceProvider($this->router, $this->config)
        ];

        foreach ($providers as $provider) {
            if ($provider instanceof ServiceProviderInterface) {
                $provider->register();
                $provider->boot();
            } else {
                $logger->error('Provider ' . get_class($provider) . ' does not implement ServiceProviderInterface.');
            }
        }
    }

    public function getConfig(): Config
    {
        return $this->config;
    }
}
