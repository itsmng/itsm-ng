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

class ProjectTask_Ticket extends DbTestCase
{
    public function testGetTicketsTotalActionTime()
    {
        $this->login();

        $project = new \Project();
        $project_id = $project->add([
           'name' => 'projecttask-ticket-project-' . $this->getUniqueString(),
        ]);
        $this->integer((int)$project_id)->isGreaterThan(0);

        $project_task = new \ProjectTask();
        $task_id = $project_task->add([
           'name'                    => 'projecttask-ticket-task-' . $this->getUniqueString(),
           'projects_id'             => $project_id,
           'projecttasktemplates_id' => 0,
        ]);
        $this->integer((int)$task_id)->isGreaterThan(0);

        $ticket_1 = new \Ticket();
        $ticket_1_id = $ticket_1->add([
           'name'       => 'projecttask-ticket-1-' . $this->getUniqueString(),
           'content'    => 'content',
           'actiontime' => 120,
        ]);
        $this->integer((int)$ticket_1_id)->isGreaterThan(0);

        $ticket_2 = new \Ticket();
        $ticket_2_id = $ticket_2->add([
           'name'       => 'projecttask-ticket-2-' . $this->getUniqueString(),
           'content'    => 'content',
           'actiontime' => 60,
        ]);
        $this->integer((int)$ticket_2_id)->isGreaterThan(0);

        $relation = new \ProjectTask_Ticket();
        $this->integer((int)$relation->add([
           'projecttasks_id' => $task_id,
           'tickets_id'      => $ticket_1_id,
        ]))->isGreaterThan(0);
        $this->integer((int)$relation->add([
           'projecttasks_id' => $task_id,
           'tickets_id'      => $ticket_2_id,
        ]))->isGreaterThan(0);

        $this->integer((int)\ProjectTask_Ticket::getTicketsTotalActionTime($task_id))->isEqualTo(180);
    }
}
