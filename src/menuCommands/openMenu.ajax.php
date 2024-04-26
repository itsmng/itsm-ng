<?php
$AJAX_INCLUDE = 1;
include ('../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (filter_var($_POST['clear'], FILTER_VALIDATE_BOOLEAN)) {
    $menu_open = json_encode([]);
    $DB->updateOrInsert('glpi_users', ['menu_open' => $menu_open], ['id' => $_SESSION['glpiID']]);
    die();
}

// New logic for openmenus
$menu_open = [];

// Open or close checking
if (filter_var($_POST['open'], FILTER_VALIDATE_BOOLEAN)) {
    $menu_open[] = $_POST['menu_name'];

    // Close all others openMenus
    $menu_open = json_encode([$menu_open[0]]);
} else {
    $menu_open = json_encode([]);
}

// Update
$DB->updateOrInsert('glpi_users', ['menu_open' => $menu_open], ['id' => $_SESSION['glpiID']]);
