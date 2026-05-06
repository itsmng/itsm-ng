<?php

include('../../inc/includes.php');

Session::checkRightsOr('appointment', [READ, CREATE, UPDATE]);

if (!isset($_REQUEST['action'])) {
    exit;
}

function appointment_ajax_date($value): string
{
    $time = strtotime((string)$value);
    return date('Y-m-d H:i:s', $time === false ? time() : $time);
}

function appointment_ajax_get_schedule_events($appointmenttargets_id, $start, $end, $editable_exceptions = false): array
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

    $days = Toolbox::getDaysOfWeekArray();
    $availabilities = $DB->request([
       'FROM'  => AppointmentAvailability::getTable(),
       'WHERE' => ['appointmenttargets_id' => $appointmenttargets_id],
       'ORDER' => ['day', 'begin'],
    ]);
    foreach ($availabilities as $row) {
        $events[] = [
           'id'         => 'availability-' . $row['id'],
           'title'      => __('Office hours'),
           'daysOfWeek' => [(int)$row['day']],
           'startTime'  => $row['begin'],
           'endTime'    => $row['end'],
           'rendering'  => 'background',
           'classNames' => ['appointment-availability-background'],
           'extendedProps' => [
              'type'    => 'availability',
              'day'     => (int)$row['day'],
              'begin'   => $row['begin'],
              'end'     => $row['end'],
              'comment' => $days[$row['day']] . ' ' . substr((string)$row['begin'], 0, 5) . ' - ' . substr((string)$row['end'], 0, 5),
           ],
        ];
    }

    $exceptions = $DB->request([
       'FROM'  => AppointmentAvailabilityException::getTable(),
       'WHERE' => [
          'appointmenttargets_id' => $appointmenttargets_id,
          'end'                   => ['>', $start],
          'begin'                 => ['<', $end],
       ],
       'ORDER' => 'begin',
    ]);
    $exception = new AppointmentAvailabilityException();
    foreach ($exceptions as $row) {
        $can_edit = $editable_exceptions && $exception->getFromDB($row['id']) && $exception->canUpdateItem();
        $is_available = (int)$row['is_available'] === 1;
        $events[] = [
           'id'               => 'exception-' . $row['id'],
           'title'            => $is_available ? __('Available') : __('Unavailable'),
           'start'            => $row['begin'],
           'end'              => $row['end'],
           'editable'         => $can_edit,
           'durationEditable' => $can_edit,
           'startEditable'    => $can_edit,
           'classNames'       => [$is_available ? 'appointment-exception-open' : 'appointment-exception-closed'],
           'extendedProps'    => [
              'type'         => 'exception',
              'exception_id' => (int)$row['id'],
              'is_available' => $is_available,
              'comment'      => $row['comment'],
              'can_edit'     => $can_edit,
           ],
        ];
    }

    return $events;
}

