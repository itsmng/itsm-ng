<?php

namespace itsmng\Database\Runtime;

use Config;
use DBmysqlIterator;
use DBmysqlResult;
use DBmysqlStatement;
use Glpi\Application\ErrorHandler;
use GlpitestSQLError;
use Html;
use QueryExpression;
use QueryParam;
use QuerySubQuery;
use Session;
use Timer;
use Toolbox;
use itsmng\Database\Runtime\Platform\DatabasePlatformInterface;
use itsmng\Database\Runtime\Platform\PlatformResolver;

/**
 *  Database class for Mysql
**/
class LegacyDatabase implements DatabaseInterface
{
    private const DEFAULT_DBTYPE = 'mysql';

    /**
     * List of keys that are allowed to use signed integers.
     *
     * Elements contained in this list have to be fixed before being able to globally use foreign key contraints.
     *
     * @var array
     */
    private const ALLOWED_SIGNED_KEYS = [
       // FIXME Entity preference `glpi_entities.calendars_id` inherit/never strategy should be stored in another field.
       'glpi_calendars.id',
       // FIXME Entity preference `glpi_entities.changetemplates_id` inherit/never strategy should be stored in another field.
       'glpi_changetemplates.id',
       // FIXME Entity preference `glpi_entities.contracts_id_default` inherit/never strategy should be stored in another field.
       'glpi_contracts.id',
       // FIXME root entity uses "-1" value for its parent (`glpi_entities.entities_id`), should be null
       // FIXME some entities_id foreign keys are using "-1" as default value, should be null
       // FIXME Entity preference `glpi_entities.entities_id_software` inherit/never strategy should be stored in another field.
       'glpi_entities.id',
       // FIXME Entity preference `glpi_entities.problemtemplates_id` inherit/never strategy should be stored in another field.
       'glpi_problemtemplates.id',
       // FIXME Entity preference `glpi_entities.tickettemplates_id` inherit/never strategy should be stored in another field.
       'glpi_tickettemplates.id',
       // FIXME Entity preference `glpi_entities.transfers_id` inherit/never strategy should be stored in another field.
       'glpi_transfers.id',
    ];

    //! Database Host - string or Array of string (round robin)
    public $dbhost             = "";
    //! Database User
    public $dbuser             = "";
    //! Database Password
    public $dbpassword         = "";
    //! Default Database
    public $dbdefault          = "";
    //! Database type
    public $dbtype             = self::DEFAULT_DBTYPE;
    //! Database Handler
    protected $dbh;
    //! Last inserted identifier tracked by the abstraction layer
    protected $last_insert_id  = null;
    //! Database Error
    public $error              = 0;

    // Slave management
    public $slave              = false;
    protected $in_transaction;

    /**
     * Defines if connection must use SSL.
     *
     * @var boolean
     */
    public $dbssl              = false;

    /**
     * The path name to the key file (used in case of SSL connection).
     *
     * @see mysqli::ssl_set()
     * @var string|null
     */
    public $dbsslkey           = null;

    /**
     * The path name to the certificate file (used in case of SSL connection).
     *
     * @see mysqli::ssl_set()
     * @var string|null
     */
    public $dbsslcert          = null;

    /**
     * The path name to the certificate authority file (used in case of SSL connection).
     *
     * @see mysqli::ssl_set()
     * @var string|null
     */
    public $dbsslca            = null;

    /**
     * The pathname to a directory that contains trusted SSL CA certificates in PEM format
     * (used in case of SSL connection).
     *
     * @see mysqli::ssl_set()
     * @var string|null
     */
    public $dbsslcapath        = null;

    /**
     * A list of allowable ciphers to use for SSL encryption (used in case of SSL connection).
     *
     * @see mysqli::ssl_set()
     * @var string|null
     */
    public $dbsslcacipher      = null;


    /** Is it a first connection ?
     * Indicates if the first connection attempt is successful or not
     * if first attempt fail -> display a warning which indicates that glpi is in readonly
    **/
    public $first_connection   = true;
    // Is connected to the DB ?
    public $connected          = false;

    //to calculate execution time
    public $execution_time          = false;

    protected $cache_disabled = false;

    /**
     * Cached list fo tables.
     *
     * @var array
     * @see self::tableExists()
     */
    protected $table_cache = [];

    /**
     * Cached list of fields.
     *
     * @var array
     * @see self::listFields()
     */
    protected $field_cache = [];

    protected $last_affected_rows = -1;

    protected int|string $last_error_code = 0;

    protected string $last_error_message = '';

    protected static string $default_dbtype = self::DEFAULT_DBTYPE;

    private ?array $logical_boolean_columns = null;

    protected int $savepoint_counter = 0;

    private ?DatabasePlatformInterface $runtime_platform = null;

    /**
     * Constructor / Connect to the MySQL Database
     *
     * @param integer $choice host number (default NULL)
     *
     * @return void
     */
    public function __construct($choice = null)
    {
        if (is_array($choice)) {
            foreach ($choice as $property => $value) {
                $this->$property = $value;
            }
            $choice = $choice['choice'] ?? null;
        }

        $this->rememberActiveDialect();
        $this->connect($choice);
    }

    /**
     * Connect using current database settings
     * Use dbhost, dbuser, dbpassword and dbdefault
     *
     * @param integer $choice host number (default NULL)
     *
     * @return void
     */
    public function connect($choice = null)
    {
        $this->connected = false;
        $this->dbh = null;
        $this->in_transaction = false;
        $this->rememberActiveDialect();

        if (is_array($this->dbhost)) {
            // Round robin choice
            $i    = (isset($choice) ? $choice : mt_rand(0, count($this->dbhost) - 1));
            $host = $this->dbhost[$i];
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
            } elseif (count($host_parts) === 2 && $host_parts[1] !== '') {
                if ($host_parts[0] !== '') {
                    $dsn_parts[] = 'host=' . $host_parts[0];
                }
                $dsn_parts[] = 'unix_socket=' . $host_parts[1];
            } else {
                $dsn_parts[] = 'host=' . $host_value;
            }
        }

        if (!empty($this->dbdefault)) {
            $dsn_parts[] = 'dbname=' . $this->dbdefault;
        }

