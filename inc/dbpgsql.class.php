<?php

/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2022 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class DBpgsql extends DBmysql
{
    public $dbtype = 'pgsql';

    public $dbschema = 'public';

    public function connect($choice = null)
    {
        $this->connected = false;
        $this->dbh = null;
        $this->rememberActiveDialect();

        if (is_array($this->dbhost)) {
            $index = (isset($choice) ? $choice : mt_rand(0, count($this->dbhost) - 1));
            $host = $this->dbhost[$index];
        } else {
            $host = $this->dbhost;
        }

        $dsn_parts = [];
        $host_value = (string) $host;
        if ($host_value !== '') {
            $host_parts = explode(':', $host_value, 2);
            if (count($host_parts) === 2 && ctype_digit($host_parts[1])) {
                $dsn_parts[] = 'host=' . $host_parts[0];
                $dsn_parts[] = 'port=' . $host_parts[1];
            } else {
                $dsn_parts[] = 'host=' . $host_value;
            }
        }

        if (!empty($this->dbdefault)) {
            $dsn_parts[] = 'dbname=' . $this->dbdefault;
        }

        try {
            $this->dbh = new \PDO(
                'pgsql:' . implode(';', $dsn_parts),
                $this->dbuser,
                rawurldecode((string) $this->dbpassword),
                [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES   => false,
                    \PDO::ATTR_STRINGIFY_FETCHES  => false,
                ]
            );

            $this->connected = true;
            $this->error = 0;
            $this->clearLastError();
            $this->rememberActiveDialect();
            $this->setTimezone($this->guessTimezone());
        } catch (\PDOException $exception) {
            $this->dbh = null;
            $this->connected = false;
            $this->error = 1;
            $this->registerError($exception->getCode(), $exception->getMessage());
        }
    }

    public function listTables($table = 'glpi\_%', array $where = [])
    {
        return $this->request([
            'SELECT' => 'table_name as TABLE_NAME',
            'FROM'   => 'information_schema.tables',
            'WHERE'  => [
                'table_catalog' => $this->dbdefault,
                'table_schema'  => $this->dbschema,
                'table_type'    => 'BASE TABLE',
                'table_name'    => ['LIKE', $table],
            ] + $where,
        ]);
    }

    public function getMyIsamTables(): DBmysqlIterator
    {
        return $this->request([
            'SELECT' => 'table_name as TABLE_NAME',
            'FROM'   => 'information_schema.tables',
            'WHERE'  => [false],
        ]);
    }

    public function insert($table, $params)
    {
        $result = parent::insert($table, $params);
        if ($result && $this->dbh instanceof \PDO) {
            $this->last_insert_id = $this->resolveLastInsertId((string) $table, (array) $params);
            $this->syncAutoIncrementSequence((string) $table, (array) $params);
        }

        return $result;
    }

    public function insertId()
    {
        if ($this->last_insert_id !== null) {
            return $this->last_insert_id;
        }

        try {
            return parent::insertId();
        } catch (\PDOException $exception) {
            return 0;
        }
    }

    public function syncAllAutoIncrementSequences(): void
    {
        $tables = $this->listTables();
        while ($table = $tables->next()) {
            $table_name = $table['TABLE_NAME'] ?? null;
            if (!is_string($table_name) || $table_name === '') {
                continue;
            }

            $this->syncAutoIncrementSequence($table_name, []);
        }
    }

    public function listFields($table, $usecache = true)
    {
        if (!$this->cache_disabled && $usecache && isset($this->field_cache[$table])) {
            return $this->field_cache[$table];
        }

        $table_name = $this->quote($table);
        $schema_name = $this->quote($this->dbschema);
        $catalog_name = $this->quote($this->dbdefault);

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

        $result = $this->query($query);
        if ($result) {
            $this->field_cache[$table] = [];
            while ($data = $this->fetchAssoc($result)) {
                $this->field_cache[$table][$data['Field']] = $data;
            }
            return $this->field_cache[$table];
        }

        return false;
    }

    public function listIndexes($table)
    {
        if (!$this->tableExists($table)) {
            trigger_error("Table $table does not exists", E_USER_WARNING);
            return false;
        }

        return $this->request([
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
                'schemaname' => $this->dbschema,
                'tablename'  => $table,
            ],
            'ORDER'  => ['indexname'],
        ]);
    }

    public function constraintExists($table, $constraint)
    {
        if (!$this->tableExists($table)) {
            trigger_error("Table $table does not exists", E_USER_WARNING);
            return false;
        }

        $result = $this->request([
            'COUNT' => 'cpt',
            'FROM'  => 'information_schema.table_constraints',
            'WHERE' => [
                'table_catalog'   => $this->dbdefault,
                'table_schema'    => $this->dbschema,
                'table_name'      => $table,
                'constraint_name' => $constraint,
            ],
        ])->next();

        return (int) ($result['cpt'] ?? 0) > 0;
    }

    public function getTableSchema($table, $structure = null)
    {
        if ($structure !== null) {
            return [
                'schema' => $this->normalizeTableSchema($structure),
                'index'  => [],
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
WHERE namespaces.nspname = {$this->quote($this->dbschema)}
   AND classes.relname = {$this->quote($table)}
   AND attrs.attnum > 0
   AND NOT attrs.attisdropped
ORDER BY attrs.attnum
SQL;

        $columns = [];
        $result = $this->queryOrDie($columns_query, 'Read PostgreSQL table columns');
        while ($column = $this->fetchAssoc($result)) {
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
WHERE namespaces.nspname = {$this->quote($this->dbschema)}
   AND classes.relname = {$this->quote($table)}
   AND indexes.indisprimary
ORDER BY array_position(indexes.indkey, attrs.attnum)
SQL;

        $result = $this->queryOrDie($pk_query, 'Read PostgreSQL primary key');
        while ($column = $this->fetchAssoc($result)) {
            $primary_key_columns[] = $this->quoteName($column['column_name']);
        }

        $definition = "CREATE TABLE " . $this->quoteName($table) . " (\n";
        $definition .= implode(",\n", array_map(static fn (string $column): string => '  ' . $column, $columns));
        if ($primary_key_columns !== []) {
            $definition .= ",\n  PRIMARY KEY (" . implode(', ', $primary_key_columns) . ')';
        }
        $definition .= "\n)";

        $indexes = [];
        $index_query = <<<SQL
SELECT indexdef
FROM pg_catalog.pg_indexes
WHERE schemaname = {$this->quote($this->dbschema)}
   AND tablename = {$this->quote($table)}
   AND indexname NOT LIKE {$this->quote('%_pkey')}
ORDER BY indexname
SQL;
        $result = $this->queryOrDie($index_query, 'Read PostgreSQL indexes');
        while ($index = $this->fetchRow($result)) {
            $indexes[] = $this->normalizeTableSchema((string) $index[0]);
        }

        return [
            'schema' => $this->normalizeTableSchema($definition),
            'index'  => $indexes,
        ];
    }

    public function beginTransaction()
    {
        $this->in_transaction = true;
        return $this->dbh->beginTransaction();
    }

    public function commit()
    {
        $this->in_transaction = false;
        return $this->dbh->commit();
    }

    public function rollBack()
    {
        if (!$this->dbh instanceof \PDO || !$this->dbh->inTransaction()) {
            $this->in_transaction = false;
            return true;
        }

        $this->in_transaction = false;
        return $this->dbh->rollBack();
    }

    public function areTimezonesAvailable(string &$msg = '')
    {
        $cache = Config::getCache('cache_db');
        if ($cache->has('are_timezones_available')) {
            return $cache->get('are_timezones_available');
        }

        $cache->set('are_timezones_available', false, DAY_TIMESTAMP);
        $result = $this->request('SELECT COUNT(*) AS cpt FROM pg_timezone_names')->next();
        if ((int) ($result['cpt'] ?? 0) === 0) {
            $msg = __('Timezones seems not loaded in PostgreSQL timezone catalog.');
            return false;
        }

        $cache->set('are_timezones_available', true, DAY_TIMESTAMP);
        return true;
    }

    public function setTimezone($timezone)
    {
        if ($this->areTimezonesAvailable()) {
            date_default_timezone_set($timezone);
            $this->dbh->exec('SET TIME ZONE ' . $this->quote($timezone));
            $_SESSION['glpi_currenttime'] = date("Y-m-d H:i:s");
        }

        return $this;
    }

    public function getTimezones()
    {
        $list = [];
        $iterator = $this->request('SELECT name FROM pg_timezone_names ORDER BY name ASC');
        $now = new \DateTime();
        while ($row = $iterator->next()) {
            try {
                $now->setTimezone(new \DateTimeZone($row['name']));
            } catch (\Exception $exception) {
                continue;
            }

            $list[$row['name']] = $row['name'] . $now->format(" (T P)");
        }

        return $list;
    }

    public function notTzMigrated()
    {
        $result = $this->request([
            'COUNT' => 'cpt',
            'FROM'  => 'information_schema.columns',
            'WHERE' => [
                'table_catalog' => $this->dbdefault,
                'table_schema'  => $this->dbschema,
                'table_name'    => ['LIKE', 'glpi\_%'],
                'data_type'     => 'timestamp without time zone',
            ],
        ])->next();

        return (int) ($result['cpt'] ?? 0);
    }

    public function getSignedKeysColumns()
    {
        return $this->request([
            'SELECT' => [
                'NULL AS TABLE_NAME',
                'NULL AS COLUMN_NAME',
                'NULL AS DATA_TYPE',
                'NULL AS COLUMN_DEFAULT',
                'NULL AS IS_NULLABLE',
                'NULL AS EXTRA',
            ],
            'FROM'   => new QueryExpression('(SELECT 1) AS ' . $this->quoteName('empty_signed_keys')),
            'WHERE'  => [false],
        ]);
    }

    public function getForeignKeysContraints()
    {
        $query = [
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
                                    '= ' . $this->quoteName('kcu.constraint_name')
                                ),
                            ],
                        ],
                        [
                            'RAW' => [
                                'tc.table_schema' => new QueryExpression(
                                    '= ' . $this->quoteName('kcu.table_schema')
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
                                    '= ' . $this->quoteName('tc.constraint_name')
                                ),
                            ],
                        ],
                        [
                            'RAW' => [
                                'ccu.table_schema' => new QueryExpression(
                                    '= ' . $this->quoteName('tc.table_schema')
                                ),
                            ],
                        ],
                    ],
                ],
            ],
            'WHERE' => [
                'tc.constraint_type' => 'FOREIGN KEY',
                'ccu.table_catalog'  => $this->dbdefault,
                'ccu.table_name'     => ['LIKE', 'glpi\_%'],
            ],
            'ORDER' => ['TABLE_NAME'],
        ];

        return $this->request($query);
    }

    public function getLock($name)
    {
        $lock_id = sprintf('%u', crc32($this->dbdefault . '.' . $name));
        $result = $this->query('SELECT pg_try_advisory_lock(' . $lock_id . ')');
        [$lock_ok] = $this->fetchRow($result);

        return (bool) $lock_ok;
    }

    public function releaseLock($name)
    {
        $lock_id = sprintf('%u', crc32($this->dbdefault . '.' . $name));
        $result = $this->query('SELECT pg_advisory_unlock(' . $lock_id . ')');
        [$lock_ok] = $this->fetchRow($result);

        return (bool) $lock_ok;
    }

    public function close()
    {
        if ($this->connected) {
            $this->dbh = null;
            $this->connected = false;
            return true;
        }

        return false;
    }

    public function quote($value, int $type = 2/*\PDO::PARAM_STR*/)
    {
        if ($value === null) {
            return 'NULL';
        }

        return $this->dbh->quote((string) $value, $type);
    }

    public function getInfo()
    {
        $ret = [];
        $req = $this->request('SELECT version() AS vers')->next();
        if ($req['vers']) {
            $ret['Server Software'] = 'PostgreSQL';
            $ret['Server Version'] = $req['vers'];
        }

        $ret['Server SQL Mode'] = '';
        $ret['Parameters'] = $this->dbuser . "@" . $this->dbhost . "/" . $this->dbdefault;
        $ret['Host info'] = $this->dbhost;

        return $ret;
    }

    public function setGroupConcatMaxLen(int $length): bool
    {
        return true;
    }

    public function getLastWarning(): ?array
    {
        return null;
    }

    public function getDatabaseSize(): string
    {
        $result = $this->request([
            'SELECT' => new \QueryExpression('ROUND(pg_database_size(current_database()) / 1024.0 / 1024.0, 1) AS dbsize')
        ])->next();

        return (string) ($result['dbsize'] ?? '');
    }

    public function databaseExists(string $database): bool
    {
        $result = $this->queryOrDie(
            'SELECT 1 FROM pg_database WHERE datname = ' . $this->quote($database),
            'Check PostgreSQL database existence'
        );

        return $this->numrows($result) > 0;
    }

    public function createDatabase(string $database): bool
    {
        if ($this->databaseExists($database)) {
            return true;
        }

        return (bool) $this->query('CREATE DATABASE ' . $this->quoteName($database));
    }

    protected function executeQuery(string $query)
    {
        $statement = $this->dbh->query($query);
        $this->last_affected_rows = $statement->rowCount();
        if ($statement->columnCount() === 0) {
            $statement->closeCursor();
            return true;
        }

        return $statement;
    }

    protected function executePreparedQuery(string $query)
    {
        return $this->dbh->prepare($query);
    }

    protected function getDriverErrorCode(?\Throwable $exception = null)
    {
        if ($exception instanceof \PDOException) {
            return $exception->getCode();
        }

        return $this->dbh instanceof \PDO ? (string) ($this->dbh->errorInfo()[0] ?? 0) : 0;
    }

    protected function getDriverErrorMessage(?\Throwable $exception = null): string
    {
        if ($exception instanceof \PDOException) {
            return $exception->getMessage();
        }

        if ($this->dbh instanceof \PDO) {
            $info = $this->dbh->errorInfo();
            return $info[2] ?? '';
        }

        return '';
    }

    protected function prepareQueryString(string $query): string
    {
        return parent::prepareQueryString($query);
    }

    private function syncAutoIncrementSequence(string $table, array $params): void
    {
        $fields = $this->listFields($table);
        if (!is_array($fields)) {
            return;
        }

        foreach ($fields as $field_name => $field) {
            if (($field['Extra'] ?? '') !== 'auto_increment') {
                continue;
            }

            if (array_key_exists($field_name, $params) && !is_numeric($params[$field_name])) {
                continue;
            }

            $table_sql = $this->quote($table);
            $field_sql = $this->quote($field_name);
            $sequence_result = $this->query(
                sprintf(
                    'SELECT pg_get_serial_sequence(%s, %s) AS sequence_name',
                    $table_sql,
                    $field_sql
                )
            );
            $sequence_row = $this->fetchAssoc($sequence_result);
            $sequence_name = $sequence_row['sequence_name'] ?? null;
            if (!is_string($sequence_name) || $sequence_name === '') {
                continue;
            }

            $table_name_sql = $this->quoteName($table);
            $field_name_sql = $this->quoteName($field_name);
            $max_value_sql = sprintf('(SELECT MAX(%s) FROM %s)', $field_name_sql, $table_name_sql);

            $this->query(
                sprintf(
                    'SELECT setval(%s, GREATEST(COALESCE(%s, 1), 1), (COALESCE(%s, 0) >= 1))',
                    $this->quote($sequence_name),
                    $max_value_sql,
                    $max_value_sql
                )
            );
        }
    }

    public function sqlGroupConcat(
        string $expression,
        string $separator = ',',
        bool $distinct = false,
        ?string $order_by = null
    ): string
    {
        $expression = 'CAST((' . $expression . ') AS TEXT)';
        $sql = 'STRING_AGG(';
        if ($distinct) {
            $sql .= 'DISTINCT ';
            if ($order_by !== null && $order_by !== '') {
                // PostgreSQL requires ORDER BY expressions used with DISTINCT aggregates
                // to match the aggregated expression. Ordering by the rendered value keeps
                // deterministic output without forcing call sites to special-case pgsql.
                $order_by = $expression;
            }
        }

        $sql .= $expression . ', ' . $this->quote($separator);
        if ($order_by !== null && $order_by !== '') {
            $sql .= ' ORDER BY ' . $order_by;
        }
        $sql .= ')';

        return $sql;
    }

    public function sqlPosition(string $needle, string $haystack): string
    {
        return 'POSITION(' . $needle . ' IN ' . $haystack . ')';
    }

    public function sqlIf(string $condition, string $when_true, string $when_false): string
    {
        return 'CASE WHEN ' . $condition . ' THEN ' . $when_true . ' ELSE ' . $when_false . ' END';
    }

    public function sqlIfNull(string $expression, string $fallback): string
    {
        return 'COALESCE(' . $expression . ', ' . $fallback . ')';
    }

    public function sqlCastAsString(string $expression): string
    {
        return 'CAST(' . $expression . ' AS TEXT)';
    }

    public function sqlTextSearchExpression(string $expression): string
    {
        return $this->sqlCastAsString($expression);
    }

    public function sqlCastIpAddress(string $expression): string
    {
        return 'CAST(' . $expression . ' AS inet)';
    }

    public function sqlOrderByIpAddress(string $expression, string $alias): string
    {
        return $this->quoteName($alias);
    }

    public function sqlCastAsUnsignedInteger(string $expression): string
    {
        return 'CAST(FLOOR(((' . $expression . ')::numeric)) AS INTEGER)';
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
            . $this->quote($pattern);
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
        if ($expression === null) {
            return 'EXTRACT(EPOCH FROM NOW())';
        }

        return 'EXTRACT(EPOCH FROM ' . $expression . ')';
    }

    public function sqlDateFormat(string $expression, string $format): string
    {
        return 'TO_CHAR(' . $expression . ', ' . $this->quote($this->convertDateFormatToPostgreSql($format)) . ')';
    }

    public function sqlDateTruncateToMinute(?string $expression = null): string
    {
        return 'DATE_TRUNC('
            . $this->quote('minute')
            . ', '
            . ($expression ?? $this->sqlCurrentTimestamp())
            . ')';
    }

    public function sqlDateAddInterval(string $expression, $value, string $unit): string
    {
        $normalized_unit = $this->normalizeIntervalUnit($unit);

        return sprintf(
            '(((%s)::timestamp) + ((%s) * INTERVAL %s))',
            $expression,
            (string) $value,
            $this->quote('1 ' . $normalized_unit)
        );
    }

    public function sqlDateSubInterval(string $expression, $value, string $unit): string
    {
        $normalized_unit = $this->normalizeIntervalUnit($unit);

        return sprintf(
            '(((%s)::timestamp) - ((%s) * INTERVAL %s))',
            $expression,
            (string) $value,
            $this->quote('1 ' . $normalized_unit)
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
        return 'EXTRACT(EPOCH FROM (((' . $left . ')::time) - ((' . $right . ')::time)))';
    }

    public function sqlHavingReference(string $alias, string $expression): string
    {
        return $expression;
    }

    public function getImplicitInsertDefaults(string $table, array $reference): array
    {
        $implicit_defaults = [];
        $fields = $this->listFields($table);
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
        $name = $this->quoteName($column['column_name']);
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

    private function resolveLastInsertId(string $table, array $params)
    {
        if (array_key_exists('id', $params) && is_numeric($params['id'])) {
            return $params['id'];
        }

        $sequence_result = $this->query(
            sprintf(
                'SELECT pg_get_serial_sequence(%s, %s) AS sequence_name',
                $this->quote($table),
                $this->quote('id')
            )
        );
        $sequence_row = $this->fetchAssoc($sequence_result);
        $sequence_name = $sequence_row['sequence_name'] ?? null;
        if (!is_string($sequence_name) || $sequence_name === '') {
            return null;
        }

        $value_result = $this->query(
            sprintf(
                'SELECT CURRVAL(%s) AS last_insert_id',
                $this->quote($sequence_name)
            )
        );
        $value_row = $this->fetchAssoc($value_result);

        return $value_row['last_insert_id'] ?? null;
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
        if (strcasecmp($expression, 'CURRENT_TIMESTAMP') === 0) {
            return 'CURRENT_TIMESTAMP';
        }

        if (preg_match('/^CURRENT_TIMESTAMP\(\)$/i', $expression) === 1) {
            return 'CURRENT_TIMESTAMP';
        }

        if (strcasecmp($expression, 'true') === 0) {
            return 'TRUE';
        }

        if (strcasecmp($expression, 'false') === 0) {
            return 'FALSE';
        }

        return $expression;
    }

    private function normalizeTableSchema(string $schema): string
    {
        $schema = preg_replace('/\s+/', ' ', trim($schema));
        $schema = str_replace(', ', ',', $schema);
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

    protected function normalizeLegacySqlStringValue(string $value): string
    {
        return $this->decodeLegacyMysqlEscapes($value);
    }
}
