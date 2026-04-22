<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class DBpgsql extends DBmysql
{
    use \itsmng\Database\Runtime\PostgreSqlDatabaseTrait;
    public $dbtype = 'pgsql';
}
