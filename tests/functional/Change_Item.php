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

class Change_Item extends DbTestCase
{
    private function getNewChange(): \Change
    {
        $change = new \Change();
        $changes_id = $change->add([
           'name'        => 'change item reference',
           'content'     => 'reference change for linked item tests',
           'entities_id' => getItemByTypeName('Entity', '_test_root_entity', true),
        ]);

        $this->integer((int)$changes_id)->isGreaterThan(0);
        $this->boolean($change->getFromDB($changes_id))->isTrue();

        return $change;
    }

    public function testPrepareInputForAddRejectsMissingChangeId()
    {
        $change_item = new \Change_Item();

        $this->boolean(
            $change_item->prepareInputForAdd([
               'itemtype' => 'Computer',
               'items_id' => getItemByTypeName('Computer', '_test_pc01', true),
            ])
        )->isFalse();
    }

    public function testPrepareInputForAddRejectsDuplicateRelation()
    {
        $this->login();

        $change = $this->getNewChange();
        $computer = getItemByTypeName('Computer', '_test_pc01');

        $change_item = new \Change_Item();
        $change_item_id = $change_item->add([
           'changes_id' => $change->getID(),
           'itemtype'   => 'Computer',
           'items_id'   => $computer->getID(),
        ]);

        $this->integer((int)$change_item_id)->isGreaterThan(0);
        $this->boolean($change_item->getFromDBForItems($change, $computer))->isTrue();

        $this->boolean(
            $change_item->prepareInputForAdd([
               'changes_id' => $change->getID(),
               'itemtype'   => 'Computer',
               'items_id'   => $computer->getID(),
            ])
        )->isFalse();
    }
}
