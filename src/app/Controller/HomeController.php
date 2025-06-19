<?php
declare(strict_types=1);
namespace App\Controller;

use Illuminate\Support\Facades\View;

class HomeController
{
    public function index(): string
    {
        return View::make('layouts.home.index')->render();
    }
}
