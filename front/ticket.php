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

if ($default = Grid::getDefaultDashboardForMenu('mini_ticket', true)) {
   $dashboard = new Grid($default, 33, 2, 'mini_core');
   $dashboard->show([
      'widgetGrid' => [
         [
            [
               'type' => 'number',
               'title' => __('Tickets'),
               'value' => countElementsInTable('glpi_tickets'),
               'icon' => 'fas fa-ticket-alt',
            ],
            [
               'type' => 'number',
               'title' => __('New tickets'),
               'value' => countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::INCOMING]),
               'icon' => 'fas fa-exclamation-circle',
            ],
            [
               'type' => 'number',
               'title' => __('Pending tickets'),
               'value' => countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::WAITING]),
               'icon' => 'fas fa-pause-circle',
            ],
            [
               'type' => 'number',
               'title' => __('Assigned tickets'),
               'value' => countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::ASSIGNED]),
               'icon' => 'fas fa-users',
            ],
            [
               'type' => 'number',
               'title' => __('Planned tickets'),
               'value' => countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::PLANNED]),
               'icon' => 'fas fa-users',
            ],
            [
               'type' => 'number',
               'title' => 'Solved tickets',
               'value' => countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::SOLVED]),
               'icon' => 'fas fa-check-circle',
            ],
            [
               'type' => 'number',
               'title' => 'Closed tickets',
               'value' => countElementsInTable('glpi_tickets', ['is_deleted' => 0, 'status' => Ticket::CLOSED]),
               'icon' => 'fas fa-times-circle',
            ],
         ]
      ]
   ]);
}

Search::show('Ticket');

Html::footer();