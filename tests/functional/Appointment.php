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
        $groups_id = (int) $group->add([
            'name' => 'Appointment target group',
            'entities_id' => $entities_id,
        ]);
        $this->integer($groups_id)->isGreaterThan(0);

        $user = new \User();
        $users_id = (int) $user->add([
            'name' => 'appointment-member-' . $this->getUniqueString(),
            'authtype' => \Auth::DB_GLPI,
        ]);
        $this->integer($users_id)->isGreaterThan(0);
        $group_user = new \Group_User();
        $this->integer((int) $group_user->add([
            'groups_id' => $groups_id,
            'users_id' => $users_id,
        ]))->isGreaterThan(0);

        $target = new \AppointmentTarget();
        $appointmenttargets_id = (int) $target->add([
            'itemtype' => 'Group',
            'items_id' => $groups_id,
            'entities_id' => $entities_id,
            'is_recursive' => $is_recursive,
            'is_active' => 1,
        ]);
        $this->integer($appointmenttargets_id)->isGreaterThan(0);

        $user_target = new \AppointmentTarget();
        $user_target_id = (int) $user_target->add([
            'itemtype' => 'User',
            'items_id' => $users_id,
            'entities_id' => $entities_id,
            'is_recursive' => $is_recursive,
            'is_active' => 1,
        ]);
        $this->integer($user_target_id)->isGreaterThan(0);

        return [$target, $appointmenttargets_id, $groups_id, $user_target_id, $users_id];
    }

    private function createUserTarget(): array
    {
        $users_id = getItemByTypeName('User', 'normal', true);
        $target = new \AppointmentTarget();
        if (!$target->getFromDBByItem('User', $users_id)) {
            $appointmenttargets_id = (int) $target->add([
                'itemtype' => 'User',
                'items_id' => $users_id,
                'entities_id' => getItemByTypeName('Entity', '_test_root_entity', true),
                'is_recursive' => 1,
                'is_active' => 1,
            ]);
            $this->integer($appointmenttargets_id)->isGreaterThan(0);
        } else {
            $appointmenttargets_id = (int) $target->fields['id'];
        }

        return [$target, $appointmenttargets_id, $users_id];
    }

    private function addAvailability(int $appointmenttargets_id, int $day = self::MONDAY): int
    {
        $availability = new \AppointmentAvailability();
        $id = (int) $availability->add([
            'appointmenttargets_id' => $appointmenttargets_id,
            'day' => $day,
            'begin' => '09:00',
            'end' => '17:00',
        ]);

        $this->integer($id)->isGreaterThan(0);
        $this->string($availability->fields['begin'])->isEqualTo('09:00:00');
        $this->string($availability->fields['end'])->isEqualTo('17:00:00');

        $target = new \AppointmentTarget();
        if ($target->getFromDB($appointmenttargets_id) && $target->fields['itemtype'] === 'Group') {
            foreach (\AppointmentTarget::getGroupMemberTargetRows($target->fields) as $member_target) {
                $member_availability = new \AppointmentAvailability();
                $this->integer((int) $member_availability->add([
                    'appointmenttargets_id' => $member_target['id'],
                    'day' => $day,
                    'begin' => '09:00',
                    'end' => '17:00',
                ]))->isGreaterThan(0);
            }
        }

        return $id;
    }

    private function addAppointment(
        int $appointmenttargets_id,
        string $begin = '2030-01-07 10:00:00',
        string $end = '2030-01-07 11:00:00',
        array $input = []
    ) {
        $appointment = new \Appointment();

        return $appointment->add($input + [
            'name' => 'Appointment test',
            'appointmenttargets_id' => $appointmenttargets_id,
            'plan' => [
                'begin' => $begin,
                'end' => $end,
            ],
            '_disablenotif' => true,
        ]);
    }

    private function setSelfServiceAppointmentRight(int $right): void
    {
        global $DB;

        $DB->update(
            'glpi_profilerights',
            [
                'rights' => $right
            ],
            [
                'profiles_id' => getItemByTypeName('Profile', 'Self-Service', true),
                'name' => \Appointment::$rightname,
            ]
        );
    }

    private function getSelfServiceAppointmentRight(): int
    {
        global $DB;

        $iterator = $DB->request([
            'SELECT' => ['rights'],
            'FROM' => 'glpi_profilerights',
            'WHERE' => [
                'profiles_id' => getItemByTypeName('Profile', 'Self-Service', true),
                'name' => \Appointment::$rightname,
            ]
        ]);

        $row = $iterator->next();
        return (int) $row['rights'];
    }

    public function testAvailabilityRulesAndUnavailabilities()
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

        $unavailability = new \AppointmentUnavailability();
        $this->integer((int) $unavailability->add([
            'appointmenttargets_id' => $appointmenttargets_id,
            'plan' => [
                'begin' => '2030-01-07 10:30:00',
                'end' => '2030-01-07 10:45:00',
            ],
            'is_available' => 0,
        ]))->isGreaterThan(0);

        $this->boolean(\AppointmentAvailability::isAvailable(
            $appointmenttargets_id,
            '2030-01-07 10:00:00',
            '2030-01-07 11:00:00'
        ))->isFalse();

        $this->integer((int) $unavailability->add([
            'appointmenttargets_id' => $appointmenttargets_id,
            'plan' => [
                'begin' => '2030-01-08 10:00:00',
                'end' => '2030-01-08 11:00:00',
            ],
            'is_available' => 1,
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

    public function testAvailabilityUnavailabilityRejectsInaccessibleTargetOnAdd()
    {
        $this->login();
        $this->setEntity('_test_root_entity', true);
        $target_entity_id = getItemByTypeName('Entity', '_test_child_2', true);
        [, $appointmenttargets_id] = $this->createGroupTarget($target_entity_id, 0);

        $this->setEntity('_test_child_1', false);

        $unavailability = new \AppointmentUnavailability();
        $this->boolean($unavailability->add([
            'appointmenttargets_id' => $appointmenttargets_id,
            'plan' => [
                'begin' => '2030-01-07 10:30:00',
                'end' => '2030-01-07 10:45:00',
            ],
            'is_available' => 0,
        ]) === false)->isTrue();
        $this->hasSessionMessages(ERROR, [
            'Appointment target is not available',
        ]);
    }

    public function testAvailabilityUnavailabilityRejectsInaccessibleTargetOnUpdateRights()
    {
        $this->login();
        $this->setEntity('_test_root_entity', true);
        $target_entity_id = getItemByTypeName('Entity', '_test_child_2', true);
        [, $appointmenttargets_id] = $this->createGroupTarget($target_entity_id, 0);

        $unavailability = new \AppointmentUnavailability();
        $unavailabilities_id = (int) $unavailability->add([
            'appointmenttargets_id' => $appointmenttargets_id,
            'plan' => [
                'begin' => '2030-01-07 10:30:00',
                'end' => '2030-01-07 10:45:00',
            ],
            'is_available' => 0,
        ]);
        $this->integer($unavailabilities_id)->isGreaterThan(0);

        $this->setEntity('_test_child_1', false);

        $restricted_unavailability = new \AppointmentUnavailability();
        $this->boolean($restricted_unavailability->getFromDB($unavailabilities_id))->isTrue();
        $this->boolean($restricted_unavailability->canUpdateItem())->isFalse();
        $this->boolean($restricted_unavailability->canDeleteItem())->isFalse();
        $this->boolean($restricted_unavailability->canPurgeItem())->isFalse();
        $this->boolean($restricted_unavailability->update([
            'id' => $unavailabilities_id,
            'plan' => [
                'begin' => '2030-01-07 11:30:00',
                'end' => '2030-01-07 11:45:00',
            ],
        ]))->isFalse();
        $this->hasSessionMessages(ERROR, [
            'Appointment target is not available',
        ]);
    }

    public function testTargetMutationRightsRejectInaccessibleEntity()
    {
        $this->login();
        $this->setEntity('_test_root_entity', true);
        $target_entity_id = getItemByTypeName('Entity', '_test_child_2', true);
        [, $appointmenttargets_id] = $this->createGroupTarget($target_entity_id, 0);

        $target = new \AppointmentTarget();
        $this->boolean($target->can($appointmenttargets_id, UPDATE))->isTrue();
        $this->boolean($target->can($appointmenttargets_id, PURGE))->isTrue();

        $this->setEntity('_test_child_1', false);

        $restricted_target = new \AppointmentTarget();
        $this->boolean($restricted_target->getFromDB($appointmenttargets_id))->isTrue();
        $this->boolean($restricted_target->can($appointmenttargets_id, UPDATE))->isFalse();
        $this->boolean($restricted_target->can($appointmenttargets_id, PURGE))->isFalse();
    }

    public function testAvailabilityMutationRightsRejectInaccessibleTarget()
    {
        $this->login();
        $this->setEntity('_test_root_entity', true);
        $target_entity_id = getItemByTypeName('Entity', '_test_child_2', true);
        [, $appointmenttargets_id] = $this->createGroupTarget($target_entity_id, 0);
        $availability_id = $this->addAvailability($appointmenttargets_id);

        $availability = new \AppointmentAvailability();
        $this->boolean($availability->can($availability_id, UPDATE))->isTrue();
        $this->boolean($availability->can($availability_id, PURGE))->isTrue();

        $this->setEntity('_test_child_1', false);

        $restricted_availability = new \AppointmentAvailability();
        $this->boolean($restricted_availability->getFromDB($availability_id))->isTrue();
        $this->boolean($restricted_availability->can($availability_id, UPDATE))->isFalse();
        $this->boolean($restricted_availability->can($availability_id, PURGE))->isFalse();
    }

    public function testAvailabilityRejectsInvalidRulesAndInaccessibleTarget()
    {
        $this->login();
        $this->setEntity('_test_root_entity', true);
        $target_entity_id = getItemByTypeName('Entity', '_test_child_2', true);
        [, $appointmenttargets_id] = $this->createGroupTarget($target_entity_id, 0);

        $availability = new \AppointmentAvailability();
        $this->boolean($availability->add([
            'appointmenttargets_id' => $appointmenttargets_id,
            'day' => 8,
            'begin' => '09:00',
            'end' => '17:00',
        ]) === false)->isTrue();
        $this->hasSessionMessages(ERROR, [
            'Invalid day',
        ]);

        $this->boolean($availability->add([
            'appointmenttargets_id' => $appointmenttargets_id,
            'day' => self::MONDAY,
            'begin' => '17:00',
            'end' => '09:00',
        ]) === false)->isTrue();
        $this->hasSessionMessages(ERROR, [
            'Error in entering dates. The starting date is later than the ending date',
        ]);

        $this->setEntity('_test_child_1', false);
        $this->boolean($availability->add([
            'appointmenttargets_id' => $appointmenttargets_id,
            'day' => self::MONDAY,
            'begin' => '09:00',
            'end' => '17:00',
        ]) === false)->isTrue();
        $this->hasSessionMessages(ERROR, [
            'Appointment target is not available',
        ]);
    }

    public function testAddCompletesTargetFields()
    {
        $this->login();
        [, $appointmenttargets_id,, $user_target_id, $users_id] = $this->createGroupTarget();
        $this->addAvailability($appointmenttargets_id);

        $appointment = new \Appointment();
        $appointments_id = (int) $appointment->add([
            'name' => 'Target-backed appointment',
            'appointmenttargets_id' => $appointmenttargets_id,
            'plan' => [
                'begin' => '2030-01-07 10:00:00',
                'end' => '2030-01-07 11:00:00',
            ],
            '_disablenotif' => true,
        ]);

        $this->integer($appointments_id)->isGreaterThan(0);
        $this->boolean($appointment->getFromDB($appointments_id))->isTrue();
        $this->array($appointment->fields)
            ->integer['appointmenttargets_id']->isEqualTo($user_target_id)
            ->integer['users_id']->isEqualTo($users_id)
            ->integer['users_id_requester']->isEqualTo(\Session::getLoginUserID())
            ->integer['entities_id']->isEqualTo(getItemByTypeName('Entity', '_test_root_entity', true))
            ->integer['is_recursive']->isEqualTo(1)
            ->integer['state']->isEqualTo(\Planning::INFO)
            ->string['begin']->isEqualTo('2030-01-07 10:00:00')
            ->string['end']->isEqualTo('2030-01-07 11:00:00');
    }

    public function testIcalAttachmentKeepsAppointmentWallClockTime()
    {
        $this->login();
        [, $appointmenttargets_id] = $this->createGroupTarget();
        $this->addAvailability($appointmenttargets_id);

        $appointment = new \Appointment();
        $appointments_id = (int) $this->addAppointment($appointmenttargets_id);
        $this->integer($appointments_id)->isGreaterThan(0);
        $this->boolean($appointment->getFromDB($appointments_id))->isTrue();

        $attachment = $appointment->getIcalAttachment('new');
        $this->string($attachment['content'])->contains('DTSTART:20300107T100000');
        $this->string($attachment['content'])->contains('DTEND:20300107T110000');
        $this->string($attachment['content'])->notContains('DTSTART:20300107T100000Z');
        $this->string($attachment['content'])->notContains('DTEND:20300107T110000Z');
    }

    public function testUpdateCompletesTargetFieldsWhenRetargeting()
    {
        $this->login();
        [, $first_target_id,, $first_user_target_id] = $this->createGroupTarget();
        $this->addAvailability($first_target_id);

        $child_entity_id = getItemByTypeName('Entity', '_test_child_1', true);
        [, $second_target_id,, $second_user_target_id, $second_users_id] = $this->createGroupTarget($child_entity_id, 0);
        $this->addAvailability($second_target_id);

        $appointment = new \Appointment();
        $appointments_id = (int) $this->addAppointment($first_target_id);
        $this->integer($appointments_id)->isGreaterThan(0);

        $this->boolean($appointment->getFromDB($appointments_id))->isTrue();
        $this->integer((int) $appointment->fields['appointmenttargets_id'])->isEqualTo($first_user_target_id);
        $this->integer((int) $appointment->fields['entities_id'])
            ->isEqualTo(getItemByTypeName('Entity', '_test_root_entity', true));
        $this->integer((int) $appointment->fields['is_recursive'])->isEqualTo(1);

        $this->boolean($appointment->update([
            'id' => $appointments_id,
            'appointmenttargets_id' => $second_target_id,
            '_disablenotif' => true,
        ]))->isTrue();

        $this->boolean($appointment->getFromDB($appointments_id))->isTrue();
        $this->array($appointment->fields)
            ->integer['appointmenttargets_id']->isEqualTo($second_user_target_id)
            ->integer['users_id']->isEqualTo($second_users_id)
            ->integer['entities_id']->isEqualTo($child_entity_id)
            ->integer['is_recursive']->isEqualTo(0);
    }

    public function testRejectsOverlappingAppointmentForSameTarget()
    {
        $this->login();
        [, $appointmenttargets_id] = $this->createGroupTarget();
        $this->addAvailability($appointmenttargets_id);

        $this->integer((int) $this->addAppointment($appointmenttargets_id))->isGreaterThan(0);
        $overlap_result = $this->addAppointment(
            $appointmenttargets_id,
            '2030-01-07 10:30:00',
            '2030-01-07 11:30:00'
        );
        $this->boolean($overlap_result === false)->isTrue();
        $this->hasSessionMessages(ERROR, [
            'No technician is available for the selected timeframe',
        ]);

        $this->integer((int) $this->addAppointment(
            $appointmenttargets_id,
            '2030-01-07 11:00:00',
            '2030-01-07 12:00:00'
        ))->isGreaterThan(0);
    }

    public function testCreateOnlyUserCanOnlyViewOwnAppointmentDetails()
    {
        $this->login();
        [, $appointmenttargets_id] = $this->createGroupTarget();
        $this->addAvailability($appointmenttargets_id);

        $post_only_id = getItemByTypeName('User', 'post-only', true);
        $normal_id = getItemByTypeName('User', 'normal', true);
        $own_appointment_id = (int) $this->addAppointment(
            $appointmenttargets_id,
            '2030-01-07 10:00:00',
            '2030-01-07 11:00:00',
            ['users_id_requester' => $post_only_id]
        );
        $other_appointment_id = (int) $this->addAppointment(
            $appointmenttargets_id,
            '2030-01-07 11:00:00',
            '2030-01-07 12:00:00',
            ['users_id_requester' => $normal_id]
        );
        $this->integer($own_appointment_id)->isGreaterThan(0);
        $this->integer($other_appointment_id)->isGreaterThan(0);

        $old_appointment_right = $this->getSelfServiceAppointmentRight();

        try {
            $this->setSelfServiceAppointmentRight(CREATE);
            $this->login('post-only', 'postonly');

            $appointment = new \Appointment();
            $this->boolean($appointment->can($own_appointment_id, READ))->isTrue();

            $other_appointment = new \Appointment();
            $this->boolean($other_appointment->can($other_appointment_id, READ))->isFalse();
        } finally {
            $this->login();
            $this->setSelfServiceAppointmentRight($old_appointment_right);
        }
    }

    public function testAppointmentManagerCannotViewOrModifyOtherUserAppointmentDetails()
    {
        $this->login();
        [, $appointmenttargets_id] = $this->createGroupTarget();
        $this->addAvailability($appointmenttargets_id);

        $normal_id = getItemByTypeName('User', 'normal', true);
        $other_appointment_id = (int) $this->addAppointment(
            $appointmenttargets_id,
            '2030-01-07 10:00:00',
            '2030-01-07 11:00:00',
            ['users_id_requester' => $normal_id]
        );
        $this->integer($other_appointment_id)->isGreaterThan(0);

        $other_appointment = new \Appointment();
        $this->boolean($other_appointment->can($other_appointment_id, READ))->isFalse();
        $this->boolean($other_appointment->can($other_appointment_id, UPDATE))->isFalse();
        $this->boolean($other_appointment->getFromDB($other_appointment_id))->isTrue();
        $this->boolean($other_appointment->canPurgeItem())->isFalse();
    }

    public function testPopulatePlanningReturnsAppointmentForRequesterAndGroup()
    {
        $this->login();
        [, $appointmenttargets_id, $groups_id] = $this->createGroupTarget();
        $this->addAvailability($appointmenttargets_id);
        $appointments_id = (int) $this->addAppointment($appointmenttargets_id);

        $group_events = \Appointment::populatePlanning([
            'whogroup' => $groups_id,
            'begin' => '2030-01-07 00:00:00',
            'end' => '2030-01-08 00:00:00',
            'color' => '#ff0000',
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
            'who' => \Session::getLoginUserID(),
            'begin' => '2030-01-07 00:00:00',
            'end' => '2030-01-08 00:00:00',
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
        $appointments_id = (int) $this->addAppointment($appointmenttargets_id);

        $receiver_events = \Appointment::populatePlanning([
            'who' => $users_id,
            'begin' => '2030-01-07 00:00:00',
            'end' => '2030-01-08 00:00:00',
        ]);
        $this->array($receiver_events)->hasSize(1);
        $receiver_event = reset($receiver_events);
        $this->integer($receiver_event['appointments_id'])->isEqualTo($appointments_id);
        $this->integer($receiver_event['users_id'])->isEqualTo($users_id);

        $requester_events = \Appointment::populatePlanning([
            'who' => \Session::getLoginUserID(),
            'begin' => '2030-01-07 00:00:00',
            'end' => '2030-01-08 00:00:00',
        ]);
        $this->array($requester_events)->hasSize(1);
        $requester_event = reset($requester_events);
        $this->integer($requester_event['appointments_id'])->isEqualTo($appointments_id);
    }
}
