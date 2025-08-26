<?php

namespace Itsmng\Infrastructure\Database;

use Glpi\Application\ErrorHandler;
use mysqli;
use mysqli_sql_exception;
use \DBmysqlIterator;
use \Toolbox;
use \Timer;
use \Session;
use \Html;
use \Config;
use \QueryExpression;
use \QueryParam;

/**
 * MySQL engine implementation, adapted from legacy DBmysql.
 */
class DBEngineMySQL implements DBInterface
{
    private const ALLOWED_SIGNED_KEYS = [
        'glpi_calendars.id',
        'glpi_changetemplates.id',
        'glpi_contracts.id',
        'glpi_entities.id',
        'glpi_problemtemplates.id',
        'glpi_tickettemplates.id',
        'glpi_transfers.id',
    ];

    public $dbhost = "";
    public $dbuser = "";
    public $dbpassword = "";
    public $dbdefault = "";
    private $dbh;
    public $error = 0;
    public $slave = false;
    private $in_transaction;
    public $dbssl = false;
    public $dbsslkey = null;
    public $dbsslcert = null;
    public $dbsslca = null;
    public $dbsslcapath = null;
    public $dbsslcacipher = null;
    public $first_connection = true;
    public $connected = false;
    public $execution_time = false;
    private $cache_disabled = false;
    private $table_cache = [];
    private $field_cache = [];
    /**
     * Reference to facade DBmysql for iterator-based features.
     * @var \DBmysql|null
     */
    public $parentFacade = null;

    public function __construct($choice = null)
    {
        // Defer connection until facade supplies env properties
    }

    public function connect($choice = null)
    {
        $this->connected = false;
        $this->dbh = @new mysqli();
        if ($this->dbssl) {
            mysqli_ssl_set(
                $this->dbh,
                $this->dbsslkey,
                $this->dbsslcert,
                $this->dbsslca,
                $this->dbsslcapath,
                $this->dbsslcacipher
            );
        }

    $hosts = is_array($this->dbhost) ? $this->dbhost : [$this->dbhost];
    $i    = (isset($choice) ? $choice : mt_rand(0, max(count($hosts) - 1, 0)));
    $host = $hosts[$i] ?? $hosts[0];

        $hostport = explode(":", $host);
        if (count($hostport) < 2) {
            $this->dbh->real_connect($host, $this->dbuser, rawurldecode($this->dbpassword), $this->dbdefault);
        } elseif (intval($hostport[1]) > 0) {
            $this->dbh->real_connect($hostport[0], $this->dbuser, rawurldecode($this->dbpassword), $this->dbdefault, $hostport[1]);
        } else {
            $this->dbh->real_connect($hostport[0], $this->dbuser, rawurldecode($this->dbpassword), $this->dbdefault, ini_get('mysqli.default_port'), $hostport[1]);
        }

        if ($this->dbh->connect_error) {
            $this->connected = false;
            $this->error     = 1;
        } elseif (!defined('MYSQLI_OPT_INT_AND_FLOAT_NATIVE')) {
            $this->connected = false;
            $this->error     = 2;
        } else {
            if (isset($this->dbenc)) {
                Toolbox::deprecated('Usage of alternative DB connection encoding (DB::$dbenc property) is deprecated.');
            }
            $dbenc = isset($this->dbenc) ? $this->dbenc : "utf8";
            $this->dbh->set_charset($dbenc);
            if ($dbenc === "utf8") {
                $this->dbh->query("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci';");
            }

            if (defined('MYSQLI_OPT_INT_AND_FLOAT_NATIVE')) {
                $this->dbh->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
            }

            if (defined('GLPI_FORCE_EMPTY_SQL_MODE') && GLPI_FORCE_EMPTY_SQL_MODE) {
                $this->dbh->query("SET SESSION sql_mode = ''");
            }

            $this->connected = true;

            $this->setTimezone($this->guessTimezone());
        }
    }

    public function guessTimezone()
    {
        if (isset($_SESSION['glpi_tz'])) {
            $zone = $_SESSION['glpi_tz'];
        } else {
            $conf_tz = ['value' => null];
            if (
                $this->tableExists(Config::getTable())
                && $this->fieldExists(Config::getTable(), 'value')
            ) {
                $conf_tz = $this->request([
                   'SELECT' => 'value',
                   'FROM'   => Config::getTable(),
                   'WHERE'  => [
                      'context'   => 'core',
                      'name'      => 'timezone'
                    ]
                ])->next();
            }
            $zone = !empty($conf_tz['value']) ? $conf_tz['value'] : date_default_timezone_get();
        }

        return $zone;
    }

