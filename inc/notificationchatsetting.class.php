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
 *  This class manages the chat settings
 */
class NotificationChatSetting extends NotificationSetting
{

    static public function getTypeName($nb = 0)
    {
        return __('Chat followups configuration');
    }


    public function getEnableLabel()
    {
        return __('Enable followups via chat');
    }


    static public function getMode()
    {
        return Notification_NotificationTemplate::MODE_CHAT;
    }


    function showFormConfig($options = [])
    {
        global $CFG_GLPI;

        if (!isset($options['display'])) {
            $options['display'] = true;
        }

        $out = "<form action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "' method='post'>";
        $out .= "<div>";
        $out .= "<input type='hidden' name='id' value='1'>";
        $out .= "<table class='tab_cadre_fixe'>";


        $out .= "<tr class='tab_bg_1'><th colspan='4'>" . _n(
            'Chat notification',
            'Chat notifications',
            Session::getPluralNumber()
        ) . "</th></tr>";

        if ($CFG_GLPI['notifications_chat']) {

            $out .= "<tr class='tab_bg_2'>";

            $chatmoderand = mt_rand();
            $out .= "<td><label for='dropdown_chat_mode$chatmoderand'>" . __('Mode') . "</label></td><td>";
            $chat_modes = [
                CHAT_SLACK      => __('Slack'),
                CHAT_ROCKET     => __('Rocket chat'),
                CHAT_TEAMS      => __('Teams')

            ];

            $out .= Dropdown::showFromArray(
                "chat_mode",
                $chat_modes,
                [
                    'value'     => CHAT_SLACK,
                    'display'   => false,
                    'rand'      => $chatmoderand
                ]
            );
            $out .= Html::scriptBlock("$(function() {
            console.log($('[name=chat_mode]'));
            $('[name=chat_mode]').on('change', function() {
               var _val = $(this).find('option:selected').val();

               if (_val == '" . CHAT_ROCKET . "') {
                  $('#chat_config').removeClass('starthidden');
                  
               } else {
                  $('#chat_config').addClass('starthidden');
               }
            });
            });");
            $out .= "</td>";
            $out .= "</tr>";

            $out .= "</table>";

            $out .= "<table class= 'tab_cadre_fixe";

            if ($CFG_GLPI["chat_mode"] == CHAT_ROCKET) {
                $out .= " starthidden";
                $out .= "' id='chat_config'>";


                $out .= "<tr class='tab_bg_2'>";
                $out .= "<td><label for='rocketurl'>" . __('URL') . "</label></td>";
                $out .= "<td><input type='text' name='rocketurl' id='rocketurl' size='40' value='" .
                    $CFG_GLPI["rocketurl"] . "'>";
                $out .= "</td></tr>";

                $out .= "</table>";
            }
        } else {
            $out .= "<tr><td colspan='4'>" . __('Notifications are disabled.')  .
                "<a href='{$CFG_GLPI['root_doc']}/front/setup.notification.php'>" .
                __('See configuration') . "</a></td></tr>";
            $out .= "</table>";
        }
        $options['candel']     = false;
        if ($CFG_GLPI['notifications_chat']) {
            $options['addbuttons'] = ['test_chat_send' => __('Send a test chat')];
        }
        //do not satisfy display param since showFormButtons() will not :(
        echo $out;
        $this->showFormButtons($options);
    }
}
