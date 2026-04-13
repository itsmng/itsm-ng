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

class ChangeTask extends DbTestCase
{
    private function getNewChange(): int
    {
        $change = new \Change();
        $changes_id = $change->add([
           'name'        => 'change task reference',
           'content'     => 'reference change for task tests',
           'entities_id' => getItemByTypeName('Entity', '_test_root_entity', true),
        ]);

        $this->integer((int)$changes_id)->isGreaterThan(0);

        return (int)$changes_id;
    }

    public function testSchedulingAndRecall()
    {
        $this->login();

        $changes_id = $this->getNewChange();
        $users_id_tech = getItemByTypeName('User', TU_USER, true);

        $date_begin = new \DateTime();
        $date_begin_string = $date_begin->format('Y-m-d H:i:s');

        $date_end = new \DateTime();
        $date_end->add(new \DateInterval('P2D'));
        $date_end_string = $date_end->format('Y-m-d H:i:s');

        $task = new \ChangeTask();
        $task_id = $task->add([
           'state'             => \Planning::TODO,
           'changes_id'        => $changes_id,
           'tasktemplates_id'  => 0,
           'taskcategories_id' => 0,
           'content'           => 'Change task with schedule and recall',
           'users_id_tech'     => $users_id_tech,
           'plan'              => [
              'begin' => $date_begin_string,
              'end'   => $date_end_string,
           ],
           '_planningrecall'   => [
              'before_time' => '14400',
              'itemtype'    => 'ChangeTask',
              'users_id'    => $users_id_tech,
              'field'       => 'begin',
           ],
        ]);

        $this->integer((int)$task_id)->isGreaterThan(0);
        $this->boolean($task->getFromDB($task_id))->isTrue();

        $recall = new \PlanningRecall();
        $when = date('Y-m-d H:i:s', strtotime((string)$task->fields['begin']) - 14400);
        $this->boolean(
            $recall->getFromDBByCrit([
               'before_time' => '14400',
               'itemtype'    => 'ChangeTask',
               'items_id'    => $task_id,
               'users_id'    => $users_id_tech,
               'when'        => $when,
            ])
        )->isTrue();
    }
}
