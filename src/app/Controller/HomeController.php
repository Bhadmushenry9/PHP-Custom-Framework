<?php
declare(strict_types=1);
namespace App\Controller;

use App\View;

class HomeController
{
    public function index(): View
    {
        return View::make('layouts.home.index');
    }
}
