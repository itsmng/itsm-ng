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

include('../inc/includes.php');

Session::checkRight("reports", READ);

Html::header(Report::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "tools", "report");

Report::title();

// Titre

$values = [0 => __('All')];
foreach ($CFG_GLPI["contract_types"] as $itemtype) {
    if ($item = getItemForItemtype($itemtype)) {
        $values[$itemtype] = $item->getTypeName();
    }
}
$y      = date("Y");
$dates = [ 0 => __('All')];
for ($i = ($y - 10); $i < ($y + 10); $i++) {
    $dates[$i] = $i;
}
$form = [
   'action' => 'report.year.list.php',
   'buttons' => [
      [
         'value' => __s('Display report'),
         'class' => 'btn btn-secondary'
      ]
   ],
   'content' => [
      __('Hardware under contract') => [
         'visible' => true,
         'inputs' => [
            __('Item type') => [
               'type' => 'checklist',
               'name' => 'item_type',
               'options' => $values,
               'col_lg' => 6,
            ],
            _n('Date', 'Dates', 1) => [
               'type' => 'checklist',
               'name' => 'year',
               'options' => $dates,
               'values' => [$y],
               'col_lg' => 6,
            ],
         ]
      ]
   ]
];
renderTwigForm($form);

Html::footer();
