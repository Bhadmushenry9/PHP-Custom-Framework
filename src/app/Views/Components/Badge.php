<?php

namespace App\Views\Components;

use Illuminate\Support\Facades\View;

class Badge
{
    protected string $text;
    protected string $color;

    public function __construct(string $text, string $color)
    {
        $this->text = $text;
        $this->color = $color;
    }

    public function render(): string
    {
        return View::make('components.badge', [
            'text' => $this->text,
            'color' => $this->color,
        ])->render();
    }
}
