<?php

namespace itsmng\Database\Runtime\Platform;

use QueryExpression;
use itsmng\Database\Runtime\LegacyDatabase;

class PostgreSqlPlatform extends AbstractPlatform
{
    public function getDbType(): string
    {
        return 'pgsql';
    }

    public function getIdentifierQuoteChar(): string
    {
        return '"';
    }

    public function normalizeOperator(string $operator): string
    {
        return match (strtoupper($operator)) {
            'LIKE'        => 'ILIKE',
            'NOT LIKE'    => 'NOT ILIKE',
            'REGEXP'      => '~',
            'NOT REGEX',
            'NOT REGEXP'  => '!~',
            default       => $operator,
        };
    }

    public function listTables($table = 'glpi\_%', array $where = []): \DBmysqlIterator
    {
        return $this->database->request([
            'SELECT' => 'table_name as TABLE_NAME',
            'FROM'   => 'information_schema.tables',
            'WHERE'  => [
                'table_catalog' => $this->database->dbdefault,
                'table_schema'  => $this->database->dbschema,
                'table_type'    => 'BASE TABLE',
                'table_name'    => ['LIKE', $table],
            ] + $where,
        ]);
    }

    /**
     * @return (mixed|string[])[]|false
     *
     * @psalm-return array<array<string>|mixed>|false
     */
    public function listFields($table, $usecache = true)
    {
        if (!$this->database->isCacheDisabled() && $usecache && $this->database->hasCachedFieldList($table)) {
            return $this->database->getCachedFieldList($table);
        }

        $table_name = $this->database->quote($table);
        $schema_name = $this->database->quote($this->database->dbschema);
        $catalog_name = $this->database->quote($this->database->dbdefault);

        $query = <<<SQL
SELECT
   cols.column_name AS "Field",
   CASE
      WHEN cols.data_type = 'character varying' THEN 'varchar(' || cols.character_maximum_length || ')'
      WHEN cols.data_type = 'character' THEN 'char(' || cols.character_maximum_length || ')'
      WHEN cols.data_type = 'numeric' THEN 'numeric(' || cols.numeric_precision || ',' || cols.numeric_scale || ')'
      WHEN cols.data_type = 'timestamp without time zone' THEN 'timestamp'
      WHEN cols.data_type = 'timestamp with time zone' THEN 'timestamp'
      WHEN cols.data_type = 'time without time zone' THEN 'time'
      WHEN cols.data_type = 'boolean' THEN 'boolean'
      WHEN cols.data_type = 'double precision' THEN 'double'
      WHEN cols.data_type = 'integer' THEN 'int'
      WHEN cols.data_type = 'bigint' THEN 'bigint'
      WHEN cols.data_type = 'smallint' THEN 'smallint'
      WHEN cols.data_type = 'jsonb' THEN 'json'
      WHEN cols.data_type = 'bytea' THEN 'bytea'
      ELSE cols.data_type
   END AS "Type",
   CASE cols.is_nullable WHEN 'YES' THEN 'YES' ELSE 'NO' END AS "Null",
   CASE WHEN pk.column_name IS NOT NULL THEN 'PRI' ELSE '' END AS "Key",
   cols.column_default AS "Default",
   CASE
      WHEN cols.is_identity = 'YES' OR cols.column_default LIKE 'nextval(%' THEN 'auto_increment'
      ELSE ''
   END AS "Extra"
FROM information_schema.columns cols
LEFT JOIN (
   SELECT kcu.column_name
   FROM information_schema.table_constraints tc
   INNER JOIN information_schema.key_column_usage kcu
      ON tc.constraint_name = kcu.constraint_name
      AND tc.table_schema = kcu.table_schema
      AND tc.table_name = kcu.table_name
   WHERE tc.constraint_type = 'PRIMARY KEY'
      AND tc.table_catalog = {$catalog_name}
      AND tc.table_schema = {$schema_name}
      AND tc.table_name = {$table_name}
) pk
   ON pk.column_name = cols.column_name
WHERE cols.table_catalog = {$catalog_name}
   AND cols.table_schema = {$schema_name}
   AND cols.table_name = {$table_name}
ORDER BY cols.ordinal_position
SQL;

        $result = $this->database->query($query);
        if ($result) {
            $fields = [];
            while ($data = $this->database->fetchAssoc($result)) {
                $fields[$data['Field']] = $data;
            }
            $this->database->setCachedFieldList($table, $fields);
            return $fields;
        }

        return false;
    }

