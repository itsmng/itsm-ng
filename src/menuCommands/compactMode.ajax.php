<?php

$AJAX_INCLUDE = 1;
include('../../inc/includes.php');

header('Content-Type: application/json; charset=UTF-8');
Html::header_nocache();

if (!Session::getLoginUserID()) {
    echo json_encode(['success' => false]);
    return;
}

$compact_mode = filter_var($_POST['compact_mode'] ?? false, FILTER_VALIDATE_BOOLEAN);

global $DB;

$DB->updateOrInsert(
    'glpi_users',
    ['compact_mode_ui' => $compact_mode ? 1 : 0],
    ['id' => $_SESSION['glpiID']]
);

$_SESSION['itsm_compact_mode'] = $compact_mode;

echo json_encode([
    'success' => true,
    'compact_mode' => $compact_mode,
]);
