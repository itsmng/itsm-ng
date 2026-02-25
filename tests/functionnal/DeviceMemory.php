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

class DeviceMemory extends DbTestCase
{
    private $method;

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        //to handle GLPI barbarian replacements.
        $this->method = str_replace(
            ['\\', 'beforeTestMethod'],
            ['', $method],
            __METHOD__
        );
    }

    public function testCrudAndNumericNormalization()
    {
        $this->login();

        $obj = new \DeviceMemory();
        $id = $obj->add([
           'designation'   => $this->method,
           'size_default'  => 'not-a-number',
           'frequence'     => 3200,
        ]);
        $this->integer((int)$id)->isGreaterThan(0);
        $this->boolean($obj->getFromDB($id))->isTrue();
        $this->integer((int)$obj->getField('size_default'))->isEqualTo(0);
        $this->integer((int)$obj->getField('frequence'))->isEqualTo(3200);

        $this->boolean(
            $obj->update([
               'id'            => $id,
               'designation'   => $this->method . '-updated',
               'size_default'  => 8192,
            ])
        )->isTrue();
        $this->boolean($obj->getFromDB($id))->isTrue();
        $this->integer((int)$obj->getField('size_default'))->isEqualTo(8192);

        $this->boolean($obj->delete(['id' => $id]))->isTrue();
    }
}
