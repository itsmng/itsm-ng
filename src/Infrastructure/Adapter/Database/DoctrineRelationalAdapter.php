<?php

namespace Infrastructure\Adapter\Database;

use CommonDBTM;
use DateTime;
use DBmysqlIterator;
use Doctrine\DBAL\Result;
use ReflectionClass;
use Doctrine\ORM\EntityManager;
use Itsmng\Infrastructure\Persistence\EntityManagerProvider;
use Laminas\Stdlib\Glob;

class DoctrineRelationalAdapter implements DatabaseAdapterInterface
{
    public string $class;

    private EntityManager $em;
    private $entityName;
    private bool $isSpecialCase = false;
    private ?string $fallbackTableName = null;

    public function __construct(string|CommonDBTM $class)
    {
        $this->class = $class;
        $this->em = EntityManagerProvider::getEntityManager();
        $entityPrefix = '\Itsmng\Domain\Entities\\';
        $tableClass = '';
        $currentClass = $class;
        while (empty($tableClass)) {
            $parent = get_parent_class($currentClass);
            if (!$parent
                || !method_exists($parent, 'getTable')
                || $currentClass::getTable() != $parent::getTable()
            ) {
                $classPath = explode('\\', $currentClass::getType());
                $basename = end($classPath);
                $tableClass = str_replace('_', '', $basename);
                break;
            }
            $currentClass = get_parent_class($currentClass);
            if (!$currentClass) {
                throw new \Exception("Class does not have getTable function");
            }
        }
        $entityName = $entityPrefix . $tableClass;

        if (!isset($entityName) || !class_exists($entityName)) {
            $this->entityName = null;
            $this->isSpecialCase = true;
            $this->fallbackTableName = $currentClass::getTable();
            return;
        }

        $this->entityName = $entityName;
        $this->isSpecialCase = false;
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
        return $this->em->getConnection();
    }

    /**
     * Get the value of em
     */
    public function getEntityManager()
    {
        return $this->em;
    }


    public function findOneBy(array $criteria): mixed
    {
        if (!class_exists($this->entityName)) {
            throw new \Exception("Entity class {$this->entityName} does not exist");
        }

        // Sanitize criteria for PostgreSQL compatibility
        foreach ($criteria as $key => $value) {
            // Convert empty strings to null for ID fields
            if ($value === '' && (
                $key === 'id' ||
                substr($key, -3) === '_id' ||
                substr($key, -2) === 'id'
            )) {
                $criteria[$key] = null;
            }

            // Convert string numbers to integers for numeric fields
            if (is_string($value) && is_numeric($value) &&
                ($key === 'id' || substr($key, -3) === '_id' || substr($key, -2) === 'id')) {
                $criteria[$key] = (int)$value;
            }
        }
        try {
            $repository = $this->em->getRepository($this->entityName);
            $result = $repository->findOneBy($criteria);
            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Error in findOneBy: " . $e->getMessage());
            return null;
        }
    }

    public function findBy(array $criteria, array $order = null, int $limit = null): array
    {
        $result = $this->em->getRepository($this->entityName)->findBy($criteria);
        return $result;
    }
    public function findByRequest(array $request): array
    {

        return [];
    }

    public function deleteByCriteria(array $criteria): bool
    {
        $em = $this->em;
        $items = $this->findBy($criteria);
        foreach ($items as $item) {
            $em->remove($item);
        }
        $em->flush();

        return false;

    }

    // list columns from entity
    public function listFields(): array
    {
        if ($this->isSpecialCase || $this->entityName === null) {

            $conn = $this->em->getConnection();
            $schemaManager = $conn->createSchemaManager();
            $columns = $schemaManager->listTableColumns($this->fallbackTableName);

            $fields = [];
            foreach ($columns as $column) {
                $fieldName = $column->getName();
                $entityFieldName = $this->toEntityFormat($fieldName);
                $fields[$fieldName] = $entityFieldName;
            }

            return $fields;
        }
        $metadata = $this->em->getClassMetadata($this->entityName);
        $DoctrineFields = $metadata->getFieldNames();
        $DoctrineRelations = $metadata->getAssociationNames();
        $fields = [];
        foreach ($DoctrineFields as $field) {
            $fields[$this->toDbFormat($field)] = $field;
        }
        foreach ($DoctrineRelations as $relation) {
            $fields[$this->toDbFormat($relation, true)] = $relation;
        }
        return $fields;
    }


