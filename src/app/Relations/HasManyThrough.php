<?php

namespace App\Relations;

use App\Models\Model;

class HasManyThrough
{
    public function __construct(
        protected Model $related,
        protected Model $through,
        protected string $firstKey,
        protected string $secondKey,
        protected string $localKey,
        protected string $secondLocalKey
    ) {
    }

    public function get()
    {
        return $this->related
            ->query()
            ->join($this->through->getTable(), "{$this->through->getTable()}.{$this->secondKey}", '=', "{$this->related->getTable()}.{$this->firstKey}")
            ->where("{$this->through->getTable()}.{$this->secondLocalKey}", $this->through->getAttribute($this->localKey))
            ->get();
    }

}
