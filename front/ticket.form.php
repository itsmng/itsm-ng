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

Session::checkLoginUser();
$track = new Ticket();

if (!isset($_GET['id'])) {
    $_GET['id'] = "";
}

$date_fields = [
   'date',
   'due_date',
   'time_to_own'
];

foreach ($date_fields as $date_field) {
    //handle not clean dates...
    if (
        isset($_POST["_$date_field"])
        && isset($_POST[$date_field])
        && trim($_POST[$date_field]) == ''
        && trim($_POST["_$date_field"]) != ''
    ) {
        $_POST[$date_field] = $_POST["_$date_field"];
    }
}

if (isset($_POST["add"])) {
    $track->check(-1, CREATE, $_POST);

    if ($track->add($_POST)) {
        if ($_SESSION['glpibackcreated']) {
            Html::redirect($track->getLinkURL());
        }
    }
    Html::back();
} elseif (isset($_POST['update'])) {
    $track->check($_POST['id'], UPDATE);
    $track->update($_POST);

    if (isset($_POST['kb_linked_id'])) {
        //if solution should be linked to selected KB entry
        $params = [
           'knowbaseitems_id' => $_POST['kb_linked_id'],
           'itemtype'         => $track->getType(),
           'items_id'         => $track->getID()
        ];
        $entityManager = config::getAdapter()->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
            ->select('k.id')
            ->from(\Itsmng\Domain\Entities\KnowbaseItemItem::class, 'k')
            ->where('k.knowbaseitemsId = :kb_id')
            ->andWhere('k.itemtype = :itemtype')
            ->andWhere('k.items_id = :items_id')
            ->setParameter('kb_id', $_POST['kb_linked_id'])
            ->setParameter('itemtype', $track->getType())
            ->setParameter('items_id', $track->getID());

        $existing = $queryBuilder->getQuery()->getArrayResult();

        if (count($existing) === 0) {
            $kbItemItem = new Itsmng\Domain\Entities\KnowbaseItemItem();
            $kbItemItem->setKnowbaseItemsId((int)$_POST['kb_linked_id']);
            $kbItemItem->setItemtype($track->getType());
            $kbItemItem->setItemsId($track->getID());
            $entityManager->persist($kbItemItem);
            $entityManager->flush();
        }
    }

    if (isset($_POST['files'])) {
        $files = json_decode(stripslashes($_POST['files']), true);
        foreach ($files as $file) {
            $doc = ItsmngUploadHandler::addFileToDb($file);
            ItsmngUploadHandler::linkDocToItem(
                $doc->getID(),
                Session::getActiveEntity(),
                Session::getIsActiveEntityRecursive(),
                'Ticket',
                $_POST['id'],
                Session::getLoginUserID()
            );
        }
    }

    Event::log(
        $_POST["id"],
        "ticket",
        4,
        "tracking",
        //TRANS: %s is the user login
        sprintf(__('%s updates an item'), $_SESSION["glpiname"])
    );


    if ($track->can($_POST["id"], READ)) {
        $toadd = '';
        // Copy solution to KB redirect to KB
        if (isset($_POST['_sol_to_kb']) && $_POST['_sol_to_kb']) {
            $toadd = "&_sol_to_kb=1";
        }
        Html::redirect(Ticket::getFormURLWithID($_POST["id"]) . $toadd);
    }
    Session::addMessageAfterRedirect(
        __('You have been redirected because you no longer have access to this ticket'),
        true,
        ERROR
    );
    Html::redirect($CFG_GLPI["root_doc"] . "/front/ticket.php");
} elseif (isset($_POST['delete'])) {
    $track->check($_POST['id'], DELETE);
    if ($track->delete($_POST)) {
        Event::log(
            $_POST["id"],
            "ticket",
            4,
            "tracking",
            //TRANS: %s is the user login
            sprintf(__('%s deletes an item'), $_SESSION["glpiname"])
        );
    }
    $track->redirectToList();
} elseif (isset($_POST['purge'])) {
    $track->check($_POST['id'], PURGE);
    if ($track->delete($_POST, 1)) {
        Event::log(
            $_POST["id"],
            "ticket",
            4,
            "tracking",
            //TRANS: %s is the user login
            sprintf(__('%s purges an item'), $_SESSION["glpiname"])
        );
    }
    $track->redirectToList();
} elseif (isset($_POST["restore"])) {
    $track->check($_POST['id'], DELETE);
    if ($track->restore($_POST)) {
        Event::log(
            $_POST["id"],
            "ticket",
            4,
            "tracking",
            //TRANS: %s is the user login
            sprintf(__('%s restores an item'), $_SESSION["glpiname"])
        );
    }
    $track->redirectToList();
} elseif (isset($_POST['sla_delete'])) {
    $track->check($_POST["id"], UPDATE);

    $track->deleteLevelAgreement("SLA", $_POST["id"], $_POST['type'], $_POST['delete_date']);
    Event::log(
        $_POST["id"],
        "ticket",
        4,
        "tracking",
        //TRANS: %s is the user login
        sprintf(__('%s updates an item'), $_SESSION["glpiname"])
    );

    Html::redirect(Ticket::getFormURLWithID($_POST["id"]));
} elseif (isset($_POST['ola_delete'])) {
    $track->check($_POST["id"], UPDATE);

    $track->deleteLevelAgreement("OLA", $_POST["id"], $_POST['type'], $_POST['delete_date']);
    Event::log(
        $_POST["id"],
        "ticket",
        4,
        "tracking",
        //TRANS: %s is the user login
        sprintf(__('%s updates an item'), $_SESSION["glpiname"])
    );

    Html::redirect(Ticket::getFormURLWithID($_POST["id"]));
} elseif (isset($_POST['addme_observer'])) {
    $track->check($_POST['tickets_id'], READ);
    $input = array_merge(Toolbox::addslashes_deep($track->fields), [
       'id' => $_POST['tickets_id'],
       '_itil_observer' => [
          '_type' => "user",
          'users_id' => Session::getLoginUserID(),
          'use_notification' => 1,
       ]
    ]);
    $track->update($input);
    Event::log(
        $_POST['tickets_id'],
        "ticket",
        4,
        "tracking",
        //TRANS: %s is the user login
        sprintf(__('%s adds an actor'), $_SESSION["glpiname"])
    );
    Html::redirect(Ticket::getFormURLWithID($_POST['tickets_id']));
} elseif (isset($_POST['addme_assign'])) {
    $track->check($_POST['tickets_id'], READ);
    $input = array_merge(Toolbox::addslashes_deep($track->fields), [
       'id' => $_POST['tickets_id'],
       '_itil_assign' => [
          '_type' => "user",
          'users_id' => Session::getLoginUserID(),
          'use_notification' => 1,
       ]
    ]);
    $track->update($input);
    Event::log(
        $_POST['tickets_id'],
        "ticket",
        4,
        "tracking",
        //TRANS: %s is the user login
        sprintf(__('%s adds an actor'), $_SESSION["glpiname"])
    );
    Html::redirect(Ticket::getFormURLWithID($_POST['tickets_id']));
} elseif (isset($_POST['delete_document'])) {
    $track->getFromDB((int)$_POST['tickets_id']);
    $doc = new Document();
    $doc->getFromDB(intval($_POST['documents_id']));
    if ($doc->can($doc->getID(), UPDATE)) {
        $document_item = new Document_Item();
        $found_document_items = $document_item->find([
           $track->getAssociatedDocumentsCriteria(),
           'documents_id' => $doc->getID()
        ]);
        foreach ($found_document_items as $item) {
            $document_item->delete(Toolbox::addslashes_deep($item), true);
        }
    }
    Html::back();
}

