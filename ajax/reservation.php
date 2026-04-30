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

use Glpi\Event;

include('../inc/includes.php');

Session::checkRight("reservation", ReservationItem::RESERVEANITEM);

if (!isset($_REQUEST['action'])) {
    exit;
}

function reservation_ajax_date($value)
{
    if (empty($value)) {
        return date('Y-m-d H:i:s');
    }

    $time = strtotime((string)$value);
    if ($time === false) {
        return date('Y-m-d H:i:s');
    }

    return date('Y-m-d H:i:s', $time);
}

function reservation_ajax_item_label(ReservationItem $reservation_item)
{
    $type = $reservation_item->fields['itemtype'];
    $name = NOT_AVAILABLE;

    if ($item = getItemForItemtype($reservation_item->fields['itemtype'])) {
        $type = $item->getTypeName();
        if ($item->getFromDB($reservation_item->fields['items_id'])) {
            $name = $item->getName();
        }
    }

    return sprintf(__('%1$s - %2$s'), $type, $name);
}

if ($_REQUEST['action'] === 'get_events') {
    global $DB, $CFG_GLPI;

    header("Content-Type: application/json; charset=UTF-8");

    $reservationitems_id = (int)($_REQUEST['reservationitems_id'] ?? 0);
    $start = reservation_ajax_date($_REQUEST['start'] ?? null);
    $end = reservation_ajax_date($_REQUEST['end'] ?? null);

    $where = [
       'glpi_reservations.end'   => ['>', $start],
       'glpi_reservations.begin' => ['<', $end],
       'glpi_reservationitems.is_active'  => 1,
       'glpi_reservationitems.is_deleted' => 0,
    ];

    if ($reservationitems_id > 0) {
        $where['glpi_reservations.reservationitems_id'] = $reservationitems_id;
    }

    $iterator = $DB->request([
       'SELECT' => [
          'glpi_reservations.id',
          'glpi_reservations.begin',
          'glpi_reservations.end',
          'glpi_reservations.comment',
          'glpi_reservations.users_id',
          'glpi_reservations.reservationitems_id',
          'glpi_reservationitems.itemtype',
          'glpi_reservationitems.items_id',
       ],
       'FROM' => 'glpi_reservations',
       'INNER JOIN' => [
          'glpi_reservationitems' => [
             'ON' => [
                'glpi_reservations'     => 'reservationitems_id',
                'glpi_reservationitems' => 'id',
             ],
          ],
       ],
       'WHERE' => $where + getEntitiesRestrictCriteria(
           'glpi_reservationitems',
           'entities_id',
           $_SESSION['glpiactiveentities']
       ),
       'ORDERBY' => 'glpi_reservations.begin',
    ]);

    $events = [];
    $reservation = new Reservation();
    $reservation_item = new ReservationItem();
    while ($row = $iterator->next()) {
        if (!$reservation_item->getFromDB($row['reservationitems_id'])) {
            continue;
        }

        $item_label = reservation_ajax_item_label($reservation_item);
        $user_label = getUserName($row['users_id']);
        $title = $reservationitems_id > 0 ? $user_label : sprintf(__('%1$s - %2$s'), $item_label, $user_label);

        $can_edit = $reservation->getFromDB($row['id']) && $reservation->canEdit($row['id']);
        $events[] = [
           'id'              => $row['id'],
           'title'           => $title,
           'start'           => $row['begin'],
           'end'             => $row['end'],
           'editable'        => $can_edit,
           'durationEditable' => $can_edit,
           'startEditable'   => $can_edit,
           'url'             => $can_edit ? $CFG_GLPI['root_doc'] . '/ajax/reservation.php?action=get_form&id=' . $row['id'] : '',
           'classNames'      => ['reservation-calendar-event'],
           'extendedProps'   => [
              'comment'             => $row['comment'],
              'user'                => $user_label,
              'item'                => $item_label,
              'reservationitems_id' => $row['reservationitems_id'],
              'can_edit'            => $can_edit,
           ],
        ];
    }

    echo json_encode($events);
    exit;
}

if ($_REQUEST['action'] === 'get_form') {
    Html::header_nocache();
    header("Content-Type: text/html; charset=UTF-8");

    $reservation = new Reservation();
    $id = (int)($_REQUEST['id'] ?? 0);
    $reservationitems_id = (int)($_REQUEST['reservationitems_id'] ?? 0);

    if ($id > 0) {
        $reservation->showForm($id, []);
    } elseif ($reservationitems_id > 0) {
        $reservation->showForm('', [
           'item'  => [$reservationitems_id => $reservationitems_id],
           'begin' => reservation_ajax_date($_REQUEST['begin'] ?? null),
           'end'   => reservation_ajax_date($_REQUEST['end'] ?? null),
        ]);
    }
    Html::ajaxFooter();
    exit;
}

