<?php
declare(strict_types=1);

namespace App\Relations;

use App\Models\Model;
use App\DB;

class BelongsToMany
{
    protected Model $related;
    protected DB $db;
    protected string $pivotTable;
    protected string $foreignPivotKey;
    protected string $relatedPivotKey;
    protected $parentKeyValue;
    protected string $relatedTable;

    public function __construct(
        Model $related,
        DB $db,
        string $pivotTable,
        string $foreignPivotKey,
        string $relatedPivotKey,
        $parentKeyValue
    ) {
        $this->related = $related;
        $this->db = $db;
        $this->pivotTable = $pivotTable;
        $this->foreignPivotKey = $foreignPivotKey;
        $this->relatedPivotKey = $relatedPivotKey;
        $this->parentKeyValue = $parentKeyValue;
        $this->relatedTable = $related->getTable();
    }

    public function get(): array
    {
        // This assumes your DB supports joins
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
