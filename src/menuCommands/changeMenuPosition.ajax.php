<?php
$AJAX_INCLUDE = 1;
include ('../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

$menu_position = $_POST['position'];

$DB->updateOrInsert('glpi_users', ['menu_position' => $menu_position], ['id' => $_SESSION['glpiID']]);
