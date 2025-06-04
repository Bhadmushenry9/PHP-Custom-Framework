<?php
declare(strict_types=1);

namespace App\Models;

use App\App;
use App\DB;
use App\Relations\BelongsTo;
use App\Relations\BelongsToMany;
use App\Relations\HasMany;
use App\Relations\HasOne;
use Exception;

abstract class Model
{
    protected DB $db;
    protected string $table;

    public function __construct()
    {
        $this->db = App::db();
    }

    // Start a new query builder for this model's table
    public function query(): DB
    {
        return $this->db->table($this->getTable());
    }

    public function create(array $data): bool
    {
        return $this->db->table($this->getTable())->insertBuilder($data);
    }

    public function lastInsertId(): string
    {
        return $this->db->lastInsertId();
    }

    public function find(string|int $id, array $columns = ['*']): array
    {
        return $this->query()->selectColumns($columns)->where('id', $id)->first() ?? [];
    }

    public function findOrFail(string|int $id, array $columns = ['*']): array
    {
        $result = $this->find($id, $columns);

        if (empty($result)) {
            throw new Exception("Record not found in table {$this->getTable()} with ID {$id}");
        }

        return $result ?? [];
    }
    public function findWithColumns(string|int $id, array $columns = ['*']): array
    {
        return $this->query()->selectColumns($columns)->where('id', $id)->first() ?? [];
    }

    public function all(array $columns = ['*']): array
    {
        return $this->query()->selectColumns($columns)->get();
    }

    public function where(string $column, mixed $operatorOrValue, mixed $value = null): DB
    {
        return $this->query()->where($column, $operatorOrValue, $value);
    }

    public function orWhere(string $column, mixed $operatorOrValue, mixed $value = null): DB
    {
        return $this->query()->orWhere($column, $operatorOrValue, $value);
    }

    public function whereIn(string $column, array $values): DB
    {
        return $this->query()->whereIn($column, $values);
    }

    public function whereNull(string $column): DB
    {
        return $this->query()->whereNull($column);
    }

    public function join(string $table, string $first, ?string $operator = null, ?string $second = null, string $type = 'INNER'): DB
    {
        return $this->query()->join($table, $first, $operator, $second, $type);
    }

    public function orderBy(string $column, string $direction = 'ASC'): DB
    {
        return $this->query()->orderBy($column, $direction);
    }

    public function groupBy(string|array $columns): DB
    {
        return $this->query()->groupBy($columns);
    }

    public function having(string $column, mixed $operatorOrValue, mixed $value = null): DB
    {
        return $this->query()->having($column, $operatorOrValue, $value);
    }

    public function limit(int $limit): DB
    {
        return $this->query()->limit($limit);
    }

    public function offset(int $offset): DB
    {
        return $this->query()->offset($offset);
    }

    public function update(array $data): int
    {
        return $this->query()->updateBuilder($data);
    }

    public function delete(): int
    {
        return $this->query()->deleteBuilder();
    }

    public function __get($name)
    {
        if (isset($this->relations[$name])) {
            return $this->relations[$name];
        }

        if (method_exists($this, $name)) {
            $relation = $this->$name();

            if (method_exists($relation, 'get')) {
                $result = $relation->get();
                $this->relations[$name] = $result;
                return $result;
            }

            return $relation;
        }

        throw new Exception("Property or relation '{$name}' does not exist on " . static::class);
    }

    // Relationships
    public function hasOne(string $relatedClass, string $foreignKey, string $localKey = 'id'): HasOne
    {
        $related = new $relatedClass();
        $localKeyValue = $this->$localKey ?? null;
        return new HasOne($related, $this->db, $foreignKey, $localKeyValue);
    }

    public function hasMany(string $relatedClass, string $foreignKey, string $localKey = 'id'): HasMany
    {
        $related = new $relatedClass();
        $localKeyValue = $this->$localKey ?? null;
        return new HasMany($related, $this->db, $foreignKey, $localKeyValue);
    }

    public function belongsTo(string $relatedClass, string $foreignKey, string $ownerKey = 'id'): BelongsTo
    {
        $related = new $relatedClass();
        $foreignKeyValue = $this->$foreignKey ?? null;
        return new BelongsTo($related, $this->db, $foreignKey, $ownerKey, $foreignKeyValue);
    }

    public function belongsToMany(
        string $relatedClass,
        string $pivotTable,
        string $foreignPivotKey,
        string $relatedPivotKey,
        string $localKey = 'id'
    ): BelongsToMany {
        $related = new $relatedClass();
        $parentKeyValue = $this->$localKey ?? null;
        return new BelongsToMany($related, $this->db, $pivotTable, $foreignPivotKey, $relatedPivotKey, $parentKeyValue);
    }

    public function getTable(): string
    {
        if (!isset($this->table)) {
            throw new Exception('Model must define protected $table property.');
        }
        return $this->table;
    }
}
