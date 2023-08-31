<?php
$AJAX_INCLUDE = 1;
include ('../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

$DB->queryOrDie(
    'ALTER TABLE glpi_users ADD COLUMN IF NOT EXISTS menu_position text'
);

$menu_position = $_POST['position'];

$DB->updateOrInsert('glpi_users', ['menu_position' => $menu_position], ['id' => $_SESSION['glpiID']]);
