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

namespace tests\units\itsmng\Database\Migrations;

class MigrationRunner extends \GLPITestCase
{
    public function testRollbackLatestBatchIgnoresSyntheticBaseline()
    {
        $this->mockGenerator->orphanize('__construct');

        $db = new \mock\DBmysql();
        $repository = new \mock\itsmng\Database\Migrations\MigrationRepository('/tmp');
        $history = new \mock\itsmng\Database\Migrations\MigrationHistoryRepository($db);

        $this->calling($history)->latestBatchMigrations = [[
            'version'   => '2.1.3',
            'migration' => \itsmng\Database\Migrations\MigrationHistoryRepository::BASELINE_MIGRATION,
            'batch'     => 1,
        ]];
        $this->calling($history)->isBaselineMigration = true;

        $runner = new \itsmng\Database\Migrations\MigrationRunner($db, $repository, $history);

        $this->array($runner->rollbackLatestBatch())->isIdenticalTo([]);
    }
}