    private function getPropertiesAndGetters($content): array
    {
        $reflect = new ReflectionClass($content);
        $properties = $reflect->getProperties();
        $names = array_map(
            function ($property) {
                return $property->getName();
            },
            $properties,
        );
        $getters = array_map(
            function ($property) {
                $name = $property->getName();
                $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
                $name = str_replace('_', '', $name);
                return 'get' . $name;
            },
            $properties,
        );
        return array_combine($names, $getters);
    }

    protected function toDbFormat($input, bool $isRelation = false): string
    {
        if (preg_match('/^(phone|mobile|fax)\d+$/', $input)) {
            return $input;
        }

        if (preg_match('/^(priority)(\d+)$/', $input, $matches)) {
            return $matches[1] . '_' . $matches[2];
        }
        $input = preg_replace('/[A-Z]/', '_$0', $input);
        $input = mb_strtolower(ltrim($input, '_'));

        if ($isRelation) {
            if (str_ends_with($input, 'y')) {
                $input = preg_replace('/y$/', 'ies', $input);
            } else {
                $input .= 's';
            }

            $input .= '_id';
        }
        return $input;
    }

    private static function toEntityFormat(string $input, bool $expandId = true): string
    {
        $isRelation = str_ends_with($input, 's_id');
        if ($isRelation && $expandId) {
            $input = substr($input, 0, -4);
            if (str_ends_with($input, 'ie')) {
                $input = substr($input, 0, -2);
                $input .= 'y';
            }
        }
        if (preg_match('/^(phone|mobile|fax)\d+$/', $input)) {
            return $input;
        }

        if (preg_match('/^(priority)_(\d+)$/', $input, $matches)) {
            return $matches[1] . $matches[2];
        }
        $input = lcfirst(str_replace('_', '', ucwords($input, '_')));
        return $input;
    }

    private static function getLinkedEntity(object $object, string $field): string | null
    {
        $property = self::toEntityFormat($field);
        if (!property_exists($object, $property)) {
            return null;
        }
        $reflectionProperty = new \ReflectionProperty($object, $property);
        $attributes = $reflectionProperty->getAttributes();
        foreach ($attributes as $attribute) {
            if (in_array($attribute->getName(), [
                'Doctrine\ORM\Mapping\ManyToOne',
                'Doctrine\ORM\Mapping\OneToOne',
                'Doctrine\ORM\Mapping\ManyToMany',
                'Doctrine\ORM\Mapping\OneToMany'
            ])) {
                $targetEntity = $attribute->getArguments()['targetEntity'];
                return $targetEntity;
            }
        }
        return null;
    }

    private static function isDateFormat(object $object, string $field): bool
    {
        $property = self::toEntityFormat($field);
        if (!property_exists($object, $property)) {
            return false;
        }
        $reflectionProperty = new \ReflectionProperty($object, $property);
        $attributes = $reflectionProperty->getAttributes();
        foreach ($attributes as $attribute) {
            if (in_array($attribute->getName(), [
                'Doctrine\ORM\Mapping\Column',
            ])) {
                $type = $attribute->getArguments()['type'];
                if ($type === 'date' || $type === 'datetime') {
                    return true;
                }
            }
        }
        return false;
    }

    private static function isRelation(object $content, string $property): bool
    {
        return self::getLinkedEntity($content, $property) !== null;
    }

