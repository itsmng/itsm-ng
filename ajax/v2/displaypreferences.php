<?php

/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2025 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ITSM-NG.
 *
 * ITSM-NG is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ITSM-NG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ITSM-NG. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
    include('../../inc/includes.php');
}

header('Content-Type: application/json; charset=UTF-8');
Html::header_nocache();

Session::checkLoginUser();

global $DB, $CFG_GLPI;

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$itemtype = $_POST['itemtype'] ?? $_GET['itemtype'] ?? '';
$view = $_POST['view'] ?? $_GET['view'] ?? 'personal';

if ($itemtype !== 'AllAssets' && !class_exists($itemtype)) {
    echo json_encode(['success' => false, 'message' => __('Invalid item type')]);
    exit;
}

$can_personal = Session::haveRight(DisplayPreference::$rightname, DisplayPreference::PERSONAL);
$can_global = Session::haveRight(DisplayPreference::$rightname, DisplayPreference::GENERAL);

if (!$can_personal && !$can_global) {
    echo json_encode(['success' => false, 'message' => __('You are not allowed to edit this view')]);
    exit;
}
$personal_view = Session::getLoginUserID();
$global_view = 0;

if ($view === 'global' && !$can_global && $can_personal) {
    $view = 'personal';
}
if ($view !== 'global' && !$can_personal && $can_global) {
    $view = 'global';
}

$users_id = ($view === 'global') ? $global_view : $personal_view;

