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

class ProjectTaskTeam extends DbTestCase
{
    public function testGetTeamForReturnsAvailableTypes()
    {
        $this->login();

        $project = new \Project();
        $project_id = $project->add([
           'name' => 'project-task-team-project-' . $this->getUniqueString(),
        ]);
        $this->integer((int)$project_id)->isGreaterThan(0);

        $project_task = new \ProjectTask();
        $task_id = $project_task->add([
           'name'                    => 'project-task-team-task-' . $this->getUniqueString(),
           'projects_id'             => $project_id,
           'projecttasktemplates_id' => 0,
        ]);
        $this->integer((int)$task_id)->isGreaterThan(0);

        $user_id = getItemByTypeName('User', 'tech', true);
        $this->integer((int)$user_id)->isGreaterThan(0);

        $relation = new \ProjectTaskTeam();
        $relation_id = $relation->add([
           'projecttasks_id' => $task_id,
           'itemtype'        => 'User',
           'items_id'        => $user_id,
        ]);
        $this->integer((int)$relation_id)->isGreaterThan(0);

        $team = \ProjectTaskTeam::getTeamFor($task_id);
        $this->array($team)->hasKey('User')->hasKey('Group')->hasKey('Supplier')->hasKey('Contact');
        $this->integer((int)count($team['User']))->isEqualTo(1);
        $this->string($team['User'][0]['itemtype'])->isEqualTo('User');
        $this->integer((int)$team['User'][0]['items_id'])->isEqualTo($user_id);
    }
}
