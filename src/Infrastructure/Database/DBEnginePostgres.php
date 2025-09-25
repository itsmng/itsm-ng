<?php

namespace Itsmng\Infrastructure\Database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * PostgreSQL engine implementation focused on meta features.
 * Query/iterator compatibility with MySQL is intentionally limited for now.
 */
class DBEnginePostgres implements DBInterface
{
    // Public properties for compatibility
    public $dbhost = '';
    public $dbuser = '';
    public $dbpassword = '';
    public $dbdefault = '';
    public $error = 0;
    public $slave = false;
    public $connected = false;
    public $execution_time = false;

    /** @var PDO|null */
    private $dbh = null;
    private $in_transaction = false;
    private $last_error_msg = '';
    private $last_error_code = 0;

    private $cache_disabled = false;
    private $table_cache = [];
    private $field_cache = [];

    public function __construct($choice = null)
    {
        // defer connection until connect() to allow injecting props
    }

    public function connect($choice = null)
    {
        $this->connected = false;
        $host = $this->dbhost;
        $port = null;
        if (strpos($host, ':') !== false) {
            [$hostOnly, $portStr] = explode(':', $host, 2);
            $host = $hostOnly;
            if (ctype_digit($portStr)) {
                $port = (int) $portStr;
            }
        }

        $dsn = 'pgsql:host=' . ($host ?: 'localhost');
        if (!empty($this->dbdefault)) {
            $dsn .= ';dbname=' . $this->dbdefault;
        }
        if (!empty($port)) {
            $dsn .= ';port=' . $port;
        }

        try {
            $this->dbh = new PDO($dsn, $this->dbuser, $this->dbpassword, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $this->connected = true;
            $this->setTimezone($this->guessTimezone());
        } catch (PDOException $e) {
            $this->last_error_msg = $e->getMessage();
            $this->last_error_code = (int) $e->getCode();
            $this->connected = false;
            $this->error = 1;
            throw new RuntimeException('Failed to connect to PostgreSQL: ' . $e->getMessage(), 0, $e);
        }
    }

    public function guessTimezone()
    {
        // Simpler heuristic than MySQL engine; ORM is authoritative elsewhere
        return date_default_timezone_get();
    }

    public function escape($string)
    {
        if (!$this->dbh) {
            return (string) $string;
        }
        $q = $this->dbh->quote((string) $string);
        // Remove surrounding quotes to mimic MySQL escape() behavior
        return substr($q, 1, -1);
    }

    // Query APIs: not yet supported for Postgres in this iteration
    public function query($query)
    {
        throw new RuntimeException('PostgreSQL query API not available');
    }
    public function queryOrDie($query, $message = '')
    {
        return $this->query($query);
    }
    public function prepare($query)
    {
        throw new RuntimeException('PostgreSQL prepare API not available');
    }
    public function result($result, $i, $field)
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function numrows($result)
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function fetchArray($result)
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function fetchRow($result)
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function fetchAssoc($result)
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function fetchObject($result)
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function dataSeek($result, $num)
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function insertId()
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function numFields($result)
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function fieldName($result, $nb)
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function affectedRows()
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function freeResult($result)
    {
        return true;
    }
    public function errno()
    {
        return $this->last_error_code;
    }
    public function error()
    {
        return $this->last_error_msg;
    }

    public function close()
    {
        $this->dbh = null;
        $this->connected = false;
        return true;
    }

    public function isSlave()
    {
        return $this->slave;
    }
    public function runFile($path)
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function request($tableorsql, $crit = "", $debug = false)
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }

    public function getInfo()
    {
        $ret = [];
        try {
            $row = $this->dbh?->query('SELECT version() AS vers')->fetch() ?: null;
            if ($row && !empty($row['vers'])) {
                $ret['Server Version'] = $row['vers'];
            }
        } catch (\Throwable $e) {
        }
        $ret['Parameters'] = $this->dbuser . '@' . $this->dbhost . '/' . $this->dbdefault;
        return $ret;
    }

