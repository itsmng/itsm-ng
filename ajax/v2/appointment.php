<?php

include('../../inc/includes.php');

Session::checkRightsOr('appointment', [READ, CREATE, UPDATE]);

if (!isset($_REQUEST['action'])) {
    exit;
}

function appointment_ajax_date($value): string
{
    $time = strtotime((string) $value);
    return date('Y-m-d H:i:s', $time === false ? time() : $time);
}

function appointment_ajax_is_target_booked($appointmenttargets_id, $begin, $end): bool
{
    global $DB;

    $row = $DB->request([
        'COUNT' => 'cpt',
        'FROM' => Appointment::getTable(),
        'WHERE' => [
            'appointmenttargets_id' => (int) $appointmenttargets_id,
            'end' => ['>', $begin],
            'begin' => ['<', $end],
            'is_deleted' => 0,
        ],
    ])->next();

    return (int) ($row['cpt'] ?? 0) > 0;
}

function appointment_ajax_get_group_schedule_events(array $group_target, $start, $end): array
{
    $events = [];
    $member_targets = AppointmentTarget::getGroupMemberTargetRows($group_target);
    if (count($member_targets) === 0) {
        return $events;
    }

    $start_ts = strtotime((string) $start);
    $end_ts = strtotime((string) $end);
    if ($start_ts === false || $end_ts === false || $start_ts >= $end_ts) {
        return $events;
    }

    $slot_seconds = 15 * 60;
    $range_start = null;
    $range_end = null;
    $event_id = 0;

    for ($slot_start = $start_ts; $slot_start < $end_ts; $slot_start += $slot_seconds) {
        $slot_end = min($slot_start + $slot_seconds, $end_ts);
        $slot_begin = date('Y-m-d H:i:s', $slot_start);
        $slot_finish = date('Y-m-d H:i:s', $slot_end);
        $is_available = false;

        foreach ($member_targets as $member_target) {
            if (
                AppointmentAvailability::isAvailable($member_target['id'], $slot_begin, $slot_finish)
                && !appointment_ajax_is_target_booked($member_target['id'], $slot_begin, $slot_finish)
            ) {
                $is_available = true;
                break;
            }
        }

        if ($is_available) {
            if ($range_start === null) {
                $range_start = $slot_start;
            }
            $range_end = $slot_end;
            continue;
        }

        if ($range_start !== null) {
            $events[] = appointment_ajax_group_availability_event(++$event_id, $range_start, $range_end);
            $range_start = null;
            $range_end = null;
        }
    }

    if ($range_start !== null) {
        $events[] = appointment_ajax_group_availability_event(++$event_id, $range_start, $range_end);
    }

    return $events;
}

function appointment_ajax_group_availability_event($id, $begin_ts, $end_ts): array
{
    return [
        'id' => 'group-availability-' . $id,
        'title' => __('Office hours'),
        'start' => date('Y-m-d H:i:s', $begin_ts),
        'end' => date('Y-m-d H:i:s', $end_ts),
        'rendering' => 'background',
        'classNames' => ['appointment-availability-background'],
        'extendedProps' => [
            'type' => 'availability',
            'day' => (int) date('w', $begin_ts),
            'begin' => date('H:i:s', $begin_ts),
            'end' => date('H:i:s', $end_ts),
        ],
    ];
}

function appointment_ajax_select2_result($id, $text): array
{
    return [
        'id' => (string) $id,
        'text' => $text,
    ];
}

