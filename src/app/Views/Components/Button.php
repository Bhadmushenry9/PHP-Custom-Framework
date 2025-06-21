<?php
declare(strict_types=1);

namespace App\Views\Components;

use App\Enums\ButtonColor;

class Button extends Component
{
    public function __construct(
        public string $text,
        public string $href = '#',
        public string $type = 'button',
        public ButtonColor $color = ButtonColor::Primary
    ) {}

    public function data(): array
    {
        return [
            'text' => $this->text,
            'href' => $this->href,
            'type' => $this->type,
            'colorClasses' => $this->color->value,
        ];
    }

    public function view(): string
    {
        return 'components.button';
    }
}