if ($_REQUEST['action'] === 'get_events') {
    global $DB, $CFG_GLPI;

    header('Content-Type: application/json; charset=UTF-8');
    $appointmenttargets_id = (int)($_REQUEST['appointmenttargets_id'] ?? 0);
    $start = appointment_ajax_date($_REQUEST['start'] ?? null);
    $end = appointment_ajax_date($_REQUEST['end'] ?? null);

    $where = [
       'glpi_appointments.end'        => ['>', $start],
       'glpi_appointments.begin'      => ['<', $end],
       'glpi_appointments.is_deleted' => 0,
    ];
    if ($appointmenttargets_id > 0) {
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
                'glpi_appointments'       => 'appointmenttargets_id',
                'glpi_appointmenttargets' => 'id',
             ],
          ],
       ],
       'WHERE' => $where + getEntitiesRestrictCriteria('glpi_appointments', 'entities_id', $_SESSION['glpiactiveentities']),
       'ORDER' => 'glpi_appointments.begin',
    ]);

    $events = appointment_ajax_get_schedule_events($appointmenttargets_id, $start, $end, false);
    $appointment = new Appointment();
    foreach ($iterator as $row) {
        $target_label = AppointmentTarget::getTargetLabel($row);
        $requester = getUserName($row['users_id_requester']);
        $can_edit = $appointment->getFromDB($row['id']) && $appointment->canUpdateItem();
        $can_view_details = Session::haveRight('appointment', UPDATE)
            || (int)$row['users_id_requester'] === (int)Session::getLoginUserID();
        $appointment_title = trim((string)($row['name'] ?? ''));
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
           'id'               => $row['id'],
           'title'            => $title,
           'start'            => $row['begin'],
           'end'              => $row['end'],
           'editable'         => $can_edit,
           'durationEditable' => $can_edit,
           'startEditable'    => $can_edit,
           'url'              => $can_edit ? $CFG_GLPI['root_doc'] . '/ajax/v2/appointment.php?action=get_form&id=' . $row['id'] : '',
           'classNames'       => ['appointment-calendar-event'],
           'extendedProps'    => [
              'type'                   => 'appointment',
              'comment'                => $can_view_details ? $row['text'] : '',
              'requester'              => $can_view_details ? $requester : '',
              'target'                 => $can_view_details ? $target_label : '',
              'appointmenttargets_id'  => $row['appointmenttargets_id'],
              'can_edit'               => $can_edit,
              'can_view_details'       => $can_view_details,
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

    $appointmenttargets_id = (int)($_REQUEST['appointmenttargets_id'] ?? 0);
    $start = appointment_ajax_date($_REQUEST['start'] ?? null);
    $end = appointment_ajax_date($_REQUEST['end'] ?? null);

    if ($appointmenttargets_id <= 0) {
        echo json_encode([]);
        exit;
    }

    $events = appointment_ajax_get_schedule_events($appointmenttargets_id, $start, $end, true);

    $appointments = $DB->request([
       'SELECT' => ['glpi_appointments.*'],
       'FROM'   => 'glpi_appointments',
       'WHERE'  => [
          'appointmenttargets_id' => $appointmenttargets_id,
          'end'                   => ['>', $start],
          'begin'                 => ['<', $end],
          'is_deleted'            => 0,
       ] + getEntitiesRestrictCriteria('glpi_appointments', 'entities_id', $_SESSION['glpiactiveentities']),
       'ORDER' => 'begin',
    ]);
    $appointment = new Appointment();
    foreach ($appointments as $row) {
        $requester = getUserName($row['users_id_requester']);
        $can_edit = $appointment->getFromDB($row['id']) && $appointment->canUpdateItem();
        $events[] = [
           'id'               => 'appointment-' . $row['id'],
           'title'            => sprintf(__('Booked: %s'), $requester),
           'start'            => $row['begin'],
           'end'              => $row['end'],
           'editable'         => false,
           'durationEditable' => false,
           'startEditable'    => false,
           'classNames'       => ['appointment-booked-event'],
           'extendedProps'    => [
              'type'           => 'appointment',
              'appointment_id' => (int)$row['id'],
              'comment'        => $row['text'],
              'can_edit'       => $can_edit,
           ],
        ];
    }

    echo json_encode($events);
    exit;
}

if ($_REQUEST['action'] === 'get_exception_form') {
    Html::header_nocache();
    header('Content-Type: text/html; charset=UTF-8');
    if (!Session::haveRight('appointment', UPDATE)) {
        echo "<div class='appointment-calendar-form-error'>" . __("You don't have permission to perform this action.") . "</div>";
        exit;
    }

    $exception = new AppointmentAvailabilityException();
    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id > 0) {
        $exception->showForm($id, []);
    } else {
        $exception->showForm('', [
           'appointmenttargets_id' => (int)($_REQUEST['appointmenttargets_id'] ?? 0),
           'begin'                 => appointment_ajax_date($_REQUEST['begin'] ?? null),
           'end'                   => appointment_ajax_date($_REQUEST['end'] ?? null),
           'is_available'          => (int)($_REQUEST['is_available'] ?? 0),
        ]);
    }
    Html::ajaxFooter();
    exit;
}

