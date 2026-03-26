<?php

namespace itsmng\Database\Runtime;

interface DatabaseInterface
{
    public function getDbType(): string;

    public function connect($choice = null);

    public function quote($value, int $type = 2);

    public function query($query);

    public function queryOrDie($query, $message = '');

    public function prepare($query);

    public function request($tableorsql, $crit = "", $debug = false);

    public function tableExists($tablename, $usecache = true);

    public function listTables($table = 'glpi\_%', array $where = []);

    public function fetchAssoc($result);

    public function fetchRow($result);

    public function insert($table, $params);

    public function insertOrDie($table, $params, $message = '');

    public function update($table, $params, $where, array $joins = []);

    public function delete($table, $where, array $joins = []);

    public function deleteOrDie($table, $where, $message = '', array $joins = []);

    public function beginTransaction();

    public function commit();

    public function rollBack();

    public function inTransaction();

    public function sqlLike(string $expression, string $pattern, bool $case_sensitive = true): string;

    public function sqlCastAsString(string $expression): string;
}
