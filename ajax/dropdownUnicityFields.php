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

if (strpos($_SERVER['PHP_SELF'], "dropdownUnicityFields.php")) {
    include('../inc/includes.php');
    header("Content-Type: text/html; charset=UTF-8");
    Html::header_nocache();
}
global $DB;
Session::checkRight("config", UPDATE);

$field = new FieldUnicity();
if ($_POST['id'] > 0) {
    $field->getFromDB($_POST['id']);
} else {
    $field->getEmpty();
    $field->fields['itemtype'] = $_POST['itemtype'];
}

if ($target = getItemForItemtype($field->fields['itemtype'])) {
    //Do not check unicity on fields in DB with theses types
    $blacklisted_types = ['longtext', 'text'];

    //Construct list
    $values = [];
    foreach ($DB->listFields(getTableForItemType($target::class)) as $field) {
        $searchOption = $target->getSearchOptionByField('field', $field['Field']);
        if (
            !empty($searchOption)
              && !in_array($field['Type'], $blacklisted_types)
              && !in_array($field['Field'], $target->getUnallowedFieldsForUnicity())
        ) {
            $values[$field['Field']] = $searchOption['name'];
        }
    }
    echo json_encode($values);
}
