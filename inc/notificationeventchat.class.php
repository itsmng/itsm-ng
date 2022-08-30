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

use PHPMailer\PHPMailer\PHPMailer;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class NotificationEventChat extends NotificationEventAbstract implements NotificationEventInterface
{

    static public function getTargetFieldName()
    {
        return 'chat';
    }


    static public function getTargetField(&$data)
    {
        $field = self::getTargetFieldName();
        if (!isset($data[$field])) {
            //Missing field; set to null
            $data[$field] = null;
        }

        return $field;
    }

    static public function canCron()
    {
        return true;
    }


    static public function getAdminData()
    {

        return false;
    }
    static public function getEntityAdminsData($entity)
    {

        return false;
    }

    static public function send(array $data)
    {
        global $CFG_GLPI, $DB;

        $processed = [];

        foreach ($data as $row) {
            $current = new QueuedChat();
            $current->getFromResultSet($row);

            $sendChat = new NotificationChatSend();
            $sendChat->sendRocketNotification($current->fields['ticketTitle'], $current->fields['items_id'], $current->fields['entName'],  $current->fields['serverName'], $current->fields['rocketHookUrl']);

            $processed[] = $current->getID();
            $current->update([
                'id'        => $current->fields['id'],
                'sent_time' => $_SESSION['glpi_currenttime']
            ]);
            $current->delete(['id'        => $current->fields['id']]);
        }

        return count($processed);
    }
}
