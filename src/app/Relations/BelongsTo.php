<?php
declare(strict_types=1);

namespace App\Relations;

use App\Models\Model;
use App\DB;

class BelongsTo
{
    protected Model $related;
    protected DB $db;
    protected string $ownerKey;
    protected string $foreignKey;
    protected $foreignKeyValue;

    public function __construct(Model $related, DB $db, string $foreignKey, string $ownerKey, $foreignKeyValue)
    {
        $this->related = $related;
        $this->db = $db;
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
        $this->foreignKeyValue = $foreignKeyValue;
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