<?php

namespace itsmng\Database\Runtime\Platform;

use QueryExpression;
use itsmng\Database\Runtime\LegacyDatabase;

abstract class AbstractPlatform implements DatabasePlatformInterface
{
    public function __construct(protected readonly ?LegacyDatabase $database)
    {
    }

    public function listTables($table = 'glpi\_%', array $where = []): \DBmysqlIterator
    {
        return $this->database->request([
            'SELECT' => 'table_name as TABLE_NAME',
            'FROM'   => 'information_schema.tables',
            'WHERE'  => [
                'table_schema' => $this->database->dbdefault,
                'table_type'   => 'BASE TABLE',
                'table_name'   => ['LIKE', $table],
            ] + $where,
        ]);
    }

    public function getMyIsamTables(): \DBmysqlIterator
    {
        return $this->listTables('glpi\_%', ['engine' => 'MyIsam']);
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

        $result = $this->database->query("SHOW COLUMNS FROM `$table`");
        if ($result) {
            if ($this->database->numrows($result) > 0) {
                $fields = [];
                while ($data = $this->database->fetchAssoc($result)) {
                    $fields[$data['Field']] = $data;
                }
                $this->database->setCachedFieldList($table, $fields);
                return $fields;
            }
            return [];
        }

        return false;
    }

    /**
     * @return \itsmng\Database\Runtime\mysqli_result|bool
     */
    public function listIndexes($table)
    {
        if (!$this->database->tableExists($table)) {
            trigger_error("Table $table does not exists", E_USER_WARNING);
            return false;
        }

        return $this->database->query('SHOW INDEX FROM ' . $this->database->quoteName($table));
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

        $constraints = $this->database->getTableSchema($table);
        if (!isset($constraints['schema'])) {
            return false;
        }

        return (strpos($constraints['schema'], "CONSTRAINT `$constraint`") !== false);
    }

