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

/* Test for inc/ticketsatisfaction.class.php */

class TicketSatisfaction extends DbTestCase
{
    public function testAddAndUpdateKeepNewSurveyLoadedWhenAutoIdMatchesExistingTicketId()
    {
        global $DB;

        $this->login();

        $max_satisfaction_id = $DB->request([
           'SELECT' => new \QueryExpression('MAX(' . $DB->quoteName('id') . ') AS max_id'),
           'FROM'   => 'glpi_ticketsatisfactions',
        ])->next()['max_id'] ?? 0;
        $max_satisfaction_id = (int)$max_satisfaction_id;

        do {
            $collision_tickets_id = $this->createTicket('survey collision ticket');
        } while ($collision_tickets_id <= $max_satisfaction_id + 2);

        $dummy_tickets_id  = $this->createTicket('survey dummy ticket');
        $target_tickets_id = $this->createTicket('survey target ticket');

        $this->boolean($DB->insert('glpi_ticketsatisfactions', [
           'id'         => $max_satisfaction_id + 1,
           'tickets_id' => $collision_tickets_id,
           'type'       => 2,
           'date_begin' => $_SESSION['glpi_currenttime'],
        ]))->isTrue();

        $this->boolean($DB->insert('glpi_ticketsatisfactions', [
           'id'         => $collision_tickets_id - 1,
           'tickets_id' => $dummy_tickets_id,
           'type'       => 2,
           'date_begin' => $_SESSION['glpi_currenttime'],
        ]))->isTrue();

        $satisfaction = new \TicketSatisfaction();
        $satisfactions_id = $satisfaction->add([
           'tickets_id'  => $target_tickets_id,
           'date_begin'  => $_SESSION['glpi_currenttime'],
           'type'        => 2,
        ]);

        $this->integer((int)$satisfactions_id)->isEqualTo($collision_tickets_id);
        $this->integer((int)$satisfaction->fields['id'])->isEqualTo($collision_tickets_id);
        $this->integer((int)$satisfaction->fields['tickets_id'])->isEqualTo($target_tickets_id);

        $this->boolean($satisfaction->update([
           'tickets_id'    => $target_tickets_id,
           'satisfaction'  => 4,
           'comment'       => 'Survey answer',
        ]))->isTrue();

        $this->integer((int)$satisfaction->fields['id'])->isEqualTo($collision_tickets_id);
        $this->integer((int)$satisfaction->fields['tickets_id'])->isEqualTo($target_tickets_id);
        $this->integer((int)$satisfaction->fields['satisfaction'])->isEqualTo(4);
    }


    private function createTicket($name)
    {
        $ticket = new \Ticket();
        $tickets_id = (int)$ticket->add([
           'name'    => $name,
           'content' => $name,
        ]);

        $this->integer($tickets_id)->isGreaterThan(0);

        return $tickets_id;
    }
}
