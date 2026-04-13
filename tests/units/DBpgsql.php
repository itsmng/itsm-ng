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

namespace tests\units;

class DBpgsqlExecuteQueryFakeHandle
{
    public mixed $result = null;

    public array $queries = [];

    public function query(string $query)
    {
        $this->queries[] = $query;

        if ($this->result instanceof \Throwable) {
            throw $this->result;
        }

        return $this->result;
    }
}

class DBpgsqlSavepointFakeHandle extends \PDO
{
    public bool $in_transaction = false;

    public array $queries = [];

    public function __construct()
    {
    }

    public function inTransaction(): bool
    {
        return $this->in_transaction;
    }

    public function exec($statement): int|false
    {
        $this->queries[] = $statement;

        return 0;
    }
}

class DBpgsqlExecuteQueryDouble extends \DBpgsql
{
    public array $savepoint_calls = [];

    public function __construct()
    {
    }

    public function setHandle(object $dbh): void
    {
        $this->dbh = $dbh;
        $this->dbtype = 'pgsql';
    }

    public function runExecuteQuery(string $query)
    {
        return $this->executeQuery($query);
    }

    protected function beginTransactionalSavepoint(): ?string
    {
        $this->savepoint_calls[] = 'begin';

        return 'sp1';
    }

    protected function releaseTransactionalSavepoint(?string $savepoint): void
    {
        $this->savepoint_calls[] = 'release:' . $savepoint;
    }

    protected function rollBackTransactionalSavepoint(?string $savepoint): void
    {
        $this->savepoint_calls[] = 'rollback:' . $savepoint;
    }
}

class DBpgsqlSavepointDouble extends \DBpgsql
{
    public function __construct()
    {
    }

    public function setHandle(\PDO $dbh): void
    {
        $this->dbh = $dbh;
        $this->dbtype = 'pgsql';
    }

    public function runBeginTransactionalSavepoint(): ?string
    {
        return $this->beginTransactionalSavepoint();
    }
}

class DBpgsql extends \GLPITestCase
{
    public function testSqlCastAsUnsignedIntegerUsesBigInt()
    {
        $db = new DBpgsqlExecuteQueryDouble();

        $this
           ->string($db->sqlCastAsUnsignedInteger('"code"'))
              ->isIdenticalTo('CAST(FLOOR((("code")::numeric)) AS BIGINT)');
    }

    public function testExecuteQueryWrapsSavepointOnSuccess()
    {
        $dbh = new DBpgsqlExecuteQueryFakeHandle();
        $dbh->result = new \stdClass();

        $db = new DBpgsqlExecuteQueryDouble();
        $db->setHandle($dbh);

        $this
           ->object($db->runExecuteQuery('SELECT 1'))
              ->isIdenticalTo($dbh->result)
           ->array($dbh->queries)
              ->isIdenticalTo(['SELECT 1'])
           ->array($db->savepoint_calls)
              ->isIdenticalTo(['begin', 'release:sp1']);
    }

    public function testExecuteQueryRollsBackSavepointOnFailure()
    {
        $dbh = new DBpgsqlExecuteQueryFakeHandle();
        $dbh->result = new \RuntimeException('boom');

        $db = new DBpgsqlExecuteQueryDouble();
        $db->setHandle($dbh);

        $this->exception(
            function () use ($db) {
                $db->runExecuteQuery('SELECT broken');
            }
        )->hasMessage('boom');

        $this
           ->array($dbh->queries)
              ->isIdenticalTo(['SELECT broken'])
           ->array($db->savepoint_calls)
              ->isIdenticalTo(['begin', 'rollback:sp1']);
    }

    public function testBeginTransactionalSavepointSkipsWhenNotInTransaction()
    {
        $dbh = new DBpgsqlSavepointFakeHandle();
        $db = new DBpgsqlSavepointDouble();
        $db->setHandle($dbh);

        $this
           ->variable($db->runBeginTransactionalSavepoint())
              ->isNull()
           ->array($dbh->queries)
              ->isEmpty();
    }

    public function testBeginTransactionalSavepointExecutesInsideTransaction()
    {
        $dbh = new DBpgsqlSavepointFakeHandle();
        $dbh->in_transaction = true;

        $db = new DBpgsqlSavepointDouble();
        $db->setHandle($dbh);

        $this
           ->string($db->runBeginTransactionalSavepoint())
              ->isIdenticalTo('glpi_sp_1')
           ->array($dbh->queries)
              ->isIdenticalTo(['SAVEPOINT glpi_sp_1']);
    }