    public function getFields($content): array
    {
        if (is_array($content)) {
            return $content;
        }

        $fields = [];

        $getters = $this->getPropertiesAndGetters($content);

        foreach ($getters as $propertyName => $getter) {
            if (!method_exists($content, $getter)) {
                continue;
            }

            try {
                $value = $content->$getter();
            } catch (\Exception $e) {
                throw new \Exception('Cannot get value for property ' . $propertyName . ' of class ' . get_class($content));
                continue;
            }

            $isRelation = self::isRelation($content, $propertyName);

            $snakeCaseKey = $this->toDbFormat($propertyName, $isRelation);

            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }

            if ($isRelation && $value !== null && method_exists($value, 'getId')) {
                $fields[$snakeCaseKey] = $value->getId();
            } elseif (is_object($value)) {
                if (method_exists($value, 'getId')) {
                    $fields[$snakeCaseKey] = $value->getId();
                }
            } else {
                if (!($value instanceof \Closure) && !($value instanceof \Doctrine\ORM\PersistentCollection)) {
                    $fields[$snakeCaseKey] = $value;
                }
            }
        }
        return $fields;
    }

    public function getSettersFromFields(array $fields): array
    {
        $setters = [];
        foreach ($fields as $field) {
            $setter = 'set' . ucfirst(self::toEntityFormat($field));
            if (method_exists($this->entityName, $setter)) {
                $setters[$field] = $setter;
            } else {
                $newSetter = self::toEntityFormat($field, false);
                $setter = 'set' . ucfirst(self::toEntityFormat($newSetter));
                if (method_exists($this->entityName, $setter)) {
                    $setters[$field] = $setter;
                }
            }
        }
        return $setters;
    }

    public function save(array $fields): bool
    {
        if (!isset($fields['id'])) {
            return false;
        }

        $entity = $this->findOneBy(['id' => $fields['id']]);
        $setters = $this->getSettersFromFields(array_keys($fields));
        $object = new $this->entityName();

        foreach ($fields as $field => $value) {
            try {
                $linkedEntity = self::getLinkedEntity($object, $field);
            } catch (\Exception $e) {
                $linkedEntity = null;
            }
            if (self::isDateFormat($object, $field) && !($value instanceof \DateTime)) {
                if ($value === false || $value === null) {
                    $value = null;
                } else {
                    $value = DateTime::createFromFormat('Y-m-d H:i:s', $fields[$field]);
                }
                if ($value === false) {
                    $value = DateTime::createFromFormat('Y-m-d', $fields[$field]);
                }
                if ($value === false) {
                    $value = null;
                }
            } elseif ($linkedEntity !== null) {
                $value = self::getReferencedEntity($linkedEntity, intval($fields[$field]));
            } else {
                $value = $fields[$field];
            }
            $setter = $setters[$field] ?? '';
            if (method_exists($entity, $setter)) {
                $entity->$setter($value);
            }
        }
        try {
            $this->em->persist($entity);
            $this->em->flush();
            return true;
        } catch (\Exception $e) {
            throw new \Exception('error: ' . $e->getMessage());
            return false;
        }
    }

    private function getReferencedEntity(string $entity, int $id): object | int | null
    {
        if (!class_exists($entity)) {
            return $id;
        } elseif ($id === 0 && $entity !== \Itsmng\Domain\Entities\Entity::class) {
            return null;
        }
        return $this->em->getReference($entity, $id);
    }

    public function add(array $fields): bool|array
    {
        $entity = new $this->entityName();

        $setters = $this->getSettersFromFields(array_keys($fields));

        $object = new $this->entityName();
        foreach ($setters as $field => $setter) {
            if (!isset($fields[$field])) {
                continue;
            }

            try {
                $linkedEntity = self::getLinkedEntity($object, $field);
            } catch (\Exception $e) {
                $linkedEntity = null;
            }
            if ($linkedEntity !== null) {
                $value = self::getReferencedEntity($linkedEntity, intval($fields[$field]));
            } else {
                $value = $fields[$field];
            }
            $entity->$setter($value);
        }
        try {
            $this->em->persist($entity);
            $this->em->flush();

            return ['id' => $entity->getId()];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
            return false;
        }
    }




    public function getRelations(): array
    {
        $classMetadata = $this->em->getClassMetadata($this->entityName);
        $relations = $classMetadata->getAssociationMappings();
        $formattedRelations = [];
        foreach ($relations as $relationName => $relationDetails) {
            $formattedRelations[] = [
                'field' => $relationName,
                'type' => $relationDetails['type'], // relation typen (ex: ManyToOne)
                'targetEntity' => $relationDetails['targetEntity'], // target class
                'mappedBy' => $relationDetails['mappedBy'] ?? null, // if applicable
                'inversedBy' => $relationDetails['inversedBy'] ?? null, // if applicable
            ];
        }

        return $formattedRelations;

        // return [];
    }


    public function request(array $request): mixed
    {
        global $DB;

        $SqlIterator = new DBmysqlIterator($DB);
        if ($this->isSpecialCase && $this->fallbackTableName) {
            if (!isset($request['FROM'])) {
                $request['FROM'] = $this->fallbackTableName;
            }
        }
        // PostgreSQL correction for GROUP BY and ORDER BY
        if ($_ENV['DB_DRIVER'] == 'pdo_pgsql') {
            // If the query contents GROUP BY and columns in SELECT
            if (isset($request['SELECT']) && isset($request['GROUP'])) {
                $originalSelect = is_array($request['SELECT']) ? implode(', ', $request['SELECT']) : $request['SELECT'];
                $originalGroup = is_array($request['GROUP']) ? implode(', ', $request['GROUP']) : $request['GROUP'];

                $modifiedGroup = $this->fixPostgreSQLGroupBy($originalSelect, $originalGroup);
                $request['GROUP'] = $modifiedGroup;
            }
        }
        $query = $SqlIterator->buildQuery($request);
        return $this->query($query);
    }

    public function query(string $query): Result
    {
        if ($_ENV['DB_DRIVER'] == 'pdo_pgsql') {
            // Apply fixes befor execute the query
            if (preg_match("/\w+\.items_id\s*=\s*'\w+\.\w+'/i", $query)) {
                $query = $this->fixTableFieldReferences($query);
            }
            // Apply the dynamic correction for GROUP BY
            if (stripos($query, 'GROUP BY') !== false) {
                $parts = preg_split('/\s+GROUP\s+BY\s+/i', $query, 2);
                if (count($parts) === 2) {
                    $select_part = $parts[0];
                    $rest_part = $parts[1];

                    // Extract the pure GROUP BY part
                    $group_by = preg_split('/\s+(?:ORDER|HAVING|LIMIT)\s+/i', $rest_part, 2)[0];

                    // Correct the GROUP BY clause
                    $fixed_group_by = $this->fixPostgreSQLGroupBy($select_part, $group_by);

                    // Replace the old GROUP BY clause with the new one
                    $query = str_replace("GROUP BY $group_by", "GROUP BY $fixed_group_by", $query);
                }
            }
        }
        $stmt = $this->em->getConnection()->prepare($query);
        $results = $stmt->executeQuery();
        return $results;
    }

    /**
     * Fixes table.field reference issues in quotes
     *
     * @param string $query The SQL query to correct
     * @return string The corrected query
     */
    public function fixTableFieldReferences(string $query): string
    {

        // Pattern to find comparisons between a field and a string that looks like a table reference.field
        $pattern = "/(\w+\.items_id)\s*=\s*'(\w+\.\w+)'/i";
        $replacement = "$1 = $2";
        $query = preg_replace($pattern, $replacement, $query);

        return $query;
    }


    public function getDateAdd(string $date, $interval, string $unit, ?string $alias = null): string
    {
        global $DB;
        // PostgreSQL uses the syntax: date_field + INTERVAL 'value unit'
        $date_field = $DB->quoteName($date);
        $unit = strtolower($unit);

        // Ensure unit is singular for PostgreSQL syntax
        if (substr($unit, -1) === 's' && $unit != 'hours' && $unit != 'minutes' && $unit != 'seconds') {
            $unit = substr($unit, 0, -1);
        }

        if (is_string($interval) && !is_numeric($interval) && !preg_match('/^\d/', $interval)) {
            // PostgreSQL: date + (column || ' unit')::interval
            $expression = "$date_field + (" . $DB->quoteName($interval) . " || ' $unit')::interval";
        } else {
            // PostgreSQL: date + INTERVAL 'value unit'
            $expression = "$date_field + INTERVAL '$interval $unit'";
        }

        if ($alias !== null) {
            $expression .= ' AS ' . $DB->quoteName($alias);
        }

        return $expression;
    }

    public function getPositionExpression(string $substring, string $string, ?string $alias = null): string
    {
        // PostgreSQL syntax: POSITION(substring IN string)
        global $DB;
        $expr = sprintf(
            "POSITION(%s IN %s)",
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
        return 'EXTRACT(HOUR FROM CURRENT_TIME)';
    }

    public function getUnixTimestamp(string $field, ?string $alias = null): string
    {
        global $DB;
        $expr = sprintf(
            "EXTRACT(EPOCH FROM %s)",
            $DB->quoteName($field)
        );

        if ($alias !== null) {
            $expr .= ' AS ' . $DB->quoteName($alias);
        }

        return $expr;
    }

    public function getRightExpression(string $field, int $value): array
    {
        return ["($field & $value)" => ['>', 0]];
    }

    public function getGroupConcat(string $field, string $separator = ', ', ?string $order_by = null, bool $distinct = true): string
    {
        // Check if DISTINCT is already in the field
        $has_distinct = stripos($field, 'DISTINCT') !== false;

        // For PostgreSQL, always convert to text
        $field_clean = $has_distinct ? preg_replace('/DISTINCT\s+/i', '', $field) : $field;
        $final_field = $has_distinct
            ? "DISTINCT " . $field_clean . "::text"
            : ($distinct ? "DISTINCT $field_clean::text" : "$field_clean::text");

        // Escape the separator for SQL
        $escaped_separator = "'" . str_replace("'", "''", $separator) . "'";

        // Generate the STRING_AGG function
        $sql = "STRING_AGG($final_field, $escaped_separator";

        // Handling ORDER BY for PostgreSQL
        if (!empty($order_by)) {
            // If it's a constant and we're using DISTINCT, ignore the ORDER BY
            if (($has_distinct || $distinct) && preg_match('/^[\'"](.*?)[\'"]\s*$/', $order_by)) {
                // Do not add ORDER BY
            } else {
                // Always convert ORDER BY to text too
                $sql .= " ORDER BY $order_by::text";
            }
        }

        $sql .= ")";

        return $sql;
    }


    public function concat(array $exprs): string
    {
        return implode(" || ", $exprs);
    }


    public function ifnull(string $expr, string $default): string
    {
        if ($_ENV['DB_DRIVER'] == 'pdo_pgsql') {
            // Check if the default value is a string
            if ((preg_match('/^\'.*\'$/', $default) || $default === "'__NULL__'")) {

                // If the expression contains an ID or appears to be numeric, convert to text
                if (preg_match('/(^|\s|\.)id($|\s|,)/i', $expr) ||
                    preg_match('/users_id|items_id|_id/', $expr)) {
                    return "COALESCE($expr::text, $default)";
                }
            }
        }
        // Default case (MySQL or non-ID for PostgreSQL)
        return "COALESCE($expr, $default)";

    }

    /**
     * Fix GROUP BY clause for PostgreSQL by adding all non-aggregated columns from SELECT
     * PostgreSQL requires all columns in the SELECT clause to also appear in the GROUP BY
     * clause unless they are used in an aggregate function.
     *
     * @param string $select  The SELECT part of the query
     * @param string $groupBy The current GROUP BY part of the query
     *
     * @return string The modified GROUP BY clause
     */
    public function fixPostgreSQLGroupBy($select, $groupBy): string
    {
        // Return unchanged if GROUP BY is empty
        if (empty($groupBy)) {
            return $groupBy;
        }

        // Pattern to find different column formats in SELECT
        $patterns = [
            // "table.column format AS alias"
            '/\b([a-zA-Z0-9_]+\.[a-zA-Z0-9_]+)\s+AS\s+"?[^",]+"?/i',

            // "table.column" format without alias
            '/\bFROM\b.+?\b([a-zA-Z0-9_]+\.[a-zA-Z0-9_]+)\b(?!\s*\()/i',

            // "column format AS alias" (without table)
            '/\b([a-zA-Z0-9_]+)\s+AS\s+"?[^",]+"?/i',

            // Extract columns from a simple list like "col1, col2, col3"
            '/\bSELECT\s+((?:[a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)?\s*,\s*)*[a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)?)\s+FROM\b/is'
        ];

        $columns_to_add = [];

        // For each pattern, extract the corresponding columns
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $select, $matches)) {
                foreach ($matches[1] as $match) {
                    // If this is a comma-separated list of columns (last pattern)
                    if (strpos($match, ',') !== false) {
                        $cols = explode(',', $match);
                        foreach ($cols as $col) {
                            $col = trim($col);
                            if (!empty($col)) {
                                $columns_to_add[] = $col;
                            }
                        }
                    } else {
                        $columns_to_add[] = $match;
                    }
                }
            }
        }

        // List of aggregation functions to check
        $agg_functions = ['STRING_AGG', 'COUNT', 'SUM', 'MIN', 'MAX', 'AVG', 'ARRAY_AGG', 'GROUP_CONCAT'];

        // Filter columns and add only those that are not already in GROUP BY
        // and that are not used in aggregate functions
        foreach ($columns_to_add as $column) {
            if (
                !empty($column) &&
                strpos($groupBy, $column) === false
            ) {
                // Check if the column is used in an aggregate function
                $skip = false;
                foreach ($agg_functions as $func) {
                    if (preg_match('/' . $func . '\s*\(\s*(?:DISTINCT\s+)?' . preg_quote($column, '/') . '/i', $select)) {
                        $skip = true;
                        break;
                    }
                }

                // If the column is not used in an aggregate function, add it to the GROUP BY
                if (!$skip) {
                    $groupBy .= ", $column";
                }
            }
        }

        return $groupBy;
    }

    /**
     * Fixes PostgreSQL queries using SELECT DISTINCT with ORDER BY by ensuring
     * all columns referenced in the ORDER BY clause are also present in the SELECT clause.
     *
     * PostgreSQL requires that when using SELECT DISTINCT, every column in the ORDER BY
     * must be part of the SELECT list. If this condition is not met, the query fails.
     *
     * @param string      $select      The SELECT part of the SQL query.
     * @param string      $order_by    The ORDER BY clause of the SQL query.
     * @param string|null $full_query  Optional full SQL query (not used internally).
     *
     * @return array Returns an array with two keys:
     *               - 'select': The possibly modified SELECT clause including missing ORDER BY columns.
     *               - 'order_by': The original ORDER BY clause.
     */
    public function fixPostgreSQLCompleteOrderBy(string $select, string $order_by, ?string $full_query = null): array
    {
        // Skip if no ORDER BY or no DISTINCT
        if (empty($order_by) || stripos($select, 'DISTINCT') === false) {
            return ['order_by' => $order_by, 'select' => $select];
        }

        // Extract columns from ORDER BY clause
        $order_columns = [];
        if (preg_match('/ORDER\s+BY\s+(.+?)(?:\s+LIMIT|\s*$)/is', $order_by, $matches)) {
            $parts = explode(',', $matches[1]);
            foreach ($parts as $part) {
                $clean_part = preg_replace('/\s+(ASC|DESC)$/i', '', trim($part));
                if (preg_match('/([a-zA-Z0-9_]+\.[a-zA-Z0-9_]+)/', $clean_part, $col_match)) {
                    $order_columns[] = $col_match[1];
                }
            }
        }

        // Check which columns we need to add to SELECT
        $missing_columns = [];
        foreach ($order_columns as $column) {
            // Check if column already exists in SELECT (not in functions)
            $quoted_column = preg_quote($column, '/');

            // Various patterns to check for column presence
            $patterns = [
                "/\b$quoted_column\b\s+AS/i",          // Column AS alias
                "/\b$quoted_column\b\s*,/i",           // Column followed by comma
                "/\b$quoted_column\b\s+FROM/i",        // Column followed by FROM
                "/\b$quoted_column\b\s*$/i",           // Column at the end
                "/SELECT.*\b$quoted_column\b.*FROM/is" // Column anywhere in SELECT
            ];

            $found = false;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $select)) {
                    $found = true;
                    break;
                }
            }

            // If not found and not in aggregation, add it with a special alias
            if (!$found && !preg_match("/STRING_AGG\s*\(\s*.*$quoted_column.*\)/is", $select)) {
                $missing_columns[] = "$column AS \"__orderby_" . str_replace('.', '_', $column) . "\"";
            }
        }

        // Add missing columns to SELECT
        if (!empty($missing_columns)) {
            $modified_select = rtrim($select);
            // Ensure we don't add an extra comma if SELECT already ends with one
            if (substr($modified_select, -1) !== ',') {
                $modified_select .= ', ';
            }
            $modified_select .= implode(', ', $missing_columns);

            return ['order_by' => $order_by, 'select' => $modified_select];
        }

        return ['order_by' => $order_by, 'select' => $select];
    }

    public function getBooleanValue($value): string
    {
        // For PostgreSQL, return 'true' or 'false'
        return (bool)$value ? 'true' : 'false';
    }

    public function adaptQueryForPostgreSQL(string $query): string
    {
        // First replace backticks with double quotes
        if (strpos($query, '`') !== false) {
            $query = str_replace('`', '"', $query);
        }

        // List of boolean fields to process
        $boolFields = [
            'is_deleted', 'is_template', 'is_recursive', 'is_active',
            'is_dynamic', 'is_valid', 'is_deleted_item', 'is_template_item',
            'is_active_item', 'is_recursive_item', 'is_dynamic_item',
            'is_valid_item', 'is_fixed', 'is_imported', 'is_global'
        ];

        // For each boolean field
        foreach ($boolFields as $field) {
            // Replacements with different syntaxes
            $patterns = [
                // Without quotes
                ".$field = 0" => ".$field = " . $this->getBooleanValue(false),
                ".$field = 1" => ".$field = " . $this->getBooleanValue(true),
                ".$field=0"   => ".$field=" . $this->getBooleanValue(false),
                ".$field=1"   => ".$field=" . $this->getBooleanValue(true),

                // With quotes
                ".\"$field\" = 0" => ".\"$field\" = " . $this->getBooleanValue(false),
                ".\"$field\" = 1" => ".\"$field\" = " . $this->getBooleanValue(true),
                ".\"$field\"=0"   => ".\"$field\"=" . $this->getBooleanValue(false),
                ".\"$field\"=1"   => ".\"$field\"=" . $this->getBooleanValue(true)
            ];

            // Apply all replacements
            foreach ($patterns as $search => $replace) {
                $query = str_replace($search, $replace, $query);
            }
        }
        // Handle problematic WHERE and AND conditions for PostgreSQL
        // These regular expressions capture all possible cases

        // Replace WHERE (1) and variants
        $query = preg_replace('/WHERE\s*\(\s*1\s*\)/i', 'WHERE (TRUE)', $query);

        // Replace AND (1) and variants
        $query = preg_replace('/AND\s*\(\s*1\s*\)/i', 'AND (TRUE)', $query);

        // Replace OR (1) and variants
        $query = preg_replace('/OR\s*\(\s*1\s*\)/i', 'OR (TRUE)', $query);

        // Replace WHERE 1 (without parentheses)
        $query = preg_replace('/WHERE\s+1(\s|$)/i', 'WHERE TRUE$1', $query);

        // Replace AND 1 (without parentheses)
        $query = preg_replace('/AND\s+1(\s|$)/i', 'AND TRUE$1', $query);

        // Replace OR 1 (without parentheses)
        $query = preg_replace('/OR\s+1(\s|$)/i', 'OR TRUE$1', $query);

        // Special case: WHERE (1) AND -> replace with just WHERE
        $query = preg_replace('/WHERE\s*\(\s*1\s*\)\s*AND/i', 'WHERE', $query);


        return $query;
    }


    /**
     * Makes the keys of a query result insensitive to case for PostgreSQL
     *
     * @param array $row A row of query results
     * @return array The row adapted to be case-insensitive
     */
    public function makeResultKeysInsensitive(array $row): array
    {
        // Determine the real type of the item
        $real_type = null;
        if (isset($row['type'])) {
            $real_type = $row['type'];
        }

        // Create a case-insensitive version with the correct type
        $enhanced_row = [];
        foreach ($row as $key => $val) {
            // Keep the original
            $enhanced_row[$key] = $val;

            // Lowercase version
            $key_lower = strtolower($key);
            if ($key_lower !== $key) {
                $enhanced_row[$key_lower] = $val;
            }

            // For ITEM_AllAssets_X, create variants with the real type
            if (preg_match('/^ITEM_AllAssets_(\d+)(_(.+))?$/i', $key, $matches)) {
                $field_id = $matches[1];
                $suffix = isset($matches[3]) ? '_' . $matches[3] : '';

                // Add direct access by ID
                $enhanced_row[$field_id] = $val;

                // If we have the real type, create a key with this type
                if ($real_type) {
                    $type_key = "ITEM_{$real_type}_{$field_id}{$suffix}";
                    $enhanced_row[$type_key] = $val;

                    // Lowercase version
                    $type_key_lower = strtolower($type_key);
                    $enhanced_row[$type_key_lower] = $val;
                }
            }
        }
        return $enhanced_row;
    }

    /**
     * Generates filter criteria for a date range
     *
     * @param string $field The name of the date field
     * @param string|null $begin Start date (format YYYY-MM-DD [HH:MM:SS])
     * @param string|null $end End date (format YYYY-MM-DD [HH:MM:SS])
     * @return array The criteria for the date filter
     */
    public function getDateCriteria(string $field, $begin = null, $end = null): array
    {
        $date_pattern = '/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/'; // YYYY-MM-DD optionally followed by HH:MM:SS
        $criteria = [];

        // Validation and processing of begin date
        if (is_string($begin) && preg_match($date_pattern, $begin) === 1) {
            $criteria[] = [$field => ['>=', $begin]];
        } elseif ($begin !== null && $begin !== '') {
            trigger_error(
                sprintf('Invalid begin date value: %s', json_encode($begin)),
                E_USER_WARNING
            );
        }

        // Validation and processing of end date
        if (is_string($end) && preg_match($date_pattern, $end) === 1) {
            // Determine which syntax to use based on the platform
            // PostgreSQL: date + INTERVAL '1 day'
            $end_expr = new \QueryExpression(
                $this->quoteValue($end) . "::date + INTERVAL '1 day'"
            );

            $criteria[] = [$field => ['<', $end_expr]];
        } elseif ($end !== null && $end !== '') {
            trigger_error(
                sprintf('Invalid end date value: %s', json_encode($end)),
                E_USER_WARNING
            );
        }

        return $criteria;
    }

    /**
     * Quote a value for use in a SQL statement
     *
     * @param mixed $value The value to quote
     * @return string The quoted value
     */
    public function quoteValue($value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }

        // Quote string values
        return "'" . $this->escape($value) . "'";
    }

    /**
     * Escape a string for use in a SQL statement
     *
     * @param string $value The string to escape
     * @return string The escaped string
     */
    public function escape($value): string
    {
        // If we have access to the Doctrine connection, use its methods
        if (isset($this->em) && method_exists($this->em->getConnection(), 'quote')) {
            $quoted = $this->em->getConnection()->quote($value);
            // Remove the surrounding quotes that quote() adds
            return substr($quoted, 1, -1);
        }

        // Otherwise, use a basic implementation
        return str_replace(["'", "\\"], ["''", "\\\\"], $value);
    }

    /**
     * Returns a SQL expression to format a date according to a specified format
     * Compatible with MySQL and PostgreSQL
     *
     * @param string $field The date field
     * @param string $format The date format (default MySQL format)
     * @param string|null $alias Alias for the column
     * @return \QueryExpression The formatted SQL expression
     */
    public function getFormattedDateExpression(string $field, string $format = '%Y-%m', ?string $alias = null): \QueryExpression
    {
        static $is_postgresql = null;

        // Determine the SQL dialect once for performance
        if ($is_postgresql === null) {
            try {
                // Try with PostgreSQL syntax
                $this->query("SELECT TO_CHAR(NOW(), 'YYYY-MM') AS test");
                $is_postgresql = true;
            } catch (\Exception $e) {
                // If it fails, it's probably MySQL
                $is_postgresql = false;
            }
        }

        // Convert formats between MySQL and PostgreSQL
        $pg_format = str_replace(
            ['%Y', '%y', '%m', '%d', '%H', '%h', '%i', '%s', '%w'],
            ['YYYY', 'YY', 'MM', 'DD', 'HH24', 'HH12', 'MI', 'SS', 'ID'],
            $format
        );

        // Build the SQL expression according to the dialect
        if ($is_postgresql) {
            $expr = "TO_CHAR($field, '$pg_format')";
        } else {
            $expr = "FROM_UNIXTIME(UNIX_TIMESTAMP($field), '$format')";
        }

        // Add the alias if specified
        if ($alias !== null) {
            $expr .= " AS $alias";
        }

        return new \QueryExpression($expr);
    }
}
