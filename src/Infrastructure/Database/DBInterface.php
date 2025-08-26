<?php

namespace Itsmng\Infrastructure\Database;

/**
 * Database engine interface mirroring the public API of legacy DBmysql.
 * Implementations should be drop-in replacements.
 */
interface DBInterface
{
    // Connection/config
    public function __construct($choice = null);
    public function connect($choice = null);
    public function guessTimezone();

    // Escaping/quoting
    public function escape($string);
    public static function quoteName($name);
    public static function quoteValue($value);
    public function quote($value, int $type = 2);
    public static function getQuoteNameChar(): string;
    public static function isNameQuoted($value): bool;

    // Querying
    public function query($query);
    public function queryOrDie($query, $message = '');
    public function prepare($query);

    // Results helpers
    public function result($result, $i, $field);
    public function numrows($result);
    public function fetchArray($result);
    public function fetchRow($result);
    public function fetchAssoc($result);
    public function fetchObject($result);
    public function dataSeek($result, $num);
    public function insertId();
    public function numFields($result);
    public function fieldName($result, $nb);
    public function affectedRows();
    public function freeResult($result);
    public function errno();
    public function error();

    // Connection state
    public function close();
    public function isSlave();

    // Utilities
    public function runFile($path);
    public function request($tableorsql, $crit = "", $debug = false);
    public function getInfo();
    public static function isMySQLStrictMode(&$msg);
    public function getLock($name);
    public function releaseLock($name);
    public function tableExists($tablename, $usecache = true);
    public function fieldExists($table, $field, $usecache = true);
    public function constraintExists($table, $constraint);
    public function disableTableCaching();
    public function listTables($table = 'glpi\_%', array $where = []);
    public function getMyIsamTables();
    public function listFields($table, $usecache = true);
    public function getField(string $table, string $field, $usecache = true): ?array;
    public function buildInsert($table, $params);
    public function insert($table, $params);
    public function insertOrDie($table, $params, $message = '');
    public function buildUpdate($table, $params, $clauses, array $joins = []);
    public function update($table, $params, $where, array $joins = []);
    public function updateOrDie($table, $params, $where, $message = '', array $joins = []);
    public function updateOrInsert($table, $params, $where, $onlyone = true);
    public function buildDelete($table, $where, array $joins = []);
    public function delete($table, $where, array $joins = []);
    public function deleteOrDie($table, $where, $message = '', array $joins = []);
    public function getTableSchema($table, $structure = null);
    public function getVersion();
    public function beginTransaction();
    public function commit();
    public function rollBack();
    public function inTransaction();
    public function areTimezonesAvailable(string &$msg = '');
    public function setTimezone($timezone);
    public function getTimezones();
    public function notTzMigrated();
    public function getSignedKeysColumns();
    public function getForeignKeysContraints();
    public function clearSchemaCache();
    public function removeSqlComments($output);
    public function removeSqlRemarks($sql);

    // Public properties expected by legacy code (keep as public in engines)
    // These are not enforced by interface but noted here for clarity:
    // $dbhost, $dbuser, $dbpassword, $dbdefault, $connected, $slave, $execution_time
}
