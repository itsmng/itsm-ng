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

include ('../inc/includes.php');

Session::checkLoginUser();

Html::header(Ticket::getTypeName(Session::getPluralNumber()), '', "helpdesk", "ticket");

$callback = <<<JS
   if ($('div[role="dialog"]:visible').length === 0) {
      window.location.reload();
   }
JS;

echo Html::manageRefreshPage(false, $callback);

$dashboard = new Grid();
$dashboard->show([
   'widgetGrid' => [
      [
         [
            'type' => 'count',
            'title' => __('Tickets'),
            'value' => countElementsInTable('glpi_tickets', ['entities_id' => $_SESSION['glpiactive_entity']]),
            'options' => [
               'icon' => 'fas fa-ticket-alt',
            ],
         ],
         [
            'type' => 'count',
            'title' => __('New tickets'),
            'value' => countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::INCOMING, 'entities_id' => $_SESSION['glpiactive_entity']]),
            'options' => [
               'icon' => 'fas fa-exclamation-circle',
            ],
         ],
         [
            'type' => 'count',
            'title' => __('Pending tickets'),
            'value' => countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::WAITING, 'entities_id' => $_SESSION['glpiactive_entity']]),
            'options' => [
               'icon' => 'fas fa-pause-circle',
            ],
         ],
         [
            'type' => 'count',
            'title' => __('Assigned tickets'),
            'value' => countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::ASSIGNED, 'entities_id' => $_SESSION['glpiactive_entity']]),
            'options' => [
               'icon' => 'fas fa-users',
            ],
         ],
         [
            'type' => 'count',
            'title' => __('Planned tickets'),
            'value' => countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::PLANNED, 'entities_id' => $_SESSION['glpiactive_entity']]),
            'options' => [
               'icon' => 'fas fa-calendar-check',
            ],
         ],
         [
            'type' => 'count',
            'title' => 'Solved tickets',
            'value' => countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::SOLVED, 'entities_id' => $_SESSION['glpiactive_entity']]),
            'options' => [
               'icon' => 'fas fa-check-circle',
            ],
         ],
         [
            'type' => 'count',
            'title' => 'Closed tickets',
            'value' => countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::CLOSED, 'entities_id' => $_SESSION['glpiactive_entity']]),
            'options' => [
               'icon' => 'fas fa-times-circle',
            ],
         ],
      ]
   ]
]);

Search::show('Ticket');

Html::footer();