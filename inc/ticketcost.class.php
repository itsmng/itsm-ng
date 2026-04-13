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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * TicketCost Class
 *
 * @since 0.84
**/
class TicketCost extends CommonITILCost
{
    // From CommonDBChild
    public static $itemtype  = 'Ticket';
    public static $items_id  = 'tickets_id';

    public static $rightname        = 'ticketcost';

    private function updateLinkedItemsTco(): void
    {
        $used_items = \Item_Ticket::getUsedItems((int)$this->fields['tickets_id']);

        foreach ($used_items as $itemtype => $items) {
            $item = getItemForItemtype($itemtype);
            if (!$item) {
                continue;
            }

            foreach ($items as $items_id) {
                if (!$item->getFromDB($items_id) || !$item->isField('ticket_tco')) {
                    continue;
                }

                $item->update([
                   'id'         => $items_id,
                   'ticket_tco' => \Ticket::computeTco($item),
                ]);
            }
        }
    }

    public function post_addItem()
    {
        $this->updateLinkedItemsTco();
    }

    public function post_updateItem($history = 1)
    {
        $this->updateLinkedItemsTco();
    }

    public function post_purgeItem()
    {
        $this->updateLinkedItemsTco();
    }
}
