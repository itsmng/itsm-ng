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

class Appointment extends \DbTestCase
{
    private const MONDAY = 1;

    private function createGroupTarget(?int $entities_id = null, int $is_recursive = 1): array
    {
        if ($entities_id === null) {
            $entities_id = getItemByTypeName('Entity', '_test_root_entity', true);
        }

        $group = new \Group();
        $groups_id = (int)$group->add([
           'name'        => 'Appointment target group',
           'entities_id' => $entities_id,
        ]);
        $this->integer($groups_id)->isGreaterThan(0);

        $target = new \AppointmentTarget();
        $appointmenttargets_id = (int)$target->add([
           'itemtype'     => 'Group',
           'items_id'     => $groups_id,
           'entities_id'  => $entities_id,
           'is_recursive' => $is_recursive,
           'is_active'    => 1,
        ]);
        $this->integer($appointmenttargets_id)->isGreaterThan(0);

        return [$target, $appointmenttargets_id, $groups_id];
    }

    private function createUserTarget(): array
    {
        $users_id = getItemByTypeName('User', 'normal', true);
        $target = new \AppointmentTarget();
        $appointmenttargets_id = (int)$target->add([
           'itemtype'     => 'User',
           'items_id'     => $users_id,
           'entities_id'  => getItemByTypeName('Entity', '_test_root_entity', true),
           'is_recursive' => 1,
           'is_active'    => 1,
        ]);
        $this->integer($appointmenttargets_id)->isGreaterThan(0);

        return [$target, $appointmenttargets_id, $users_id];
    }

    private function addAvailability(int $appointmenttargets_id, int $day = self::MONDAY): void
    {
        $availability = new \AppointmentAvailability();
        $id = (int)$availability->add([
           'appointmenttargets_id' => $appointmenttargets_id,
           'day'                   => $day,
           'begin'                 => '09:00',
           'end'                   => '17:00',
        ]);

        $this->integer($id)->isGreaterThan(0);
        $this->string($availability->fields['begin'])->isEqualTo('09:00:00');
        $this->string($availability->fields['end'])->isEqualTo('17:00:00');
    }

    private function addAppointment(
        int $appointmenttargets_id,
        string $begin = '2030-01-07 10:00:00',
        string $end = '2030-01-07 11:00:00',
        array $input = []
    ) {
        $appointment = new \Appointment();

        return $appointment->add($input + [
           'name'                  => 'Appointment test',
           'appointmenttargets_id' => $appointmenttargets_id,
           'plan'                  => [
              'begin' => $begin,
              'end'   => $end,
           ],
           '_disablenotif'         => true,
        ]);
    }

    public function testAvailabilityRulesAndExceptions()
    {
        $this->login();
        [, $appointmenttargets_id] = $this->createGroupTarget();
        $this->addAvailability($appointmenttargets_id);

        $this->boolean(\AppointmentAvailability::isAvailable(
            $appointmenttargets_id,
            '2030-01-07 10:00:00',
            '2030-01-07 11:00:00'
        ))->isTrue();

        $this->boolean(\AppointmentAvailability::isAvailable(
            $appointmenttargets_id,
            '2030-01-07 08:00:00',
            '2030-01-07 09:00:00'
        ))->isFalse();

        $this->boolean(\AppointmentAvailability::isAvailable(
            $appointmenttargets_id,
            '2030-01-07 16:00:00',
            '2030-01-08 10:00:00'
        ))->isFalse();

        $exception = new \AppointmentAvailabilityException();
        $this->integer((int)$exception->add([
           'appointmenttargets_id' => $appointmenttargets_id,
           'plan'                  => [
              'begin' => '2030-01-07 10:30:00',
              'end'   => '2030-01-07 10:45:00',
           ],
           'is_available'          => 0,
        ]))->isGreaterThan(0);

        $this->boolean(\AppointmentAvailability::isAvailable(
            $appointmenttargets_id,
            '2030-01-07 10:00:00',
            '2030-01-07 11:00:00'
        ))->isFalse();

        $this->integer((int)$exception->add([
           'appointmenttargets_id' => $appointmenttargets_id,
           'plan'                  => [
              'begin' => '2030-01-08 10:00:00',
              'end'   => '2030-01-08 11:00:00',
           ],
           'is_available'          => 1,
        ]))->isGreaterThan(0);

        $this->boolean(\AppointmentAvailability::isAvailable(
            $appointmenttargets_id,
            '2030-01-08 10:00:00',
            '2030-01-08 11:00:00'
        ))->isTrue();

        $this->boolean(\AppointmentAvailability::isAvailable(
            $appointmenttargets_id,
            '2030-01-08 09:00:00',
            '2030-01-08 12:00:00'
        ))->isFalse();
    }

