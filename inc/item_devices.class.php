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

/**
 * @since 0.84
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Relation between item and devices
 * We completely relies on CommonDBConnexity to manage the can* and the history and the deletion ...
 **/
class Item_Devices extends CommonDBRelation
{
    public static $itemtype_1            = 'itemtype';
    public static $items_id_1            = 'items_id';
    public static $mustBeAttached_1      = false;
    public static $take_entity_1         = false;
    // static public $checkItem_1_Rights    = self::DONT_CHECK_ITEM_RIGHTS;

    protected static $notable            = true;

    public static $logs_for_item_2       = false;
    public static $take_entity_2         = true;

    public static $log_history_1_add     = Log::HISTORY_ADD_DEVICE;
    public static $log_history_1_update  = Log::HISTORY_UPDATE_DEVICE;
    public static $log_history_1_delete  = Log::HISTORY_DELETE_DEVICE;
    public static $log_history_1_lock    = Log::HISTORY_LOCK_DEVICE;
    public static $log_history_1_unlock  = Log::HISTORY_UNLOCK_DEVICE;

    // This var is defined by CommonDBRelation ...
    public $no_form_page                 = false;

    public $dohistory = true;

    protected static $forward_entity_to  = ['Infocom'];

    public static $undisclosedFields      = [];

    public static $mustBeAttached_2 = false; // Mandatory to display creation form

    protected function computeFriendlyName()
    {
        $itemtype = static::$itemtype_2;
        if (!empty($this->fields[static::$itemtype_1])) {
            $item = new $this->fields[static::$itemtype_1]();
            $item->getFromDB($this->fields[static::$items_id_1]);
            $name = sprintf(__('%1$s of item "%2$s"'), $itemtype::getTypeName(1), $item->getName());
        } else {
            $name = $itemtype::getTypeName(1);
        }
        return $name;
    }

    public static function getTypeName($nb = 0)
    {
        $device_type = static::getDeviceType();
        return class_exists($device_type) ? $device_type::getTypeName($nb) : $device_type;
    }


    /**
     * Get type name for device (used in Log)
     *
     * @param integer $nb Count
     *
     * @return string
     */
    public static function getDeviceTypeName($nb = 0)
    {
        $device_type = static::getDeviceType();
        //TRANS: %s is the type of the component
        return sprintf(__('Item - %s link'), $device_type::getTypeName($nb));
    }


