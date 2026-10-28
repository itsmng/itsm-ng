<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2022 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
**/
class Item_Enclosure extends CommonDBRelation
{
    public static $itemtype_1 = 'Enclosure';
    public static $items_id_1 = 'enclosures_id';
    public static $itemtype_2 = 'itemtype';
    public static $items_id_2 = 'items_id';
    public static $checkItem_1_Rights = self::DONT_CHECK_ITEM_RIGHTS;
    public static $mustBeAttached_1      = false;
    public static $mustBeAttached_2      = false;

    public static function getTypeName($nb = 0)
    {
        return _n('Item', 'Item', $nb);
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $nb = 0;
        if ($_SESSION['glpishow_count_on_tabs']) {
            $nb = self::countForMainItem($item);
        }
        return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showItems($item, $withtemplate);
    }

    /**
     * Print enclosure items
     *
     * @return void
    **/
    public static function showItems(Enclosure $enclosure, $withtemplate = 0)
    {
        global $DB;

        $ID = $enclosure->getID();
        $rand = mt_rand();

        if (
            !$enclosure->getFromDB($ID)
            || !$enclosure->can($ID, READ)
        ) {
            return false;
        }
        $canedit = $enclosure->canEdit($ID) && $withtemplate != 2;

        $items = $DB->request([
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'enclosures_id' => $enclosure->getID()
           ]
        ]);

        Session::initNavigateListItems(
            self::getType(),
            //TRANS : %1$s is the itemtype name,
            //        %2$s is the name of the item (used for headings of a list)
            sprintf(
                __('%1$s = %2$s'),
                $enclosure->getTypeName(1),
                $enclosure->getName()
            )
        );