    /**
     * @return \DBmysqlIterator|false
     */
    public function listIndexes($table)
    {
        if (!$this->database->tableExists($table)) {
            trigger_error("Table $table does not exists", E_USER_WARNING);
            return false;
        }

        return $this->database->request([
            'SELECT' => [
                new QueryExpression(
                    "CASE
                        WHEN indexname LIKE '%_pkey' THEN 'PRIMARY'
                        ELSE regexp_replace(indexname, '^' || tablename || '_', '')
                    END AS \"Key_name\""
                ),
            ],
            'FROM'   => 'pg_catalog.pg_indexes',
            'WHERE'  => [
                'schemaname' => $this->database->dbschema,
                'tablename'  => $table,
            ],
            'ORDER'  => ['indexname'],
        ]);
    }

    /**
     * @return bool
     */
    public function constraintExists($table, $constraint)
    {
        if (!$this->database->tableExists($table)) {
            trigger_error("Table $table does not exists", E_USER_WARNING);
            return false;
        }

        $result = $this->database->request([
            'COUNT' => 'cpt',
            'FROM'  => 'information_schema.table_constraints',
            'WHERE' => [
                'table_catalog'   => $this->database->dbdefault,
                'table_schema'    => $this->database->dbschema,
                'table_name'      => $table,
                'constraint_name' => $constraint,
            ],
        ])->next();

        return (int) ($result['cpt'] ?? 0) > 0;
    }

    /**
     * @return (string|string[])[]
     *
     * @psalm-return array{schema: string, index: list<string>}
     */
    public function getTableSchema($table, $structure = null)
    {
        if ($structure !== null) {
            if (!is_array($structure)) {
                $structure = [$structure];
            }

            $table_structure = array_shift($structure);
            $indexes = [];
            foreach ($structure as $statement) {
                if (preg_match('/^CREATE(?: UNIQUE)? INDEX /i', trim((string) $statement)) === 1) {
                    $indexes[] = $this->normalizeTableSchema((string) $statement);
                }
            }

            return [
                'schema' => $this->normalizeTableSchema((string) $table_structure),
                'index'  => $indexes,
            ];
        }

        $columns_query = <<<SQL
SELECT
   attrs.attname AS column_name,
   pg_catalog.format_type(attrs.atttypid, attrs.atttypmod) AS formatted_type,
   NOT attrs.attnotnull AS is_nullable,
   pg_get_expr(defaults.adbin, defaults.adrelid) AS column_default
FROM pg_catalog.pg_attribute attrs
INNER JOIN pg_catalog.pg_class classes
   ON classes.oid = attrs.attrelid
INNER JOIN pg_catalog.pg_namespace namespaces
   ON namespaces.oid = classes.relnamespace
LEFT JOIN pg_catalog.pg_attrdef defaults
   ON defaults.adrelid = attrs.attrelid
   AND defaults.adnum = attrs.attnum
WHERE namespaces.nspname = {$this->database->quote($this->database->dbschema)}
   AND classes.relname = {$this->database->quote($table)}
   AND attrs.attnum > 0
   AND NOT attrs.attisdropped
ORDER BY attrs.attnum
SQL;

        $columns = [];
        $result = $this->database->queryOrDie($columns_query, 'Read PostgreSQL table columns');
        while ($column = $this->database->fetchAssoc($result)) {
            $columns[] = $this->normalizeColumnDefinition($column);
        }

        $primary_key_columns = [];
        $pk_query = <<<SQL
SELECT attrs.attname AS column_name
FROM pg_catalog.pg_index indexes
INNER JOIN pg_catalog.pg_class classes
   ON classes.oid = indexes.indrelid
INNER JOIN pg_catalog.pg_namespace namespaces
   ON namespaces.oid = classes.relnamespace
INNER JOIN pg_catalog.pg_attribute attrs
   ON attrs.attrelid = classes.oid
   AND attrs.attnum = ANY(indexes.indkey)
WHERE namespaces.nspname = {$this->database->quote($this->database->dbschema)}
   AND classes.relname = {$this->database->quote($table)}
   AND indexes.indisprimary
ORDER BY array_position(indexes.indkey, attrs.attnum)
SQL;

        $result = $this->database->queryOrDie($pk_query, 'Read PostgreSQL primary key');
        while ($column = $this->database->fetchAssoc($result)) {
            $primary_key_columns[] = $this->database->quoteName($column['column_name']);
        }

        $definition = 'CREATE TABLE ' . $this->database->quoteName($table) . " (\n";
        $definition .= implode(",\n", array_map(static fn (string $column): string => '  ' . $column, $columns));
        if ($primary_key_columns !== []) {
            $definition .= ",\n  PRIMARY KEY (" . implode(', ', $primary_key_columns) . ')';
        }
        $definition .= "\n)";

        $indexes = [];
        $index_query = <<<SQL
SELECT indexdef
FROM pg_catalog.pg_indexes
WHERE schemaname = {$this->database->quote($this->database->dbschema)}
   AND tablename = {$this->database->quote($table)}
   AND indexname NOT LIKE {$this->database->quote('%_pkey')}
ORDER BY indexname
SQL;
        $result = $this->database->queryOrDie($index_query, 'Read PostgreSQL indexes');
        while ($index = $this->database->fetchRow($result)) {
            $indexes[] = $this->normalizeTableSchema((string) $index[0]);
        }

        return [
            'schema' => $this->normalizeTableSchema($definition),
            'index'  => $indexes,
        ];
    }

