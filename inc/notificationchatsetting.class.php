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
        global $CFG_GLPI, $DB;

        if (!isset($options['display'])) {
            $options['display'] = true;
        }

        $out = "<form action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "' method='post'>";
        $out .= "<div>";
        $out .= "<input type='hidden' name='id' value='1'>";
        $out .= Html::scriptBlock("$(function() {

        $('[name=value]').prop('disabled', true);
        $('[name=value_entity]').attr('hidden', true);
        $('[name=value_group]').attr('hidden', true);
        $('[name=value_location]').attr('hidden', true);
        $('[name=value_category]').attr('hidden', true);

        $('[name=type]').on('change', function() {
            var _val = $(this).find('option:selected').val();
            if (_val == 'all') {
                $('[name=value_all]').attr('hidden', false);
                $('[name=value_entity]').attr('hidden', true);
                $('[name=value_group]').attr('hidden', true);
                $('[name=value_location]').attr('hidden', true);
                $('[name=value_category]').attr('hidden', true);
            } else if (_val == 'entity') {
                $('[name=value_all]').attr('hidden', true);
                $('[name=value_entity]').attr('hidden', false);
                $('[name=value_group]').attr('hidden', true);
                $('[name=value_location]').attr('hidden', true);
                $('[name=value_category]').attr('hidden', true);
            } else if (_val == 'group') {
                $('[name=value_all]').attr('hidden', true);
                $('[name=value_entity]').attr('hidden', true);
                $('[name=value_group]').attr('hidden', false);
                $('[name=value_location]').attr('hidden', true);
                $('[name=value_category]').attr('hidden', true);
            } else if (_val == 'location') {
                $('[name=value_all]').attr('hidden', true);
                $('[name=value_entity]').attr('hidden', true);
                $('[name=value_group]').attr('hidden', true);
                $('[name=value_location]').attr('hidden', false);
                $('[name=value_category]').attr('hidden', true);
            } else if (_val == 'category') {
                $('[name=value_all]').attr('hidden', true);
                $('[name=value_entity]').attr('hidden', true);
                $('[name=value_group]').attr('hidden', true);
                $('[name=value_location]').attr('hidden', true);
                $('[name=value_category]').attr('hidden', false);
            }
        });
        });");
        $out .= "<table class='tab_cadre_fixe'>";


        $out .= "<tr class='tab_bg_1'><th colspan='8'>" . _n(
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

            $types = [
                'all'      => __("All"),
                'entity'   => __("Entity"),
                'group'    => __("Group"),
                'location' => __("Location"),
                'category' => __("ITIL category")
            ];

            $groupsRaw = (new Group)->find();
            $groups = [];
            foreach ($groupsRaw as $key => $group) {
                $groups[$group['id']] = $group['completename'];
            }

            $entitiesRaw = (new Entity)->find();
            $entities = [];
            foreach ($entitiesRaw as $key => $entity) {
                $entities[$entity['id']] = $entity['completename'];
            }

            $locationsRaw = (new Location)->find();
            $locations = [];
            foreach ($locationsRaw as $key => $location) {
                $locations[$location['id']] = $location['completename'];
            }

            $categoriesRaw = (new ITILCategory)->find();
            $categories = [];
            foreach ($categoriesRaw as $key => $category) {
                $categories[$category['id']] = $category['completename'];
            }

            $out .= Dropdown::showFromArray(
                "chat_mode",
                $chat_modes,
                [
                    'value'     => CHAT_SLACK,
                    'display'   => false,
                    'rand'      => $chatmoderand
                ]
            );
            $out .= "</td>";
            $out .= "<td><label for='hookurl'>" . __('URL') . "</label></td>";
            $out .= "<td><input type='text' name='hookurl' id='hookurl'></td>";
            $out .= "<td><label for='type'>" . __('Type') . "</label></td>";
            //$out .= "<td><input type='text' name='type' id='type'></td>";
            $out .= "<td>";
            $out .= Dropdown::showFromArray(
                'type', 
                $types, 
                [
                    'value' => 'all',
                    'display'   => false,
                ]
            );
            $out .= "</td>";
            $out .= "<td><label for='value'>" . __('Value') . "</label></td>";
            //$out .= "<td><input type='text' name='value' id='value'></td>";
            $out .= "<td name='value_all'><input type='text' name='value' id='value' disable></td>";
            $out .= "<td name='value_entity'>";
            $out .= Dropdown::showFromArray(
                'value_entity', 
                $entities, 
                [
                    'display'   => false,
                ]
            );
            $out .= "</td>";
            $out .= "<td name='value_group'>";
            $out .= Dropdown::showFromArray(
                'value_group', 
                $groups, 
                [
                    'display'   => false,
                ]
            );
            $out .= "</td>";
            $out .= "<td name='value_location'>";
            $out .= Dropdown::showFromArray(
                'value_location', 
                $locations, 
                [
                    'display'   => false,
                ]
            );
            $out .= "</td>";
            $out .= "<td name='value_category'>";
            $out .= Dropdown::showFromArray(
                'value_category', 
                $categories, 
                [
                    'display'   => false,
                ]
            );
            $out .= "</td>";
            $out .= "</tr>";

        } else {
            $out .= "<tr><td colspan='6'>" . __('Notifications are disabled.')  .
                "<a href='{$CFG_GLPI['root_doc']}/front/setup.notification.php'>" .
                __('See configuration') . "</a></td></tr>";
            $out .= "</table>";
        }
        $options['candel']     = false;
        $options['colspan']     = 12;
        
        //do not satisfy display param since showFormButtons() will not :(
        echo $out;
        $this->showFormButtons($options);


        // Display existing configs
        echo "<div>";

        $query = "SELECT * FROM glpi_notificationchatconfigs";
        $iterators = $DB->request($query);

        $result = [];
        foreach ($iterators as $key => $iterator) {
            $res = [];
            $res['hookurl'] = $iterator['hookurl'];
            $res['chat'] = $iterator['chat'];
            $res['type'] = $iterator['type'];
            $res['value'] = $iterator['value'];
            $res['id'] = $iterator['id'];

            $result[] = $res;
        }

        echo "<table class='tab_cadre_fixe'>";
        echo "<tbody>";
        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='6'>" . "Liste des configs chats" . "</th>";
        echo "</tr>";
        foreach ($result as $key => $value) {
            echo "<tr class='tab_bg_2'>";
            echo "<td>" . $chat_modes[$value['chat']] . "</td>";
            echo "<td>" . $value['hookurl'] . "</td>";
            echo "<td>" . $types[$value['type']] . "</td>";

            switch ($value['type']) {
                case 'entity':
                    echo "<td>" . $entities[$value['value']] . "</td>";
                    break;
                case 'group':
                    echo "<td>" . $groups[$value['value']] . "</td>";
                    break;
                case 'location':
                    echo "<td>" . $locations[$value['value']] . "</td>";
                    break;
                case 'category':
                    echo "<td>" . $categories[$value['value']] . "</td>";
                    break;   
                default:
                    echo "<td>" . $value['value'] . "</td>";
                    break;
            }
            
            echo "<td><a href='notificationchatsetting.form.php?test=" . $value['id'] ."' class='vsubmit'>" . __("Test") . "</a></td>";
            echo "<td><a href='notificationchatsetting.form.php?delete=" . $value['id'] ."' class='vsubmit'>" . __("Delete") . "</a></td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";

        echo "</div>";
    }

}
