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
 * Show choices for network reports
 */

include('../inc/includes.php');

Session::checkRight("reports", READ);

Html::header(Report::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "tools", "report");

Report::title();

// Titre

echo "<table class='tab_cadre' aria-label='Network Report Form' >";
echo "<tr><th colspan='3'>&nbsp;" . __('Network report') . "</th></tr>";
echo "</table><br>";

// 3. Selection d'affichage pour generer la liste
$form = [
   'action' => $_SERVER['PHP_SELF'],
   'buttons' => [
      [
         'value' => __s('Display report'),
         'class' => 'btn btn-secondary',
      ]
   ],
   'content' => [
      '' => [
         'visible' => true,
         'inputs' => [
            __('By location') => [
               'type' => 'select',
               'name' => 'locations_id',
               'itemtype' => Location::class,
               'col_lg' => 12,
               'col_md' => 12,
            ]
         ]
      ]
   ]
];
renderTwigForm($form);

$form = [
   'action' => $_SERVER['PHP_SELF'],
   'buttons' => [
      [
         'value' => __s('Display report'),
         'class' => 'btn btn-secondary',
      ]
   ],
   'content' => [
      '' => [
         'visible' => true,
         'inputs' => [
            __('By hardware') => [
               'type' => 'select',
               'name' => 'switch',
               'itemtype' => NetworkEquipment::class,
               'col_lg' => 12,
               'col_md' => 12,
            ]
         ]
      ]
   ]
];
renderTwigForm($form);

if (countElementsInTableForMyEntities("glpi_netpoints") > 0) {
    echo "<form name='form3' aria-label='Report Choices by location' method='post' action='report.netpoint.list.php'>";
    echo "<table class='tab_cadre' width='500' aria-label='Report Choices by Location'>";
    echo "<tr class='tab_bg_1'><td width='120'>" . __('By network outlet') . "</td>";
    echo "<td>";
    Netpoint::dropdownNetpoint("prise", 0, -1, 1, $_SESSION["glpiactive_entity"]);
    echo "</td><td class='center' width='120'>";
    echo "<input type='submit' value=\"" . __s('Display report') . "\" class='submit'>";
    echo "</td></tr>";
    echo "</table>";
    Html::closeForm();
}

Html::footer();
