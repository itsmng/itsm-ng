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

// Change profile system
if (isset($_POST['newprofile'])) {
    if (isset($_SESSION["glpiprofiles"][$_POST['newprofile']])) {
        Session::changeProfile($_POST['newprofile']);

        if (Session::getCurrentInterface() == "central") {
            Html::redirect($CFG_GLPI['root_doc'] . "/front/central.php");
        } else {
            Html::redirect($_SERVER['PHP_SELF']);
        }
    } else {
        Html::redirect(preg_replace("/entities_id=.*/", "", (string) $_SERVER['HTTP_REFERER']));
    }
}

// Manage entity change
if (isset($_GET["active_entity"])) {
    $_GET["active_entity"] = rtrim((string) $_GET["active_entity"], 'r');
    if (!isset($_GET["is_recursive"])) {
        $_GET["is_recursive"] = 0;
    }
    if (Session::changeActiveEntities($_GET["active_entity"], $_GET["is_recursive"])) {
        if ($_GET["active_entity"] == $_SESSION["glpiactive_entity"]) {
            Html::redirect(preg_replace("/(\?|&|" . urlencode('?') . "|" . urlencode('&') . ")?(entities_id|active_entity).*/", "", (string) $_SERVER['HTTP_REFERER']));
        }
    }
}

// Redirect management
if (isset($_GET["redirect"])) {
    Toolbox::manageRedirect($_GET["redirect"]);
}

// redirect if no create ticket right
if (
    !Session::haveRight('ticket', CREATE)
    && !Session::haveRight('reminder_public', READ)
    && !Session::haveRight("rssfeed_public", READ)
) {
    if (
        Session::haveRight('followup', ITILFollowup::SEEPUBLIC)
        || Session::haveRight('task', TicketTask::SEEPUBLIC)
        || Session::haveRightsOr('ticketvalidation', [TicketValidation::VALIDATEREQUEST,
                                                           TicketValidation::VALIDATEINCIDENT])
    ) {
        Html::redirect($CFG_GLPI['root_doc'] . "/front/ticket.php");
    } elseif (Session::haveRight('reservation', ReservationItem::RESERVEANITEM)) {
        Html::redirect($CFG_GLPI['root_doc'] . "/front/reservationitem.php");
    } elseif (Session::haveRight('knowbase', KnowbaseItem::READFAQ)) {
        Html::redirect($CFG_GLPI['root_doc'] . "/front/helpdesk.faq.php");
    }
}

Session::checkHelpdeskAccess();


Html::header(__('Home'), $_SERVER['PHP_SELF'], $_SESSION["glpiname"]);
if (isset($_GET['create_ticket'])) {
    $ticket = new Ticket();
    $ticket->showFormHelpdesk(Session::getLoginUserID());
} else {
    $user = new User();
    $user->getFromDB(Session::getLoginUserID());
    if ($user->fields['authtype'] == Auth::DB_GLPI && $user->shouldChangePassword()) {
        $expiration_msg = sprintf(__('Your password will expire on %s.'),
            Html::convDateTime(date('Y-m-d H:i:s', $user->getPasswordExpirationTime())));
        echo "<div class='alert alert-warning'>" . $expiration_msg . ' '
            . "<a href='" . $CFG_GLPI['root_doc'] . "/front/updatepassword.php'>" . __('Update my password') . '</a></div>';
    }
    // Preserve the table-row contract of external dashboard hooks.
    ob_start();
    Plugin::doHook('display_central');
    $plugin_content = ob_get_clean();
    if (trim($plugin_content) !== '') {
        echo "<table class='central w-100'>" . $plugin_content . '</table>';
    }
    echo "<div class='row g-3'><div class='col-12 col-xl-6'>";
    if (Session::haveRight('ticket', CREATE)) {
        Ticket::showCentralCount(true);
        Ticket::showCentralList(0, 'survey', false);
    }
    if (Session::haveRight('reminder_public', READ)) {
        Reminder::showListForCentral(false);
    }
    if (Session::haveRight('rssfeed_public', READ)) {
        RSSFeed::showListForCentral(false);
    }
    echo "</div><div class='col-12 col-xl-6'>";
    if (Session::haveRight('knowbase', KnowbaseItem::READFAQ)) {
        foreach (['popular', 'recent', 'lastupdate'] as $type) {
            KnowbaseItem::showRecentPopular($type);
        }
    }
    echo '</div></div>';
}

Html::footer();
