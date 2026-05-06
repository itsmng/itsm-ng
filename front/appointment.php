<?php

include('../inc/includes.php');

Session::checkRightsOr('appointment', [READ, CREATE, UPDATE]);

Html::header(Appointment::getTypeName(Session::getPluralNumber()), $_SERVER['PHP_SELF'], 'tools', 'appointmenttarget');

if (!isset($_GET['appointmenttargets_id'])) {
    Appointment::showTargetList();
} else {
    Appointment::showCalendar((int)$_GET['appointmenttargets_id']);
}

Html::footer();
