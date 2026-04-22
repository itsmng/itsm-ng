<?php

namespace itsmng\Database\Runtime\Statement;

class LegacyStatement
{
    private \mysqli_stmt|\PDOStatement $statement;

    /** @var array<int, mixed> */
    private array $bound_values = [];

    private bool $is_pdo;

    public string $error = '';

    public function __construct(\mysqli_stmt|\PDOStatement $statement)
    {
        $this->statement = $statement;
        $this->is_pdo = $statement instanceof \PDOStatement;
    }

    public function bind_param(string $types, mixed &...$values): bool
    {
        if (!$this->is_pdo) {
            /** @psalm-suppress PossiblyUndefinedMethod - not-pdo path */
            $result = $this->statement->bind_param($types, ...$values);
            /** @psalm-suppress UndefinedPropertyFetch - not-pdo path */
            $this->error = $this->statement->error;
            return $result;
        }

        $this->bound_values = [];
        foreach ($values as $index => &$value) {
            $this->bound_values[$index] = &$value;
        }

        $this->error = '';
        return true;
    }

    public function execute(?array $params = null): bool
    {
        if (!$this->is_pdo) {
            $result = $this->statement->execute();
            /** @psalm-suppress UndefinedPropertyFetch - not-pdo path */
            $this->error = $this->statement->error;
            return $result;
        }

        if ($params === null) {
            $params = [];
            foreach ($this->bound_values as &$value) {
                $params[] = $value;
            }
        } elseif (array_keys($params) === range(1, count($params))) {
            $params = array_values($params);
        }

        try {
            $result = $this->statement->execute($params);
            $error_info = $this->statement->errorInfo();
            $this->error = $error_info[2] ?? '';
        } catch (\Throwable $throwable) {
            $this->error = $throwable->getMessage();
            return false;
        }

        return $result;
    }

    public function close(): bool
    {
        if (!$this->is_pdo) {
            /** @psalm-suppress PossiblyUndefinedMethod - not-pdo path */
            $result = $this->statement->close();
            /** @psalm-suppress UndefinedPropertyFetch - not-pdo path */
            $this->error = $this->statement->error;
            return $result;
        }

        /** @psalm-suppress PossiblyUndefinedMethod - pdo path */
        $this->statement->closeCursor();
        return true;
    }

    public function __call(string $name, array $arguments)
    {
        return $this->statement->$name(...$arguments);
    }

    public function __get(string $name)
    {
        return $this->statement->$name;
    }
}
