<?php
declare(strict_types=1);
namespace App;

use App\Exception\RequestNotFoundException;
use App\Exception\ViewNotFoundException;

class App
{
    protected static DB $db;
    public function __construct(protected Routes $router, protected array $request, protected Config $config)
    {
        self::$db = new DB($config->db ?? []);
    }

    public static function db(): DB
    {
        return static::$db;
    }

    public function run()
    {
        try {
            echo $this->router->resolve(
                $this->request['uri']
                , strtolower($this->request['method'])
            );
        } catch(RequestNotFoundException|ViewNotFoundException $e) {
            http_response_code(404);
            echo View::make('errors/404');
        } catch(\Throwable $e) {
            echo $e->getMessage();
        }
    }
}
