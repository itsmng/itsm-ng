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

/**
 * @since 0.84
 */

$AJAX_INCLUDE = 1;
include('../inc/includes.php');

// Send UTF8 Headers
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (isset($_POST['type']) && isset($_POST['end'])) {
    echo "<table width='90%' aria-label='Periodicity settings table'>";
    switch ($_POST['type']) {
        case 'day':
            renderTwigTemplate('macros/wrappedInput.twig', [
               'title' => __('End date') . ' (' . __('Repetition') . ')',
               'input' => [
                   'type'  => 'datetime-local',
                   'name'  => 'periodicity[end]',
                   'value' => $_POST['end'],
                   'min'   => date('Y-m-d H:00:00'),
                   'max'   => date('Y-m-d H:00:00', strtotime('+1 day')),
                   'col_lg' => 12,
                   'col_md' => 12,
               ]
            ]);
            echo "</td></tr>";
            break;

        case 'week':
            renderTwigTemplate('macros/wrappedInput.twig', [
               'title' => __('End date') . ' (' . __('Repetition') . ')',
               'input' => [
                   'type'  => 'datetime-local',
                   'name'  => 'periodicity[end]',
                   'value' => $_POST['end'],
                   'min'   => date('Y-m-d H:00:00'),
                   'max'   => date('Y-m-d H:00:00', strtotime('+1 day')),
                   'col_lg' => 12,
                   'col_md' => 12,
               ]
            ]);
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($days as $day) {
                echo "<th>" . __($day) . "</th>";
            }
            echo "</tr><tr class='center'><td>" . __('By day') . '</td>';

            foreach ($days as $day) {
                echo "<td><input type='checkbox' name='periodicity[days][$day]'></td>";
            }
            echo "</tr>";
            break;

        case 'month':
            $values = ['date' => __('Each month, same date'),
                            'day'  => __('Each month, same day of week')];
            renderTwigTemplate('macros/wrappedInput.twig', [
               'title' => __('Type'),
               'input' => [
                   'type'  => 'select',
                   'name'  => 'periodicity[subtype]',
                   'values' => $values,
                   'col_lg' => 12,
                   'col_md' => 12,
               ]
            ]);
            renderTwigTemplate('macros/wrappedInput.twig', [
               'title' => __('End date') . ' (' . __('Repetition') . ')',
               'input' => [
                   'type'  => 'datetime-local',
                   'name'  => 'periodicity[end]',
                   'value' => $_POST['end'],
                   'min'   => date('Y-m-d H:00:00'),
                   'max'   => date('Y-m-d H:00:00', strtotime('+1 day')),
                   'col_lg' => 12,
                   'col_md' => 12,
               ]
            ]);
    }
    echo '</table>';
}