if ($_REQUEST['action'] === 'get_form') {
    Html::header_nocache();
    header('Content-Type: text/html; charset=UTF-8');

    $appointment = new Appointment();
    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id > 0) {
        $appointment->showForm($id, []);
    } else {
        $appointment->showForm('', [
           'appointmenttargets_id' => (int)($_REQUEST['appointmenttargets_id'] ?? 0),
           'begin'                 => appointment_ajax_date($_REQUEST['begin'] ?? null),
           'end'                   => appointment_ajax_date($_REQUEST['end'] ?? null),
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
        if ($appointment->getFromDB((int)$_POST['id']) && $appointment->canPurgeItem()) {
            $success = (bool)$appointment->delete(['id' => (int)$_POST['id']], 1);
        }
    } elseif (isset($_POST['update']) && !empty($_POST['id'])) {
        if ($appointment->getFromDB((int)$_POST['id']) && $appointment->canUpdateItem()) {
            $success = (bool)$appointment->update($_POST);
        }
    } elseif (isset($_POST['add'])) {
        $success = (bool)$appointment->add($_POST);
    }
    $output = ob_get_clean();

    echo json_encode([
       'success' => $success,
       'html'    => $output,
    ]);
    exit;
}

if ($_REQUEST['action'] === 'save_exception') {
    Session::checkRight('appointment', UPDATE);
    header('Content-Type: application/json; charset=UTF-8');

    $exception = new AppointmentAvailabilityException();
    $success = false;
    ob_start();
    if (isset($_POST['purge']) && !empty($_POST['id'])) {
        if ($exception->getFromDB((int)$_POST['id']) && $exception->canPurgeItem()) {
            $success = (bool)$exception->delete(['id' => (int)$_POST['id']], 1);
        } else {
            echo "<div class='appointment-calendar-form-error'>" . __("You don't have permission to perform this action.") . "</div>";
        }
    } elseif (isset($_POST['update']) && !empty($_POST['id'])) {
        if ($exception->getFromDB((int)$_POST['id']) && $exception->canUpdateItem()) {
            $success = (bool)$exception->update($_POST);
        } else {
            echo "<div class='appointment-calendar-form-error'>" . __("You don't have permission to perform this action.") . "</div>";
        }
    } elseif (isset($_POST['add'])) {
        $success = (bool)$exception->add($_POST);
    }
    $output = ob_get_clean();

    echo json_encode([
       'success' => $success,
       'html'    => $output,
    ]);
    exit;
}

if ($_REQUEST['action'] === 'update_times') {
    header('Content-Type: application/json; charset=UTF-8');

    $appointment = new Appointment();
    $success = false;
    ob_start();
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0 && $appointment->getFromDB($id) && $appointment->canUpdateItem()) {
        $success = (bool)$appointment->update([
           'id'   => $id,
           'plan' => [
              'begin' => appointment_ajax_date($_POST['begin'] ?? null),
              'end'   => appointment_ajax_date($_POST['end'] ?? null),
           ],
        ]);
    }
    $output = ob_get_clean();

    echo json_encode([
       'success' => $success,
       'html'    => $output,
    ]);
    exit;
}

if ($_REQUEST['action'] === 'update_exception_times') {
    Session::checkRight('appointment', UPDATE);
    header('Content-Type: application/json; charset=UTF-8');

    $exception = new AppointmentAvailabilityException();
    $success = false;
    ob_start();
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0 && $exception->getFromDB($id) && $exception->canUpdateItem()) {
        $success = (bool)$exception->update([
           'id'   => $id,
           'plan' => [
              'begin' => appointment_ajax_date($_POST['begin'] ?? null),
              'end'   => appointment_ajax_date($_POST['end'] ?? null),
           ],
        ]);
    }
    $output = ob_get_clean();

    echo json_encode([
       'success' => $success,
       'html'    => $output,
    ]);
    exit;
}
