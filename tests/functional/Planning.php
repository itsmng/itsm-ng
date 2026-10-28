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

namespace tests\units;

/* Test for inc/planning.class.php */

class Planning extends \DbTestCase
{
    public function testListStartsToday()
    {
        $this->login();
        \Planning::initSessionForCurrentUser();
        foreach ($_SESSION['glpi_plannings']['filters'] as $type => &$filter) {
            if ($type !== 'view_filter') {
                $filter['display'] = $type === 'PlanningExternalEvent';
            }
        }
        unset($filter);
        $today = date('Y-m-d');
        $ids = [];
        foreach (['yesterday' => '-1 day', 'today' => 'today', 'tomorrow' => '+1 day',
                  'recurring' => '-2 days', 'expired' => '-2 days', 'midnight' => '-1 day'] as $name => $offset) {
            $day = date('Y-m-d', strtotime($offset));
            $input = [
                'name' => 'Planning range ' . $name,
                'entities_id' => getItemByTypeName('Entity', '_test_root_entity', true),
                'state' => \Planning::TODO,
                'plan' => ['begin' => "$day 00:00:00", 'end' => "$day 01:00:00"],
            ];
            if ($name === 'recurring' || $name === 'expired') {
                $input['rrule'] = json_encode(['freq' => 'daily', 'count' => $name === 'expired' ? 1 : 5]);
            }
            if ($name === 'midnight') {
                $input['plan']['end'] = "$today 00:00:00";
            }
            $event = new \PlanningExternalEvent();
            $this->integer($ids[$name] = $event->add($input))->isGreaterThan(0);
        }
        // Overlapping fixtures intentionally trigger availability warnings.
        unset($_SESSION['MESSAGE_AFTER_REDIRECT'][WARNING]);
        $options = [
            'start' => date('Y-m-d', strtotime('-5 years')),
            'end' => date('Y-m-d', strtotime('+5 years')),
            'view_name' => 'listFull',
            'display_done_events' => false,
        ];
        $events = \Planning::constructEventsArray($options);
        $events = array_column($events, null, 'items_id');
        $this->array($events)->hasKeys([$ids['today'], $ids['tomorrow'], $ids['recurring']])
            ->notHasKeys([$ids['yesterday'], $ids['expired'], $ids['midnight']]);
        $this->string(substr($events[$ids['recurring']]['start'], 0, 10))->isIdenticalTo($today);

        // Calendar views must still allow browsing historical events.
        $options['view_name'] = 'timeGridWeek';
        $options['start'] = date('Y-m-d', strtotime('-3 days'));
        $options['end'] = date('Y-m-d', strtotime('+3 days'));
        $events = \Planning::constructEventsArray($options);
        $this->array(array_column($events, 'items_id'))->contains($ids['yesterday']);
    }

    public function testViewFiltersPersistAndDecorateTitles()
    {
        $this->login();
        \Planning::initSessionForCurrentUser();
        $this->array($_SESSION['glpi_plannings']['filters']['view_filter'])->isIdenticalTo([
            'start' => 'false', 'end' => 'false', 'user' => 'false', 'group' => 'false',
        ]);
        $task = new \TicketTask();
        $group = new \Group();
        $this->integer($group_id = $group->add([
            'name' => 'Planning view group',
            'entities_id' => getItemByTypeName('Entity', '_test_root_entity', true),
        ]))->isGreaterThan(0);
        $today = date('Y-m-d');
        $this->integer($task_id = $task->add([
            'tickets_id' => getItemByTypeName('Ticket', '_ticket01', true),
            'content' => 'Planning view task',
            'state' => \Planning::TODO,
            'users_id_tech' => \Session::getLoginUserID(),
            'groups_id_tech' => $group_id,
            'plan' => ['begin' => "$today 10:00:00", 'end' => "$today 11:00:00"],
        ]))->isGreaterThan(0);
        $options = ['start' => $today, 'end' => date('Y-m-d', strtotime('+1 day')), 'view_name' => 'listFull'];
        $find_task = static function () use ($options, $task_id) {
            foreach (\Planning::constructEventsArray($options) as $event) {
                if ($event['itemtype'] === 'TicketTask' && $event['items_id'] === $task_id) {
                    return $event;
                }
            }
            return [];
        };
        $plain = $find_task();
        $this->array($plain)->isNotEmpty();
        $user = new \User();
        $user->getFromDB(\Session::getLoginUserID());
        foreach (['start', 'end', 'user', 'group'] as $name) {
            \Planning::toggleFilter(['type' => 'view_filter', 'name' => $name, 'display' => 'true']);
        }
        $saved_user = new \User();
        $saved_user->getFromDB(\Session::getLoginUserID());
        $saved = importArrayFromDB($saved_user->fields['plannings']);
        $this->array($saved['filters']['view_filter'])->isIdenticalTo(array_fill_keys(['start', 'end', 'user', 'group'], 'true'));
        $_SESSION['glpi_plannings'] = $saved;
        \Planning::initSessionForCurrentUser();
        $decorated = $find_task();
        $this->string($decorated['title'])->contains(\Html::convDate($today))
            ->contains($user->getName())->contains('Planning view group');
        $this->string(substr($decorated['title'], -strlen($plain['title'])))->isIdenticalTo($plain['title']);
        foreach (['start', 'end', 'user', 'group'] as $name) {
            \Planning::toggleFilter(['type' => 'view_filter', 'name' => $name, 'display' => 'false']);
        }
        $this->string($find_task()['title'])->isIdenticalTo($plain['title']);
    }

