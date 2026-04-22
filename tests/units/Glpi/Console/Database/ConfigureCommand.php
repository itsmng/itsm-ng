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

class ConfigureCommandConnectionDouble extends \DBmysql
{
    public $connected = true;

    public function __construct(
        bool $connected = true
    ) {
        $this->connected = $connected;
    }
}

class ConfigureCommandTestDouble extends \Glpi\Console\Database\AbstractConfigureCommand
{
    private array $connections = [];

    public array $requested_databases = [];

    public function queueConnection(\DBmysql $connection): void
    {
        $this->connections[] = $connection;
    }

    public function pickManagementConnection(string $db_type, string $db_name): \DBmysql
    {
        return $this->createDatabaseManagementConnection(
            $db_type,
            'localhost',
            'glpi',
            'secret',
            $db_name
        );
    }

    protected function createDatabaseConnection(
        string $db_type,
        string $db_hostport,
        string $db_user,
        string $db_pass,
        string $db_name
    ): \DBmysql {
        $this->requested_databases[] = $db_name;

        return array_shift($this->connections);
    }
}

class ConfigureCommand extends \GLPITestCase
{
    public function testCreateDatabaseManagementConnectionUsesTargetDatabaseForPostgreSql()
    {
        $command = new ConfigureCommandTestDouble();
        $target_connection = new ConfigureCommandConnectionDouble(true);
        $command->queueConnection($target_connection);

        $this
            ->object($command->pickManagementConnection('pgsql', 'glpi'))
                ->isIdenticalTo($target_connection)
            ->array($command->requested_databases)
                ->isIdenticalTo(['glpi']);
    }

    public function testCreateDatabaseManagementConnectionFallsBackToAdminDatabaseForPostgreSql()
    {
        $command = new ConfigureCommandTestDouble();
        $target_connection = new ConfigureCommandConnectionDouble(false);
        $admin_connection = new ConfigureCommandConnectionDouble(true);
        $command->queueConnection($target_connection);
        $command->queueConnection($admin_connection);

        $this
            ->object($command->pickManagementConnection('pgsql', 'glpi'))
                ->isIdenticalTo($admin_connection)
            ->array($command->requested_databases)
                ->isIdenticalTo(['glpi', 'postgres']);
    }
}
