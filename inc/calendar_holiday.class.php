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


class Calendar_Holiday extends CommonDBRelation
{
    public $auto_message_on_action = false;

    // From CommonDBRelation
    public static $itemtype_1 = 'Calendar';
    public static $items_id_1 = 'calendars_id';
    public static $itemtype_2 = 'Holiday';
    public static $items_id_2 = 'holidays_id';

    public static $checkItem_2_Rights = self::DONT_CHECK_ITEM_RIGHTS;


    /**
     * @since 0.84
    **/
    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    /**
     * Show holidays for a calendar
     *
     * @param $calendar Calendar object
     *
     * @return void|boolean (HTML display) False if there is a rights error.
     */
    public static function showForCalendar(Calendar $calendar)
    {
        global $DB;

        $ID = $calendar->getField('id');
        if (!$calendar->can($ID, READ)) {
            return false;
        }

        $canedit = $calendar->can($ID, UPDATE);

        $rand    = mt_rand();
        //   $iterator = $DB->request([

        //      'SELECT' => [
        //         'glpi_calendars_holidays.id AS linkid',
        //         'glpi_holidays.*'
        //      ],
        //      'DISTINCT'        => true,
        //      'FROM'            => 'glpi_calendars_holidays',
        //      'LEFT JOIN'       => [
        //         'glpi_holidays'   => [
        //            'ON' => [
        //               'glpi_calendars_holidays'  => 'holidays_id',
        //               'glpi_holidays'            => 'id'
        //            ]
        //         ]
        //      ],
        //      'WHERE'           => [
        //         'glpi_calendars_holidays.calendars_id' => $ID
        //      ],
        //      'ORDERBY'         => 'glpi_holidays.name'
        //   ]);
        $dql = "SELECT t.id AS linkid, h
            FROM Itsmng\\Domain\\Entities\\CalendarHoliday t
            LEFT JOIN t.holiday h
            WHERE t.calendar = :calendars_id
            ORDER BY h.name";

        $result = self::getAdapter()->request($dql, [
           'calendars_id' => $ID
        ]);


        //   $numrows = count($iterator);
        $numrows = count($result);
        $holidays = [];
        $used     = [];
        //   while ($data = $iterator->next()) {
        foreach ($result as $data) {
            $holidays[$data['id']] = $data;
            $used[$data['id']]     = $data['id'];
        }

        if ($canedit) {
            $form = [
               'action' => Toolbox::getItemTypeFormURL(__CLASS__),
               'buttons' => [
                  [
                     'name' => 'add',
                     'value' => _sx('button', 'Add'),
                     'class' => 'btn btn-secondary'
                  ]
               ],
               'content' => [
                  __('Add a close time') => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'calendars_id',
                           'value' => $ID
                        ],
                        Holiday::getTypeName() => [
                           'type' => 'select',
                           'name' => 'holidays_id',
                           'itemtype' => Holiday::class,
                           'used' => $used,
                           'col_lg' => 12,
                           'col_md' => 12,
                        ]
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }


        $massActionContainerId = 'mass' . __CLASS__ . $rand;
        if ($canedit && $numrows) {
            $massiveactionparams = [
               'num_displayed' => min($_SESSION['glpilist_limit'], $numrows),
               'container'     => $massActionContainerId,
               'specific_actions' => [
                  'MassiveAction:purge' => _x('button', 'Delete permanently the relation with selected elements'),
               ],
               'display_arrow' => false,
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        $fields = [
           __('Name'),
           __('Start'),
           __('End'),
           __('Recurrent'),
        ];
        $values = [];
        $massive_action = [];
        foreach ($holidays as $data) {
            $values[] = [
               '<a href="' . Toolbox::getItemTypeFormURL('Holiday') . "?id=" . $data['id'] . '">' . $data["name"] . '</a>',
               Html::convDate($data["begin_date"]),
               Html::convDate($data["end_date"]),
               Dropdown::getYesNo($data["is_perpetual"]),
            ];
            $massive_action[] = sprintf('item[%s][%s]', __CLASS__, $data['linkid']);
        }

        renderTwigTemplate('table.twig', [
           'id' => $massActionContainerId,
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action,
        ]);
    }

    /**
     * Duplicate all holidays from a calendar to its clone
     *
     * @deprecated 9.5
     *
     * @param integer $oldid The ID of the calendar to copy from.
     * @param integer $newid The ID of the calendar to copy to.
    **/
    public static function cloneCalendar($oldid, $newid)
    {
        global $DB;

        Toolbox::deprecated('Use clone');
        //   $result = $DB->request(
        //       [
        //         'FROM'   => self::getTable(),
        //         'WHERE'  => [
        //            'calendars_id' => $oldid,
        //         ]
        //       ]
        //   );
        $dql = "SELECT t
         FROM Itsmng\\Domain\\Entities\\CalendarHoliday t
         WHERE t.calendar = :oldid";

        $result = self::getAdapter()->request($dql, [
        'oldid' => $oldid
        ]);
        foreach ($result as $data) {
            $ch                   = new self();
            unset($data['id']);
            $data['calendarsId'] = $newid;
            $data['_no_history']  = true;

            $ch->add($data);
        }
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            $nb = 0;
            switch ($item->getType()) {
                case 'Calendar':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = countElementsInTable($this->getTable(), ['calendars_id' => $item->getID()]);
                    }
                    return self::createTabEntry(
                        _n('Close time', 'Close times', Session::getPluralNumber()),
                        $nb
                    );
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == 'Calendar') {
            self::showForCalendar($item);
        }
        return true;
    }
}
