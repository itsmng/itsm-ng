<?php

include('../inc/includes.php');

Session::checkRight('appointment', UPDATE);

$availability = new AppointmentAvailability();
if (isset($_POST['add'])) {
    $availability->add($_POST);
} elseif (isset($_POST['update'])) {
    $availability->update($_POST);
} elseif (isset($_POST['purge'])) {
    $availability->delete($_POST, 1);
}

Html::back();
