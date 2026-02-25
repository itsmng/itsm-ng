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

/* Test for inc/problem.class.php */

class Problem extends DbTestCase
{
    public function testAddFromItem()
    {
        // add problem from a computer
        $computer   = getItemByTypeName('Computer', '_test_pc01');
        $problem     = new \Problem();
        $problems_id = $problem->add([
           'name'           => "test add from computer \'_test_pc01\'",
           'content'        => "test add from computer \'_test_pc01\'",
           '_add_from_item' => true,
           '_from_itemtype' => 'Computer',
           '_from_items_id' => $computer->getID(),
        ]);
        $this->integer($problems_id)->isGreaterThan(0);
        $this->boolean($problem->getFromDB($problems_id))->isTrue();

        // check relation
        $problem_item = new \Item_Problem();
        $this->boolean($problem_item->getFromDBForItems($problem, $computer))->isTrue();
    }

    public function testAssignedStatusOnAddWithAssignee()
    {
        $this->login();

        $users_id_assign = (int)getItemByTypeName('User', 'tech', true);
        $problem = new \Problem();
        $problems_id = $problem->add([
           'name'              => 'problem auto assigned',
           'content'           => 'assignment status should switch',
           'status'            => \Problem::INCOMING,
           '_users_id_assign'  => $users_id_assign,
        ]);
        $this->integer($problems_id)->isGreaterThan(0);
        $this->boolean($problem->getFromDB($problems_id))->isTrue();
        $this->integer((int)$problem->fields['status'])->isEqualTo(\Problem::ASSIGNED);
    }

    public function testReopenViaFollowup()
    {
        $this->login();

        $problem = new \Problem();
        $problems_id = $problem->add([
           'name'    => 'problem to reopen',
           'content' => 'initial content',
        ]);
        $this->integer($problems_id)->isGreaterThan(0);

        $this->boolean(
            $problem->update([
               'id'      => $problems_id,
               'status'  => \Problem::SOLVED,
            ])
        )->isTrue();
        $this->boolean($problem->getFromDB($problems_id))->isTrue();
        $this->integer((int)$problem->fields['status'])->isEqualTo(\Problem::SOLVED);

        $interface_bak = $_SESSION['glpiactiveprofile']['interface'] ?? null;
        $_SESSION['glpiactiveprofile']['interface'] = 'helpdesk';

        $followup = new \ITILFollowup();
        $followups_id = $followup->add([
           'itemtype'    => 'Problem',
           'items_id'    => $problems_id,
           'content'     => 'Need to reopen after review',
           'add_reopen'  => 1,
        ]);
        $this->integer($followups_id)->isGreaterThan(0);

        if ($interface_bak === null) {
            unset($_SESSION['glpiactiveprofile']['interface']);
        } else {
            $_SESSION['glpiactiveprofile']['interface'] = $interface_bak;
        }

        $this->boolean($problem->getFromDB($problems_id))->isTrue();
        $this->integer((int)$problem->fields['status'])->isEqualTo(\Problem::INCOMING);
    }

    public function testProblemTaskActiontimeAndPrivateFlag()
    {
        $this->login();

        $problem = new \Problem();
        $problems_id = $problem->add([
           'name'    => 'problem with task actiontime',
           'content' => 'validate actiontime update from ProblemTask',
        ]);
        $this->integer($problems_id)->isGreaterThan(0);

        $task = new \ProblemTask();
        $task_id = $task->add([
           'problems_id'      => $problems_id,
           'content'          => 'private problem task',
           'actiontime'       => 240,
           'is_private'       => 1,
           'users_id_tech'    => getItemByTypeName('User', 'tech', true),
        ]);
        $this->integer((int)$task_id)->isGreaterThan(0);
        $this->boolean($task->getFromDB($task_id))->isTrue();
        $this->integer((int)$task->fields['is_private'])->isEqualTo(1);

        $this->boolean($problem->getFromDB($problems_id))->isTrue();
        $this->integer((int)$problem->fields['actiontime'])->isEqualTo(240);

        $this->boolean($task->delete(['id' => $task_id]))->isTrue();
        $this->boolean($problem->getFromDB($problems_id))->isTrue();
        $this->integer((int)$problem->fields['actiontime'])->isEqualTo(0);
    }
}
