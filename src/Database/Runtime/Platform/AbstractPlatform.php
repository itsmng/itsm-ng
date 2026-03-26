<?php

namespace itsmng\Database\Runtime\Platform;

use QueryExpression;
use itsmng\Database\Runtime\LegacyDatabase;

abstract class AbstractPlatform implements DatabasePlatformInterface
{
    public function listTables(LegacyDatabase $database, $table = 'glpi\_%', array $where = []): \DBmysqlIterator
    {
        return $database->request([
            'SELECT' => 'table_name as TABLE_NAME',
            'FROM'   => 'information_schema.tables',
            'WHERE'  => [
                'table_schema' => $database->dbdefault,
                'table_type'   => 'BASE TABLE',
                'table_name'   => ['LIKE', $table],
            ] + $where,
        ]);
    }

    public function getMyIsamTables(LegacyDatabase $database): \DBmysqlIterator
    {
        return $this->listTables($database, 'glpi\_%', ['engine' => 'MyIsam']);
    }

    public function listFields(LegacyDatabase $database, $table, $usecache = true)
    {
        if (!$database->isCacheDisabled() && $usecache && $database->hasCachedFieldList($table)) {
            return $database->getCachedFieldList($table);
        }

        $result = $database->query("SHOW COLUMNS FROM `$table`");
        if ($result) {
            if ($database->numrows($result) > 0) {
                $fields = [];
                while ($data = $database->fetchAssoc($result)) {
                    $fields[$data['Field']] = $data;
                }
                $database->setCachedFieldList($table, $fields);
                return $fields;
            }
            return [];
        }

        return false;
    }

    public function listIndexes(LegacyDatabase $database, $table)
    {
        if (!$database->tableExists($table)) {
            trigger_error("Table $table does not exists", E_USER_WARNING);
            return false;
        }

        return $database->query('SHOW INDEX FROM ' . $database->quoteName($table));
    }

    public function constraintExists(LegacyDatabase $database, $table, $constraint)
    {
        if (!$database->tableExists($table)) {
            trigger_error("Table $table does not exists", E_USER_WARNING);
            return false;
        }

        $constraints = $database->getTableSchema($table);
        if (!isset($constraints['schema'])) {
            return false;
        }

        return (strpos($constraints['schema'], "CONSTRAINT `$constraint`") !== false);
    }