    public function areTimezonesAvailable(string &$msg = ''): bool
    {
        $cache = \Config::getCache('cache_db');
        if ($cache->has('are_timezones_available')) {
            return $cache->get('are_timezones_available');
        }

        $cache->set('are_timezones_available', false, DAY_TIMESTAMP);
        $result = $this->database->request('SELECT COUNT(*) AS cpt FROM pg_timezone_names')->next();
        if ((int) ($result['cpt'] ?? 0) === 0) {
            $msg = __('Timezones seems not loaded in PostgreSQL timezone catalog.');
            return false;
        }

        $cache->set('are_timezones_available', true, DAY_TIMESTAMP);
        return true;
    }

    public function setTimezone($timezone): LegacyDatabase|null
    {
        if ($this->areTimezonesAvailable()) {
            date_default_timezone_set($timezone);
            $this->database->query('SET TIME ZONE ' . $this->database->quote($timezone));
            $_SESSION['glpi_currenttime'] = date("Y-m-d H:i:s");
        }

        return $this->database;
    }

    public function getTimezones(): array
    {
        $list = [];
        $iterator = $this->database->request('SELECT name FROM pg_timezone_names ORDER BY name ASC');
        $now = new \DateTime();
        while ($row = $iterator->next()) {
            try {
                $now->setTimezone(new \DateTimeZone($row['name']));
            } catch (\Exception $exception) {
                continue;
            }

            $list[$row['name']] = $row['name'] . $now->format(' (T P)');
        }

        return $list;
    }

    public function notTzMigrated(): int
    {
        $result = $this->database->request([
            'COUNT' => 'cpt',
            'FROM'  => 'information_schema.columns',
            'WHERE' => [
                'table_catalog' => $this->database->dbdefault,
                'table_schema'  => $this->database->dbschema,
                'table_name'    => ['LIKE', 'glpi\_%'],
                'data_type'     => 'timestamp without time zone',
            ],
        ])->next();

        return (int) ($result['cpt'] ?? 0);
    }

    public function getSignedKeysColumns(): \DBmysqlIterator
    {
        return $this->database->request([
            'SELECT' => [
                'NULL AS TABLE_NAME',
                'NULL AS COLUMN_NAME',
                'NULL AS DATA_TYPE',
                'NULL AS COLUMN_DEFAULT',
                'NULL AS IS_NULLABLE',
                'NULL AS EXTRA',
            ],
            'FROM'   => new QueryExpression('(SELECT 1) AS ' . $this->database->quoteName('empty_signed_keys')),
            'WHERE'  => [false],
        ]);
    }

