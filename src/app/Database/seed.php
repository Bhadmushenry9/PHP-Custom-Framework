<?php
declare(strict_types=1);

use App\App;
use App\Seeders\DatabaseSeeder;
use Illuminate\Container\Container;

require_once __DIR__ . '../../../vendor/autoload.php';

// Initialize Router
$container = Container::getInstance();
// Run the application
(new App(
    $container,
))->boot();

DatabaseSeeder::run();
