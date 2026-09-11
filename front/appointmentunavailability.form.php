<?php

include('../inc/includes.php');

Session::checkRight('appointment', UPDATE);

$unavailability = new AppointmentUnavailability();
if (isset($_POST['add'])) {
    $unavailability->check(-1, CREATE, $_POST);
    $unavailability->add($_POST);
} elseif (isset($_POST['update'])) {
    $unavailability->check($_POST['id'], UPDATE);
    $unavailability->update($_POST);
} elseif (isset($_POST['purge'])) {
    $unavailability->check($_POST['id'], PURGE);
    $unavailability->delete($_POST, 1);
}

Html::back();
