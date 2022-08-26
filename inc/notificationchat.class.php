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



    static function testNotification()
    {
        global $CFG_GLPI;
        $rocketNotifConfiguration = new NotificationChatSend();
        $config = $rocketNotifConfiguration->find();
        $rocketHookUrl = $config[key($config)]['rockethookurl'];

        $glpiUrl = '172.18.25.160/itsm-ng';
        $ticketId = '1';
        $entName = 'parent';
        $ticketTitle = 'test ticket';
        $rocketHookUrl = $rocketHookUrl;

        $sendNotif = new NotificationChatSend();
        $sendNotif->sendRocketNotification($ticketId, $entName, $ticketTitle, $glpiUrl, $rocketHookUrl);
    }


    function sendNotification($options = [])
    {

        $data = [];
        $data['itemtype']                             = $options['_itemtype'];
        $data['items_id']                             = $options['_items_id'];
        $data['notificationtemplates_id']             = $options['_notificationtemplates_id'];
        $data['entities_id']                          = $options['_entities_id'];


        $post = new Ticket();
        $data['ticketId']                             = $post->fields['id'];
        $data['ticketTitle']                          = $post->fields['name'];

        $data['serverName']                           = $_SERVER['SERVER_NAME'] . $_SESSION['glpiroot'];

        $rocketNotifConfiguration = new NotificationChatSend();
        $config = $rocketNotifConfiguration->find();
        $rocketHookUrl = $config[key($config)]['rockethookurl'];
        $data['rocketHookUrl']                               = $rocketHookUrl;

        $data['mode'] = Notification_NotificationTemplate::MODE_CHAT;

        $queue = new QueuedNotification();

        if (!$queue->add(Toolbox::addslashes_deep($data))) {
            Session::addMessageAfterRedirect(__('Error inserting chat to queue'), true, ERROR);
            return false;
        } else {
            //TRANS to be written in logs %1$s is the to email / %2$s is the subject of the mail
            Toolbox::logInFile(
                "chat",
                sprintf(
                    __('The chat  %s was added to queue'),
                    $post->fields['name']
                ),

            );
        }

        return true;
    }
}
