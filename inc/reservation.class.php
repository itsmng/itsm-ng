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

use itsmng\Timezone;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Reservation Class
**/
class Reservation extends CommonDBChild
{
    // From CommonDBChild
    public static $itemtype          = 'ReservationItem';
    public static $items_id          = 'reservationitems_id';

    public static $rightname                = 'reservation';
    public static $checkParentRights = self::HAVE_VIEW_RIGHT_ON_ITEM;


    /**
     * @param $nb  integer  for singular or plural
    **/
    public static function getTypeName($nb = 0)
    {
        return _n('Reservation', 'Reservations', $nb);
    }

    /**
 * Get the appropriate reservation form URL based on current interface
 *
 * @return string The URL to use for reservation form
 */
    private static function getReservationFormURL($id = null)
    {
        global $CFG_GLPI;

        $is_helpdesk_interface = strpos((string) $_SERVER['REQUEST_URI'], '/plugins/formcreator/front/') !== false ||
                                (isset($_SESSION['glpiactiveprofile']['interface']) &&
                                $_SESSION['glpiactiveprofile']['interface'] == 'helpdesk');

        if ($is_helpdesk_interface) {
            if ($id !== null) {
                $link = $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/reservation.form.php';
            } else {
                $link = $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/reservation.form.php';
            }
        } else {
            $link = self::getFormURL();
        }

        if ($id !== null) {
            $link .= (strpos($link, '?') ? '&' : '?') . 'id=' . $id;
        }

        return $link;
    }