    public function getForeignKeysContraints(): \DBmysqlIterator
    {
        return $this->database->request([
            'SELECT' => [
                'tc.table_schema AS TABLE_SCHEMA',
                'tc.table_name AS TABLE_NAME',
                'kcu.column_name AS COLUMN_NAME',
                'tc.constraint_name AS CONSTRAINT_NAME',
                'ccu.table_name AS REFERENCED_TABLE_NAME',
                'ccu.column_name AS REFERENCED_COLUMN_NAME',
                'kcu.ordinal_position AS ORDINAL_POSITION',
            ],
            'FROM'   => 'information_schema.table_constraints AS tc',
            'INNER JOIN' => [
                'information_schema.key_column_usage AS kcu' => [
                    'ON' => [
                        [
                            'RAW' => [
                                'tc.constraint_name' => new QueryExpression(
                                    '= ' . $this->database->quoteName('kcu.constraint_name')
                                ),
                            ],
                        ],
                        [
                            'RAW' => [
                                'tc.table_schema' => new QueryExpression(
                                    '= ' . $this->database->quoteName('kcu.table_schema')
                                ),
                            ],
                        ],
                    ],
                ],
                'information_schema.constraint_column_usage AS ccu' => [
                    'ON' => [
                        [
                            'RAW' => [
                                'ccu.constraint_name' => new QueryExpression(
                                    '= ' . $this->database->quoteName('tc.constraint_name')
                                ),
                            ],
                        ],
                        [
                            'RAW' => [
                                'ccu.table_schema' => new QueryExpression(
                                    '= ' . $this->database->quoteName('tc.table_schema')
                                ),
                            ],
                        ],
                    ],
                ],
            ],
            'WHERE' => [
                'tc.constraint_type' => 'FOREIGN KEY',
                'ccu.table_catalog'  => $this->database->dbdefault,
                'ccu.table_name'     => ['LIKE', 'glpi\_%'],
            ],
            'ORDER' => ['TABLE_NAME'],
        ]);
    }

    public function getInfo(): array
    {
        $ret = [];
        $req = $this->database->request('SELECT version() AS vers')->next();
        if ($req['vers']) {
            $ret['Server Software'] = 'PostgreSQL';
            $ret['Server Version'] = $req['vers'];
        }

        $ret['Server SQL Mode'] = '';
        $ret['Parameters'] = $this->database->dbuser . '@' . $this->database->dbhost . '/' . $this->database->dbdefault;
        $ret['Host info'] = (string) $this->database->dbhost;

        return $ret;
    }

    public function getDatabaseSize(): string
    {
        $result = $this->database->request([
            'SELECT' => new QueryExpression('ROUND(pg_database_size(current_database()) / 1024.0 / 1024.0, 1) AS dbsize'),
        ])->next();

        return (string) ($result['dbsize'] ?? '');
    }

    public function getLock(string $name): bool
    {
        $lock_id = sprintf('%u', crc32($this->database->dbdefault . '.' . $name));
        $result = $this->database->query('SELECT pg_try_advisory_lock(' . $lock_id . ')');
        [$lock_ok] = $this->database->fetchRow($result);

        return (bool) $lock_ok;
    }

    public function releaseLock(string $name): bool
    {
        $lock_id = sprintf('%u', crc32($this->database->dbdefault . '.' . $name));
        $result = $this->database->query('SELECT pg_advisory_unlock(' . $lock_id . ')');
        [$lock_ok] = $this->database->fetchRow($result);

        return (bool) $lock_ok;
    }

    public function databaseExists(string $database_name): bool
    {
        $result = $this->database->queryOrDie(
            'SELECT 1 FROM pg_database WHERE datname = ' . $this->database->quote($database_name),
            'Check PostgreSQL database existence'
        );

        return $this->database->numrows($result) > 0;
    }

    public function createDatabase(string $database_name): bool
    {
        if ($this->databaseExists($database_name)) {
            return true;
        }

        return (bool) $this->database->query('CREATE DATABASE ' . $this->database->quoteName($database_name));
    }

    public function sqlPosition(string $needle, string $haystack): string
    {
        return 'POSITION(' . $needle . ' IN ' . $haystack . ')';
    }

    public function sqlIf(string $condition, string $when_true, string $when_false): string
    {
        return 'CASE WHEN ' . $condition . ' THEN ' . $when_true . ' ELSE ' . $when_false . ' END';
    }

    public function sqlGroupConcat(string $expression, string $separator = ',', bool $distinct = false, ?string $order_by = null): string
    {
        $expression = 'CAST((' . $expression . ') AS TEXT)';
        $sql = 'STRING_AGG(';
        if ($distinct) {
            $sql .= 'DISTINCT ';
            if ($order_by !== null && $order_by !== '') {
                $order_by = $expression;
            }
        }

        $sql .= $expression . ', ' . $this->database->quote($separator);
        if ($order_by !== null && $order_by !== '') {
            $sql .= ' ORDER BY ' . $order_by;
        }

        return $sql . ')';
    }

    public function sqlIfNull(string $expression, string $fallback): string
    {
        return 'COALESCE(' . $expression . ', ' . $fallback . ')';
    }

    public function sqlCastAsString(string $expression): string
    {
        return 'CAST(' . $expression . ' AS TEXT)';
    }

    public function sqlConcat(array $expressions): string
    {
        if ($expressions === []) {
            return "''";
        }

        if (count($expressions) === 1) {
            return reset($expressions);
        }

        return '(' . implode(' || ', $expressions) . ')';
    }

