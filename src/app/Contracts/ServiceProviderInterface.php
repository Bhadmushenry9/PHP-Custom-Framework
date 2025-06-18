<?php

declare(strict_types=1);

namespace App\Contracts;

interface ServiceProviderInterface
{
    public function register(): void;
    public function boot(): void;
}
