<?php
declare(strict_types=1);

namespace App\Views\Components;

class FormInput extends Component
{
    public function __construct(
        public string $name,
        public string $label,
        public string $type = 'text',
        public ?string $value = '',
        public ?string $placeholder = '',
        public ?string $error = null,
        public bool $required = false
    ) {}

    public function data(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
            'value' => $this->value,
            'placeholder' => $this->placeholder,
            'error' => $this->error,
            'required' => $this->required
        ];
    }

    public function view(): string
    {
        return 'components.form-input';
    }
}