    /**
     * @return (string|string[])[]
     *
     * @psalm-return array{schema: string, index: list<lowercase-string>}
     */
    public function getTableSchema($table, $structure = null)
    {
        if (is_null($structure)) {
            $result = $this->database->query("SHOW CREATE TABLE `$table`");
            $row = $this->database->fetchRow($result);
            $structure = $row[1];
        }

        $indexes = [];
        preg_match_all('/KEY `([^`]+)` \(([^)]+)\)/', (string) $structure, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $indexes[] = strtolower($match[0]);
        }

        return [
            'schema' => strtolower((string) preg_replace('/\s+/', ' ', (string) $structure)),
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

        $mysql_db_res = $this->database->request('SHOW DATABASES LIKE ' . $this->database->quoteValue('mysql'));
        if ($mysql_db_res->count() === 0) {
            $msg = __('Access to timezone database (mysql) is not allowed.');
            return false;
        }

        $tz_table_res = $this->database->request(
            'SHOW TABLES FROM '
            . $this->database->quoteName('mysql')
            . ' LIKE '
            . $this->database->quoteValue('time_zone_name')
        );
        if ($tz_table_res->count() === 0) {
            $msg = __('Access to timezone table (mysql.time_zone_name) is not allowed.');
            return false;
        }

        $iterator = $this->database->request([
            'COUNT' => 'cpt',
            'FROM'  => 'mysql.time_zone_name',
        ]);
        $result = $iterator->next();
        if ($result['cpt'] == 0) {
            $msg = __('Timezones seems not loaded, see https://glpi-install.readthedocs.io/en/latest/timezones.html.');
            return false;
        }

        $cache->set('are_timezones_available', true);
        return true;
    }

    public function setTimezone($timezone): LegacyDatabase
    {
        if ($this->database === null) {
            throw new \LogicException('Cannot set timezone without an active database connection.');
        }

        if ($this->areTimezonesAvailable()) {
            date_default_timezone_set($timezone);
            $this->database->query('SET SESSION time_zone = ' . $this->database->quote($timezone));
            $_SESSION['glpi_currenttime'] = date("Y-m-d H:i:s");
        }

        return $this->database;
    }

    public function getTimezones(): array
    {
        $list = [];
        $from_php = \DateTimeZone::listIdentifiers();
        $now = new \DateTime();

        try {
            $iterator = $this->database->request([
                'SELECT' => 'Name',
                'FROM'   => 'mysql.time_zone_name',
                'WHERE'  => ['Name' => $from_php],
            ]);
            while ($from_mysql = $iterator->next()) {
                $now->setTimezone(new \DateTimeZone($from_mysql['Name']));
                $list[$from_mysql['Name']] = $from_mysql['Name'] . $now->format(" (T P)");
            }
        } catch (\Exception $e) {
        }

        return $list;
    }

    public function notTzMigrated(): int
    {
        $result = $this->database->request([
            'COUNT' => 'cpt',
            'FROM'  => 'information_schema.columns',
            'WHERE' => [
                'information_schema.columns.table_schema' => $this->database->dbdefault,
                'information_schema.columns.table_name'   => ['LIKE', 'glpi\_%'],
                'information_schema.columns.data_type'    => ['datetime'],
            ],
        ])->next();

        return (int) $result['cpt'];
    }

    public function getSignedKeysColumns(): \DBmysqlIterator
    {
        $query = [
            'SELECT'     => [
                'information_schema.columns.table_name as TABLE_NAME',
                'information_schema.columns.column_name as COLUMN_NAME',
                'information_schema.columns.data_type as DATA_TYPE',
                'information_schema.columns.column_default as COLUMN_DEFAULT',
                'information_schema.columns.is_nullable as IS_NULLABLE',
                'information_schema.columns.extra as EXTRA',
            ],
            'FROM'       => 'information_schema.columns',
            'INNER JOIN' => [
                'information_schema.tables' => [
                    'FKEY' => [
                        'information_schema.tables'  => 'table_name',
                        'information_schema.columns' => 'table_name',
                        [
                            'AND' => [
                                'information_schema.tables.table_schema' => new QueryExpression(
                                    $this->database->quoteName('information_schema.columns.table_schema')
                                ),
                            ],
                        ],
                    ],
                ],
            ],
            'WHERE'      => [
                'information_schema.tables.table_schema'  => $this->database->dbdefault,
                'information_schema.tables.table_name'    => ['LIKE', 'glpi\_%'],
                'information_schema.tables.table_type'    => 'BASE TABLE',
                [
                    'OR' => [
                        ['information_schema.columns.column_name' => 'id'],
                        ['information_schema.columns.column_name' => ['LIKE', '%\_id']],
                        ['information_schema.columns.column_name' => ['LIKE', '%\_id\_%']],
                    ],
                ],
                'information_schema.columns.data_type' => ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'],
                ['NOT' => ['information_schema.columns.column_type' => ['LIKE', '%unsigned%']]],
            ],
            'ORDER'      => ['TABLE_NAME'],
        ];

        foreach (LegacyDatabase::getAllowedSignedKeys() as $allowed_signed_key) {
            [$excluded_table, $excluded_field] = explode('.', $allowed_signed_key);
            $excluded_fkey = getForeignKeyFieldForTable($excluded_table);
            $query['WHERE'][] = [
                [
                    'NOT' => [
                        'information_schema.tables.table_name'   => $excluded_table,
                        'information_schema.columns.column_name' => $excluded_field,
                    ],
                ],
                ['NOT' => ['information_schema.columns.column_name' => $excluded_fkey]],
                ['NOT' => ['information_schema.columns.column_name' => ['LIKE', str_replace('_', '\_', $excluded_fkey . '_%')]]],
            ];
        }

        return $this->database->request($query);
    }

    public function getForeignKeysContraints(): \DBmysqlIterator
    {
        return $this->database->request([
            'SELECT' => [
                'table_schema as TABLE_SCHEMA',
                'table_name as TABLE_NAME',
                'column_name as COLUMN_NAME',
                'constraint_name as CONSTRAINT_NAME',
                'referenced_table_name as REFERENCED_TABLE_NAME',
                'referenced_column_name as REFERENCED_COLUMN_NAME',
                'ordinal_position as ORDINAL_POSITION',
            ],
            'FROM'   => 'information_schema.key_column_usage',
            'WHERE'  => [
                'referenced_table_schema' => $this->database->dbdefault,
                'referenced_table_name'   => ['LIKE', 'glpi\_%'],
            ],
            'ORDER'  => ['TABLE_NAME'],
        ]);
    }

    public function getInfo(): array
    {
        $ret = [];
        $req = $this->database->request("SELECT @@sql_mode as mode, @@version AS vers, @@version_comment AS stype");

        if (($data = $req->next())) {
            $ret['Server Software'] = $data['stype'] ?? '';
            $ret['Server Version'] = $data['vers'] ?? '';
            $ret['Server SQL Mode'] = $data['mode'] ?? '';
        }
        $ret['Parameters'] = $this->database->dbuser . "@" . $this->database->dbhost . "/" . $this->database->dbdefault;
        $ret['Host info'] = $this->database->getConnectionHandle() instanceof \PDO && defined('PDO::ATTR_CONNECTION_STATUS')
            ? (string) $this->database->getConnectionHandle()->getAttribute(\PDO::ATTR_CONNECTION_STATUS)
            : '';

        return $ret;
    }

    public function getDatabaseSize(): string
    {
        $size_res = $this->database->request([
            'SELECT' => new QueryExpression('ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS dbsize'),
            'FROM'   => 'information_schema.tables',
            'WHERE'  => ['table_schema' => $this->database->dbdefault],
        ])->next();

        return (string) ($size_res['dbsize'] ?? '');
    }

    public function getLock(string $name): bool
    {
        $lock_name = $this->database->quote($this->database->dbdefault . '.' . $name);
        $result = $this->database->query("SELECT GET_LOCK($lock_name, 0)");
        [$lock_ok] = $this->database->fetchRow($result);

        return (bool) $lock_ok;
    }

    public function releaseLock(string $name): bool
    {
        $lock_name = $this->database->quote($this->database->dbdefault . '.' . $name);
        $result = $this->database->query("SELECT RELEASE_LOCK($lock_name)");
        [$lock_ok] = $this->database->fetchRow($result);

        return (bool) $lock_ok;
    }

    public function databaseExists(string $database_name): bool
    {
        $result = $this->database->request([
            'COUNT' => 'cpt',
            'FROM'  => 'information_schema.schemata',
            'WHERE' => [
                'schema_name' => $database_name,
            ],
        ])->next();

        return (int) ($result['cpt'] ?? 0) > 0;
    }

    public function createDatabase(string $database_name): bool
    {
        if ($this->database === null) {
            return false;
        }

        return (bool) $this->database->query(
            'CREATE DATABASE IF NOT EXISTS ' . \itsmng\Database\Runtime\LegacyDatabase::quoteName($database_name)
        );
    }

    public function sqlPosition(string $needle, string $haystack): string
    {
        return 'LOCATE(' . $needle . ', ' . $haystack . ')';
    }

    public function sqlIf(string $condition, string $when_true, string $when_false): string
    {
        return 'IF(' . $condition . ', ' . $when_true . ', ' . $when_false . ')';
    }

    public function sqlGroupConcat(string $expression, string $separator = ',', bool $distinct = false, ?string $order_by = null): string
    {
        $sql = 'GROUP_CONCAT(';
        if ($distinct) {
            $sql .= 'DISTINCT ';
        }
        $sql .= $expression;
        if ($order_by !== null && $order_by !== '') {
            $sql .= ' ORDER BY ' . $order_by;
        }
        $sql .= ' SEPARATOR ' . $this->database->quote($separator);

        return $sql . ')';
    }

    public function sqlIfNull(string $expression, string $fallback): string
    {
        return 'IFNULL(' . $expression . ', ' . $fallback . ')';
    }

    public function sqlCastAsString(string $expression): string
    {
        return 'CAST(' . $expression . ' AS CHAR)';
    }

    public function sqlConcat(array $expressions): string
    {
        return 'CONCAT(' . implode(', ', $expressions) . ')';
    }

    public function sqlCastAsUnsignedInteger(string $expression): string
    {
        return 'CAST(' . $expression . ' AS UNSIGNED)';
    }

    public function sqlCurrentTimestamp(): string
    {
        return 'CURRENT_TIMESTAMP()';
    }

    public function sqlCurrentDate(): string
    {
        return 'CURDATE()';
    }

    public function sqlCurrentHour(): string
    {
        return 'HOUR(CURTIME())';
    }

    public function sqlLike(string $expression, string $pattern, bool $case_sensitive = true): string
    {
        return $expression . ' LIKE ' . $this->database->quote($pattern);
    }

    public function sqlFullTextBooleanMatch(array $expressions, string $search): string
    {
        if ($expressions === []) {
            return 'FALSE';
        }

        return 'MATCH('
            . implode(', ', $expressions)
            . ') AGAINST('
            . $this->database->quote($search)
            . ' IN BOOLEAN MODE)';
    }

    public function sqlFullTextBooleanScore(array $expressions, string $search): string
    {
        return '(' . $this->sqlFullTextBooleanMatch($expressions, $search) . ')';
    }

    public function sqlBitCount(string $expression, int $width = 32): string
    {
        return 'BIT_COUNT(' . $expression . ')';
    }

    public function sqlBitwiseAnd(string $left, string $right): string
    {
        return '((' . $left . ') & (' . $right . '))';
    }

    public function sqlUnixTimestamp(?string $expression = null): string
    {
        return $expression === null ? 'UNIX_TIMESTAMP()' : 'UNIX_TIMESTAMP(' . $expression . ')';
    }

    public function sqlDateFormat(string $expression, string $format): string
    {
        return 'DATE_FORMAT(' . $expression . ', ' . $this->database->quote($format) . ')';
    }

    public function sqlDateTruncateToMinute(?string $expression = null): string
    {
        $value = $expression ?? $this->database->sqlNow();
        return 'DATE_FORMAT(' . $value . ', ' . $this->database->quote('%Y-%m-%d %H:%i:00') . ')';
    }

    public function sqlDateAddInterval(string $expression, $value, string $unit): string
    {
        return sprintf('DATE_ADD(%s, INTERVAL %s %s)', $expression, (string) $value, strtoupper($unit));
    }

    public function sqlDateSubInterval(string $expression, $value, string $unit): string
    {
        return sprintf('DATE_SUB(%s, INTERVAL %s %s)', $expression, (string) $value, strtoupper($unit));
    }

    public function sqlDateDiffDays(string $left, string $right): string
    {
        return 'DATEDIFF(' . $left . ', ' . $right . ')';
    }

    public function sqlMonthDayOrdinal(string $expression): string
    {
        return '(MONTH(' . $expression . ') * 100 + DAY(' . $expression . '))';
    }

    public function sqlTimeDiffInSeconds(string $left, string $right): string
    {
        return 'TIME_TO_SEC(TIMEDIFF(' . $left . ', ' . $right . '))';
    }

    public function sqlClockTimeDiffInSeconds(string $left, string $right): string
    {
        return $this->sqlTimeDiffInSeconds($left, $right);
    }

    public function getImplicitInsertDefaults(string $table, array $reference): array
    {
        return [];
    }
}
