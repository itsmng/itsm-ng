<?php

include('../inc/includes.php');

Session::checkRight('appointment', UPDATE);

$exception = new AppointmentAvailabilityException();
if (isset($_POST['add'])) {
    $exception->check(-1, CREATE, $_POST);
    $exception->add($_POST);
} elseif (isset($_POST['update'])) {
    $exception->check($_POST['id'], UPDATE);
    $exception->update($_POST);
} elseif (isset($_POST['purge'])) {
    $exception->check($_POST['id'], PURGE);
    $exception->delete($_POST, 1);
}

Html::back();
