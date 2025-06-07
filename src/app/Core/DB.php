<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use App\Query\Raw;

class DB
{
    private static ?self $instance = null;
    private PDO $pdo;

    private string $table = '';
    private array $columns = ['*'];
    private array $joins = [];
    private array $wheres = [];
    private array $bindings = [];
    private array $groups = [];
    private array $havings = [];
    private string $order = '';
    private ?int $limit = null;
    private ?int $offset = null;

    // --- Initialization ---
    private function __construct(array $config)
    {
        try {
            $options = $config['options'] ?? [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ];

            $dsn = "{$config['driver']}:host={$config['host']};dbname={$config['database']}";
            $this->pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int) $e->getCode());
        }
    }

    public function __call(string $name, array $args)
    {
        if (method_exists($this->pdo, $name)) {
            return call_user_func_array([$this->pdo, $name], $args);
        }

        throw new \BadMethodCallException("Method {$name} does not exist in " . static::class . " or PDO.");
    }

    public static function instance(array $config = []): self
    {
        if (self::$instance === null) {
            if (empty($config)) {
                throw new \RuntimeException("DB not initialized. Provide config at least once.");
            }
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public static function table(string $table): self
    {
        return self::instance()->reset()->setTable($table);
    }

    private function setTable(string $table): self
    {
        $this->reset();
        $this->table = $table;
        return $this;
    }

    // --- Core Query Builder Methods ---
    private function reset(): self
    {
        $this->columns = ['*'];
        $this->joins = [];
        $this->wheres = [];
        $this->bindings = [];
        $this->groups = [];
        $this->havings = [];
        $this->order = '';
        $this->limit = null;
        $this->offset = null;
        return $this;
    }

    public function select(array $columns): self
    {
        $this->columns = array_map(fn($col) => $col instanceof Raw ? (string) $col : $col, $columns);
        return $this;
    }

    public function join(string $table, string $first, ?string $operator = null, ?string $second = null, string $type = 'INNER'): self
    {
        $on = ($operator && $second) ? "{$first} {$operator} {$second}" : $first;
        $this->joins[] = "{$type} JOIN {$table} ON {$on}";
        return $this;
    }

    public function where(string $column, mixed $operatorOrValue, mixed $value = null): self
    {
        $operator = $value !== null ? $operatorOrValue : '=';
        $val = $value ?? $operatorOrValue;

        $this->wheres[] = "{$column} {$operator} ?";
        $this->bindings[] = $val;
        return $this;
    }

    public function orWhere(string $column, mixed $operatorOrValue, mixed $value = null): self
    {
        $operator = $value !== null ? $operatorOrValue : '=';
        $val = $value ?? $operatorOrValue;

        $prefix = empty($this->wheres) ? '' : 'OR ';
        $this->wheres[] = "{$prefix}{$column} {$operator} ?";
        $this->bindings[] = $val;
        return $this;
    }

    public function whereNull(string $column): self
    {
        $this->wheres[] = "{$column} IS NULL";
        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $this->wheres[] = "{$column} IS NOT NULL";
        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->wheres[] = "{$column} IN ({$placeholders})";
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    public function groupBy(string ...$columns): self
    {
        $this->groups = array_merge($this->groups, $columns);
        return $this;
    }

    public function having(string $column, string $operator, mixed $value): self
    {
        $this->havings[] = "{$column} {$operator} ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->order = "ORDER BY {$column} {$direction}";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    // --- Execution ---
    public function get(): array
    {
        $stmt = $this->query($this->toSql(), $this->bindings);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function first(): ?array
    {
        return $this->limit(1)->get()[0] ?? null;
    }

    public function insert(array $data): bool
    {
        $columns = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES ({$placeholders})";

        $result = $this->query($sql, array_values($data));
        $this->reset();
        return $result !== false;
    }

    public function update(array $data): int
    {
        $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $sql = "UPDATE {$this->table} SET {$set}";

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }

        $stmt = $this->query($sql, array_merge(array_values($data), $this->bindings));
        $this->reset();
        return $stmt->rowCount();
    }

    public function delete(): int
    {
        $sql = "DELETE FROM {$this->table}";
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }

        $stmt = $this->query($sql, $this->bindings);
        $this->reset();
        return $stmt->rowCount();
    }


    public static function lastInsertId(): string
    {
        return self::$pdo->lastInsertId();
    }

    // --- Internal Utilities ---
    protected function query(string $sql, array $params = []): \PDOStatement|false
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function toSql(): string
    {
        $sql = "SELECT " . implode(', ', $this->columns) . " FROM {$this->table}";

        if ($this->joins) $sql .= ' ' . implode(' ', $this->joins);
        if ($this->wheres) $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        if ($this->groups) $sql .= ' GROUP BY ' . implode(', ', $this->groups);
        if ($this->havings) $sql .= ' HAVING ' . implode(' AND ', $this->havings);
        if ($this->order) $sql .= ' ' . $this->order;
        if ($this->limit !== null) $sql .= " LIMIT {$this->limit}";
        if ($this->offset !== null) $sql .= " OFFSET {$this->offset}";

        return $sql;
    }

    public function toRawSql(): string
    {
        $sql = $this->toSql();
        foreach ($this->bindings as $val) {
            $val = is_numeric($val) ? $val : "'$val'";
            $sql = preg_replace('/\?/', $val, $sql, 1);
        }
        return $sql;
    }
}