if (isset($_GET["id"]) && ($_GET["id"] > 0)) {
    Html::header(Ticket::getTypeName(Session::getPluralNumber()), '', "helpdesk", "ticket");

    $available_options = ['load_kb_sol', '_openfollowup'];
    $options           = [];
    foreach ($available_options as $key) {
        if (isset($_GET[$key])) {
            $options[$key] = $_GET[$key];
        }
    }


    $options['id'] = $_GET["id"];
    $track->display($options);

    if (isset($_GET['_sol_to_kb'])) {
        Ajax::createIframeModalWindow(
            'savetokb',
            KnowbaseItem::getFormURL() .
                                       "?_in_modal=1&item_itemtype=Ticket&item_items_id=" .
                                       $_GET["id"],
            ['title'         => __('Save solution to the knowledge base'),
                                            'reloadonclose' => false]
        );
        echo Html::scriptBlock('$(function() {' . Html::jsGetElementbyID('savetokb') . ".dialog('open'); });");
    }
} else {
    if (Session::getCurrentInterface() != 'central') {
        Html::redirect($CFG_GLPI["root_doc"] . "/front/helpdesk.public.php?create_ticket=1");
        die;
    }

    Html::header(__('New ticket'), '', "helpdesk", "ticket");
    unset($_REQUEST['id']);
    unset($_GET['id']);
    unset($_POST['id']);

    // alternative email must be empty for create ticket
    unset($_REQUEST['_users_id_requester_notif']['alternative_email']);
    unset($_REQUEST['_users_id_observer_notif']['alternative_email']);
    unset($_REQUEST['_users_id_assign_notif']['alternative_email']);
    unset($_REQUEST['_suppliers_id_assign_notif']['alternative_email']);
    // Add a ticket from item : format data
    if (
        isset($_REQUEST['_add_fromitem'])
        && isset($_REQUEST['itemtype'])
        && isset($_REQUEST['items_id'])
    ) {
        $_REQUEST['items_id'] = [$_REQUEST['itemtype'] => [$_REQUEST['items_id']]];
    }
    $track->display($_REQUEST);
}

Html::footer();
