<?php

$AJAX_INCLUDE = 1;
include('../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

$bubble_pos = json_encode(array('x' => $_POST['x'], 'y' => $_POST['y']));

$DB->updateOrInsert('glpi_users', ['bubble_pos' => $bubble_pos], ['id' => $_SESSION['glpiID']]);
