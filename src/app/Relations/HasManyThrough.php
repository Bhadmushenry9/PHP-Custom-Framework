<?php
declare(strict_types=1);

namespace App\Relations;

use App\Core\Model;
use App\Core\DB;

class HasManyThrough
{
    public function __construct(
        protected Model $related,
        protected Model $through,
        protected DB $db,
        protected string $firstKey,
        protected string $secondKey,
        protected mixed $localKeyValue,       // this is the actual value
        protected string $secondLocalKey
    ) {
    }

    public function get(): array
    {
        return $this->related
            ->query()
            ->join(
                $this->through->getTable(),
                "{$this->through->getTable()}.{$this->secondKey}",
                '=',
                "{$this->related->getTable()}.{$this->firstKey}"
            )
            ->where("{$this->through->getTable()}.{$this->secondLocalKey}", '=', $this->localKeyValue)  // use value here
            ->get();
    }
}