if ($_REQUEST['action'] === 'save') {
    header("Content-Type: application/json; charset=UTF-8");

    $reservation = new Reservation();
    $output = '';
    $success = false;

    ob_start();
    if (isset($_POST['purge'])) {
        $reservationitems_id = key($_POST['items'] ?? []);
        if (!empty($_POST['id']) && $reservation->delete($_POST, 1)) {
            Event::log(
                $_POST['id'],
                "reservation",
                4,
                "inventory",
                sprintf(
                    __('%1$s purges the reservation for item %2$s'),
                    $_SESSION["glpiname"],
                    $reservationitems_id
                )
            );
            $success = true;
        }
    } elseif (isset($_POST['update'])) {
        Toolbox::manageBeginAndEndPlanDates($_POST['resa']);
        $_POST['_target'] = Reservation::getFormURL();
        $_POST['_item'] = key($_POST['items'] ?? []);
        $_POST['begin'] = $_POST['resa']['begin'];
        $_POST['end'] = $_POST['resa']['end'];
        $_POST['_ajax_reservation'] = 1;

        if (
            Session::haveRight("reservation", UPDATE)
            || (Session::getLoginUserID() == ($_POST["users_id"] ?? 0))
        ) {
            $success = (bool)$reservation->update($_POST);
        }
    } elseif (isset($_POST['add'])) {
        if (empty($_POST['users_id'])) {
            $_POST['users_id'] = Session::getLoginUserID();
        }
        Toolbox::manageBeginAndEndPlanDates($_POST['resa']);

        $dates_to_add = [];
        if (isset($_POST['resa']['end'])) {
            $dates_to_add[$_POST['resa']['begin']] = $_POST['resa']['end'];

            if (
                isset($_POST['periodicity']) && is_array($_POST['periodicity'])
                && isset($_POST['periodicity']['type']) && !empty($_POST['periodicity']['type'])
            ) {
                $dates_to_add += Reservation::computePeriodicities(
                    $_POST['resa']['begin'],
                    $_POST['resa']['end'],
                    $_POST['periodicity']
                );
            }
        }
        ksort($dates_to_add);

        $success = count($dates_to_add) > 0 && count($_POST['items'] ?? []) > 0;
        foreach ($_POST['items'] ?? [] as $reservationitems_id) {
            $input = [
               'reservationitems_id' => $reservationitems_id,
               'comment'             => $_POST['comment'] ?? '',
               'group'               => $reservation->getUniqueGroupFor($reservationitems_id),
               '_ajax_reservation'   => 1,
            ];

            foreach ($dates_to_add as $begin => $end) {
                $input['begin'] = $begin;
                $input['end'] = $end;
                $input['users_id'] = (int)$_POST['users_id'];

                if (
                    Session::haveRight("reservation", UPDATE)
                    || (Session::getLoginUserID() === $input["users_id"])
                ) {
                    unset($reservation->fields["id"]);
                    if ($newID = $reservation->add($input)) {
                        Event::log(
                            $newID,
                            "reservation",
                            4,
                            "inventory",
                            sprintf(
                                __('%1$s adds the reservation %2$s for item %3$s'),
                                $_SESSION["glpiname"],
                                $newID,
                                $reservationitems_id
                            )
                        );
                    } else {
                        $success = false;
                    }
                }
            }
        }
    }
    $output = ob_get_clean();

    echo json_encode([
       'success' => $success,
       'html'    => $output,
    ]);
    exit;
}

if ($_REQUEST['action'] === 'update_times') {
    header("Content-Type: application/json; charset=UTF-8");

    $reservation = new Reservation();
    $id = (int)($_POST['id'] ?? 0);
    $success = false;
    $output = '';

    ob_start();
    if ($id > 0 && $reservation->getFromDB($id) && $reservation->canEdit($id)) {
        $input = [
           'id'                => $id,
           '_item'             => $reservation->fields['reservationitems_id'],
           'begin'             => reservation_ajax_date($_POST['begin'] ?? null),
           'end'               => reservation_ajax_date($_POST['end'] ?? null),
           '_ajax_reservation' => 1,
        ];
        $success = (bool)$reservation->update($input);
    }
    $output = ob_get_clean();

    echo json_encode([
       'success' => $success,
       'html'    => $output,
    ]);
    exit;
}
