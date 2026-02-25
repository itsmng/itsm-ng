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

/* Test for inc/change.class.php */

class Change extends DbTestCase
{
    public function testAddFromItem()
    {
        // add change from a computer
        $computer   = getItemByTypeName('Computer', '_test_pc01');
        $change     = new \Change();
        $changes_id = $change->add([
           'name'           => "test add from computer \'_test_pc01\'",
           'content'        => "test add from computer \'_test_pc01\'",
           '_add_from_item' => true,
           '_from_itemtype' => 'Computer',
           '_from_items_id' => $computer->getID(),
        ]);
        $this->integer($changes_id)->isGreaterThan(0);
        $this->boolean($change->getFromDB($changes_id))->isTrue();

        // check relation
        $change_item = new \Change_Item();
        $this->boolean($change_item->getFromDBForItems($change, $computer))->isTrue();
    }

    public function testChangeValidationWithMultipleValidators()
    {
        $this->login();

        $change = new \Change();
        $changes_id = $change->add([
           'name'    => 'change with multi validators',
           'content' => 'validation workflow',
        ]);
        $this->integer($changes_id)->isGreaterThan(0);

        $users_id_itsm = (int)getItemByTypeName('User', 'itsm', true);
        $users_id_tech = (int)getItemByTypeName('User', 'tech', true);

        $validation = new \ChangeValidation();
        $validation_id = $validation->add([
           'changes_id'         => $changes_id,
           'users_id_validate'  => [$users_id_itsm, $users_id_tech],
           'comment_submission' => 'Please validate this change',
        ]);
        $this->integer($validation_id)->isGreaterThan(0);

        $this->integer(countElementsInTable(
            \ChangeValidation::getTable(),
            ['changes_id' => $changes_id]
        ))->isEqualTo(2);

        $this->boolean($change->getFromDB($changes_id))->isTrue();
        $this->integer((int)$change->fields['global_validation'])->isEqualTo(\CommonITILValidation::WAITING);

        $this->login('itsm', 'itsm');
        $validation = new \ChangeValidation();
        $this->boolean(
            $validation->getFromDBByCrit([
               'changes_id'         => $changes_id,
               'users_id_validate'  => $users_id_itsm,
            ])
        )->isTrue();

        $this->boolean(
            $validation->update([
               'id'                 => $validation->fields['id'],
               'changes_id'         => $changes_id,
               'status'             => \CommonITILValidation::REFUSED,
               'comment_validation' => 'Needs rework',
            ])
        )->isTrue();

        $change = new \Change();
        $this->boolean($change->getFromDB($changes_id))->isTrue();
        $this->integer((int)$change->fields['global_validation'])->isEqualTo(\CommonITILValidation::REFUSED);
    }

    public function testStatusTransitionLifecycle()
    {
        $this->login();

        $allowed_path = [
            [\Change::INCOMING, \Change::EVALUATION],
            [\Change::EVALUATION, \Change::APPROVAL],
            [\Change::APPROVAL, \Change::ACCEPTED],
            [\Change::ACCEPTED, \Change::TEST],
            [\Change::TEST, \Change::QUALIFICATION],
            [\Change::QUALIFICATION, \Change::SOLVED],
            [\Change::SOLVED, \Change::CLOSED],
        ];

        foreach ($allowed_path as [$from, $to]) {
            $this->boolean(\Change::isAllowedStatus($from, $to))->isTrue();
        }
    }

    public function testChangeTaskActiontimeAndPrivateFlag()
    {
        $this->login();

        $change = new \Change();
        $changes_id = $change->add([
           'name'    => 'change with task actiontime',
           'content' => 'validate actiontime update from ChangeTask',
        ]);
        $this->integer($changes_id)->isGreaterThan(0);

        $task = new \ChangeTask();
        $task_id = $task->add([
           'changes_id'       => $changes_id,
           'content'          => 'private task',
           'actiontime'       => 180,
           'is_private'       => 1,
           'users_id_tech'    => getItemByTypeName('User', 'tech', true),
        ]);
        $this->integer((int)$task_id)->isGreaterThan(0);
        $this->boolean($task->getFromDB($task_id))->isTrue();
        $this->integer((int)$task->fields['is_private'])->isEqualTo(1);

        $this->boolean($change->getFromDB($changes_id))->isTrue();
        $this->integer((int)$change->fields['actiontime'])->isEqualTo(180);

        $this->boolean($task->delete(['id' => $task_id]))->isTrue();
        $this->boolean($change->getFromDB($changes_id))->isTrue();
        $this->integer((int)$change->fields['actiontime'])->isEqualTo(0);
    }
}
