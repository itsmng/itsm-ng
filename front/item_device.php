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

if (!isset($_GET['itemtype']) || !class_exists($_GET['itemtype'])) {
    throw new \RuntimeException(
        'Missing or incorrect item device type called!'
    );
}

$itemDevice = getItemForItemtype($_GET['itemtype']);
if (!$itemDevice->canView()) {
    Session::redirectIfNotLoggedIn();
    Html::displayRightError();
}

if (in_array($itemDevice->getType(), $CFG_GLPI['devices_in_menu'])) {
    Html::header($itemDevice->getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "assets", strtolower($itemDevice->getType()));
} else {
    Html::header($itemDevice->getTypeName(Session::getPluralNumber()), '', "config", "commondevice", $itemDevice->getDeviceType());
}

Search::show($_GET['itemtype']);

Html::footer();