    /**
     * @see CommonGLPI::getTabNameForItem()
    **/
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (
            !$withtemplate
            && Session::haveRight("reservation", READ)
        ) {
            return self::getTypeName(Session::getPluralNumber());
        }
        return '';
    }


    /**
     * @param $item         CommonGLPI object
     * @param $tabnum       (default1)
     * @param $withtemplate (default0)
    **/
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == 'User') {
            self::showForUser($_GET["id"]);
        } else {
            self::showForItem($item);
        }
        return true;
    }


    public function pre_deleteItem()
    {
        global $CFG_GLPI;

        if (
            isset($this->fields["users_id"])
            && (($this->fields["users_id"] === Session::getLoginUserID())
                || Session::haveRight("reservation", DELETE))
        ) {
            // Processing Email
            if (!isset($this->input['_disablenotif']) && $CFG_GLPI["use_notifications"]) {
                // Only notify for non-completed reservations
                if (strtotime((string) $this->fields['end']) > time()) {
                    NotificationEvent::raiseEvent("delete", $this);
                }
            }
        }
        return true;
    }


    /**
     * @see CommonDBChild::prepareInputForUpdate()
    **/
    public function prepareInputForUpdate($input)
    {

        $item = 0;
        if (isset($input['_item'])) {
            $item = $input['_item'];
        }

        // Save fields
        $oldfields             = $this->fields;
        // Needed for test already planned
        if (isset($input["begin"])) {
            $this->fields["begin"] = $input["begin"];
        }
        if (isset($input["end"])) {
            $this->fields["end"] = $input["end"];
        }

        if (!$this->test_valid_date()) {
            $this->displayError("date", $item);
            return false;
        }

        if ($this->is_reserved()) {
            $this->displayError("is_res", $item);
            return false;
        }

        // Restore fields
        $this->fields = $oldfields;

        return parent::prepareInputForUpdate($input);
    }


    /**
     * @see CommonDBTM::post_updateItem()
    **/
    public function post_updateItem($history = 1)
    {
        global $CFG_GLPI;

        if (
            count($this->updates)
            && $CFG_GLPI["use_notifications"]
            && !isset($this->input['_disablenotif'])
        ) {
            NotificationEvent::raiseEvent("update", $this);
            //$mail = new MailingResa($this,"update");
            //$mail->send();
        }

        parent::post_updateItem($history);
    }


    /**
     * @see CommonDBChild::prepareInputForAdd()
    **/
    public function prepareInputForAdd($input)
    {

        // Error on previous added reservation on several add
        if (isset($input['_ok']) && !$input['_ok']) {
            return false;
        }

        // set new date.
        $this->fields["reservationitems_id"] = $input["reservationitems_id"];
        $this->fields["begin"]               = $input["begin"];
        $this->fields["end"]                 = $input["end"];

        if (!$this->test_valid_date()) {
            $this->displayError("date", $input["reservationitems_id"]);
            return false;
        }

        if ($this->is_reserved()) {
            $this->displayError("is_res", $input["reservationitems_id"]);
            return false;
        }

        return parent::prepareInputForAdd($input);
    }


    public function post_addItem()
    {
        global $CFG_GLPI;

        if (!isset($this->input['_disablenotif']) && $CFG_GLPI["use_notifications"]) {
            NotificationEvent::raiseEvent("new", $this);
        }

        parent::post_addItem();

        if (isset($this->fields['reservationitems_id']) && !isset($this->input['_ajax_reservation'])) {
            if (strpos((string) $_SERVER['REQUEST_URI'], '/plugins/formcreator/front/') !== false ||
                (isset($_SESSION['glpiactiveprofile']['interface']) &&
                $_SESSION['glpiactiveprofile']['interface'] == 'helpdesk')) {

                $redirect_url = $CFG_GLPI['root_doc'] . '/plugins/formcreator/front/reservation.php?reservationitems_id=' . $this->fields['reservationitems_id'] . '&reservation_added=1';
            } else {
                $redirect_url = $CFG_GLPI['root_doc'] . '/front/reservation.php?reservationitems_id=' . $this->fields['reservationitems_id'] . '&reservation_added=1';
            }

            Html::redirect($redirect_url);
        }
    }


    // SPECIFIC FUNCTIONS

    /**
     * @param $reservationitems_id
    **/
    public function getUniqueGroupFor($reservationitems_id)
    {
        global $DB;

        do {
            $rand = mt_rand(1, mt_getrandmax());

            $result = $DB->request([
               'COUNT'  => 'cpt',
               'FROM'   => 'glpi_reservations',
               'WHERE'  => [
                  'reservationitems_id'   => $reservationitems_id,
                  'group'                 => $rand
               ]
            ])->next();
            $count = (int)$result['cpt'];
        } while ($count > 0);

        return $rand;
    }


    /**
     * Is the item already reserved ?
     *
     *@return boolean
    **/
    public function is_reserved()
    {
        global $DB;

        if (
            !isset($this->fields["reservationitems_id"])
            || empty($this->fields["reservationitems_id"])
        ) {
            return true;
        }

        // When modify a reservation do not itself take into account
        $where = [];
        if (isset($this->fields["id"])) {
            $where['id'] = ['<>', $this->fields['id']];
        }

        $result = $DB->request([
           'COUNT'  => 'cpt',
           'FROM'   => $this->getTable(),
           'WHERE'  => $where + [
              'reservationitems_id'   => $this->fields['reservationitems_id'],
              'end'                   => ['>', $this->fields['begin']],
              'begin'                 => ['<', $this->fields['end']]
           ]
        ])->next();
        return $result['cpt'] > 0;
    }


    /**
     * Current dates are valid ? begin before end
     *
     *@return boolean
    **/
    public function test_valid_date()
    {

        return (!empty($this->fields["begin"])
                && !empty($this->fields["end"])
                && (strtotime((string) $this->fields["begin"]) < strtotime((string) $this->fields["end"])));
    }


    /**
     * display error message
     *
     * @param $type   error type : date / is_res / other
     * @param $ID     ID of the item
     *
     * @return void
    **/
    public function displayError($type, $ID)
    {

        echo "<br><div class='center'>";
        switch ($type) {
            case "date":
                echo __('Error in entering dates. The starting date is later than the ending date');
                break;

            case "is_res":
                echo __('The required item is already reserved for this timeframe');
                break;

            default:
                echo __("Unknown error");
        }

        echo "<br><a href='reservation.php?reservationitems_id=$ID'>" . __('Back to planning') . "</a>";
        echo "</div>";
    }


    /**
     * @since 0.84
    **/
    public static function canCreate()
    {
        return (Session::haveRight(self::$rightname, ReservationItem::RESERVEANITEM));
    }


    /**
     * @since 0.84
    **/
    public static function canUpdate()
    {
        return (Session::haveRight(self::$rightname, ReservationItem::RESERVEANITEM));
    }


    /**
     * @since 0.84
    **/
    public static function canDelete()
    {
        return (Session::haveRight(self::$rightname, ReservationItem::RESERVEANITEM));
    }


    /**
     * Overload canChildItem to make specific checks
     * @since 0.84
    **/
    public function canChildItem($methodItem, $methodNotItem)
    {

        // Original user always have right
        if ($this->fields['users_id'] === Session::getLoginUserID()) {
            return true;
        }

        if (!parent::canChildItem($methodItem, $methodNotItem)) {
            return false;
        }

        $ri = $this->getItem();
        if ($ri === false) {
            return false;
        }

        $item = $ri->getItem();
        if ($item === false) {
            return false;
        }

        return Session::haveAccessToEntity($item->getEntityID());
    }


    public function post_purgeItem()
    {
        global $DB;

        if (isset($this->input['_delete_group']) && $this->input['_delete_group']) {
            $iterator = $DB->request([
               'FROM'   => 'glpi_reservations',
               'WHERE'  => [
                  'reservationitems_id'   => $this->fields['reservationitems_id'],
                  'group'                 => $this->fields['group']
               ]
            ]);
            $rr = clone $this;
            while ($data = $iterator->next()) {
                $rr->delete(['id' => $data['id']]);
            }
        }
    }


    /**
     * Show reservation calendar
     *
     * @param $ID   ID of the reservation item (if empty display all) (default '')
    **/
    public static function showCalendar($ID = "")
    {
        global $CFG_GLPI;

        if (!Session::haveRight("reservation", ReservationItem::RESERVEANITEM)) {
            return false;
        }

        $ID = (int)$ID;
        $calendar_title = __('All reservable devices');
        $item_label = '';
        $can_reserve = false;
        if (!empty($ID)) {
            $m = new ReservationItem();
            if (!$m->getFromDB($ID)) {
                Html::displayNotFoundError();
                return false;
            }

            if ((!isset($m->fields['is_active'])) || !$m->fields['is_active']) {
                echo "<div class='center'>";
                echo "<table class='tab_cadre_fixe' aria-label='Device unavailable'>";
                echo "<tr class='tab_bg_2'>";
                echo "<td class='center b'>" . __('Device temporarily unavailable') . "</td></tr>";
                echo "<tr class='tab_bg_1'><td class='center b'>";
                Html::displayBackLink();
                echo "</td></tr>";
                echo "</table>";
                echo "</div>";
                return false;
            }
            $type = $m->fields["itemtype"];
            $name = NOT_AVAILABLE;
            if ($item = getItemForItemtype($m->fields["itemtype"])) {
                $type = $item->getTypeName();

                if ($item->getFromDB($m->fields["items_id"])) {
                    $name = $item->getName();
                }
            }

            $item_label = sprintf(__('%1$s - %2$s'), $type, $name);
            $calendar_title = $item_label;
            $can_reserve = true;
        }

        $initial_date = date('Y-m-d');
        if (isset($_GET['mois_courant'], $_GET['annee_courante'])) {
            $initial_date = sprintf(
                '%04d-%02d-01',
                (int)$_GET['annee_courante'],
                (int)$_GET['mois_courant']
            );
        }

        $options = [
           'reservationitems_id' => $ID,
           'can_reserve'         => $can_reserve,
           'initial_date'        => $initial_date,
           'title'               => $calendar_title,
           'item_label'          => $item_label,
           'all_url'             => $CFG_GLPI['root_doc'] . '/front/reservation.php?reservationitems_id=',
           'ajax_url'            => $CFG_GLPI['root_doc'] . '/ajax/reservation.php',
           'form_url'            => self::getReservationFormURL(),
           'planning_begin'      => $CFG_GLPI['planning_begin'] ?? '08:00:00',
           'planning_end'        => $CFG_GLPI['planning_end'] ?? '20:00:00',
        ];

        echo Html::css('public/lib/fullcalendar.css', ['media' => '']);
        echo Html::fullCalendarScripts();
        echo Html::script('js/reservation.js');

        echo "<div class='reservation-calendar-shell'>";
        echo "<div class='reservation-calendar-header'>";
        echo "<div class='reservation-calendar-title'>";
        echo "<div>";
        echo "<h2>" . htmlspecialchars($calendar_title) . "</h2>";
        if (!empty($item_label)) {
            echo "<a href='" . $CFG_GLPI['root_doc'] . "/front/reservation.php?reservationitems_id='>";
            echo __('Show all') . "</a>";
        }
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "<div id='reservation-calendar'></div>";
        echo "</div>";

        echo Html::scriptBlock(
            '$(function() { ITSMReservationCalendar.display(' . json_encode($options) . '); });'
        );
    }


    /**
     * Display for reservation
     *
     * @param $ID              ID of the reservation (empty for create new)
     * @param $options   array of possibles options:
     *     - item  reservation items ID for creation process
     *     - date date for creation process
    **/
    public function showForm($ID, $options = [])
    {
        global $CFG_GLPI;

        if (!Session::haveRight("reservation", ReservationItem::RESERVEANITEM)) {
            return false;
        }

        $resa = new self();

        if (!empty($ID)) {
            if (!$resa->getFromDB($ID)) {
                return false;
            }

            if (!$resa->can($ID, UPDATE)) {
                return false;
            }
            // Set item if not set
            if (
                (!isset($options['item']) || (count($options['item']) == 0))
                && ($itemid = $resa->getField('reservationitems_id'))
            ) {
                $options['item'][$itemid] = $itemid;
            }
        } else {
            $resa->getEmpty();
            $resa->fields["begin"] = $options['begin'];
            if (!isset($options['end'])) {
                $resa->fields["end"] = date(
                    "Y-m-d H:00:00",
                    strtotime((string) $resa->fields["begin"]) + HOUR_TIMESTAMP
                );
            } else {
                $resa->fields["end"] = $options['end'];
            }
        }

        // No item : problem
        if (!isset($options['item']) || (count($options['item']) == 0)) {
            return false;
        }
        ob_start();
        // Add Hardware name
        $r = new ReservationItem();

        foreach ($options['item'] as $itemID) {
            $r->getFromDB($itemID);
            $type = $r->fields["itemtype"];
            $name = NOT_AVAILABLE;
            $item = null;

            if ($item = getItemForItemtype($r->fields["itemtype"])) {
                $type = $item->getTypeName();

                if ($item->getFromDB($r->fields["items_id"])) {
                    $name = $item->getName();
                } else {
                    $item = null;
                }
            }

            echo "<span class='b'>" . sprintf(__('%1$s - %2$s'), $type, $name) . "</span><br>";
            echo "<input type='hidden' name='items[$itemID]' value='$itemID'>";
        }
        $items = ob_get_clean();

        $uid = (empty($ID) ? Session::getLoginUserID() : $resa->fields['users_id']);

        $default_delay = floor((strtotime((string) $resa->fields["end"]) - strtotime((string) $resa->fields["begin"]))
                               / $CFG_GLPI['time_step'] / MINUTE_TIMESTAMP)
                         * $CFG_GLPI['time_step'] * MINUTE_TIMESTAMP;

        $form = [
           'action'      => self::getFormURL(),
           'buttons'     => [
              empty($ID) ? [
                 'name'  => 'add',
                 'value'  => __('Add'),
                 'type'  => 'submit',
                 'class' => 'btn btn-secondary'
              ] : (Session::haveRight('reservation', UPDATE) ? [
                 'name'  => 'update',
                 'value'  => __('Save'),
                 'type'  => 'submit',
                 'class' => 'btn btn-secondary'
              ] : []),
              Session::haveRight('reservation', PURGE) && !empty($ID) ? [
                 'name'  => 'purge',
                 'value'  => __('Delete permanently'),
                 'type'  => 'submit',
                 'class' => 'btn btn-secondary'
              ] : [],
           ],
           'content'     => [
              __('Reserve an item') => [
                  'visible' => true,
                  'inputs'   => [
                      !empty($ID) ? [
                          'type'  => 'hidden',
                          'name'  => 'id',
                          'value' => $ID
                      ] : [],
                      _n('Item', 'Items', 1) => [
                          'content' => $items,
                          'col_lg'  => 12,
                          'col_md'  => 12,
                      ],
                      !Session::haveRight("reservation", UPDATE) ? [
                          'type'  => 'hidden',
                          'name'  => 'users_id',
                          'value' => $uid
                      ] : [],
                      !Session::haveRight("reservation", UPDATE) ? [
                          'content' => Dropdown::getDropdownName(
                              User::getTable(),
                              $uid
                          ),
                          'col_lg'  => 12,
                          'col_md'  => 12,
                      ] : [],
                      __('By') => Session::haveRight("reservation", UPDATE) ? [
                          'type'  => 'select',
                          'name'  => 'users_id',
                          'values' => getOptionsForUsers('all'),
                          'value'  => $uid,
                      ] : [],
                      __('Start date') => [
                          'type'  => 'datetime-local',
                          'name'  => 'resa[begin]',
                          'value' => $resa->fields["begin"],
                          'min'   => date('Y-m-d H:00:00'),
                      ],
                      __('End date') => [
                          'type'  => 'datetime-local',
                          'id'    => 'entTimeStamp',
                          'name'  =>  'resa[end]',
                          'value' => $resa->fields["end"],
                          'min'   => $this->fields["begin"] ?? date('Y-m-d H:00:00'),
                      ],
                      __('Repetition') => !empty($ID) ? [] : [
                          'type'  => 'select',
                          'name'  => 'periodicity',
                          'values' => [''      => _x('periodicity', 'None'),
                                       'day'   => _x('periodicity', 'Daily'),
                                       'week'  => _x('periodicity', 'Weekly'),
                                       'month' => _x('periodicity', 'Monthly')],
                          'value' => $resa->fields["periodicity"] ?? '',
                          'hooks' => [
                              'change' => <<<JS
                               const value = this.value;
                               // remove the T from date
                               $.ajax({
                                   url: '{$CFG_GLPI['root_doc']}/ajax/resaperiod.php',
                                   type: 'POST',
                                   data: {
                                      type: value,
                                      end:  $('#entTimeStamp').val().replace('T', ' ')
                                   },
                                   success: function(data) {
                                      $('#resaperiodcontent').html(data);
                                   }
                                });
                            JS,
                          ],
                      ],
                      '' => !empty($ID) ? [] : [
                          'content' => "<div id='resaperiodcontent'></div>",
                          'col_lg'  => 12,
                          'col_md'  => 12,
                      ],
                      __('Comments') => [
                          'type'  => 'textarea',
                          'name'  => 'comment',
                          'value' => $resa->fields["comment"],
                          'col_lg'  => 12,
                          'col_md'  => 12,
                      ],
                  ],
              ],
           ]
        ];
        renderTwigForm($form);
    }


    /**
     * compute periodicities for reservation
     *
     * @since 0.84
     *
     * @param $begin             begin of the initial reservation
     * @param $end               begin of the initial reservation
     * @param $options   array   periodicity parameters : must contain : type (day/week/month), end
    **/
    public static function computePeriodicities($begin, $end, $options = [])
    {
        $toadd = [];

        if (isset($options['type']) && isset($options['end'])) {
            $begin_time = strtotime((string) $begin);
            $end_time   = strtotime((string) $end);
            $repeat_end = strtotime($options['end'] . ' 23:59:59');

            switch ($options['type']) {
                case 'day':
                    $begin_time = strtotime("+1 day", $begin_time);
                    $end_time   = strtotime("+1 day", $end_time);
                    while ($begin_time < $repeat_end) {
                        $toadd[date('Y-m-d H:i:s', $begin_time)] = date('Y-m-d H:i:s', $end_time);
                        $begin_time = strtotime("+1 day", $begin_time);
                        $end_time   = strtotime("+1 day", $end_time);
                    }
                    break;

                case 'week':
                    $dates = [];

                    // No days set add 1 week
                    if (!isset($options['days'])) {
                        $dates = [['begin' => strtotime('+1 week', $begin_time),
                                             'end'   => strtotime('+1 week', $end_time)]];
                    } else {
                        if (is_array($options['days'])) {
                            $begin_hour = $begin_time - strtotime(date('Y-m-d', $begin_time));
                            $end_hour   = $end_time - strtotime(date('Y-m-d', $end_time));
                            foreach ($options['days'] as $day => $val) {
                                $dates[] = ['begin' => strtotime("next $day", $begin_time) + $begin_hour,
                                                 'end'   => strtotime("next $day", $end_time) + $end_hour];
                            }
                        }
                    }

                    foreach ($dates as $key => $val) {
                        $begin_time = $val['begin'];
                        $end_time   = $val['end'];

                        while ($begin_time < $repeat_end) {
                            $toadd[date('Y-m-d H:i:s', $begin_time)] = date('Y-m-d H:i:s', $end_time);
                            $begin_time = strtotime('+1 week', $begin_time);
                            $end_time   = strtotime('+1 week', $end_time);
                        }
                    }
                    break;

                case 'month':
                    if (isset($options['subtype'])) {
                        switch ($options['subtype']) {
                            case 'date':
                                $i = 1;
                                $calc_begin_time = strtotime("+$i month", $begin_time);
                                $calc_end_time   = strtotime("+$i month", $end_time);
                                while ($calc_begin_time < $repeat_end) {
                                    $toadd[date('Y-m-d H:i:s', $calc_begin_time)] = date(
                                        'Y-m-d H:i:s',
                                        $calc_end_time
                                    );
                                    $i++;
                                    $calc_begin_time = strtotime("+$i month", $begin_time);
                                    $calc_end_time   = strtotime("+$i month", $end_time);
                                }
                                break;

                            case 'day':
                                $dayofweek = date('l', $begin_time);

                                $i               = 1;
                                $calc_begin_time = strtotime("+$i month", $begin_time);
                                $calc_end_time   = strtotime("+$i month", $end_time);
                                $begin_hour      = $begin_time - strtotime(date('Y-m-d', $begin_time));
                                $end_hour        = $end_time - strtotime(date('Y-m-d', $end_time));

                                $calc_begin_time = strtotime("next $dayofweek", $calc_begin_time)
                                                   + $begin_hour;
                                $calc_end_time   = strtotime("next $dayofweek", $calc_end_time) + $end_hour;

                                while ($calc_begin_time < $repeat_end) {
                                    $toadd[date('Y-m-d H:i:s', $calc_begin_time)] = date(
                                        'Y-m-d H:i:s',
                                        $calc_end_time
                                    );
                                    $i++;
                                    $calc_begin_time = strtotime("+$i month", $begin_time);
                                    $calc_end_time   = strtotime("+$i month", $end_time);
                                    $calc_begin_time = strtotime("next $dayofweek", $calc_begin_time)
                                                       + $begin_hour;
                                    $calc_end_time   = strtotime("next $dayofweek", $calc_end_time)
                                                       + $end_hour;
                                }
                                break;
                        }
                    }

                    break;
            }
        }

        return $toadd;
    }


    /**
     * Display for reservation
     *
     * @param $ID     ID a the reservation item (empty to show all)
     * @param $date   date to display
    **/
    public static function displayReservationDay($ID, $date)
    {
        global $DB;

        if (!empty($ID)) {
            self::displayReservationsForAnItem($ID, $date);
        } else {
            $debut = $date . " 00:00:00";
            $fin   = $date . " 23:59:59";

            $iterator = $DB->request([
               'SELECT'          => 'glpi_reservationitems.id',
               'DISTINCT'        => true,
               'FROM'            => 'glpi_reservationitems',
               'INNER JOIN'      => [
                  'glpi_reservations'  => [
                     'ON' => [
                        'glpi_reservationitems' => 'id',
                        'glpi_reservations'     => 'reservationitems_id'
                     ]
                  ]
               ],
               'WHERE'           => [
                  'is_active' => 1,
                  'end'       => ['>', $debut],
                  'begin'     => ['<', $fin]
               ],
               'ORDERBY'         => 'begin'
            ]);

            if (count($iterator)) {
                $m = new ReservationItem();
                while ($data = $iterator->next()) {
                    $m->getFromDB($data['id']);

                    if (!($item = getItemForItemtype($m->fields["itemtype"]))) {
                        continue;
                    }

                    if (
                        $item->getFromDB($m->fields["items_id"])
                        && Session::haveAccessToEntity($item->fields["entities_id"])
                    ) {
                        $typename = $item->getTypeName();

                        if ($m->fields["itemtype"] == 'Peripheral') {
                            if (
                                isset($item->fields["peripheraltypes_id"])
                                && ($item->fields["peripheraltypes_id"] != 0)
                            ) {
                                $typename = Dropdown::getDropdownName(
                                    "glpi_peripheraltypes",
                                    $item->fields["peripheraltypes_id"]
                                );
                            }
                        }

                        list($annee, $mois, $jour) = explode("-", (string) $date);
                        echo "<tr class='tab_bg_1'><td>";
                        echo "<a href='reservation.php?reservationitems_id=" . $data['id'] .
                              "&amp;mois_courant=$mois&amp;annee_courante=$annee'>" .
                              sprintf(__('%1$s - %2$s'), $typename, $item->getName()) . "</a></td></tr>\n";
                        echo "<tr><td>";
                        self::displayReservationsForAnItem($data['id'], $date);
                        echo "</td></tr>\n";
                    }
                }
            }
        }
    }


    /**
     * Display a reservation
     *
     * @param $ID     ID a the reservation item
     * @param $date   date to display
    **/
    public static function displayReservationsForAnItem($ID, $date)
    {
        global $DB;

        $users_id = Session::getLoginUserID();
        $resa     = new self();
        $user     = new User();
        list($year, $month, $day) = explode("-", (string) $date);
        $debut    = $date . " 00:00:00";
        $fin      = $date . " 23:59:59";

        $iterator = $DB->request([
           'FROM'   => 'glpi_reservations',
           'WHERE'  => [
              'end'                   => ['>', $debut],
              'begin'                 => ['<', $fin],
              'reservationitems_id'   => $ID
           ],
           'ORDER'  => 'begin'
        ]);

        if (count($iterator)) {
            echo "<table width='100%' aria-label='User Time Interval'>";
            while ($row = $iterator->next()) {
                echo "<tr>";
                $user->getFromDB($row["users_id"]);
                $display = "";

                if ($debut > $row['begin']) {
                    $heure_debut = "00:00";
                } else {
                    $heure_debut = get_hour_from_sql($row['begin']);
                }

                if ($fin < $row['end']) {
                    $heure_fin = "24:00";
                } else {
                    $heure_fin = get_hour_from_sql($row['end']);
                }

                if (
                    (strcmp($heure_debut, "00:00") == 0)
                      && (strcmp($heure_fin, "24:00") == 0)
                ) {
                    $display = _n('Day', 'Days', 1);
                } elseif (strcmp($heure_debut, "00:00") == 0) {
                    $display = sprintf(__('To %s'), $heure_fin);
                } elseif (strcmp($heure_fin, "24:00") == 0) {
                    $display = sprintf(__('From %s'), $heure_debut);
                } else {
                    $display = $heure_debut . "-" . $heure_fin;
                }

                $rand  = mt_rand();
                $modif = $modif_end = "";
                if ($resa->canEdit($row['id'])) {
                    $modif      = "<a id='content_" . $ID . $rand . "'
                                 href='" . self::getReservationFormURL($row['id']) . "'>";
                    $modif_end  = "</a>";
                    $modif_end .= Html::showToolTip(
                        $row["comment"],
                        ['applyto' => "content_" . $ID . $rand,
                                                           'display' => false]
                    );
                }

                echo "<td class='tab_resa center'>" . $modif . "<span>" . $display . "<br><span class='b'>" .
                formatUserName(
                    $user->fields["id"],
                    $user->fields["name"],
                    $user->fields["realname"],
                    $user->fields["firstname"]
                );
                echo "</span></span>";
                echo $modif_end;
                echo "</td></tr>\n";
            }
            echo "</table>\n";
        }
    }


    /**
     * Display reservations for an item
     *
     * @param $item            CommonDBTM object for which the reservation tab need to be displayed
     * @param $withtemplate    withtemplate param (default 0)
    **/
    public static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {
        global $DB, $CFG_GLPI;

        $resaID = 0;
        if (!Session::haveRight("reservation", READ)) {
            return false;
        }

        echo "<div class='firstbloc'>";
        ReservationItem::showActivationFormForItem($item);

        $ri = new ReservationItem();
        if ($ri->getFromDBbyItem($item->getType(), $item->getID())) {
            $now = $_SESSION["glpi_currenttime"];

            // Print reservation in progress
            $iterator = $DB->request([
               'FROM'   => 'glpi_reservations',
               'WHERE'  => [
                  'end'                   => ['>', $now],
                  'reservationitems_id'   => $ri->fields['id']
               ],
               'ORDER'  => 'begin'
            ]);

            echo "<table class='tab_cadre_fixehov' aria-label='Current and future reservations'><tr><th colspan='5'>";

            if (
                count($iterator) && $ri->fields["is_active"]
                && Session::haveRight('reservation', ReservationItem::RESERVEANITEM)
            ) {
                echo "<a href='" . $CFG_GLPI["root_doc"] . "/front/reservation.php?reservationitems_id=" .
                       $ri->fields['id'] . "'>" . __('Current and future reservations') . "</a>";
            } else {
                echo __('Current and future reservations');
            }
            echo "</th></tr>\n";

            if (!count($iterator)) {
                echo "<tr class='tab_bg_2'>";
                echo "<td class='center' colspan='5'>" . __('No reservation') . "</td></tr>\n";
            } else {
                echo "<tr><th>" . __('Start date') . "</th>";
                echo "<th>" . __('End date') . "</th>";
                echo "<th>" . __('By') . "</th>";
                echo "<th>" . __('Comments') . "</th><th>&nbsp;</th></tr>\n";

                while ($data = $iterator->next()) {
                    echo "<tr class='tab_bg_2'>";
                    echo "<td class='center'>" . Html::convDateTime($data["begin"]) . "</td>";
                    echo "<td class='center'>" . Html::convDateTime($data["end"]) . "</td>";
                    echo "<td class='center'>";
                    if (Session::haveRight('user', READ)) {
                        echo "<a href='" . User::getFormURLWithID($data["users_id"]) . "'>" . getUserName($data["users_id"]) . "</a>";
                    } else {
                        echo getUserName($data["users_id"]);
                    }
                    echo "</td>";
                    echo "<td class='center'>" . nl2br((string) $data["comment"]) . "</td>";
                    echo "<td class='center'>";
                    if (Session::haveRight('reservation', ReservationItem::RESERVEANITEM)) {
                        list($annee, $mois, $jour) = explode("-", (string) $data["begin"]);
                        echo "<a href='" . $CFG_GLPI["root_doc"] . "/front/reservation.php?reservationitems_id=" .
                              $ri->fields['id'] . "&amp;mois_courant=$mois&amp;annee_courante=$annee' title=\"" .
                              __s('See planning') . "\">";
                        echo "<i class='far fa-calendar-alt' aria-hidden='true'></i>";
                        echo "<span class='sr-only'>" . __('See planning') . "</span></a>";
                    } else {
                        echo "&nbsp;";
                    }
                    echo "</td></tr>\n";
                }
            }
            echo "</table></div>\n";

            // Print old reservations
            $iterator = $DB->request([
               'FROM'   => 'glpi_reservations',
               'WHERE'  => [
                  'end'                   => ['<=', $now],
                  'reservationitems_id'   => $ri->fields['id']
               ],
               'ORDER'  => 'begin DESC'
            ]);

            echo "<div class='spaced'><table class='tab_cadre_fixehov' aria-label='Past Reservations'><tr><th colspan='5'>";

            if (
                count($iterator) && $ri->fields["is_active"]
                && Session::haveRight('reservation', ReservationItem::RESERVEANITEM)
            ) {
                echo "<a href='" . $CFG_GLPI["root_doc"] . "/front/reservation.php?reservationitems_id=" .
                       $ri->fields['id'] . "' >" . __('Past reservations') . "</a>";
            } else {
                echo __('Past reservations');
            }
            echo "</th></tr>\n";

            if (!count($iterator)) {
                echo "<tr class='tab_bg_2'>";
                echo "<td class='center' colspan='5'>" . __('No reservation') . "</td></tr>\n";
            } else {
                echo "<tr><th>" . __('Start date') . "</th>";
                echo "<th>" . __('End date') . "</th>";
                echo "<th>" . __('By') . "</th>";
                echo "<th>" . __('Comments') . "</th><th>&nbsp;</th></tr>\n";

                while ($data = $iterator->next()) {
                    echo "<tr class='tab_bg_2'>";
                    echo "<td class='center'>" . Html::convDateTime($data["begin"]) . "</td>";
                    echo "<td class='center'>" . Html::convDateTime($data["end"]) . "</td>";
                    echo "<td class='center'>";
                    if (Session::haveRight('user', READ)) {
                        echo "<a href='" . User::getFormURLWithID($data["users_id"]) . "'>" . getUserName($data["users_id"]) . "</a>";
                    } else {
                        echo getUserName($data["users_id"]);
                    }
                    echo "</td>";
                    echo "<td class='center'>" . nl2br((string) $data["comment"]) . "</td>";
                    echo "<td class='center'>";
                    if (Session::haveRight('reservation', ReservationItem::RESERVEANITEM)) {
                        list($annee, $mois, $jour) = explode("-", (string) $data["begin"]);
                        echo "<a href='" . $CFG_GLPI["root_doc"] . "/front/reservation.php?reservationitems_id=" .
                              $ri->fields['id'] . "&amp;mois_courant=$mois&amp;annee_courante=$annee' title=\"" .
                              __s('See planning') . "\">";
                        echo "<i class='far fa-calendar-alt' aria-hidden='true'></i>";
                        echo "<span class='sr-only'>" . __('See planning') . "</span>";
                        echo "</a>";
                    } else {
                        echo "&nbsp;";
                    }
                    echo "</td></tr>\n";
                }
            }
            echo "</table>\n";
        }
        echo "</div>\n";
    }


    /**
     * Display reservations for a user
     *
     * @param $ID ID a the user
    **/
    public static function showForUser($ID)
    {
        global $DB, $CFG_GLPI;

        $resaID = 0;

        if (!Session::haveRight("reservation", READ)) {
            return false;
        }

        echo "<div class='firstbloc'>";
        $now = $_SESSION["glpi_currenttime"];

        // Print reservation in progress
        $iterator = $DB->request([
           'SELECT'    => [
              'begin',
              'end',
              'items_id',
              'glpi_reservationitems.entities_id',
              'users_id',
              'glpi_reservations.comment',
              'reservationitems_id',
              'completename'
           ],
           'FROM'      => 'glpi_reservations',
           'LEFT JOIN' => [
              'glpi_reservationitems' => [
                 'ON' => [
                    'glpi_reservationitems' => 'id',
                    'glpi_reservations'     => 'reservationitems_id'
                 ]
              ],
              'glpi_entities' => [
                 'ON' => [
                    'glpi_reservationitems' => 'entities_id',
                    'glpi_entities'         => 'id'
                 ]
              ]
           ],
           'WHERE'     => [
              'end'       => ['>', $now],
              'users_id'  => $ID
           ],
           'ORDERBY'   => 'begin'
        ]);

        $ri = new ReservationItem();
        echo "<table class='tab_cadre_fixehov' aria-label='Current and future reservations'>";
        echo "<tr><th colspan='6'>" . __('Current and future reservations') . "</th></tr>\n";

        if (count($iterator) == 0) {
            echo "<tr class='tab_bg_2'>";
            echo "<td class='center' colspan='6'>" . __('No reservation') . "</td></tr\n>";
        } else {
            echo "<tr><th>" . __('Start date') . "</th>";
            echo "<th>" . __('End date') . "</th>";
            echo "<th>" . _n('Item', 'Items', 1) . "</th>";
            echo "<th>" . Entity::getTypeName(1) . "</th>";
            echo "<th>" . __('By') . "</th>";
            echo "<th>" . __('Comments') . "</th><th>&nbsp;</th></tr>\n";

            while ($data = $iterator->next()) {
                echo "<tr class='tab_bg_2'>";
                echo "<td class='center'>" . Html::convDateTime($data["begin"]) . "</td>";
                echo "<td class='center'>" . Html::convDateTime($data["end"]) . "</td>";

                if ($ri->getFromDB($data["reservationitems_id"])) {
                    $link = "&nbsp;";

                    if ($item = getItemForItemtype($ri->fields['itemtype'])) {
                        if ($item->getFromDB($ri->fields['items_id'])) {
                            $link = $item->getLink();
                        }
                    }
                    echo "<td class='center'>$link</td>";
                    echo "<td class='center'>" . $data['completename'] . "</td>";
                } else {
                    echo "<td class='center'>&nbsp;</td>";
                }

                echo "<td class='center'>" . getUserName($data["users_id"]) . "</td>";
                echo "<td class='center'>" . nl2br((string) $data["comment"]) . "</td>";
                echo "<td class='center'>";
                list($annee, $mois, $jour) = explode("-", (string) $data["begin"]);
                echo "<a href='" . $CFG_GLPI["root_doc"] . "/front/reservation.php?reservationitems_id=" .
                      $data["reservationitems_id"] . "&amp;mois_courant=$mois&amp;" .
                      "annee_courante=$annee' title=\"" . __s('See planning') . "\">";
                echo "<i class='far fa-calendar-alt' aria-hidden='true'></i>";
                echo "<span class='sr-only'>" . __('See planning') . "</span>";
                echo "</a></td></tr>\n";
            }
        }
        echo "</table></div>\n";

        // Print old reservations
        $iterator = $DB->request([
           'SELECT'    => [
              'begin',
              'end',
              'items_id',
              'glpi_reservationitems.entities_id',
              'users_id',
              'glpi_reservations.comment',
              'reservationitems_id',
              'completename'
           ],
           'FROM'      => 'glpi_reservations',
           'LEFT JOIN' => [
              'glpi_reservationitems' => [
                 'ON' => [
                    'glpi_reservationitems' => 'id',
                    'glpi_reservations'     => 'reservationitems_id'
                 ]
              ],
              'glpi_entities'         => [
                 'ON' => [
                    'glpi_reservationitems' => 'entities_id',
                    'glpi_entities'         => 'id'
                 ]
              ]
           ],
           'WHERE'     => [
              'end'       => ['<=', $now],
              'users_id'  => $ID
           ],
           'ORDERBY'   => 'begin DESC'
        ]);

        echo "<div class='spaced'>";
        echo "<table class='tab_cadre_fixehov' aria-label='Past Reservations'>";
        echo "<tr><th colspan='6'>" . __('Past reservations') . "</th></tr>\n";

        if (count($iterator) == 0) {
            echo "<tr class='tab_bg_2'>";
            echo "<td class='center' colspan='6'>" . __('No reservation') . "</td></tr>\n";
        } else {
            echo "<tr><th>" . __('Start date') . "</th>";
            echo "<th>" . __('End date') . "</th>";
            echo "<th>" . _n('Item', 'Items', 1) . "</th>";
            echo "<th>" . Entity::getTypeName(1) . "</th>";
            echo "<th>" . __('By') . "</th>";
            echo "<th>" . __('Comments') . "</th><th>&nbsp;</th></tr>\n";

            while ($data = $iterator->next()) {
                echo "<tr class='tab_bg_2'>";
                echo "<td class='center'>" . Html::convDateTime($data["begin"]) . "</td>";
                echo "<td class='center'>" . Html::convDateTime($data["end"]) . "</td>";

                if ($ri->getFromDB($data["reservationitems_id"])) {
                    $link = "&nbsp;";

                    if ($item = getItemForItemtype($ri->fields['itemtype'])) {
                        if ($item->getFromDB($ri->fields['items_id'])) {
                            $link = $item->getLink();
                        }
                    }
                    echo "<td class='center'>$link</td>";
                    echo "<td class='center'>" . $data['completename'] . "</td>";
                } else {
                    echo "<td class='center'>&nbsp;</td>";
                }

                echo "<td class='center'>" . getUserName($data["users_id"]) . "</td>";
                echo "<td class='center'>" . nl2br((string) $data["comment"]) . "</td>";
                echo "<td class='center'>";
                list($annee, $mois, $jour) = explode("-", (string) $data["begin"]);
                echo "<a href='" . $CFG_GLPI["root_doc"] . "/front/reservation.php?reservationitems_id=" .
                      $data["reservationitems_id"] . "&amp;mois_courant=$mois&amp;annee_courante=$annee' " .
                      "title=\"" . __s('See planning') . "\">";
                echo "<i class='far fa-calendar-alt' aria-hidden='true'></i>";
                echo "<span class='sr-only'>" . __('See planning') . "</span>";
                echo "</td></tr>\n";
            }
        }
        echo "</table></div>\n";
    }


    public static function getIcon()
    {
        return "fas fa-calendar-check";
    }
}
