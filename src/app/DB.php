<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;
use App\Query\Raw;

class DB
{
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

    public function __construct(array $config)
    {
        try {
            $options = $config['options'] ?? [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ];

            $this->pdo = new PDO(
                "{$config['driver']}:host={$config['host']};dbname={$config['database']}",
                $config['user'],
                $config['pass'],
                $options
            );
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int) $e->getCode());
        }
    }

    public function __call(string $name, array $args): callable
    {
        return call_user_func_array([$this->pdo, $name], $args);
    }

    private function reset(): void
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
    }

    public function query(string $sql, array $params = []): \PDOStatement|false
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function transaction(callable $callback): void
    {
        try {
            $this->pdo->beginTransaction();
            $callback($this);
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function table(string $table): static
    {
        $this->reset();
        $this->table = $table;
        return $this;
    }

    public function selectColumns(array $columns): static
    {
        $this->columns = array_map(fn($col) => $col instanceof Raw ? (string) $col : $col, $columns);
        return $this;
    }

    public function join(string $table, string $on, string $type = 'INNER'): static
    {
        $this->joins[] = "{$type} JOIN {$table} ON {$on}";
        return $this;
    }

    public function where(string $column, mixed $operatorOrValue, mixed $value = null): static
    {
        $operator = $value !== null ? $operatorOrValue : '=';
        $val = $value !== null ? $value : $operatorOrValue;

        $this->wheres[] = "{$column} {$operator} ?";
        $this->bindings[] = $val;

        return $this;
    }

    public function orWhere(string $column, mixed $operatorOrValue, mixed $value = null): static
    {
        $operator = $value !== null ? $operatorOrValue : '=';
        $val = $value !== null ? $value : $operatorOrValue;

        $prefix = empty($this->wheres) ? '' : 'OR ';
        $this->wheres[] = "{$prefix}{$column} {$operator} ?";
        $this->bindings[] = $val;

        return $this;
    }

    public function whereNull(string $column): static
    {
        $this->wheres[] = "{$column} IS NULL";
        return $this;
    }

    public function whereNotNull(string $column): static
    {
        $this->wheres[] = "{$column} IS NOT NULL";
        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->wheres[] = "{$column} IN ({$placeholders})";
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    public function groupBy(string ...$columns): static
    {
        $this->groups = array_merge($this->groups, $columns);
        return $this;
    }

    public function having(string $column, string $operator, mixed $value): static
    {
        $this->havings[] = "{$column} {$operator} ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->order = "ORDER BY {$column} {$direction}";
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    public function get(): array
    {
        $sql = $this->toSql();
        $stmt = $this->query($sql, $this->bindings);
        $this->reset();
        return $stmt->fetchAll();
    }

    public function first(): array|false
    {
        return $this->limit(1)->get()[0] ?? false;
    }

    public function lastInsertId(): string|false
    {
        return $this->pdo->lastInsertId();
    }

    public function insertBuilder(array $data): bool
    {
        $columns = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES ({$placeholders})";
        $result = $this->query($sql, array_values($data));
        $this->reset();
        return $result !== false;
    }

    public function updateBuilder(array $data): int
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

    public function deleteBuilder(): int
    {
        $sql = "DELETE FROM {$this->table}";
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }

        $stmt = $this->query($sql, $this->bindings);
        $this->reset();
        return $stmt->rowCount();
    }

    public function toSql(): string
    {
        $sql = "SELECT " . implode(', ', $this->columns) . " FROM {$this->table}";

        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->wheres);
        }
        if (!empty($this->groups)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groups);
        }
        if (!empty($this->havings)) {
            $sql .= ' HAVING ' . implode(' AND ', $this->havings);
        }
        if (!empty($this->order)) {
            $sql .= ' ' . $this->order;
        }
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

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
