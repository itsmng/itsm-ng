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

/**
 * @since 0.85
 */

/**
 * Following variables have to be defined before inclusion of this file:
 * @var CommonITILValidation $validation
 */

use Glpi\Event;

// autoload include in objecttask.form (ticketvalidation, changevalidation,...)
if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

Session::checkLoginUser();

if (!($validation instanceof CommonITILValidation)) {
    Html::displayErrorAndDie('');
}
if (!$validation->canView()) {
    Html::displayRightError();
}

$itemtype = $validation->getItilObjectItemType();
$fk       = getForeignKeyFieldForItemType($itemtype);

if (isset($_POST["add"])) {
    $validation->check(-1, CREATE, $_POST);
    if (
        isset($_POST['validate_users_id'])
        && (count($_POST['validate_users_id']) > 0)
    ) {
        $users = $_POST['validate_users_id'];
        foreach ($users as $user) {
            $_POST['validate_users_id'] = $user;
            $validation->add($_POST);
            Event::log(
                $validation->getField($fk),
                strtolower($itemtype),
                4,
                "tracking",
                //TRANS: %s is the user login
                sprintf(__('%s adds an approval'), $_SESSION["glpiname"])
            );
        }
    }
    Html::back();
} elseif (isset($_POST["update"])) {
    $validation->check($_POST['id'], UPDATE);
    $validation->update($_POST);
    Event::log(
        $validation->getField($fk),
        strtolower($itemtype),
        4,
        "tracking",
        //TRANS: %s is the user login
        sprintf(__('%s updates an approval'), $_SESSION["glpiname"])
    );
    Html::back();
} elseif (isset($_POST["purge"])) {
    $validation->check($_POST['id'], PURGE);
    $validation->delete($_POST, 1);

    Event::log(
        $validation->getField($fk),
        strtolower($itemtype),
        4,
        "tracking",
        //TRANS: %s is the user login
        sprintf(__('%s purges an approval'), $_SESSION["glpiname"])
    );
    Html::back();
} elseif (isset($_POST['approval_action'])) {
    if ($_POST['validate_users_id'] == Session::getLoginUserID()) {
        $validation->update($_POST + [
           'status' => ($_POST['approval_action'] === 'approve') ? CommonITILValidation::ACCEPTED : CommonITILValidation::REFUSED
        ]);
        Html::back();
    }
}
Html::displayErrorAndDie('Lost');