    public static function isMySQLStrictMode(&$msg)
    {
        $msg = '';
        return false;
    }

    public function getLock($name)
    {
        // Use advisory locks in PostgreSQL
        // Hash the name into two 32-bit integers for pg_try_advisory_lock
        $hash = crc32($this->dbdefault . '.' . $name);
        $stmt = $this->dbh->prepare('SELECT pg_try_advisory_lock(:key) AS locked');
        $stmt->execute([':key' => (int) $hash]);
        $row = $stmt->fetch();
        return (bool) ($row['locked'] ?? false);
    }

    public function releaseLock($name)
    {
        $hash = crc32($this->dbdefault . '.' . $name);
        $stmt = $this->dbh->prepare('SELECT pg_advisory_unlock(:key) AS unlocked');
        $stmt->execute([':key' => (int) $hash]);
        $row = $stmt->fetch();
        return (bool) ($row['unlocked'] ?? false);
    }

    public function tableExists($tablename, $usecache = true)
    {
        if (!$this->cache_disabled && $usecache && in_array($tablename, $this->table_cache, true)) {
            return true;
        }
        $sql = "SELECT 1 FROM information_schema.tables
        WHERE table_catalog = current_database()
          AND table_schema NOT IN ('pg_catalog','information_schema')
          AND table_type='BASE TABLE'
          AND table_name = :t LIMIT 1";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([':t' => $tablename]);
        $exists = (bool) $stmt->fetchColumn();
        if ($exists && !$this->cache_disabled) {
            $this->table_cache[] = $tablename;
        }
        return $exists;
    }

    public function fieldExists($table, $field, $usecache = true)
    {
        if (!$this->tableExists($table, $usecache)) {
            trigger_error("Table $table does not exists", E_USER_WARNING);
            return false;
        }
        $fields = $this->listFields($table, $usecache);
        return is_array($fields) && array_key_exists($field, $fields);
    }

    public function constraintExists($table, $constraint)
    {
        if (!$this->tableExists($table)) {
            trigger_error("Table $table does not exists", E_USER_WARNING);
            return false;
        }
        $sql = "SELECT 1
                FROM information_schema.table_constraints tc
                WHERE tc.table_catalog = current_database()
                    AND tc.table_schema NOT IN ('pg_catalog','information_schema')
                    AND tc.table_name = :t
                    AND tc.constraint_name = :c
                LIMIT 1";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([':t' => $table, ':c' => $constraint]);
        return (bool) $stmt->fetchColumn();
    }

    public function disableTableCaching()
    {
        $this->cache_disabled = true;
    }

