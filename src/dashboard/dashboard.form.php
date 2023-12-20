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

 include ('../../inc/includes.php');

 Session::checkRight("dashboard", UPDATE);
 
 Html::header(Dashboard::getMenuName(), $_SERVER['PHP_SELF'], "config", "Dashboard");
if (isset($_POST['update']) && isset($_POST['id'])) {
    $dashboard = new Dashboard();
    $dashboard->getFromDB($_POST['id']);
    $dashboard->update($_POST);
    Html::back();
} else if (isset($_POST['add'])) {
    $dashboard = new Dashboard();
    try {
        $dashboard->add($_POST);
        Html::back();
    } catch (Exception $e) {
        Session::addMessageAfterRedirect(__('Could not create dashboard', 'itsmng'), false);
        Html::back();
    }
} else {
    $dashboard = new Dashboard();
    $dashboard->showForm($_GET['id'] ?? null, $_GET);
}

 Html::footer();
?>