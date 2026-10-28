<?php

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
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}


/**
 * This class manages locks
 * Lock management is available for objects and link between objects. It relies on the use of
 * a is_dynamic field, to incidate if item supports lock, and is_deleted field to incidate if the
 * item or link is locked
 * By setting is_deleted to 0 again, the item is unlock
 *
 * Note : GLPI's core supports locks for objects. It's up to the external inventory tool to manage
 * locks for fields
 *
 * @since 0.84
 **/
class Lock extends CommonGLPI
{
    public static function getTypeName($nb = 0)
    {
        return _n('Lock', 'Locks', $nb);
    }


    /**
     * Display form to unlock fields and links
     *
     * @param CommonDBTM $item the source item
    **/
    public static function showForItem(CommonDBTM $item)
    {
        global $DB;

        $ID       = $item->getID();
        $itemtype = $item->getType();
        $header   = false;

        //If user doesn't have update right on the item, lock form must not be displayed
        if (!$item->isDynamic() || !$item->can($item->fields['id'], UPDATE)) {
            return false;
        }

        $values = [];
        $selection = [];
        $add_lock = static function (CommonDBTM $locked_item, $id, $type_name, $name) use (&$values, &$selection, &$header) {
            $type = $locked_item->getType();
            $key = $type . '_' . $id;
            $values[$key] = [
                htmlspecialchars((string) $type_name, ENT_QUOTES, 'UTF-8', false),
                htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8', false),
            ];
            if ($locked_item->can($id, UPDATE) || $locked_item->can($id, PURGE)) {
                $selection[$key] = $type . '[' . $id . ']';
                $header = true;
            }
        };

        // Inventory plugins still provide table rows through this hook.
        ob_start();
        $results = Plugin::doHookFunction('display_locked_fields', ['item' => $item, 'header' => $header]);
        $plugin_fields = ob_get_clean();
        $header |= $results['header'];

        //Special locks for computers only
        if ($itemtype == 'Computer') {
            $computer_item = new Computer_Item();
            //Locks for items recorded in glpi_computers_items table
            $types = ['Monitor', 'Peripheral', 'Printer'];
            foreach ($types as $type) {
                $params = ['is_dynamic'    => 1,
                                'is_deleted'    => 1,
                                'computers_id'  => $ID,
                                'itemtype'      => $type];
                $params['FIELDS'] = ['id', 'items_id'];
                foreach ($DB->request('glpi_computers_items', $params) as $line) {
                    /** @var CommonDBTM $asset */
                    $asset = new $type();
                    $asset->getFromDB($line['items_id']);
                    $add_lock($computer_item, $line['id'], $type::getTypeName(1), $asset->getName());
                }
            }

            //items disks
            $item_disk = new Item_Disk();
            $params = [
               'is_dynamic'   => 1,
               'is_deleted'   => 1,
               'items_id'     => $ID,
               'itemtype'     => $itemtype
            ];
            $params['FIELDS'] = ['id', 'name'];
            foreach ($DB->request($item_disk->getTable(), $params) as $line) {
                $add_lock($item_disk, $line['id'], $item_disk->getTypeName(1), $line['name']);
            }

            $computer_vm = new ComputerVirtualMachine();
            $params = ['is_dynamic'    => 1,
                            'is_deleted'    => 1,
                            'computers_id'  => $ID];
            $params['FIELDS'] = ['id', 'name'];
            foreach ($DB->request($computer_vm->getTable(), $params) as $line) {
                $add_lock($computer_vm, $line['id'], $computer_vm->getTypeName(1), $line['name']);
            }
        }

        //Software versions
        $item_sv = new Item_SoftwareVersion();
        $item_sv_table = Item_SoftwareVersion::getTable();

        $iterator = $DB->request([
           'SELECT'    => [
              'isv.id AS id',
              'sv.name AS version',
              's.name AS software'
           ],
           'FROM'      => "{$item_sv_table} AS isv",
           'LEFT JOIN' => [
              'glpi_softwareversions AS sv' => [
                 'FKEY' => [
                    'isv' => 'softwareversions_id',
                    'sv'  => 'id'
                 ]
              ],
              'glpi_softwares AS s'         => [
                 'FKEY' => [
                    'sv'  => 'softwares_id',
                    's'   => 'id'
                 ]
              ]
           ],
           'WHERE'     => [
              'isv.is_deleted'  => 1,
              'isv.is_dynamic'  => 1,
              'isv.items_id'    => $ID,
              'isv.itemtype'    => $itemtype,
           ]
        ]);
        while ($data = $iterator->next()) {
            $add_lock($item_sv, $data['id'], Software::getTypeName(1), $data['software'] . ' ' . $data['version']);
        }

        //Software licenses
        $item_sl = new Item_SoftwareLicense();
        $item_sl_table = Item_SoftwareLicense::getTable();

        $iterator = $DB->request([
           'SELECT'    => [
              'isl.id AS id',
              'sl.name AS version',
              's.name AS software'
           ],
           'FROM'      => "{$item_sl_table} AS isl",
           'LEFT JOIN' => [
              'glpi_softwarelicenses AS sl' => [
                 'FKEY' => [
                    'isl' => 'softwarelicenses_id',
                    'sl'  => 'id'
                 ]
              ],
              'glpi_softwares AS s'         => [
                 'FKEY' => [
                    'sl'  => 'softwares_id',
                    's'   => 'id'
                 ]
              ]
           ],
           'WHERE'     => [
              'isl.is_deleted'  => 1,
              'isl.is_dynamic'  => 1,
              'isl.items_id'    => $ID,
              'isl.itemtype'    => $itemtype,
           ]
        ]);

        while ($data = $iterator->next()) {
            $add_lock($item_sl, $data['id'], SoftwareLicense::getTypeName(1), $data['software'] . ' ' . $data['version']);
        }

        $networkport = new NetworkPort();
        $params = ['is_dynamic' => 1,
                        'is_deleted' => 1,
                        'items_id'   => $ID,
                        'itemtype'   => $itemtype];
        $params['FIELDS'] = ['id'];
        foreach ($DB->request($networkport->getTable(), $params) as $line) {
            $networkport->getFromDB($line['id']);
            $add_lock($networkport, $line['id'], NetworkPort::getTypeName(1), $networkport->getName());
        }

        $networkname = new NetworkName();
        $params = [
           'glpi_networknames.is_dynamic' => 1,
           'glpi_networknames.is_deleted' => 1,
           'glpi_networknames.itemtype'   => 'NetworkPort',
           'glpi_networknames.items_id'   => new QueryExpression($DB->quoteName('glpi_networkports.id')),
           'glpi_networkports.items_id'   => $ID,
           'glpi_networkports.itemtype'   => $itemtype
        ];
        $params['FIELDS'] = ['glpi_networknames' => 'id'];
        foreach ($DB->request(['glpi_networknames', 'glpi_networkports'], $params) as $line) {
            $networkname->getFromDB($line['id']);
            $add_lock($networkname, $line['id'], NetworkName::getTypeName(1), $networkname->getName());
        }

        $ipaddress = new IPAddress();
        $params = [
           'glpi_ipaddresses.is_dynamic' => 1,
           'glpi_ipaddresses.is_deleted' => 1,
           'glpi_ipaddresses.itemtype'   => 'NetworkName',
           'glpi_ipaddresses.items_id'   => new QueryExpression($DB->quoteName('glpi_networknames.id')),
           'glpi_networknames.itemtype'  => 'NetworkPort',
           'glpi_networknames.items_id'  => new QueryExpression($DB->quoteName('glpi_networkports.id')),
           'glpi_networkports.items_id'  => $ID,
           'glpi_networkports.itemtype'  => $itemtype
        ];
        $params['FIELDS'] = ['glpi_ipaddresses' => 'id'];
        foreach (
            $DB->request(['glpi_ipaddresses',
                                    'glpi_networknames',
                                    'glpi_networkports'], $params) as $line
        ) {
            $ipaddress->getFromDB($line['id']);
            $add_lock($ipaddress, $line['id'], IPAddress::getTypeName(1), $ipaddress->getName());
        }

        $types = Item_Devices::getDeviceTypes();
        foreach ($types as $type) {
            $type_item = new $type();

            $associated_type  = str_replace('Item_', '', $type);
            $associated_table = getTableForItemType($associated_type);
            $fk               = getForeignKeyFieldForTable($associated_table);

            $iterator = $DB->request([
               'SELECT'    => [
                  'i.id',
                  't.designation AS name'
               ],
               'FROM'      => getTableForItemType($type) . ' AS i',
               'LEFT JOIN' => [
                  "$associated_table AS t"   => [
                     'ON' => [
                        't'   => 'id',
                        'i'   => $fk
                     ]
                  ]
               ],
               'WHERE'     => [
                  'itemtype'     => $itemtype,
                  'items_id'     => $ID,
                  'is_dynamic'   => 1,
                  'is_deleted'   => 1
               ]
            ]);

            while ($data = $iterator->next()) {
                $add_lock($type_item, $data['id'], $associated_type::getTypeName(1), $data['name']);
            }
        }
        echo "<form aria-label='" . __s('Locked items') . "' method='post' id='lock_form' name='lock_form' action='"
            . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
        echo Html::hidden('id', ['value' => $ID]);
        echo Html::hidden('itemtype', ['value' => $itemtype]);
        renderTwigTemplate('locks.twig', [
            'table_id' => 'LockedItems_' . $itemtype . '_' . $ID,
            'page_size' => (int) ($_SESSION['glpilist_limit'] ?? 15),
            'values' => $values,
            'selection' => $selection,
            'plugin_fields' => $plugin_fields,
            'can_unlock' => $header,
            'unlock_label' => _sx('button', 'Unlock'),
            'purge_label' => _sx('button', 'Delete permanently'),
        ]);
        Html::closeForm();
    }


