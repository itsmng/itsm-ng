<?php
/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2022 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ITSM-NG.
 *
 * ITSM-NG is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ITSM-NG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ITSM-NG. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

include('../inc/includes.php');

if (
    isset($_POST['linkId']) &&
    isset($_POST['objectTypeId']) &&
    isset($_POST['objectId']) &&
    isset($_POST['ticketId']) &&
    Session::haveRight('ticket', UPDATE)) {
    $ticket = new Ticket();
    $ticket->getFromDB($_POST['ticketId']);

    $linkId = $_POST['linkId'];
    $objectTypeId = $_POST['objectTypeId'];
    $objectId = $_POST['objectId'];

    switch ($linkId) {
        case 'user':
            $ticketUser = new Ticket_User();
            $ticketUser->getFromDBByCrit([
                'tickets_id' => $ticket->getID(),
                'users_id' => $objectId,
                'type' => $objectTypeId
            ]);
            $ticketUser->delete(['id' => $ticketUser->getID()]);
            break;
        case 'group':
            $ticketGroup = new Group_Ticket();
            $ticketGroup->getFromDBByCrit([
                'tickets_id' => $ticket->getID(),
                'groups_id' => $objectId,
                'type' => $objectTypeId
            ]);
            $ticketGroup->delete(['id' => $ticketGroup->getID()]);
            break;
        case 'supplier':
            $ticketSupplier = new Supplier_Ticket();
            $ticketSupplier->getFromDBByCrit([
                'tickets_id' => $ticket->getID(),
                'suppliers_id' => $objectId,
                'type' => $objectTypeId
            ]);
            $ticketSupplier->delete(['id' => $ticketSupplier->getID()]);
            break;
        case 'ticket':
            $otherTicket = new Ticket();
            $otherTicket->getFromDB($objectId);
            $ticketLink = new Ticket_Ticket();
            $ticketLink->getFromDBForItems($ticket, $otherTicket);
            if ($ticketLink->getId() != -1) {
                $ticketLink->delete(['id' => $ticketLink->getID()]);
            }
            $ticketLink->getFromDBForItems($otherTicket, $ticket);
            if ($ticketLink->getId() != -1) {
                $ticketLink->delete(['id' => $ticketLink->getID()]);
            }
            break;
    }
    echo json_encode([
        'success' => true,
        'message' => __('Actor deleted successfully')
    ]);
    return;
} else {
    echo json_encode([
        'success' => false,
        'message' => __('Error while deleting actor')
    ]);
    return;
}
