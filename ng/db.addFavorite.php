<?php
if (isset($_POST['menu_name']))
file_put_contents('./test.txt', $_POST);
$AJAX_INCLUDE = 1;
include ('../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

$DB->queryOrDie(
    'ALTER TABLE glpi_users ADD COLUMN IF NOT EXISTS menu_favorite longtext'
);
$favorites = $DB->request(
    [
        'SELECT' => 'menu_favorite',
        'FROM'   => 'glpi_users',
        'WHERE'  => ['id' => $_SESSION["glpiID"]]
    ]
);
if (!isset($_POST['menu_name'])) {
    echo "printing query result :<br>";
    print_r($favorites->next());
    echo "<br>";
    echo "user_id : " . $_SESSION["glpiID"];
    die();
}
$favorites = json_decode($favorites->next()['menu_favorite'], true);
if (is_null($favorites)){
    $favorites = [];
}
if (filter_var($_POST['remove'], FILTER_VALIDATE_BOOLEAN)) {
    $key = array_search($_POST['submenu_name'], $favorites[$_POST['menu_name']]);
    unset($favorites[$_POST['menu_name']][$key]);
    $favorites[$_POST['menu_name']] = array_values($favorites[$_POST['menu_name']]); //reindex key value
    if(empty($favorites[$_POST['menu_name']])){
        unset($favorites[$_POST['menu_name']]);
    }
} else {
    $favorites[$_POST['menu_name']][] = $_POST['submenu_name'];
}
file_put_contents('./test.txt', $_POST['remove']);
$favorites = json_encode($favorites);

$DB->updateOrInsert('glpi_users', ['menu_favorite' => $favorites], ['id' => $_SESSION['glpiID']]);
