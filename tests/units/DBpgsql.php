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
}
