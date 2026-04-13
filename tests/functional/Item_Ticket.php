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

class Item_Ticket extends DbTestCase
{
    public function testUpdateItemTCO()
    {
        $this->login();

        $computer = new \Computer();
        $computers_id = (int)$computer->add([
           'name'        => __FUNCTION__,
           'entities_id' => getItemByTypeName('Entity', '_test_root_entity', true),
        ]);
        $this->integer($computers_id)->isGreaterThan(0);

        $ticket = new \Ticket();
        $tickets_id = (int)$ticket->add([
           'name'        => __FUNCTION__,
           'content'     => 'test',
           'entities_id' => getItemByTypeName('Entity', '_test_root_entity', true),
           'items_id'    => ['Computer' => [$computers_id]],
        ]);
        $this->integer($tickets_id)->isGreaterThan(0);

        $ticket_cost = new \TicketCost();
        $ticketcosts_id = (int)$ticket_cost->add([
           'tickets_id' => $tickets_id,
           'cost_fixed' => 100,
        ]);
        $this->integer($ticketcosts_id)->isGreaterThan(0);

        $this->boolean($computer->getFromDB($computers_id))->isTrue();
        $this->integer((int)$computer->fields['ticket_tco'])->isIdenticalTo(100);
    }
}
