<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Router;
use App\Exception\RouteNotFoundException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Tests\DataProviders\RouterDataProvider;

final class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->router = new Router();
    }

    public function testThereAreNoRoutesWhenRouterIsCreated(): void
    {
        $this->assertEmpty($this->router->routes());
    }

    public function testCanRegisterRouteWithGenericMethod(): void
    {
        $this->router->register('GET', '/invoices', ['Invoices', 'index']);
        $this->assertEquals([
            'GET' => [
                '/invoices' => ['Invoices', 'index']
            ]
        ], $this->router->routes());
    }

    public function testItResolvesClosureRoute(): void
    {
        $this->router->get('/users', fn() => ['John', 'Jane']);
        $this->assertEquals(['John', 'Jane'], $this->router->resolve('/users', 'GET'));
    }

    public function testItResolvesControllerRoute(): void
    {
        $controller = new class {
            public function index(): array {
                return ['Alice', 'Bob'];
            }
        };

        $this->router->get('/people', [$controller::class, 'index']);
        $this->assertSame(['Alice', 'Bob'], $this->router->resolve('/people', 'GET'));
    }

    public function testItResolvesDynamicRouteWithParameter(): void
    {
        $controller = new class {
            public function show($id): string {
                return "User ID: $id";
            }
        };

        $this->router->get('/users/{id}', [$controller::class, 'show']);
        $this->assertEquals('User ID: 42', $this->router->resolve('/users/42', 'GET'));
    }

    #[DataProviderExternal(RouterDataProvider::class, 'httpMethodsData')]
    public function testCanRegisterAllHttpMethods(string $method): void
    {
        $this->router->{$method}('/sample', fn() => true);
        $this->assertNotEmpty($this->router->routes()[$method]);
        $this->assertTrue($this->router->resolve('/sample', $method));
    }

    #[DataProviderExternal(RouterDataProvider::class, 'routeNotFoundCases')]
    public function testThrowsRouteNotFoundException(string $requestUri, string $requestMethod): void
    {
        $this->router->get('/users', fn() => true);
        $this->expectException(RouteNotFoundException::class);
        $this->router->resolve($requestUri, $requestMethod);
    }

    public function testThrowsRouteNotFoundExceptionForMissingMethod(): void
    {
        $this->router->get('/hello', fn() => 'Hi');
        $this->expectException(RouteNotFoundException::class);
        $this->router->resolve('/hello', 'POST');
    }

    public function testThrowsRouteNotFoundExceptionForUnknownController(): void
    {
        $this->router->get('/fail', ['UnknownController', 'missing']);
        $this->expectException(RouteNotFoundException::class);
        $this->router->resolve('/fail', 'GET');
    }
}
