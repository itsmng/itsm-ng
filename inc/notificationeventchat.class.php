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
    public static function getTargetFieldName()
    {
        return 'chat';
    }


    public static function getTargetField(&$data)
    {
        $field = self::getTargetFieldName();
        if (!isset($data[$field])) {
            //Missing field; set to null
            $data[$field] = 1;
        }

        return $field;
    }

    public static function canCron()
    {
        return true;
    }


    public static function getAdminData()
    {
        global $CFG_GLPI;

        return [
           'email'     => $CFG_GLPI['admin_email'],
           'name'      => $CFG_GLPI['admin_email_name'],
           'language'  => $CFG_GLPI['language']
        ];

        //return true;
    }
    public static function getEntityAdminsData($entity)
    {

        return true;
    }

    public static function send(array $data)
    {
        global $CFG_GLPI, $DB;

        $processed = [];

        foreach ($data as $row) {
            $current = new QueuedChat();
            $current->getFromResultSet($row);

            $sendChat = new NotificationChatConfig();
            $list = $sendChat->find(['type' => 'all']);
            $webHooks = [];
            if (empty($list)) {
                $list['entity'] = $sendChat->find(['type' => 'entity', 'value' => $row['entities_id']]);
                $list['location'] = $sendChat->find(['type' => 'location', 'value' => $row['locations_id']]);
                $list['group'] = $sendChat->find(['type' => 'group', 'value' => $row['groups_id']]);
                $list['category'] = $sendChat->find(['type' => 'category', 'value' => $row['itilcategories_id']]);

                foreach ($list as $value) {
                    foreach ($value as $val) {
                        if (!in_array($val['hookurl'], $webHooks)) {
                            $webHooks[] = $val['hookurl'];
                        }
                    }
                }
            } else {
                foreach ($list as $value) {
                    $webHooks[] = $value['hookurl'];
                }
            }

            foreach ($webHooks as $value) {
                $sendChat->sendRocketNotificationNew($current->fields['ticketTitle'], $current->fields['items_id'], $current->fields['entName'], $current->fields['serverName'], $value);
            }

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