function appointment_ajax_get_schedule_events($appointmenttargets_id, $start, $end, $editable_unavailabilities = false): array
{
    global $DB;

    $events = [];
    if ($appointmenttargets_id <= 0) {
        return $events;
    }

    $target = new AppointmentTarget();
    if (!$target->getFromDB($appointmenttargets_id)) {
        return $events;
    }
    if (!$target->canAccessEntity()) {
        return $events;
    }
    if ($target->fields['itemtype'] === 'Group' && !$editable_unavailabilities) {
        return appointment_ajax_get_group_schedule_events($target->fields, $start, $end);
    }

    $days = Toolbox::getDaysOfWeekArray();
    $availabilities = $DB->request([
        'FROM' => AppointmentAvailability::getTable(),
        'WHERE' => ['appointmenttargets_id' => $appointmenttargets_id],
        'ORDER' => ['day', 'begin'],
    ]);
    foreach ($availabilities as $row) {
        $events[] = [
            'id' => 'availability-' . $row['id'],
            'title' => __('Office hours'),
            'daysOfWeek' => [(int) $row['day']],
            'startTime' => $row['begin'],
            'endTime' => $row['end'],
            'rendering' => 'background',
            'classNames' => ['appointment-availability-background'],
            'extendedProps' => [
                'type' => 'availability',
                'day' => (int) $row['day'],
                'begin' => $row['begin'],
                'end' => $row['end'],
                'comment' => $days[$row['day']] . ' ' . substr((string) $row['begin'], 0, 5) . ' - ' . substr((string) $row['end'], 0, 5),
            ],
        ];
    }

    $unavailabilities = $DB->request([
        'FROM' => AppointmentUnavailability::getTable(),
        'WHERE' => [
            'appointmenttargets_id' => $appointmenttargets_id,
            'end' => ['>', $start],
            'begin' => ['<', $end],
        ],
        'ORDER' => 'begin',
    ]);
    $unavailability = new AppointmentUnavailability();
    foreach ($unavailabilities as $row) {
        $can_edit = $editable_unavailabilities && $unavailability->getFromDB($row['id']) && $unavailability->canUpdateItem();
        $is_available = (int) $row['is_available'] === 1;
        $events[] = [
            'id' => 'unavailability-' . $row['id'],
            'title' => $is_available ? __('Available') : __('Unavailable'),
            'start' => $row['begin'],
            'end' => $row['end'],
            'editable' => $can_edit,
            'durationEditable' => $can_edit,
            'startEditable' => $can_edit,
            'classNames' => [$is_available ? 'appointment-unavailability-open' : 'appointment-unavailability-closed'],
            'extendedProps' => [
                'type' => 'unavailability',
                'unavailability_id' => (int) $row['id'],
                'is_available' => $is_available,
                'comment' => $row['comment'],
                'can_edit' => $can_edit,
            ],
        ];
    }

    return $events;
}

if ($_REQUEST['action'] === 'get_group_member_targets') {
    header('Content-Type: application/json; charset=UTF-8');

    $appointmenttargets_id = (int) ($_REQUEST['appointmenttargets_id'] ?? 0);
    $search = trim((string) ($_REQUEST['searchText'] ?? ''));
    $page = max(1, (int) ($_REQUEST['page'] ?? 1));
    $limit = max(1, min(100, (int) ($_REQUEST['page_limit'] ?? 50)));

    $target = new AppointmentTarget();
    if (
        $appointmenttargets_id <= 0
        || !$target->getFromDB($appointmenttargets_id)
        || $target->fields['itemtype'] !== 'Group'
        || !$target->canAccessEntity()
    ) {
        echo json_encode(['results' => [], 'pagination' => ['more' => false]]);
        exit;
    }

    $results = [];
    $group_label = __('Book with group');
    $include_group_option = $page === 1
        && (
            $search === ''
            || stripos($group_label, $search) !== false
            || stripos(AppointmentTarget::getTargetName($target->fields), $search) !== false
        );
    if ($include_group_option) {
        $results[] = appointment_ajax_select2_result($appointmenttargets_id, $group_label);
    }

    $search_result = AppointmentTarget::searchGroupMemberTargetRows($target->fields, $search, $page, $limit);
    foreach ($search_result['rows'] as $row) {
        $results[] = appointment_ajax_select2_result((int) $row['id'], AppointmentTarget::getTargetName($row));
    }

    echo json_encode([
        'results' => $results,
        'pagination' => [
            'more' => ($page * $limit) < (int) $search_result['total'],
        ],
    ]);
    exit;
}

