<?php
declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;
use App\Core\Config;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Cookie\CookieJar;
use Illuminate\Events\Dispatcher;
use App\Contracts\LoggerInterface;
use Illuminate\View\FileViewFinder;
use Illuminate\Encryption\Encrypter;
use Illuminate\Pagination\Paginator;
use App\Providers\AppServiceProvider;
use Illuminate\Filesystem\Filesystem;
use App\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\View\Engines\PhpEngine;
use App\Providers\RouteServiceProvider;
use App\Providers\LoggerServiceProvider;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Validation\Factory as ValidationFactory;

class App
{
    protected Config $config;
    protected string $basePath;

    public function __construct(
        protected Container $container,
        protected Dispatcher $events,
        protected Request $request,
        protected ?Router $router = null,
    ) {
        $this->basePath = dirname(__DIR__);
    }

    public function boot(): void
    {
        $this->loadEnv();
        $this->loadDefines();
        $this->initConfig();
        $this->initDb();

        // Bind interface to container instance - FIX for binding resolution error
        $this->bindContainerInterfaces();

        $this->initFacades();
        $this->registerProviders();
    }

    private function bindContainerInterfaces(): void
    {
        $this->container->instance(Container::class, $this->container);
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

        $this->bindFilesystem();
        $this->bindTranslator();
        $this->bindValidator();
        $this->bindEventSystem();
        $this->bindRequestAndContainer();
        $this->bindViewEngine();
        $this->bindRoutingComponents();
        $this->bindEncryption();
        $this->bindCookies();
    }


    private function bindFilesystem(): void
    {
        $filesystem = new Filesystem();
        $this->container->instance('files', $filesystem);
    }

    private function bindTranslator(): void
    {
        $filesystem = $this->container->get('files');
        $langPath = $this->basePath . '/lang';

        $loader = new FileLoader($filesystem, $langPath);
        $translator = new Translator($loader, 'en');

        $this->container->instance('translator', $translator);
    }

    private function bindValidator(): void
    {
        $translator = $this->container->get('translator');

        $this->container->bind(
            'validator',
            fn($container) =>
            new ValidationFactory($translator, $container)
        );
    }

    private function bindEventSystem(): void
    {
        $this->container->instance('events', $this->events);
    }

    private function bindRequestAndContainer(): void
    {
        $this->container->instance('request', $this->request);
        $this->container->instance('container', $this->container);
    }

    private function bindViewEngine(): void
    {
        $filesystem = $this->container->get('files');
        $viewPaths = [$this->basePath . '/resources/views'];
        $cachePath = $this->basePath . '/storage/cache/views';

        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0777, true);
        }

        $viewFinder = new FileViewFinder($filesystem, $viewPaths);
        $resolver = new EngineResolver();
        $resolver->register('php', fn() => new PhpEngine($filesystem));

        $viewFactory = new ViewFactory($resolver, $viewFinder, $this->events);
        $viewFactory->addExtension('php', 'php');

        $this->container->instance('view', $viewFactory);
    }

    private function bindRoutingComponents(): void
    {
        $this->container->instance('router', $this->router);

        $routes = $this->router->getRoutes();
        $url = new \Illuminate\Routing\UrlGenerator($routes, $this->request);
        $redirect = new \Illuminate\Routing\Redirector($url);

        $this->container->instance('url', $url);
        $this->container->instance('redirect', $redirect);
    }

    private function bindEncryption(): void
    {
        $key = $_ENV['APP_KEY'] ?? base64_encode(random_bytes(32));

        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        if (strlen($key) !== 32) {
            throw new \RuntimeException('Invalid encryption key length. AES-256-CBC requires a 32-byte key.');
        }

        $encrypter = new Encrypter($key, 'AES-256-CBC');
        $this->container->instance('encrypter', $encrypter);
    }

    private function bindCookies(): void
    {
        $this->container->singleton('cookie', fn() => new CookieJar());
    }

    private function initDb(): void
    {
        $capsule = new Capsule;

        $capsule->addConnection($this->config->db);

        $capsule->setEventDispatcher($this->events);

        $capsule->setAsGlobal();

        $capsule->bootEloquent();

        Paginator::currentPageResolver(function () {
            $page = $this->request['page'] ?? 1;
            return filter_var($page, FILTER_VALIDATE_INT) ?: 1;
        });

        Paginator::currentPathResolver(function () {
            return $this->request->path() ?? '/';
        });
    }

    private function registerProviders(): void
    {
        $loggerProvider = new LoggerServiceProvider($this->container);
        $loggerProvider->register();
        $loggerProvider->boot();

        /** @var LoggerInterface $logger */
        $logger = $this->container->get(LoggerInterface::class);

        $providers = [
            new AppServiceProvider($this->container, $this->config, $this->request, $this->router),
            new RouteServiceProvider($this->router, $this->config),
            new AuthServiceProvider($this->container),
        ];

        foreach ($providers as $provider) {
            if ($provider instanceof \App\Contracts\ServiceProviderInterface) {
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
