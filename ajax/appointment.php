<?php

include('../inc/includes.php');

Session::checkRight('appointment', CREATE);

if (!isset($_REQUEST['action'])) {
    exit;
}

function appointment_ajax_date($value)
{
    $time = strtotime((string)$value);
    return date('Y-m-d H:i:s', $time === false ? time() : $time);
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

    $events = [];
    $appointment = new Appointment();
    foreach ($iterator as $row) {
        $target_label = AppointmentTarget::getTargetLabel($row);
        $requester = getUserName($row['users_id_requester']);
        $title = $appointmenttargets_id > 0 ? $requester : sprintf(__('%1$s - %2$s'), $target_label, $requester);
        $can_edit = $appointment->getFromDB($row['id']) && $appointment->canUpdateItem();
        $events[] = [
           'id'               => $row['id'],
           'title'            => $title,
           'start'            => $row['begin'],
           'end'              => $row['end'],
           'editable'         => $can_edit,
           'durationEditable' => $can_edit,
           'startEditable'    => $can_edit,
           'url'              => $can_edit ? $CFG_GLPI['root_doc'] . '/ajax/appointment.php?action=get_form&id=' . $row['id'] : '',
           'classNames'       => ['appointment-calendar-event'],
           'extendedProps'    => [
              'comment'                => $row['text'],
              'requester'              => $requester,
              'target'                 => $target_label,
              'appointmenttargets_id'  => $row['appointmenttargets_id'],
              'can_edit'               => $can_edit,
           ],
        ];
    }

    echo json_encode($events);
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
        $success = (bool)$appointment->update($_POST);
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