        if (isset($this->dbenc)) {
            Toolbox::deprecated('Usage of alternative DB connection encoding (`DB::$dbenc` property) is deprecated.');
        }
        $dbenc = isset($this->dbenc) ? $this->dbenc : 'utf8';
        $dsn_parts[] = 'charset=' . $dbenc;

        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
            \PDO::ATTR_STRINGIFY_FETCHES  => false,
        ];

        if ($this->dbssl) {
            if ($this->dbsslkey !== null && defined('PDO::MYSQL_ATTR_SSL_KEY')) {
                $options[\PDO::MYSQL_ATTR_SSL_KEY] = $this->dbsslkey;
            }
            if ($this->dbsslcert !== null && defined('PDO::MYSQL_ATTR_SSL_CERT')) {
                $options[\PDO::MYSQL_ATTR_SSL_CERT] = $this->dbsslcert;
            }
            if ($this->dbsslca !== null && defined('PDO::MYSQL_ATTR_SSL_CA')) {
                $options[\PDO::MYSQL_ATTR_SSL_CA] = $this->dbsslca;
            }
            if ($this->dbsslcapath !== null && defined('PDO::MYSQL_ATTR_SSL_CAPATH')) {
                $options[\PDO::MYSQL_ATTR_SSL_CAPATH] = $this->dbsslcapath;
            }
            if ($this->dbsslcacipher !== null && defined('PDO::MYSQL_ATTR_SSL_CIPHER')) {
                $options[\PDO::MYSQL_ATTR_SSL_CIPHER] = $this->dbsslcacipher;
            }
        }

        try {
            $this->dbh = new \PDO(
                'mysql:' . implode(';', $dsn_parts),
                $this->dbuser,
                rawurldecode((string) $this->dbpassword),
                $options
            );
        } catch (\PDOException $exception) {
            $this->connected = false;
            $this->error     = 1;
            $this->registerError($exception->getCode(), $exception->getMessage());
            return;
        }

        if ($dbenc === 'utf8') {
            $this->dbh->exec("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'");
        }

        if (GLPI_FORCE_EMPTY_SQL_MODE) {
            // Keep legacy non-strict behavior while preserving explicit id=0 inserts
            // used by core fixtures such as the root entity.
            $this->dbh->exec("SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");
        }

        $this->connected = true;
        $this->error = 0;
        $this->clearLastError();
        $this->rememberActiveDialect();

        $this->setTimezone($this->guessTimezone());
    }

    protected function rememberActiveDialect(): void
    {
        $this->runtime_platform = null;
        static::$default_dbtype = $this->dbtype ?: self::DEFAULT_DBTYPE;
    }

    public function getDbType(): string
    {
        return $this->dbtype ?: self::DEFAULT_DBTYPE;
    }

    protected function getPlatform(): DatabasePlatformInterface
    {
        return $this->runtime_platform ??= PlatformResolver::resolve($this->getDbType());
    }

    public function getConnectionHandle()
    {
        return $this->dbh;
    }

    public function isCacheDisabled(): bool
    {
        return $this->cache_disabled;
    }

    public function hasCachedFieldList(string $table): bool
    {
        return array_key_exists($table, $this->field_cache);
    }

    public function getCachedFieldList(string $table): array
    {
        return $this->field_cache[$table];
    }

    public function setCachedFieldList(string $table, array $fields): void
    {
        $this->field_cache[$table] = $fields;
    }

    public static function getAllowedSignedKeys(): array
    {
        return self::ALLOWED_SIGNED_KEYS;
    }

    protected function prepareQueryString(string $query): string
    {
        return $query;
    }

    protected function executeQuery(string $query)
    {
        $savepoint = $this->beginTransactionalSavepoint();

        try {
            $result = $this->dbh->query($query);
            $this->releaseTransactionalSavepoint($savepoint);
            $this->last_affected_rows = $result instanceof \PDOStatement ? $result->rowCount() : -1;
            if ($result instanceof \PDOStatement && $result->columnCount() === 0) {
                $result->closeCursor();
                return true;
            }

            return $result;
        } catch (\Throwable $throwable) {
            $this->rollBackTransactionalSavepoint($savepoint);
            throw $throwable;
        }
    }

    protected function executePreparedQuery(string $query)
    {
        return $this->dbh->prepare($query);
    }

    protected function wrapQueryResult($result)
    {
        if ($result instanceof DBmysqlResult) {
            return $result;
        }

        if ($result instanceof \mysqli_result || $result instanceof \PDOStatement) {
            return new DBmysqlResult($result, $this);
        }

        return $result;
    }

    protected function wrapPreparedStatement($statement)
    {
        if ($statement instanceof DBmysqlStatement) {
            return $statement;
        }

        if ($statement instanceof \mysqli_stmt || $statement instanceof \PDOStatement) {
            return new DBmysqlStatement($statement);
        }

        return $statement;
    }

    protected function getDriverErrorCode(?\Throwable $exception = null)
    {
        if ($exception instanceof \PDOException) {
            return $exception->getCode();
        }

        if ($exception instanceof \mysqli_sql_exception) {
            return $exception->getCode();
        }

        if ($this->dbh instanceof \PDO) {
            return $this->dbh->errorInfo()[1] ?? $this->dbh->errorInfo()[0] ?? 0;
        }

        return $this->dbh instanceof \mysqli ? $this->dbh->errno : 0;
    }

    protected function getDriverErrorMessage(?\Throwable $exception = null): string
    {
        if ($exception !== null) {
            return $exception->getMessage();
        }

        if ($this->dbh instanceof \PDO) {
            return (string) ($this->dbh->errorInfo()[2] ?? '');
        }

        return $this->dbh instanceof \mysqli ? $this->dbh->error : '';
    }

    protected function registerError(int|string $code, string $message): void
    {
        $this->last_error_code = $code;
        $this->last_error_message = $message;
    }

    protected function clearLastError(): void
    {
        $this->registerError(0, '');
    }

    protected function handleQueryFailure(string $query, ?\Throwable $exception = null): void
    {
        global $CFG_GLPI, $DEBUG_SQL, $GLPI, $SQL_TOTAL_REQUEST;

        $code = $this->getDriverErrorCode($exception);
        $message = $this->getDriverErrorMessage($exception);
        $this->registerError($code, $message);

        $label = $this->dbtype === 'pgsql' ? 'PostgreSQL' : 'MySQL';
        $error = "  *** {$label} query error:\n  SQL: " . $query . "\n  Error: " . $message . "\n";
        $error .= Toolbox::backtrace(false, 'DBmysql->query()', ['Toolbox::backtrace()']);

        Toolbox::logSqlError($error);

        $error_handler = $GLPI->getErrorHandler();
        if ($error_handler instanceof ErrorHandler) {
            $error_handler->handleSqlError((int) $code, $message, $query);
        }

        $is_debug = isset($_SESSION['glpi_use_mode']) && ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE);
        if (($is_debug || isAPI()) && $CFG_GLPI["debug_sql"]) {
            $DEBUG_SQL["errors"][$SQL_TOTAL_REQUEST] = $this->error();
        }
    }

    protected function beginTransactionalSavepoint(): ?string
    {
        if (
            $this->dbtype !== 'pgsql'
            || !$this->dbh instanceof \PDO
            || !$this->dbh->inTransaction()
        ) {
            return null;
        }

        $savepoint = 'glpi_sp_' . (++$this->savepoint_counter);
        try {
            $this->dbh->exec('SAVEPOINT ' . $savepoint);
        } catch (\Throwable $throwable) {
            return null;
        }

        return $savepoint;
    }

    protected function releaseTransactionalSavepoint(?string $savepoint): void
    {
        if ($savepoint === null || !$this->dbh instanceof \PDO) {
            return;
        }

        try {
            $this->dbh->exec('RELEASE SAVEPOINT ' . $savepoint);
        } catch (\Throwable $throwable) {
            return;
        }
    }

    protected function rollBackTransactionalSavepoint(?string $savepoint): void
    {
        if ($savepoint === null || !$this->dbh instanceof \PDO) {
            return;
        }

        try {
            $this->dbh->exec('ROLLBACK TO SAVEPOINT ' . $savepoint);
            $this->dbh->exec('RELEASE SAVEPOINT ' . $savepoint);
        } catch (\Throwable $throwable) {
            return;
        }
    }

    /**
     * Guess timezone
     *
     * Will  check for an existing loaded timezone from user,
     * then will check in preferences and finally will fallback to system one.
     *
     * @return string
     *
     * @since 9.5.0
     */
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

    /**
     * Escapes special characters in a string for use in an SQL statement,
     * taking into account the current charset of the connection
     *
     * @since 0.84
     *
     * @param string $string String to escape
     *
     * @return string escaped string
     */
    public function escape($string)
    {
        if ($this->dbh instanceof \mysqli) {
            return $this->dbh->real_escape_string($string ?? '');
        }

        return $this->escapeWithMysqlCompatibilityMap((string) ($string ?? ''));
    }

    protected function escapeWithMysqlCompatibilityMap(string $string): string
    {
        return strtr(
            $string,
            [
                "\\" => "\\\\",
                "\0" => "\\0",
                "\n" => "\\n",
                "\r" => "\\r",
                "'"  => "\\'",
                '"'  => '\\"',
                "\x1a" => "\\Z",
            ]
        );
    }

    public function normalizeCompatibleFetchedRow(array $row, array $field_names = [], array $field_meta = []): array
    {
        if (!$this->shouldNormalizeFetchedValues()) {
            return $row;
        }

        foreach ($field_names as $index => $name) {
            if (!array_key_exists($name, $row)) {
                continue;
            }

            $meta = $field_meta[$index] ?? [];
            $native_type = strtolower((string) ($meta['native_type'] ?? ''));
            $pgsql_type = strtolower((string) ($meta['pgsql:oid_name'] ?? ''));
            $table_name = is_string($meta['table'] ?? null)
                ? $meta['table']
                : (is_string($meta['table_name'] ?? null) ? $meta['table_name'] : null);
            if (
                in_array($native_type, ['bool', 'boolean'], true)
                || in_array($pgsql_type, ['bool', 'boolean'], true)
                || $this->isLogicalBooleanField((string) $name, $table_name)
            ) {
                $row[$name] = $this->normalizeLogicalBooleanValue($row[$name]);
                continue;
            }

            $row[$name] = $this->normalizeCompatibleFetchedValue($row[$name], $meta);
        }

        return $row;
    }

    public function normalizeCompatibleFetchedValue($value, array $meta = [])
    {
        if (!$this->shouldNormalizeFetchedValues()) {
            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        $native_type = strtolower((string) ($meta['native_type'] ?? ''));
        $pgsql_type = strtolower((string) ($meta['pgsql:oid_name'] ?? ''));

        if (in_array($native_type, ['bpchar', 'char'], true) || $pgsql_type === 'bpchar') {
            return rtrim($value, ' ');
        }

        if (
            $this->dbtype === 'pgsql'
            && (
                in_array($native_type, ['timestamp', 'timestamptz'], true)
                || in_array($pgsql_type, ['timestamp', 'timestamptz'], true)
            )
        ) {
            return preg_replace('/(?:\.\d+)?(?:[+-]\d{2}(?::?\d{2})?)$/', '', $value) ?? $value;
        }

        return $value;
    }

    protected function shouldNormalizeFetchedValues(): bool
    {
        return $this->dbtype === 'pgsql' || $this->getLogicalBooleanColumns() !== [];
    }

    protected function getLogicalBooleanColumns(): array
    {
        if (is_array($this->logical_boolean_columns)) {
            return $this->logical_boolean_columns;
        }

        $this->logical_boolean_columns = [];

        foreach (\itsmng\Database\Schema\CoreSchema::definition()['tables'] ?? [] as $table) {
            $table_name = strtolower((string) ($table['name'] ?? ''));
            if ($table_name === '') {
                continue;
            }

            foreach ($table['columns'] ?? [] as $column) {
                if (($column['type'] ?? null) !== 'boolean') {
                    continue;
                }

                $column_name = strtolower((string) ($column['name'] ?? ''));
                if ($column_name !== '') {
                    $this->logical_boolean_columns[$table_name][$column_name] = true;
                }
            }
        }

        return $this->logical_boolean_columns;
    }

    public function isLogicalBooleanField(string $field_name, ?string $table_name = null): bool
    {
        $normalized_field = strtolower((string) preg_replace('/^.*\./', '', $field_name));
        $normalized_table = $table_name !== null
            ? strtolower(trim($table_name, '`"'))
            : null;

        if ($normalized_table === null && str_contains($field_name, '.')) {
            [$resolved_table] = explode('.', strtolower((string) $field_name), 2);
            $normalized_table = trim($resolved_table, '`"');
        }

        if ($normalized_table === null || $normalized_field === '') {
            return false;
        }

        return isset($this->getLogicalBooleanColumns()[$normalized_table][$normalized_field]);
    }

    protected function normalizeLogicalBooleanValue($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return (int) $value;
        }

        if (is_int($value) || is_float($value)) {
            return ((int) $value) === 0 ? 0 : 1;
        }

        if (is_string($value)) {
            return match (strtolower($value)) {
                '1', 't', 'true', 'y', 'yes' => 1,
                '0', 'f', 'false', 'n', 'no' => 0,
                default => $value,
            };
        }

        return $value;
    }

    /**
     * Execute a MySQL query
     *
     * @param string $query Query to execute
     *
     * @var array   $CFG_GLPI
     * @var array   $DEBUG_SQL
     * @var integer $SQL_TOTAL_REQUEST
     *
     * @return mysqli_result|boolean Query result handler
     *
     * @throws GlpitestSQLError
     */
    public function query($query)
    {
        global $CFG_GLPI, $DEBUG_SQL, $GLPI, $SQL_TOTAL_REQUEST;

        $query = $this->prepareQueryString($query);
        $is_debug = isset($_SESSION['glpi_use_mode']) && ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE);
        if ($is_debug && $CFG_GLPI["debug_sql"]) {
            $SQL_TOTAL_REQUEST++;
            $DEBUG_SQL["queries"][$SQL_TOTAL_REQUEST] = $query;
        }
        if ($is_debug && $CFG_GLPI["debug_sql"] || $this->execution_time === true) {
            $TIMER                                    = new Timer();
            $TIMER->start();
        }

        try {
            $res = $this->executeQuery($query);
            if ($res === false) {
                $this->handleQueryFailure($query);
                return false;
            }

            $res = $this->wrapQueryResult($res);
            if ($res instanceof DBmysqlResult) {
                $this->last_affected_rows = $res->num_rows;
            }
            $this->clearLastError();

            if ($is_debug && $CFG_GLPI["debug_sql"]) {
                $TIME                                   = $TIMER->getTime();
                $DEBUG_SQL["times"][$SQL_TOTAL_REQUEST] = $TIME;
                $DEBUG_SQL['rows'][$SQL_TOTAL_REQUEST] = $this->affectedRows();
            }
            if ($this->execution_time === true) {
                $this->execution_time = $TIMER->getTime(0, true);
            }
            return $res;
        } catch (\Throwable $exception) {
            $this->handleQueryFailure($query, $exception);
        }

        return false;
    }

    /**
     * Execute a MySQL query and die
     * (optionnaly with a message) if it fails
     *
     * @since 0.84
     *
     * @param string $query   Query to execute
     * @param string $message Explanation of query (default '')
     *
     * @return mysqli_result Query result handler
     */
    public function queryOrDie($query, $message = '')
    {
        $res = $this->query($query);
        if (!$res) {
            //TRANS: %1$s is the description, %2$s is the query, %3$s is the error message
            $message = sprintf(
                __('%1$s - Error during the database query: %2$s - Error is %3$s'),
                $message,
                $query,
                $this->error()
            );
            if (isCommandLine()) {
                throw new \RuntimeException($message);
            } else {
                echo $message . "\n";
                die(1);
            }
        }
        return $res;
    }

    /**
     * Prepare a MySQL query
     *
     * @param string $query Query to prepare
     *
     * @return mysqli_stmt|boolean statement object or FALSE if an error occurred.
     *
     * @throws GlpitestSQLError
     */
    public function prepare($query)
    {
        global $CFG_GLPI, $DEBUG_SQL, $SQL_TOTAL_REQUEST;

        $query = $this->prepareQueryString($query);

        try {
            $res = $this->executePreparedQuery($query);
        } catch (\Throwable $exception) {
            $this->registerError(
                $this->getDriverErrorCode($exception),
                $this->getDriverErrorMessage($exception)
            );
            $res = false;
        }
        if (!$res) {
            // no translation for error logs
            $error = "  *** " . ($this->dbtype === 'pgsql' ? 'PostgreSQL' : 'MySQL') .
                " prepare error:\n  SQL: " . $query . "\n  Error: " .
                      $this->error() . "\n";
            $error .= Toolbox::backtrace(false, 'DBmysql->prepare()', ['Toolbox::backtrace()']);

            Toolbox::logInFile("sql-errors", $error);
            if (class_exists('GlpitestSQLError')) { // For unit test
                throw new GlpitestSQLError($error);
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
        $this->clearLastError();
        return $this->wrapPreparedStatement($res);
    }

    /**
     * Give result from a sql result
     *
     * @param mysqli_result $result MySQL result handler
     * @param int           $i      Row offset to give
     * @param string        $field  Field to give
     *
     * @return mixed Value of the Row $i and the Field $field of the Mysql $result
     */
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

    /**
     * Number of rows
     *
     * @param mysqli_result $result MySQL result handler
     *
     * @return integer number of rows
     */
    public function numrows($result)
    {
        if (!is_object($result) || !property_exists($result, 'num_rows')) {
            return 0;
        }

        return (int) $result->num_rows;
    }

    /**
     * Fetch array of the next row of a Mysql query
     * Please prefer fetchRow or fetchAssoc
     *
     * @param mysqli_result $result MySQL result handler
     *
     * @return string[]|null array results
     *
     * @deprecated 9.5.0
     */
    public function fetch_array($result)
    {
        Toolbox::deprecated('Use DBmysql::fetchArray()');
        return $this->fetchArray($result);
    }

    /**
     * Fetch array of the next row of a Mysql query
     * Please prefer fetchRow or fetchAssoc
     *
     * @param mysqli_result $result MySQL result handler
     *
     * @return string[]|null array results
     */
    public function fetchArray($result)
    {
        return $result->fetch_array();
    }

    /**
     * Fetch row of the next row of a Mysql query
     *
     * @param mysqli_result $result MySQL result handler
     *
     * @return mixed|null result row
     *
     * @deprecated 9.5.0
     */
    public function fetch_row($result)
    {
        Toolbox::deprecated('Use DBmysql::fetchRow()');
        return $this->fetchRow($result);
    }

    /**
     * Fetch row of the next row of a Mysql query
     *
     * @param mysqli_result $result MySQL result handler
     *
     * @return mixed|null result row
     */
    public function fetchRow($result)
    {
        return $result->fetch_row();
    }

    /**
     * Fetch assoc of the next row of a Mysql query
     *
     * @param mysqli_result $result MySQL result handler
     *
     * @return string[]|null result associative array
     *
     * @deprecated 9.5.0
     */
    public function fetch_assoc($result)
    {
        Toolbox::deprecated('Use DBmysql::fetchAssoc()');
        return $this->fetchAssoc($result);
    }

    /**
     * Fetch assoc of the next row of a Mysql query
     *
     * @param mysqli_result $result MySQL result handler
     *
     * @return string[]|null result associative array
     */
    public function fetchAssoc($result)
    {
        if (!is_object($result) || !method_exists($result, 'fetch_assoc')) {
            return null;
        }

        return $result->fetch_assoc();
    }

    /**
     * Fetch object of the next row of an SQL query
     *
     * @param mysqli_result $result MySQL result handler
     *
     * @return object|null
     */
    public function fetch_object($result)
    {
        Toolbox::deprecated('Use DBmysql::fetchObject()');
        return $this->fetchObject();
    }

    /**
     * Fetch object of the next row of an SQL query
     *
     * @param mysqli_result $result MySQL result handler
     *
     * @return object|null
     */
    public function fetchObject($result)
    {
        return $result->fetch_object();
    }

    /**
     * Move current pointer of a Mysql result to the specific row
     *
     * @deprecated 9.5.0
     *
     * @param mysqli_result $result MySQL result handler
     * @param integer       $num    Row to move current pointer
     *
     * @return boolean
     */
    public function data_seek($result, $num)
    {
        Toolbox::deprecated('Use DBmysql::dataSeek()');
        return $this->dataSeek($result, $num);
    }

    /**
     * Move current pointer of a Mysql result to the specific row
     *
     * @param mysqli_result $result MySQL result handler
     * @param integer       $num    Row to move current pointer
     *
     * @return boolean
     */
    public function dataSeek($result, $num)
    {
        return $result->data_seek($num);
    }


    /**
     * Give ID of the last inserted item by Mysql
     *
     * @return mixed
     *
     * @deprecated 9.5.0
     */
    public function insert_id()
    {
        Toolbox::deprecated('Use DBmysql::insertId()');
        return $this->insertId();
    }

    /**
     * Give ID of the last inserted item by Mysql
     *
     * @return mixed
     */
    public function insertId()
    {
        if ($this->last_insert_id !== null) {
            return ctype_digit((string) $this->last_insert_id)
                ? (int) $this->last_insert_id
                : $this->last_insert_id;
        }

        if ($this->dbh instanceof \PDO) {
            $insert_id = $this->dbh->lastInsertId();
            return ctype_digit((string) $insert_id) ? (int) $insert_id : $insert_id;
        }

        if ($this->dbh instanceof \mysqli) {
            return $this->dbh->insert_id;
        }

        return null;
    }

    /**
     * Give number of fields of a Mysql result
     *
     * @deprecated 9.5.0
     *
     * @param mysqli_result $result MySQL result handler
     *
     * @return int number of fields
     */
    public function num_fields($result)
    {
        Toolbox::deprecated('Use DBmysql::numFields()');
        return $this->numFields($result);
    }

    /**
     * Give number of fields of a Mysql result
     *
     * @param mysqli_result $result MySQL result handler
     *
     * @return int number of fields
     */
    public function numFields($result)
    {
        return $result->field_count;
    }


    /**
     * Give name of a field of a Mysql result
     *
     * @param mysqli_result $result MySQL result handler
     * @param integer       $nb     ID of the field
     *
     * @return string name of the field
     *
     * @deprecated 9.5.0
     */
    public function field_name($result, $nb)
    {
        Toolbox::deprecated('Use DBmysql::fieldName()');
        return $this->fieldName($result, $nb);
    }

    /**
     * Give name of a field of a Mysql result
     *
     * @param mysqli_result $result MySQL result handler
     * @param integer       $nb     ID of the field
     *
     * @return string name of the field
     *
     * @deprecated 9.5.0
     */
    public function fieldName($result, $nb)
    {
        $finfo = $result->fetch_fields();
        return $finfo[$nb]->name;
    }


    /**
     * List tables in database
     *
     * @param string $table Table name condition (glpi_% as default to retrieve only glpi tables)
     * @param array  $where Where clause to append
     *
     * @return DBmysqlIterator
     */
    public function listTables($table = 'glpi\_%', array $where = [])
    {
        return $this->getPlatform()->listTables($this, $table, $where);
    }

    /**
     * Returns tables using "MyIsam" engine.
     *
     * @return DBmysqlIterator
     */
    public function getMyIsamTables(): DBmysqlIterator
    {
        return $this->getPlatform()->getMyIsamTables($this);
    }

    /**
     * List fields of a table
     *
     * @param string  $table    Table name condition
     * @param boolean $usecache If use field list cache (default true)
     *
     * @return mixed list of fields
     *
     * @deprecated 9.5.0
     */
    public function list_fields($table, $usecache = true)
    {
        Toolbox::deprecated('Use DBmysql::listFields()');
        return $this->listFields($table, $usecache);
    }

    /**
     * List fields of a table
     *
     * @param string  $table    Table name condition
     * @param boolean $usecache If use field list cache (default true)
     *
     * @return mixed list of fields
     */
    public function listFields($table, $usecache = true)
    {
        return $this->getPlatform()->listFields($this, $table, $usecache);
    }

    public function listIndexes($table)
    {
        return $this->getPlatform()->listIndexes($this, $table);
    }

    /**
     * Get field of a table
     *
     * @param string  $table
     * @param string  $field
     * @param boolean $usecache
     *
     * @return array|null Field characteristics
     */
    public function getField(string $table, string $field, $usecache = true): ?array
    {

        $fields = $this->listFields($table, $usecache);
        if (!is_array($fields)) {
            return null;
        }

        $field_definition = $fields[$field] ?? null;
        return is_array($field_definition) ? $field_definition : null;
    }

    /**
     * Get number of affected rows in previous MySQL operation
     *
     * @return int number of affected rows on success, and -1 if the last query failed.
     *
     * @deprecated 9.5.0
     */
    public function affected_rows()
    {
        Toolbox::deprecated('Use DBmysql::affectedRows()');
        return $this->affectedRows();
    }

    /**
     * Get number of affected rows in previous MySQL operation
     *
     * @return int number of affected rows on success, and -1 if the last query failed.
     */
    public function affectedRows()
    {
        return $this->last_affected_rows;
    }


    /**
     * Free result memory
     *
     * @param mysqli_result $result MySQL result handler
     *
     * @return boolean
     *
     * @deprecated 9.5.0
     */
    public function free_result($result)
    {
        Toolbox::deprecated('Use DBmysql::freeResult()');
        return $this->freeResult($result);
    }

    /**
     * Free result memory
     *
     * @param mysqli_result $result MySQL result handler
     *
     * @return boolean
     */
    public function freeResult($result)
    {
        return $result->free();
    }

    /**
     * Returns the numerical value of the error message from previous MySQL operation
     *
     * @return int error number from the last MySQL function, or 0 (zero) if no error occurred.
     */
    public function errno()
    {
        return (int) $this->last_error_code;
    }

    /**
     * Returns the text of the error message from previous MySQL operation
     *
     * @return string error text from the last MySQL function, or '' (empty string) if no error occurred.
     */
    public function error()
    {
        return $this->last_error_message;
    }

    /**
     * Close MySQL connection
     *
     * @return boolean TRUE on success or FALSE on failure.
     */
    public function close()
    {
        if (!$this->connected) {
            return false;
        }

        if ($this->dbh instanceof \PDO) {
            $this->dbh = null;
            $this->connected = false;
            $this->in_transaction = false;
            return true;
        }

        if ($this->dbh instanceof \mysqli) {
            $result = $this->dbh->close();
            if ($result) {
                $this->connected = false;
            }
            return $result;
        }

        return false;
    }

    /**
     * is a slave database ?
     *
     * @return boolean
     */
    public function isSlave()
    {
        return $this->slave;
    }

    /**
     * Execute all the request in a file
     *
     * @param string $path with file full path
     *
     * @return boolean true if all query are successfull
     */
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
                if (!isCommandLine()) {
                    // Flush will prevent proxy to timeout as it will receive data.
                    // Flush requires a content to be sent, so we sent spaces as multiple spaces
                    // will be shown as a single one on browser.
                    echo ' ';
                    Html::glpi_flush();
                }
            }
        }

        return true;
    }

    /**
     * Instanciate a Simple DBIterator
     *
     * Examples =
     *  foreach ($DB->request("select * from glpi_states") as $data) { ... }
     *  foreach ($DB->request("glpi_states") as $ID => $data) { ... }
     *  foreach ($DB->request("glpi_states", "ID=1") as $ID => $data) { ... }
     *  foreach ($DB->request("glpi_states", "", "name") as $ID => $data) { ... }
     *  foreach ($DB->request("glpi_computers",array("name"=>"SBEI003W","entities_id"=>1),array("serial","otherserial")) { ... }
     *
     * Examples =
     *   array("id"=>NULL)
     *   array("OR"=>array("id"=>1, "NOT"=>array("state"=>3)));
     *   array("AND"=>array("id"=>1, array("NOT"=>array("state"=>array(3,4,5),"toto"=>2))))
     *
     * FIELDS name or array of field names
     * ORDER name or array of field names
     * LIMIT max of row to retrieve
     * START first row to retrieve
     *
     * @param string|string[] $tableorsql Table name, array of names or SQL query
     * @param string|string[] $crit       String or array of filed/values, ex array("id"=>1), if empty => all rows
     *                                    (default '')
     * @param boolean         $debug      To log the request (default false)
     *
     * @return DBmysqlIterator
     */
    public function request($tableorsql, $crit = "", $debug = false)
    {
        $iterator = new DBmysqlIterator($this);
        $iterator->execute($tableorsql, $crit, $debug);
        return $iterator;
    }


    /**
     * Get information about DB connection for showSystemInformation
     *
     * @since 0.84
     *
     * @return string[] Array of label / value
     */
    public function getInfo()
    {
        return $this->getPlatform()->getInfo($this);
    }

    public function setGroupConcatMaxLen(int $length): bool
    {
        return (bool) $this->query('SET SESSION group_concat_max_len = ' . $length);
    }

    public function getLastWarning(): ?array
    {
        $result = $this->query('SHOW WARNINGS');
        if (!$result || $this->numrows($result) === 0) {
            return null;
        }

        $warning = $this->fetchAssoc($result);
        return is_array($warning) ? $warning : null;
    }

    public function getDatabaseSize(): string
    {
        return $this->getPlatform()->getDatabaseSize($this);
    }

    /**
     * Is MySQL strict mode ?
     *
     * @var DB $DB
     *
     * @param string $msg Mode
     *
     * @return boolean
     *
     * @since 0.90
     * @deprecated 9.5.0
     */
    public static function isMySQLStrictMode(&$msg)
    {
        Toolbox::deprecated();
        global $DB;

        $msg = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_DATE,NO_ZERO_IN_DATE,ONLY_FULL_GROUP_BY,NO_AUTO_CREATE_USER';
        $req = $DB->request("SELECT @@sql_mode as mode");
        if (($data = $req->next())) {
            return (preg_match("/STRICT_TRANS/", (string) $data['mode'])
                    && preg_match("/NO_ZERO_/", (string) $data['mode'])
                    && preg_match("/ONLY_FULL_GROUP_BY/", (string) $data['mode']));
        }
        return false;
    }

    /**
     * Get a global DB lock
     *
     * @since 0.84
     *
     * @param string $name lock's name
     *
     * @return boolean
     */
    public function getLock($name)
    {
        return $this->getPlatform()->getLock($this, (string) $name);
    }

    /**
     * Release a global DB lock
     *
     * @since 0.84
     *
     * @param string $name lock's name
     *
     * @return boolean
     */
    public function releaseLock($name)
    {
        return $this->getPlatform()->releaseLock($this, (string) $name);
    }


    /**
     * Check if a table exists
     *
     * @since 9.2
     * @since 9.5 Added $usecache parameter.
     *
     * @param string  $tablename Table name
     * @param boolean $usecache  If use table list cache
     *
     * @return boolean
     **/
    public function tableExists($tablename, $usecache = true)
    {

        if (!$this->cache_disabled && $usecache && in_array($tablename, $this->table_cache)) {
            return true;
        }

        // Retrieve all tables if cache is empty but enabled, in order to fill cache
        // with all known tables
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

    /**
     * Check if a field exists
     *
     * @since 9.2
     *
     * @param string  $table    Table name for the field we're looking for
     * @param string  $field    Field name
     * @param Boolean $usecache Use cache; @see DBmysql::listFields(), defaults to true
     *
     * @return boolean
     **/
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
        return $this->getPlatform()->constraintExists($this, $table, $constraint);
    }

    /**
     * Disable table cache globally; usefull for migrations
     *
     * @return void
     */
    public function disableTableCaching()
    {
        $this->cache_disabled = true;
    }

    /**
     * Quote field name
     *
     * @since 9.3
     *
     * @param string $name of field to quote (or table.field)
     *
     * @return string
     */
    public static function quoteName($name)
    {
        $quote = static::getQuoteNameChar();

        // handle verbatim names
        if ($name instanceof QueryExpression) {
            return $name->getValue();
        }

        // handle aliases
        $name_matches = preg_split('/\s+AS\s+/i', (string) $name, 2);
        if (is_array($name_matches) && count($name_matches) === 2) {
            return static::quoteName($name_matches[0]) . ' AS ' . static::quoteName($name_matches[1]);
        }

        // handle names with multiple chunks (e.g. db.table.field or table.field)
        if (strpos($name, '.')) {
            $names = explode('.', $name);
            return implode('.', array_map(static::quoteName(...), $names));
        }

        // do not quote wildcard (*)
        if ($name === '*') {
            return $name;
        }

        foreach (['`', '"'] as $already_quote) {
            if (
                str_starts_with((string) $name, $already_quote)
                && str_ends_with((string) $name, $already_quote)
            ) {
                $name = trim($name, $already_quote);
                break;
            }
        }

        return sprintf(
            '%1$s%2$s%1$s',
            $quote,
            str_replace($quote, $quote . $quote, $name)
        );
    }

    /**
     * Quote value for insert/update
     *
     * @param mixed $value Value
     *
     * @return mixed
     */
    public static function quoteValue($value)
    {
        global $DB;

        if ($DB instanceof self) {
            return $DB->quoteCompatibleValue($value);
        }

        if ($value instanceof QueryParam || $value instanceof QueryExpression) {
            return $value->getValue();
        }

        if ($value === null || $value === 'NULL' || $value === 'null') {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        return "'$value'";
    }

    public function quoteCompatibleValue($value)
    {
        if ($value instanceof QueryParam || $value instanceof QueryExpression) {
            return $value->getValue();
        }

        if ($value === null || $value === 'NULL' || $value === 'null') {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        if (is_int($value) || is_float($value)) {
            return "'" . (string) $value . "'";
        }

        return $this->quote(
            $this->normalizeLegacySqlStringValue((string) $value)
        );
    }

    public function quoteFieldValue(?string $table, string $field, $value)
    {
        $normalized_field = strtolower((string) preg_replace('/^.*\./', '', $field));
        $normalized_table = $table !== null ? trim($table, '`"') : null;
        $is_boolean_field = $this->isLogicalBooleanField($normalized_field, $normalized_table);

        if (
            !$is_boolean_field
            && $this->dbtype === 'pgsql'
            && $normalized_table !== null
        ) {
            $field_definition = $this->getField($normalized_table, trim($field, '`"'));
            $is_boolean_field = ($field_definition['Type'] ?? null) === 'boolean';
        }

        if ($is_boolean_field) {
            return $this->quoteBooleanValue($value);
        }

        if (is_bool($value)) {
            return $this->quoteCompatibleValue((int) $value);
        }

        return $this->quoteCompatibleValue($value);
    }

    protected function quoteBooleanValue($value): string
    {
        if ($value === null || $value === 'NULL' || $value === 'null') {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        if (is_int($value) || is_float($value)) {
            return ((int) $value) === 0 ? 'FALSE' : 'TRUE';
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            return match ($normalized) {
                '', '0', 'false', 'f', 'n', 'no' => 'FALSE',
                '1', 'true', 't', 'y', 'yes' => 'TRUE',
                default => $this->quoteCompatibleValue($value),
            };
        }

        return $this->quoteCompatibleValue($value);
    }

    /**
     * Builds an insert statement
     *
     * @since 9.3
     *
     * @param string $table  Table name
     * @param array  $params Query parameters ([field name => field value)
     *
     * @return string
     */
    public function buildInsert($table, $params)
    {
        $query = "INSERT INTO " . self::quoteName($table) . " (";

        $fields = [];
        foreach ($params as $key => &$value) {
            $fields[] = $this->quoteName($key);

            if (
                $this->dbtype === 'pgsql'
                && ($value === null || $value === 'NULL' || $value === 'null')
            ) {
                $field = $this->getField($table, $key);
                if (($field['Extra'] ?? '') === 'auto_increment' || $key === 'id') {
                    $value = 'DEFAULT';
                    continue;
                }
            }

            $value = $this->quoteFieldValue((string) $table, (string) $key, $value);
        }

        $query .= implode(', ', $fields);
        $query .= ") VALUES (";
        $query .= implode(", ", $params);
        $query .= ")";

        return $query;
    }

    /**
     * Insert a row in the database
     *
     * @since 9.3
     *
     * @param string $table  Table name
     * @param array  $params Query parameters ([field name => field value)
     *
     * @return mysqli_result|boolean Query result handler
     */
    public function insert($table, $params)
    {
        $this->last_insert_id = null;
        $result = $this->query(
            $this->buildInsert($table, $params)
        );
        return $result;
    }

    /**
     * Insert a row in the database and die
     * (optionnaly with a message) if it fails
     *
     * @since 9.3
     *
     * @param string $table  Table name
     * @param array  $params  Query parameters ([field name => field value)
     * @param string $message Explanation of query (default '')
     *
     * @return mysqli_result|boolean Query result handler
     */
    public function insertOrDie($table, $params, $message = '')
    {
        $insert = $this->buildInsert($table, $params);
        $res = $this->query($insert);
        if (!$res) {
            //TRANS: %1$s is the description, %2$s is the query, %3$s is the error message
            $message = sprintf(
                __('%1$s - Error during the database query: %2$s - Error is %3$s'),
                $message,
                $insert,
                $this->error()
            );
            if (isCommandLine()) {
                throw new \RuntimeException($message);
            } else {
                echo $message . "\n";
                die(1);
            }
        }
        return $res;
    }

    /**
     * Builds an update statement
     *
     * @since 9.3
     *
     * @param string $table   Table name
     * @param array  $params  Query parameters ([field name => field value)
     * @param array  $clauses Clauses to use. If not 'WHERE' key specified, will b the WHERE clause (@see DBmysqlIterator capabilities)
     * @param array  $joins  JOINS criteria array
     *
     * @since 9.4.0 $joins parameter added
     * @return string
     */
    public function buildUpdate($table, $params, $clauses, array $joins = [])
    {
        //when no explicit "WHERE", we only have a WHEre clause.
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

        $it = new DBmysqlIterator($this);
        if ($this->requiresPgSqlSubselectUpdate($clauses, $joins)) {
            return $this->buildPgSqlSubselectUpdate((string) $table, $params, $clauses, $joins, $it);
        }

        $query  = "UPDATE " . self::quoteName($table);
        if ($this->dbtype !== 'pgsql') {
            $query .= $it->analyseJoins($joins);
        }

        $query .= $this->buildUpdateSetClause((string) $table, $params);
        $query .= " WHERE " . $it->analyseCrit($clauses['WHERE']);

        // ORDER BY
        if (isset($clauses['ORDER']) && !empty($clauses['ORDER'])) {
            $query .= $it->handleOrderClause($clauses['ORDER']);
        }

        if (isset($clauses['LIMIT']) && !empty($clauses['LIMIT'])) {
            $offset = (isset($clauses['START']) && !empty($clauses['START'])) ? $clauses['START'] : null;
            $query .= $it->handleLimits($clauses['LIMIT'], $offset);
        }

        return $query;
    }

    protected function requiresPgSqlSubselectUpdate(array $clauses, array $joins): bool
    {
        if ($this->dbtype !== 'pgsql') {
            return false;
        }

        return $joins !== []
            || !empty($clauses['ORDER'])
            || !empty($clauses['LIMIT'])
            || !empty($clauses['START']);
    }

    protected function buildPgSqlSubselectUpdate(
        string $table,
        array $params,
        array $clauses,
        array $joins,
        DBmysqlIterator $iterator
    ): string {
        $target_table = self::quoteName($table);
        $query = "UPDATE " . $target_table;
        $query .= $this->buildUpdateSetClause($table, $params);

        $selection = "SELECT " . self::quoteName($table . '.ctid') . " FROM " . $target_table;
        if ($joins !== []) {
            $selection .= $iterator->analyseJoins($joins);
        }

        $selection .= " WHERE " . $iterator->analyseCrit($clauses['WHERE']);

        if (isset($clauses['ORDER']) && !empty($clauses['ORDER'])) {
            $selection .= $iterator->handleOrderClause($clauses['ORDER']);
        }

        if (isset($clauses['LIMIT']) && !empty($clauses['LIMIT'])) {
            $offset = (isset($clauses['START']) && !empty($clauses['START'])) ? $clauses['START'] : null;
            $selection .= $iterator->handleLimits($clauses['LIMIT'], $offset);
        }

        $query .= " WHERE " . self::quoteName('ctid') . " IN (" . $selection . ")";

        return $query;
    }

    protected function buildUpdateSetClause(string $table, array $params): string
    {
        $clause = " SET ";
        foreach ($params as $field => $value) {
            $target_field = $this->normalizeUpdateTargetField((string) $field);
            $clause .= self::quoteName($target_field)
                . " = "
                . $this->quoteFieldValue($table, $target_field, $value)
                . ", ";
        }

        return rtrim($clause, ', ');
    }

    protected function normalizeUpdateTargetField(string $field): string
    {
        if ($this->dbtype !== 'pgsql') {
            return $field;
        }

        return (string) preg_replace('/^.*\./', '', $field);
    }

    /**
     * Update a row in the database
     *
     * @since 9.3
     *
     * @param string $table  Table name
     * @param array  $params Query parameters ([:field name => field value)
     * @param array  $where  WHERE clause
     * @param array  $joins  JOINS criteria array
     *
     * @since 9.4.0 $joins parameter added
     * @return mysqli_result|boolean Query result handler
     */
    public function update($table, $params, $where, array $joins = [])
    {
        $query = $this->buildUpdate($table, $params, $where, $joins);
        $result = $this->query($query);
        return $result;
    }

    /**
     * Update a row in the database or die
     * (optionnaly with a message) if it fails
     *
     * @since 9.3
     *
     * @param string $table   Table name
     * @param array  $params  Query parameters ([:field name => field value)
     * @param array  $where   WHERE clause
     * @param string $message Explanation of query (default '')
     * @param array  $joins   JOINS criteria array
     *
     * @since 9.4.0 $joins parameter added
     * @return mysqli_result|boolean Query result handler
     */
    public function updateOrDie($table, $params, $where, $message = '', array $joins = [])
    {
        $update = $this->buildUpdate($table, $params, $where, $joins);
        $res = $this->query($update);
        if (!$res) {
            //TRANS: %1$s is the description, %2$s is the query, %3$s is the error message
            $message = sprintf(
                __('%1$s - Error during the database query: %2$s - Error is %3$s'),
                $message,
                $update,
                $this->error()
            );
            if (isCommandLine()) {
                throw new \RuntimeException($message);
            } else {
                echo $message . "\n";
                die(1);
            }
        }
        return $res;
    }

    /**
     * Update a row in the database or insert a new one
     *
     * @since 9.4
     *
     * @param string  $table   Table name
     * @param array   $params  Query parameters ([:field name => field value)
     * @param array   $where   WHERE clause
     * @param boolean $onlyone Do the update only one one element, defaults to true
     *
     * @return mysqli_result|boolean Query result handler
     */
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

    /**
     * Builds a delete statement
     *
     * @since 9.3
     *
     * @param string $table  Table name
     * @param array  $params Query parameters ([field name => field value)
     * @param array  $where  WHERE clause (@see DBmysqlIterator capabilities)
     * @param array  $joins  JOINS criteria array
     *
     * @since 9.4.0 $joins parameter added
     * @return string
     */
    public function buildDelete($table, $where, array $joins = [])
    {

        if (!count($where)) {
            throw new \RuntimeException('Cannot run an DELETE query without WHERE clause!');
        }

        $it = new DBmysqlIterator($this);
        $join_where = [];
        if ($this->dbtype === 'pgsql') {
            $query = "DELETE FROM " . self::quoteName($table);
            $join_tables = $this->buildJoinTargets($joins, $join_where);
            if ($join_tables !== []) {
                $query .= ' USING ' . implode(', ', $join_tables);
            }
        } else {
            $query  = "DELETE " . self::quoteName($table) . " FROM " . self::quoteName($table);
            $query .= $it->analyseJoins($joins);
        }

        $where_parts = [];
        if ($join_where !== []) {
            $where_parts[] = implode(' AND ', $join_where);
        }
        $where_parts[] = $it->analyseCrit($where);
        $query .= " WHERE " . implode(' AND ', $where_parts);

        return $query;
    }

    protected function buildJoinTargets(array $joins, array &$conditions = []): array
    {
        $targets = [];
        $iterator = new DBmysqlIterator($this);

        foreach ($joins as $jointables) {
            if (!is_array($jointables)) {
                continue;
            }

            foreach ($jointables as $jointablekey => $jointablecrit) {
                if (isset($jointablecrit['TABLE'])) {
                    $jointablekey = $jointablecrit['TABLE'];
                    unset($jointablecrit['TABLE']);
                }

                if ($jointablekey instanceof QuerySubQuery) {
                    $targets[] = $jointablekey->getQuery();
                } else {
                    $targets[] = self::quoteName($jointablekey);
                }

                $conditions[] = '(' . $iterator->analyseCrit($jointablecrit) . ')';
            }
        }

        return $targets;
    }

    /**
     * Delete rows in the database
     *
     * @since 9.3
     *
     * @param string $table  Table name
     * @param array  $where  WHERE clause
     * @param array  $joins  JOINS criteria array
     *
     * @since 9.4.0 $joins parameter added
     * @return mysqli_result|boolean Query result handler
     */
    public function delete($table, $where, array $joins = [])
    {
        $query = $this->buildDelete($table, $where, $joins);
        $result = $this->query($query);
        return $result;
    }

    /**
     * Delete a row in the database and die
     * (optionnaly with a message) if it fails
     *
     * @since 9.3
     *
     * @param string $table   Table name
     * @param array  $where   WHERE clause
     * @param string $message Explanation of query (default '')
     * @param array  $joins   JOINS criteria array
     *
     * @since 9.4.0 $joins parameter added
     * @return mysqli_result|boolean Query result handler
     */
    public function deleteOrDie($table, $where, $message = '', array $joins = [])
    {
        $update = $this->buildDelete($table, $where, $joins);
        $res = $this->query($update);
        if (!$res) {
            //TRANS: %1$s is the description, %2$s is the query, %3$s is the error message
            $message = sprintf(
                __('%1$s - Error during the database query: %2$s - Error is %3$s'),
                $message,
                $update,
                $this->error()
            );
            if (isCommandLine()) {
                throw new \RuntimeException($message);
            } else {
                echo $message . "\n";
                die(1);
            }
        }
        return $res;
    }


    /**
     * Get table schema
     *
     * @param string $table Table name,
     * @param string|null $structure Raw table structure
     *
     * @return array
     */
    public function getTableSchema($table, $structure = null)
    {
        return $this->getPlatform()->getTableSchema($this, $table, $structure);
    }

    /**
     * Get database raw version
     *
     * @return string
     */
    public function getVersion()
    {
        $result = $this->queryOrDie('SELECT version()', 'Read database version');
        $row = $this->fetchRow($result);
        return (string) ($row[0] ?? '');
    }

    /**
     * Starts a transaction
     *
     * @return boolean
     */
    public function beginTransaction()
    {
        $this->in_transaction = true;
        return $this->dbh->beginTransaction();
    }

    /**
     * Commits a transaction
     *
     * @return boolean
     */
    public function commit()
    {
        $this->in_transaction = false;

        if ($this->dbh instanceof \PDO && !$this->dbh->inTransaction()) {
            return true;
        }

        return $this->dbh->commit();
    }

    /**
     * Rollbacks a transaction
     *
     * @return boolean
     */
    public function rollBack()
    {
        $this->in_transaction = false;

        if ($this->dbh instanceof \PDO && !$this->dbh->inTransaction()) {
            return true;
        }

        return $this->dbh->rollback();
    }

    /**
     * Are we in a transaction?
     *
     * @return boolean
     */
    public function inTransaction()
    {
        return $this->in_transaction;
    }

    /**
     * Check if timezone data is accessible and available in database.
     *
     * @param string $msg  Variable that would contain the reason of data unavailability.
     *
     * @return boolean
     *
     * @since 9.5.0
     */
    public function areTimezonesAvailable(string &$msg = '')
    {
        return $this->getPlatform()->areTimezonesAvailable($this, $msg);
    }

    /**
     * Defines timezone to use.
     *
     * @param string $timezone
     *
     * @return DBmysql
     */
    public function setTimezone($timezone)
    {
        return $this->getPlatform()->setTimezone($this, $timezone);
    }

    /**
     * Returns list of timezones.
     *
     * @return string[]
     *
     * @since 9.5.0
     */
    public function getTimezones()
    {
        return $this->getPlatform()->getTimezones($this);
    }

    /**
     * Returns count of tables that were not migrated to be compatible with timezones usage.
     *
     * @return number
     *
     * @since 9.5.0
     */
    public function notTzMigrated()
    {
        return $this->getPlatform()->notTzMigrated($this);
    }

    /**
     * Returns columns that corresponds to signed primary/foreign keys.
     *
     * @return DBmysqlIterator
     *
     * @since 9.5.7
     */
    public function getSignedKeysColumns()
    {
        return $this->getPlatform()->getSignedKeysColumns($this);
    }

    /**
     * Returns foreign keys constraints.
     *
     * @return DBmysqlIterator
     *
     * @since 9.5.7
     */
    public function getForeignKeysContraints()
    {
        return $this->getPlatform()->getForeignKeysContraints($this);
    }

    /**
     * Clear cached schema information.
     *
     * @return void
     */
    public function clearSchemaCache()
    {
        $this->table_cache = [];
        $this->field_cache = [];
    }

    public function databaseExists(string $database): bool
    {
        return $this->getPlatform()->databaseExists($this, $database);
    }

    public function createDatabase(string $database): bool
    {
        return $this->getPlatform()->createDatabase($this, $database);
    }

    public function useDatabase(string $database): bool
    {
        $this->dbdefault = $database;
        $this->clearSchemaCache();

        $this->close();
        $this->connect();

        return $this->connected;
    }

    /**
     * Quote a value for a specified type
     * Should be used for PDO, but this will prevent heavy
     * replacements in the source code in the future.
     *
     * @param mixed   $value Value to quote
     * @param integer $type  Value type, defaults to PDO::PARAM_STR
     *
     * @return mixed
     *
     * @since 9.5.0
     */
    public function quote($value, int $type = 2/*\PDO::PARAM_STR*/)
    {
        if ($value === null) {
            return 'NULL';
        }

        return $this->dbh instanceof \PDO
            ? $this->dbh->quote((string) $value, $type)
            : "'" . $this->escape($value) . "'";
    }

    public function sqlConcat(array $expressions): string
    {
        return 'CONCAT(' . implode(', ', $expressions) . ')';
    }

    public function sqlPosition(string $needle, string $haystack): string
    {
        return $this->getPlatform()->sqlPosition($this, $needle, $haystack);
    }

    public function sqlIf(string $condition, string $when_true, string $when_false): string
    {
        return $this->getPlatform()->sqlIf($this, $condition, $when_true, $when_false);
    }

    public function sqlGroupConcat(
        string $expression,
        string $separator = ',',
        bool $distinct = false,
        ?string $order_by = null
    ): string {
        return $this->getPlatform()->sqlGroupConcat($this, $expression, $separator, $distinct, $order_by);
    }

    public function sqlIfNull(string $expression, string $fallback): string
    {
        return $this->getPlatform()->sqlIfNull($this, $expression, $fallback);
    }

    public function sqlNullIf(string $expression, string $fallback): string
    {
        return 'NULLIF(' . $expression . ', ' . $fallback . ')';
    }

    public function sqlCastAsString(string $expression): string
    {
        return $this->getPlatform()->sqlCastAsString($this, $expression);
    }

    public function sqlCastAsUnsignedInteger(string $expression): string
    {
        return $this->getPlatform()->sqlCastAsUnsignedInteger($this, $expression);
    }

    public function sqlNow(): string
    {
        return 'NOW()';
    }

    public function sqlCurrentTimestamp(): string
    {
        return $this->getPlatform()->sqlCurrentTimestamp($this);
    }

    public function sqlCurrentDate(): string
    {
        return $this->getPlatform()->sqlCurrentDate($this);
    }

    public function sqlCurrentHour(): string
    {
        return $this->getPlatform()->sqlCurrentHour($this);
    }

    public function sqlLike(string $expression, string $pattern, bool $case_sensitive = true): string
    {
        return $this->getPlatform()->sqlLike($this, $expression, $pattern, $case_sensitive);
    }

    public function sqlFullTextBooleanMatch(array $expressions, string $search): string
    {
        return $this->getPlatform()->sqlFullTextBooleanMatch($this, $expressions, $search);
    }

    public function sqlFullTextBooleanScore(array $expressions, string $search): string
    {
        return $this->getPlatform()->sqlFullTextBooleanScore($this, $expressions, $search);
    }

    public function sqlBitCount(string $expression, int $width = 32): string
    {
        return $this->getPlatform()->sqlBitCount($this, $expression, $width);
    }

    public function sqlBitwiseAnd(string $left, string $right): string
    {
        return $this->getPlatform()->sqlBitwiseAnd($this, $left, $right);
    }

    public function sqlBitTest(string $left, string $right): string
    {
        return $this->sqlBitwiseAnd($left, $right) . ' <> 0';
    }

    public function sqlUnixTimestamp(?string $expression = null): string
    {
        return $this->getPlatform()->sqlUnixTimestamp($this, $expression);
    }

    public function sqlDateFormat(string $expression, string $format): string
    {
        return $this->getPlatform()->sqlDateFormat($this, $expression, $format);
    }

    public function sqlDateTruncateToMinute(?string $expression = null): string
    {
        return $this->getPlatform()->sqlDateTruncateToMinute($this, $expression);
    }

    public function sqlDateAddInterval(string $expression, $value, string $unit): string
    {
        return $this->getPlatform()->sqlDateAddInterval($this, $expression, $value, $unit);
    }

    public function sqlDateSubInterval(string $expression, $value, string $unit): string
    {
        return $this->getPlatform()->sqlDateSubInterval($this, $expression, $value, $unit);
    }

    public function sqlDateDiffDays(string $left, string $right): string
    {
        return $this->getPlatform()->sqlDateDiffDays($this, $left, $right);
    }

    public function sqlMonthDayOrdinal(string $expression): string
    {
        return $this->getPlatform()->sqlMonthDayOrdinal($this, $expression);
    }

    public function sqlTimeDiffInSeconds(string $left, string $right): string
    {
        return $this->getPlatform()->sqlTimeDiffInSeconds($this, $left, $right);
    }

    public function sqlClockTimeDiffInSeconds(string $left, string $right): string
    {
        return $this->getPlatform()->sqlClockTimeDiffInSeconds($this, $left, $right);
    }

    protected function normalizeLegacySqlStringValue(string $value): string
    {
        return $this->decodeLegacyMysqlEscapes($value);
    }

    /**
     * Extract normalized search tokens from a boolean full-text query string.
     *
     * This is intentionally scoped to search input, not SQL parsing.
     *
     * @return string[]
     */
    protected function extractFullTextSearchTerms(string $search): array
    {
        $normalized = preg_replace('/[\\\\\"+~<>()-]+/', ' ', $search) ?? $search;
        $normalized = preg_replace('/\s+/', ' ', trim($normalized)) ?? trim($normalized);
        if ($normalized === '') {
            return [];
        }

        $terms = [];
        foreach (explode(' ', $normalized) as $term) {
            $term = trim($term);
            if ($term === '') {
                continue;
            }

            $term = rtrim($term, '*');
            if ($term === '') {
                continue;
            }

            $terms[] = $term;
        }

        return array_values(array_unique($terms));
    }

    protected function decodeLegacyMysqlEscapes(string $value): string
    {
        $length = strlen($value);
        $decoded = '';

        for ($index = 0; $index < $length; $index++) {
            $char = $value[$index];
            if ($char !== '\\' || $index + 1 >= $length) {
                $decoded .= $char;
                continue;
            }

            $index++;
            $next = $value[$index];
            $decoded .= match ($next) {
                '0' => "\0",
                'b' => "\b",
                'n' => "\n",
                'r' => "\r",
                't' => "\t",
                'Z' => "\x1a",
                default => $next,
            };
        }

        return $decoded;
    }

    public function syncAllAutoIncrementSequences(): void
    {
    }

    public function getImplicitInsertDefaults(string $table, array $reference): array
    {
        return $this->getPlatform()->getImplicitInsertDefaults($this, $table, $reference);
    }

    /**
     * Get character used to quote names for current database engine
     *
     * @return string
     *
     * @since 9.5.0
     */
    public static function getQuoteNameChar(): string
    {
        return PlatformResolver::resolve(static::getCurrentDbType())->getIdentifierQuoteChar();
    }

    /**
     * Is value quoted as database field/expression?
     *
     * @param string|\QueryExpression $value Value to check
     *
     * @return boolean
     *
     * @since 9.5.0
     */
    public static function isNameQuoted($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        foreach (['`', '"'] as $quote) {
            if (trim($value, $quote) != $value) {
                return true;
            }
        }

        return false;
    }

    protected static function getCurrentDbType(): string
    {
        global $DB;

        if ($DB instanceof DatabaseInterface) {
            return $DB->getDbType();
        }

        return static::$default_dbtype;
    }

    /**
     * Remove SQL comments
     * © 2011 PHPBB Group
     *
     * @param string $output SQL statements
     *
     * @return string
     */
    public function removeSqlComments($output)
    {
        $lines = explode("\n", $output);
        $output = "";

        // try to keep mem. use down
        $linecount = count($lines);

        $in_comment = false;
        for ($i = 0; $i < $linecount; $i++) {
            if (preg_match("/^\/\*/", $lines[$i])) {
                $in_comment = true;
            }

            if (!$in_comment) {
                $output .= $lines[$i] . "\n";
            }

            if (preg_match("/\*\/$/", preg_quote($lines[$i]))) {
                $in_comment = false;
            }
        }

        unset($lines);
        return trim($output);
    }

    /**
     * Remove remarks and comments from SQL
     * @see DBmysql::removeSqlComments()
     * © 2011 PHPBB Group
     *
     * @param $string $sql SQL statements
     *
     * @return string
     */
    public function removeSqlRemarks($sql)
    {
        $lines = explode("\n", (string) $sql);

        // try to keep mem. use down
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
                // Trading a bit of speed for lower mem. use here.
                $lines[$i] = "";
            }
        }
        return trim($this->removeSqlComments($output));
    }
}
