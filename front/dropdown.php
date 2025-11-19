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

Session::checkLoginUser();

Html::header(__('Setup'), $_SERVER['PHP_SELF'], "config", "commondropdown");

echo "<div class='center'>";

$optgroup = Dropdown::getStandardDropdownItemTypes();
if (count($optgroup) > 0) {
    $selected = '';
    foreach ($optgroup as $label => $dp) {
        foreach ($dp as $key => $val) {
            $search = $key::getSearchURL();
            $values[$label][$search] = $val;
        }
    }
    //Dropdown::showFromArray('dpmenu', $values,
    //['on_change'
    //=> "var _value = this.options[this.selectedIndex].value; if (_value != 0) {window.location.href=_value;}",
    //'value'               => $selected,
    //'display_emptychoice' => true]);
    echo "<div class='container'>";
    renderTwigTemplate('macros/wrappedInput.twig', [
        'title' => _n('Dropdown', 'Dropdowns', Session::getPluralNumber()),
        'input' => [
            'type' => 'select',
            'id' => 'dpmenu',
            'name' => 'dpmenu',
            'values' => [Dropdown::EMPTY_VALUE] + $values,
            'hooks' => [
                'change' => <<<JS
                    var _value = this.options[this.selectedIndex].value;
                    if (_value != 0) {
                        window.location.href=_value;
                    }
                JS
            ],
            'col_lg' => 12,
            'col_md' => 12,
        ]
    ]);
    Dropdown::showItemTypeList($optgroup);
    echo "</div>";
} else {
    Html::displayRightError();
}

echo "</div>";
Html::footer();
