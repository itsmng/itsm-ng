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


/** QueuedChat class
 *
 * @since 0.85
 **/
class QueuedChat extends CommonDBTM
{
    public static $rightname = 'queuedchat';


    public static function getTypeName($nb = 0)
    {
        return __('Chat queue');
    }


    public static function canCreate()
    {
        // Everybody can create : human and cron
        return Session::getLoginUserID(false);
    }

    public static function getForbiddenActionsForMenu()
    {
        return ['add'];
    }


    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }

    /**
     * @see CommonDBTM::getSpecificMassiveActions()
     **/
    public function getSpecificMassiveActions($checkitem = null, $is_deleted = false)
    {

        $isadmin = static::canUpdate();
        $actions = parent::getSpecificMassiveActions($checkitem);

        if ($isadmin && !$is_deleted) {
            $actions[__CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'sendchat'] = _x('button', 'Send');
        }

        return $actions;
    }

    /**
     * @see CommonDBTM::processMassiveActionsForOneItemtype()
     **/
    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {
        switch ($ma->getAction()) {
            case 'sendchat':
                foreach ($ids as $id) {
                    if ($item->canEdit($id)) {
                        if ($item->sendById($id)) {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                        } else {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        }
                    } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_NORIGHT);
                    }
                }
                return;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    public function prepareInputForAdd($input)
    {
        global $DB;

        if (!isset($input['create_time']) || empty($input['create_time'])) {
            $input['create_time'] = $_SESSION["glpi_currenttime"];
        }
        if (!isset($input['send_time']) || empty($input['send_time'])) {
            $toadd = 0;
            if (isset($input['entities_id'])) {
                $toadd = Entity::getUsedConfig('delay_send_emails', $input['entities_id']);
            }
            if ($toadd > 0) {
                $input['send_time'] = date(
                    "Y-m-d H:i:s",
                    strtotime($_SESSION["glpi_currenttime"])
                        + $toadd * MINUTE_TIMESTAMP
                );
            } else {
                $input['send_time'] = $_SESSION["glpi_currenttime"];
            }
        }
        $input['sent_try'] = 0;


        // Force items_id to integer
        if (!isset($input['items_id']) || empty($input['items_id'])) {
            $input['items_id'] = 0;
        }

        // Drop existing mails in queue for the same event and item
        if (
            isset($input['itemtype']) && !empty($input['itemtype'])
            && isset($input['entities_id']) && ($input['entities_id'] >= 0)
            && isset($input['items_id']) && ($input['items_id'] >= 0)
            && isset($input['notificationtemplates_id']) && !empty($input['notificationtemplates_id'])
        ) {
            $criteria = [
                'FROM'   => $this->getTable(),
                'WHERE'  => [
                    'is_deleted'   => 0,
                    'itemtype'     => $input['itemtype'],
                    'items_id'     => $input['items_id'],
                    'entities_id'  => $input['entities_id'],
                    'notificationtemplates_id' => $input['notificationtemplates_id'],


                ]
            ];
            $request = $this::getAdapter()->request($criteria);
            while ($data = $request->fetchAssociative()) {
                $this->delete(['id' => $data['id']], 1);
            }
        }

        return $input;
    }


    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id'                 => 'common',
            'name'               => __('Characteristics')
        ];
        $tab[] = [
            'id'                 => '1',
            'table'              => $this->getTable(),
            'field'              => 'completName',
            'name'               => __('Subject'),
            'datatype'           => 'itemlink',
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '2',
            'table'              => $this->getTable(),
            'field'              => 'id',
            'name'               => __('ID'),
            'massiveaction'      => false,
            'datatype'           => 'number'
        ];

        $tab[] = [
            'id'                 => '16',
            'table'              => $this->getTable(),
            'field'              => 'create_time',
            'name'               => __('Creation date'),
            'datatype'           => 'datetime',
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '3',
            'table'              => $this->getTable(),
            'field'              => 'send_time',
            'name'               => __('Expected send date'),
            'datatype'           => 'datetime',
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '4',
            'table'              => $this->getTable(),
            'field'              => 'sent_time',
            'name'               => __('Send date'),
            'datatype'           => 'datetime',
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '15',
            'table'              => $this->getTable(),
            'field'              => 'sent_try',
            'name'               => __('Number of tries of sent'),
            'datatype'           => 'integer',
            'massiveaction'      => false
        ];
        $tab[] = [
            'id'                 => '20',
            'table'              => $this->getTable(),
            'field'              => 'itemtype',
            'name'               => _n('Type', 'Types', 1),
            'datatype'           => 'itemtype',
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '21',
            'table'              => $this->getTable(),
            'field'              => 'items_id',
            'name'               => __('Associated item ID'),
            'massiveaction'      => false,
            'datatype'           => 'integer'
        ];

        $tab[] = [
            'id'                 => '23',
            'table'              => 'glpi_queuedchats',
            'field'              => 'entName',
            'name'               => __('Entity name'),
            'massiveaction'      => false,
            'datatype'           => 'text'
        ];

        $tab[] = [
            'id'                 => '24',
            'table'              => 'glpi_queuedchats',
            'field'              => 'serverName',
            'name'               => __('Server name'),
            'massiveaction'      => false,
            'datatype'           => 'string'
        ];

        $tab[] = [
            'id'                 => '25',
            'table'              => 'glpi_queuedchats',
            'field'              => 'hookurl',
            'name'               => __('URl Hook'),
            'massiveaction'      => false,
            'datatype'           => 'itemtype'
        ];

        $tab[] = [
            'id'                 => '26',
            'table'              => 'glpi_queuedchats',
            'field'              => 'mode',
            'name'               => __('Mode'),
            'massiveaction'      => false,
            'datatype'           => 'specific',
            'searchtype'         => [
                0 => 'equals',
                1 => 'notequals'
            ]
        ];
        return $tab;
    }

    /**
     * @param $field
     * @param $values
     * @param $options   array
     **/
    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'headers':
                $values[$field] = importArrayFromDB($values[$field]);
                $out = '';
                if (is_array($values[$field]) && count($values[$field])) {
                    foreach ($values[$field] as $key => $val) {
                        $out .= $key . ': ' . $val . '<br>';
                    }
                }
                return $out;
                break;
            case 'mode':
                $out = Notification_NotificationTemplate::getMode($values[$field])['label'];
                return $out;
                break;
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }


    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;

        switch ($field) {
            case 'mode':
                $options['name']  = $name;
                $options['value'] = $values[$field];
                return Notification_NotificationTemplate::dropdownMode($options);
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }


    /**
     * Send chat in queue
     *
     * @param integer $ID Id
     *
     * @return boolean
     */
    public function sendById($ID)
    {
        if ($this->getFromDB($ID)) {
            $mode = $this->getField('mode');
            $eventclass = 'NotificationEvent' . ucfirst($mode);
            $conf = Notification_NotificationTemplate::getMode($mode);
            if ($conf['from'] != 'core') {
                $eventclass = 'Plugin' . ucfirst($conf['from']) . $eventclass;
            }

            return $eventclass::send([$this->fields]);
        } else {
            return false;
        }
    }

    /**
     * Give cron information
     *
     * @param $name : task's name
     *
     * @return array of information
     **/
    public static function cronInfo($name)
    {

        switch ($name) {
            case 'queuedchat':
                return [
                    'description' => __('Send chat in queue'),
                    'parameter'   => __('Maximum chat to send at once')
                ];

            case 'queuedchatclean':
                return [
                    'description' => __('Clean chat queue'),
                    'parameter'   => __('Days to keep sent chat')
                ];
        }
        return [];
    }



    /**
     * Get pending chat in queue
     *
     * @param string  $send_time   Maximum sent_time
     * @param integer $limit       Query limit clause
     * @param array   $limit_modes Modes to limit to
     * @param array   $extra_where Extra params to add to the where clause
     *
     * @return array
     */
    public static function getPendings($send_time = null, $limit = 20, $limit_modes = null, $extra_where = [])
    {
        global $DB, $CFG_GLPI;

        if ($send_time === null) {
            $send_time = date('Y-m-d H:i:s');
        }

        $base_query = [
            'FROM'   => self::getTable(),
            'WHERE'  => [
                'is_deleted'   => 0,
                'mode'         => 'TOFILL',
                'send_time'    => ['<', $send_time],
            ] +  $extra_where,
            'ORDER'  => 'send_time ASC',
            'START'  => 0,
            'LIMIT'  => $limit
        ];

        $pendings = [];
        $modes = Notification_NotificationTemplate::getModes();
        foreach ($modes as $mode => $conf) {
            $eventclass = 'NotificationEvent' . ucfirst($mode);
            if ($conf['from'] != 'core') {
                $eventclass = 'Plugin' . ucfirst($conf['from']) . $eventclass;
            }

            if (
                $limit_modes !== null && !in_array($mode, $limit_modes)
                || !$CFG_GLPI['notifications_' . $mode]
                || !$eventclass::canCron()
            ) {
                //mode is not in limits, is disabled, or cannot be called from cron, passing
                continue;
            }

            $query = $base_query;
            $query['WHERE']['mode'] = $mode;

            $request = self::getAdapter()->request($query);
            $results = $request->fetchAllAssociative();

            if (count($results) > 0) {
                $pendings[$mode] = [];
                foreach ($results as $row) {
                    $pendings[$mode][] = $row;
                }
            }
        }

        return $pendings;
    }


    /**
     * Cron action on chat queue: send chat in queue
     *
     * @param CommonDBTM $task for log (default NULL)
     *
     * @return integer either 0 or 1
     **/
    public static function cronQueuedChat($task = null)
    {
        if (!Notification_NotificationTemplate::hasActiveMode()) {
            return 0;
        }
        $cron_status = 0;

        // Send notifications at least 1 minute after adding in queue to be sure that process on it is finished
        $send_time = date("Y-m-d H:i:s", strtotime("+1 minutes"));

        $pendings = self::getPendings(
            $send_time,
            $task->fields['param']
        );

        foreach ($pendings as $mode => $data) {
            $eventclass = 'NotificationEvent' . ucfirst($mode);
            $conf = Notification_NotificationTemplate::getMode($mode);
            if ($conf['from'] != 'core') {
                $eventclass = 'Plugin' . ucfirst($conf['from']) . $eventclass;
            }

            $result = $eventclass::send($data);
            if ($result !== false) {
                $cron_status = 1;
                if (!is_null($task)) {
                    $task->addVolume($result);
                }
            }
        }

        return $cron_status;
    }


    /**
     * Cron action on queued notification: clean notification queue
     *
     * @param CommonDBTM $task for log (default NULL)
     *
     * @return integer either 0 or 1
     **/
    public static function cronQueuedChatClean($task = null)
    {
        global $DB;

        $vol = 0;

        // Expire chat in queue
        if ($task->fields['param'] > 0) {
            $secs      = $task->fields['param'] * DAY_TIMESTAMP;
            $send_time = date("U") - $secs;
            $adapter = self::getAdapter();
            $chats = $adapter->request([
                'SELECT' => ['id'],
                'FROM'   => self::getTable(),
                'WHERE'  => [
                    'is_deleted'   => 1,
                    'RAW' => [
                        'UNIX_TIMESTAMP(' . $DB->quoteName('send_time') . ') < ' . $DB->quoteValue($send_time)
                    ]
                ]
            ]);

            // Supprimer chaque chat individuellement
            foreach ($chats->fetchAllAssociative() as $data) {
                $chat = new self();
                if ($chat->getFromDB($data['id'])) {
                    if ($chat->deleteFromDB()) {
                        $vol++;
                    }
                }
            }
        }

        $task->setVolume($vol);
        return ($vol > 0 ? 1 : 0);
    }



    /**
     * Force sending all chat in queue for a specific item
     *
     * @param string  $itemtype item type
     * @param integer $items_id id of the item
     *
     * @return void
     **/
    public static function forceSendFor($itemtype, $items_id)
    {
        if (
            !empty($itemtype)
            && !empty($items_id)
        ) {
            $pendings = self::getPendings(
                null,
                1,
                null,
                [
                    'itemtype'  => $itemtype,
                    'items_id'  => $items_id
                ]
            );

            foreach ($pendings as $mode => $data) {
                $eventclass = Notification_NotificationTemplate::getModeClass($mode, 'event');
                $eventclass::send($data);
            }
        }
    }

    /**
     * Print the queued chat form
     *
     * @param integer $ID      ID of the item
     * @param array   $options Options
     *
     * @return true if displayed  false if item not found or not right to display
     **/
    public function showForm($ID, $options = [])
    {
        if (!Session::haveRight("queuedchat", READ)) {
            return false;
        }

        $this->check($ID, READ);
        $options['canedit'] = false;

        $this->showFormHeader($options);
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . _n('Type', 'Types', 1) . "</td>";

        echo "<td>";
        if (!($item = getItemForItemtype($this->fields['itemtype']))) {
            echo NOT_AVAILABLE;
            echo "</td>";
            echo "<td>" . _n('Name', 'Names', 1) . "</td>";
            echo "<td>";
            echo NOT_AVAILABLE;
        } elseif ($item instanceof CommonDBTM) {
            echo $item->getType();
            $item->getFromDB($this->fields['items_id']);
            echo "</td>";
            echo "<td>" . _n('Name', 'Names', 1) . "</td>";
            echo "<td>";
            echo $item->getLink();
        } else {
            echo get_class($item);
            echo "</td><td></td>";
        }
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . _n('Notification template', 'Notification templates', 1) . "</td>";
        echo "<td>";
        echo Dropdown::getDropdownName(
            'glpi_notificationtemplates',
            $this->fields['notificationtemplates_id']
        );
        echo "</td>";
        echo "<td>" . __('Ticket ID') . "</td>";
        echo "<td>" . $this->fields['items_id'] . "</td>";

        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Creation date') . "</td>";
        echo "<td>";
        echo Html::convDateTime($this->fields['create_time']);
        echo "</td><td>" . __('Expected send date') . "</td>";
        echo "<td>" . Html::convDateTime($this->fields['send_time']) . "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Send date') . "</td>";
        echo "<td>" . Html::convDateTime($this->fields['sent_time']) . "</td>";
        echo "<td>" . __('Number of tries of sent') . "</td>";
        echo "<td>" . $this->fields['sent_try'] . "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Subject') . "</td>";
        echo "<td>" . $this->fields['completName'] . "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Server name') . "</td>";
        echo "<td>" . $this->fields['serverName'] . "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Entity name') . "</td>";
        echo "<td>" . $this->fields['entName'] . "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Rocket Hook Url') . "</td>";
        echo "<td>" . $this->fields['hookurl'] . "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1 top' >";
        echo "<td colspan='2' class='queuechat_preview'>" . self::cleanHtml($this->fields['ticketTitle']) . "</td>";
        echo "<td colspan='2'>" . nl2br($this->fields['body_text'], false) . "</td>";
        echo "</tr>";

        $this->showFormButtons($options);

        return true;
    }

    /**
     * @since 0.85
     *
     * @param $string
     **/
    public static function cleanHtml($string)
    {

        $begin_strip     = -1;
        $end_strip       = -1;
        $begin_match     = "/<body>/";
        $end_match       = "/<\/body>/";
        $content         = explode("\n", $string);
        $newstring       = '';
        foreach ($content as $ID => $val) {
            // Get last tag for end
            if ($begin_strip >= 0) {
                if (preg_match($end_match, $val)) {
                    $end_strip = $ID;
                    continue;
                }
            }
            if (($begin_strip >= 0) && ($end_strip < 0)) {
                $newstring .= $val;
            }
            // Get first tag for begin
            if ($begin_strip < 0) {
                if (preg_match($begin_match, $val)) {
                    $begin_strip = $ID;
                }
            }
        }
        return nl2br($newstring, false);
    }


    public static function getIcon()
    {
        return "far fa-list-alt";
    }
}
