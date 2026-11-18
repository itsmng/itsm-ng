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

use Glpi\Event;

include('../inc/includes.php');

Session::checkLoginUser();
header('Content-Type: application/json; charset=UTF-8');
Html::header_nocache();

$linkId = (int)($_POST['id'] ?? 0);
$ticketId = (int)($_POST['ticketId'] ?? 0);
$ticket = new Ticket();
$link = new Ticket_Ticket();

if (
    (GLPI_USE_CSRF_CHECK && (
        empty($_SERVER['HTTP_X_GLPI_CSRF_TOKEN'])
        || !hash_equals($_SESSION['_glpi_csrf_token'] ?? '', $_SERVER['HTTP_X_GLPI_CSRF_TOKEN'])
        || time() >= ($_SESSION['csrf_token_time'] ?? 0)
    ))
    || $linkId <= 0
    || $ticketId <= 0
    || !$ticket->can($ticketId, UPDATE)
    || !$link->can($linkId, PURGE)
    || !in_array($ticketId, [(int)$link->fields['tickets_id_1'], (int)$link->fields['tickets_id_2']], true)
) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    return;
}

$success = $link->delete(['id' => $linkId], true);
if ($success) {
    Event::log(
        $ticketId,
        "ticket",
        4,
        "tracking",
        sprintf(__('%s purges link between tickets'), $_SESSION['glpiname'])
    );
}

echo json_encode(['success' => (bool)$success]);