    public function testAvailabilityExceptionRejectsInaccessibleTargetOnAdd()
    {
        $this->login();
        $this->setEntity('_test_root_entity', true);
        $target_entity_id = getItemByTypeName('Entity', '_test_child_2', true);
        [, $appointmenttargets_id] = $this->createGroupTarget($target_entity_id, 0);

        $this->setEntity('_test_child_1', false);

        $exception = new \AppointmentAvailabilityException();
        $this->boolean($exception->add([
           'appointmenttargets_id' => $appointmenttargets_id,
           'plan'                  => [
              'begin' => '2030-01-07 10:30:00',
              'end'   => '2030-01-07 10:45:00',
           ],
           'is_available'          => 0,
        ]) === false)->isTrue();
        $this->hasSessionMessages(ERROR, [
           'Appointment target is not available',
        ]);
    }

    public function testAvailabilityExceptionRejectsInaccessibleTargetOnUpdateRights()
    {
        $this->login();
        $this->setEntity('_test_root_entity', true);
        $target_entity_id = getItemByTypeName('Entity', '_test_child_2', true);
        [, $appointmenttargets_id] = $this->createGroupTarget($target_entity_id, 0);

        $exception = new \AppointmentAvailabilityException();
        $exceptions_id = (int)$exception->add([
           'appointmenttargets_id' => $appointmenttargets_id,
           'plan'                  => [
              'begin' => '2030-01-07 10:30:00',
              'end'   => '2030-01-07 10:45:00',
           ],
           'is_available'          => 0,
        ]);
        $this->integer($exceptions_id)->isGreaterThan(0);

        $this->setEntity('_test_child_1', false);

        $restricted_exception = new \AppointmentAvailabilityException();
        $this->boolean($restricted_exception->getFromDB($exceptions_id))->isTrue();
        $this->boolean($restricted_exception->canUpdateItem())->isFalse();
        $this->boolean($restricted_exception->canDeleteItem())->isFalse();
        $this->boolean($restricted_exception->canPurgeItem())->isFalse();
        $this->boolean($restricted_exception->update([
           'id'   => $exceptions_id,
           'plan' => [
              'begin' => '2030-01-07 11:30:00',
              'end'   => '2030-01-07 11:45:00',
           ],
        ]))->isFalse();
        $this->hasSessionMessages(ERROR, [
           'Appointment target is not available',
        ]);
    }

    public function testAddCompletesTargetFields()
    {
        $this->login();
        [, $appointmenttargets_id, $groups_id] = $this->createGroupTarget();
        $this->addAvailability($appointmenttargets_id);

        $appointment = new \Appointment();
        $appointments_id = (int)$appointment->add([
           'name'                  => 'Target-backed appointment',
           'appointmenttargets_id' => $appointmenttargets_id,
           'plan'                  => [
              'begin' => '2030-01-07 10:00:00',
              'end'   => '2030-01-07 11:00:00',
           ],
           '_disablenotif'         => true,
        ]);

        $this->integer($appointments_id)->isGreaterThan(0);
        $this->boolean($appointment->getFromDB($appointments_id))->isTrue();
        $this->array($appointment->fields)
           ->integer['appointmenttargets_id']->isEqualTo($appointmenttargets_id)
           ->integer['users_id_requester']->isEqualTo(\Session::getLoginUserID())
           ->integer['entities_id']->isEqualTo(getItemByTypeName('Entity', '_test_root_entity', true))
           ->integer['is_recursive']->isEqualTo(1)
           ->integer['state']->isEqualTo(\Planning::INFO)
           ->string['begin']->isEqualTo('2030-01-07 10:00:00')
           ->string['end']->isEqualTo('2030-01-07 11:00:00');
    }

