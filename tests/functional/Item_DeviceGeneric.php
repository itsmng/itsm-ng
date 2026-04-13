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

class Item_DeviceGeneric extends DbTestCase
{
    public function testAdditionalItemDeviceLinksCrud()
    {
        $previous_error_reporting = error_reporting();
        error_reporting($previous_error_reporting & ~E_DEPRECATED & ~E_USER_DEPRECATED);

        $this->login();

        $computer = getItemByTypeName('Computer', '_test_pc01');
        $this->object($computer)->isInstanceOf('\Computer');

        $link_types = [
            'Item_DeviceProcessor',
            'Item_DeviceHardDrive',
            'Item_DeviceNetworkCard',
            'Item_DeviceBattery',
            'Item_DeviceControl',
            'Item_DeviceFirmware',
            'Item_DeviceGraphicCard',
            'Item_DevicePowerSupply',
            'Item_DeviceSoundCard',
            'Item_DeviceMotherboard',
            'Item_DeviceCase',
            'Item_DeviceDrive',
            'Item_DeviceGeneric',
        ];

        try {
            foreach ($link_types as $link_type) {
                $link_class = '\\' . $link_type;
                $link = new $link_class();
                $device_type = $link_type::getDeviceType();
                $device_fk = $link_type::getDeviceForeignKey();
                $device_class = '\\' . $device_type;
                $device = new $device_class();
                $device_id = $device->add([
                    'designation' => strtolower($device_type) . '-' . $this->getUniqueString(),
                ]);

                $this->integer((int)$device_id)->isGreaterThan(0);

                $id = $link->add([
                    'itemtype'    => 'Computer',
                    'items_id'    => $computer->getID(),
                    $device_fk    => $device_id,
                    'entities_id' => 0,
                ]);
                $this->integer((int)$id)->isGreaterThan(0);
                $this->boolean($link->getFromDB($id))->isTrue();

                $this->boolean($link->delete(['id' => $id]))->isTrue();
            }
        } finally {
            error_reporting($previous_error_reporting);
        }
    }

    public function testAddDevicesFromPOSTAndUpdateAll()
    {
        $previous_error_reporting = error_reporting();
        error_reporting($previous_error_reporting & ~E_DEPRECATED & ~E_USER_DEPRECATED);

        $this->login();

        try {
            $source_computer = getItemByTypeName('Computer', '_test_pc01');
            $target_computer = getItemByTypeName('Computer', '_test_pc02');
            $this->object($source_computer)->isInstanceOf('\Computer');
            $this->object($target_computer)->isInstanceOf('\Computer');

            $device = new \DeviceMemory();
            $device_id = $device->add([
                'designation'  => 'memory-' . $this->getUniqueString(),
                'size_default' => 2048,
            ]);
            $this->integer((int)$device_id)->isGreaterThan(0);

            $link = new \Item_DeviceMemory();
            $initial_link_id = $link->add([
                'itemtype'          => 'Computer',
                'items_id'          => $source_computer->getID(),
                'devicememories_id' => $device_id,
                'entities_id'       => 0,
            ]);
            $this->integer((int)$initial_link_id)->isGreaterThan(0);

            $link_selection_key = \Item_DeviceMemory::getForeignKeyField();
            $_POST = ['devices_id' => $device_id];
            \Item_Devices::addDevicesFromPOST([
                'devicetype'          => 'DeviceMemory',
                'itemtype'            => 'Computer',
                'items_id'            => $target_computer->getID(),
                $link_selection_key   => [$initial_link_id],
            ]);

            $this->boolean($link->getFromDB($initial_link_id))->isTrue();
            $this->integer((int)$link->getField('items_id'))->isEqualTo((int)$target_computer->getID());

            $count_before_update = count($link->find([
                'itemtype'          => 'Computer',
                'items_id'          => $target_computer->getID(),
                'devicememories_id' => $device_id,
                'is_deleted'        => 0,
            ]));

            $_POST = [
                'itemtype'                                      => 'Computer',
                'items_id'                                      => $target_computer->getID(),
                'value_DeviceMemory_' . $initial_link_id . '_size' => 8192,
            ];
            \Item_Devices::updateAll($_POST);
            $_POST = [];

            $links_after_update = $link->find([
                'itemtype'          => 'Computer',
                'items_id'          => $target_computer->getID(),
                'devicememories_id' => $device_id,
                'is_deleted'        => 0,
            ]);
            $this->integer(count($links_after_update))->isEqualTo($count_before_update);

            $this->boolean($link->getFromDB($initial_link_id))->isTrue();
            $this->integer((int)$link->getField('size'))->isEqualTo(8192);
        } finally {
            $_POST = [];
            error_reporting($previous_error_reporting);
        }
    }
}
