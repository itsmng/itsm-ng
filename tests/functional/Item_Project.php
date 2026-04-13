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

use DbTestCase;

class Item_Project extends DbTestCase
{
    public function testDuplicateLinkIsRejected()
    {
        $this->login();

        $project = new \Project();
        $project_id = $project->add([
           'name' => 'item-project-' . $this->getUniqueString(),
        ]);
        $this->integer((int)$project_id)->isGreaterThan(0);

        $computer = getItemByTypeName('Computer', '_test_pc01');
        $this->object($computer)->isInstanceOf('\Computer');

        $obj = new \Item_Project();
        $first_id = $obj->add([
           'projects_id' => $project_id,
           'itemtype'    => 'Computer',
           'items_id'    => $computer->getID(),
        ]);
        $this->integer((int)$first_id)->isGreaterThan(0);

        $duplicate_id = $obj->add([
           'projects_id' => $project_id,
           'itemtype'    => 'Computer',
           'items_id'    => $computer->getID(),
        ]);
        $this->integer((int)$duplicate_id)->isEqualTo(0);
        $this->integer((int)countElementsInTable(
            \Item_Project::getTable(),
            [
                'projects_id' => $project_id,
                'itemtype'    => 'Computer',
                'items_id'    => $computer->getID(),
            ]
        ))->isEqualTo(1);
    }
}
