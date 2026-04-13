<?php

namespace itsmng\Database\Runtime\Result;

use itsmng\Database\Runtime\LegacyDatabase;

class LegacyResult
{
    private const FETCH_ASSOC = 1;
    private const FETCH_NUM = 2;
    private const FETCH_BOTH = 3;

    private ?\mysqli_result $result = null;

    private ?\PDOStatement $pdo_statement = null;

    private ?LegacyDatabase $connection = null;

    /** @var array<int, array<string, mixed>> */
    private array $rows = [];

    /** @var string[] */
    private array $field_names = [];

    /** @var array<int, array<string, mixed>> */
    private array $field_meta = [];

    private int $cursor = 0;

    public int $num_rows = 0;

    public int $field_count = 0;

    public function __construct($result, ?LegacyDatabase $connection = null)
    {
        $this->connection = $connection;

        if ($result instanceof \mysqli_result) {
            $this->result = $result;
            $this->num_rows = $result->num_rows;
            $this->field_count = $result->field_count;
            return;
        }

        if ($result instanceof \PDOStatement) {
            $this->initializeFromPdoStatement($result);
            return;
        }

        throw new \InvalidArgumentException('Unsupported query result handler.');
    }

    public function fetch_assoc(): array|null
    {
        if ($this->result instanceof \mysqli_result) {
            return $this->normalizeRow($this->result->fetch_assoc());
        }

        if ($this->loadPdoRowAtCursor()) {
            return $this->normalizeRow($this->rows[$this->cursor++]);
        }

        return null;
    }

    public function fetchAssoc()
    {
        return $this->fetch_assoc();
    }

    /**
     * @return (float|int|null|string|value-of<TArray>)[]|false|null
     *
     * @psalm-return false|list<float|int|null|string|value-of<array>>|null
     */
    public function fetch_row(): array|false|null
    {
        if ($this->result instanceof \mysqli_result) {
            return $this->result->fetch_row();
        }

        $row = $this->fetch_assoc();
        if ($row === null) {
            return null;
        }

        return array_values($row);
    }

    public function fetchRow()
    {
        return $this->fetch_row();
    }

    public function fetch_array($mode = null)
    {
        $mode ??= defined('MYSQLI_BOTH') ? MYSQLI_BOTH : self::FETCH_BOTH;

        if ($this->result instanceof \mysqli_result) {
            return $this->result->fetch_array($mode);
        }

        $row = $this->fetch_assoc();
        if ($row === null) {
            return null;
        }

        $assoc = defined('MYSQLI_ASSOC') ? MYSQLI_ASSOC : self::FETCH_ASSOC;
        $num   = defined('MYSQLI_NUM') ? MYSQLI_NUM : self::FETCH_NUM;

        return match ($mode) {
            $assoc => $row,
            $num   => array_values($row),
            default => array_values($row) + $row,
        };
    }

    public function fetchArray($mode = null)
    {
        return $this->fetch_array($mode);
    }

    /**
     * @return false|null|object
     */
    public function fetch_object(): object|false|null
    {
        if ($this->result instanceof \mysqli_result) {
            return $this->result->fetch_object();
        }

        $row = $this->fetch_assoc();
        if ($row === null) {
            return null;
        }

        return (object) $row;
    }

    public function fetchObject()
    {
        return $this->fetch_object();
    }

    public function fetch_fields(): array
    {
        if ($this->result instanceof \mysqli_result) {
            return $this->result->fetch_fields();
        }

        return array_map(
            static function (string $name): object {
                return (object) ['name' => $name];
            },
            $this->field_names
        );
    }

    public function fetchFields(): array
    {
        return $this->fetch_fields();
    }

    public function data_seek(int $offset): bool
    {
        if ($this->result instanceof \mysqli_result) {
            return $this->result->data_seek($offset);
        }

        if ($offset < 0) {
            return false;
        }

        if ($this->num_rows > 0 && $offset >= $this->num_rows) {
            return false;
        }

        while ($this->pdo_statement instanceof \PDOStatement && count($this->rows) < $offset) {
            if (!$this->loadNextPdoRow()) {
                break;
            }
        }

        if ($offset > count($this->rows)) {
            return false;
        }

        $this->cursor = $offset;
        return true;
    }

    public function dataSeek(int $offset): bool
    {
        return $this->data_seek($offset);
    }

    public function free(): bool
    {
        if ($this->result instanceof \mysqli_result) {
            $this->result->free();
        }

        if ($this->pdo_statement instanceof \PDOStatement) {
            $this->pdo_statement->closeCursor();
        }

        $this->rows = [];
        $this->field_names = [];
        $this->field_meta = [];
        $this->pdo_statement = null;
        $this->cursor = 0;
        $this->num_rows = 0;
        $this->field_count = 0;

        return true;
    }

    private function initializeFromPdoStatement(\PDOStatement $statement): void
    {
        $this->pdo_statement = $statement;
        $column_count = $statement->columnCount();
        for ($index = 0; $index < $column_count; $index++) {
            $meta = $statement->getColumnMeta($index) ?: [];
            $this->field_meta[$index] = $meta;
            $this->field_names[] = $meta['name'] ?? (string) $index;
        }

        $this->num_rows = max(0, $statement->rowCount());
        $this->field_count = count($this->field_names);
    }

    private function loadPdoRowAtCursor(): bool
    {
        if (isset($this->rows[$this->cursor])) {
            return true;
        }

        if (!$this->pdo_statement instanceof \PDOStatement) {
            return false;
        }

        while (count($this->rows) <= $this->cursor) {
            if (!$this->loadNextPdoRow()) {
                return false;
            }
        }

        return true;
    }

    private function loadNextPdoRow(): bool
    {
        if (!$this->pdo_statement instanceof \PDOStatement) {
            return false;
        }

        $row = $this->pdo_statement->fetch(\PDO::FETCH_ASSOC);
        if (!is_array($row)) {
            $this->num_rows = max($this->num_rows, count($this->rows));
            $this->pdo_statement->closeCursor();
            $this->pdo_statement = null;
            return false;
        }

        $this->rows[] = $row;

        return true;
    }

    private function normalizeRow(?array $row): ?array
    {
        if ($row === null) {
            return null;
        }

        if (!$this->connection instanceof LegacyDatabase) {
            return $row;
        }

        return $this->connection->normalizeCompatibleFetchedRow(
            $row,
            $this->field_names,
            $this->field_meta
        );
    }
}