    public function listTables($table = 'glpi\_%', array $where = [])
    {
        // Convert MySQL-style escaped pattern to Postgres and use ESCAPE \\
        $pattern = $table;
        $sql = "SELECT table_name, table_type
        FROM information_schema.tables
        WHERE table_catalog = current_database()
          AND table_schema NOT IN ('pg_catalog','information_schema')
          AND table_type = 'BASE TABLE'
          AND table_name LIKE :pattern ESCAPE '\\'";

        // Support optional filters in $where for compatibility when possible
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([':pattern' => $pattern]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        // Normalize keys like MySQL engine: TABLE_NAME, TABLE_TYPE
        $mapped = array_map(function ($r) {
            return [
                'TABLE_NAME' => $r['table_name'] ?? null,
                'TABLE_TYPE' => $r['table_type'] ?? null,
            ];
        }, $rows);
        return new PgArrayIterator($mapped);
    }

    public function getMyIsamTables()
    {
        // Not applicable to PostgreSQL; return empty iterator
        return new PgArrayIterator([]);
    }

    public function listFields($table, $usecache = true)
    {
        if (!$this->cache_disabled && $usecache && isset($this->field_cache[$table])) {
            return $this->field_cache[$table];
        }
        $sql = "SELECT column_name, data_type, is_nullable, column_default
                FROM information_schema.columns
                WHERE table_catalog = current_database()
                    AND table_schema NOT IN ('pg_catalog','information_schema')
                    AND table_name = :t
                ORDER BY ordinal_position";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([':t' => $table]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $result = [];
        foreach ($rows as $r) {
            $result[$r['column_name']] = [
                'Field' => $r['column_name'],
                'Type' => $r['data_type'],
                'Null' => $r['is_nullable'],
                'Default' => $r['column_default'],
                'Key' => '',
                'Extra' => '',
            ];
        }
        if (!$this->cache_disabled) {
            $this->field_cache[$table] = $result;
        }
        return $result;
    }

    public function getField(string $table, string $field, $usecache = true): ?array
    {
        $fields = $this->listFields($table, $usecache);
        return $fields[$field] ?? null;
    }

    // CRUD builders are not used for PostgreSQL in this phase
    public function buildInsert($table, $params)
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function insert($table, $params)
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function insertOrDie($table, $params, $message = '')
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function buildUpdate($table, $params, $clauses, array $joins = [])
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function update($table, $params, $where, array $joins = [])
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function updateOrDie($table, $params, $where, $message = '', array $joins = [])
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function updateOrInsert($table, $params, $where, $onlyone = true)
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function buildDelete($table, $where, array $joins = [])
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function delete($table, $where, array $joins = [])
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }
    public function deleteOrDie($table, $where, $message = '', array $joins = [])
    {
        throw new RuntimeException('Not implemented for PostgreSQL');
    }

