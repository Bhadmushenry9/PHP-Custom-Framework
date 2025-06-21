<?php
declare(strict_types=1);
namespace App\Views\Components;

class Card extends Component
{
    public function __construct(
        public string $title,
        public string $content
    ) {}

    public function data(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
        ];
    }

    public function view(): string
    {
        return 'components.card';
    }
}
