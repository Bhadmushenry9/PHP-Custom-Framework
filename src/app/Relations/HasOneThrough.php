<?php
namespace App\Relations;

use App\Models\Model;

class HasOneThrough
{
    public function __construct(
        protected Model $related,
        protected Model $through,
        protected \App\DB $db,
        protected string $firstKey,
        protected string $secondKey,
        protected mixed $localKeyValue,       // changed to value
        protected string $secondLocalKey
    ) {
    }

    public function get(): ?Model
    {
        return $this->related
            ->query()
            ->join(
                $this->through->getTable(),
                "{$this->through->getTable()}.{$this->secondKey}",
                '=',
                "{$this->related->getTable()}.{$this->firstKey}"
            )
            ->where("{$this->through->getTable()}.{$this->secondLocalKey}", $this->localKeyValue)  // use value here
            ->first();
    }
}
