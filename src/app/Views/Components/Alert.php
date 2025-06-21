<?php
declare(strict_types=1);

namespace App\Views\Components;

use App\Enums\AlertType;

class Alert extends Component
{
    public function __construct(
        public string $message,
        public AlertType $type = AlertType::Info
    ) {}

    public function data(): array
    {
        $classes = match ($this->type) {
            AlertType::Success => 'bg-green-100 text-green-700 border-green-400',
            AlertType::Error   => 'bg-red-100 text-red-700 border-red-400',
            AlertType::Warning => 'bg-yellow-100 text-yellow-700 border-yellow-400',
            AlertType::Info    => 'bg-blue-100 text-blue-700 border-blue-400',
        };

        return [
            'message' => $this->message,
            'type' => $this->type->value,
            'classes' => $classes,
        ];
    }

    public function view(): string
    {
        return 'components.alert';
    }
}