switch ($action) {
    case 'load':
        if ($view === 'global' && !$can_global) {
            echo json_encode(['success' => false, 'message' => __('You are not allowed to edit this view')]);
            exit;
        }
        if ($view !== 'global' && !$can_personal) {
            echo json_encode(['success' => false, 'message' => __('You are not allowed to edit this view')]);
            exit;
        }
        $searchopt = Search::getCleanedOptions($itemtype);
        if (!is_array($searchopt)) {
            echo json_encode(['success' => false, 'message' => __('Invalid item type')]);
            exit;
        }

        $item = null;
        $entity_locked = false;
        if ($itemtype !== 'AllAssets') {
            $item = getItemForItemtype($itemtype);
        }
        if (
            Session::isMultiEntitiesMode()
            && (
                isset($GLOBALS['CFG_GLPI']["union_search_type"][$itemtype])
                || ($item && $item->maybeRecursive())
                || (count($_SESSION['glpiactiveentities']) > 1)
            )
            && isset($searchopt[80])
        ) {
            $entity_locked = true;
        }

        $available = [];
        $selected = [];
        $locked = [1];
        if ($entity_locked) {
            $locked[] = 80;
        }

        $labels = [];
        $noremove = [];
        $group = '';
        foreach ($searchopt as $key => $val) {
            if (!is_array($val)) {
                $group = $val;
                continue;
            }
            if (count($val) === 1) {
                $group = $val['name'];
                continue;
            }
            if (!is_numeric($key)) {
                continue;
            }
            $labels[(int) $key] = $val['name'] ?? '';
            if (isset($val['noremove']) && $val['noremove'] === true) {
                $noremove[] = (int) $key;
            }
            if (
                $key != 1
                && !in_array((int) $key, $locked, true)
                && (!isset($val['nodisplay']) || !$val['nodisplay'])
            ) {
                $available[] = [
                    'id' => (int) $key,
                    'name' => $val['name'] ?? '',
                    'group' => $group
                ];
            }
        }

        $iterator = $DB->request([
            'FROM'   => DisplayPreference::getTable(),
            'WHERE'  => [
                'itemtype'  => $itemtype,
                'users_id'  => $users_id
            ],
            'ORDER'  => 'rank'
        ]);

        while ($data = $iterator->next()) {
            if (isset($searchopt[$data['num']])) {
                $selected[] = $data['num'];
            }
        }

        if ($users_id === $personal_view && count($selected) === 0) {
            $iterator = $DB->request([
                'FROM'   => DisplayPreference::getTable(),
                'WHERE'  => [
                    'itemtype'  => $itemtype,
                    'users_id'  => $global_view
                ],
                'ORDER'  => 'rank'
            ]);
            while ($data = $iterator->next()) {
                if (isset($searchopt[$data['num']])) {
                    $selected[] = $data['num'];
                }
            }
        }

        $has_personal = countElementsInTable(DisplayPreference::getTable(), [
            'itemtype' => $itemtype,
            'users_id' => $personal_view
        ]) > 0;

        echo json_encode([
            'success' => true,
            'itemtype' => $itemtype,
            'view' => $view,
            'can_personal' => $can_personal,
            'can_global' => $can_global,
            'has_personal' => $has_personal,
            'selected' => $selected,
            'available' => $available,
            'locked' => $locked,
            'labels' => $labels,
            'noremove' => $noremove
        ]);
        break;

    case 'activate_personal':
        if (!$can_personal) {
            echo json_encode(['success' => false, 'message' => __('You are not allowed to edit this view')]);
            exit;
        }
        $dp = new DisplayPreference();
        $dp->activatePerso([
            'itemtype' => $itemtype,
            'users_id' => $personal_view
        ]);
        echo json_encode(['success' => true]);
        break;

    case 'delete_personal':
        if (!$can_personal) {
            echo json_encode(['success' => false, 'message' => __('You are not allowed to edit this view')]);
            exit;
        }
        $dp = new DisplayPreference();
        $deleted = $dp->deleteByCriteria([
            'itemtype' => $itemtype,
            'users_id' => $personal_view
        ]);
        if ($deleted) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => __('Unable to delete personal view')]);
        }
        break;

    case 'save':
        if ($view === 'global' && !$can_global) {
            echo json_encode(['success' => false, 'message' => __('You are not allowed to edit this view')]);
            exit;
        }
        if ($view !== 'global' && !$can_personal) {
            echo json_encode(['success' => false, 'message' => __('You are not allowed to edit this view')]);
            exit;
        }

        if ($view !== 'global') {
            $personal_count = countElementsInTable(DisplayPreference::getTable(), [
                'itemtype' => $itemtype,
                'users_id' => $personal_view
            ]);
            if ($personal_count === 0) {
                echo json_encode([
                    'success' => false,
                    'message' => __('No personal criteria. Create personal parameters?')
                ]);
                exit;
            }
        }

        $order = $_POST['order'] ?? [];
        if (!is_array($order)) {
            $order = [];
        }

        $searchopt = Search::getCleanedOptions($itemtype);
        if (!is_array($searchopt)) {
            echo json_encode(['success' => false, 'message' => __('Invalid item type')]);
            exit;
        }

        $locked = [1];
        $item = null;
        if ($itemtype !== 'AllAssets') {
            $item = getItemForItemtype($itemtype);
        }
        if (
            Session::isMultiEntitiesMode()
            && (
                isset($CFG_GLPI["union_search_type"][$itemtype])
                || ($item && $item->maybeRecursive())
                || (count($_SESSION['glpiactiveentities']) > 1)
            )
            && isset($searchopt[80])
        ) {
            $locked[] = 80;
        }

        $valid = [];
        foreach ($locked as $locked_num) {
            if (isset($searchopt[$locked_num]) && is_array($searchopt[$locked_num])) {
                $valid[] = (int) $locked_num;
            }
        }
        $existing_iterator = $DB->request([
            'SELECT' => ['num'],
            'FROM'   => DisplayPreference::getTable(),
            'WHERE'  => [
                'itemtype' => $itemtype,
                'users_id' => $users_id
            ]
        ]);
        $existing_noremove = [];
        while ($existing = $existing_iterator->next()) {
            $num = (int) $existing['num'];
            if (
                isset($searchopt[$num])
                && isset($searchopt[$num]['noremove'])
                && $searchopt[$num]['noremove'] === true
            ) {
                $existing_noremove[] = $num;
            }
        }
        $valid = array_values(array_unique($valid));
        foreach ($order as $num) {
            $num = (int) $num;
            if (in_array($num, $locked, true)) {
                continue;
            }
            if (isset($searchopt[$num]) && is_array($searchopt[$num])) {
                $valid[] = $num;
            }
        }
        $valid = array_values(array_unique($valid));
        foreach ($existing_noremove as $num) {
            if (!in_array($num, $valid, true)) {
                $valid[] = $num;
            }
        }

        $dp = new DisplayPreference();
        $dp->deleteByCriteria([
            'itemtype' => $itemtype,
            'users_id' => $users_id
        ]);

        $rank = 1;
        foreach ($valid as $num) {
            $dp->add([
                'itemtype' => $itemtype,
                'users_id' => $users_id,
                'num' => $num,
                'rank' => $rank
            ]);
            $rank++;
        }

        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => __('Invalid request')]);
        break;
}
