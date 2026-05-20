<?php

include('../inc/includes.php');

Session::checkRight('appointment', UPDATE);

$target = new AppointmentTarget();
if (isset($_POST['add'])) {
    $target->check(-1, CREATE, $_POST);
    $target->add($_POST);
} elseif (isset($_POST['update'])) {
    $target->check($_POST['id'], UPDATE);
    $target->update($_POST);
} elseif (isset($_POST['purge'])) {
    $target->check($_POST['id'], PURGE);
    $target->delete($_POST, 1);
}

Html::back();
