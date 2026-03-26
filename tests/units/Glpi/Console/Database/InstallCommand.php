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

class InstallCommandConnectionDouble extends \DBmysql
{
    public $connected = true;

    public function __construct(
        private bool $create_database_result = true,
        private int $table_count = 0
    ) {
    }

    public function createDatabase(string $database): bool
    {
        return $this->create_database_result;
    }

    public function listTables($table = 'glpi\_%', array $where = [])
    {
        return new class ($this->table_count) {
            public function __construct(private int $count)
            {
            }

            public function count(): int
            {
                return $this->count;
            }
        };
    }

    public function errno()
    {
        return 0;
    }

    public function error()
    {
        return '';
    }
}

class InstallCommandTestDouble extends \Glpi\Console\Database\InstallCommand
{
    private array $connections = [];

    public ?\Throwable $schema_throwable = null;

    public function queueConnection(\DBmysql $connection): void
    {
        $this->connections[] = $connection;
    }

    public function runForTest(array $options): array
    {
        $input = new ArrayInput($options, $this->getDefinition());
        $output = new BufferedOutput();

        return [
            $this->execute($input, $output),
            $output->fetch(),
        ];
    }

    protected function configureDatabase($input, $output)
    {
        return self::SUCCESS;
    }

    protected function createDatabaseConnection(
        string $db_type,
        string $db_hostport,
        string $db_user,
        string $db_pass,
        string $db_name
    ): \DBmysql {
        return array_shift($this->connections);
    }

    protected function ensureSecurityKey(): bool
    {
        return true;
    }

    protected function createSchema(string $default_language, \DBmysql $db_instance): void
    {
        if ($this->schema_throwable !== null) {
            throw $this->schema_throwable;
        }
    }
}

class InstallCommand extends \GLPITestCase
{
    public function testExecuteReturnsSchemaCreationFailureWhenSchemaCreationThrows()
    {
        $command = new InstallCommandTestDouble();
        $command->queueConnection(new InstallCommandConnectionDouble());
        $command->queueConnection(new InstallCommandConnectionDouble());
        $command->schema_throwable = new \RuntimeException('Errors occurred inserting default database: boom');

        [$code, $output] = $command->runForTest([
            '--db-name' => 'glpi',
            '--db-user' => 'glpi',
            '--db-password' => 'secret',
            '--db-type' => 'pgsql',
        ]);

        $this
           ->integer($code)
              ->isIdenticalTo(\Glpi\Console\Database\InstallCommand::ERROR_SCHEMA_CREATION_FAILED)
           ->boolean(str_contains($output, 'Errors occurred inserting default database: boom'))
              ->isTrue();
    }
}
