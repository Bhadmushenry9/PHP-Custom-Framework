<?php
declare(strict_types=1);

namespace App\Relations;

use App\Core\Model;
use App\Core\DB;

class HasOne
{
    public function __construct(
        protected Model $related, 
        protected DB $db, 
        protected string $foreignKey, 
        protected string $localKeyValue
    ) {
    }

    public function get()
    {
        return $this->related->query()
            ->where($this->foreignKey, '=', $this->localKeyValue)
            ->first();
    }

    public function where(string $column, $operatorOrValue, $value = null)
    {
        $query = $this->related->query()
            ->where($this->foreignKey, '=', $this->localKeyValue);

        if ($value === null) {
            $query->where($column, '=', $operatorOrValue);
        } else {
            $query->where($column, $operatorOrValue, $value);
        }

        return $query;
    }
}
