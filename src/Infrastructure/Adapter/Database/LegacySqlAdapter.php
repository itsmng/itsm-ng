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

    public function getSettersFromFields(array $fields): array
    {
        return [];
    }

    public function request(array | QueryBuilder $criteria): mixed
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

    public function getDateAdd(string $date, $interval, string $unit, ?string $alias = null): string
    {
        global $DB;
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
    public function getPositionExpression(string $substring, string $string, ?string $alias = null): string
    {
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

    public function getCurrentHourExpression(): string
    {
        return 'hour(curtime())';
    }

    public function getUnixTimestamp(string $field, ?string $alias = null): string
    {
        global $DB;
        $expr = sprintf(
            "UNIX_TIMESTAMP(%s)",
            $DB->quoteName($field)
        );

        if ($alias !== null) {
            $expr .= ' AS ' . $DB->quoteName($alias);
        }

        return $expr;
    }

    public function getRightExpression(string $field, int $value): array
    {
        return [$field => ['&', $value]];
    }

    public function getGroupConcat(string $field, string $separator = ', ', ?string $order_by = null, bool $distinct = true): string
    {
        // Check if DISTINCT is already in the field
        $has_distinct = stripos($field, 'DISTINCT') !== false;
        $field = $has_distinct ? $field : ($distinct ? "DISTINCT $field" : $field);

        // Escape the separator for SQL
        $escaped_separator = "'" . str_replace("'", "''", $separator) . "'";

        // Generate the GROUP_CONCAT function
        $sql = "GROUP_CONCAT($field";

        // Add ORDER BY if provided
        if (!empty($order_by)) {
            $sql .= " ORDER BY $order_by";
        }

        // Add separator
        $sql .= " SEPARATOR $escaped_separator)";

        return $sql;
    }

    public function concat(array $exprs): string
    {
        return "CONCAT(" . implode(", ", $exprs) . ")";
    }

    // public function dateAdd(string $date, string $interval_unit, string $interval): string
    // {
    //     return "ADDDATE($date, INTERVAL $interval $interval_unit)";
    // }

    public function ifnull(string $expr, string $default): string
    {
        return "IFNULL($expr, $default)";
    }

    /**
     * Fix ORDER BY clause for DISTINCT - not needed for MySQL/MariaDB
     */
    public function fixPostgreSQLCompleteOrderBy(string $select, string $order_by, ?string $full_query = null): array
    {
        // No fix needed for MySQL/MariaDB
        return ['order_by' => $order_by, 'select' => $select];
    }

    /**
     * Fix GROUP BY clause - not needed for MySQL/MariaDB
     */
    public function fixPostgreSQLGroupBy(string $select, string $group_by): string
    {
        // No fix needed for MySQL/MariaDB
        return $group_by;
    }

    public function getBooleanValue($value): string
    {
        // Pour MySQL, retourner '1' ou '0'
        return (bool)$value ? '1' : '0';
    }

    /**
     * Génère des critères de filtre pour un intervalle de dates
     * Version pour l'adaptateur legacy MySQL/MariaDB
     *
     * @param string $field Le nom du champ de date
     * @param string|null $begin Date de début (format YYYY-MM-DD [HH:MM:SS])
     * @param string|null $end Date de fin (format YYYY-MM-DD [HH:MM:SS])
     * @return array Les critères pour le filtre de date
     */
    public function getDateCriteria(string $field, $begin = null, $end = null): array
    {
        global $DB;
        $date_pattern = '/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/';
        $criteria = [];

        if (is_string($begin) && preg_match($date_pattern, $begin) === 1) {
            $criteria[] = [$field => ['>=', $begin]];
        } elseif ($begin !== null && $begin !== '') {
            trigger_error(
                sprintf('Invalid begin date value: %s', json_encode($begin)),
                E_USER_WARNING
            );
        }

        if (is_string($end) && preg_match($date_pattern, $end) === 1) {
            // Pour MySQL, on utilise toujours ADDDATE
            $end_expr = new \QueryExpression(
                "ADDDATE(" . $DB->quote($end) . ", INTERVAL 1 DAY)"
            );
            $criteria[] = [$field => ['<=', $end_expr]];
        } elseif ($end !== null && $end !== '') {
            trigger_error(
                sprintf('Invalid end date value: %s', json_encode($end)),
                E_USER_WARNING
            );
        }

        return $criteria;
    }
}
