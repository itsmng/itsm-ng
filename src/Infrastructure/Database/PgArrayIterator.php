<?php

namespace Itsmng\Infrastructure\Database;

/**
 * Lightweight array-backed iterator with next()/count() compatible surface.
 */
class PgArrayIterator implements \Iterator, \Countable
{
    private $rows;
    private $pos = 0;

    public function __construct(array $rows)
    {
        $this->rows = array_values($rows);
        $this->pos = 0;
    }

    public function rewind(): void
    {
        $this->pos = 0;
    }

    public function current(): mixed
    {
        return $this->rows[$this->pos] ?? false;
    }

    public function key(): mixed
    {
        // If rows have an 'id' like DBmysqlIterator, return it, else position
        $row = $this->rows[$this->pos] ?? null;
        if (is_array($row) && array_key_exists('id', $row)) {
            return $row['id'];
        }
        return $this->pos;
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        if ($this->pos >= count($this->rows)) {
            return false;
        }
        return $this->rows[$this->pos++];
    }

    public function valid(): bool
    {
        return $this->pos < count($this->rows);
    }

    public function count(): int
    {
        return count($this->rows);
    }
}