    public function sqlCastAsUnsignedInteger(string $expression): string
    {
        return 'CAST(FLOOR(((' . $expression . ')::numeric)) AS BIGINT)';
    }

    public function sqlCurrentTimestamp(): string
    {
        return 'CURRENT_TIMESTAMP';
    }

    public function sqlCurrentDate(): string
    {
        return 'CURRENT_DATE';
    }

    public function sqlCurrentHour(): string
    {
        return 'EXTRACT(HOUR FROM CURRENT_TIME)';
    }

    public function sqlLike(string $expression, string $pattern, bool $case_sensitive = true): string
    {
        return $expression
            . ($case_sensitive ? ' LIKE ' : ' ILIKE ')
            . $this->database->quote($pattern);
    }

    public function sqlFullTextBooleanMatch(array $expressions, string $search): string
    {
        $terms = $this->extractFullTextSearchTerms($search);
        if ($expressions === [] || $terms === []) {
            return 'FALSE';
        }

        $field_matches = [];
        foreach ($expressions as $expression) {
            $term_matches = [];
            foreach ($terms as $term) {
                $term_matches[] = $this->sqlLike(
                    'CAST((' . $expression . ') AS TEXT)',
                    '%' . $term . '%',
                    false
                );
            }

            if ($term_matches !== []) {
                $field_matches[] = '(' . implode(' OR ', $term_matches) . ')';
            }
        }

        if ($field_matches === []) {
            return 'FALSE';
        }

        return '(' . implode(' OR ', $field_matches) . ')';
    }

    public function sqlFullTextBooleanScore(array $expressions, string $search): string
    {
        $terms = $this->extractFullTextSearchTerms($search);
        if ($expressions === [] || $terms === []) {
            return '0';
        }

        $scores = [];
        foreach ($expressions as $expression) {
            $cast_expression = 'CAST((' . $expression . ') AS TEXT)';
            foreach ($terms as $term) {
                $scores[] = 'CASE WHEN '
                    . $this->sqlLike($cast_expression, '%' . $term . '%', false)
                    . ' THEN 1 ELSE 0 END';
            }
        }

        if ($scores === []) {
            return '0';
        }

        return '(' . implode(' + ', $scores) . ')';
    }

    public function sqlBitCount(string $expression, int $width = 32): string
    {
        return "LENGTH(REPLACE(({$expression})::bit({$width})::text, '0', ''))";
    }

    public function sqlBitwiseAnd(string $left, string $right): string
    {
        return '(((' . $left . ')::bigint) & ((' . $right . ')::bigint))';
    }

    public function sqlUnixTimestamp(?string $expression = null): string
    {
        return $expression === null
            ? 'EXTRACT(EPOCH FROM NOW())'
            : 'EXTRACT(EPOCH FROM ' . $expression . ')';
    }

    public function sqlDateFormat(string $expression, string $format): string
    {
        return 'TO_CHAR(' . $expression . ', ' . $this->database->quote($this->convertDateFormatToPostgreSql($format)) . ')';
    }

    public function sqlDateTruncateToMinute(?string $expression = null): string
    {
        return 'DATE_TRUNC('
            . $this->database->quote('minute')
            . ', '
            . ($expression ?? $this->sqlCurrentTimestamp())
            . ')';
    }

    public function sqlDateAddInterval(string $expression, $value, string $unit): string
    {
        return sprintf(
            '(((%s)::timestamp) + ((%s) * INTERVAL %s))',
            $expression,
            (string) $value,
            $this->database->quote('1 ' . $this->normalizeIntervalUnit($unit))
        );
    }

    public function sqlDateSubInterval(string $expression, $value, string $unit): string
    {
        return sprintf(
            '(((%s)::timestamp) - ((%s) * INTERVAL %s))',
            $expression,
            (string) $value,
            $this->database->quote('1 ' . $this->normalizeIntervalUnit($unit))
        );
    }

    public function sqlDateDiffDays(string $left, string $right): string
    {
        return '((' . $left . ')::date - (' . $right . ')::date)';
    }

    public function sqlMonthDayOrdinal(string $expression): string
    {
        return '((EXTRACT(MONTH FROM ' . $expression . ') * 100) + EXTRACT(DAY FROM ' . $expression . '))';
    }

    public function sqlTimeDiffInSeconds(string $left, string $right): string
    {
        return 'EXTRACT(EPOCH FROM (((' . $left . ')::timestamp) - ((' . $right . ')::timestamp)))';
    }