if ($_REQUEST['action'] === 'get_events') {
    global $DB, $CFG_GLPI;

    header('Content-Type: application/json; charset=UTF-8');
    $appointmenttargets_id = (int) ($_REQUEST['appointmenttargets_id'] ?? 0);
    $start = appointment_ajax_date($_REQUEST['start'] ?? null);
    $end = appointment_ajax_date($_REQUEST['end'] ?? null);
    $selected_target = null;
    if ($appointmenttargets_id > 0) {
        $selected_target = new AppointmentTarget();
        if (!$selected_target->getFromDB($appointmenttargets_id) || !$selected_target->canAccessEntity()) {
            echo json_encode([]);
            exit;
        }
    }

    $where = [
        'glpi_appointments.end' => ['>', $start],
        'glpi_appointments.begin' => ['<', $end],
        'glpi_appointments.is_deleted' => 0,
    ];
    $is_group_aggregate = $selected_target !== null && $selected_target->fields['itemtype'] === 'Group';
    if ($is_group_aggregate) {
        $users = [];
        foreach (Group_User::getGroupUsers((int) $selected_target->fields['items_id']) as $user) {
            $users[] = (int) $user['id'];
        }
        if (count($users) === 0) {
            echo json_encode(appointment_ajax_get_schedule_events($appointmenttargets_id, $start, $end, false));
            exit;
        }
        $where['glpi_appointments.users_id'] = $users;
        $where['glpi_appointments.users_id_requester'] = (int) Session::getLoginUserID();
    } elseif ($appointmenttargets_id > 0) {
        $where['glpi_appointments.appointmenttargets_id'] = $appointmenttargets_id;
    }

    $iterator = $DB->request([
        'SELECT' => [
            'glpi_appointments.*',
            'glpi_appointmenttargets.itemtype',
            'glpi_appointmenttargets.items_id',
        ],
        'FROM' => 'glpi_appointments',
        'INNER JOIN' => [
            'glpi_appointmenttargets' => [
                'ON' => [
                    'glpi_appointments' => 'appointmenttargets_id',
                    'glpi_appointmenttargets' => 'id',
                ],
            ],
        ],
        'WHERE' => $where + getEntitiesRestrictCriteria('glpi_appointments', 'entities_id', $_SESSION['glpiactiveentities'], true),
        'ORDER' => 'glpi_appointments.begin',
    ]);

    $events = appointment_ajax_get_schedule_events($appointmenttargets_id, $start, $end, false);
    $appointment = new Appointment();
    foreach ($iterator as $row) {
        $target_label = AppointmentTarget::getTargetLabel($row);
        $requester = getUserName($row['users_id_requester']);
        $appointment_loaded = $appointment->getFromDB($row['id']);
        $can_view_details = $appointment_loaded && $appointment->canViewItem();
        $can_edit = $appointment_loaded && $appointment->canUpdateItem();
        $appointment_title = trim((string) ($row['name'] ?? ''));
        if ($appointment_title === '') {
            $appointment_title = Appointment::getTypeName(1);
        }
        if ($can_view_details) {
            $requester_title = sprintf(__('%1$s - %2$s'), $requester, $appointment_title);
            $title = $appointmenttargets_id > 0
                ? $requester_title
                : sprintf(__('%1$s - %2$s'), $target_label, $requester_title);
        } else {
            $title = __('Booked');
        }
        $events[] = [
            'id' => $row['id'],
            'title' => $title,
            'start' => $row['begin'],
            'end' => $row['end'],
            'editable' => $can_edit,
            'durationEditable' => $can_edit,
            'startEditable' => $can_edit,
            'url' => $can_view_details ? $CFG_GLPI['root_doc'] . '/ajax/v2/appointment.php?action=get_form&id=' . $row['id'] : '',
            'classNames' => ['appointment-calendar-event'],
            'extendedProps' => [
                'type' => 'appointment',
                'comment' => $can_view_details ? $row['text'] : '',
                'requester' => $can_view_details ? $requester : '',
                'target' => $can_view_details ? $target_label : '',
                'appointmenttargets_id' => $row['appointmenttargets_id'],
                'can_edit' => $can_edit,
                'can_view_details' => $can_view_details,
            ],
        ];
    }

    echo json_encode($events);
    exit;
}

