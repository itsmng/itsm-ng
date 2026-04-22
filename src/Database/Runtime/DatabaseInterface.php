<?php

namespace itsmng\Database\Runtime;

use DBmysqlIterator;
use DBmysqlResult;
use DBmysqlStatement;
use mysqli_result;
use QueryExpression;

/**
 * @phpstan-type DbQueryResult DBmysqlResult|mysqli_result|bool
 * @phpstan-type DbPreparedStatement DBmysqlStatement|false
 * @phpstan-type DbRow array<int, mixed>
 * @phpstan-type DbAssocRow array<string, mixed>
 * @phpstan-type DbIdentifier string|QueryExpression
 * @phpstan-type DbRequestSource string|list<string>
 * @phpstan-type DbCriteria array<array-key, mixed>|string
 * @phpstan-type DbWhere array<array-key, mixed>
 * @phpstan-type DbJoins array<array-key, mixed>
 * @phpstan-type DbValues array<string, mixed>
 */
interface DatabaseInterface
{
    public function getDbType(): string;

    /**
     * @param int|null $choice
     */
    public function connect($choice = null);

    /**
     * @param mixed $value
     * @return string|false
     */
    public function quote($value, int $type = 2);

    /**
     * @param DbIdentifier $name
     * @return string
     */
    public static function quoteName($name);

    /**
     * @param string $query
     * @return DbQueryResult
     */
    public function query($query);

    /**
     * @param string $query
     * @param string $message
     * @return DBmysqlResult
     */
    public function queryOrDie($query, $message = '');

    /**
     * @param string $query
     * @return DbPreparedStatement
     */
    public function prepare($query);

    /**
     * @param DbRequestSource $tableorsql
     * @param DbCriteria $crit
     * @param bool $debug
     * @return DBmysqlIterator
     */
    public function request($tableorsql, $crit = "", $debug = false);

    /**
     * @param string $tablename
     * @param bool $usecache
     * @return bool
     */
    public function tableExists($tablename, $usecache = true);

    /**
     * @param string $table
     * @param array<array-key, mixed> $where
     * @return DBmysqlIterator
     */
    public function listTables($table = 'glpi\_%', array $where = []);

    /**
     * @param DBmysqlResult $result
     * @return DbAssocRow|null
     */
    public function fetchAssoc($result);

    /**
     * @param DBmysqlResult $result
     * @return DbRow|null
     */
    public function fetchRow($result);

    /**
     * @param DbValues $params
     * @param string $table
     * @return DbQueryResult
     */
    public function insert($table, $params);

    /**
     * @param string $table
     * @param DbValues $params
     * @param string $message
     * @return DBmysqlResult
     */
    public function insertOrDie($table, $params, $message = '');

    /**
     * @param string $table
     * @param DbValues $params
     * @param DbWhere $where
     * @param DbJoins $joins
     * @return DbQueryResult
     */
    public function update($table, $params, $where, array $joins = []);

    /**
     * @param string $table
     * @param DbWhere $where
     * @param DbJoins $joins
     * @return DbQueryResult
     */
    public function delete($table, $where, array $joins = []);

    /**
     * @param string $table
     * @param DbWhere $where
     * @param string $message
     * @param DbJoins $joins
     * @return DBmysqlResult
     */
    public function deleteOrDie($table, $where, $message = '', array $joins = []);

    /**
     * @return bool
     */
    public function beginTransaction();

    /**
     * @return bool
     */
    public function commit();

    /**
     * @return bool
     */
    public function rollBack();

    /**
     * @return bool
     */
    public function inTransaction();

    public function sqlLike(string $expression, string $pattern, bool $case_sensitive = true): string;

    public function sqlCastAsString(string $expression): string;
}
