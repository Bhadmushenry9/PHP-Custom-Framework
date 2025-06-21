<?php
declare(strict_types=1);
namespace App\Views\Components;

class Modal extends Component
{
    public function __construct(
        public string $id,
        public string $title,
        public string $content
    ) {}

    public function data(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
        ];
    }

    public function view(): string
    {
        return 'components.modal';
    }
}
