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
 * Computer_Item Class
 *
 * Relation between Computer and Items (monitor, printer, phone, peripheral only)
**/
class Computer_Item extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1          = 'Computer';
    public static $items_id_1          = 'computers_id';

    public static $itemtype_2          = 'itemtype';
    public static $items_id_2          = 'items_id';
    public static $checkItem_2_Rights  = self::HAVE_VIEW_RIGHT_ON_ITEM;


    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    /**
     * Count connection for a Computer and an itemtype
     *
     * @since 0.84
     *
     * @param $comp   Computer object
     * @param $item   CommonDBTM object
     *
     * @return integer: count
    **/
    public static function countForAll(Computer $comp, CommonDBTM $item)
    {

        return countElementsInTable(
            'glpi_computers_items',
            ['computers_id' => $comp->getField('id'),
                                     'itemtype'     => $item->getType(),
                                     'items_id'     => $item->getField('id')]
        );
    }


    public function prepareInputForAdd($input)
    {
        global $CFG_GLPI;

        $item = static::getItemFromArray(static::$itemtype_2, static::$items_id_2, $input);
        if (
            !($item instanceof CommonDBTM)
            || (($item->getField('is_global') == 0)
                && ($this->countForItem($item) > 0))
        ) {
            return false;
        }

        $comp = static::getItemFromArray(static::$itemtype_1, static::$items_id_1, $input);
        if (
            !($comp instanceof Computer)
            || (self::countForAll($comp, $item) > 0)
        ) {
            // no duplicates
            return false;
        }

        if (!$item->getField('is_global')) {
            // Autoupdate some fields - should be in post_addItem (here to avoid more DB access)
            $updates = [];

            if (
                $CFG_GLPI["is_location_autoupdate"]
                && ($comp->fields['locations_id'] != $item->getField('locations_id'))
            ) {
                $updates['locations_id'] = addslashes($comp->fields['locations_id']);
                Session::addMessageAfterRedirect(
                    __('Location updated. The connected items have been moved in the same location.'),
                    true
                );
            }
            if (
                ($CFG_GLPI["is_user_autoupdate"]
                 && ($comp->fields['users_id'] != $item->getField('users_id')))
                || ($CFG_GLPI["is_group_autoupdate"]
                    && ($comp->fields['groups_id'] != $item->getField('groups_id')))
            ) {
                if ($CFG_GLPI["is_user_autoupdate"]) {
                    $updates['users_id'] = $comp->fields['users_id'];
                }
                if ($CFG_GLPI["is_group_autoupdate"]) {
                    $updates['groups_id'] = $comp->fields['groups_id'];
                }
                Session::addMessageAfterRedirect(
                    __('User or group updated. The connected items have been moved in the same values.'),
                    true
                );
            }

            if (
                $CFG_GLPI["is_contact_autoupdate"]
                && (($comp->fields['contact'] != $item->getField('contact'))
                    || ($comp->fields['contact_num'] != $item->getField('contact_num')))
            ) {
                $updates['contact']     = addslashes($comp->fields['contact']);
                $updates['contact_num'] = addslashes($comp->fields['contact_num']);
                Session::addMessageAfterRedirect(
                    __('Alternate username updated. The connected items have been updated using this alternate username.'),
                    true
                );
            }

            if (
                ($CFG_GLPI["state_autoupdate_mode"] < 0)
                && ($comp->fields['states_id'] != $item->getField('states_id'))
            ) {
                $updates['states_id'] = $comp->fields['states_id'];
                Session::addMessageAfterRedirect(
                    __('Status updated. The connected items have been updated using this status.'),
                    true
                );
            }

            if (
                ($CFG_GLPI["state_autoupdate_mode"] > 0)
                && ($item->getField('states_id') != $CFG_GLPI["state_autoupdate_mode"])
            ) {
                $updates['states_id'] = $CFG_GLPI["state_autoupdate_mode"];
            }

            if (count($updates)) {
                $updates['id'] = $input['items_id'];
                $history = true;
                if (isset($input['_no_history']) && $input['_no_history']) {
                    $history = false;
                }
                $item->update($updates, $history);
            }
        }
        return parent::prepareInputForAdd($input);
    }


    public function cleanDBonPurge()
    {
        global $CFG_GLPI;

        if (!isset($this->input['_no_auto_action'])) {
            //Get the computer name
            $computer = new Computer();
            $computer->getFromDB($this->fields['computers_id']);

            //Get device fields
            if ($device = getItemForItemtype($this->fields['itemtype'])) {
                if ($device->getFromDB($this->fields['items_id'])) {
                    if (!$device->getField('is_global')) {
                        $updates = [];
                        if ($CFG_GLPI["is_location_autoclean"] && $device->isField('locations_id')) {
                            $updates['locations_id'] = 0;
                        }
                        if ($CFG_GLPI["is_user_autoclean"] && $device->isField('users_id')) {
                            $updates['users_id'] = 0;
                        }
                        if ($CFG_GLPI["is_group_autoclean"] && $device->isField('groups_id')) {
                            $updates['groups_id'] = 0;
                        }
                        if ($CFG_GLPI["is_contact_autoclean"] && $device->isField('contact')) {
                            $updates['contact'] = "";
                        }
                        if ($CFG_GLPI["is_contact_autoclean"] && $device->isField('contact_num')) {
                            $updates['contact_num'] = "";
                        }
                        if (
                            ($CFG_GLPI["state_autoclean_mode"] < 0)
                            && $device->isField('states_id')
                        ) {
                            $updates['states_id'] = 0;
                        }

                        if (
                            ($CFG_GLPI["state_autoclean_mode"] > 0)
                            && $device->isField('states_id')
                            && ($device->getField('states_id') != $CFG_GLPI["state_autoclean_mode"])
                        ) {
                            $updates['states_id'] = $CFG_GLPI["state_autoclean_mode"];
                        }

                        if (count($updates)) {
                            $updates['id'] = $this->fields['items_id'];
                            $device->update($updates);
                        }
                    }
                }
            }
        }
    }


    public static function getMassiveActionsForItemtype(
        array &$actions,
        $itemtype,
        $is_deleted = 0,
        ?CommonDBTM $checkitem = null
    ) {

        $action_prefix = __CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR;
        $specificities = self::getRelationMassiveActionsSpecificities();

        if (in_array($itemtype, $specificities['itemtypes'])) {
            $actions[$action_prefix . 'add']    = "<i class='ma-icon fas fa-plug' aria-hidden='true'></i>" .
                                                _x('button', 'Connect');
            $actions[$action_prefix . 'remove'] = _x('button', 'Disconnect');
        }
        parent::getMassiveActionsForItemtype($actions, $itemtype, $is_deleted, $checkitem);
    }


    public static function getRelationMassiveActionsSpecificities()
    {

        $specificities              = parent::getRelationMassiveActionsSpecificities();

        $specificities['itemtypes'] = ['Monitor', 'Peripheral', 'Phone', 'Printer'];

        $specificities['select_items_options_2']['entity_restrict'] = $_SESSION['glpiactive_entity'];
        $specificities['select_items_options_2']['onlyglobal']      = true;

        $specificities['only_remove_all_at_once']                   = true;

        // Set the labels for add_item and remove_item
        $specificities['button_labels']['add']                      = _sx('button', 'Connect');
        $specificities['button_labels']['remove']                   = _sx('button', 'Disconnect');

        return $specificities;
    }


    /**
    * Disconnect an item to its computer
    *
    * @param $item    CommonDBTM object: the Monitor/Phone/Peripheral/Printer
    *
    * @return boolean : action succeeded
    */
    public function disconnectForItem(CommonDBTM $item)
    {
        global $DB;

        if ($item->getField('id')) {
            $iterator = $DB->request([
               'SELECT' => ['id'],
               'FROM'   => $this->getTable(),
               'WHERE'  => [
                  'itemtype'  => $item->getType(),
                  'items_id'  => $item->getID()
               ]
            ]);

            if (count($iterator) > 0) {
                $ok = true;
                while ($data = $iterator->next()) {
                    if ($this->can($data["id"], UPDATE)) {
                        $ok &= $this->delete($data);
                    }
                }
                return $ok;
            }
        }
        return false;
    }


    /**
     *
     * Print the form for computers or templates connections to printers, screens or peripherals
     *
     * @param Computer $comp         Computer object
     * @param boolean  $withtemplate Template or basic item (default 0)
     *
     * @return void
    **/
    public static function showForComputer(Computer $comp, $withtemplate = 0)
    {
        global $CFG_GLPI;

        $ID      = $comp->fields['id'];
        $canedit = $comp->canEdit($ID);

        $datas = [];
        $used  = [];
        foreach ($CFG_GLPI["directconnect_types"] as $itemtype) {
            $item = new $itemtype();
            if ($item->canView()) {
                $iterator = self::getTypeItems($ID, $itemtype);

                while ($data = $iterator->next()) {
                    $data['assoc_itemtype'] = $itemtype;
                    $datas[]           = $data;
                    $used[$itemtype][] = $data['id'];
                }
            }
        }
        $AjaxUsedData = json_encode($used);
        $number = count($datas);

        if (
            $canedit
            && !(!empty($withtemplate) && ($withtemplate == 2))
        ) {
            $valuesForDropdown = [];
            foreach ($CFG_GLPI['directconnect_types'] as $type) {
                if ($item = getItemForItemtype($type)) {
                    if (!$item->canView()) {
                        continue;
                    }
                    $valuesForDropdown[$type] = $item->getTypeName(1);
                }
            }
            $form = [
               'action' => Toolbox::getItemTypeFormURL(__CLASS__),
               'buttons' => [
                  [
                     'type'  => 'submit',
                     'name'  => 'add',
                     'value' => _sx('button', 'Connect'),
                     'class' => 'btn btn-secondary',
                  ]
                  ],
                  'content' => [
                     __('Connect an item') => [
                        'visible' => true,
                        'inputs' => [
                           [
                              'type'  => 'hidden',
                              'name'  => 'computers_id',
                              'value' => $comp->fields['id'],
                           ],
                           [
                              'type'  => 'hidden',
                              'name'  => 'itemtype',
                              'value' => $comp::class,
                           ],
                           [
                              'type'  => 'hidden',
                              'name'  => 'items_id',
                              'value' => $comp->getID(),
                           ],
                           !empty($withtemplate) ? [
                              'type'  => 'hidden',
                              'name'  => '_no_history',
                              'value' => '1',
                           ] : [],
                           __('Device type') => [
                              'type' => 'select',
                              'name' => 'itemtype',
                              'id' => 'ItemTypeConnectDropdown',
                              'values' => array_merge([ Dropdown::EMPTY_VALUE ], $valuesForDropdown),
                              'value' => '',
                              'col_lg' => 6,
                              'hooks' => [
                                 'change' => <<<JS
                                 $('select[name="items_id"]').empty();
                                 if ($('select[name="itemtype"]').val() == 0) {
                                    $('select[name="items_id"]').prop('disabled', true);
                                 } else {
                                    $('select[name="items_id"]').prop('disabled', false);
                                 }
                                 $.ajax({
                                    url: '{$CFG_GLPI['root_doc']}/ajax/dropdownConnect.php',
                                    type: 'POST',
                                    data: {
                                       itemtype: $('select[name="itemtype"]').val(),
                                       fromtype: 'Computer',
                                       value: 0,
                                       myname: 'items_id',
                                       onlyglobal: '',
                                       'used': JSON.parse('{$AjaxUsedData}'),
                                       entity_restrict: {$comp->getEntityID()},
                                    },
                                    dataType: 'json',
                                    success: function(data) {
                                       $.each(data, function(key, value) {
                                          if (typeof value === 'object') {
                                             const group = $('#ItemConnectDropdown')
                                                .append("<optgroup label='" + key + "'></optgroup>");
                                             for (const j in value) {
                                                group.append("<option value='" + j + "'>" + value[j] + "</option>");
                                             }
                                          } else {
                                             $('#ItemConnectDropdown').append("<option value='" + key + "'>" + value + "</option>");
                                          }

                                       });
                                    }
                                 });
                              JS,
                              ]
                           ],
                           __('Device') => [
                              'type' => 'select',
                              'id' => 'ItemConnectDropdown',
                              'name' => 'items_id',
                              'values' => [],
                              'value' => '',
                              'col_lg' => 6,
                              'disabled' => true,
                           ],
                        ],
                     ]
                  ]
            ];
            renderTwigForm($form);
        }

        if ($number) {
            if ($canedit) {
                $massiveactionparams                   = [
                   'container' => "ComputerConnectionTable",
                   'display_arrow' => false,
                   'specific_actions' => [
                      'purge' => __('Disconnect'),
                   ],
                   'is_deleted' => false,
                ];

                Html::showMassiveActions($massiveactionparams);
            }
            $fields = [
               _n('Type', 'Types', 1),
               __('Name'),
               Entity::getTypeName(1),
               __('Serial number'),
               __('Inventory number'),
            ];
            $values = [];
            $massiveActionValues = [];
            foreach ($datas as $data) {
                $linkname = $data["name"];
                if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                    $linkname = sprintf(__('%1$s (%2$s)'), $linkname, $data["id"]);
                }
                $link = $data['assoc_itemtype']::getFormURLWithID($data["id"]);
                $massiveActionValues[$data['id']] = 'item[Computer_Item][' . $data['linkid'] . ']';
                $values[$data['id']] = [
                   $data['assoc_itemtype']::getTypeName(1),
                   "<a href=\"" . $link . "\">" . $linkname . "</a>",
                   Dropdown::getDropdownName("glpi_entities", $data['entities_id']),
                   (isset($data["serial"]) ? "" . $data["serial"] . "" : "-"),
                   (isset($data["otherserial"]) ? "" . $data["otherserial"] . "" : "-"),
                ];
                if (Plugin::haveImport()) {
                    $values[$data['id']][6] = Dropdown::getYesNo($data[static::getTable() . '_is_dynamic']);
                }
            }
            $twig_vars = [
               'id' => 'ComputerConnectionTable',
               'fields' => $fields,
               'values' => $values,
               'massive_action' => $massiveActionValues,
            ];
            if (Plugin::haveImport()) {
                $twig_vars['fields'][] = __('Automatic inventory');
            }
            renderTwigTemplate('table.twig', $twig_vars);
        }
    }


    /**
     * Prints a direct connection to a computer
     *
     * @param $item                     CommonDBTM object: the Monitor/Phone/Peripheral/Printer
     * @param $withtemplate    integer  withtemplate param (default 0)
     *
     * @return void
    **/
    public static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {
        // Prints a direct connection to a computer
        global $DB;

        $comp   = new Computer();
        $ID     = $item->getField('id');

        if (!$item->can($ID, READ)) {
            return;
        }
        $canedit = $item->canEdit($ID);
        $rand    = mt_rand();

        // Is global connection ?
        $global  = $item->getField('is_global');

        $used    = [];
        $compids = [];
        $dynamic = [];
        $result = $DB->request(
            [
              'SELECT' => ['id', 'computers_id', 'is_dynamic'],
              'FROM'   => self::getTable(),
              'WHERE'  => [
                 'itemtype'   => $item->getType(),
                 'items_id'   => $ID,
                 'is_deleted' => 0,
              ]
            ]
        );
        foreach ($result as $data) {
            $compids[$data['id']] = $data['computers_id'];
            $dynamic[$data['id']] = $data['is_dynamic'];
            $used['Computer'][]   = $data['computers_id'];
        }
        $number = count($compids);
        if (
            $canedit
            && ($global || !$number)
            && !(!empty($withtemplate) && ($withtemplate == 2))
        ) {
            $form = [
               'action' => Toolbox::getItemTypeFormURL(__CLASS__),
               'buttons' => [
                   [
                       'type'  => 'submit',
                       'name'  => 'add',
                       'value' => _sx('button', 'Connect'),
                       'class' => 'btn btn-secondary',
                   ]
               ],
               'content' => [
                   '' => [
                       'visible' => true,
                       'inputs' => [
                           Computer::getTypeName() => [
                               'type' => 'select',
                               'name' => 'computers_id',
                               'values' => getItemByEntity(Computer::class, $item->fields['entities_id']),
                               'col_lg' => 12,
                               'col_md' => 12,
                           ],
                           [
                               'type'  => 'hidden',
                               'name'  => 'itemtype',
                               'value' => $item->getType(),
                           ],
                           [
                               'type'  => 'hidden',
                               'name'  => 'items_id',
                               'value' => $ID,
                           ],
                       ]
                   ]
               ],
            ];
            renderTwigForm($form);
        }

        echo "<div class='spaced'>";
        $massActionId = 'mass' . __CLASS__ . $rand;
        if ($canedit && $number) {
            $massiveactionparams = [
               'num_displayed' => min($_SESSION['glpilist_limit'], $number),
               'specific_actions' => ['purge' => _x('button', 'Disconnect')],
               'container' => 'mass' . __CLASS__ . $rand,
               'display_arrow' => false,
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        if ($number > 0) {
            $fields = [
                'name' => __('Name'),
                'entity' => Entity::getTypeName(1),
                'serial' => __('Serial number'),
               'otherserial' =>  __('Inventory number'),
            ];
            if (Plugin::haveImport()) {
                $fields['inventory'] = __('Automatic inventory');
            }
            $values = [];
            $massiveActionValues = [];
            foreach ($compids as $key => $compid) {
                $comp->getFromDB($compid);


                if ($canedit) {
                    $massiveActionValues[$key] = 'item[Computer_Item][' . $key . ']';
                }
                $newValue = [
                    'name' => $comp->getLink(),
                    'entity' => Dropdown::getDropdownName("glpi_entities", $comp->getField('entities_id')),
                    'serial' => $comp->getField('serial'),
                    'otherserial' => $comp->getField('otherserial'),
                ];
                if (Plugin::haveImport()) {
                    $newValue['inventory'] = Dropdown::getYesNo($dynamic[$key]);
                }
                $values[$key] = $newValue;
            }
            renderTwigTemplate('table.twig', [
               'id' => $massActionId,
               'fields' => $fields,
               'values' => $values,
               'massive_action' => $massiveActionValues,
            ]);
        } else {
            echo "<tr><td class='tab_bg_1 b'><i>" . __('Not connected') . "</i>";
            echo "</td></tr>";
        }
    }


    /**
     * Unglobalize an item : duplicate item and connections
     *
     * @param $item   CommonDBTM object to unglobalize
    **/
    public static function unglobalizeItem(CommonDBTM $item)
    {
        global $DB;

        // Update item to unit management :
        if ($item->getField('is_global')) {
            $input = ['id'        => $item->fields['id'],
                           'is_global' => 0];
            $item->update($input);

            // Get connect_wire for this connection
            $iterator = $DB->request([
               'SELECT' => ['id'],
               'FROM'   => self::getTable(),
               'WHERE'  => [
                  'items_id'  => $item->getID(),
                  'itemtype'  => $item->getType()
               ]
            ]);

            $first = true;
            while ($data = $iterator->next()) {
                if ($first) {
                    $first = false;
                    unset($input['id']);
                    $conn = new self();
                } else {
                    $temp = clone $item;
                    unset($temp->fields['id']);
                    if ($newID = $temp->add($temp->fields)) {
                        $conn->update(['id'       => $data['id'],
                                       'items_id' => $newID]);
                    }
                }
            }
        }
    }


    /**
    * Make a select box for connections
    *
    * @since 0.84
    *
    * @param string            $fromtype        from where the connection is
    * @param string            $myname          select name
    * @param integer|integer[] $entity_restrict Restrict to a defined entity (default = -1)
    * @param boolean           $onlyglobal      display only global devices (used for templates) (default 0)
    * @param integer[]         $used            Already used items ID: not to display in dropdown
    *
    * @return integer Random generated number used for select box ID (select box HTML is printed)
    */
    public static function dropdownAllConnect(
        $fromtype,
        $myname,
        $entity_restrict = -1,
        $onlyglobal = 0,
        $used = []
    ) {
        global $CFG_GLPI;

        $rand = mt_rand();

        $options               = [];
        $options['checkright'] = true;
        $options['name']       = 'itemtype';

        $rand = Dropdown::showItemType($CFG_GLPI['directconnect_types'], $options);
        if ($rand) {
            $params = ['itemtype'        => '__VALUE__',
                            'fromtype'        => $fromtype,
                            'value'           => 0,
                            'myname'          => $myname,
                            'onlyglobal'      => $onlyglobal,
                            'entity_restrict' => $entity_restrict,
                            'used'            => $used];

            if ($onlyglobal) {
                $params['condition'] = ['is_global' => 1];
            }
            Ajax::updateItemOnSelectEvent(
                "dropdown_itemtype$rand",
                "show_$myname$rand",
                $CFG_GLPI["root_doc"] . "/ajax/dropdownConnect.php",
                $params
            );

            echo "<br><div id='show_$myname$rand'>&nbsp;</div>\n";
        }
        return $rand;
    }


    /**
    * Make a select box for connections
    *
    * @param string            $itemtype        type to connect
    * @param string            $fromtype        from where the connection is
    * @param string            $myname          select name
    * @param integer|integer[] $entity_restrict Restrict to a defined entity (default = -1)
    * @param boolean           $onlyglobal      display only global devices (used for templates) (default 0)
    * @param integer[]         $used            Already used items ID: not to display in dropdown
    *
    * @return integer Random generated number used for select box ID (select box HTML is printed)
    */
    public static function dropdownConnect(
        $itemtype,
        $fromtype,
        $myname,
        $entity_restrict = -1,
        $onlyglobal = 0,
        $used = []
    ) {
        global $CFG_GLPI;

        $rand     = mt_rand();

        $field_id = Html::cleanId("dropdown_" . $myname . $rand);
        $param    = [
           'entity_restrict' => $entity_restrict,
           'fromtype'        => $fromtype,
           'itemtype'        => $itemtype,
           'onlyglobal'      => $onlyglobal,
           'used'            => $used,
           '_idor_token'     => Session::getNewIDORToken($itemtype, [
              'entity_restrict' => $entity_restrict,
           ]),
        ];

        echo Html::jsAjaxDropdown(
            $myname,
            $field_id,
            $CFG_GLPI['root_doc'] . "/ajax/getDropdownConnect.php",
            $param
        );

        return $rand;
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        // can exists for Template
        if ($item->can($item->getField('id'), READ)) {
            $nb = 0;
            switch ($item->getType()) {
                case 'Phone':
                case 'Printer':
                case 'Peripheral':
                case 'Monitor':
                    if (Computer::canView()) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            $nb = self::countForItem($item);
                        }
                        return self::createTabEntry(
                            _n('Connection', 'Connections', Session::getPluralNumber()),
                            $nb
                        );
                    }
                    break;

                case 'Computer':
                    if (
                        Phone::canView()
                        || Printer::canView()
                        || Peripheral::canView()
                        || Monitor::canView()
                    ) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            $nb = self::countForMainItem($item);
                        }
                        return self::createTabEntry(
                            _n('Connection', 'Connections', Session::getPluralNumber()),
                            $nb
                        );
                    }
                    break;
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'Phone':
            case 'Printer':
            case 'Peripheral':
            case 'Monitor':
                self::showForItem($item, $withtemplate);
                return true;

            case 'Computer':
                self::showForComputer($item, $withtemplate);
                return true;
        }
    }


    /**
     * Duplicate connected items to computer from an item template to its clone
     *
     * @deprecated 9.5
     * @since 0.84
     *
     * @param integer $oldid ID of the item to clone
     * @param integer $newid ID of the item cloned
    **/
    public static function cloneComputer($oldid, $newid)
    {
        global $DB;

        Toolbox::deprecated('Use clone');
        $iterator = $DB->request([
           'FROM'   => self::getTable(),
           'WHERE'  => ['computers_id' => $oldid]
        ]);

        while ($data = $iterator->next()) {
            $conn = new Computer_Item();
            $conn->add(['computers_id' => $newid,
                        'itemtype'     => $data["itemtype"],
                        'items_id'     => $data["items_id"]]);
        }
    }


    /**
     * Duplicate connected items to item from an item template to its clone
     *
     * @deprecated 9.5
     * @since 0.83.3
     *
     * @param string  $itemtype type of the item to clone
     * @param integer $oldid    ID of the item to clone
     * @param integer $newid    ID of the item cloned
    **/
    public static function cloneItem($itemtype, $oldid, $newid)
    {
        global $DB;

        Toolbox::deprecated('Use clone');
        $iterator = $DB->request([
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'itemtype'  => $itemtype,
              'items_id'  => $oldid
           ]
        ]);

        while ($data = $iterator->next()) {
            $conn = new self();
            $conn->add(['computers_id' => $data["computers_id"],
                        'itemtype'     => $data["itemtype"],
                        'items_id'     => $newid]);
        }
    }


    /**
     * @since 9.1.7
     *
     * @param CommonDBTM $item     item linked to the computer to check
     * @param integer[]  $entities entities to check
     *
     * @return boolean
    **/
    public static function canUnrecursSpecif(CommonDBTM $item, $entities)
    {
        global $DB;

        if ($item instanceof Computer) {
            // RELATION : items -> computers
            $iterator = $DB->request([
               'SELECT' => [
                  'itemtype',
                  new \QueryExpression('GROUP_CONCAT(DISTINCT ' . $DB->quoteName('items_id') . ') AS ids'),
               ],
               'FROM' => self::getTable(),
               'WHERE' => [
                  'computers_id' => $item->fields['id']
               ],
               'GROUP' => 'itemtype'
            ]);

            while ($data = $iterator->next()) {
                if (!class_exists($data['itemtype'])) {
                    continue;
                }
                if (
                    countElementsInTable(
                        $data['itemtype']::getTable(),
                        [
                         'id' => $data['ids'],
                         'NOT' => ['entities_id' => $entities]
                        ]
                    ) > 0
                ) {
                    return false;
                }
            }
        } else {
            // RELATION : computers -> items
            $iterator = $DB->request([
               'SELECT' => [
                  'itemtype',
                  new \QueryExpression('GROUP_CONCAT(DISTINCT ' . $DB->quoteName('items_id') . ') AS ids'),
                  'computers_id'
               ],
               'FROM' => self::getTable(),
               'WHERE' => [
                  'itemtype' => $item->getType(),
                  'items_id' => $item->fields['id']
               ],
               'GROUP' => 'itemtype'
            ]);

            while ($data = $iterator->next()) {
                if (
                    countElementsInTable(
                        "glpi_computers",
                        ['id' => $data["computers_id"],
                         'NOT' => ['entities_id' => $entities]]
                    ) > 0
                ) {
                    return false;
                }
            }
        }

        return true;
    }


    protected static function getListForItemParams(CommonDBTM $item, $noent = false)
    {
        $params = parent::getListForItemParams($item, $noent);
        $params['WHERE'][self::getTable() . '.is_deleted'] = 0;
        return $params;
    }

    /**
     * Get SELECT param for getTypeItemsQueryParams
     *
     * @param CommonDBTM $item
     *
     * @return array
     */
    public static function getTypeItemsQueryParams_Select(CommonDBTM $item): array
    {
        $table = static::getTable();
        $select = parent::getTypeItemsQueryParams_Select($item);
        $select[] = "$table.is_dynamic AS {$table}_is_dynamic";

        return $select;
    }
}
