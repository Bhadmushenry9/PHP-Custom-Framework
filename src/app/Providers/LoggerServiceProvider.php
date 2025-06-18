<?php
declare(strict_types=1);

namespace App\Providers;

use App\Contracts\LoggerInterface;
use App\Logging\FileLogger;
use Illuminate\Container\Container;

class LoggerServiceProvider extends BaseServiceProvider
{
    public function __construct(protected Container $container) {}

    public function register(): void
    {
        $this->container->bind(LoggerInterface::class, function () {
            $logPath = STORAGE_PATH. '/logs/app.log';
            return new FileLogger($logPath);
        });
    }

    public function boot(): void
    {
        // No boot logic required here
    }
}