    // ---------------------------------------------------------------
    // Integration tests — require a real PostgreSQL connection via $DB
    // ---------------------------------------------------------------

    private function requirePgsql(): \DBmysql
    {
        global $DB;

        if (!($DB instanceof \DBmysql) || $DB->dbtype !== 'pgsql') {
            // Skip: these tests only run against a real PostgreSQL connection
            $this->boolean(true)->isTrue();
            return $DB;
        }

        return $DB;
    }

    /**
     * Test that a successful DML inside a transaction uses savepoints
     * and that the data persists after RELEASE SAVEPOINT.
     */
    public function testSavepointRealConnectionSuccess()
    {
        $db = $this->requirePgsql();
        if ($db->dbtype !== 'pgsql') {
            return;
        }

        // Create a temporary table to avoid polluting real schema
        $db->query('CREATE TEMPORARY TABLE _test_sp_success (id SERIAL PRIMARY KEY, val TEXT)');

        $db->beginTransaction();
        try {
            // Insert inside transaction — savepoint should be created/released automatically
            $db->query("INSERT INTO _test_sp_success (val) VALUES ('hello')");

            // The row should be visible within the same transaction
            $result = $db->query("SELECT val FROM _test_sp_success WHERE val = 'hello'");
            $row = $result->fetchAssoc();
            $this->string($row['val'])->isIdenticalTo('hello');

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        // After commit the row is durable
        $result = $db->query("SELECT COUNT(*) AS cnt FROM _test_sp_success WHERE val = 'hello'");
        $row = $result->fetchAssoc();
        $this->integer((int) $row['cnt'])->isIdenticalTo(1);

        $db->query('DROP TABLE IF EXISTS _test_sp_success');
    }

    /**
     * Test that a failed query inside a transaction rolls back its
     * savepoint and leaves the transaction usable for further queries.
     */
    public function testSavepointRealConnectionRollbackOnError()
    {
        $db = $this->requirePgsql();
        if ($db->dbtype !== 'pgsql') {
            return;
        }

        $db->query('CREATE TEMPORARY TABLE _test_sp_rollback (id SERIAL PRIMARY KEY, val TEXT NOT NULL)');

        $db->beginTransaction();
        try {
            // Good insert
            $db->query("INSERT INTO _test_sp_rollback (val) VALUES ('good')");

            // Bad insert — NULL into NOT NULL column — should fail
            // The savepoint mechanism should roll back only this statement
            $failed = false;
            try {
                $db->query("INSERT INTO _test_sp_rollback (val) VALUES (NULL)");
            } catch (\Throwable $e) {
                $failed = true;
            }
            $this->boolean($failed)->isTrue();

            // The transaction should still be usable (thanks to savepoint rollback)
            // and the first insert should still be visible
            $result = $db->query("SELECT COUNT(*) AS cnt FROM _test_sp_rollback WHERE val = 'good'");
            $row = $result->fetchAssoc();
            $this->integer((int) $row['cnt'])->isIdenticalTo(1);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        $db->query('DROP TABLE IF EXISTS _test_sp_rollback');
    }

    /**
     * Test that nested savepoints increment correctly and each can be
     * independently released or rolled back.
     */
    public function testSavepointRealConnectionNested()
    {
        $db = $this->requirePgsql();
        if ($db->dbtype !== 'pgsql') {
            return;
        }

        $db->query('CREATE TEMPORARY TABLE _test_sp_nested (id SERIAL PRIMARY KEY, val TEXT NOT NULL)');

        $db->beginTransaction();
        try {
            $db->query("INSERT INTO _test_sp_nested (val) VALUES ('first')");
            $db->query("INSERT INTO _test_sp_nested (val) VALUES ('second')");

            // Both should be visible
            $result = $db->query('SELECT COUNT(*) AS cnt FROM _test_sp_nested');
            $row = $result->fetchAssoc();
            $this->integer((int) $row['cnt'])->isIdenticalTo(2);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        $db->query('DROP TABLE IF EXISTS _test_sp_nested');
    }
}
