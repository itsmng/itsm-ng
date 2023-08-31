<?php
$AJAX_INCLUDE = 1;
include ('../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

$DB->queryOrDie(
    'ALTER TABLE glpi_users ADD COLUMN IF NOT EXISTS menu_favorite_on text'
);

$menu_favorite_on = filter_var($_POST['menu_favorite_on'], FILTER_VALIDATE_BOOLEAN);

$DB->updateOrInsert('glpi_users', ['menu_favorite_on' => $menu_favorite_on], ['id' => $_SESSION['glpiID']]);
