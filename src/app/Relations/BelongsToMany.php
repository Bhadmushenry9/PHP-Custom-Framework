<?php
declare(strict_types=1);

namespace App\Relations;

use App\Core\Model;
use App\Core\DB;

class BelongsToMany
{
    protected $parentKeyValue;
    protected string $relatedTable;

    public function __construct(
        protected Model $related,
        protected DB $db,
        protected string $pivotTable,
        protected string $foreignPivotKey,
        protected string $relatedPivotKey,
        $parentKeyValue
    ) {
        $this->parentKeyValue = $parentKeyValue;
        $this->relatedTable = $related->getTable();
    }

    public function get(): array
    {
        return $this->db->table($this->relatedTable)
            ->join($this->pivotTable, "{$this->relatedTable}.id", '=', "{$this->pivotTable}.{$this->relatedPivotKey}")
            ->where("{$this->pivotTable}.{$this->foreignPivotKey}", '=', $this->parentKeyValue)
            ->get();
    }

    public function where(string $column, $operatorOrValue, $value = null)
    {
        $query = $this->db->table($this->relatedTable)
            ->join($this->pivotTable, "{$this->relatedTable}.id", '=', "{$this->pivotTable}.{$this->relatedPivotKey}")
            ->where("{$this->pivotTable}.{$this->foreignPivotKey}", '=', $this->parentKeyValue);

        if ($value === null) {
            $query->where($column, '=', $operatorOrValue);
        } else {
            $query->where($column, $operatorOrValue, $value);
        }

        return $query;
    }
}
