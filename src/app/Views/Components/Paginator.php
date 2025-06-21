<?php
declare(strict_types=1);

namespace App\Views\Components;

use Illuminate\Pagination\LengthAwarePaginator;

class Paginator extends Component
{
    public function __construct(public LengthAwarePaginator $paginator) {}

    public function data(): array
    {
        return [
            'paginator' => $this->paginator,
        ];
    }

    public function view(): string
    {
        return 'components.paginator';
    }
}
