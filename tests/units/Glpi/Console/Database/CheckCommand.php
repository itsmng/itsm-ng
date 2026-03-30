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

namespace tests\units\Glpi\Console\Database;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CheckCommandTestDouble extends \Glpi\Console\Database\CheckCommand
{
    private array $schema_tables = [];

    private object $dialect;

    public function setDbForTest(object $db): void
    {
        $this->db = $db;
    }

    public function setSchemaTablesForTest(array $schema_tables): void
    {
        $this->schema_tables = $schema_tables;
    }

    public function setDialectForTest(object $dialect): void
    {
        $this->dialect = $dialect;
    }

    public function runForTest(): int
    {
        return $this->execute(new ArrayInput([]), new BufferedOutput());
    }

    public function getBufferedOutput(): string
    {
        $output = new BufferedOutput();
        $this->execute(new ArrayInput([]), $output);

        return $output->fetch();
    }

    protected function initDbConnection()
    {
    }

    protected function getSchemaTables(): array
    {
        return $this->schema_tables;
    }

    protected function resolveDialect()
    {
        return $this->dialect;
    }
}

class CheckCommand extends \GLPITestCase
{
    public function testExecuteReportsMissingIndexes()
    {
        $db = new class () {
            public function getTableSchema($table, $structure = null): array
            {
                $expected = [
                    'schema' => 'create table "glpi_test" ("id" integer not null)',
                    'index'  => ['create index "glpi_test_name" on "glpi_test" ("name")'],
                ];

                if ($structure !== null) {
                    return $expected;
                }

                return [
                    'schema' => $expected['schema'],
                    'index'  => [],
                ];
            }
        };

        $dialect = new class () {
            public function createTableStatements(array $table): array
            {
                return $table['statements'];
            }
        };

        $command = new CheckCommandTestDouble();
        $command->setDbForTest($db);
        $command->setDialectForTest($dialect);
        $command->setSchemaTablesForTest([
            [
                'name'       => 'glpi_test',
                'statements' => [
                    'CREATE TABLE "glpi_test" ("id" integer NOT NULL)',
                    'CREATE INDEX "glpi_test_name" ON "glpi_test" ("name")',
                ],
            ],
        ]);

        $output = $command->getBufferedOutput();

        $this
           ->integer($command->runForTest())
              ->isIdenticalTo(0)
           ->boolean(str_contains($output, 'Table schema differs for table "glpi_test".'))
              ->isTrue()
           ->boolean(str_contains($output, 'glpi_test_name'))
              ->isTrue();
    }
}