        if ($canedit && $enclosure->canAddItem('itemtype')) {
            $relation = new self();
            $relation->getEmpty();
            $relation->fields['enclosures_id'] = $ID;
            $relation->showForm(-1, ['enclosures_id' => $ID, 'position' => 1]);
        }
        $values = [];
        $selection = [];
        foreach ($items as $row) {
            $item = getItemForItemtype($row['itemtype']);
            if (!$item || !$item->getFromDB($row['items_id'])) {
                continue;
            }
            $values[] = [$item->getLink(), (int) $row['position']];
            $selection[] = sprintf('item[%s][%s]', self::class, $row['id']);
        }
        $table_id = 'item_enclosure_' . $ID;
        $table = [
            'id' => $table_id,
            'state_key' => $table_id,
            'fields' => [_n('Item', 'Items', 1), __('Position')],
            'values' => $values,
            'pageSize' => (int) ($_SESSION['glpilist_limit'] ?? 15),
        ];
        if ($canedit) {
            Html::showMassiveActions([
                'container' => $table_id,
                'num_displayed' => min((int) ($_SESSION['glpilist_limit'] ?? 15), count($values)),
                'itemtype' => self::class,
                'display_arrow' => false,
            ]);
            $table['massive_action'] = $selection;
        }
        renderTwigTemplate('table.twig', $table);
    }

    public function showForm($ID, $options = [])
    {
        global $DB, $CFG_GLPI;
        $this->initForm($ID, $options);
        $enclosure = new Enclosure();
        $enclosure->getFromDB($this->fields['enclosures_id']);
        $used = [];
        foreach ($DB->request(['FROM' => self::getTable()]) as $row) {
            $used[$row['itemtype']][] = $row['items_id'];
        }
        foreach ($DB->request(['FROM' => Item_Rack::getTable(), 'WHERE' => ['is_reserved' => 0]]) as $row) {
            $used[$row['itemtype']][] = $row['items_id'];
        }
        $types = [];
        foreach ($CFG_GLPI['rackable_types'] as $type) {
            if ($type !== 'Enclosure') {
                $types[$type] = $type::getTypeName(1);
            }
        }
        $current_type = $this->fields['itemtype'] ?? '';
        $used_json = json_encode($used);
        $dropdown_id = 'enclosureItems_' . ($ID > 0 ? $ID : $enclosure->getID());
        $load_items = <<<JS
            $.post({
                url: "{$CFG_GLPI['root_doc']}/ajax/dropdownAllItems.php",
                data: {idtable: this.value, used: $used_json},
                success: function(data) {
                    setAjaxDropdownOptions('#$dropdown_id', typeof data === 'string' ? JSON.parse(data) : data);
                }
            });
        JS;
        $positions = array_combine(range(1, 100), range(1, 100));
        $positions = array_diff_key($positions, $enclosure->getFilled($current_type, $this->fields['items_id'] ?? 0));
        renderTwigForm([
            'action' => self::getFormURL(),
            'buttons' => [[
                'type' => 'submit', 'name' => $this->isNewID($ID) ? 'add' : 'update',
                'value' => $this->isNewID($ID) ? _x('button', 'Add') : _x('button', 'Save'),
                'class' => 'btn btn-primary', 'icon' => $this->isNewID($ID) ? 'fas fa-plus' : null,
            ]],
            'content' => [__('Add new item to this enclosure...') => [
                'visible' => true,
                'inputs' => [
                    $this->isNewID($ID) ? [] : ['type' => 'hidden', 'name' => 'id', 'value' => $ID],
                    __('Item type') => [
                        'type' => 'select', 'name' => 'itemtype', 'values' => [0 => Dropdown::EMPTY_VALUE] + $types,
                        'value' => $current_type, 'hooks' => ['change' => $load_items],
                    ],
                    _n('Item', 'Items', 1) => [
                        'type' => 'select', 'id' => $dropdown_id, 'name' => 'items_id',
                        'value' => $this->fields['items_id'] ?? 0,
                        ...($current_type ? getAjaxDropdownOptions($current_type, [], true, false, $used[$current_type] ?? []) : []),
                    ],
                    Enclosure::getTypeName(1) => [
                        'type' => 'select', 'name' => 'enclosures_id', 'value' => $enclosure->getID(),
                        ...getAjaxDropdownOptions(Enclosure::class),
                    ],
                    __('Position') => [
                        'type' => 'select', 'name' => 'position', 'values' => $positions,
                        'value' => $this->fields['position'] ?: ($options['position'] ?? array_key_first($positions)),
                    ],
                ],
            ]],
        ]);
        return true;
    }

    public function prepareInputForAdd($input)
    {
        return $this->prepareInput($input);
    }

    public function prepareInputForUpdate($input)
    {
        return $this->prepareInput($input);
    }

    /**
     * Prepares input (for update and add)
     *
     * @param array $input Input data
     *
     * @return array
     */
    private function prepareInput($input)
    {
        $error_detected = [];

        //check for requirements
        if (
            ($this->isNewItem() && (!isset($input['itemtype']) || empty($input['itemtype'])))
            || (isset($input['itemtype']) && empty($input['itemtype']))
        ) {
            $error_detected[] = __('An item type is required');
        }
        if (
            ($this->isNewItem() && (!isset($input['items_id']) || empty($input['items_id'])))
            || (isset($input['items_id']) && empty($input['items_id']))
        ) {
            $error_detected[] = __('An item is required');
        }
        if (
            ($this->isNewItem() && (!isset($input['enclosures_id']) || empty($input['enclosures_id'])))
            || (isset($input['enclosures_id']) && empty($input['enclosures_id']))
        ) {
            $error_detected[] = __('An enclosure is required');
        }
        if (
            ($this->isNewItem() && (!isset($input['position']) || empty($input['position'])))
            || (isset($input['position']) && empty($input['position']))
        ) {
            $error_detected[] = __('A position is required');
        }

        if (count($error_detected)) {
            foreach ($error_detected as $error) {
                Session::addMessageAfterRedirect(
                    $error,
                    true,
                    ERROR
                );
            }
            return false;
        }

        return $input;
    }
}
