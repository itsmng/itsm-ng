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

/* Test for inc/consumable.class.php */

class Consumable extends \DbTestCase
{
    private function processMassiveAction($action, \CommonDBTM $item, array $ids, array $input = [])
    {
        $ok = 0;
        $ko = 0;

        $controller = new \atoum\atoum\mock\controller();
        $controller->__construct = function ($args) {
        };

        $ma = new \mock\MassiveAction([], [], '', false, $controller);
        $ma->getMockController()->getAction = $action;
        $ma->getMockController()->addMessage = function () {
        };
        $ma->getMockController()->getInput = $input;
        $ma->getMockController()->itemDone = function ($itemtype, $id, $res) use (&$ok, &$ko) {
            if ($res == \MassiveAction::ACTION_OK) {
                $ok++;
            } else {
                $ko++;
            }
        };

        \Consumable::processMassiveActionsForOneItemtype($ma, $item, $ids);

        return [$ok, $ko];
    }

    /**
     * Test "out" and "back to stock" functions.
     * Test "back" to stock whend linked user or group is deleted.
     */
    public function testOutAndBackToStock()
    {

        $consumable = new \Consumable();

        $consumable_item = new \ConsumableItem();
        $cu_id = (int)$consumable_item->add([
           'name' => 'Test consumable item'
        ]);
        $this->integer($cu_id)->isGreaterThan(0);

        $group = new \Group();
        $gid1 = (int)$group->add([
           'name' => 'Test group 1'
        ]);
        $this->integer($gid1)->isGreaterThan(0);
        $gid2 = (int)$group->add([
           'name' => 'Test group 2'
        ]);
        $this->integer($gid2)->isGreaterThan(0);

        $user = new \User();
        $uid = (int)$user->add([
           'name'    => 'User group',
           'picture' => ''
        ]);
        $this->integer($uid)->isGreaterThan(0);

        $c_ids = [];
        for ($i = 0; $i < 20; $i++) {
            $c_id = (int)$consumable->add([
               'name'               => 'Test consumable',
               'consumableitems_id' => $cu_id,
            ]);
            $this->integer($c_id)->isGreaterThan(0);

            $c_ids[] = $c_id;

            // Give 1/4 of consumable pool to test group 1
            if ($i % 4 === 0) {
                $consumable->out($c_id, 'Group', $gid1);
            }
            // Give 1/4 of consumable pool to test group 2
            if ($i % 4 === 1) {
                $consumable->out($c_id, 'Group', $gid2);
            }
            // Give 1/4 of consumable pool to test user
            if ($i % 4 === 2) {
                $consumable->out($c_id, 'User', $uid);
            }
        }

        // Test counters
        $this->integer($consumable->getTotalNumber($cu_id))->isEqualTo(20);
        $this->integer($consumable->getUnusedNumber($cu_id))->isEqualTo(5);
        $this->integer($consumable->getOldNumber($cu_id))->isEqualTo(15);

        // Test back to stock
        $this->boolean($consumable->backToStock(['id' => $c_ids[0]]))->isTrue();
        $this->integer($consumable->getUnusedNumber($cu_id))->isEqualTo(6);
        $this->integer($consumable->getOldNumber($cu_id))->isEqualTo(14);

        // Test forced back to stock by removal of group (not replaced)
        $this->boolean($group->delete(['id' => $gid1, true]))->isTrue();
        $this->integer($consumable->getUnusedNumber($cu_id))->isEqualTo(10);
        $this->integer($consumable->getOldNumber($cu_id))->isEqualTo(10);
        $this->integer(
            countElementsInTable(
                $consumable->getTable(),
                [
                    'consumableitems_id' => $cu_id,
                    'itemtype'           => 'Group',
                    'items_id'           => $gid1,
                 ]
            )
        )->isEqualTo(0);

        // Test replacement of a group (no back to stock)
        $this->boolean($group->delete(['id' => $gid2, '_replace_by' => $gid1], true))->isTrue();
        $this->integer($consumable->getUnusedNumber($cu_id))->isEqualTo(10);
        $this->integer($consumable->getOldNumber($cu_id))->isEqualTo(10);
        $this->integer(
            countElementsInTable(
                $consumable->getTable(),
                [
                    'consumableitems_id' => $cu_id,
                    'itemtype'           => 'Group',
                    'items_id'           => $gid2,
                 ]
            )
        )->isEqualTo(0);
        $this->integer(
            countElementsInTable(
                $consumable->getTable(),
                [
                    'consumableitems_id' => $cu_id,
                    'itemtype'           => 'Group',
                    'items_id'           => $gid1,
                 ]
            )
        )->isEqualTo(5);

        // Test forced back to stock by removal of user (not replaced)
        $this->boolean($user->delete(['id' => $uid], true))->isTrue();
        $this->integer($consumable->getUnusedNumber($cu_id))->isEqualTo(15);
        $this->integer($consumable->getOldNumber($cu_id))->isEqualTo(5);
        $this->integer(
            countElementsInTable(
                $consumable->getTable(),
                [
                    'consumableitems_id' => $cu_id,
                    'itemtype'           => 'User',
                    'items_id'           => $uid,
                 ]
            )
        )->isEqualTo(0);
    }