    public function testUpdateCompletesTargetFieldsWhenRetargeting()
    {
        $this->login();
        [, $first_target_id] = $this->createGroupTarget();
        $this->addAvailability($first_target_id);

        $child_entity_id = getItemByTypeName('Entity', '_test_child_1', true);
        [, $second_target_id] = $this->createGroupTarget($child_entity_id, 0);
        $this->addAvailability($second_target_id);

        $appointment = new \Appointment();
        $appointments_id = (int)$this->addAppointment($first_target_id);
        $this->integer($appointments_id)->isGreaterThan(0);

        $this->boolean($appointment->getFromDB($appointments_id))->isTrue();
        $this->integer((int)$appointment->fields['entities_id'])
           ->isEqualTo(getItemByTypeName('Entity', '_test_root_entity', true));
        $this->integer((int)$appointment->fields['is_recursive'])->isEqualTo(1);

        $this->boolean($appointment->update([
           'id'                    => $appointments_id,
           'appointmenttargets_id' => $second_target_id,
           '_disablenotif'         => true,
        ]))->isTrue();

        $this->boolean($appointment->getFromDB($appointments_id))->isTrue();
        $this->array($appointment->fields)
           ->integer['appointmenttargets_id']->isEqualTo($second_target_id)
           ->integer['entities_id']->isEqualTo($child_entity_id)
           ->integer['is_recursive']->isEqualTo(0);
    }

    public function testRejectsOverlappingAppointmentForSameTarget()
    {
        $this->login();
        [, $appointmenttargets_id] = $this->createGroupTarget();
        $this->addAvailability($appointmenttargets_id);

        $this->integer((int)$this->addAppointment($appointmenttargets_id))->isGreaterThan(0);
        $overlap_result = $this->addAppointment(
            $appointmenttargets_id,
            '2030-01-07 10:30:00',
            '2030-01-07 11:30:00'
        );
        $this->boolean($overlap_result === false)->isTrue();
        $this->hasSessionMessages(ERROR, [
           'The selected appointment target is already booked for this timeframe',
        ]);

        $this->integer((int)$this->addAppointment(
            $appointmenttargets_id,
            '2030-01-07 11:00:00',
            '2030-01-07 12:00:00'
        ))->isGreaterThan(0);
    }

    public function testPopulatePlanningReturnsAppointmentForRequesterAndGroup()
    {
        $this->login();
        [, $appointmenttargets_id, $groups_id] = $this->createGroupTarget();
        $this->addAvailability($appointmenttargets_id);
        $appointments_id = (int)$this->addAppointment($appointmenttargets_id);

        $group_events = \Appointment::populatePlanning([
           'whogroup' => $groups_id,
           'begin'    => '2030-01-07 00:00:00',
           'end'      => '2030-01-08 00:00:00',
           'color'    => '#ff0000',
        ]);
        $this->array($group_events)->hasSize(1);

        $event = reset($group_events);
        $this->array($event)
           ->integer['appointments_id']->isEqualTo($appointments_id)
           ->integer['id']->isEqualTo($appointments_id)
           ->string['itemtype']->isEqualTo('Appointment')
           ->string['name']->isEqualTo('Appointment test')
           ->string['begin']->isEqualTo('2030-01-07 10:00:00')
           ->string['end']->isEqualTo('2030-01-07 11:00:00')
           ->string['color']->isEqualTo('#ff0000');

        $requester_events = \Appointment::populatePlanning([
           'who'   => \Session::getLoginUserID(),
           'begin' => '2030-01-07 00:00:00',
           'end'   => '2030-01-08 00:00:00',
        ]);
        $this->array($requester_events)->hasSize(1);
        $requester_event = reset($requester_events);
        $this->integer($requester_event['appointments_id'])->isEqualTo($appointments_id);
    }

    public function testPopulatePlanningReturnsAppointmentForRequesterAndReceiver()
    {
        $this->login();
        [, $appointmenttargets_id, $users_id] = $this->createUserTarget();
        $this->addAvailability($appointmenttargets_id);
        $appointments_id = (int)$this->addAppointment($appointmenttargets_id);

        $receiver_events = \Appointment::populatePlanning([
           'who'   => $users_id,
           'begin' => '2030-01-07 00:00:00',
           'end'   => '2030-01-08 00:00:00',
        ]);
        $this->array($receiver_events)->hasSize(1);
        $receiver_event = reset($receiver_events);
        $this->integer($receiver_event['appointments_id'])->isEqualTo($appointments_id);
        $this->integer($receiver_event['users_id'])->isEqualTo($users_id);

        $requester_events = \Appointment::populatePlanning([
           'who'   => \Session::getLoginUserID(),
           'begin' => '2030-01-07 00:00:00',
           'end'   => '2030-01-08 00:00:00',
        ]);
        $this->array($requester_events)->hasSize(1);
        $requester_event = reset($requester_events);
        $this->integer($requester_event['appointments_id'])->isEqualTo($appointments_id);
    }
}
