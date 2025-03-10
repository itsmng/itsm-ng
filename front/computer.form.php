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

use Glpi\Event;
use Ajax;
use CommonDBTM;
use CronTask;
use Document;
use Html;
use Session;
use Toolbox;
use Infocom;
use DBConnection;

include('../inc/includes.php');

Session::checkRight("computer", READ);

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}

if (!isset($_GET["withtemplate"])) {
    $_GET["withtemplate"] = "";
}

$computer = new Computer();
//Add a new computer
if (isset($_POST["add"])) {
    $computer->check(-1, CREATE, $_POST);
    if ($newID = $computer->add($_POST)) {
        Event::log(
            $newID,
            "computers",
            4,
            "inventory",
            sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["name"])
        );

        if ($_SESSION['glpibackcreated']) {
            Html::redirect($computer->getLinkURL());
        }
    }
    Html::back();

    // delete a computer
} elseif (isset($_POST["delete"])) {
    $computer->check($_POST['id'], DELETE);
    $ok = $computer->delete($_POST);
    if ($ok) {
        Event::log(
            $_POST["id"],
            "computers",
            4,
            "inventory",
            //TRANS: %s is the user login
            sprintf(__('%s deletes an item'), $_SESSION["glpiname"])
        );
    }
    $computer->redirectToList();
} elseif (isset($_POST["restore"])) {
    $computer->check($_POST['id'], DELETE);
    if ($computer->restore($_POST)) {
        Event::log(
            $_POST["id"],
            "computers",
            4,
            "inventory",
            //TRANS: %s is the user login
            sprintf(__('%s restores an item'), $_SESSION["glpiname"])
        );
    }
    $computer->redirectToList();
} elseif (isset($_POST["purge"])) {
    $computer->check($_POST['id'], PURGE);
    if ($computer->delete($_POST, 1)) {
        Event::log(
            $_POST["id"],
            "computers",
            4,
            "inventory",
            //TRANS: %s is the user login
            sprintf(__('%s purges an item'), $_SESSION["glpiname"])
        );
    }
    $computer->redirectToList();

    //update a computer
} elseif (isset($_POST["update"])) {
    $computer->check($_POST['id'], UPDATE);
    $computer->update($_POST);
    Event::log(
        $_POST["id"],
        "computers",
        4,
        "inventory",
        //TRANS: %s is the user login
        sprintf(__('%s updates an item'), $_SESSION["glpiname"])
    );
    Html::back();

    // Disconnect a computer from a printer/monitor/phone/peripheral
} else {//print computer information
    Html::header(Computer::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "assets", "computer");
    //show computer form to add
    $computer->display([
       'id'           => $_GET["id"],
       'withtemplate' => $_GET["withtemplate"],
       'formoptions'  => "data-track-changes=true"
    ]);
    Html::footer();
}
