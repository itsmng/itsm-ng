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

namespace Glpi;

use Ajax;
use CommonDBTM;
use CronTask;
use Document;
use Html;
use Session;
use Toolbox;
use Infocom;
use DBConnection;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Event Class
**/
class Event extends CommonDBTM
{
    public static $rightname = 'logs';



    public static function getTypeName($nb = 0)
    {
        return _n('Log', 'Logs', $nb);
    }


    public function prepareInputForAdd($input)
    {
        global $CFG_GLPI;

        if (isset($input['level']) && ($input['level'] <= $CFG_GLPI["event_loglevel"])) {
            return $input;
        }
        return false;
    }

    public function post_addItem()
    {
        //only log in file, important events (connections and critical events; TODO : we need to add a general option to filter this in 9.1)
        if (isset($this->fields['level']) && $this->fields['level'] <= 3) {
            $message_type = "";
            if (isset($this->fields['type']) && $this->fields['type'] != 'system') {
                $message_type = "[" . $this->fields['type'] . " " . $this->fields['id'] . "] ";
            }

            $full_message = "[" . $this->fields['service'] . "] " .
                            $message_type .
                            $this->fields['level'] . ": " .
                            Toolbox::stripslashes_deep($this->fields['message']) . "\n";

            Toolbox::logInFile("event", $full_message);
        }
    }


    /**
     * Log an event.
     *
     * Log the event $event on the glpi_event table with all the others args, if
     * $level is above or equal to setting from configuration.
     *
     * @param $items_id
     * @param $type
     * @param $level
     * @param $service
     * @param $event
    **/
    public static function log($items_id, $type, $level, $service, $event)
    {
        global $DB;

        $input = ['items_id' => intval($items_id),
                       'type'     => $DB->escape($type),
                       'date'     => $_SESSION["glpi_currenttime"],
                       'service'  => $DB->escape($service),
                       'level'    => intval($level),
                       'message'  => $DB->escape($event)];
        $tmp = new self();
        return $tmp->add($input);
    }


    /**
     * Clean old event - Call by cron
     *
     * @param $day integer
     *
     * @return integer number of events deleted
    **/
    public static function cleanOld($day)
    {
        global $DB;

        $secs = $day * DAY_TIMESTAMP;

        $DB->delete(
            'glpi_events',
            [
              new \QueryExpression("UNIX_TIMESTAMP(date) < UNIX_TIMESTAMP()-$secs")
            ]
        );
        return $DB->affectedRows();
    }


    /**
     * Return arrays for function showEvent et lastEvent
    **/
    public static function logArray()
    {

        static $logItemtype = [];
        static $logService  = [];

        if (count($logItemtype)) {
            return [$logItemtype, $logService];
        }

        $logItemtype = ['system'      => __('System'),
                             'devices'     => _n('Component', 'Components', Session::getPluralNumber()),
                             'planning'    => __('Planning'),
                             'reservation' => _n('Reservation', 'Reservations', Session::getPluralNumber()),
                             'dropdown'    => _n('Dropdown', 'Dropdowns', Session::getPluralNumber()),
                             'rules'       => _n('Rule', 'Rules', Session::getPluralNumber())];

        $logService = ['inventory'    => __('Assets'),
                            'tracking'     => _n('Ticket', 'Tickets', Session::getPluralNumber()),
                            'maintain'     => __('Assistance'),
                            'planning'     => __('Planning'),
                            'tools'        => __('Tools'),
                            'financial'    => __('Management'),
                            'login'        => _n('Connection', 'Connections', 1),
                            'setup'        => __('Setup'),
                            'security'     => __('Security'),
                            'reservation'  => _n('Reservation', 'Reservations', Session::getPluralNumber()),
                            'cron'         => CronTask::getTypeName(Session::getPluralNumber()),
                            'document'     => Document::getTypeName(Session::getPluralNumber()),
                            'notification' => _n('Notification', 'Notifications', Session::getPluralNumber()),
                            'plugin'       => _n('Plugin', 'Plugins', Session::getPluralNumber())];

        return [$logItemtype, $logService];
    }


    /**
     * @param $type
     * @param $items_id
    **/
    public static function displayItemLogID($type, $items_id)
    {
        global $CFG_GLPI;

        if ($items_id <= 0) {
            echo "&nbsp;";//$item;
        } else {
            switch ($type) {
                case "rules":
                    echo "<a href=\"" . $CFG_GLPI["root_doc"] . "/front/rule.generic.form.php?id=" .
                          $items_id . "\">" . $items_id . "</a>";
                    break;

                case "infocom":
                    $rand = mt_rand();
                    echo " <a href='#' onClick=\"" . Html::jsGetElementbyID('infocom' . $rand) . ".
                       dialog('open');\">$items_id</a>";
                    Ajax::createIframeModalWindow(
                        'infocom' . $rand,
                        Infocom::getFormURLWithID($items_id),
                        ['height' => 600]
                    );
                    break;

                case "devices":
                    echo $items_id;
                    break;

                case "reservationitem":
                    echo "<a href=\"" . $CFG_GLPI["root_doc"] . "/front/reservation.php?reservationitems_id=" .
                          $items_id . "\">" . $items_id . "</a>";
                    break;

                default:
                    $type = getSingular($type);
                    $url  = '';
                    if ($item = getItemForItemtype($type)) {
                        $url  =  $item->getFormURLWithID($items_id);
                    }
                    if (!empty($url)) {
                        echo "<a href=\"" . $url . "\">" . $items_id . "</a>";
                    } else {
                        echo $items_id;
                    }
                    break;
            }
        }
    }


