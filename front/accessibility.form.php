<?php

/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2022 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ITSM-NG.
 *
 * ITSM-NG is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ITSM-NG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ITSM-NG. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

use Glpi\Event;

if (!defined('GLPI_ROOT')) {
    include ('../inc/includes.php');
}

$user = new User();

Session::checkLoginUser();

if (isset($_POST["update"])
    && ($_POST["id"] == Session::getLoginUserID())) {
    // Prepare user data
    $shortcuts = [];
    foreach ($_POST as $K => $V) { // Iterate KV, remove shortcuts from POST
        $shortcuts[$K] = $V;

    }
    $shortcuts = str_ireplace( array( '\'', '"', '\\'), '', $shortcuts);
    $_POST["access_custom_shortcuts"] = json_encode($shortcuts);
    $user->update($_POST);

    Event::log($_POST["id"], "users", 5, "setup",
        //TRANS: %s is the user login
        sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
    Html::back();

} else {
    if (Session::getCurrentInterface() == "central") {
        Html::header(Preference::getTypeName(1), $_SERVER['PHP_SELF'], 'accessibility');
    } else {
        Html::helpHeader(Preference::getTypeName(1), $_SERVER['PHP_SELF']);
    }

    $access = new Accessibility();
    $access->display(['main_class' => 'tab_cadre_fixe']);

    if (Session::getCurrentInterface() == "central") {
        Html::footer();
    } else {
        Html::helpFooter();
    }
}