    public function testCalendarRequestsCannotChangePermissions()
    {
        $this->login();
        \Planning::initSessionForCurrentUser();

        $_SESSION['glpiactiveprofile']['externalevent'] = READ | UPDATE;
        foreach ($_SESSION['glpi_plannings']['filters'] as $itemtype => $filter) {
            $_SESSION['glpi_plannings']['filters'][$itemtype]['display'] = $itemtype === 'PlanningExternalEvent';
        }
        $_SESSION['glpi_plannings']['plannings'] = [
           'user_' . \Session::getLoginUserID() => [
              'color' => '#000000',
              'display' => true,
              'type' => 'user',
           ],
        ];
        $profile = $_SESSION['glpiactiveprofile'];

        \Planning::constructEventsArray([
           'start' => '2026-10-12 00:00:00',
           'end' => '2026-10-13 00:00:00',
           'genical' => true,
        ]);

        $this->array($_SESSION['glpiactiveprofile'])->isIdenticalTo($profile);
    }

    public function testCloneEvent()
    {
        $this->login();

        $input = [
           'name'  => "test event to clone",
           'entities_id' => getItemByTypeName('Entity', '_test_root_entity', true),
           'plan'  => [
              'begin'     => date('Y-m-d H:i:s'),
              '_duration' => 2 * HOUR_TIMESTAMP
           ],
           'rrule' => '{"freq":"weekly","interval":"1"}'
        ];

        $event = new \PlanningExternalEvent();
        $this->integer($event_id = $event->add($input))->isGreaterThan(0);

        $timestamp = time() + DAY_TIMESTAMP;
        $new_start = date('Y-m-d H:i:s', $timestamp);
        $new_end   = date('Y-m-d H:i:s', $timestamp + 2 * HOUR_TIMESTAMP);

        $this->integer($clone_events_id = \Planning::cloneEvent([
           'old_itemtype' => 'PlanningExternalEvent',
           'old_items_id' => $event_id,
           'start'        => $new_start,
           'end'          => $new_end
        ]))->isGreaterThan(0);

        // check cloned event
        $this->boolean($event->getFromDB($clone_events_id))->isTrue();
        $this->array($event->fields)
           ->string['begin']->isEqualTo($new_start)
           ->string['end']->isEqualTo($new_end)
           ->string['rrule']->isEqualTo($input['rrule']);
        $this->string($event->fields['name'])->contains(sprintf(__('Copy of %s'), $input['name']));
    }

    public function testGetExternalCalendarEvents()
    {

        $this->login();

        $session_backup = $_SESSION['glpi_plannings'];

        \Planning::initSessionForCurrentUser();

        // Expected results
        $expected_events_data = [
           'recurring_evt_1' => [
              'title'   => 'Recur event',
              'tooltip' => "Recur event\nMonday, 11 am to 12 am starting on 1st of July",
              'color'   => '#ff0000',
              'rrule'   => "DTSTART:20190701T090000\nRRULE:FREQ=WEEKLY;BYDAY=MO\n",
           ],
           'simple_evt_1'    => [
              'title'   => 'Base event with no desc',
              'tooltip' => 'Base event with no desc',
              'color'   => '#ff0000',
           ],
           'simple_evt_2'    => [
              'title'   => 'An event in 2020/01/05',
              'tooltip' => "An event in 2020/01/05\nDescription of my event",
              'color'   => '#ff0000',
           ],
           'task_1'          => [
              'title'   => 'Test task',
              'tooltip' => "Test task\nDescription of the task.",
              'color'   => '#ff0000',
           ],
           'another_evt_1'   => [
              'title'   => 'Another event',
              'tooltip' => "Another event\nAnother event description",
              'color'   => '#a500b3',
           ],
        ];

        $expected_events_keys = [
           'all_events'   => ['recurring_evt_1', 'simple_evt_1', 'simple_evt_2', 'task_1', 'another_evt_1'],
           'month_events' => ['recurring_evt_1', 'simple_evt_1', 'task_1', 'another_evt_1'],
           'cal2_events'  => ['another_evt_1'],
        ];

        // Add calendars
        $_SESSION['glpi_plannings']['plannings'] = [
           'external_1' => [
              'color'   => '#ff0000',
              'display' => true,
              'type'    => 'external',
              'name'    => 'External calendar 1',
              'url'     => 'file://' . realpath(GLPI_ROOT . '/tests/fixtures/ical/sample_1.ics'),
           ],
           'external_2' => [
              'color'   => '#a500b3',
              'display' => true,
              'type'    => 'external',
              'name'    => 'External calendar 2',
              'url'     => 'file://' . realpath(GLPI_ROOT . '/tests/fixtures/ical/sample_2.ics'),
           ],
        ];

        // Fetch all events
        $all_events = \Planning::constructEventsArray(
            [
              'start'            => '2000-01-01 00:00:00',
              'end'              => '2050-12-31 23:59:59',
              'force_all_events' => true
         ]
        );
        // Fetch events only for a given month
        $month_events = \Planning::constructEventsArray(
            [
              'start' => '2019-11-01 00:00:00',
              'end'   => '2019-11-30 23:59:59',
         ]
        );
        // Fetch events only for a given calendar
        $_SESSION['glpi_plannings']['plannings']['external_1']['display'] = false;
        $cal2_events = \Planning::constructEventsArray(
            [
              'start' => '2019-11-01 00:00:00',
              'end'   => '2019-11-30 23:59:59',
         ]
        );

        $_SESSION['glpi_plannings'] = $session_backup;

        foreach ($expected_events_keys as $list_var => $events_keys) {
            $events_list = $$list_var;
            $this->array($events_list)->hasSize(count($events_keys));
            foreach ($events_keys as $index => $evt_key) {
                $event_data = $expected_events_data[$evt_key];
                $this->array($events_list)->hasKey($index);
                $this->array($events_list[$index])
                   ->string['title']->isEqualTo($event_data['title'])
                   ->string['tooltip']->isEqualTo($event_data['tooltip'])
                   ->string['color']->isEqualTo($event_data['color']);
                if (array_key_exists('rrule', $event_data)) {
                    $this->array($events_list[$index])
                       ->string['rrule']
                          ->isEqualTo($event_data['rrule']);
                } else {
                    $this->array($events_list[$index])->notHasKey('rrule');
                }
            }
        }
    }

