<?php

if (!defined('GLPI_ROOT')) {
    include ('../inc/includes.php');
}

Html::popHeader(__("Setup"), $_SERVER["PHP_SELF"]);

Session::checkRight("accessibility", READ);

$accessdisplay = new Accessibility();

if (isset($_POST["update"])) {

}