    public function getTableSchema($table, $structure = null)
    {
        // Provide a simplified schema string and index list for informational use
        $schema = '';
        try {
            $cols = $this->listFields($table) ?: [];
            $parts = [];
            foreach ($cols as $c) {
                $parts[] = $c['Field'] . ' ' . $c['Type'];
            }
            $schema = strtolower('CREATE TABLE ' . $table . ' (' . implode(',', $parts) . ')');
        } catch (\Throwable $e) {
        }
        $indexes = [];
        try {
            $sql = "SELECT indexname FROM pg_indexes
            WHERE schemaname NOT IN ('pg_catalog','information_schema')
              AND schemaname IN (SELECT nspname FROM pg_namespace WHERE obj_description(oid, 'pg_namespace') IS NOT NULL OR nspname = ANY(current_schemas(true)))
              AND tablename = :t";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([':t' => $table]);
            $indexes = array_map(function ($r) {
                return $r['indexname'];
            }, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
        } catch (\Throwable $e) {
        }
        return ['schema' => $schema, 'index' => $indexes];
    }

    public function getVersion()
    {
        try {
            $row = $this->dbh?->query('SELECT version()')->fetch(PDO::FETCH_NUM);
            return $row ? (string) $row[0] : '0';
        } catch (\Throwable $e) {
            return '0';
        }
    }

    public function beginTransaction()
    {
        $this->in_transaction = true;
        return (bool) $this->dbh?->beginTransaction();
    }
    public function commit()
    {
        $this->in_transaction = false;
        return (bool) $this->dbh?->commit();
    }
    public function rollBack()
    {
        $this->in_transaction = false;
        return (bool) $this->dbh?->rollBack();
    }
    public function inTransaction()
    {
        return $this->in_transaction;
    }

    public function areTimezonesAvailable(string &$msg = '')
    {
        // Postgres ships with pg_timezone_names; verify we can read it and it is populated
        try {
            $stmt = $this->dbh->query("SELECT count(*) AS c FROM pg_timezone_names");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || (int) $row['c'] === 0) {
                $msg = 'Timezones list not available (pg_timezone_names empty).';
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            $msg = 'Access to timezone view (pg_timezone_names) is not allowed.';
            return false;
        }
    }

    public function setTimezone($timezone)
    {
        if ($this->dbh) {
            try {
                $this->dbh->exec("SET TIME ZONE '" . str_replace("'", "''", (string) $timezone) . "'");
            } catch (\Throwable $e) {
                // ignore
            }
        }
        date_default_timezone_set($timezone);
        if (isset($_SESSION)) {
            $_SESSION['glpi_currenttime'] = date('Y-m-d H:i:s');
        }
        return $this;
    }

    public function getTimezones()
    {
        $list = [];
        $from_php = \DateTimeZone::listIdentifiers();
        $now = new \DateTime();
        try {
            $stmt = $this->dbh->query('SELECT name FROM pg_timezone_names');
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $pg_names = array_column($rows, 'name');
            $valid = array_intersect($pg_names, $from_php);
            foreach ($valid as $tz) {
                $now->setTimezone(new \DateTimeZone($tz));
                $list[$tz] = $tz . $now->format(' (T P)');
            }
        } catch (\Throwable $e) {
        }
        return $list;
    }

    public function notTzMigrated()
    {
        return 0;
    }

    public function getSignedKeysColumns()
    {
        // PostgreSQL has no unsigned types; return empty
        return new PgArrayIterator([]);
    }

    public function getForeignKeysContraints()
    {
        $sql = "SELECT
                    tc.table_schema AS TABLE_SCHEMA,
                    tc.table_name AS TABLE_NAME,
                    kcu.column_name AS COLUMN_NAME,
                    tc.constraint_name AS CONSTRAINT_NAME,
                    ccu.table_name AS REFERENCED_TABLE_NAME,
                    ccu.column_name AS REFERENCED_COLUMN_NAME,
                    kcu.ordinal_position AS ORDINAL_POSITION
                FROM information_schema.table_constraints tc
                JOIN information_schema.key_column_usage kcu
                  ON tc.constraint_name = kcu.constraint_name
                 AND tc.table_schema = kcu.table_schema
                JOIN information_schema.constraint_column_usage ccu
                  ON ccu.constraint_name = tc.constraint_name
                 AND ccu.table_schema = tc.table_schema
               WHERE tc.constraint_type = 'FOREIGN KEY'
                 AND tc.table_schema = ANY (current_schemas(true))
               ORDER BY tc.table_name, kcu.ordinal_position";
        $stmt = $this->dbh->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return new PgArrayIterator($rows);
    }

    public function clearSchemaCache()
    {
        $this->table_cache = [];
        $this->field_cache = [];
    }

    public function quote($value, int $type = 2)
    {
        if ($value === null) {
            return 'NULL';
        }
        if ($this->dbh) {
            return $this->dbh->quote((string) $value);
        }
        return "'" . str_replace("'", "''", (string) $value) . "'";
    }

    public static function getQuoteNameChar(): string
    {
        return '"';
    }
    public static function isNameQuoted($value): bool
    {
        $q = static::getQuoteNameChar();
        return is_string($value) && trim($value, $q) != $value;
    }
    public static function quoteName($name)
    {
        if (is_string($name) && strpos($name, '.')) {
            $n = explode('.', $name, 2);
            $table = '"' . str_replace('"', '""', $n[0]) . '"';
            $field = ($n[1] === '*') ? $n[1] : '"' . str_replace('"', '""', $n[1]) . '"';
            return "$table.$field";
        }
        if (is_string($name) && $name === '*') {
            return $name;
        }
        return '"' . str_replace('"', '""', (string) $name) . '"';
    }
    public static function quoteValue($value)
    {
        if ($value === null || $value === 'NULL' || $value === 'null') {
            return 'NULL';
        }
        if ($value instanceof \DateTime) {
            return "'" . $value->format('Y-m-d H:i:s') . "'";
        }
        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }
        return "'" . str_replace("'", "''", (string) $value) . "'";
    }

    public function removeSqlComments($output)
    {
        return $output;
    }
    public function removeSqlRemarks($sql)
    {
        return $sql;
    }
}
// PgArrayIterator moved to its own file
