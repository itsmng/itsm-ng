<?php
$AJAX_INCLUDE = 1;
include ('../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

file_put_contents('./test.txt', $_POST['bubble_pos']);
$DB->queryOrDie(
    'ALTER TABLE glpi_users ADD COLUMN IF NOT EXISTS bubble_pos longtext'
);


$bubble_pos = json_encode(array('x' => $_POST['x'], 'y' => $_POST['y']));

$DB->updateOrInsert('glpi_users', ['bubble_pos' => $bubble_pos], ['id' => $_SESSION['glpiID']]);
