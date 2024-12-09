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

// Direct access to file
if (strpos($_SERVER['PHP_SELF'], "visibility.php")) {
    $AJAX_INCLUDE = 1;
    include('../inc/includes.php');
    header("Content-Type: text/html; charset=UTF-8");
    Html::header_nocache();
}

Session::checkCentralAccess();

if (!isset($_POST['type']) || empty($_POST['type']) || !isset($_POST['right'])) {
    return;
}

switch ($_POST['type']) {
    case 'User':
        echo json_encode(getOptionsForUsers($_POST['right']));
        break;
    case 'Group':
        echo json_encode(getItemByEntity(Group::class, Session::getActiveEntity()));
        break;
    case 'Profile':
        global $DB;

        $checkright   = (READ | CREATE | UPDATE | PURGE);
        $righttocheck = $_POST['right'];
        if ($_POST['right'] == 'faq') {
            $righttocheck = 'knowbase';
            $checkright   = KnowbaseItem::READFAQ;
        }

        $result = $DB->request([
           'SELECT' => ['profiles_id', 'name'],
           'FROM'   => ProfileRight::getTable(),
           'WHERE'  => [
              'name'   => $righttocheck,
              'rights' => ['&', $checkright]
           ]
        ]);
        $profileWithRight = array_column(iterator_to_array($result), 'profiles_id', 'profiles_id');
        $options = getOptionForItems(Profile::class);
        foreach ($options as $id => $name) {
            if (!isset($profileWithRight[$id])) {
                unset($options[$id]);
            }
        }
        echo json_encode($options);
        break;
}
