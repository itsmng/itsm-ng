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

Session::checkRight("config", UPDATE);
$notificationChatSetting = new NotificationChatSetting();
$notificationChatSend = new NotificationChatConfig();


if (!empty($_POST["test_chat_send"])) {
    NotificationChat::testNotification();
    Html::back();
} else if (!empty($_POST["update"]) && isset($_POST['rocketurl'])) {
    $config = new Config();
    $config->update($_POST);
    $notificationChatSend->processPostData($_POST['rocketurl']);
    Html::back();
}

Html::header(Notification::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], "config", "notification", "config");
$notificationChatSetting->display(['id' => 1]);


Html::footer();
