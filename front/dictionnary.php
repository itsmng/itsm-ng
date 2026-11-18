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

Session::checkSeveralRightsOr(['rule_dictionnary_dropdown' => READ,
                                    'rule_dictionnary_software' => READ]);

Html::header(__('Administration'), $_SERVER['PHP_SELF'], "admin", "dictionnary", -1);

RuleCollection::titleBackup();
$dictionnaries = RuleCollection::getDictionnaries();

echo "<div class='row g-3'>";
foreach ($dictionnaries as $dictionnary) {
    echo "<section class='col-12 col-md-6 col-xl-4'><h2 class='h5'>" . $dictionnary['type'] . '</h2>';
    echo "<nav class='list-group'>";
    foreach ($dictionnary['entries'] as $entry) {
        echo "<a class='list-group-item list-group-item-action' href='" . $entry['link'] . "'>" . $entry['label'] . '</a>';
    }
    echo '</nav></section>';
}
echo '</div>';

Html::footer();
