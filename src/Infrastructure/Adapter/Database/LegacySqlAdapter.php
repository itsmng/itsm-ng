<?php

namespace Infrastructure\Adapter\Database;

use CommonDBTM;
use Doctrine\ORM\QueryBuilder;
use Infrastructure\Adapter\Database\DatabaseAdapterInterface;
use Laminas\Stdlib\Glob;

class LegacySqlAdapter implements DatabaseAdapterInterface
{
    public string $class;

    public function __construct(string|CommonDBTM $class)
    {
        $this->class = $class;
    }

    public function getClass(): string
    {
        return $this->class;
    }
    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function getConnection(): mixed
    {
        global $DB;

        return $DB;
    }

    public function getEntityManager()
    {
        return null;
    }

    public function findOneBy(array $criteria): mixed
    {
        global $DB;

        $iterator = $DB->request([
            'FROM'   => $this->class::getTable(),
            'WHERE'  => $criteria,
            'LIMIT'  => 1
        ]);
        if ($iterator->count()) {
            return $iterator->next();
        }
        return null;
    }

    public function findBy(array $criteria, array $order = null, int $limit = null): array
    {
        global $DB;

        $request = [
            'FROM'   => $this->class::getTable(),
            'WHERE'  => $criteria,
        ];
        if ($order) {
            $request['ORDERBY'] = $order;
        }
        if ($limit) {
            $request['LIMIT'] = $limit;
        }
        $iterator = $DB->request($request);
        if ($iterator->count()) {
            return iterator_to_array($iterator);
        }
        return [];
    }

    public function findByRequest(array $request): array
    {
        global $DB;

        $iterator = $DB->request($request);
        if ($iterator->count()) {
            return iterator_to_array($iterator);
        }
        return [];
    }

    public function deleteByCriteria(array $criteria): bool
    {
        global $DB;

        $error = $DB->delete($this->class::getTable(), $criteria);
        return $error === false;
    }

    // list columns from entity
    public function listFields(): array
    {
        return [];
    }
    // get values from entity as array
    public function getFields(mixed $content): array
    {
        return $content;
    }

    public function save(array $fields): bool
    {
        global $DB;

        $error = $DB->update(
            $this->class::getTable(),
            $fields,
            [$this->class::getIndexName() => $fields[$this->class::getIndexName()]]
        );
        return $error === false;
    }

    public function add(array $fields): bool|array
    {
        global $DB;

        $error = $DB->insert($this->class::getTable(), $fields);
        if ($error === false) {
            $fields[$this->class::getIndexName()] = $DB->insertId();
        }
        return $error === false;
    }

    public function getRelations(): array
    {
        return [];
    }

    public function getSettersFromFields(array $fields, object $content): array
    {
        return [];
    }

    public function request(array | QueryBuilder $criteria): \Iterator
    {
        return new \ArrayIterator();
    }
    // public function getTableFields(): array
    // {
    //     return [];
    // }
    public function findEntityById(array $id): mixed
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getDateAdd(string $date, $interval, string $unit, ?string $alias = null): string {
        Global $DB;
        // MySQL uses the syntax: DATE_ADD(date_field, INTERVAL value unit)
        $date_field = $DB->quoteName($date);
        
        // Standardize unit to MySQL format
        $unit = strtoupper($unit);
        
        $expression = "DATE_ADD($date_field, INTERVAL $interval $unit)";
        
        if ($alias !== null) {
            $expression .= ' AS ' . $DB->quoteName($alias);
        }
        
        return $expression;
    }

    /**
     * {@inheritDoc}
     */
    public function getPositionExpression(string $substring, string $string, ?string $alias = null): string {
        // MySQL syntax: LOCATE(substring, string)
        global $DB;
        $expr = sprintf(
            "LOCATE(%s, %s)",
            $DB->quote($substring),
            $DB->quoteName($string)
        );
        
        if ($alias !== null) {
            $expr .= ' AS ' . $DB->quoteName($alias);
        }
        
        return $expr;
    }

    public function getCurrentHourExpression(): string {
        return 'hour(curtime())';
    }

    public function getUnixTimestamp(string $field, ?string $alias = null): string {
        Global $DB;
        $expr = sprintf(
            "UNIX_TIMESTAMP(%s)",
            $DB->quoteName($field)
        );
        
        if ($alias !== null) {
            $expr .= ' AS ' . $DB->quoteName($alias);
        }
        
        return $expr;
    }

    public function getRightExpression(string $field, int $value): array {
        return [$field => ['&', $value]];
    }
}
