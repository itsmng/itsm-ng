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
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

// Read parameters
$context  = $_POST['context'] ?? '';
$itemtype = $_POST["itemtype"] ?? '';

// Check for required params
if (empty($itemtype)) {
    http_response_code(400);
    Toolbox::logWarning("Bad request: itemtype cannot be empty, received: $itemtype");
    die;
}

// Check if itemtype is valid in the given context
if ($context == "impact") {
    $isValidItemtype = Impact::isEnabled($itemtype);
} else {
    $isValidItemtype = CommonITILObject::isPossibleToAssignType($itemtype);
}

// Make a select box
if ($isValidItemtype) {
    $table = getTableForItemType($itemtype);

    $rand = mt_rand();
    if (isset($_POST["rand"])) {
        $rand = $_POST["rand"];
    }

    // Message for post-only
    $p = [
       'itemtype'            => $itemtype,
       'entity_restrict'     => $_POST['entity_restrict'],
       'table'               => $table,
       '_idor_token'         => Session::getNewIDORToken($itemtype, [
          'entity_restrict' => $_POST['entity_restrict'],
       ]),
    ];

    if (isset($_POST["used"]) && !empty($_POST["used"])) {
        if (isset($_POST["used"][$itemtype])) {
            $p["used"] = $_POST["used"][$itemtype];
        }
    }

    // Add context if defined
    if (!empty($context)) {
        $p["context"] = $context;
    }
    $p['table'] = $table;

    echo Dropdown::getDropdownFindNum($p);
}
