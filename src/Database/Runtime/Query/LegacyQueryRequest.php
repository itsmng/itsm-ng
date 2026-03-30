<?php

namespace itsmng\Database\Runtime\Query;

use Countable;
use DBmysqlResult;
use Iterator;
use itsmng\Database\Runtime\LegacyDatabase;

class LegacyQueryRequest implements Iterator, Countable
{
    private ?LegacyDatabase $conn;

    private LegacyQueryBuilder $builder;

    private $res = false;

    private $row;

    private int $position = 0;

    public function __construct(?LegacyDatabase $dbconnection)
    {
        $this->conn = $dbconnection;
        $this->builder = new LegacyQueryBuilder($dbconnection);
    }

    public function execute($table, $crit = "", $debug = false): self
    {
        $this->buildQuery($table, $crit, $debug);
        $this->res = ($this->conn ? $this->conn->query($this->getSql()) : false);
        $this->position = 0;

        return $this;
    }

    public function buildQuery($table, $crit = "", $log = false): void
    {
        $this->res = false;
        $this->builder->buildQuery($table, $crit, $log);
    }

    public function handleOrderClause($clause): string
    {
        return $this->builder->handleOrderClause($clause);
    }

    public function handleLimits($limit, $offset = null): string
    {
        return $this->builder->handleLimits($limit, $offset);
    }

    public function getSql(): string
    {
        return $this->builder->getSql();
    }

    public function __destruct()
    {
        if ($this->res instanceof DBmysqlResult) {
            $this->conn?->freeResult($this->res);
        }
    }

    public function analyseCrit($crit, $bool = "AND"): string
    {
        return $this->builder->analyseCrit($crit, $bool);
    }

    public function analyseJoins(array $joinarray): string
    {
        return $this->builder->analyseJoins($joinarray);
    }

    public function rewind(): void
    {
        if ($this->res && $this->conn !== null && $this->conn->numrows($this->res)) {
            $this->conn->dataSeek($this->res, 0);
        }
        $this->position = 0;
        $this->next();
    }

    public function current(): mixed
    {
        return $this->row;
    }

    public function key(): mixed
    {
        return (isset($this->row["id"]) ? $this->row["id"] : $this->position - 1);
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        if (!($this->res instanceof DBmysqlResult) || $this->conn === null) {
            return false;
        }
        $this->row = $this->conn->fetchAssoc($this->res);
        ++$this->position;
        return $this->row;
    }

    public function valid(): bool
    {
        return $this->res instanceof DBmysqlResult && $this->row;
    }

    public function numrows()
    {
        return ($this->res instanceof DBmysqlResult && $this->conn !== null ? $this->conn->numrows($this->res) : 0);
    }

    public function count(): int
    {
        return ($this->res instanceof DBmysqlResult && $this->conn !== null ? $this->conn->numrows($this->res) : 0);
    }

    public function isOperator($value)
    {
        return $this->builder->isOperator($value);
    }
}
