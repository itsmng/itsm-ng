<?php

include('../inc/includes.php');

Session::checkRight('appointment', CREATE);

$appointment = new Appointment();
if (isset($_POST['add'])) {
    $appointment->check(-1, CREATE, $_POST);
    $appointment->add($_POST);
    Html::back();
} elseif (isset($_POST['update'])) {
    $appointment->check($_POST['id'], UPDATE);
    $appointment->update($_POST);
    Html::back();
} elseif (isset($_POST['purge'])) {
    $appointment->check($_POST['id'], PURGE);
    $appointment->delete($_POST, 1);
    Html::redirect($appointment->getSearchURL());
}

Html::header(Appointment::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], 'tools', 'appointmenttarget');
$appointment->display($_GET);
Html::footer();