    public function testInfocomInheritance()
    {
        $consumable = new \Consumable();

        $consumable_item = new \ConsumableItem();
        $cu_id = (int) $consumable_item->add([
           'name' => 'Test consumable item'
        ]);
        $this->integer($cu_id)->isGreaterThan(0);

        $infocom = new \Infocom();
        $infocom_id = (int) $infocom->add([
           'itemtype'  => \ConsumableItem::getType(),
           'items_id'  => $cu_id,
           'buy_date'  => '2020-10-21',
           'value'     => '500'
        ]);
        $this->integer($infocom_id)->isGreaterThan(0);

        $consumable_id = $consumable->add([
           'consumableitems_id' => $cu_id
        ]);
        $this->integer($consumable_id)->isGreaterThan(0);

        $infocom2 = new \Infocom();
        $infocom2_id = (int) $infocom2->getFromDBByCrit([
           'itemtype'  => \Consumable::getType(),
           'items_id'  => $consumable_id
        ]);
        $this->integer($infocom2_id)->isGreaterThan(0);
        $this->string($infocom2->fields['buy_date'])->isEqualTo($infocom->fields['buy_date']);
        $this->string($infocom2->fields['value'])->isEqualTo($infocom->fields['value']);
    }

    public function testMassiveActionsGiveAndBackToStock()
    {
        $this->login();
        $this->setEntity(0, true);
        $old_right = $_SESSION['glpiactiveprofile']['consumable'] ?? null;
        $_SESSION['glpiactiveprofile']['consumable'] = 31;

        try {
            $consumable_item = new \ConsumableItem();
            $consumableitems_id = (int)$consumable_item->add([
                'name' => 'Massive consumable item ' . $this->getUniqueString(),
            ]);
            $this->integer($consumableitems_id)->isGreaterThan(0);

            $consumable = new \Consumable();
            $consumable_ids = [];
            foreach ([1, 2] as $i) {
                $id = (int)$consumable->add([
                    'name'               => 'Massive consumable ' . $i,
                    'consumableitems_id' => $consumableitems_id,
                ]);
                $this->integer($id)->isGreaterThan(0);
                $consumable_ids[] = $id;
            }

            $user = new \User();
            $users_id = (int)$user->add([
                'name'    => 'massive-consumable-user-' . $this->getUniqueString(),
                'picture' => '',
            ]);
            $this->integer($users_id)->isGreaterThan(0);

            [$ok, $ko] = $this->processMassiveAction(
                'give',
                $consumable,
                $consumable_ids,
                [
                    'give_itemtype' => 'User',
                    'give_items_id' => $users_id,
                ]
            );
            $this->integer($ok)->isEqualTo(2);
            $this->integer($ko)->isEqualTo(0);

            foreach ($consumable_ids as $id) {
                $this->boolean($consumable->getFromDB($id))->isTrue();
                $this->string($consumable->fields['itemtype'])->isEqualTo('User');
                $this->integer((int)$consumable->fields['items_id'])->isEqualTo($users_id);
                $this->string($consumable->fields['date_out'])->matches('#^\d{4}-\d{2}-\d{2}$#');
            }

            [$ok, $ko] = $this->processMassiveAction('backtostock', $consumable, $consumable_ids);
            $this->integer($ok)->isEqualTo(2);
            $this->integer($ko)->isEqualTo(0);

            foreach ($consumable_ids as $id) {
                $this->boolean($consumable->getFromDB($id))->isTrue();
                $this->variable($consumable->fields['date_out'])->isNull();
            }
        } finally {
            if ($old_right === null) {
                unset($_SESSION['glpiactiveprofile']['consumable']);
            } else {
                $_SESSION['glpiactiveprofile']['consumable'] = $old_right;
            }
        }
    }
}
