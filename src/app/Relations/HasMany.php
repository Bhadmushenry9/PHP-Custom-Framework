<?php
declare(strict_types=1);

namespace App\Relations;

use App\Core\Model;
use App\Core\DB;

class HasMany
{
    protected Model $related;
    protected DB $db;
    protected string $foreignKey;
    protected $localKeyValue;

    public function __construct(Model $related, DB $db, string $foreignKey, $localKeyValue)
    {
        $this->related = $related;
        $this->db = $db;
        $this->foreignKey = $foreignKey;
        $this->localKeyValue = $localKeyValue;
    }

    public function get(): array
    {
        return $this->related->query()
            ->where($this->foreignKey, '=', $this->localKeyValue)
            ->get();
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
