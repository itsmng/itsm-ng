<?php

include('../inc/includes.php');

Session::checkRight('appointment', UPDATE);

$target = new AppointmentTarget();
if (isset($_POST['add'])) {
    $target->add($_POST);
} elseif (isset($_POST['update'])) {
    $target->update($_POST);
} elseif (isset($_POST['purge'])) {
    $target->delete($_POST, 1);
}

Html::back();
