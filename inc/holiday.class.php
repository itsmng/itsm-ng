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
 * Holiday Class
**/
class Holiday extends CommonDropdown
{
    public static $rightname = 'calendar';

    public $can_be_translated = false;


    public static function getTypeName($nb = 0)
    {
        return _n('Close time', 'Close times', $nb);
    }


    public function getAdditionalFields()
    {

        return [
           __('Start') => [
              'name'  => 'begin_date',
              'type'  => 'date',
              'value' => $this->fields['begin_date']
           ],
           __('End') => [
              'name'  => 'end_date',
              'type'  => 'date',
              'value' => $this->fields['end_date']
           ],
           __('Recurrent') => [
              'name'  => 'is_perpetual',
              'type'  => 'checkbox',
              'value' => $this->fields['is_perpetual']
           ]
        ];
    }


    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '11',
           'table'              => $this->getTable(),
           'field'              => 'begin_date',
           'name'               => __('Start'),
           'datatype'           => 'date'
        ];

        $tab[] = [
           'id'                 => '12',
           'table'              => $this->getTable(),
           'field'              => 'end_date',
           'name'               => __('End'),
           'datatype'           => 'date'
        ];

        $tab[] = [
           'id'                 => '13',
           'table'              => $this->getTable(),
           'field'              => 'is_perpetual',
           'name'               => __('Recurrent'),
           'datatype'           => 'bool'
        ];

        return $tab;
    }


    /**
     * Populate planning with close times/holidays.
     *
     * @param array $options Planning options
     *
     * @return array
     */
    public static function populatePlanning($options = []): array
    {
        global $DB, $CFG_GLPI;

        $events = [];
        if (
            !isset($options['begin']) || ($options['begin'] == 'NULL')
            || !isset($options['end']) || ($options['end'] == 'NULL')
        ) {
            return $events;
        }

        $default_options = [
           'genical'          => false,
           'color'            => '',
           'event_type_color' => '',
           'resourceIds'      => [],
        ];
        $options = array_merge($default_options, $options);

        $begin = $options['begin'];
        $end   = $options['end'];
        $begin_date = date('Y-m-d', strtotime((string) $begin));
        $end_date   = date('Y-m-d', strtotime((string) $end));

        $where = getEntitiesRestrictCriteria(self::getTable(), '', '', true);
        $where[] = ['NOT' => [self::getTable() . '.begin_date' => null]];
        $where[] = [
           'OR' => [
              [
                 'AND' => [
                    [
                       self::getTable() . '.is_perpetual' => 0,
                       self::getTable() . '.begin_date'   => ['<=', $end_date],
                    ],
                    [
                       'OR' => [
                          [self::getTable() . '.end_date' => ['>=', $begin_date]],
                          [self::getTable() . '.end_date' => null],
                       ],
                    ],
                 ],
              ],
              [
                 self::getTable() . '.is_perpetual' => 1,
              ],
           ],
        ];

        $iterator = $DB->request([
           'SELECT' => [
              self::getTable() . '.*',
              'glpi_calendars.id AS calendars_id',
              'glpi_calendars.name AS calendar_name',
           ],
           'FROM'      => self::getTable(),
           'LEFT JOIN' => [
              'glpi_calendars_holidays' => [
                 'ON' => [
                    self::getTable()          => 'id',
                    'glpi_calendars_holidays' => 'holidays_id',
                 ],
              ],
              'glpi_calendars' => [
                 'ON' => [
                    'glpi_calendars_holidays' => 'calendars_id',
                    'glpi_calendars'          => 'id',
                 ],
              ],
           ],
           'WHERE'   => $where,
           'ORDERBY' => [
              self::getTable() . '.begin_date',
              self::getTable() . '.name',
           ],
        ]);

        $holidays = [];
        while ($data = $iterator->next()) {
            if (!isset($holidays[$data['id']])) {
                $data['calendar_names'] = [];
                $holidays[$data['id']] = $data;
            }

            if (!empty($data['calendar_name'])) {
                $holidays[$data['id']]['calendar_names'][] = $data['calendar_name'];
            }
        }

        foreach ($holidays as $holiday) {
            $holiday['calendar_names'] = array_values(array_unique($holiday['calendar_names']));

            foreach (self::getOccurrencesForPlanningRange($holiday, $begin, $end) as $occurrence) {
                $key = $occurrence['begin'] . '$$Holiday$$' . $holiday['id'];
                $url = self::getFormURLWithID($holiday['id'], false);

                $events[$key] = [
                   'color'              => $options['color'],
                   'event_type_color'   => $options['event_type_color'],
                   'itemtype'           => self::getType(),
                   'holidays_id'        => $holiday['id'],
                   'id'                 => $holiday['id'],
                   'users_id'           => Session::getLoginUserID(),
                   'begin'              => $occurrence['begin'],
                   'end'                => $occurrence['end'],
                   'holiday_begin_date' => $occurrence['begin_date'],
                   'holiday_end_date'   => $occurrence['end_date'],
                   'name'               => Html::clean(Html::resume_text($holiday['name'], $CFG_GLPI['cut'])),
                   'text'               => Html::resume_text(Html::clean($holiday['comment']), $CFG_GLPI['cut']),
                   'calendar_names'     => $holiday['calendar_names'],
                   'editable'           => false,
                   'url'                => $options['genical'] ? $CFG_GLPI['url_base'] . $url : $url,
                   'resourceIds'        => $options['resourceIds'],
                ];
            }
        }

        return $events;
    }


    /**
     * Display a planning item.
     *
     * @param array  $val      Array of the item to display
     * @param int    $who      ID of the user
     * @param string $type     Position of the item in the time block
     * @param bool   $complete Complete display
     *
     * @return string
     */
    public static function displayPlanningItem(array $val, $who, $type = "", $complete = 0)
    {
        global $CFG_GLPI;

        $rand = mt_rand();
        $id = 'holiday_' . $val['holidays_id'] . $rand;

        $html = "<img src='" . $CFG_GLPI['root_doc'] . "/pics/rdv.png' alt='' title=\"" .
              self::getTypeName(1) . "\">&nbsp;";
        $html .= "<a id='" . $id . "' href='" . self::getFormURLWithID($val['holidays_id']) . "'>";
        $html .= Html::clean($val['name']);
        $html .= "</a>";

        $details = [];
        $details[] = sprintf(
            __('%1$s: %2$s'),
            __('Period'),
            self::getPlanningDateRangeLabel($val['holiday_begin_date'], $val['holiday_end_date'])
        );

        if (!empty($val['calendar_names'])) {
            $details[] = sprintf(
                __('%1$s: %2$s'),
                Calendar::getTypeName(count($val['calendar_names'])),
                implode(', ', array_map([Html::class, 'clean'], $val['calendar_names']))
            );
        }

        if (!empty($val['text'])) {
            $details[] = $val['text'];
        }

        $content = implode('<br>', $details);
        if ($complete) {
            $html .= "<div class='event-description rich_text_container'>" . $content . "</div>";
        } else {
            $html .= Html::showToolTip($content, [
               'applyto' => $id,
               'display' => false,
            ]);
        }

        return $html;
    }


    private static function getOccurrencesForPlanningRange(array $holiday, string $range_begin, string $range_end): array
    {
        $occurrences = [];

        if (empty($holiday['begin_date'])) {
            return $occurrences;
        }

        $holiday_end = empty($holiday['end_date']) ? $holiday['begin_date'] : $holiday['end_date'];

        if (!$holiday['is_perpetual']) {
            return self::getOccurrenceIfInRange($holiday['begin_date'], $holiday_end, $range_begin, $range_end);
        }

        $start_year = (int) date('Y', strtotime($range_begin)) - 1;
        $end_year   = (int) date('Y', strtotime($range_end)) + 1;

        for ($year = $start_year; $year <= $end_year; $year++) {
            $begin = self::getPerpetualDateForYear($holiday['begin_date'], $year);
            $end = self::getPerpetualDateForYear($holiday_end, $year);

            if (strtotime($end) < strtotime($begin)) {
                $end = self::getPerpetualDateForYear($holiday_end, $year + 1);
            }

            $occurrences = array_merge(
                $occurrences,
                self::getOccurrenceIfInRange($begin, $end, $range_begin, $range_end)
            );
        }

        return $occurrences;
    }


    private static function getOccurrenceIfInRange(string $begin, string $end, string $range_begin, string $range_end): array
    {
        $event_begin = strtotime($begin . ' 00:00:00');
        $event_end = strtotime($end . ' 00:00:00 +1 day');
        $range_begin_ts = strtotime($range_begin);
        $range_end_ts = strtotime($range_end);

        if ($event_end <= $range_begin_ts || $event_begin >= $range_end_ts) {
            return [];
        }

        return [[
           'begin'      => date('Y-m-d 00:00:00', $event_begin),
           'end'        => date('Y-m-d 00:00:00', $event_end),
           'begin_date' => date('Y-m-d', $event_begin),
           'end_date'   => date('Y-m-d', strtotime($end)),
        ]];
    }


    private static function getPerpetualDateForYear(string $date, int $year): string
    {
        $month = (int) date('m', strtotime($date));
        $day = (int) date('d', strtotime($date));
        $day = min($day, (int) date('t', mktime(0, 0, 0, $month, 1, $year)));

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }


    private static function getPlanningDateRangeLabel(string $begin, string $end): string
    {
        if ($begin === $end) {
            return Html::convDate($begin);
        }

        return sprintf(__('%1$s - %2$s'), Html::convDate($begin), Html::convDate($end));
    }


    public function prepareInputForAdd($input)
    {

        $input = parent::prepareInputForAdd($input);

        if (
            empty($input['end_date'])
            || ($input['end_date'] == 'NULL')
            || ($input['end_date'] < $input['begin_date'])
        ) {
            $input['end_date'] = $input['begin_date'];
        }
        return $input;
    }


    public function prepareInputForUpdate($input)
    {

        $input = parent::prepareInputForUpdate($input);

        if (
            isset($input['begin_date']) && (empty($input['end_date'])
            || ($input['end_date'] == 'NULL')
            || ($input['end_date'] < $input['begin_date']))
        ) {
            $input['end_date'] = $input['begin_date'];
        }

        return $input;
    }
}
