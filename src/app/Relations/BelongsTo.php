<?php
declare(strict_types=1);

namespace App\Relations;

use App\Models\Model;
use App\DB;

class BelongsTo
{
    public function __construct(
        protected Model $related, 
        protected DB $db, 
        protected string $foreignKeyValue,
        protected string $ownerKey,
        protected string $localKeyValue
    ) {
    }

    public function get()
    {
        return $this->related->query()
            ->where($this->ownerKey, '=', $this->foreignKeyValue)
            ->first();
    }

    public function where(string $column, $operatorOrValue, $value = null)
    {
        $query = $this->related->query()
            ->where($this->ownerKey, '=', $this->foreignKeyValue);

        if ($value === null) {
            $query->where($column, '=', $operatorOrValue);
        } else {
            $query->where($column, $operatorOrValue, $value);
        }

        return $query;
    }
}