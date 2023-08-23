<?php
$AJAX_INCLUDE = 1;
include ('../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

$DB->queryOrDie(
    'ALTER TABLE glpi_users ADD COLUMN IF NOT EXISTS menu_small text'
);

$menu_small = $_POST['small'];

$DB->updateOrInsert('glpi_users', ['menu_small' => $menu_small], ['id' => $_SESSION['glpiID']]);
