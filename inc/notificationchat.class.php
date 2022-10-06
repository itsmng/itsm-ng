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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 *  NotificationChat class implements the NotificationInterface
 **/
class NotificationChat implements NotificationInterface
{

    /**
     * Check data
     *
     * @param mixed $value   The data to check (may differ for every notification mode)
     * @param array $options Optionnal special options (may be needed)
     *
     * @return boolean
     **/
    static function check($value, $options = [])
    {
    }



    static function testNotification($id = 1)
    {
        global $CFG_GLPI;
        $rocketNotifConfiguration = new NotificationChatConfig();
        $config = $rocketNotifConfiguration->find(['id'=> $id]);
        $hookurl = $config[key($config)]['hookurl'];

        $glpiUrl = 'localhost/itsm-ng';
        $entName = 'parent';
        $ticketId = 1;
        $ticketTitle = 'test static data';
        $hookurl = $hookurl;

        $sendNotif = new NotificationChatConfig();
        $sendNotif->sendRocketNotification($ticketTitle, $ticketId, $entName, $glpiUrl, $hookurl);
    }


    function sendNotification($options = [])
    {
        global $DB;
        $data = [];
        $data['itemtype']                             = $options['_itemtype'];
        $data['items_id']                             = $options['_items_id'];
        $data['notificationtemplates_id']             = $options['_notificationtemplates_id'];
        $data['entities_id']                          = $options['_entities_id'];
        $data['locations_id']                         = $options['_locations_id'];
        $data['groups_id']                            = $options['_groups_id'];
        $data['itilcategories_id']                    = $options['_itilcategories_id'];

        $data['completName']                          = $options['subject'];

        $data['serverName']                           = $_SERVER['SERVER_NAME'] . $_SESSION['glpiroot'];

        /* $entity = new Entity();
        if ($entity->getFromDB($options['_entities_id'])) {
            $entName = $entity->getField('name');
            $data['entName']                          = $entName;
        } */
        $data['entName'] = $options['content_text'];

        $ticket = new Ticket();
        if ($ticket->getFromDB($options['_items_id'])) {
            $ticketTitle = $ticket->getField('name');
            $data['ticketTitle']                      = $ticketTitle;
        }




        $rocketNotifConfiguration = new NotificationChatConfig();
        $config = $rocketNotifConfiguration->find();
        $hookurl = $config[key($config)]['hookurl'];
        $data['hookurl'] = $hookurl;

        $data['mode'] = Notification_NotificationTemplate::MODE_CHAT;

        $queue = new QueuedChat();

        if (!$queue->add(Toolbox::addslashes_deep($data))) {
            Session::addMessageAfterRedirect(__('Error inserting chat to queue'), true, ERROR);
            Toolbox::logInFile(
                "chat-error",
                sprintf(
                    __('Fatal-error: The chat %s was not added to queue '),
                    $data['completName']
                ) . "\n"

            );
            return false;
        } else {
            //TRANS to be written in logs %1$s is the to email / %2$s is the subject of the mail
            Toolbox::logInFile(
                "chat",
                sprintf(
                    __('Rocket chat: The chat %s was added to queue '),
                    $data['completName']
                ) . "\n"

            );
        }

        return true;
    }
}
