<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Container;
use App\Core\Router;
use App\Enums\HttpMethod;
use App\Exception\RouteNotFoundException;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Tests\DataProviders\RouterDataProvider;

final class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->router = new Router(new Container());
    }

    public function testThereAreNoRoutesWhenRouterIsCreated(): void
    {
        $this->assertEmpty($this->router->routes());
    }

    public function testCanRegisterRouteWithGenericMethod(): void
    {
        $this->router->register(HttpMethod::GET, '/invoices', ['Invoices', 'index']);
        $this->assertEquals([
            HttpMethod::GET->value => [
                '/invoices' => ['Invoices', 'index']
            ]
        ], $this->router->routes());
    }

    public function testItResolvesClosureRoute(): void
    {
        $this->router->get('/users', fn() => ['John', 'Jane']);
        $this->assertEquals(['John', 'Jane'], $this->router->resolve('/users', HttpMethod::GET));
    }

    public function testItResolvesControllerRoute(): void
    {
        $controller = new class {
            public function index(): array
            {
                return ['Alice', 'Bob'];
            }
        };

        $this->router->get('/people', [$controller::class, 'index']);
        $this->assertSame(['Alice', 'Bob'], $this->router->resolve('/people', HttpMethod::GET));
    }

    public function testItResolvesDynamicRouteWithParameter(): void
    {
        $controller = new class {
            public function show($id): string
            {
                return "User ID: $id";
            }
        };

        $this->router->get('/users/{id}', [$controller::class, 'show']);
        $this->assertEquals('User ID: 42', $this->router->resolve('/users/42', HttpMethod::GET));
    }

    public function testPostRoute(): void
    {
        $this->router->post('/create', fn() => 'created');
        $this->assertSame('created', $this->router->resolve('/create', HttpMethod::POST));
    }

    public function testPutRoute(): void
    {
        $this->router->put('/update', fn() => 'updated');
        $this->assertSame('updated', $this->router->resolve('/update', HttpMethod::PUT));
    }

    public function testPatchRoute(): void
    {
        $this->router->patch('/modify', fn() => 'patched');
        $this->assertSame('patched', $this->router->resolve('/modify', HttpMethod::PATCH));
    }

    public function testDeleteRoute(): void
    {
        $this->router->delete('/remove', fn() => 'deleted');
        $this->assertSame('deleted', $this->router->resolve('/remove', HttpMethod::DELETE));
    }

    public function testOptionsRoute(): void
    {
        $this->router->options('/options', fn() => 'ok');
        $this->assertSame('ok', $this->router->resolve('/options', HttpMethod::OPTIONS));
    }
    public function testTraceRoute(): void
    {
        $this->router->trace('/trace', fn() => 'ok');
        $this->assertSame('ok', $this->router->resolve('/trace', HttpMethod::TRACE));
    }
    public function testConnectRoute(): void
    {
        $this->router->connect('/connect', fn() => 'ok');
        $this->assertSame('ok', $this->router->resolve('/connect', HttpMethod::CONNECT));
    }


    #[DataProviderExternal(RouterDataProvider::class, 'httpMethodsData')]
    public function testCanRegisterAllHttpMethods(HttpMethod $method): void
    {
        $this->router->{$method->value}('/sample', fn() => true);
        $this->assertNotEmpty($this->router->routes()[$method->value]);
        $this->assertTrue($this->router->resolve('/sample', $method));
    }

    #[DataProviderExternal(RouterDataProvider::class, 'routeNotFoundCases')]
    public function testThrowsRouteNotFoundException(string $requestUri, HttpMethod $requestMethod): void
    {
        $this->router->get('/users', fn() => true);
        $this->expectException(RouteNotFoundException::class);
        $this->router->resolve($requestUri, $requestMethod);
    }

    public function testThrowsRouteNotFoundExceptionForUnknownController(): void
    {
        $this->router->get('/fail', ['UnknownController', 'missing']);
        $this->expectException(RouteNotFoundException::class);
        $this->router->resolve('/fail', HttpMethod::GET);
    }
}
