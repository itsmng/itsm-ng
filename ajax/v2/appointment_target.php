<?php

include('../../inc/includes.php');

header('Content-Type: application/json; charset=UTF-8');
Html::header_nocache();

Session::checkLoginUser();

if (!Appointment::canView()) {
    echo json_encode(['total' => 0, 'rows' => []]);
    exit;
}

$offset = isset($_GET['offset']) ? max(0, (int) $_GET['offset']) : 0;
$limit = isset($_GET['limit']) ? max(1, (int) $_GET['limit']) : (int) $_SESSION['glpilist_limit'];
$search = trim((string) ($_GET['search'] ?? ''));
$sort = (string) ($_GET['sort'] ?? 'target');
$order = strtolower((string) ($_GET['order'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';

$total = 0;
$rows = [];
$remaining_offset = $offset;
$remaining_limit = $limit;

foreach (['Group', 'User'] as $itemtype) {
    $item = getItemForItemtype($itemtype);
    if (!$item) {
        continue;
    }

    $itemtable = getTableForItemType($itemtype);
    $itemname = $item->getNameField();
    $where = [
        AppointmentTarget::getTable() . '.itemtype' => $itemtype,
        AppointmentTarget::getTable() . '.is_active' => 1,
        AppointmentTarget::getTable() . '.is_deleted' => 0,
    ] + getEntitiesRestrictCriteria(AppointmentTarget::getTable(), 'entities_id', $_SESSION['glpiactiveentities'], true);

    if ($search !== '') {
        $like = Search::makeTextSearchValue($search);
        $where[] = [
            'OR' => [
                "$itemtable.$itemname" => ['LIKE', $like],
                'glpi_entities.completename' => ['LIKE', $like],
                AppointmentTarget::getTable() . '.comment' => ['LIKE', $like],
                AppointmentTarget::getTable() . '.itemtype' => ['LIKE', $like],
            ],
        ];
    }

    $joins = [
        $itemtable => [
            'ON' => [
                AppointmentTarget::getTable() => 'items_id',
                $itemtable => 'id',
            ],
        ],
        'glpi_entities' => [
            'ON' => [
                AppointmentTarget::getTable() => 'entities_id',
                'glpi_entities' => 'id',
            ],
        ],
    ];

    $count = $DB->request([
        'SELECT' => ['COUNT' => AppointmentTarget::getTable() . '.id AS cpt'],
        'FROM' => AppointmentTarget::getTable(),
        'INNER JOIN' => [$itemtable => $joins[$itemtable]],
        'LEFT JOIN' => ['glpi_entities' => $joins['glpi_entities']],
        'WHERE' => $where,
    ])->next();
    $type_total = (int) ($count['cpt'] ?? 0);
    $total += $type_total;

    if ($remaining_limit <= 0) {
        continue;
    }
    if ($remaining_offset >= $type_total) {
        $remaining_offset -= $type_total;
        continue;
    }

    $order_field = "$itemtable.$itemname";
    if ($sort === 'entity') {
        $order_field = 'glpi_entities.completename';
    } elseif ($sort === 'comment') {
        $order_field = AppointmentTarget::getTable() . '.comment';
    }

    $iterator = $DB->request([
        'SELECT' => [
            AppointmentTarget::getTable() . '.*',
            "$itemtable.$itemname AS target_name",
            'glpi_entities.completename AS entity_name',
        ],
        'FROM' => AppointmentTarget::getTable(),
        'INNER JOIN' => [$itemtable => $joins[$itemtable]],
        'LEFT JOIN' => ['glpi_entities' => $joins['glpi_entities']],
        'WHERE' => $where,
        'ORDERBY' => [$order_field . ' ' . $order, AppointmentTarget::getTable() . '.id'],
        'START' => $remaining_offset,
        'LIMIT' => $remaining_limit,
    ]);

    $before = count($rows);
    foreach ($iterator as $row) {
        $icon = AppointmentTarget::getTargetIcon($row);
        $type_label = $itemtype::getTypeName(1);
        $target = "<a class='appointment-target-link' href='" . $CFG_GLPI['root_doc'] . "/front/appointment.php?appointmenttargets_id=" . (int) $row['id'] . "'>";
        if ($icon !== '') {
            $target .= "<i class='" . Html::entities_deep($icon) . "' title='" . Html::entities_deep($type_label) . "' aria-hidden='true'></i>";
            $target .= "<span class='sr-only'>" . Html::clean($type_label) . "</span>";
        }
        $target .= "<span>" . Html::clean($row['target_name']) . "</span></a>";

        $rows[] = [
            'target' => $target,
            'entity' => Html::clean($row['entity_name']),
            'comment' => Html::clean($row['comment']),
        ];
    }

    $remaining_limit -= count($rows) - $before;
    $remaining_offset = 0;
}

echo json_encode([
    'total' => $total,
    'rows' => $rows,
]);
