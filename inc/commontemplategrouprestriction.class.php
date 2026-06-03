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
    die("Sorry. You can't access this file directly");
}

abstract class CommonTemplateGroupRestriction extends CommonDBRelation
{
    public static $itemtype_2         = Group::class;
    public static $items_id_2         = 'groups_id';
    public static $checkItem_2_Rights = self::DONT_CHECK_ITEM_RIGHTS;
    public static $logs_for_item_2    = false;


    public static function getGroups($items_id)
    {
        global $DB;

        $groups = [];
        $iterator = $DB->request([
           'FROM'  => static::getTable(),
           'WHERE' => [
              static::$items_id_1 => $items_id,
           ],
        ]);

        while ($data = $iterator->next()) {
            $groups[$data['groups_id']][] = $data;
        }

        return $groups;
    }


    public static function getGroupIDs($items_id)
    {
        return array_map('intval', array_keys(static::getGroups($items_id)));
    }


    public static function canAccessItem(int $items_id, ?array $groups_ids = null): bool
    {
        $allowed_groups = static::getGroupIDs($items_id);
        if (!count($allowed_groups)) {
            return true;
        }

        $groups_ids = static::normalizeGroupIDs($groups_ids);
        return count(array_intersect($allowed_groups, $groups_ids)) > 0;
    }


    public static function getItemRestrictionCondition(?array $groups_ids = null): array
    {
        global $DB;

        $items_id_field = static::$items_id_1;
        $restricted_ids = [];
        $allowed_ids    = [];

        $iterator = $DB->request([
           'SELECT'   => [$items_id_field],
           'DISTINCT' => true,
           'FROM'     => static::getTable(),
        ]);

        while ($data = $iterator->next()) {
            $restricted_ids[] = (int)$data[$items_id_field];
        }

        if (!count($restricted_ids)) {
            return [];
        }

        $groups_ids = static::normalizeGroupIDs($groups_ids);
        if (count($groups_ids)) {
            $iterator = $DB->request([
               'SELECT'   => [$items_id_field],
               'DISTINCT' => true,
               'FROM'     => static::getTable(),
               'WHERE'    => [
                  'groups_id' => $groups_ids,
               ],
            ]);

            while ($data = $iterator->next()) {
                $allowed_ids[] = (int)$data[$items_id_field];
            }
        }

        $denied_ids = array_values(array_diff($restricted_ids, $allowed_ids));
        if (!count($denied_ids)) {
            return [];
        }

        return [
           'NOT' => [
              getTableForItemType(static::$itemtype_1) . '.id' => $denied_ids,
           ],
        ];
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (
            $withtemplate
            || $item->getType() !== static::$itemtype_1
            || !Group::canView()
        ) {
            return '';
        }

        $nb = 0;
        if ($_SESSION['glpishow_count_on_tabs']) {
            $nb = static::countForItem($item);
        }

        return self::createTabEntry(__('Allowed groups'), $nb);
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() !== static::$itemtype_1) {
            return false;
        }

        static::showForItem($item, $withtemplate);
        return true;
    }


    public static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {
        if ($withtemplate || $item->getType() !== static::$itemtype_1) {
            return;
        }

        $ID      = $item->getID();
        $canedit = $item->can($ID, UPDATE);
        $used    = static::getGroupIDs($ID);

        if ($canedit) {
            $conditions = [];
            if ($item->isEntityAssign()) {
                $conditions['entities_id'] = $item->fields['entities_id'];
                $conditions['is_recursive'] = $item->fields['is_recursive'];
            }
            if (count($used)) {
                $conditions['NOT'] = [
                   'id' => $used,
                ];
            }

            $options = getOptionForItems(Group::class, $conditions);
            if (count($options)) {
                $form = [
                   'action'  => Toolbox::getItemTypeFormURL(static::class),
                   'buttons' => [[
                      'type'  => 'submit',
                      'name'  => 'add',
                      'value' => _sx('button', 'Add'),
                      'class' => 'btn btn-secondary',
                   ]],
                   'content' => [
                      __('Add a group restriction') => [
                         'visible' => true,
                         'inputs'  => [
                         [
                            'type'  => 'hidden',
                            'name'  => static::$items_id_1,
                            'value' => $ID,
                         ],
                         Group::getTypeName(1) => [
                            'type'    => 'select',
                            'name'    => 'groups_id',
                            'values'  => $options,
                            'actions' => getItemActionButtons(['info', 'add'], Group::class),
                         ],
                         ],
                      ],
                   ],
                ];
                renderTwigForm($form);
            }
        }

        if ($canedit && count($used)) {
            Html::showMassiveActions([
               'container'     => 'tab_' . static::class,
               'display_arrow' => false,
            ]);
        }

        $values = [];
        $massiveactionparams = [];
        $group = new Group();

        foreach (static::getListForItem($item) as $data) {
            if (!$group->getFromDB($data['id'])) {
                continue;
            }

            $values[] = [$group->getLink()];
            $massiveactionparams[] = sprintf('item[%s][%s]', static::class, $data['linkid']);
        }

        renderTwigTemplate('table.twig', [
           'id'             => 'tab_' . static::class,
           'fields'         => [Group::getTypeName(1)],
           'values'         => $values,
           'massive_action' => $massiveactionparams,
           'pageSize'       => $_SESSION['glpilist_limit'],
        ]);
    }


    private static function normalizeGroupIDs(?array $groups_ids = null): array
    {
        $groups_ids = $groups_ids ?? ($_SESSION['glpigroups'] ?? []);
        return array_values(array_unique(array_map('intval', $groups_ids)));
    }
}
