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

class ProjectTaskTemplate extends DbTestCase
{
    public function testCrudWithStateAndType()
    {
        $this->login();

        $state = new \ProjectState();
        $state_id = $state->add([
           'name' => 'project-task-template-state-' . $this->getUniqueString(),
        ]);
        $this->integer((int)$state_id)->isGreaterThan(0);

        $type = new \ProjectTaskType();
        $type_id = $type->add([
           'name' => 'project-task-template-type-' . $this->getUniqueString(),
        ]);
        $this->integer((int)$type_id)->isGreaterThan(0);

        $obj = new \ProjectTaskTemplate();
        $id = $obj->add([
           'name'                => 'project-task-template-' . $this->getUniqueString(),
           'projectstates_id'    => $state_id,
           'projecttasktypes_id' => $type_id,
           'percent_done'        => 15,
           'is_milestone'        => 1,
        ]);
        $this->integer((int)$id)->isGreaterThan(0);
        $this->boolean($obj->getFromDB($id))->isTrue();
        $this->integer((int)$obj->getField('projectstates_id'))->isEqualTo($state_id);
        $this->integer((int)$obj->getField('projecttasktypes_id'))->isEqualTo($type_id);

        $this->boolean($obj->update([
           'id'                  => $id,
           'percent_done'        => 90,
           'is_milestone'        => 0,
        ]))->isTrue();
        $this->boolean($obj->getFromDB($id))->isTrue();
        $this->integer((int)$obj->getField('percent_done'))->isEqualTo(90);
        $this->integer((int)$obj->getField('is_milestone'))->isEqualTo(0);

        $this->boolean($obj->delete(['id' => $id]))->isTrue();
    }
}