    public function getForbiddenStandardMassiveAction()
    {

        $forbidden = parent::getForbiddenStandardMassiveAction();

        if (
            (count(static::getSpecificities()) == 0)
            && !Infocom::canApplyOn($this)
        ) {
            $forbidden[] = 'update';
        }

        return $forbidden;
    }

    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
           'id'                 => 'common',
           'name'               => __('Characteristics')
        ];

        $tab[] = [
           'id'                 => '1',
           'table'              => $this->getTable(),
           'field'              => 'id',
           'name'               => __('ID'),
           'datatype'           => 'itemlink',
           'massiveaction'      => false,
        ];

        $deviceType = $this->getDeviceType();
        $tab[] = [
           'id'                 => '4',
           'table'              => getTableForItemType($deviceType),
           'field'              => 'designation',
           'name'               => $deviceType::getTypeName(1),
           'datatype'           => 'itemlink',
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => $this->getTable(),
                 'joinparams'         => [
                    'jointype'           => 'child'
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '5',
           'table'              => $this->getTable(),
           'field'              => 'items_id',
           'name'               => _n('Associated element', 'Associated elements', Session::getPluralNumber()),
           'datatype'           => 'specific',
           'comments'           => true,
           'nosort'             => true,
           'additionalfields'   => ['itemtype']
        ];

        $tab[] = [
           'id'                 => '6',
           'table'              => $this->getTable(),
           'field'              => 'itemtype',
           'name'               => _n('Associated item type', 'Associated item types', Session::getPluralNumber()),
           'datatype'           => 'itemtypename',
           'itemtype_list'      => 'itemdevices_types',
           'nosort'             => true
        ];

        foreach (static::getSpecificities() as $field => $attributs) {
            if ($field == 'contentForm') {
                continue;
            }
            if (isForeignKeyField($field)) {
                $table = getTableNameForForeignKeyField($field);
                $linked_itemtype = getItemTypeForTable($table);
                $field = $linked_itemtype::getNameField();
            } else {
                $table = $this->getTable();
            }
            if (array_key_exists('field', $attributs)) {
                $field = $attributs['field'];
            }

            $newtab = [
               'id'                 => $attributs['id'],
               'table'              => $table,
               'field'              => $field,
               'name'               => $attributs['long name'],
               'massiveaction'      => true
            ];

            if (isset($attributs['datatype'])) {
                $newtab['datatype'] = $attributs['datatype'];
            }
            if (isset($attributs['nosearch'])) {
                $newtab['nosearch'] = $attributs['nosearch'];
            }
            if (isset($attributs['nodisplay'])) {
                $newtab['nodisplay'] = $attributs['nodisplay'];
            }
            if (isset($attributs['autocomplete'])) {
                $newtab['autocomplete'] = $attributs['autocomplete'];
            }
            $tab[] = $newtab;
        }

        $tab[] = [
           'id'                 => '80',
           'table'              => 'glpi_entities',
           'field'              => 'completename',
           'name'               => Entity::getTypeName(1),
           'datatype'           => 'dropdown'
        ];

        return $tab;
    }

    public static function rawSearchOptionsToAdd($itemtype)
    {
        global $CFG_GLPI;

        $options = [];
        $device_types = $CFG_GLPI['device_types'];

        $main_joinparams = [
           'jointype'           => 'itemtype_item',
           'specific_itemtype'  => $itemtype
        ];

        foreach ($device_types as $device_type) {
            if (isset($CFG_GLPI['item' . strtolower($device_type) . '_types'])) {
                $itemtypes = $CFG_GLPI['item' . strtolower($device_type) . '_types'];
                if ($itemtypes == '*' || in_array($itemtype, $itemtypes)) {
                    if (method_exists($device_type, 'rawSearchOptionsToAdd')) {
                        $options = array_merge(
                            $options,
                            $device_type::rawSearchOptionsToAdd(
                                $itemtype,
                                $main_joinparams
                            )
                        );
                    }
                }
            }
        }

        if (count($options)) {
            //add title if there are options
            $options = array_merge(
                [[
                  'id'                => 'devices',
                  'name'              => _n('Component', 'Components', Session::getPluralNumber())
                ]],
                $options
            );
        }

        return $options;
    }

    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'items_id':
                if (isset($values['itemtype'])) {
                    if (isset($options['comments']) && $options['comments']) {
                        $valueData = Dropdown::getDropdownName(
                            getTableForItemType($values['itemtype']),
                            $values[$field],
                            1
                        );
                        return sprintf(
                            __('%1$s %2$s'),
                            $valueData['name'],
                            Html::showToolTip($valueData['comment'], ['display' => false])
                        );
                    }
                    return Dropdown::getDropdownName(
                        getTableForItemType($values['itemtype']),
                        $values[$field]
                    );
                }
                break;
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }

    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;
        switch ($field) {
            case 'items_id':
                if (isset($values['itemtype']) && !empty($values['itemtype'])) {
                    $options['name']  = $name;
                    $options['value'] = $values[$field];
                    return Dropdown::show($values['itemtype'], $options);
                }
                break;
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }


    /**
     * Get the specificities of the given device. For instance, the
     * serial number, the size of the memory, the frequency of the CPUs ...
     *
     * @param $specif   string   specificity to display
     *
     * Should be overloaded by Item_Device*
     *
     * @return array of the specificities: index is the field name and the values are the attributs
     *                                     of the specificity (long name, short name, size)
     **/
    public static function getSpecificities($specif = '')
    {

        switch ($specif) {
            case 'serial':
                return [
                   'long name'  => __('Serial number'),
                   'short name' => __('Serial number'),
                   'size'       => 20,
                   'id'         => 10,
                   'autocomplete' => true,
                   'formContent' => [
                      'type' => 'text',
                   ]
                ];

            case 'busID':
                return [
                   'long name'  => __('Position of the device on its bus'),
                   'short name' => __('bus ID'),
                   'size'       => 10,
                   'id'         => 11,
                   'autocomplete' => true,
                   'formContent' => [
                      'type' => 'text',
                   ]
                ];

            case 'otherserial':
                return [
                   'long name'  => __('Inventory number'),
                   'short name' => __('Inventory number'),
                   'size'       => 20,
                   'id'         => 12,
                   'autocomplete' => true,
                   'formContent' => [
                      'type' => 'text',
                   ]
                ];

            case 'locations_id':
                include_once GLPI_ROOT . "/src/twig/twig.utils.php";
                return [
                   'long name'  => Location::getTypeName(1),
                   'short name' => Location::getTypeName(1),
                   'size'       => 20,
                   'id'         => 13,
                   'datatype'   => 'dropdown',
                   'formContent' => [
                      'type' => 'select',
                      'values' => getOptionForItems(Location::class),
                      'actions' => getItemActionButtons(['info', 'add'], Location::class),
                   ]
                ];

            case 'states_id':
                return [
                   'long name'  => __('Status'),
                   'short name' => __('Status'),
                   'size'       => 20,
                   'id'         => 14,
                   'datatype'   => 'dropdown',
                   'formContent' => [
                      'type' => 'select',
                      'values' => getOptionForItems(State::class),
                      'actions' => getItemActionButtons(['info', 'add'], State::class),
                   ]
                ];
        }
        return [];
    }


    /**
     * Get the items on which this Item_Device can be attached. For instance, a computer can have
     * any kind of device. Conversely, a soundcard does not concern a NetworkEquipment
     * A configuration entry is automatically checked in $CFG_GLPI (must be the name of
     * the class, lowercase, without "_" with extra "_types" at the end; for example
     * "itemdevicesoundcard_types").
     *
     * Alternatively, it could be overloaded from subclasses
     *
     * @since 0.85
     *
     * @return array of the itemtype that can have this Item_Device
     **/
    public static function itemAffinity()
    {
        global $CFG_GLPI;

        $conf_param = str_replace('_', '', strtolower(static::class)) . '_types';
        if (isset($CFG_GLPI[$conf_param])) {
            return $CFG_GLPI[$conf_param];
        }

        return $CFG_GLPI["itemdevices_itemaffinity"];
    }


    /**
     * Get all the kind of devices available inside the system.
     * This method is equivalent to getItemAffinities('')
     *
     * @return array of the types of Item_Device* available
     **/
    public static function getDeviceTypes()
    {
        global $CFG_GLPI;

        // If the size of $CFG_GLPI['item_device_types'] and $CFG_GLPI['device_types'] then,
        // there is new device_types and we must update item_device_types !
        if (
            !isset($CFG_GLPI['item_device_types'])
            || (count($CFG_GLPI['item_device_types']) != count($CFG_GLPI['device_types']))
        ) {
            $CFG_GLPI['item_device_types'] = [];

            foreach (CommonDevice::getDeviceTypes() as $deviceType) {
                $CFG_GLPI['item_device_types'][] = $deviceType::getItem_DeviceType();
            }
        }

        return $CFG_GLPI['item_device_types'];
    }


    /**
     * Get the Item_Device* a given item type can have
     *
     * @param string $itemtype the type of the item that we want to know its devices
     *
     * @since 0.85
     *
     * @return array of Item_Device*
     **/
    public static function getItemAffinities($itemtype)
    {
        global $GLPI_CACHE;

        if (!$GLPI_CACHE->has('item_device_affinities')) {
            $GLPI_CACHE->set('item_device_affinities', ['' => static::getDeviceTypes()]);
        }
        $items_affinities = $GLPI_CACHE->get('item_device_affinities');

        if (!isset($items_affinities[$itemtype])) {
            $afffinities = [];
            foreach ($items_affinities[''] as $item_id => $item_device) {
                if (in_array($itemtype, $item_device::itemAffinity()) || in_array('*', $item_device::itemAffinity())) {
                    $afffinities[$item_id] = $item_device;
                }
            }
            $items_affinities[$itemtype] = $afffinities;
            $GLPI_CACHE->set('item_device_affinities', $items_affinities);
        }

        return $items_affinities[$itemtype];
    }


    /**
     * Get all kind of items that can be used by Item_Device*
     *
     * @since 0.85
     *
     * @return array of the available items
     **/
    public static function getConcernedItems()
    {
        global $CFG_GLPI;

        $itemtypes = $CFG_GLPI['itemdevices_types'];

        $conf_param = str_replace('_', '', strtolower(static::class)) . '_types';
        if (isset($CFG_GLPI[$conf_param]) && !in_array('*', $CFG_GLPI[$conf_param])) {
            $itemtypes = array_intersect($itemtypes, $CFG_GLPI[$conf_param]);
        }

        return $itemtypes;
    }


    /**
     * Get associated device to the current item_device
     *
     * @since 0.85
     *
     * @return string containing the device
     **/
    public static function getDeviceType()
    {

        $devicetype = get_called_class();
        if ($plug = isPluginItemType($devicetype)) {
            return 'Plugin' . $plug['plugin'] . str_replace('Item_', '', $plug['class']);
        }
        return str_replace('Item_', '', $devicetype);
    }

    /**
     * get items associated to the given one (defined by $itemtype and $items_id)
     *
     * @param string  $itemtype          the type of the item we want the resulting items to be associated to
     * @param string  $items_id          the name of the item we want the resulting items to be associated to
     *
     * @return array the items associated to the given one (empty if none was found)
     **/
    public static function getItemsAssociatedTo($itemtype, $items_id)
    {
        $res = [];
        foreach (self::getItemAffinities($itemtype) as $link_type) {
            $table = $link_type::getTable();
            $request = self::getAdapter()->request([
               'SELECT' => 'id',
               'FROM'   => $table,
               'WHERE'  => [
                  'itemtype'  => $itemtype,
                  'items_id'  => $items_id
               ]
            ]);

            while ($row = $request->fetchAssociative()) {
                $input = Toolbox::addslashes_deep($row);
                $item = new $link_type();
                $item->getFromDB($input['id']);
                $res[] = $item;
            }
        }
        return $res;
    }

    /**
     *
     * @deprecated 9.5
     *
     **/
    public static function cloneItem($itemtype, $oldid, $newid)
    {
        global $DB;

        Toolbox::deprecated('Use clone');
        foreach (self::getItemAffinities($itemtype) as $link_type) {
            $table = $link_type::getTable();
            $olds = self::getAdapter()->request([
               'FROM'   => $table,
               'WHERE'  => [
                  'itemtype'  => $itemtype,
                  'items_id'  => $oldid
               ]
            ]);

            while ($data = $olds->fetchAssociative()) {
                $link = new $link_type();
                unset($data['id']);
                $data['items_id']     = $newid;
                $data['_itemtype']    = $itemtype;
                $data['_no_history']  = true;
                $data                 = Toolbox::addslashes_deep($data);

                $link->add($data);
            }
        }
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if ($item->canView()) {
            $nb = 0;
            if (in_array($item->getType(), self::getConcernedItems())) {
                if ($_SESSION['glpishow_count_on_tabs']) {
                    foreach (self::getItemAffinities($item->getType()) as $link_type) {
                        $nb   += countElementsInTable(
                            $link_type::getTable(),
                            [
                              'items_id'   => $item->getID(),
                              'itemtype'   => $item->getType(),
                              'is_deleted' => 0
                            ]
                        );
                    }
                }
                return self::createTabEntry(
                    _n('Component', 'Components', Session::getPluralNumber()),
                    $nb
                );
            }
            if ($item instanceof CommonDevice) {
                if ($_SESSION['glpishow_count_on_tabs']) {
                    $deviceClass     = $item->getType();
                    $linkClass       = $deviceClass::getItem_DeviceType();
                    $table           = $linkClass::getTable();
                    $foreignkeyField = $deviceClass::getForeignKeyField();
                    $nb = countElementsInTable(
                        $table,
                        [
                          $foreignkeyField => $item->getID(),
                          'is_deleted' => 0
                        ]
                    );
                }
                return self::createTabEntry(_n('Item', 'Items', Session::getPluralNumber()), $nb);
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        self::showForItem($item, $withtemplate);
        return true;
    }


    public static function showForItem(CommonGLPI $item, $withtemplate = 0)
    {
        global $CFG_GLPI;

        $is_device = ($item instanceof CommonDevice);

        $ID = $item->getField('id');

        $devtypes = [];
        foreach (self::getItemAffinities($item->getType()) as $link_type) {
            $devtypes[] = $link_type::getDeviceType();
        }

        if (!$item->can($ID, READ)) {
            return false;
        }

        $canedit = (($withtemplate != 2)
           && $item->canEdit($ID)
           && Session::haveRightsOr('device', [UPDATE, PURGE]));

        $entity = isset($item->fields) ? $item->fields['entities_id'] : 0;
        if ($canedit) {
            $dropdownValues = [Dropdown::EMPTY_VALUE];
            foreach ($devtypes ?? [] as $devtype) {
                $dropdownValues[$devtype] = $devtype::getTypeName(1);
            }
            asort($dropdownValues);
            $form = [
               'action' => Toolbox::getItemTypeFormURL(__CLASS__),
               'buttons' => [
                  [
                     'type'  => 'submit',
                     'value' => _sx('button', 'Add'),
                     'name'  => 'add',
                     'class' => 'btn btn-secondary'
                  ]
               ],
               'content' => [
                  __('Add a new component') => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type'  => 'hidden',
                           'name'  => 'items_id',
                           'value' => $ID
                        ],
                        [
                           'type'  => 'hidden',
                           'name'  => 'itemtype',
                           'value' => $item->getType()
                        ],
                        __('Number of components') => ($is_device) ? [
                           'type'  => 'number',
                           'name'  => 'number_devices_to_add',
                           'value' => 0,
                           'min'   => 0,
                           'max'   => 10
                        ] : [],
                        __('Itemtype') => (!$is_device) ? [
                           'type' => 'select',
                           'id' => 'selectForDeviceIdItemDevices',
                           'name' => 'devicetype',
                           'values' => $dropdownValues,
                           'col_lg' => 6,
                           'hooks' => [
                              'change' => <<<JS
                              const val = this.value;
                              $('select[name="devices_id"]').empty();
                              if (val != 0) {
                                 $.ajax({
                                    url: '{$CFG_GLPI['root_doc']}/ajax/dropdownAllItems.php',
                                    data: {
                                       itemtype_name: 'devicetype',
                                       items_id_name: 'devices_id',
                                       idtable: val,
                                       entity_restrict: $entity,
                                    },
                                    type: 'POST',
                                    success: function(data) {
                                       const jsonDatas = JSON.parse(data);
                                       for (const key in jsonDatas) {
                                          if (typeof(jsonDatas[key]) == 'object') {
                                            $('select[name="devices_id"]').append('<optgroup label="' + key + '"></optgroup>');
                                            for (const child in jsonDatas[key]) {
                                              $('select[name="devices_id"] optgroup').last().append('<option value="' + child + '">' + jsonDatas[key][child] + '</option>');
                                            }
                                          } else {
                                              $('select[name="devices_id"]').append('<option value="' + key + '">' + jsonDatas[key] + '</option>');
                                          }
                                       }
                                    }
                                 });
                              }
                              if (val == 0) {
                                 $('input[name="new_devices"]').prop('disabled', true);
                                 $('select[name="devices_id"]').prop('disabled', true);
                                 $('select[name="devices_id"]').empty();
                                 $('#checklistForDeviceIdItemDevices').empty();
                              } else {
                                 $('select[name="devices_id"]').prop('disabled', false);
                              }
                           JS,
                           ]
                        ] : [],
                        __('Component') => (!$is_device) ? [
                           'type' => 'select',
                           'name' => 'devices_id',
                           'id' => 'DropdownForDeviceIdItemDevices',
                           'values' => [],
                           'col_lg' => 6,
                           'disabled' => '',
                           'hooks' => [
                              'change' => <<<JS
                              const val = this.value;
                              $('#checklistForDeviceIdItemDevices').empty();
                              if (val != 0) {
                                 $('input[name="new_devices"]').prop('disabled', false);
                                 $.ajax({
                                    url: '{$CFG_GLPI['root_doc']}/ajax/selectUnaffectedOrNewItem_Device.php',
                                    data: {
                                       itemtype: $('#selectForDeviceIdItemDevices').val(),
                                       items_id: val,
                                    },
                                    type: 'POST',
                                    success: function(data) {
                                       const jsonDatas = JSON.parse(data);
                                       for (const key in jsonDatas.options) {
                                          $('#checklistForDeviceIdItemDevices').append(`
                                             <div class="row">
                                                <input class="form-check-input
                                                   col col-2" type="checkbox" value="` + key + `"
                                                      id="` + jsonDatas.name + `_` + key + `" name="items_` + jsonDatas.name + `[]">
                                                <label class="form-check
                                                   col col-10" for="` + jsonDatas.name + `_` + key + `">
                                                   ` + jsonDatas.options[key] + `
                                                </label>
                                             </div>
                                          `);
                                       }
                                    }
                                 });
                              } else {
                                 $('input[name="new_devices"]').prop('disabled', true);
                              }
                           JS,
                           ]
                        ] : [],
                        __('Choose an existing device') => (!$is_device) ? [
                           'type' => 'checklist',
                           'id' => 'checklistForDeviceIdItemDevices',
                           'col_lg' => 6,
                        ] : [],
                        __('Add new devices') => (!$is_device) ? [
                           'type' => 'number',
                           'name' => 'new_devices',
                           'min' => 0,
                           'max' => 10,
                           'step' => 1,
                           'col_lg' => 6,
                           'disabled' => '',
                        ] : [],
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }

        foreach (self::getItemAffinities($item->getType()) as $link_type) {
            $link = getItemForItemtype($link_type);
            $table = $link->getTable();
            $criteria = [
               'SELECT' => "$table.*",
               'FROM'   => $table
            ];
            if ($is_device) {
                $fk = 'items_id';

                // Entity restrict
                $criteria['WHERE'] = [
                   $link->getDeviceForeignKey()  => $item->getID(),
                   "$table.itemtype"            => $peer_type,
                   "$table.is_deleted"          => 0
                ];
                $criteria['ORDERBY'] = [
                   "$table.itemtype",
                   "$table.$fk"
                ];
                if (!empty($peer_type)) {
                    $criteria['LEFT JOIN'] = [
                       getTableForItemType($peer_type) => [
                          'ON' => [
                             $table                          => 'items_id',
                             getTableForItemType($peer_type)  => 'id', [
                                'AND' => [
                                   "$table.itemtype"   => $peer_type
                                ]
                             ]
                          ]
                       ]
                    ];
                    $criteria['WHERE'] += getEntitiesRestrictCriteria(getTableForItemType($peer_type));
                }
            } else {
                $fk = $link->getDeviceForeignKey();

                $criteria['WHERE'] = [
                   'itemtype'     => $item->getType(),
                   'items_id'     => $item->getID(),
                   'is_deleted'   => 0
                ];
                $criteria['ORDERBY'] = $fk;
            }
            $request = self::getAdapter()->request($criteria);
            $datas = $request->fetchAllAssociative();
            if (count($datas)) {
                $massiveActionContainerId = 'mass' . __CLASS__ . rand();
                if ($canedit) {
                    $params = [
                       'container' => $massiveActionContainerId,
                       'is_deleted' => 0,
                       'display_arrow' => false,
                    ];
                    Html::showMassiveActions($params);
                }
                $columns = $link->getSpecificities();
                $fields = [];
                foreach ($columns as $name => $column) {
                    if ((!isset($column['nodisplay']) || $column['nodisplay'] == false) && !isset($column['datatype'])) {
                        $fields[$name] = $column['long name'];
                    }
                }
                if ($canedit) {
                    $fields[] = __('Update');
                }
                $values = [];
                $massive_action = [];
                foreach ($datas as $data) {
                    $newValue = [];
                    foreach (array_keys($columns) as $column) {
                        $newValue[$column] = $data[$column];
                    }
                    if ($canedit) {
                        $newValue[] = '<a href=' . $link->getFormURL() . '?id=' . $data['id'] . '>' . __('Update') . "</a>";
                    }
                    $values[] = $newValue;
                    $massive_action[] = sprintf('item[%s][%s]', $link::class, $data['id']);
                }
                echo "<hr>";
                echo "<h2>" . $link::getTypeName() . "</h2>";
                renderTwigTemplate('table.twig', [
                   'id' => $massiveActionContainerId,
                   'fields' => $fields,
                   'values' => $values,
                   'massive_action' => $massive_action
                ]);
            }
        }
        // Force disable selected items
        $_SESSION['glpimassiveactionselected'] = [];
    }


    public static function getDeviceForeignKey()
    {
        return getForeignKeyFieldForTable(getTableForItemType(static::getDeviceType()));
    }

    public function getTableGroupCriteria($item, $peer_type = null)
    {
        $is_device = ($item instanceof CommonDevice);
        $ctable = $this->getTable();
        $criteria = [
           'SELECT' => "$ctable.*",
           'FROM'   => $ctable
        ];
        if ($is_device) {
            $fk = 'items_id';

            // Entity restrict
            $criteria['WHERE'] = [
               $this->getDeviceForeignKey()  => $item->getID(),
               "$ctable.itemtype"            => $peer_type,
               "$ctable.is_deleted"          => 0
            ];
            $criteria['ORDERBY'] = [
               "$ctable.itemtype",
               "$ctable.$fk"
            ];
            if (!empty($peer_type)) {
                $criteria['LEFT JOIN'] = [
                   getTableForItemType($peer_type) => [
                      'ON' => [
                         $ctable                          => 'items_id',
                         getTableForItemType($peer_type)  => 'id', [
                            'AND' => [
                               "$ctable.itemtype"   => $peer_type
                            ]
                         ]
                      ]
                   ]
                ];
                $criteria['WHERE'] += getEntitiesRestrictCriteria(getTableForItemType($peer_type));
            }
        } else {
            $fk = $this->getDeviceForeignKey();

            $criteria['WHERE'] = [
               'itemtype'     => $item->getType(),
               'items_id'     => $item->getID(),
               'is_deleted'   => 0
            ];
            $criteria['ORDERBY'] = $fk;
        }

        return $criteria;
    }

    /**
     * Get the group of elements regarding given item.
     * Two kind of item :
     *              - Device* feed by a link to the attached item (Computer, Printer ...)
     *              - Computer, Printer ...: feed by the "global" properties of the CommonDevice
     * Then feed with the specificities of the Item_Device elements
     * In cas of $item is an instance, then $options contains the type of the item (Computer,
     * Printer ...).
     *
     * @param $item
     * @param $table
     * @param $options            array
     * @param $delete_all_column
     * @param $common_column
     * @param $specific_column
     * @param $delete_column
     * @param $dynamic_column
     **/
    public function getTableGroup(
        CommonDBTM $item,
        HTMLTableMain $table,
        array $options,
        HTMLTableSuperHeader $delete_all_column = null,
        HTMLTableSuperHeader $common_column,
        HTMLTableSuperHeader $specific_column,
        HTMLTableSuperHeader $delete_column = null,
        $dynamic_column
    ) {
        global $DB;

        $is_device = ($item instanceof CommonDevice);

        if ($is_device) {
            $peer_type = $options['itemtype'];

            if (empty($peer_type)) {
                $column_label = __('Dissociated devices');
                $group_name   = 'None';
            } else {
                $column_label = $peer_type::getTypeName(Session::getPluralNumber());
                $group_name   = $peer_type;
            }

            $table_group = $table->createGroup($group_name, '');

            $peer_column = $table_group->addHeader('item', $column_label, $common_column, null);

            if (!empty($peer_type)) {
                //TRANS : %1$s is the type of the device
                //        %2$s is the type of the item
                //        %3$s is the name of the item (used for headings of a list),
                $itemtype_nav_title = sprintf(
                    __('%1$s of %2$s: %3$s'),
                    $peer_type::getTypeName(Session::getPluralNumber()),
                    $item->getTypeName(1),
                    $item->getName()
                );
                $peer_column->setItemType($peer_type, $itemtype_nav_title);
            }
        } else {
            $peer_type   = $this->getDeviceType();

            $table_group = $table->createGroup($peer_type, '');

            //TRANS : %1$s is the type of the device
            //        %2$s is the type of the item
            //        %3$s is the name of the item (used for headings of a list),
            $options['itemtype_title'] = sprintf(
                __('%1$s of %2$s: %3$s'),
                $peer_type::getTypeName(Session::getPluralNumber()),
                $item->getTypeName(1),
                $item->getName()
            );

            $peer_type::getHTMLTableHeader(
                $item->getType(),
                $table_group,
                $common_column,
                null,
                $options
            );
        }

        $specificity_columns = [];
        $link_column         = $table_group->addHeader('spec_link', '', $specific_column);
        $spec_column         = $link_column;

        foreach ($this->getSpecificities() as $field => $attributs) {
            $spec_column                 = $table_group->addHeader(
                'spec_' . $field,
                $attributs['long name'],
                $specific_column,
                $spec_column
            );
            $specificity_columns[$field] = $spec_column;
        }

        $infocom_column  = $table_group->addHeader(
            'infocom',
            Infocom::getTypeName(Session::getPluralNumber()),
            $specific_column,
            $spec_column
        );

        $document_column = $table_group->addHeader(
            'document',
            Document::getTypeName(Session::getPluralNumber()),
            $specific_column,
            $spec_column
        );

        if ($item->isDynamic()) {
            $dynamics_column = $table_group->addHeader(
                'one',
                '&nbsp;',
                $dynamic_column,
                $spec_column
            );
            $previous_column = $dynamics_column;
        } else {
            $previous_column = $spec_column;
        }

        if ($options['canedit']) {
            $group_checkbox_tag =  (empty($peer_type) ? '__' : $peer_type);
            $content            = Html::getCheckbox(['criterion'
            => ['tag_for_massive'
            => $group_checkbox_tag]]);
            $delete_one         = $table_group->addHeader(
                'one',
                $content,
                $delete_column,
                $previous_column
            );
        }

        $criteria = $this->getTableGroupCriteria($item, $peer_type);
        $fk = $item instanceof CommonDevice ? 'items_id' : $this->getDeviceForeignKey();

        if (!empty($peer_type)) {
            $peer = new $peer_type();
            $peer->getEmpty();
        } else {
            $peer = null;
        }

        $request = $this::getAdapter()->request($criteria);
        while ($link = $request->fetchAssociative()) {
            Session::addToNavigateListItems(static::getType(), $link["id"]);
            $this->getFromDB($link['id']);
            $current_row  = $table_group->createRow();
            if ((is_null($peer)) || ($link[$fk] != $peer->getID())) {
                if ($peer instanceof CommonDBTM) {
                    $peer->getFromDB($link[$fk]);
                }

                $peer_group   = $peer_type . '_' . $link[$fk] . '_' . mt_rand();
                $current_row->setHTMLID($peer_group);

                if ($options['canedit']) {
                    $cell_value = Html::getCheckAllAsCheckbox($peer_group);
                    $current_row->addCell($delete_all_column, $cell_value);
                }

                if ($is_device) {
                    $cell = $current_row->addCell(
                        $peer_column,
                        ($peer ? $peer->getLink() : __('None')),
                        null,
                        $peer
                    );
                    if (is_null($peer)) {
                        $cell->setHTMLClass('center');
                    }
                } else {
                    $peer->getHTMLTableCellForItem($current_row, $item, null, $options);
                }
            }

            if (Session::haveRight('device', UPDATE)) {
                $mode = __s('Update');
            } else {
                $mode = _sn('View', 'Views', 1);
            }
            $spec_cell = $current_row->addCell(
                $link_column,
                "<a href='" . $this->getLinkURL() . "'>$mode</a>"
            );

            foreach ($this->getSpecificities() as $field => $attributs) {
                $content = '';

                if (!empty($link[$field])) {
                    // Check the user can view the field
                    if (!isset($attributs['right'])) {
                        $canRead = true;
                    } else {
                        $canRead = (Session::haveRightsOr($attributs['right'], [READ, UPDATE]));
                    }

                    // Don't show if the field shall not display in the list
                    if (isset($attributs['nodisplay']) && $attributs['nodisplay']) {
                        $canRead = false;
                    }

                    if (!isset($attributs['datatype'])) {
                        $attributs['datatype'] = 'text';
                    }
                    if ($canRead) {
                        switch ($attributs['datatype']) {
                            case 'dropdown':
                                $dropdownType = getItemtypeForForeignKeyField($field);
                                $content = Dropdown::getDropdownName($dropdownType::getTable(), $link[$field]);
                                break;

                            default:
                                $content = $link[$field];
                        }
                    }
                }

                $spec_cell = $current_row->addCell($specificity_columns[$field], $content, $spec_cell);
            }

            if (
                countElementsInTable('glpi_infocoms', [
                'itemtype' => $this->getType(),
                'items_id' => $link['id']
                ])
            ) {
                $content = [[
                   'function'   => 'Infocom::showDisplayLink',
                   'parameters' => [$this->getType(), $link['id']]
                ]];
            } else {
                $content = '';
            }
            $current_row->addCell($infocom_column, $content, $spec_cell);

            $content = [];
            // The order is to be sure that specific documents appear first
            $doc_request = $this::getAdapter()->request([
               'SELECT' => 'documents_id',
               'FROM'   => 'glpi_documents_items',
               'WHERE'  => [
                  'OR' => [
                     [
                        'itemtype'  => $this->getType(),
                        'items_id'  => $link['id']
                     ],
                     [
                        'itemtype'  => $this->getDeviceType(),
                        'items_id'  => $link[$this->getDeviceForeignKey()]
                     ]
                  ]
               ],
               'ORDER'  => 'itemtype'
            ]);
            $document = new Document();
            while ($document_link = $doc_request->fetchAssociative()) {
                if ($document->can($document_link['documents_id'], READ)) {
                    $content[] = $document->getLink();
                }
            }
            $content = implode('<br>', $content);
            $current_row->addCell($document_column, $content, $spec_cell);

            if ($item->isDynamic()) {
                $previous_cell = $current_row->addCell(
                    $dynamics_column,
                    Dropdown::getYesNo($link['is_dynamic']),
                    $spec_cell
                );
            } else {
                $previous_cell = $spec_cell;
            }

            if ($options['canedit']) {
                $cell_value = Html::getMassiveActionCheckBox(
                    $this->getType(),
                    $link['id'],
                    ['massive_tags' => $group_checkbox_tag]
                );
                $current_row->addCell($delete_one, $cell_value, $previous_cell);
            }
        }
    }


    /**
     * @param $numberToAdd
     * @param $itemtype
     * @param $items_id
     * @param $devices_id
     * @param $input          array to complete (permit to define values)
     **/
    public function addDevices($numberToAdd, $itemtype, $items_id, $devices_id, $input = [])
    {
        if ($numberToAdd == 0) {
            return;
        }

        $input['itemtype']                    = $itemtype;
        $input['items_id']                    = $items_id;
        $input[static::getDeviceForeignKey()] = $devices_id;

        $device_type = static::getDeviceType();
        $device      = new $device_type();
        $device->getFromDB($devices_id);

        foreach (static::getSpecificities() as $field => $attributs) {
            if (isset($device->fields[$field . '_default'])) {
                $input[$field] = $device->fields[$field . '_default'];
            }
        }

        if ($this->can(-1, CREATE, $input)) {
            for ($i = 0; $i < $numberToAdd; $i++) {
                $this->add($input);
            }
        }
    }


    /**
     * Add one or several device(s) from front/item_devices.form.php.
     *
     * @param $input array of input: should be $_POST
     *
     * @since 0.85
     **/
    public static function addDevicesFromPOST($input)
    {
        if (isset($input['devicetype']) && !$input['devicetype']) {
            Session::addMessageAfterRedirect(
                __('Please select a device type'),
                false,
                ERROR
            );
            return;
        } elseif (isset($_POST['devices_id']) && !$_POST['devices_id']) {
            Session::addMessageAfterRedirect(
                __('Please select a device'),
                false,
                ERROR
            );
            return;
        }

        if (isset($input['devicetype'])) {
            $devicetype = $input['devicetype'];
            $linktype   = $devicetype::getItem_DeviceType();
            if ($link = getItemForItemtype($linktype)) {
                if (
                    !isset($input[$linktype::getForeignKeyField()])
                    && (!isset($input['new_devices']) || !$input['new_devices'])
                ) {
                    Session::addMessageAfterRedirect(
                        __('You must choose any unaffected device or ask to add new.'),
                        false,
                        ERROR
                    );
                    return;
                }

                if (
                    (isset($input[$linktype::getForeignKeyField()]))
                    && (count($input[$linktype::getForeignKeyField()]))
                ) {
                    $update_input = [
                       'itemtype' => $input['itemtype'],
                       'items_id' => $input['items_id']
                    ];
                    foreach ($input[$linktype::getForeignKeyField()] as $id) {
                        $update_input['id'] = $id;
                        $link->update($update_input);
                    }
                }
                if (isset($input['new_devices'])) {
                    $link->addDevices(
                        $input['new_devices'],
                        $input['itemtype'],
                        $input['items_id'],
                        $input['devices_id']
                    );
                }
            }
        } else {
            if (!$item = getItemForItemtype($input['itemtype'])) {
                Html::displayNotFoundError();
            }
            if ($item instanceof CommonDevice) {
                if ($link = getItemForItemtype($item->getItem_DeviceType())) {
                    $link->addDevices($input['number_devices_to_add'], '', 0, $input['items_id']);
                }
            }
        }
    }


    /**
     * @param $input array of input: should be $_POST
     **/
    public static function updateAll($input)
    {

        if (
            !isset($input['itemtype'])
            || !isset($input['items_id'])
        ) {
            Html::displayNotFoundError();
        }

        $itemtype = $input['itemtype'];
        $items_id = $input['items_id'];
        if (!$item = getItemForItemtype($itemtype)) {
            Html::displayNotFoundError();
        }
        $item->check($input['items_id'], UPDATE, $_POST);

        $is_device = ($item instanceof CommonDevice);
        if ($is_device) {
            $link_type = $itemtype::getItem_DeviceType();
        }

        $links   = [];
        // Update quantity or values
        $device_type = '';
        foreach ($input as $key => $val) {
            $data = explode("_", $key);
            if (!empty($data[0])) {
                $command = $data[0];
            } else {
                continue;
            }
            if (($command != 'quantity') && ($command != 'value')) {
                // items_id, itemtype, devicetype ...
                continue;
            }
            if (!$is_device) {
                if (empty($data[1])) {
                    continue;
                }
                $device_type = $data[1];
                if (in_array($device_type::getItem_DeviceType(), self::getItemAffinities($itemtype))) {
                    $link_type = $device_type::getItem_DeviceType();
                }
            }
            if (!empty($data[2])) {
                $links_id = $data[2];
            } else {
                continue;
            }
            if (!isset($links[$link_type])) {
                $links[$link_type] = [
                   'add'    => [],
                   'update' => []
                ];
            }

            switch ($command) {
                case 'quantity':
                    $links[$link_type]['add'][$links_id] = $val;
                    break;

                case 'value':
                    if (!isset($links[$link_type]['update'][$links_id])) {
                        $links[$link_type]['update'][$links_id] = [];
                    }
                    if (isset($data[3])) {
                        $links[$link_type]['update'][$links_id][$data[3]] = $val;
                    }
                    break;
            }
        }

        foreach ($links as $type => $commands) {
            if ($link = getItemForItemtype($type)) {
                foreach ($commands['add'] as $link_to_add => $number) {
                    $link->addDevices($number, $itemtype, $items_id, $link_to_add);
                }
                foreach ($commands['update'] as $link_to_update => $input) {
                    $input['id'] = $link_to_update;
                    $link->update($input);
                }
                unset($link);
            }
        }
    }


    /**
     * @since 0.85
     *
     * @param $item_devices_id
     * @param $items_id
     * @param $itemtype
     *
     * @return boolean
     **/
    public static function affectItem_Device($item_devices_id, $items_id, $itemtype)
    {

        $link = new static();
        return $link->update([
           'id'       => $item_devices_id,
           'items_id' => $items_id,
           'itemtype' => $itemtype
        ]);
    }


    /**
     * @param $itemtype
     * @param $items_id
     * @param $unaffect
     **/
    public static function cleanItemDeviceDBOnItemDelete($itemtype, $items_id, $unaffect)
    {
        global $DB;

        foreach (self::getItemAffinities($itemtype) as $link_type) {
            $link = getItemForItemtype($link_type);
            if ($link) {
                if ($unaffect) {
                    $DB->update(
                        $link->getTable(),
                        [
                          'items_id'  => 0,
                          'itemtype'  => ''
                        ],
                        [
                          'items_id'  => $items_id,
                          'itemtype'  => $itemtype
                        ]
                    );
                } else {
                    $link->cleanDBOnItemDelete($itemtype, $items_id);
                }
            }
        }
    }


    public function getRights($interface = 'central')
    {

        $values = parent::getRights();
        return $values;
    }


    /**
     * @since 0.85
     *
     * @see CommonDBConnexity::getConnexityMassiveActionsSpecificities()
     **/
    public static function getConnexityMassiveActionsSpecificities()
    {

        $specificities              = parent::getConnexityMassiveActionsSpecificities();

        $specificities['reaffect']  = 1;
        $specificities['itemtypes'] = self::getConcernedItems();

        return $specificities;
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Infocom', $ong, $options);
        $this->addStandardTab('Document_Item', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);
        $this->addStandardTab('Contract_Item', $ong, $options);

        return $ong;
    }


    /**
     * @since 0.85
     **/
    public function showForm($ID, $options = [])
    {
        if (!$this->isNewID($ID)) {
            $this->check($ID, READ);
        } else {
            // Create item
            $this->check(-1, CREATE);
        }

        $item   = $this->getOnePeer(0);
        $device = $this->getOnePeer(1);

        $form = [
           'action' => $this->getFormURL(),
           'itemtype' => $this::class,
           'content' => [
              $this->getTypeName() => [
                 'visible' => true,
                 'inputs' => [
                    !$this->isNewID($ID) ? [
                       'type' => 'hidden',
                       'name' => 'id',
                       'value' => $ID,
                    ] : [],
                    _n('Item', 'Items', 1) => [
                       'content' => ($item === false) ? __('No associated item') : $item->getLink(),
                    ],
                    _n('Component', 'Components', 1) => (false === $device) ? [
                       'type' => 'select',
                       'name' => $this->getDeviceForeignKey(),
                       'values' => getOptionForItems($this->getDeviceType(), [], true, true),
                       'actions' => getItemActionButtons(['info', 'add'], $this->getDeviceType())
                    ] : [
                       'content' => $device->getLink(),
                    ],
                 ]
              ]
           ]
        ];
        foreach (static::getSpecificities() as $field => $attributes) {
            if (!isset($attributes['right'])) {
                $canRead = true;
            } else {
                $canRead = (Session::haveRightsOr($attributes['right'], [READ, UPDATE]));
            }
            if (!$canRead) {
                continue;
            }
            $newInput = [];
            foreach ($attributes['formContent'] ?? [] as $name => $value) {
                $newInput[$name] = $value;
            }
            $newInput['name'] = $field;
            $newInput['value'] = $this->fields[$field];
            $form['content'][array_key_first($form['content'])]['inputs']
               [$attributes['long name'] ?? $attributes['short name']] = $newInput;
        };
        renderTwigForm($form, '', $this->fields);

        return true;
    }

    public function prepareInputForAdd($input)
    {
        global $CFG_GLPI;

        if (!isset($input[static::$items_id_2]) || !$input[static::$items_id_2]) {
            Session::addMessageAfterRedirect(
                sprintf(
                    __('%1$s: %2$s'),
                    static::getTypeName(),
                    __('A device ID is mandatory')
                ),
                false,
                ERROR
            );
            return false;
        }

        $computer = static::getItemFromArray(static::$itemtype_1, static::$items_id_1, $input);

        if ($computer instanceof CommonDBTM) {
            if (
                isset($CFG_GLPI['is_location_autoupdate'])
                && $CFG_GLPI["is_location_autoupdate"]
                && (!isset($input['locations_id'])
                   || $computer->fields['locations_id'] != $input['locations_id'])
            ) {
                $input['locations_id'] = $computer->fields['locations_id'];
            }

            if (
                (isset($CFG_GLPI['state_autoupdate_mode'])
                && $CFG_GLPI["state_autoupdate_mode"] < 0)
                && (!isset($input['states_id'])
                  || $computer->fields['states_id'] != $input['states_id'])
            ) {
                $input['states_id'] = $computer->fields['states_id'];
            }

            if (
                (isset($CFG_GLPI['state_autoupdate_mode'])
                && $CFG_GLPI["state_autoupdate_mode"] > 0)
                && (!isset($input['states_id'])
                  || $input['states_id'] != $CFG_GLPI["state_autoupdate_mode"])
            ) {
                $input['states_id'] = $CFG_GLPI["state_autoupdate_mode"];
            }
        }

        return parent::prepareInputForAdd($input);
    }

    public function prepareInputForUpdate($input)
    {
        foreach (static::getSpecificities() as $field => $attributs) {
            if (!isset($attributs['right'])) {
                $canUpdate = true;
            } else {
                $canUpdate = (Session::haveRightsOr($attributs['right'], [UPDATE]));
            }
            if (isset($input[$field]) && !$canUpdate) {
                unset($input[$field]);
                Session::addMessageAfterRedirect(__('Update of ' . $attributs['short name'] . ' denied'));
            }
        }

        return $input;
    }

    public static function unsetUndisclosedFields(&$fields)
    {
        foreach (static::getSpecificities() as $key => $attributs) {
            if (isset($attributs['right'])) {
                if (!Session::haveRightsOr($attributs['right'], [READ])) {
                    unset($fields[$key]);
                }
            }
        }
    }

    public static function getSearchURL($full = true)
    {
        global $CFG_GLPI;

        $dir = ($full ? $CFG_GLPI['root_doc'] : '');
        $itemtype = get_called_class();
        $link = "$dir/front/item_device.php?itemtype=$itemtype";

        return $link;
    }


    public static function getIcon()
    {
        $device_class = static::$itemtype_2 ?? "CommonDevice";
        return $device_class::getIcon();
    }
}
