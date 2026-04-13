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

class Supplier extends DbTestCase
{
    public function testCrud()
    {
        $this->login();

        $obj = new \Supplier();
        $id = $obj->add([
           'name'        => 'supplier-' . $this->getUniqueString(),
           'entities_id' => 0,
           'email'       => 'supplier-' . mt_rand(1000, 9999) . '@example.com',
           'is_active'   => 1,
        ]);
        $this->integer((int)$id)->isGreaterThan(0);
        $this->boolean($obj->getFromDB($id))->isTrue();

        $this->boolean($obj->update([
           'id'          => $id,
           'phonenumber' => '0102030405',
        ]))->isTrue();
        $this->boolean($obj->getFromDB($id))->isTrue();
        $this->string($obj->getField('phonenumber'))->isEqualTo('0102030405');

        $this->boolean($obj->delete(['id' => $id]))->isTrue();
    }

    public function testGetLinksSanitizesOutput()
    {
        $obj = new \Supplier();
        $obj->fields = [
           'id'      => 0,
           'name'    => '\'"<svg/onload=alert(1)>',
           'website' => "example.com' onclick='alert(1)",
        ];

        $links = $obj->getLinks(true);

        $this->string($links)
           ->contains("&lt;svg/onload=alert(1)&gt;")
           ->contains("href='http://example.com&#039; onclick=&#039;alert(1)'")
           ->notContains("<svg/onload=alert(1)>")
           ->notContains("href='http://example.com' onclick='alert(1)'");
    }
}