if ($_REQUEST['action'] === 'get_target_events') {
    global $DB;

    Session::checkRight('appointment', UPDATE);
    header('Content-Type: application/json; charset=UTF-8');

    $appointmenttargets_id = (int) ($_REQUEST['appointmenttargets_id'] ?? 0);
    $start = appointment_ajax_date($_REQUEST['start'] ?? null);
    $end = appointment_ajax_date($_REQUEST['end'] ?? null);

    if ($appointmenttargets_id <= 0) {
        echo json_encode([]);
        exit;
    }

    $events = appointment_ajax_get_schedule_events($appointmenttargets_id, $start, $end, true);

    $appointments = $DB->request([
        'SELECT' => ['glpi_appointments.*'],
        'FROM' => 'glpi_appointments',
        'WHERE' => [
            'appointmenttargets_id' => $appointmenttargets_id,
            'end' => ['>', $start],
            'begin' => ['<', $end],
            'is_deleted' => 0,
        ] + getEntitiesRestrictCriteria('glpi_appointments', 'entities_id', $_SESSION['glpiactiveentities'], true),
        'ORDER' => 'begin',
    ]);
    $appointment = new Appointment();
    foreach ($appointments as $row) {
        $requester = getUserName($row['users_id_requester']);
        $appointment_loaded = $appointment->getFromDB($row['id']);
        $can_view_details = $appointment_loaded && $appointment->canViewItem();
        $can_edit = $appointment_loaded && $appointment->canUpdateItem();
        $events[] = [
            'id' => 'appointment-' . $row['id'],
            'title' => $can_view_details ? sprintf(__('Booked: %s'), $requester) : __('Booked'),
            'start' => $row['begin'],
            'end' => $row['end'],
            'editable' => false,
            'durationEditable' => false,
            'startEditable' => false,
            'classNames' => ['appointment-booked-event'],
            'extendedProps' => [
                'type' => 'appointment',
                'appointment_id' => (int) $row['id'],
                'comment' => $can_view_details ? $row['text'] : '',
                'can_edit' => $can_edit,
                'can_view_details' => $can_view_details,
            ],
        ];
    }

    echo json_encode($events);
    exit;
}

if ($_REQUEST['action'] === 'get_unavailability_form') {
    Html::header_nocache();
    header('Content-Type: text/html; charset=UTF-8');
    if (!Session::haveRight('appointment', UPDATE)) {
        echo "<div class='appointment-calendar-form-error'>" . __("You don't have permission to perform this action.") . "</div>";
        exit;
    }

    $unavailability = new AppointmentUnavailability();
    $id = (int) ($_REQUEST['id'] ?? 0);
    if ($id > 0) {
        $unavailability->showForm($id, []);
    } else {
        $unavailability->showForm('', [
            'appointmenttargets_id' => (int) ($_REQUEST['appointmenttargets_id'] ?? 0),
            'begin' => appointment_ajax_date($_REQUEST['begin'] ?? null),
            'end' => appointment_ajax_date($_REQUEST['end'] ?? null),
            'is_available' => (int) ($_REQUEST['is_available'] ?? 0),
        ]);
    }
    Html::ajaxFooter();
    exit;
}

if ($_REQUEST['action'] === 'get_form') {
    Html::header_nocache();
    header('Content-Type: text/html; charset=UTF-8');

    $appointment = new Appointment();
    $id = (int) ($_REQUEST['id'] ?? 0);
    if ($id > 0) {
        if (
            $appointment->getFromDB($id)
            && $appointment->can($id, READ)
        ) {
            $appointment->showForm($id, []);
        } else {
            echo "<div class='appointment-calendar-form-error'>" . __("You don't have permission to perform this action.") . "</div>";
        }
    } else {
        $appointment->showForm('', [
            'appointmenttargets_id' => (int) ($_REQUEST['appointmenttargets_id'] ?? 0),
            'begin' => appointment_ajax_date($_REQUEST['begin'] ?? null),
            'end' => appointment_ajax_date($_REQUEST['end'] ?? null),
        ]);
    }
    Html::ajaxFooter();
    exit;
}

