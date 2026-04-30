<?php

include('../inc/includes.php');

Session::checkRight('appointment', UPDATE);

$exception = new AppointmentAvailabilityException();
if (isset($_POST['add'])) {
    $exception->add($_POST);
} elseif (isset($_POST['update'])) {
    $exception->update($_POST);
} elseif (isset($_POST['purge'])) {
    $exception->delete($_POST, 1);
}

Html::back();
