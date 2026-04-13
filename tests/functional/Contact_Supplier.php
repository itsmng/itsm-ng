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

class Contact_Supplier extends DbTestCase
{
    public function testLinkAndReadSupplierDataFromContact()
    {
        $this->login();

        $supplier = new \Supplier();
        $supplier_id = $supplier->add([
           'name'        => 'supplier-' . $this->getUniqueString(),
           'entities_id' => 0,
           'website'     => 'https://example.com',
           'address'     => '1 Test street',
           'town'        => 'Test City',
           'postcode'    => '12345',
           'country'     => 'Testland',
        ]);
        $this->integer((int)$supplier_id)->isGreaterThan(0);

        $contact = new \Contact();
        $contact_id = $contact->add([
           'name'        => 'contact-' . $this->getUniqueString(),
           'firstname'   => 'first-' . $this->getUniqueString(),
           'entities_id' => 0,
        ]);
        $this->integer((int)$contact_id)->isGreaterThan(0);

        $relation = new \Contact_Supplier();
        $relation_id = $relation->add([
           'contacts_id'  => $contact_id,
           'suppliers_id' => $supplier_id,
        ]);
        $this->integer((int)$relation_id)->isGreaterThan(0);

        $this->boolean($contact->getFromDB($contact_id))->isTrue();
        $this->string($contact->getWebsite())->isEqualTo('https://example.com');
        $address = $contact->getAddress();
        $this->array($address)->hasKey('address')->hasKey('town')->hasKey('country');
        $this->string($address['address'])->isEqualTo('1 Test street');

        $this->boolean($relation->delete(['id' => $relation_id]))->isTrue();
        $this->integer((int)countElementsInTable(
            \Contact_Supplier::getTable(),
            [
                'contacts_id'  => $contact_id,
                'suppliers_id' => $supplier_id,
            ]
        ))->isEqualTo(0);
    }
}