if ($_REQUEST['action'] === 'save') {
    header('Content-Type: application/json; charset=UTF-8');

    $appointment = new Appointment();
    $success = false;
    ob_start();
    if (isset($_POST['purge']) && !empty($_POST['id'])) {
        $id = (int) $_POST['id'];
        if ($appointment->getFromDB($id) && $appointment->can($id, READ) && $appointment->canPurgeItem()) {
            $success = (bool) $appointment->delete(['id' => (int) $_POST['id']], 1);
        }
    } elseif (isset($_POST['update']) && !empty($_POST['id'])) {
        $id = (int) $_POST['id'];
        if ($appointment->getFromDB($id) && $appointment->can($id, UPDATE)) {
            $success = (bool) $appointment->update($_POST);
        }
    } elseif (isset($_POST['add'])) {
        if ($appointment->can(-1, CREATE, $_POST)) {
            $success = (bool) $appointment->add($_POST);
        }
    }
    $output = ob_get_clean();

    echo json_encode([
        'success' => $success,
        'html' => $output,
    ]);
    exit;
}

if ($_REQUEST['action'] === 'save_unavailability') {
    Session::checkRight('appointment', UPDATE);
    header('Content-Type: application/json; charset=UTF-8');

    $unavailability = new AppointmentUnavailability();
    $success = false;
    ob_start();
    if (isset($_POST['purge']) && !empty($_POST['id'])) {
        if ($unavailability->getFromDB((int) $_POST['id']) && $unavailability->canPurgeItem()) {
            $success = (bool) $unavailability->delete(['id' => (int) $_POST['id']], 1);
        } else {
            echo "<div class='appointment-calendar-form-error'>" . __("You don't have permission to perform this action.") . "</div>";
        }
    } elseif (isset($_POST['update']) && !empty($_POST['id'])) {
        if ($unavailability->getFromDB((int) $_POST['id']) && $unavailability->canUpdateItem()) {
            $success = (bool) $unavailability->update($_POST);
        } else {
            echo "<div class='appointment-calendar-form-error'>" . __("You don't have permission to perform this action.") . "</div>";
        }
    } elseif (isset($_POST['add'])) {
        $success = (bool) $unavailability->add($_POST);
    }
    $output = ob_get_clean();

    echo json_encode([
        'success' => $success,
        'html' => $output,
    ]);
    exit;
}

if ($_REQUEST['action'] === 'update_times') {
    header('Content-Type: application/json; charset=UTF-8');

    $appointment = new Appointment();
    $success = false;
    ob_start();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0 && $appointment->getFromDB($id) && $appointment->canUpdateItem()) {
        $success = (bool) $appointment->update([
            'id' => $id,
            'plan' => [
                'begin' => appointment_ajax_date($_POST['begin'] ?? null),
                'end' => appointment_ajax_date($_POST['end'] ?? null),
            ],
        ]);
    }
    $output = ob_get_clean();

    echo json_encode([
        'success' => $success,
        'html' => $output,
    ]);
    exit;
}

if ($_REQUEST['action'] === 'update_unavailability_times') {
    Session::checkRight('appointment', UPDATE);
    header('Content-Type: application/json; charset=UTF-8');

    $unavailability = new AppointmentUnavailability();
    $success = false;
    ob_start();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0 && $unavailability->getFromDB($id) && $unavailability->canUpdateItem()) {
        $success = (bool) $unavailability->update([
            'id' => $id,
            'plan' => [
                'begin' => appointment_ajax_date($_POST['begin'] ?? null),
                'end' => appointment_ajax_date($_POST['end'] ?? null),
            ],
        ]);
    }
    $output = ob_get_clean();

    echo json_encode([
        'success' => $success,
        'html' => $output,
    ]);
    exit;
}
