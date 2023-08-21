<?php
$AJAX_INCLUDE = 1;
include ('../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

file_put_contents('./test.txt', $_POST['menu']);
$DB->queryOrDie(
    'ALTER TABLE glpi_users ADD COLUMN IF NOT EXISTS menu_width longtext'
);

$menu_width = $DB->request(
    [
        'SELECT' => 'menu_width',
        'FROM'   => 'glpi_users',
        'WHERE'  => ['id' => $_SESSION["glpiID"]]
    ]
);

$menu_width = json_decode($menu_width->next()['menu_width'], true);

$menu_width[$_POST['menu']] = $_POST['width'];
$menu_width = json_encode($menu_width);

$DB->updateOrInsert('glpi_users', ['menu_width' => $menu_width], ['id' => $_SESSION['glpiID']]);
