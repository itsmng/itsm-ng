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

class NetworkEquipment extends DbTestCase
{
    public function testCloneRelationsContainsExpectedLinks()
    {
        $obj = new \NetworkEquipment();
        $relations = $obj->getCloneRelations();

        $this->boolean(in_array(\NetworkPort::class, $relations, true))->isTrue();
        $this->boolean(in_array(\Contract_Item::class, $relations, true))->isTrue();
        $this->boolean(in_array(\Item_Devices::class, $relations, true))->isTrue();
    }

    public function testCanUnrecursReturnsFalseOnCrossEntityConnection()
    {
        $this->login();

        $root_entity = getItemByTypeName('Entity', '_test_root_entity', true);
        $child_entity = getItemByTypeName('Entity', '_test_child_1', true);

        $networkequipment = new \NetworkEquipment();
        $root_id = $networkequipment->add([
           'name'         => 'root-networkequipment',
           'entities_id'  => $root_entity,
           'is_recursive' => 1,
        ]);
        $this->integer((int)$root_id)->isGreaterThan(0);

        $child_networkequipment = new \NetworkEquipment();
        $child_id = $child_networkequipment->add([
           'name'         => 'child-networkequipment',
           'entities_id'  => $child_entity,
           'is_recursive' => 0,
        ]);
        $this->integer((int)$child_id)->isGreaterThan(0);

        $networkport = new \NetworkPort();
        $root_port_id = $networkport->add([
           'items_id'          => $root_id,
           'itemtype'          => 'NetworkEquipment',
           'entities_id'       => $root_entity,
           'logical_number'    => 1,
           'name'              => 'root-port',
        ]);
        $child_port_id = $networkport->add([
           'items_id'          => $child_id,
           'itemtype'          => 'NetworkEquipment',
           'entities_id'       => $child_entity,
           'logical_number'    => 1,
           'name'              => 'child-port',
        ]);
        $this->integer((int)$root_port_id)->isGreaterThan(0);
        $this->integer((int)$child_port_id)->isGreaterThan(0);

        $link = new \NetworkPort_NetworkPort();
        $link_id = $link->add([
           'networkports_id_1' => $root_port_id,
           'networkports_id_2' => $child_port_id,
        ]);
        $this->integer((int)$link_id)->isGreaterThan(0);

        $this->boolean($networkequipment->getFromDB($root_id))->isTrue();
        $this->boolean($networkequipment->canUnrecurs())->isFalse();
    }
}