    public function escape($string)
    {
        return $this->dbh->real_escape_string($string ?? '');
    }

    public function query($query)
    {
        global $CFG_GLPI, $DEBUG_SQL, $GLPI, $SQL_TOTAL_REQUEST;

        $is_debug = isset($_SESSION['glpi_use_mode']) && ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE);
        if ($is_debug && $CFG_GLPI["debug_sql"]) {
            $SQL_TOTAL_REQUEST++;
            $DEBUG_SQL["queries"][$SQL_TOTAL_REQUEST] = $query;
        }
        if ($is_debug && $CFG_GLPI["debug_sql"] || $this->execution_time === true) {
            $TIMER = new Timer();
            $TIMER->start();
        }

        try {
            $res = $this->dbh->query($query);

            if ($is_debug && $CFG_GLPI["debug_sql"]) {
                $TIME = $TIMER->getTime();
                $DEBUG_SQL["times"][$SQL_TOTAL_REQUEST] = $TIME;
                $DEBUG_SQL['rows'][$SQL_TOTAL_REQUEST] = $this->affectedRows();
            }
            if ($this->execution_time === true) {
                $this->execution_time = $TIMER->getTime(0, true);
            }
            return $res;
        } catch (mysqli_sql_exception $e) {
            $error = "  *** MySQL query error:\n  SQL: " . $query . "\n  Error: " .
                      $this->dbh->error . "\n";
            $error .= Toolbox::backtrace(false, 'DBmysql->query()', ['Toolbox::backtrace()']);

            Toolbox::logSqlError($error);

            if (isset($GLPI)) {
                $error_handler = $GLPI->getErrorHandler();
                if ($error_handler instanceof ErrorHandler) {
                    $error_handler->handleSqlError($this->dbh->errno, $this->dbh->error, $query);
                }
            }

            if (($is_debug || function_exists('isAPI') && isAPI()) && $CFG_GLPI["debug_sql"]) {
                $DEBUG_SQL["errors"][$SQL_TOTAL_REQUEST] = $this->error();
            }
        }
    }

    public function queryOrDie($query, $message = '')
    {
        $res = $this->query($query);
        if (!$res) {
            $message = sprintf(
                __('%1$s - Error during the database query: %2$s - Error is %3$s'),
                $message,
                $query,
                $this->error()
            );
            if (function_exists('isCommandLine') && isCommandLine()) {
                throw new \RuntimeException($message);
            } else {
                echo $message . "\n";
                die(1);
            }
        }
        return $res;
    }

    public function prepare($query)
    {
        global $CFG_GLPI, $DEBUG_SQL, $SQL_TOTAL_REQUEST;

        $res = $this->dbh->prepare($query);
        if (!$res) {
            $error = "  *** MySQL prepare error:\n  SQL: " . $query . "\n  Error: " .
                      $this->dbh->error . "\n";
            $error .= Toolbox::backtrace(false, 'DBmysql->prepare()', ['Toolbox::backtrace()']);

            Toolbox::logInFile("sql-errors", $error);
            if (class_exists('GlpitestSQLError')) {
                throw new \GlpitestSQLError($error);
            }

            if (
                isset($_SESSION['glpi_use_mode'])
                && $_SESSION['glpi_use_mode'] == Session::DEBUG_MODE
                && $CFG_GLPI["debug_sql"]
            ) {
                $SQL_TOTAL_REQUEST++;
                $DEBUG_SQL["errors"][$SQL_TOTAL_REQUEST] = $this->error();
            }
        }
        return $res;
    }

    public function result($result, $i, $field)
    {
        if (
            $result && ($result->data_seek($i))
            && ($data = $result->fetch_array())
            && isset($data[$field])
        ) {
            return $data[$field];
        }
        return null;
    }

    public function numrows($result)
    {
        return $result->num_rows;
    }

    public function fetchArray($result)
    {
        return $result->fetch_array();
    }

    public function fetchRow($result)
    {
        return $result->fetch_row();
    }

    public function fetchAssoc($result)
    {
        return $result->fetch_assoc();
    }

    public function fetchObject($result)
    {
        return $result->fetch_object();
    }

    public function dataSeek($result, $num)
    {
        return $result->data_seek($num);
    }

    public function insertId()
    {
        return $this->dbh->insert_id;
    }

    public function numFields($result)
    {
        return $result->field_count;
    }

    public function fieldName($result, $nb)
    {
        $finfo = $result->fetch_fields();
        return $finfo[$nb]->name;
    }

     public function listTables($table = 'glpi\\_%', array $where = [])
     {
          return $this->request([
              'SELECT' => 'table_name as TABLE_NAME',
              'FROM'   => 'information_schema.tables',
              'WHERE'  => [
                  'table_schema' => $this->dbdefault,
                  'table_type'   => 'BASE TABLE',
                  'table_name'   => ['LIKE', $table]
              ] + $where
          ]);
     }

    public function getMyIsamTables(): DBmysqlIterator
    {
    return $this->listTables('glpi\\_%', ['engine' => 'MyIsam']);
    }

    public function listFields($table, $usecache = true)
    {
        if (!$this->cache_disabled && $usecache && isset($this->field_cache[$table])) {
            return $this->field_cache[$table];
        }
        $result = $this->query("SHOW COLUMNS FROM $table");
        if ($result) {
            if ($this->numrows($result) > 0) {
                $this->field_cache[$table] = [];
                while ($data = $this->fetchAssoc($result)) {
                    $this->field_cache[$table][$data["Field"]] = $data;
                }
                return $this->field_cache[$table];
            }
            return [];
        }
        return false;
    }

    public function getField(string $table, string $field, $usecache = true): ?array
    {
        $fields = $this->listFields($table, $usecache);
        return $fields[$field] ?? null;
    }

    public function affectedRows()
    {
        return $this->dbh->affected_rows;
    }

    public function freeResult($result)
    {
        return $result->free();
    }

    public function errno()
    {
        return $this->dbh->errno;
    }

    public function error()
    {
        return $this->dbh->error;
    }

    public function close()
    {
        if ($this->connected && $this->dbh) {
            return $this->dbh->close();
        }
        return false;
    }

    public function isSlave()
    {
        return $this->slave;
    }

    public function runFile($path)
    {
        $script = fopen($path, 'r');
        if (!$script) {
            return false;
        }
        $sql_query = @fread(
            $script,
            @filesize($path)
        ) . "\n";
        $sql_query = html_entity_decode($sql_query, ENT_COMPAT, 'UTF-8');

        $sql_query = $this->removeSqlRemarks($sql_query);
        $queries = preg_split('/;\s*$/m', $sql_query);

        foreach ($queries as $query) {
            $query = trim($query);
            if ($query != '') {
                if (!$this->query($query)) {
                    return false;
                }
                if (!function_exists('isCommandLine') || !isCommandLine()) {
                    echo ' ';
                    Html::glpi_flush();
                }
            }
        }

        return true;
    }

    public function request($tableorsql, $crit = "", $debug = false)
    {
        // Always route through facade to keep signature expected by iterator
        if (!($this->parentFacade instanceof \DBmysql)) {
            $this->parentFacade = new \DBmysql();
        }
        return $this->parentFacade->request($tableorsql, $crit, $debug);
    }

    public function getInfo()
    {
        $ret = [];
        $req = $this->request("SELECT @@sql_mode as mode, @@version AS vers, @@version_comment AS stype");

        if (($data = $req->next())) {
            if ($data['stype']) {
                $ret['Server Software'] = $data['stype'];
            }
            if ($data['vers']) {
                $ret['Server Version'] = $data['vers'];
            } else {
                $ret['Server Version'] = $this->dbh->server_info;
            }
            if ($data['mode']) {
                $ret['Server SQL Mode'] = $data['mode'];
            } else {
                $ret['Server SQL Mode'] = '';
            }
        }
        $ret['Parameters'] = $this->dbuser . "@" . $this->dbhost . "/" . $this->dbdefault;
        $ret['Host info']  = $this->dbh->host_info;

        return $ret;
    }

    public static function isMySQLStrictMode(&$msg)
    {
        Toolbox::deprecated();
        global $DB;

        $msg = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_DATE,NO_ZERO_IN_DATE,ONLY_FULL_GROUP_BY,NO_AUTO_CREATE_USER';
        $req = $DB->request("SELECT @@sql_mode as mode");
        if (($data = $req->next())) {
            return (preg_match("/STRICT_TRANS/", $data['mode'])
                    && preg_match("/NO_ZERO_/", $data['mode'])
                    && preg_match("/ONLY_FULL_GROUP_BY/", $data['mode']));
        }
        return false;
    }

    public function getLock($name)
    {
        $name          = addslashes($this->dbdefault . '.' . $name);
        $query         = "SELECT GET_LOCK('$name', 0)";
        $result        = $this->query($query);
        list($lock_ok) = $this->fetchRow($result);

        return (bool)$lock_ok;
    }

    public function releaseLock($name)
    {
        $name          = addslashes($this->dbdefault . '.' . $name);
        $query         = "SELECT RELEASE_LOCK('$name')";
        $result        = $this->query($query);
        list($lock_ok) = $this->fetchRow($result);

        return $lock_ok;
    }

    public function tableExists($tablename, $usecache = true)
    {
        if (!$this->cache_disabled && $usecache && in_array($tablename, $this->table_cache)) {
            return true;
        }

        $retrieve_all = !$this->cache_disabled && empty($this->table_cache);

        $result = $this->listTables($retrieve_all ? 'glpi\_%' : $tablename);
        $found_tables = [];
        while ($data = $result->next()) {
            $found_tables[] = $data['TABLE_NAME'];
        }

        if (!$this->cache_disabled) {
            $this->table_cache = array_unique(array_merge($this->table_cache, $found_tables));
        }

        if (in_array($tablename, $found_tables)) {
            return true;
        }

        return false;
    }

    public function fieldExists($table, $field, $usecache = true)
    {
        if (!$this->tableExists($table, $usecache)) {
            trigger_error("Table $table does not exists", E_USER_WARNING);
            return false;
        }

        if ($fields = $this->listFields($table, $usecache)) {
            if (isset($fields[$field])) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function constraintExists($table, $constraint)
    {
        if (!$this->tableExists($table)) {
            trigger_error("Table $table does not exists", E_USER_WARNING);
            return false;
        }
        $result = $this->query("SHOW CREATE TABLE $table");
        if ($result) {
            if ($this->numrows($result) > 0) {
                $data = $this->fetchArray($result);
                if (preg_match("/CONSTRAINT $constraint FOREIGN KEY/", $data[1])) {
                    return true;
                }
            }
        }
        return false;
    }

    public function disableTableCaching()
    {
        $this->cache_disabled = true;
    }

    public static function quoteName($name)
    {
        if ($name instanceof QueryExpression) {
            return $name->getValue();
        }
        $names = is_string($name) ? preg_split('/\s+AS\s+/i', $name) : [$name];
        if (count($names) > 2) {
            throw new \RuntimeException(
                'Invalid field name ' . $name
            );
        }
        if (count($names) == 2) {
            $name = self::quoteName($names[0]);
            $name .= ' AS ' . self::quoteName($names[1]);
            return $name;
        } else {
            if (is_string($name) && strpos($name, '.')) {
                $n = explode('.', $name, 2);
                $table = self::quoteName($n[0]);
                $field = ($n[1] === '*') ? $n[1] : self::quoteName($n[1]);
                return "$table.$field";
            }

            return (is_string($name) && isset($name[0]) && $name[0] == '`') ? $name :
            ((is_string($name) && $name === '*') ? $name : (is_array($name) ? implode('.', $name) : (string)$name));
        }
    }

    public static function quoteValue($value)
    {
        if ($value instanceof QueryParam || $value instanceof QueryExpression) {
            $value = $value->getValue();
        } elseif ($value === null || $value === 'NULL' || $value === 'null') {
            $value = 'NULL';
        } elseif ($value instanceof \DateTime) {
            $value = "'" . $value->format('Y-m-d H:i:s') . "'";
        } elseif (is_bool($value)) {
            $value = "'" . (int)$value . "'";
        } else {
            $value = "'$value'";
        }
        return $value;
    }

    public function buildInsert($table, $params)
    {
        $query = "INSERT INTO " . self::quoteName($table) . " (";

        $fields = [];
        foreach ($params as $key => &$value) {
            $fields[] = $this->quoteName($key);
            $value = $this->quoteValue($value);
        }

        $query .= implode(', ', $fields);
        $query .= ") VALUES (";
        $query .= implode(", ", $params);
        $query .= ")";

        return $query;
    }

    public function insert($table, $params)
    {
        $result = $this->query(
            $this->buildInsert($table, $params)
        );
        return $result;
    }

    public function insertOrDie($table, $params, $message = '')
    {
        $insert = $this->buildInsert($table, $params);
        $res = $this->query($insert);
        if (!$res) {
            $message = sprintf(
                __('%1$s - Error during the database query: %2$s - Error is %3$s'),
                $message,
                $insert,
                $this->error()
            );
            if (function_exists('isCommandLine') && isCommandLine()) {
                throw new \RuntimeException($message);
            } else {
                echo $message . "\n";
                die(1);
            }
        }
        return $res;
    }

    public function buildUpdate($table, $params, $clauses, array $joins = [])
    {
        if (!isset($clauses['WHERE'])) {
            $clauses  = ['WHERE' => $clauses];
        } else {
            $known_clauses = ['WHERE', 'ORDER', 'LIMIT', 'START'];
            foreach (array_keys($clauses) as $key) {
                if (!in_array($key, $known_clauses)) {
                    throw new \RuntimeException(
                        str_replace(
                            '%clause',
                            $key,
                            'Trying to use an unknonw clause (%clause) building update query!'
                        )
                    );
                }
            }
        }

        if (!count($clauses['WHERE'])) {
            throw new \RuntimeException('Cannot run an UPDATE query without WHERE clause!');
        }

        $query  = "UPDATE " . self::quoteName($table);

    $it = new DBmysqlIterator($this->parentFacade);
        $query .= $it->analyseJoins($joins);

        $query .= " SET ";
        foreach ($params as $field => $value) {
            $query .= self::quoteName($field) . " = " . $this->quoteValue($value) . ", ";
        }
        $query = rtrim($query, ', ');

        $query .= " WHERE " . $it->analyseCrit($clauses['WHERE']);

        if (isset($clauses['ORDER']) && !empty($clauses['ORDER'])) {
            $query .= $it->handleOrderClause($clauses['ORDER']);
        }

        if (isset($clauses['LIMIT']) && !empty($clauses['LIMIT'])) {
            $offset = (isset($clauses['START']) && !empty($clauses['START'])) ? $clauses['START'] : null;
            $query .= $it->handleLimits($clauses['LIMIT'], $offset);
        }

        return $query;
    }

    public function update($table, $params, $where, array $joins = [])
    {
        $query = $this->buildUpdate($table, $params, $where, $joins);
        $result = $this->query($query);
        return $result;
    }

    public function updateOrDie($table, $params, $where, $message = '', array $joins = [])
    {
        $update = $this->buildUpdate($table, $params, $where, $joins);
        $res = $this->query($update);
        if (!$res) {
            $message = sprintf(
                __('%1$s - Error during the database query: %2$s - Error is %3$s'),
                $message,
                $update,
                $this->error()
            );
            if (function_exists('isCommandLine') && isCommandLine()) {
                throw new \RuntimeException($message);
            } else {
                echo $message . "\n";
                die(1);
            }
        }
        return $res;
    }

    public function updateOrInsert($table, $params, $where, $onlyone = true)
    {
        $req = $this->request($table, $where);
        $data = array_merge($where, $params);
        if ($req->count() == 0) {
            return $this->insertOrDie($table, $data, 'Unable to create new element or update existing one');
        } elseif ($req->count() == 1 || !$onlyone) {
            return $this->updateOrDie($table, $data, $where, 'Unable to create new element or update existing one');
        } else {
            Toolbox::logWarning('Update would change too many rows!');
            return false;
        }
    }

    public function buildDelete($table, $where, array $joins = [])
    {
        if (!count($where)) {
            throw new \RuntimeException('Cannot run an DELETE query without WHERE clause!');
        }

        $query  = "DELETE " . self::quoteName($table) . " FROM " . self::quoteName($table);

    $it = new DBmysqlIterator($this->parentFacade);
        $query .= $it->analyseJoins($joins);
        $query .= " WHERE " . $it->analyseCrit($where);

        return $query;
    }

    public function delete($table, $where, array $joins = [])
    {
        $query = $this->buildDelete($table, $where, $joins);
        $result = $this->query($query);
        return $result;
    }

    public function deleteOrDie($table, $where, $message = '', array $joins = [])
    {
        $update = $this->buildDelete($table, $where, $joins);
        $res = $this->query($update);
        if (!$res) {
            $message = sprintf(
                __('%1$s - Error during the database query: %2$s - Error is %3$s'),
                $message,
                $update,
                $this->error()
            );
            if (function_exists('isCommandLine') && isCommandLine()) {
                throw new \RuntimeException($message);
            } else {
                echo $message . "\n";
                die(1);
            }
        }
        return $res;
    }

    public function getTableSchema($table, $structure = null)
    {
        if ($structure === null) {
            $structure = $this->query("SHOW CREATE TABLE $table")->fetch_row();
            $structure = $structure[1];
        }

        $index = preg_grep(
            "/^\s\s+?KEY/",
            array_map(
                function ($idx) {
                    return rtrim($idx, ',');
                },
                explode("\n", $structure)
            )
        );
        $structure = preg_replace(
            [
              "/\s\s+KEY .*/",
              "/AUTO_INCREMENT=\d+ /"
            ],
            "",
            $structure
        );
        $structure = preg_replace('/,(\s)?$/m', '', $structure);
        $structure = preg_replace('/ COMMENT \'(.+)\'/', '', $structure);

        $structure = str_replace(
            [
              " COLLATE utf8_unicode_ci",
              " CHARACTER SET utf8",
              ', ',
            ],
            [
              '',
              '',
              ',',
            ],
            trim($structure)
        );

        $structure = preg_replace(
            '/\) ENGINE.*$/',
            '',
            $structure
        );

        $structure = preg_replace(
            '/ CURRENT_TIMESTAMP\(\)/i',
            ' CURRENT_TIMESTAMP',
            $structure
        );

        $defaults = [];
        preg_match_all(
            '/^.+ (longblob|text|longtext) .+$/m',
            $structure,
            $defaults
        );
        if (count($defaults[0])) {
            foreach ($defaults[0] as $line) {
                $structure = str_replace(
                    $line,
                    str_replace(' DEFAULT NULL', '', $line),
                    $structure
                );
            }
        }

        $structure = preg_replace("/(DEFAULT) ([-|+]?\d+)(\.\d+)?/", "$1 '$2$3'", $structure);
        $structure = preg_replace('/(BIGINT)\(\d+\)/i', '$1', $structure);
        $structure = preg_replace('/(TINYINT) /i', '$1(4) ', $structure);

        return [
           'schema' => strtolower($structure),
           'index'  => $index
        ];
    }

    public function getVersion()
    {
        $req = $this->request('SELECT version()')->next();
        $raw = $req['version()'];
        return $raw;
    }

    public function beginTransaction()
    {
        $this->in_transaction = true;
        return $this->dbh->begin_transaction();
    }

    public function commit()
    {
        $this->in_transaction = false;
        return $this->dbh->commit();
    }

    public function rollBack()
    {
        $this->in_transaction = false;
        return $this->dbh->rollback();
    }

    public function inTransaction()
    {
        return $this->in_transaction;
    }

    public function areTimezonesAvailable(string &$msg = '')
    {
        $cache = Config::getCache('cache_db');

        if ($cache->has('are_timezones_available')) {
            return $cache->get('are_timezones_available');
        }
        $cache->set('are_timezones_available', false, defined('DAY_TIMESTAMP') ? DAY_TIMESTAMP : 86400);

        $mysql_db_res = $this->request('SHOW DATABASES LIKE ' . $this->quoteValue('mysql'));
        if ($mysql_db_res->count() === 0) {
            $msg = __('Access to timezone database (mysql) is not allowed.');
            return false;
        }

        $tz_table_res = $this->request(
            'SHOW TABLES FROM '
            . $this->quoteName('mysql')
            . ' LIKE '
            . $this->quoteValue('time_zone_name')
        );
        if ($tz_table_res->count() === 0) {
            $msg = __('Access to timezone table (mysql.time_zone_name) is not allowed.');
            return false;
        }

        $criteria = [
           'COUNT'  => 'cpt',
           'FROM'   => 'mysql.time_zone_name',
        ];
        $iterator = $this->request($criteria);
        $result = $iterator->next();
        if ($result['cpt'] == 0) {
            $msg = __('Timezones seems not loaded, see https://glpi-install.readthedocs.io/en/latest/timezones.html.');
            return false;
        }

        $cache->set('are_timezones_available', true);
        return true;
    }

    public function setTimezone($timezone)
    {
        if ($this->areTimezonesAvailable()) {
            date_default_timezone_set($timezone);
            $this->dbh->query("SET SESSION time_zone = '$timezone'");
            $_SESSION['glpi_currenttime'] = date("Y-m-d H:i:s");
        }
        return $this;
    }

    public function getTimezones()
    {
        $list = [];

        $from_php = \DateTimeZone::listIdentifiers();
        $now = new \DateTime();

        try {
            $iterator = $this->request([
               'SELECT' => 'Name',
               'FROM'   => 'mysql.time_zone_name',
               'WHERE'  => ['Name' => $from_php]
            ]);
            while ($from_mysql = $iterator->next()) {
                $now->setTimezone(new \DateTimeZone($from_mysql['Name']));
                $list[$from_mysql['Name']] = $from_mysql['Name'] . $now->format(" (T P)");
            }
        } catch (\Exception $e) {
        }

        return $list;
    }

    public function notTzMigrated()
    {
        global $DB;

        $result = $DB->request([
            'COUNT'       => 'cpt',
            'FROM'        => 'information_schema.columns',
            'WHERE'       => [
               'information_schema.columns.table_schema' => $DB->dbdefault,
               'information_schema.columns.table_name'   => ['LIKE', 'glpi\_%'],
               'information_schema.columns.data_type'    => ['datetime']
            ]
        ])->next();
        return (int)$result['cpt'];
    }

    public function getSignedKeysColumns()
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
                              $this->quoteName('information_schema.columns.table_schema')
                          ),
                       ]
                    ],
                 ]
              ]
           ],
           'WHERE'      => [
            'information_schema.tables.table_schema'  => $this->dbdefault,
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
           'ORDER'      => ['TABLE_NAME']
        ];
        foreach (self::ALLOWED_SIGNED_KEYS as $allowed_signed_key) {
            list($excluded_table, $excluded_field) = explode('.', $allowed_signed_key);
            $excluded_fkey = getForeignKeyFieldForTable($excluded_table);
            $query['WHERE'][] = [
               [
                  'NOT' => [
                     'information_schema.tables.table_name'   => $excluded_table,
                     'information_schema.columns.column_name' => $excluded_field
                  ]
               ],
               ['NOT' => ['information_schema.columns.column_name' => $excluded_fkey]],
               ['NOT' => ['information_schema.columns.column_name' => ['LIKE', str_replace('_', '\\_', $excluded_fkey . '_%')]]],
            ];
        }

        $iterator = $this->request($query);

        return $iterator;
    }

    public function getForeignKeysContraints()
    {
        $query = [
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
              'referenced_table_schema' => $this->dbdefault,
              'referenced_table_name'   => ['LIKE', 'glpi\_%'],
           ],
           'ORDER'  => ['TABLE_NAME']
        ];

        $iterator = $this->request($query);

        return $iterator;
    }

    public function clearSchemaCache()
    {
        $this->table_cache = [];
        $this->field_cache = [];
    }

    public function quote($value, int $type = 2)
    {
        return "'" . $this->escape($value) . "'";
    }

    public static function getQuoteNameChar(): string
    {
        return '';
    }

    public static function isNameQuoted($value): bool
    {
        $quote = static::getQuoteNameChar();
        return is_string($value) && trim($value, $quote) != $value;
    }

    public function removeSqlComments($output)
    {
        $lines = explode("\n", $output);
        $output = "";

        $linecount = count($lines);

        $in_comment = false;
        for ($i = 0; $i < $linecount; $i++) {
            if (preg_match("#/\*#", $lines[$i])) {
                $in_comment = true;
            }

            if (!$in_comment) {
                $output .= $lines[$i] . "\n";
            }

            if (preg_match("#\*/$#", preg_quote($lines[$i]))) {
                $in_comment = false;
            }
        }

        unset($lines);
        return trim($output);
    }

    public function removeSqlRemarks($sql)
    {
        $lines = explode("\n", $sql);
        $sql = "";

        $linecount = count($lines);
        $output = "";

        for ($i = 0; $i < $linecount; $i++) {
            if (($i != ($linecount - 1)) || (strlen($lines[$i]) > 0)) {
                if (isset($lines[$i][0])) {
                    if ($lines[$i][0] != "#" && substr($lines[$i], 0, 2) != "--") {
                        $output .= $lines[$i] . "\n";
                    } else {
                        $output .= "\n";
                    }
                }
                $lines[$i] = "";
            }
        }
        return trim($this->removeSqlComments($output));
    }
}
