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

class Datacenter extends DbTestCase
{
    public function testDatacenterRoomRackAndEnclosureRelations()
    {
        $this->login();
        $this->setEntity(0, true);

        $locations_id = getItemByTypeName('Location', '_location01', true);

        $datacenter = new \Datacenter();
        $datacenters_id = (int)$datacenter->add([
            'name'         => 'dc-' . $this->getUniqueString(),
            'locations_id' => $locations_id,
        ]);
        $this->integer($datacenters_id)->isGreaterThan(0);

        $room = new \DCRoom();
        $dcrooms_id = (int)$room->add([
            'name'           => 'room-' . $this->getUniqueString(),
            'datacenters_id' => $datacenters_id,
            'locations_id'   => $locations_id,
            'vis_cols'       => 2,
            'vis_rows'       => 2,
        ]);
        $this->integer($dcrooms_id)->isGreaterThan(0);
        $this->integer((int)countElementsInTable(\DCRoom::getTable(), ['datacenters_id' => $datacenters_id]))->isEqualTo(1);

        $rack = new \Rack();
        $racks_id = (int)$rack->add([
            'name'         => 'rack-' . $this->getUniqueString(),
            'entities_id'  => 0,
            'dcrooms_id'   => $dcrooms_id,
            'number_units' => 10,
            'position'     => 1,
        ]);
        $this->integer($racks_id)->isGreaterThan(0);
        $this->boolean($rack->getFromDB($racks_id))->isTrue();
        $this->integer((int)$rack->getField('dcrooms_id'))->isEqualTo($dcrooms_id);

        $enclosure = new \Enclosure();
        $enclosures_id = (int)$enclosure->add([
            'name'        => 'enclosure-' . $this->getUniqueString(),
            'entities_id' => 0,
        ]);
        $this->integer($enclosures_id)->isGreaterThan(0);

        $computer = new \Computer();
        $computers_id = (int)$computer->add([
            'name'        => 'rack-comp-' . $this->getUniqueString(),
            'entities_id' => 0,
        ]);
        $this->integer($computers_id)->isGreaterThan(0);

        $item_enclosure = new \Item_Enclosure();
        $item_enclosure_id = (int)$item_enclosure->add([
            'enclosures_id' => $enclosures_id,
            'itemtype'      => 'Computer',
            'items_id'      => $computers_id,
            'position'      => 1,
        ]);
        $this->integer($item_enclosure_id)->isGreaterThan(0);
        $this->integer((int)countElementsInTable(\Item_Enclosure::getTable(), ['enclosures_id' => $enclosures_id]))->isEqualTo(1);

        $item_rack = new \Item_Rack();
        $item_rack_id = (int)$item_rack->add([
            'racks_id' => $racks_id,
            'position' => 1,
            'itemtype' => 'Computer',
            'items_id' => $computers_id,
        ]);
        $this->integer($item_rack_id)->isGreaterThan(0);
        $this->integer((int)countElementsInTable(\Item_Rack::getTable(), ['racks_id' => $racks_id]))->isEqualTo(1);

        $this->boolean($enclosure->delete(['id' => $enclosures_id], 1))->isTrue();
        $this->integer((int)countElementsInTable(\Item_Enclosure::getTable(), ['enclosures_id' => $enclosures_id]))->isEqualTo(0);

        $this->boolean($rack->delete(['id' => $racks_id], 1))->isTrue();
        $this->integer((int)countElementsInTable(\Item_Rack::getTable(), ['racks_id' => $racks_id]))->isEqualTo(0);
    }
}
