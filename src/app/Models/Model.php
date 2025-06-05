<?php
declare(strict_types=1);

namespace App\Models;

use App\App;
use App\DB;
use App\Relations\BelongsTo;
use App\Relations\BelongsToMany;
use App\Relations\HasMany;
use App\Relations\HasManyThrough;
use App\Relations\HasOne;
use App\Relations\HasOneThrough;
use Exception;
use Ramsey\Uuid\Uuid;

abstract class Model
{
    protected DB $db;
    protected string $table;
    protected array $attributes = [];
    protected array $fillable = [];
    protected array $guarded = ['*'];
    protected bool $timestamps = true;
    protected string $createdAtColumn = 'created_at';
    protected string $updatedAtColumn = 'updated_at';
    protected string $primaryKey = 'id';
    protected bool $autoGenerateGuid = true;
    protected array $with = [];
    protected array $relations = [];

    public function __construct()
    {
        $this->db = App::db();
    }

    public function query(): DB
    {
        return $this->db->table($this->getTable());
    }

    public function create(array $data): string|int
    {
        $data = $this->filterFillable($data);

        if ($this->autoGenerateGuid && empty($data[$this->primaryKey])) {
            $data[$this->primaryKey] = Uuid::uuid4()->toString();
        }

        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');

            if (empty($data[$this->createdAtColumn])) {
                $data[$this->createdAtColumn] = $now;
            }

            if (empty($data[$this->updatedAtColumn])) {
                $data[$this->updatedAtColumn] = $now;
            }
        }

        $this->db->table($this->getTable())->insertBuilder($data);

        return $data[$this->primaryKey] ?? $this->db->lastInsertId();
    }

    public function update(array $data): int
    {
        $data = $this->filterFillable($data);

        if ($this->timestamps && empty($data[$this->updatedAtColumn])) {
            $data[$this->updatedAtColumn] = date('Y-m-d H:i:s');
        }

        return $this->query()->updateBuilder($data);
    }

    public function delete(): int
    {
        return $this->query()->deleteBuilder();
    }

    protected function filterFillable(array $data): array
    {
        if ($this->fillable) {
            return array_intersect_key($data, array_flip($this->fillable));
        }

        if ($this->guarded === ['*']) {
            return [];
        }

        return array_diff_key($data, array_flip($this->guarded));
    }

    /**
     * Find a record by ID or return null.
     *
     * @param int|string $id
     * @param array $columns
     * @return static|null
     */
    public function find(string|int $id, array $columns = ['*']): static|null
    {
        $record = $this->query()->selectColumns($columns)->where($this->primaryKey, $id)->first();

        if (!$record) {
            return null;
        }

        $this->setAttributes($record);

        // eager load relations if any
        foreach ($this->with as $relation) {
            if (method_exists($this, $relation)) {
                $related = $this->$relation();
                if (method_exists($related, 'get')) {
                    $this->relations[$relation] = $related->get();
                }
            }
        }

        // clear $with after eager loading
        $this->with = [];

        return $this;
    }

    /**
     * Find a record by ID or throw exception.
     *
     * @param int|string $id
     * @param array $columns
     * @return static
     * @throws \Exception
     */
    public function findOrFail(string|int $id, array $columns = ['*']): static
    {
        $model = $this->find($id, $columns);

        if (!$model) {
            throw new Exception("Record not found in table {$this->getTable()} with ID {$id}");
        }

        return $this;
    }

    public function findWithColumns(string|int $id, array $columns = ['*']): static|null
    {
        $this->find($id, $columns);
        return $this;
    }

    public function with(array|string $relations): static
    {
        $this->with = is_array($relations) ? $relations : func_get_args();
        return $this;
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

    public function setAttributes(array $data): void
    {
        $this->attributes = $data;
    }

    public function getAttributes(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function __get($name)
    {
        // Check if it's a loaded relation
        if (isset($this->relations[$name])) {
            return $this->relations[$name];
        }

        // Check if it's a defined attribute (from DB or manually set)
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        // Check if it's a relation method
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

    public function __set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    // Relationships

    public function hasOne(string $relatedClass, string $foreignKey, string $localKey = 'id'): HasOne
    {
        $related = new $relatedClass();
        $localKeyValue = $this->attributes[$localKey] ?? null;
        return new HasOne($related, $this->db, $foreignKey, $localKeyValue);
    }

    public function hasMany(string $relatedClass, string $foreignKey, string $localKey = 'id'): HasMany
    {
        $related = new $relatedClass();
        $localKeyValue = $this->attributes[$localKey] ?? null;
        return new HasMany($related, $this->db, $foreignKey, $localKeyValue);
    }

    public function belongsTo(string $relatedClass, string $foreignKey, string $ownerKey = 'id'): BelongsTo
    {
        $related = new $relatedClass();
        $foreignKeyValue = $this->attributes[$foreignKey] ?? null;
        return new BelongsTo($related, $this->db, $foreignKey, $ownerKey, $foreignKeyValue);
    }

    public function belongsToMany(
        string $relatedClass,
        string $pivotTable,
        string $foreignPivotKey,
        string $relatedPivotKey,
        string $localKey = 'id',
        string $relatedKey = 'id'
    ): BelongsToMany
    {
        $related = new $relatedClass();
        $localKeyValue = $this->attributes[$localKey] ?? null;

        return new BelongsToMany(
            $related,
            $this->db,
            $pivotTable,
            $foreignPivotKey,
            $relatedPivotKey,
            $localKeyValue,
            $relatedKey
        );
    }

    public function hasOneThrough(
    string $relatedClass,
    string $throughClass,
    string $firstKey,
    string $secondKey,
    string $localKey = 'id',
    string $secondLocalKey = 'id'
): HasOneThrough
{
    $related = new $relatedClass();
    $through = new $throughClass();
    $localKeyValue = $this->attributes[$localKey] ?? null;  // <-- value here!

    return new HasOneThrough(
        $related,
        $through,
        $this->db,
        $firstKey,
        $secondKey,
        $localKeyValue,     // pass value, not key name
        $secondLocalKey
    );
}

public function hasManyThrough(
    string $relatedClass,
    string $throughClass,
    string $firstKey,
    string $secondKey,
    string $localKey = 'id',
    string $secondLocalKey = 'id'
): HasManyThrough
{
    $related = new $relatedClass();
    $through = new $throughClass();
    $localKeyValue = $this->attributes[$localKey] ?? null;  // <-- value here!

    return new HasManyThrough(
        $related,
        $through,
        $this->db,
        $firstKey,
        $secondKey,
        $localKeyValue,     // pass value, not key name
        $secondLocalKey
    );
}


    public function getTable(): string
    {
        if (!isset($this->table)) {
            // Default to snake_case pluralized class name
            $class = static::class;
            $class = substr(strrchr($class, '\\') ?: $class, 1);
            $this->table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class)) . 's';
        }
        return $this->table;
    }
}
