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

class DeviceGeneric extends DbTestCase
{
    public function testAdditionalDeviceClassesCrud()
    {
        $previous_error_reporting = error_reporting();
        error_reporting($previous_error_reporting & ~E_DEPRECATED & ~E_USER_DEPRECATED);

        $this->login();

        $device_types = [
            'DeviceProcessor',
            'DeviceGraphicCard',
            'DeviceHardDrive',
            'DeviceFirmware',
            'DeviceBattery',
            'DeviceControl',
            'DeviceDrive',
            'DevicePowerSupply',
            'DeviceSoundCard',
            'DeviceMotherboard',
            'DeviceCase',
            'DeviceGeneric',
        ];

        try {
            foreach ($device_types as $device_type) {
                $device_class = '\\' . $device_type;
                $obj = new $device_class();
                $designation = strtolower($device_type) . '-' . $this->getUniqueString();

                $id = $obj->add(['designation' => $designation]);
                $this->integer((int)$id)->isGreaterThan(0);
                $this->boolean($obj->getFromDB($id))->isTrue();

                $this->boolean(
                    $obj->update([
                        'id'          => $id,
                        'designation' => $designation . '-updated',
                    ])
                )->isTrue();

                $this->boolean($obj->delete(['id' => $id]))->isTrue();
            }
        } finally {
            error_reporting($previous_error_reporting);
        }
    }
}