    public function getTableSchema(LegacyDatabase $database, $table, $structure = null)
    {
        if (is_null($structure)) {
            $result = $database->query("SHOW CREATE TABLE `$table`");
            $row = $database->fetchRow($result);
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

    public function areTimezonesAvailable(LegacyDatabase $database, string &$msg = ''): bool
    {
        $cache = \Config::getCache('cache_db');

        if ($cache->has('are_timezones_available')) {
            return $cache->get('are_timezones_available');
        }
        $cache->set('are_timezones_available', false, DAY_TIMESTAMP);

        $mysql_db_res = $database->request('SHOW DATABASES LIKE ' . $database->quoteValue('mysql'));
        if ($mysql_db_res->count() === 0) {
            $msg = __('Access to timezone database (mysql) is not allowed.');
            return false;
        }

        $tz_table_res = $database->request(
            'SHOW TABLES FROM '
            . $database->quoteName('mysql')
            . ' LIKE '
            . $database->quoteValue('time_zone_name')
        );
        if ($tz_table_res->count() === 0) {
            $msg = __('Access to timezone table (mysql.time_zone_name) is not allowed.');
            return false;
        }

        $iterator = $database->request([
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

    public function setTimezone(LegacyDatabase $database, $timezone): LegacyDatabase
    {
        if ($this->areTimezonesAvailable($database)) {
            date_default_timezone_set($timezone);
            $database->query('SET SESSION time_zone = ' . $database->quote($timezone));
            $_SESSION['glpi_currenttime'] = date("Y-m-d H:i:s");
        }

        return $database;
    }

    public function getTimezones(LegacyDatabase $database): array
    {
        $list = [];
        $from_php = \DateTimeZone::listIdentifiers();
        $now = new \DateTime();

        try {
            $iterator = $database->request([
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

    public function notTzMigrated(LegacyDatabase $database): int
    {
        $result = $database->request([
            'COUNT' => 'cpt',
            'FROM'  => 'information_schema.columns',
            'WHERE' => [
                'information_schema.columns.table_schema' => $database->dbdefault,
                'information_schema.columns.table_name'   => ['LIKE', 'glpi\_%'],
                'information_schema.columns.data_type'    => ['datetime'],
            ],
        ])->next();

        return (int) $result['cpt'];
    }

    public function getSignedKeysColumns(LegacyDatabase $database): \DBmysqlIterator
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
                                    $database->quoteName('information_schema.columns.table_schema')
                                ),
                            ],
                        ],
                    ],
                ],
            ],
            'WHERE'      => [
                'information_schema.tables.table_schema'  => $database->dbdefault,
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

        return $database->request($query);
    }

    public function getForeignKeysContraints(LegacyDatabase $database): \DBmysqlIterator
    {
        return $database->request([
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
                'referenced_table_schema' => $database->dbdefault,
                'referenced_table_name'   => ['LIKE', 'glpi\_%'],
            ],
            'ORDER'  => ['TABLE_NAME'],
        ]);
    }

    public function getInfo(LegacyDatabase $database): array
    {
        $ret = [];
        $req = $database->request("SELECT @@sql_mode as mode, @@version AS vers, @@version_comment AS stype");

        if (($data = $req->next())) {
            $ret['Server Software'] = $data['stype'] ?? '';
            $ret['Server Version'] = $data['vers'] ?? '';
            $ret['Server SQL Mode'] = $data['mode'] ?? '';
        }
        $ret['Parameters'] = $database->dbuser . "@" . $database->dbhost . "/" . $database->dbdefault;
        $ret['Host info'] = $database->getConnectionHandle() instanceof \PDO && defined('PDO::ATTR_CONNECTION_STATUS')
            ? (string) $database->getConnectionHandle()->getAttribute(\PDO::ATTR_CONNECTION_STATUS)
            : '';

        return $ret;
    }

    public function getDatabaseSize(LegacyDatabase $database): string
    {
        $size_res = $database->request([
            'SELECT' => new QueryExpression('ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS dbsize'),
            'FROM'   => 'information_schema.tables',
            'WHERE'  => ['table_schema' => $database->dbdefault],
        ])->next();

        return (string) ($size_res['dbsize'] ?? '');
    }

    public function getLock(LegacyDatabase $database, string $name): bool
    {
        $name = addslashes($database->dbdefault . '.' . $name);
        $result = $database->query("SELECT GET_LOCK('$name', 0)");
        [$lock_ok] = $database->fetchRow($result);

        return (bool) $lock_ok;
    }

    public function releaseLock(LegacyDatabase $database, string $name): bool
    {
        $name = addslashes($database->dbdefault . '.' . $name);
        $result = $database->query("SELECT RELEASE_LOCK('$name')");
        [$lock_ok] = $database->fetchRow($result);

        return (bool) $lock_ok;
    }

    public function databaseExists(LegacyDatabase $database, string $database_name): bool
    {
        $result = $database->request([
            'COUNT' => 'cpt',
            'FROM'  => 'information_schema.schemata',
            'WHERE' => [
                'schema_name' => $database_name,
            ],
        ])->next();

        return (int) ($result['cpt'] ?? 0) > 0;
    }

    public function createDatabase(LegacyDatabase $database, string $database_name): bool
    {
        return (bool) $database->query(
            'CREATE DATABASE IF NOT EXISTS ' . $database::quoteName($database_name)
        );
    }

    public function sqlPosition(LegacyDatabase $database, string $needle, string $haystack): string
    {
        return 'LOCATE(' . $needle . ', ' . $haystack . ')';
    }

    public function sqlIf(LegacyDatabase $database, string $condition, string $when_true, string $when_false): string
    {
        return 'IF(' . $condition . ', ' . $when_true . ', ' . $when_false . ')';
    }

    public function sqlGroupConcat(LegacyDatabase $database, string $expression, string $separator = ',', bool $distinct = false, ?string $order_by = null): string
    {
        $sql = 'GROUP_CONCAT(';
        if ($distinct) {
            $sql .= 'DISTINCT ';
        }
        $sql .= $expression;
        if ($order_by !== null && $order_by !== '') {
            $sql .= ' ORDER BY ' . $order_by;
        }
        $sql .= ' SEPARATOR ' . $database->quote($separator);

        return $sql . ')';
    }

    public function sqlIfNull(LegacyDatabase $database, string $expression, string $fallback): string
    {
        return 'IFNULL(' . $expression . ', ' . $fallback . ')';
    }

    public function sqlCastAsString(LegacyDatabase $database, string $expression): string
    {
        return 'CAST(' . $expression . ' AS CHAR)';
    }

    public function sqlCastAsUnsignedInteger(LegacyDatabase $database, string $expression): string
    {
        return 'CAST(' . $expression . ' AS UNSIGNED)';
    }

    public function sqlCurrentTimestamp(LegacyDatabase $database): string
    {
        return 'CURRENT_TIMESTAMP()';
    }

    public function sqlCurrentDate(LegacyDatabase $database): string
    {
        return 'CURDATE()';
    }

    public function sqlCurrentHour(LegacyDatabase $database): string
    {
        return 'HOUR(CURTIME())';
    }

    public function sqlLike(LegacyDatabase $database, string $expression, string $pattern, bool $case_sensitive = true): string
    {
        return $expression . ' LIKE ' . $database->quote($pattern);
    }

    public function sqlFullTextBooleanMatch(LegacyDatabase $database, array $expressions, string $search): string
    {
        if ($expressions === []) {
            return 'FALSE';
        }

        return 'MATCH('
            . implode(', ', $expressions)
            . ') AGAINST('
            . $database->quote($search)
            . ' IN BOOLEAN MODE)';
    }

    public function sqlFullTextBooleanScore(LegacyDatabase $database, array $expressions, string $search): string
    {
        return '(' . $this->sqlFullTextBooleanMatch($database, $expressions, $search) . ')';
    }

    public function sqlBitCount(LegacyDatabase $database, string $expression, int $width = 32): string
    {
        return 'BIT_COUNT(' . $expression . ')';
    }

    public function sqlBitwiseAnd(LegacyDatabase $database, string $left, string $right): string
    {
        return '((' . $left . ') & (' . $right . '))';
    }

    public function sqlUnixTimestamp(LegacyDatabase $database, ?string $expression = null): string
    {
        return $expression === null ? 'UNIX_TIMESTAMP()' : 'UNIX_TIMESTAMP(' . $expression . ')';
    }

    public function sqlDateFormat(LegacyDatabase $database, string $expression, string $format): string
    {
        return 'DATE_FORMAT(' . $expression . ', ' . $database->quote($format) . ')';
    }

    public function sqlDateTruncateToMinute(LegacyDatabase $database, ?string $expression = null): string
    {
        $value = $expression ?? $database->sqlNow();
        return 'DATE_FORMAT(' . $value . ', ' . $database->quote('%Y-%m-%d %H:%i:00') . ')';
    }

    public function sqlDateAddInterval(LegacyDatabase $database, string $expression, $value, string $unit): string
    {
        return sprintf('DATE_ADD(%s, INTERVAL %s %s)', $expression, (string) $value, strtoupper($unit));
    }

    public function sqlDateSubInterval(LegacyDatabase $database, string $expression, $value, string $unit): string
    {
        return sprintf('DATE_SUB(%s, INTERVAL %s %s)', $expression, (string) $value, strtoupper($unit));
    }

    public function sqlDateDiffDays(LegacyDatabase $database, string $left, string $right): string
    {
        return 'DATEDIFF(' . $left . ', ' . $right . ')';
    }

    public function sqlMonthDayOrdinal(LegacyDatabase $database, string $expression): string
    {
        return '(MONTH(' . $expression . ') * 100 + DAY(' . $expression . '))';
    }

    public function sqlTimeDiffInSeconds(LegacyDatabase $database, string $left, string $right): string
    {
        return 'TIME_TO_SEC(TIMEDIFF(' . $left . ', ' . $right . '))';
    }

    public function sqlClockTimeDiffInSeconds(LegacyDatabase $database, string $left, string $right): string
    {
        return 'TIME_TO_SEC(TIMEDIFF(' . $left . ', ' . $right . '))';
    }

    public function getImplicitInsertDefaults(LegacyDatabase $database, string $table, array $reference): array
    {
        return [];
    }
}
