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

use CommonITILActor;
use DbTestCase;

class Supplier_Ticket extends DbTestCase
{
    public function testIsSupplierEmailChecksTicketAssignments()
    {
        $this->login();

        $supplier = new \Supplier();
        $supplier_id = $supplier->add([
           'name'        => 'supplier-ticket-' . $this->getUniqueString(),
           'entities_id' => 0,
           'email'       => 'supplier-ticket-' . mt_rand(1000, 9999) . '@example.com',
        ]);
        $this->integer((int)$supplier_id)->isGreaterThan(0);

        $ticket = new \Ticket();
        $ticket_id = $ticket->add([
           'name'    => 'ticket-' . $this->getUniqueString(),
           'content' => 'content-' . $this->getUniqueString(),
        ]);
        $this->integer((int)$ticket_id)->isGreaterThan(0);

        $relation = new \Supplier_Ticket();
        $relation_id = $relation->add([
           'tickets_id'   => $ticket_id,
           'suppliers_id' => $supplier_id,
           'type'         => CommonITILActor::ASSIGN,
        ]);
        $this->integer((int)$relation_id)->isGreaterThan(0);

        $this->boolean($relation->isSupplierEmail($ticket_id, $supplier->fields['email']))->isTrue();
        $this->boolean($relation->isSupplierEmail($ticket_id, 'no-match@example.com'))->isFalse();
    }
}
