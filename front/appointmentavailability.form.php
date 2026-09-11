<?php

include('../inc/includes.php');

Session::checkRight('appointment', UPDATE);

$availability = new AppointmentAvailability();
if (isset($_POST['add'])) {
    $availability->check(-1, CREATE, $_POST);
    $availability->add($_POST);
} elseif (isset($_POST['update'])) {
    $availability->check($_POST['id'], UPDATE);
    $availability->update($_POST);
} elseif (isset($_POST['purge'])) {
    $availability->check($_POST['id'], PURGE);
    $availability->delete($_POST, 1);
}

Html::back();
