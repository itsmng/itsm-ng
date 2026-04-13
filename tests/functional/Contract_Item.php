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

class Contract_Item extends DbTestCase
{
    public function testMaxLinksAllowedPreventsAdditionalAssociations()
    {
        $this->login();

        $contract = new \Contract();
        $contract_id = $contract->add([
           'name'              => 'contract-item-' . $this->getUniqueString(),
           'entities_id'       => 0,
           'max_links_allowed' => 1,
        ]);
        $this->integer((int)$contract_id)->isGreaterThan(0);

        $computer = getItemByTypeName('Computer', '_test_pc01');
        $this->object($computer)->isInstanceOf('\Computer');

        $computer_2 = new \Computer();
        $computer_2_id = $computer_2->add([
           'name'        => 'contract-item-computer-' . $this->getUniqueString(),
           'entities_id' => 0,
        ]);
        $this->integer((int)$computer_2_id)->isGreaterThan(0);

        $relation = new \Contract_Item();
        $first_id = $relation->add([
           'contracts_id' => $contract_id,
           'itemtype'     => 'Computer',
           'items_id'     => $computer->getID(),
        ]);
        $this->integer((int)$first_id)->isGreaterThan(0);

        $second_id = $relation->add([
           'contracts_id' => $contract_id,
           'itemtype'     => 'Computer',
           'items_id'     => $computer_2_id,
        ]);
        $this->integer((int)$second_id)->isEqualTo(0);
        $this->integer((int)countElementsInTable(
            \Contract_Item::getTable(),
            ['contracts_id' => $contract_id]
        ))->isEqualTo(1);
    }
}
