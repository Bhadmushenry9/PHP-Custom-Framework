<?php
declare(strict_types=1);
namespace App;

use App\Core\Router;
use App\Enums\HttpMethod;
use App\Exception\RouteNotFoundException;
use App\Exception\ViewNotFoundException;

class App
{
    public function __construct(
        protected Router $router, 
        protected array $request, 
    )
    {
    }

    public function run()
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
}