    public function testHolidayPlanningEvents()
    {
        $this->login();

        $session_backup = $_SESSION['glpi_plannings'];

        \Planning::initSessionForCurrentUser();
        foreach ($_SESSION['glpi_plannings']['filters'] as $itemtype => $filter) {
            $_SESSION['glpi_plannings']['filters'][$itemtype]['display'] = false;
        }
        $_SESSION['glpi_plannings']['filters']['Holiday']['display'] = true;
        $_SESSION['glpi_plannings']['filters']['Holiday']['color'] = \Planning::HOLIDAY_DEFAULT_COLOR;
        $_SESSION['glpi_plannings']['plannings'] = [
           'user_' . \Session::getLoginUserID() => [
              'color'   => \Planning::getPaletteColor('bg', 0),
              'display' => true,
              'type'    => 'user',
           ],
        ];

        $holiday = new \Holiday();
        $holidays_id = (int)$holiday->add([
           'name'         => 'Planning test close time',
           'entities_id'  => getItemByTypeName('Entity', '_test_root_entity', true),
           'is_recursive' => 1,
           'begin_date'   => '2026-04-29',
           'end_date'     => '2026-04-30',
           'is_perpetual' => 0,
        ]);
        $this->integer($holidays_id)->isGreaterThan(0);

        $events = \Planning::constructEventsArray([
           'start' => '2026-04-01 00:00:00',
           'end'   => '2026-04-30 23:59:59',
        ]);

        $_SESSION['glpi_plannings'] = $session_backup;

        $this->array($events)->hasSize(1);
        $this->array($events[0])
           ->string['title']->isEqualTo('Planning test close time')
           ->string['start']->isEqualTo('2026-04-29')
           ->string['end']->isEqualTo('2026-05-01')
           ->string['color']->isEqualTo(\Planning::HOLIDAY_DEFAULT_COLOR)
           ->string['borderColor']->isEqualTo(\Planning::HOLIDAY_DEFAULT_COLOR)
           ->string['itemtype']->isEqualTo('Holiday')
           ->boolean['editable']->isFalse();
        $this->integer((int)$events[0]['items_id'])
           ->isEqualTo($holidays_id);
        $this->array($events[0])
           ->hasKey('resourceIds');
        $this->array($events[0]['resourceIds'])
           ->contains('user_' . \Session::getLoginUserID());
    }

    public function testHolidayDefaultColor()
    {
        $this->login();

        $session_backup = $_SESSION['glpi_plannings'];
        unset($_SESSION['glpi_plannings']['filters']['Holiday']);

        \Planning::initSessionForCurrentUser();
        $this->array($_SESSION['glpi_plannings']['filters'])
           ->hasKey('Holiday');
        $this->string($_SESSION['glpi_plannings']['filters']['Holiday']['color'])
           ->isEqualTo(\Planning::HOLIDAY_DEFAULT_COLOR);

        $_SESSION['glpi_plannings']['filters']['Holiday']['color'] = '#8C5344';
        \Planning::initSessionForCurrentUser();
        $this->string($_SESSION['glpi_plannings']['filters']['Holiday']['color'])
           ->isEqualTo(\Planning::HOLIDAY_DEFAULT_COLOR);

        $_SESSION['glpi_plannings']['filters']['Holiday']['color'] = '#123456';
        \Planning::initSessionForCurrentUser();
        $this->string($_SESSION['glpi_plannings']['filters']['Holiday']['color'])
           ->isEqualTo('#123456');

        $_SESSION['glpi_plannings'] = $session_backup;
    }
}