    /**
     * Print a nice tab for last event from inventory section
     *
     * Print a great tab to present lasts events occured on glpi
     *
     * @param $user   string  name user to search on message (default '')
     **/
    public static function showForUser($user = "")
    {
        global $DB, $CFG_GLPI;

        $iterator = $DB->request([
            'FROM' => self::getTable(), 'WHERE' => ['message' => ['LIKE', ($user !== '' ? $user . ' ' : '') . '%']],
            'ORDER' => ['date DESC', 'id DESC'], 'LIMIT' => max(1, (int) ($_SESSION['glpilist_limit'] ?? 15)),
        ]);
        echo "<a class='btn btn-link p-0 mb-3' href='" . $CFG_GLPI['root_doc'] . "/front/event.php'>"
            . sprintf(__('Last %d events'), $_SESSION['glpilist_limit']) . '</a>';
        $id = 'userEvents_' . md5($user);
        $fields = self::getListFields();
        unset($fields['level']);
        renderTwigTemplate('events.twig', [
            'id' => $id, 'state_key' => $id, 'fields' => $fields,
            'infocom_label' => Infocom::getTypeName(1),
            'values' => self::getListRows($iterator), 'pageSize' => max(1, (int) ($_SESSION['glpilist_limit'] ?? 15)),
        ]);
    }


    /**
     * Print a nice tab for last event
     *
     * Print a great tab to present lasts events occured on glpi
     *
     * @param string  $target  where to go when complete
     * @param string  $order   order by clause occurences (eg: ) (default 'DESC')
     * @param string  $sort    order by clause occurences (eg: date) (defaut 'date')
     * @param integer $start   (default 0)
    **/
    public static function showList($target, $order = 'DESC', $sort = 'date', $start = 0)
    {
        global $CFG_GLPI;

        Session::checkRight(static::$rightname, READ);
        renderTwigTemplate('events.twig', [
            'id' => 'eventList', 'state_key' => 'eventList', 'fields' => self::getListFields(),
            'infocom_label' => Infocom::getTypeName(1),
            'url' => $CFG_GLPI['root_doc'] . '/ajax/v2/event.php?' . http_build_query(['sort' => $sort, 'order' => $order]),
            'pageSize' => max(1, (int) ($_SESSION['glpilist_limit'] ?? 15)),
        ]);
    }

    private static function getListFields()
    {
        return ['type' => __('Source'), 'items_id' => __('ID'), 'date' => _n('Date', 'Dates', 1),
            'service' => __('Service'), 'level' => __('Level'), 'message' => __('Message')];
    }

    /** Preserve bounded database loading for the complete event history. */
    public static function getPaginatedEvents(array $options = [])
    {
        Session::checkRight(static::$rightname, READ);
        $DBread = DBConnection::getReadConnection();
        $sort = $options['sort'] ?? 'date';
        if (!is_string($sort) || !isset(self::getListFields()[$sort])) {
            $sort = 'date';
        }
        $order = is_string($options['order'] ?? null) && strtoupper($options['order']) === 'ASC' ? 'ASC' : 'DESC';
        $limit = max(1, min(10000, (int) ($options['limit'] ?? $_SESSION['glpilist_limit'] ?? 15)));
        $offset = max(0, (int) ($options['offset'] ?? 0));
        $total = $DBread->request(['COUNT' => 'total', 'FROM' => self::getTable()])->next()['total'];
        $iterator = $DBread->request([
            'FROM' => self::getTable(), 'ORDER' => [$sort . ' ' . $order, 'id ' . $order],
            'START' => $offset, 'LIMIT' => $limit,
        ]);
        return ['total' => (int) $total, 'rows' => array_values(self::getListRows($iterator))];
    }

    private static function getListRows($iterator)
    {
        [$types, $services] = self::logArray();
        $values = [];
        foreach ($iterator as $data) {
            $type = $types[$data['type']] ?? '';
            if ($type === '' && ($item = getItemForItemtype(getSingular($data['type'])))) {
                $type = $item->getTypeName(1);
            }
            if ($data['type'] === 'infocom' && $data['items_id'] > 0) {
                $url = htmlspecialchars(Infocom::getFormURLWithID($data['items_id']) . '&_in_modal=1', ENT_QUOTES, 'UTF-8', false);
                $itemlink = "<a data-event-infocom href='$url'>" . (int) $data['items_id'] . '</a>';
            } else {
                ob_start();
                self::displayItemLogID($data['type'], $data['items_id']);
                $itemlink = ob_get_clean();
            }
            $values[$data['id']] = [
                'type' => $type, 'items_id' => $itemlink, 'date' => Html::convDateTime($data['date']),
                'service' => $services[$data['service']] ?? htmlspecialchars($data['service'], ENT_QUOTES, 'UTF-8', false),
                'level' => (int) $data['level'],
                'message' => htmlspecialchars($data['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false),
            ];
        }
        return $values;
    }


    public static function getIcon()
    {
        return "fas fa-scroll";
    }
}
