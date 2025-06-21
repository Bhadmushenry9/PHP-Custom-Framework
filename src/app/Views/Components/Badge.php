<?php
declare(strict_types=1);
namespace App\Views\Components;

class Badge extends Component
{
    public function __construct(
        public string $text,
        public string $color = '#6B7280'
    ) {}

    public function data(): array
    {
        return [
            'text' => $this->text,
            'color' => $this->color,
        ];
    }

    public function view(): string
    {
        return 'components.badge';
    }
}