    public function sqlClockTimeDiffInSeconds(string $left, string $right): string
    {
        return 'EXTRACT(EPOCH FROM (((' . $left . ')::time) - ((' . $right . ')::time)))';
    }

    public function getImplicitInsertDefaults(string $table, array $reference): array
    {
        $implicit_defaults = [];
        $fields = $this->database->listFields($table);
        if (!is_array($fields)) {
            return $implicit_defaults;
        }

        foreach ($fields as $field_name => $field) {
            if (array_key_exists($field_name, $reference)) {
                continue;
            }

            if (($field['Null'] ?? 'YES') !== 'NO') {
                continue;
            }

            if (($field['Default'] ?? null) !== null) {
                continue;
            }

            if (($field['Extra'] ?? '') === 'auto_increment') {
                continue;
            }

            $type = strtolower((string) ($field['Type'] ?? ''));
            $fallback = match (true) {
                str_starts_with($type, 'varchar'),
                str_starts_with($type, 'char'),
                $type === 'text',
                $type === 'json',
                $type === 'bytea' => '',
                $type === 'boolean',
                str_starts_with($type, 'tinyint(1)') => '0',
                str_starts_with($type, 'int'),
                str_starts_with($type, 'bigint'),
                str_starts_with($type, 'smallint'),
                str_starts_with($type, 'numeric'),
                str_starts_with($type, 'decimal'),
                str_starts_with($type, 'double'),
                str_starts_with($type, 'float') => '0',
                default => null,
            };

            if ($fallback === null) {
                continue;
            }

            $implicit_defaults[$field_name] = $fallback;
        }

        return $implicit_defaults;
    }

    private function normalizeColumnDefinition(array $column): string
    {
        $name = $this->database->quoteName($column['column_name']);
        $type = strtolower((string) $column['formatted_type']);
        $default = $column['column_default'];

        if (preg_match('/^nextval\(/', (string) $default) === 1) {
            $type = match ($type) {
                'smallint' => 'smallserial',
                'bigint'   => 'bigserial',
                default    => 'serial',
            };
            $default = null;
        }

        $definition = $name . ' ' . strtoupper($type);
        $definition .= $column['is_nullable'] ? ' NULL' : ' NOT NULL';

        if ($default !== null && $default !== '' && !str_starts_with((string) $default, 'nextval(')) {
            $definition .= ' DEFAULT ' . $this->normalizeDefaultExpression((string) $default);
        }

        return $definition;
    }

    /**
     * @return string[]
     */
    private function extractFullTextSearchTerms(string $search): array
    {
        $normalized = preg_replace('/[\\\\\"+~<>()-]+/', ' ', $search) ?? $search;
        $normalized = preg_replace('/\s+/', ' ', trim($normalized)) ?? trim($normalized);
        if ($normalized === '') {
            return [];
        }

        $terms = [];
        foreach (explode(' ', $normalized) as $term) {
            $term = rtrim(trim($term), '*');
            if ($term !== '') {
                $terms[] = $term;
            }
        }

        return array_values(array_unique($terms));
    }

    private function normalizeIntervalUnit(string $unit): string
    {
        return match (strtolower($unit)) {
            'second', 'seconds' => 'second',
            'minute', 'minutes' => 'minute',
            'hour', 'hours'     => 'hour',
            'day', 'days'       => 'day',
            'month', 'months'   => 'month',
            'year', 'years'     => 'year',
            default             => strtolower($unit),
        };
    }

    private function normalizeDefaultExpression(string $expression): string
    {
        return match (strtolower($expression)) {
            'current_timestamp', 'current_timestamp()' => 'CURRENT_TIMESTAMP',
            'true' => 'TRUE',
            'false' => 'FALSE',
            default => $expression,
        };
    }

    private function normalizeTableSchema(string $schema): string
    {
        $schema = preg_replace('/\s+/', ' ', trim($schema));
        $schema = preg_replace('/\s*,\s*/', ',', $schema);
        $schema = preg_replace('/\s*\(\s*/', ' (', $schema);
        $schema = preg_replace('/\s*\)\s*/', ')', $schema);

        return strtolower((string) $schema);
    }

    private function convertDateFormatToPostgreSql(string $format): string
    {
        return strtr($format, [
            '%Y' => 'YYYY',
            '%m' => 'MM',
            '%d' => 'DD',
            '%H' => 'HH24',
            '%i' => 'MI',
            '%s' => 'SS',
        ]);
    }
}
