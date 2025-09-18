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

include('../inc/includes.php');

if (strpos($_SERVER['HTTP_REFERER'] ?? '', '/plugins/formcreator/') !== false && 
    Session::getCurrentInterface() == "helpdesk") {
    
    $new_url = str_replace('/front/knowbaseitem.form.php', '/plugins/formcreator/front/knowbaseitem.form.php', $_SERVER['REQUEST_URI']);
    header("Location: " . $new_url);
    exit;
}

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}
if (!isset($_GET["item_itemtype"])) {
    $_GET["item_itemtype"] = "";
}
if (!isset($_GET["item_items_id"])) {
    $_GET["item_items_id"] = "";
}
if (!isset($_GET["modify"])) {
    $_GET["modify"] = "";
}


$kb = new KnowbaseItem();

if (isset($_POST["add"])) {
    // ajoute un item dans la base de connaisssances
    $kb->check(-1, CREATE, $_POST);
    $newID = $kb->add($_POST);
    Event::log(
        $newID,
        "knowbaseitem",
        5,
        "tools",
        sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $newID)
    );
    if (isset($_POST['_in_modal']) && $_POST['_in_modal']) {
        Html::redirect($kb->getFormURLWithID($newID) . "&_in_modal=1");
    } else {
        Html::redirect($CFG_GLPI["root_doc"] . "/front/knowbaseitem.php");
    }
} elseif (isset($_POST["update"])) {
    // actualiser  un item dans la base de connaissances
    $kb->check($_POST["id"], UPDATE);

    $kb->update($_POST);
    Event::log(
        $_POST["id"],
        "knowbaseitem",
        5,
        "tools",
        //TRANS: %s is the user login
        sprintf(__('%s updates an item'), $_SESSION["glpiname"])
    );
    Html::redirect($kb->getFormURLWithID($_POST['id']));
} elseif (isset($_POST["purge"])) {
    // effacer un item dans la base de connaissances
    $kb->check($_POST["id"], PURGE);
    $kb->delete($_POST, 1);
    Event::log(
        $_POST["id"],
        "knowbaseitem",
        5,
        "tools",
        //TRANS: %s is the user login
        sprintf(__('%s purges an item'), $_SESSION["glpiname"])
    );
    $kb->redirectToList();
} elseif (isset($_POST["addvisibility"])) {
    if (
        isset($_POST["_type"]) && !empty($_POST["_type"])
        && isset($_POST["knowbaseitems_id"]) && $_POST["knowbaseitems_id"]
    ) {
        $item = null;
        switch ($_POST["_type"]) {
            case 'User':
                if (isset($_POST['users_id']) && $_POST['users_id']) {
                    $item = new KnowbaseItem_User();
                }
                break;

            case 'Group':
                if (isset($_POST['groups_id']) && $_POST['groups_id']) {
                    $item = new Group_KnowbaseItem();
                }
                break;

            case 'Profile':
                if (isset($_POST['profiles_id']) && $_POST['profiles_id']) {
                    $item = new KnowbaseItem_Profile();
                }
                break;

            case 'Entity':
                $item = new Entity_KnowbaseItem();
                break;
        }
        if (!is_null($item)) {
            $item->add($_POST);
            Event::log(
                $_POST["knowbaseitems_id"],
                "knowbaseitem",
                4,
                "tools",
                //TRANS: %s is the user login
                sprintf(__('%s adds a target'), $_SESSION["glpiname"])
            );
        }
    }
    Html::back();
} elseif (isset($_GET["id"]) and isset($_GET['to_rev'])) {
    $kb->check($_GET["id"], UPDATE);
    if ($kb->revertTo($_GET['to_rev'])) {
        Session::addMessageAfterRedirect(
            sprintf(
                __('Knowledge base item has been reverted to revision %s'),
                $_GET['to_rev']
            )
        );
    } else {
        Session::addMessageAfterRedirect(
            sprintf(
                __('Knowledge base item has not been reverted to revision %s'),
                $_GET['to_rev']
            ),
            false,
            ERROR
        );
    }
    Html::redirect($kb->getFormURLWithID($_GET['id']));
} elseif (isset($_GET["id"])) {
    if (!Session::getLoginUserID()) {
        Html::redirect("helpdesk.faq.php?id=" . $_GET['id']);
    }

    if (isset($_GET["_in_modal"])) {
        Html::popHeader(__('Knowledge base'), $_SERVER['PHP_SELF']);
        if ($_GET['id']) {
            $kb->check($_GET["id"], READ);
            $kb->showFull();
        } else { // New item
            $kb->showForm($_GET["id"], $_GET);
        }
        Html::popFooter();
    } else {
        // check we can read the article
        $kb->check($_GET["id"], READ);

        Html::header(KnowbaseItem::getTypeName(1), $_SERVER['PHP_SELF'], "tools", "knowbaseitem");

        $available_options = ['item_itemtype', 'item_items_id', 'id'];
        $options           = [];
        foreach ($available_options as $key) {
            if (isset($_GET[$key])) {
                $options[$key] = $_GET[$key];
            }
        }
        $kb->display($options);

        Html::footer();
    }
}
