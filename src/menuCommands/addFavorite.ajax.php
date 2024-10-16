<?php

$AJAX_INCLUDE = 1;
include('../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

$favorites = $DB->request(
    [
        'SELECT' => 'menu_favorite',
        'FROM'   => 'glpi_users',
        'WHERE'  => ['id' => $_SESSION["glpiID"]]
    ]
);

$favorites = json_decode($favorites->next()['menu_favorite'], true);
if (is_null($favorites)) {
    $favorites = [];
}

if (filter_var($_POST['remove'], FILTER_VALIDATE_BOOLEAN)) {
    $key = array_search($_POST['submenu_name'], $favorites[$_POST['menu_name']]);
    unset($favorites[$_POST['menu_name']][$key]);
    $favorites[$_POST['menu_name']] = array_values($favorites[$_POST['menu_name']]); //reindex key value
    if (empty($favorites[$_POST['menu_name']])) {
        unset($favorites[$_POST['menu_name']]);
    }
} else {
    $favorites[$_POST['menu_name']][] = $_POST['submenu_name'];
}

$favorites = json_encode($favorites);

$DB->updateOrInsert('glpi_users', ['menu_favorite' => $favorites], ['id' => $_SESSION['glpiID']]);
