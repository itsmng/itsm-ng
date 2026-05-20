<?php

include('../../inc/includes.php');

header('Content-Type: application/json; charset=UTF-8');
Html::header_nocache();

Session::checkLoginUser();

if (!Session::haveRight(ReservationItem::$rightname, ReservationItem::RESERVEANITEM)) {
    echo json_encode(['total' => 0, 'rows' => []]);
    exit;
}

$offset = isset($_GET['offset']) ? max(0, (int) $_GET['offset']) : 0;
$limit = isset($_GET['limit']) ? max(1, (int) $_GET['limit']) : (int) $_SESSION['glpilist_limit'];
$search = trim((string) ($_GET['search'] ?? ''));
$sort = (string) ($_GET['sort'] ?? 'item');
$order = strtolower((string) ($_GET['order'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';
$begin = trim((string) ($_GET['begin'] ?? ''));
$end = trim((string) ($_GET['end'] ?? ''));
$reservation_types = trim((string) ($_GET['reservation_types'] ?? ''));
$showentity = Session::isMultiEntitiesMode();

$filter_itemtype = '';
$filter_peripheraltype = 0;
if ($reservation_types !== '') {
    $tmp = explode('#', $reservation_types);
    $filter_itemtype = $tmp[0] ?? '';
    $filter_peripheraltype = isset($tmp[1]) ? (int) $tmp[1] : 0;
}

$buildCriteria = function ($itemtype, $count = false) use ($DB, $search, $sort, $order, $begin, $end, $filter_itemtype, $filter_peripheraltype) {
    $item = getItemForItemtype($itemtype);
    if (!$item) {
        return null;
    }

    $itemtable = getTableForItemType($itemtype);
    $itemname = $item->getNameField();
    $otherserial = new \QueryExpression($DB->quote('') . ' AS ' . $DB->quoteName('otherserial'));
    if ($item->isField('otherserial')) {
        $otherserial = "$itemtable.otherserial AS otherserial";
    }

    $select = $count ? ['COUNT DISTINCT' => 'glpi_reservationitems.id AS cpt'] : [
        'glpi_reservationitems.id',
        'glpi_reservationitems.comment',
        'glpi_reservationitems.items_id AS items_id',
        "$itemtable.$itemname AS name",
        "$itemtable.entities_id AS entities_id",
        $otherserial,
        'glpi_locations.completename AS location_name',
    ];

    $criteria = [
        'SELECT' => $select,
        'FROM' => ReservationItem::getTable(),
        'INNER JOIN' => [
            $itemtable => [
                'ON' => [
                    'glpi_reservationitems' => 'items_id',
                    $itemtable => 'id',
                    [
                        'AND' => [
                            'glpi_reservationitems.itemtype' => $itemtype,
                        ],
                    ],
                ],
            ],
        ],
        'LEFT JOIN' => [
            'glpi_locations' => [
                'ON' => [
                    $itemtable => 'locations_id',
                    'glpi_locations' => 'id',
                ],
            ],
            'glpi_entities' => [
                'ON' => [
                    $itemtable => 'entities_id',
                    'glpi_entities' => 'id',
                ],
            ],
        ],
        'WHERE' => [
            'glpi_reservationitems.is_active' => 1,
            'glpi_reservationitems.is_deleted' => 0,
            "$itemtable.is_deleted" => 0,
        ] + getEntitiesRestrictCriteria($itemtable, '', $_SESSION['glpiactiveentities'], $item->maybeRecursive()),
    ];

    if ($begin !== '' && $end !== '') {
        $criteria['LEFT JOIN']['glpi_reservations'] = [
            'ON' => [
                'glpi_reservationitems' => 'id',
                'glpi_reservations' => 'reservationitems_id',
                [
                    'AND' => [
                        'glpi_reservations.end' => ['>', $begin],
                        'glpi_reservations.begin' => ['<', $end],
                    ],
                ],
            ],
        ];
        $criteria['WHERE'][] = ['glpi_reservations.id' => null];
    }

    if ($filter_itemtype !== '') {
        if ($filter_itemtype !== $itemtype) {
            return null;
        }
        $criteria['WHERE'][] = ['glpi_reservationitems.itemtype' => $filter_itemtype];
    }

    if ($itemtype === 'Peripheral') {
        $criteria['LEFT JOIN']['glpi_peripheraltypes'] = [
            'ON' => [
                'glpi_peripherals' => 'peripheraltypes_id',
                'glpi_peripheraltypes' => 'id',
            ],
        ];
        if (!$count) {
            $criteria['SELECT'][] = 'glpi_peripheraltypes.name AS peripheraltype_name';
        }
        if ($filter_peripheraltype > 0) {
            $criteria['WHERE'][] = ["$itemtable.peripheraltypes_id" => $filter_peripheraltype];
        }
    }

    if ($search !== '') {
        $like = Search::makeTextSearchValue($search);
        $or = [
            "$itemtable.$itemname" => ['LIKE', $like],
            'glpi_locations.completename' => ['LIKE', $like],
            'glpi_entities.completename' => ['LIKE', $like],
            'glpi_reservationitems.comment' => ['LIKE', $like],
            'glpi_reservationitems.itemtype' => ['LIKE', $like],
        ];
        if ($item->isField('otherserial')) {
            $or["$itemtable.otherserial"] = ['LIKE', $like];
        }
        if ($itemtype === 'Peripheral') {
            $or['glpi_peripheraltypes.name'] = ['LIKE', $like];
        }
        if (stripos($item->getTypeName(), $search) !== false) {
            $or['glpi_reservationitems.itemtype'] = $itemtype;
        }
        $criteria['WHERE'][] = ['OR' => $or];
    }

    if (!$count) {
        $order_field = "$itemtable.$itemname";
        if ($sort === 'location') {
            $order_field = 'glpi_locations.completename';
        } elseif ($sort === 'comment') {
            $order_field = 'glpi_reservationitems.comment';
        } elseif ($sort === 'entity') {
            $order_field = 'glpi_entities.completename';
        }
        $criteria['ORDERBY'] = [$order_field . ' ' . $order, 'glpi_reservationitems.id'];
    }

    return $criteria;
};

$total = 0;
$rows = [];
$remaining_offset = $offset;
$remaining_limit = $limit;

foreach ($CFG_GLPI['reservation_types'] as $itemtype) {
    $item = getItemForItemtype($itemtype);
    if (!$item) {
        continue;
    }

    $count_criteria = $buildCriteria($itemtype, true);
    if ($count_criteria === null) {
        continue;
    }

    $count = $DB->request($count_criteria)->next();
    $type_total = (int) ($count['cpt'] ?? 0);
    $total += $type_total;

    if ($remaining_limit <= 0) {
        continue;
    }
    if ($remaining_offset >= $type_total) {
        $remaining_offset -= $type_total;
        continue;
    }

    $criteria = $buildCriteria($itemtype, false);
    $criteria['START'] = $remaining_offset;
    $criteria['LIMIT'] = $remaining_limit;

    $before = count($rows);
    foreach ($DB->request($criteria) as $row) {
        $typename = $itemtype === 'Peripheral' && !empty($row['peripheraltype_name'])
            ? $row['peripheraltype_name']
            : $item->getTypeName();
        $icon = is_a($itemtype, CommonGLPI::class, true) ? $itemtype::getIcon() : 'fas fa-desktop';

        $item_cell = "<a href='reservation.php?reservationitems_id=" . (int) $row['id'] . "' class='text-decoration-none'>";
        $item_cell .= "<div class='d-flex align-items-center reservation-table__item-cell'>";
        $item_cell .= "<i class='" . Html::entities_deep($icon) . " text-primary me-2'></i>";
        $item_cell .= "<div class='reservation-table__item-text'>";
        $item_cell .= "<strong>" . Html::clean($row['name']) . "</strong>";
        $item_cell .= "<small class='text-muted'>" . Html::clean($typename) . "</small>";
        if (!empty($row['otherserial'])) {
            $item_cell .= "<br><small class='text-info'>S/N: " . Html::clean($row['otherserial']) . "</small>";
        }
        $item_cell .= "</div></div></a>";

        if (!empty($row['location_name'])) {
            $location_cell = "<div class='d-flex align-items-center'>";
            $location_cell .= "<i class='fas fa-map-marker-alt text-success me-2'></i>";
            $location_cell .= "<span>" . Html::clean($row['location_name']) . "</span>";
            $location_cell .= "</div>";
        } else {
            $location_cell = "<span class='text-muted'><i class='fas fa-minus'></i> " . __('Not defined') . "</span>";
        }

        $comment_cell = "<span class='text-muted'>-</span>";
        if (!empty($row['comment'])) {
            $comment = Html::clean($row['comment']);
            if (strlen($comment) > 100) {
                $comment_cell = "<span data-bs-toggle='tooltip' data-bs-placement='top' title='" . Html::entities_deep($comment) . "'>";
                $comment_cell .= Html::clean(substr($comment, 0, 97)) . "...";
                $comment_cell .= "</span>";
            } else {
                $comment_cell = $comment;
            }
        }

        $table_row = [
            'item' => $item_cell,
            'location' => $location_cell,
            'comment' => $comment_cell,
        ];
        if ($showentity) {
            $table_row['entity'] = "<div class='d-flex align-items-center'><i class='fas fa-building text-warning me-2'></i><small>"
                . Html::clean(Dropdown::getDropdownName('glpi_entities', $row['entities_id'])) . "</small></div>";
        }
        $rows[] = $table_row;
    }

    $remaining_limit -= count($rows) - $before;
    $remaining_offset = 0;
}

echo json_encode([
    'total' => $total,
    'rows' => $rows,
]);
