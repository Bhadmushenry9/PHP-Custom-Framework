<?php
declare(strict_types=1);
namespace App\Views\Components;

use Illuminate\Support\Facades\View;

abstract class Component
{
    abstract public function data(): array;

    abstract public function view(): string;
    public function render(): string
    {
        return View::make($this->view(), $this->data())->render();
    }
}
