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

use Glpi\Application\ErrorHandler;
use Itsmng\Infrastructure\Database\DBInterface as __DBInterface;
use Itsmng\Infrastructure\Database\DBEngineMySQL as __DBEngineMySQL;
use Itsmng\Infrastructure\Database\DBEnginePostgres as __DBEnginePostgres;
use Symfony\Component\Dotenv\Dotenv as __Dotenv;

/**
 *  Database class for Mysql
**/
class DBmysql
{
    /**
     * List of keys that are allowed to use signed integers.
     *
     * Elements contained in this list have to be fixed before being able to globally use foreign key contraints.
     *
     * @var array
     */
    private const ALLOWED_SIGNED_KEYS = [
       // FIXME Entity preference glpi_entities.calendars_id inherit/never strategy should be stored in another field.
       'glpi_calendars.id',
       // FIXME Entity preference glpi_entities.changetemplates_id inherit/never strategy should be stored in another field.
       'glpi_changetemplates.id',
       // FIXME Entity preference glpi_entities.contracts_id_default inherit/never strategy should be stored in another field.
       'glpi_contracts.id',
       // FIXME root entity uses "-1" value for its parent (glpi_entities.entities_id), should be null
       // FIXME some entities_id foreign keys are using "-1" as default value, should be null
       // FIXME Entity preference glpi_entities.entities_id_software inherit/never strategy should be stored in another field.
       'glpi_entities.id',
       // FIXME Entity preference glpi_entities.problemtemplates_id inherit/never strategy should be stored in another field.
       'glpi_problemtemplates.id',
       // FIXME Entity preference glpi_entities.tickettemplates_id inherit/never strategy should be stored in another field.
       'glpi_tickettemplates.id',
       // FIXME Entity preference glpi_entities.transfers_id inherit/never strategy should be stored in another field.
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
    //! Database Handler (kept for BC; not used directly)
    private $dbh;
    //! Database Error
    public $error              = 0;

    // Slave management
    public $slave              = false;
    private $in_transaction;

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

    private $cache_disabled = false;

    /**
     * Cached list fo tables.
     *
     * @var array
     * @see self::tableExists()
     */
    private $table_cache = [];

    /**
     * Cached list of fields.
     *
     * @var array
     * @see self::listFields()
     */
    private $field_cache = [];

    /**
     * Selected engine implementation.
     * @var __DBInterface
     */
    private $__engine;

    /**
     * Constructor / Connect to the MySQL Database
     *
     * @param integer $choice host number (default NULL)
     *
     * @return void
     */
    public function __construct($choice = null)
    {
        $this->bootstrapEnv();
        $this->selectEngineFromEnv();
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
        // Sync engine properties from facade
        foreach (['dbhost','dbuser','dbpassword','dbdefault','dbssl','dbsslkey','dbsslcert','dbsslca','dbsslcapath','dbsslcacipher','slave','execution_time'] as $prop) {
            if (property_exists($this, $prop) && property_exists($this->__engine, $prop)) {
                $this->__engine->$prop = $this->$prop;
            }
        }
        $this->__engine->connect($choice);
        $this->connected = $this->__engine->connected ?? false;
    }

    private function bootstrapEnv(): void
    {
        $envBootstrap = GLPI_ROOT . '/src/Infrastructure/Env.php';
        if (is_readable($envBootstrap)) {
            require_once $envBootstrap;
            return;
        }
    }

    private function selectEngineFromEnv(): void
    {
        $driverCandidate = $_ENV['DB_DRIVER'] ?? ($_ENV['DATABASE_URL'] ?? 'pdo_mysql');
        $driver = is_string($driverCandidate) ? strtolower($driverCandidate) : 'pdo_mysql';
        $is_pg = str_contains($driver, 'pgsql') || str_contains($driver, 'postgres');
        if ($is_pg) {
            $this->__engine = new __DBEnginePostgres();
        } else {
            $this->__engine = new __DBEngineMySQL();
        }
        // back-reference for iterator and utilities needing facade
        if (property_exists($this->__engine, 'parentFacade')) {
            $this->__engine->parentFacade = $this;
        }

        // If URL provided, parse it to props, else use split vars; only read from $_ENV
        $urlEnv = $_ENV['DB_URL'] ?? ($_ENV['DATABASE_URL'] ?? '');
        $url = is_string($urlEnv) ? trim($urlEnv) : '';
        if ($url !== '') {
            $parts = parse_url($url);
            if ($parts !== false) {
                $host = $parts['host'] ?? 'localhost';
                $port = isset($parts['port']) ? ':' . $parts['port'] : '';
                $this->dbhost = $host . $port;
                $this->dbuser = $parts['user'] ?? '';
                $this->dbpassword = $parts['pass'] ?? '';
                $this->dbdefault = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
            }
        } else {
            $dbhost = $_ENV['DB_HOST'] ?? null;
            $dbuser = $_ENV['DB_USER'] ?? null;
            $dbpass = $_ENV['DB_PASSWORD'] ?? null;
            $dbname = $_ENV['DB_NAME'] ?? null;
            if (isset($dbhost) && $dbhost !== '') { $this->dbhost = $dbhost; }
            if (isset($dbuser) && $dbuser !== '') { $this->dbuser = $dbuser; }
            if (isset($dbpass) && $dbpass !== '') { $this->dbpassword = $dbpass; }
            if (isset($dbname) && $dbname !== '') { $this->dbdefault = $dbname; }
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
        return $this->__engine->guessTimezone();
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
        return $this->__engine->escape($string);
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
        return $this->__engine->query($query);
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
        return $this->__engine->queryOrDie($query, $message);
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
        return $this->__engine->prepare($query);
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
        return $this->__engine->result($result, $i, $field);
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
        return $this->__engine->numrows($result);
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
        return $this->__engine->fetchArray($result);
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
        return $this->__engine->fetchRow($result);
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
        return $this->__engine->fetchAssoc($result);
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
    return $this->fetchObject($result);
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
        return $this->__engine->fetchObject($result);
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
        return $this->__engine->dataSeek($result, $num);
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
        return $this->__engine->insertId();
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
        return $this->__engine->numFields($result);
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
        return $this->__engine->fieldName($result, $nb);
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
        return $this->__engine->listTables($table, $where);
    }

    /**
     * Returns tables using "MyIsam" engine.
     *
     * @return DBmysqlIterator
     */
    public function getMyIsamTables(): DBmysqlIterator
    {
        return $this->__engine->getMyIsamTables();
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
        return $this->__engine->listFields($table, $usecache);
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
        return $this->__engine->getField($table, $field, $usecache);
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
        return $this->__engine->affectedRows();
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
        return $this->__engine->freeResult($result);
    }

    /**
     * Returns the numerical value of the error message from previous MySQL operation
     *
     * @return int error number from the last MySQL function, or 0 (zero) if no error occurred.
     */
    public function errno()
    {
        return $this->__engine->errno();
    }

    /**
     * Returns the text of the error message from previous MySQL operation
     *
     * @return string error text from the last MySQL function, or '' (empty string) if no error occurred.
     */
    public function error()
    {
        return $this->__engine->error();
    }

    /**
     * Close MySQL connection
     *
     * @return boolean TRUE on success or FALSE on failure.
     */
    public function close()
    {
        return $this->__engine->close();
    }

    /**
     * is a slave database ?
     *
     * @return boolean
     */
    public function isSlave()
    {
        return $this->__engine->isSlave();
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
        return $this->__engine->runFile($path);
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
        return $this->__engine->getInfo();
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
        return __DBEngineMySQL::isMySQLStrictMode($msg);
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
        return $this->__engine->getLock($name);
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
        return $this->__engine->releaseLock($name);
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
        return $this->__engine->tableExists($tablename, $usecache);
    }

    /**
     * Check if a field exists
     *
     * @since 9.2
     *
    * @param string  $table    Table name for the field we're looking for
    * @param string  $field    Field name
    * @param boolean $usecache Use cache; @see DBmysql::listFields(), defaults to true
     *
     * @return boolean
     **/
    public function fieldExists($table, $field, $usecache = true)
    {
        return $this->__engine->fieldExists($table, $field, $usecache);
    }

    public function constraintExists($table, $constraint)
    {
        return $this->__engine->constraintExists($table, $constraint);
    }

    /**
     * Disable table cache globally; usefull for migrations
     *
     * @return void
     */
    public function disableTableCaching()
    {
        $this->__engine->disableTableCaching();
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
        // Keep legacy MySQL quoting to preserve DBmysqlIterator behavior
        return __DBEngineMySQL::quoteName($name);
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
        return __DBEngineMySQL::quoteValue($value);
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
        return $this->__engine->buildInsert($table, $params);
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
        return $this->__engine->insert($table, $params);
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
        return $this->__engine->insertOrDie($table, $params, $message);
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
        return $this->__engine->buildUpdate($table, $params, $clauses, $joins);
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
        return $this->__engine->update($table, $params, $where, $joins);
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
        return $this->__engine->updateOrDie($table, $params, $where, $message, $joins);
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
        return $this->__engine->updateOrInsert($table, $params, $where, $onlyone);
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
        return $this->__engine->buildDelete($table, $where, $joins);
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
        return $this->__engine->delete($table, $where, $joins);
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
        return $this->__engine->deleteOrDie($table, $where, $message, $joins);
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
        return $this->__engine->getTableSchema($table, $structure);
    }

    /**
     * Get database raw version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->__engine->getVersion();
    }

    /**
     * Starts a transaction
     *
     * @return boolean
     */
    public function beginTransaction()
    {
        return $this->__engine->beginTransaction();
    }

    /**
     * Commits a transaction
     *
     * @return boolean
     */
    public function commit()
    {
        return $this->__engine->commit();
    }

    /**
     * Rollbacks a transaction
     *
     * @return boolean
     */
    public function rollBack()
    {
        return $this->__engine->rollBack();
    }

    /**
     * Are we in a transaction?
     *
     * @return boolean
     */
    public function inTransaction()
    {
        return $this->__engine->inTransaction();
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
        return $this->__engine->areTimezonesAvailable($msg);
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
        return $this->__engine->setTimezone($timezone);
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
        return $this->__engine->getTimezones();
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
        return $this->__engine->notTzMigrated();
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
        return $this->__engine->getSignedKeysColumns();
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
        return $this->__engine->getForeignKeysContraints();
    }

    /**
     * Clear cached schema information.
     *
     * @return void
     */
    public function clearSchemaCache()
    {
        return $this->__engine->clearSchemaCache();
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
        return $this->__engine->quote($value, $type);
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
        return __DBEngineMySQL::getQuoteNameChar();
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
        return __DBEngineMySQL::isNameQuoted($value);
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
        return $this->__engine->removeSqlComments($output);
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
        return $this->__engine->removeSqlRemarks($sql);
    }
}
