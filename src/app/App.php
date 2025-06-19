<?php
declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;
use App\Core\Config;
use App\Core\Router;
use App\Enums\HttpMethod;
use Illuminate\View\Factory;
use Illuminate\Events\Dispatcher;
use App\Contracts\LoggerInterface;
use Illuminate\Container\Container;
use Illuminate\View\FileViewFinder;
use App\Providers\AppServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\Engines\PhpEngine;
use App\Providers\RouteServiceProvider;
use App\Exception\ViewNotFoundException;
use App\Providers\LoggerServiceProvider;
use App\Exception\RouteNotFoundException;
use App\Contracts\ServiceProviderInterface;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;

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
        $this->initFacades();         // ✅ Set up facades early
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
            echo \Illuminate\Support\Facades\View::make('errors.404'); // ✅ will now work
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

    private function initFacades(): void
    {
        Facade::setFacadeApplication($this->container);

        $filesystem = new Filesystem();
        $viewPaths = [$this->basePath . '/resources/views'];
        $cachePath = $this->basePath . '/storage/cache/views';

        // Ensure cache directory exists
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0777, true);
        }

        $viewFinder = new FileViewFinder($filesystem, $viewPaths);
        $dispatcher = new Dispatcher($this->container);
        $resolver = new EngineResolver();

        // Register Blade engine
        $bladeCompiler = new BladeCompiler($filesystem, $cachePath);
        $resolver->register('blade', fn() => new CompilerEngine($bladeCompiler));

        // Register plain PHP engine
        $resolver->register('php', fn() => new PhpEngine($filesystem));

        $factory = new Factory($resolver, $viewFinder, $dispatcher);
        $factory->addExtension('blade.php', 'blade');
        $factory->addExtension('php', 'php');

        $this->container->instance('view', $factory);
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
