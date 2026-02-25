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

class DeviceNetworkCard extends DbTestCase
{
    public function testImportUsesDesignationAndBandwidth()
    {
        $this->login();

        $obj = new \DeviceNetworkCard();
        $designation = 'nic-' . $this->getUniqueString();

        $id_1 = $obj->import([
            'designation' => $designation,
            'bandwidth'   => '1000',
        ]);
        $this->integer((int)$id_1)->isGreaterThan(0);

        $id_2 = $obj->import([
            'designation' => $designation,
            'bandwidth'   => '1000',
        ]);
        $this->integer((int)$id_2)->isEqualTo((int)$id_1);

        $id_3 = $obj->import([
            'designation' => $designation,
            'bandwidth'   => '100',
        ]);
        $this->integer((int)$id_3)->isGreaterThan(0);
        $this->integer((int)$id_3)->isNotEqualTo((int)$id_1);
    }
}