    /**
     * @see CommonGLPI::getTabNameForItem()
     *
     * @param $item               CommonGLPI object
     * @param $withtemplate       (default 0)
    **/
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if ($item->isDynamic() && $item->can($item->fields['id'], UPDATE)) {
            return Lock::getTypeName(Session::getPluralNumber());
        }
        return '';
    }


    /**
     * @param $item            CommonGLPI object
     * @param $tabnum          (default 1)
     * @param $withtemplate    (default 0)
    **/
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->isDynamic() && $item->can($item->fields['id'], UPDATE)) {
            self::showForItem($item);
        }
        return true;
    }


    /**
     * Get infos to build an SQL query to get locks fields in a table
     *
     * @param string $itemtype      itemtype of the item to look for locked fields
     * @param string $baseitemtype  itemtype of the based item
     *
     * @return array  which contains necessary information to build the SQL query
    **/
    public static function getLocksQueryInfosByItemType($itemtype, $baseitemtype)
    {
        global $DB;

        $condition = [];
        $table     = false;
        $field     = '';
        $type      = $itemtype;

        switch ($itemtype) {
            case 'Peripheral':
            case 'Monitor':
            case 'Printer':
            case 'Phone':
                $condition = ['itemtype'   => $itemtype,
                                   'is_dynamic' => 1,
                                   'is_deleted' => 1];
                $table     = 'glpi_computers_items';
                $field     = 'computers_id';
                $type      = 'Computer_Item';
                break;

            case 'NetworkPort':
                $condition = ['itemtype'   => $baseitemtype,
                                   'is_dynamic' => 1,
                                   'is_deleted' => 1];
                $table     = 'glpi_networkports';
                $field     = 'items_id';
                break;

            case 'NetworkName':
                $condition = [
                   'glpi_networknames.is_dynamic' => 1,
                   'glpi_networknames.is_deleted' => 1,
                   'glpi_networknames.itemtype'   => 'NetworkPort',
                   'glpi_networknames.items_id'   => new QueryExpression($DB->quoteName('glpi_networkports.id')),
                   'glpi_networkports.itemtype'   => $baseitemtype
                ];
                $condition['FIELDS']
                           = ['glpi_networknames' => 'id'];
                $table     = ['glpi_networknames', 'glpi_networkports'];
                $field     = 'glpi_networkports.items_id';
                break;

            case 'IPAddress':
                $condition = [
                   'glpi_ipaddresses.is_dynamic'   => 1,
                   'glpi_ipaddresses.is_deleted'   => 1,
                   'glpi_ipaddresses.itemtype'     => 'NetworkName',
                   'glpi_ipaddresses.items_id'     => 'glpi_networknames.id',
                   'glpi_networknames.itemtype'    => 'NetworkPort',
                   'glpi_networknames.items_id'    => 'glpi_networkports.id',
                   'glpi_networkports.itemtype'    => $baseitemtype];
                $condition['FIELDS']
                           = ['glpi_ipaddresses' => 'id'];
                $table     = ['glpi_ipaddresses', 'glpi_networknames', 'glpi_networkports'];
                $field     = 'glpi_networkports.items_id';
                break;

            case 'Item_Disk':
                $condition = [
                   'is_dynamic' => 1,
                   'is_deleted' => 1,
                   'itemtype'   => $itemtype
                ];
                $table     = Item_Disk::getTable();
                $field     = 'items_id';
                break;

            case 'ComputerVirtualMachine':
                $condition = [
                   'is_dynamic' => 1,
                   'is_deleted' => 1,
                   'itemtype'   => $itemtype];
                $table     = 'glpi_computervirtualmachines';
                $field     = 'computers_id';
                break;

            case 'SoftwareVersion':
                $condition = [
                   'is_dynamic' => 1,
                   'is_deleted' => 1,
                   'itemtype'   => $itemtype];
                $table     = 'glpi_items_softwareversions';
                $field     = 'items_id';
                $type      = 'Item_SoftwareVersion';
                break;

            default:
                // Devices
                if (preg_match('/^Item\_Device/', $itemtype)) {
                    $condition = ['itemtype'   => $baseitemtype,
                                       'is_dynamic' => 1,
                                       'is_deleted' => 1];
                    $table     = getTableForItemType($itemtype);
                    $field     = 'items_id';
                }
        }

        return ['condition' => $condition,
                     'table'     => $table,
                     'field'     => $field,
                     'type'      => $type];
    }


    /**
     * @since 0.85
     *
     * @see CommonDBTM::getMassiveActionsForItemtype()
     **/
    public static function getMassiveActionsForItemtype(
        array &$actions,
        $itemtype,
        $is_deleted = 0,
        CommonDBTM|null $checkitem = null
    ) {

        $action_name = __CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'unlock';

        if (
            Session::haveRight('computer', UPDATE)
            && ($itemtype == 'Computer')
        ) {
            $actions[$action_name] = __('Unlock components');
        }
    }


    /**
     * @since 0.85
     *
     * @see CommonDBTM::showMassiveActionsSubForm()
     **/
    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {

        switch ($ma->getAction()) {
            case 'unlock':
                $types = ['Monitor'                => _n('Monitor', 'Monitors', Session::getPluralNumber()),
                               'Peripheral'             => Peripheral::getTypeName(Session::getPluralNumber()),
                               'Printer'                => Printer::getTypeName(Session::getPluralNumber()),
                               'SoftwareVersion'        => SoftwareVersion::getTypeName(Session::getPluralNumber()),
                               'NetworkPort'            => NetworkPort::getTypeName(Session::getPluralNumber()),
                               'NetworkName'            => NetworkName::getTypeName(Session::getPluralNumber()),
                               'IPAddress'              => IPAddress::getTypeName(Session::getPluralNumber()),
                               'Item_Disk'              => Item_Disk::getTypeName(Session::getPluralNumber()),
                               'Device'                 => _n('Component', 'Components', Session::getPluralNumber()),
                               'ComputerVirtualMachine' => ComputerVirtualMachine::getTypeName(Session::getPluralNumber())];

                renderTwigTemplate('macros/wrappedInput.twig', [
                   'title' => __('Select the type of the item that must be unlock'),
                   'input' => [
                      'type' => 'checklist',
                      'name' => 'attached_item',
                      'options' => $types,
                      'col_lg' => 12,
                      'col_md' => 12,
                   ]
                ]);
                echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction', 'class' => 'btn btn-secondary']);
                return true;
        }
        return false;
    }


    /**
     * @since 0.85
     *
     * @see CommonDBTM::processMassiveActionsForOneItemtype()
     **/
    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $baseitem,
        array $ids
    ) {
        global $DB;

        switch ($ma->getAction()) {
            case 'unlock':
                $input = $ma->getInput();
                if (isset($input['attached_item'])) {
                    $attached_items = $input['attached_item'];
                    if (($device_key = array_search('Device', $attached_items)) !== false) {
                        unset($attached_items[$device_key]);
                        $attached_items = array_merge($attached_items, Item_Devices::getDeviceTypes());
                    }
                    $links = [];
                    foreach ($attached_items as $attached_item) {
                        $infos = self::getLocksQueryInfosByItemType($attached_item, $baseitem->getType());
                        if ($item = getItemForItemtype($infos['type'])) {
                            $infos['item'] = $item;
                            $links[$attached_item] = $infos;
                        }
                    }
                    foreach ($ids as $id) {
                        $action_valid = false;
                        foreach ($links as $infos) {
                            $infos['condition'][$infos['field']] = $id;
                            $locked_items = $DB->request($infos['table'], $infos['condition']);

                            if ($locked_items->count() === 0) {
                                $action_valid = true;
                                continue;
                            }
                            foreach ($locked_items as $data) {
                                // Restore without history
                                $action_valid = $infos['item']->restore(['id' => $data['id']]);
                            }
                        }

                        $baseItemType = $baseitem->getType();
                        if ($action_valid) {
                            $ma->itemDone($baseItemType, $id, MassiveAction::ACTION_OK);
                        } else {
                            $ma->itemDone($baseItemType, $id, MassiveAction::ACTION_KO);

                            $erroredItem = new $baseItemType();
                            $erroredItem->getFromDB($id);
                            $ma->addMessage($erroredItem->getErrorMessage(ERROR_ON_ACTION));
                        }
                    }
                }
                return;
        }
    }
}
