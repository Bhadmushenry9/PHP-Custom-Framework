<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\DB;
use Exception;

class Validator
{
    protected array $data = [];
    protected array $rules = [];
    protected array $messages = [];
    protected array $aliases = [];
    protected array $errors = [];
    protected array $afterHooks = [];


    protected static array $defaultMessages = [
        'required' => 'The :attribute field is required.',
        'email' => 'The :attribute must be a valid email address.',
        'min' => 'The :attribute must be at least :min characters.',
        'max' => 'The :attribute must not exceed :max characters.',
        'same' => 'The :attribute and :other must match.',
        'confirmed' => 'The :attribute confirmation does not match.',
        'exists' => 'The selected :attribute is invalid.',
        'regex' => 'The :attribute format is invalid.',
    ];

    public static function make(array $data, array $rules, array $messages = [], array $aliases = []): static
    {
        $instance = new static();
        $instance->data = $data;
        $instance->rules = $rules;
        $instance->messages = $messages;
        $instance->aliases = $aliases;
        return $instance->validate();
    }

    public function validate(): static
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = is_string($ruleString) ? explode('|', $ruleString) : $ruleString;
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$ruleName, $paramString] = explode(':', $rule, 2);
                    $params = explode(',', $paramString);
                } else {
                    $ruleName = $rule;
                }

                $method = 'validate' . ucfirst($ruleName);

                if (!method_exists($this, $method)) {
                    throw new Exception("Validation rule {$ruleName} does not exist.");
                }

                if (!$this->{$method}($field, $value, $params)) {
                    $this->addError($field, $ruleName, $params);
                    break;
                }
            }
        }

        foreach ($this->afterHooks as $hook) {
            $hook($this);
        }

        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return !$this->fails();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function validated(): array
    {
        return array_intersect_key($this->data, $this->rules);
    }

    protected function addError(string $field, string $rule, array $params): void
    {
        $message = $this->messages["{$field}.{$rule}"] ??
            self::$defaultMessages[$rule] ??
            'The :attribute field is invalid.';

        $attribute = $this->aliases[$field] ?? str_replace('_', ' ', $field);

        $replacements = array_merge([':attribute' => $attribute], array_map(
            fn($p, $i) => [":{$rule}" . ($i + 1) => $p],
            $params,
            array_keys($params)
        ));

        // Also allow :min, :max, :other, etc.
        foreach ($params as $i => $param) {
            $replacements[":param{$i}"] = $param;
        }

        if (isset($params[0])) {
            $replacements[':min'] = $params[0];
            $replacements[':max'] = $params[0];
            $replacements[':other'] = $params[0];
        }

        $this->errors[$field] = strtr($message, $replacements);
    }

    // -------------------------
    // Validation Rule Methods
    // -------------------------

    protected function validateRequired(string $field, $value): bool
    {
        return !is_null($value) && $value !== '';
    }

    protected function validateEmail(string $field, $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validateMin(string $field, $value, array $params): bool
    {
        return is_string($value) && mb_strlen($value) >= (int) ($params[0] ?? 0);
    }

    protected function validateMax(string $field, $value, array $params): bool
    {
        return is_string($value) && mb_strlen($value) <= (int) ($params[0] ?? PHP_INT_MAX);
    }

    protected function validateSame(string $field, $value, array $params): bool
    {
        $other = $params[0] ?? '';
        return $value === ($this->data[$other] ?? null);
    }

    protected function validateConfirmed(string $field, $value): bool
    {
        return $value === ($this->data["{$field}_confirmation"] ?? null);
    }

    protected function validateExists(string $field, $value, array $params): bool
    {
        [$table, $column] = $params;
        return DB::table($table)->where($column, $value)->exists();
    }

    protected function validateRegex(string $field, $value, array $params): bool
    {
        return preg_match($params[0], $value);
    }

    protected function validateUnique(string $field, $value, array $params): bool
    {
        $table = $params[0] ?? null;
        $column = $params[1] ?? $field;
        $exceptId = $params[2] ?? null;

        $query = DB::table($table)->where($column, $value);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return !$query->exists();
    }

    public function after(callable $callback): static
    {
        $this->afterHooks[] = $callback;
        return $this;
    }
}
