<?php

use Itsmng\Domain\Entities\ItemOperatingSystem;

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
class Item_OperatingSystem extends CommonDBRelation
{
    public static $itemtype_1 = 'OperatingSystem';
    public static $items_id_1 = 'operatingsystems_id';
    public static $itemtype_2 = 'itemtype';
    public static $items_id_2 = 'items_id';
    public static $checkItem_1_Rights = self::DONT_CHECK_ITEM_RIGHTS;


    public static function getTypeName($nb = 0)
    {
        return _n('Item operating system', 'Item operating systems', $nb);
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $nb = 0;
        switch ($item->getType()) {
            default:
                if ($_SESSION['glpishow_count_on_tabs']) {
                    $nb = self::countForItem($item);
                }
                return self::createTabEntry(OperatingSystem::getTypeName(Session::getPluralNumber()), $nb);
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        self::showForItem($item, $withtemplate);
    }

    /**
     * Get operating systems related to a given item
     *
     * @param CommonDBTM $item  Item instance
     * @param string     $sort  Field to sort on
     * @param string     $order Sort order
     *
     * @return Doctrine\DBAL\Result
     */
    public static function getFromItem(CommonDBTM $item, $sort = null, $order = null): Doctrine\DBAL\Result
    {
        if ($sort === null) {
            $sort = "glpi_items_operatingsystems.id";
        }
        if ($order === null) {
            $order = 'ASC';
        }

        $request = self::getAdapter()->request([
           'SELECT'    => [
              'glpi_items_operatingsystems.id AS assocID',
              'glpi_operatingsystems.name',
              'glpi_operatingsystemversions.name AS version',
              'glpi_operatingsystemarchitectures.name AS architecture',
              'glpi_operatingsystemservicepacks.name AS servicepack'
           ],
           'FROM'      => 'glpi_items_operatingsystems',
           'LEFT JOIN' => [
              'glpi_operatingsystems'             => [
                 'ON' => [
                    'glpi_items_operatingsystems' => 'operatingsystems_id',
                    'glpi_operatingsystems'       => 'id'
                 ]
              ],
              'glpi_operatingsystemservicepacks'  => [
                 'ON' => [
                    'glpi_items_operatingsystems'       => 'operatingsystemservicepacks_id',
                    'glpi_operatingsystemservicepacks'  => 'id'
                 ]
              ],
              'glpi_operatingsystemarchitectures' => [
                 'ON' => [
                    'glpi_items_operatingsystems'       => 'operatingsystemarchitectures_id',
                    'glpi_operatingsystemarchitectures' => 'id'
                 ]
              ],
              'glpi_operatingsystemversions'      => [
                 'ON' => [
                    'glpi_items_operatingsystems'    => 'operatingsystemversions_id',
                    'glpi_operatingsystemversions'   => 'id'
                 ]
              ]
           ],
           'WHERE'     => [
              'glpi_items_operatingsystems.itemtype' => $item->getType(),
              'glpi_items_operatingsystems.items_id' => $item->getID()
           ],
           'ORDERBY'   => "$sort $order"
        ]);
        return $request;
    }

    /**
     * Print the item's operating system form
     *
     * @param CommonDBTM $item Item instance
     *
     * @since 9.2
     *
     * @return void
    **/
    public static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {
        global $DB;

        //default options
        $params = ['rand' => mt_rand()];

        $columns = [
           __('Name'),
           _n('Version', 'Versions', 1),
           _n('Architecture', 'Architectures', 1),
           OperatingSystemServicePack::getTypeName(1)
        ];

        if (isset($_GET["order"]) && ($_GET["order"] == "ASC")) {
            $order = "ASC";
        } else {
            $order = "DESC";
        }

        if (
            (isset($_GET["sort"]) && !empty($_GET["sort"]))
            && isset($columns[$_GET["sort"]])
        ) {
            $sort = $_GET["sort"];
        } else {
            $sort = "glpi_items_operatingsystems.id";
        }

        if (empty($withtemplate)) {
            $withtemplate = 0;
        }

        $iterator = self::getFromItem($item, $sort, $order);
        $results = $iterator->fetchAllAssociative();
        $number = count($results);
        $i      = 0;

        $os = [];
      //   while ($data = $iterator->next()) {
         foreach ($results as $data) {
            $os[$data['assocID']] = $data;
        }

        $canedit = $item->canEdit($item->getID());

        //multi OS for an item is not an existing feature right now.
        /*if ($canedit && $number >= 1
            && !(!empty($withtemplate) && ($withtemplate == 2))) {
           echo "<div class='center firstbloc'>".
              "<a class='vsubmit' href='" . Toolbox::getItemTypeFormURL(self::getType()) . "?items_id=" . $item->getID() .
              "&amp;itemtype=" . $item->getType() . "&amp;withtemplate=" . $withtemplate."'>";
           echo __('Add an operating system');
           echo "</a></div>\n";
        }*/

        if ($number <= 1) {
            $id = -1;
            $instance = new self();
            if ($number > 0) {
                $id = array_keys($os)[0];
            } else {
                //set itemtype and items_id
                $instance->fields['itemtype']    = $item->getType();
                $instance->fields['items_id']    = $item->getID();
                $instance->fields['entities_id'] = $item->fields['entities_id'];
            }
            $instance->showForm($id, ['canedit' => $canedit]);
            return;
        }

        echo "<div class='spaced'>";
        if (
            $canedit
            && $number
            && ($withtemplate < 2)
        ) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $params['rand']);
            $massiveactionparams = ['num_displayed'  => min($_SESSION['glpilist_limit'], $number),
                                         'container'      => 'mass' . __CLASS__ . $params['rand']];
            Html::showMassiveActions($massiveactionparams);
        }

        echo "<table class='tab_cadre_fixehov' aria-label='Editable Table'>";

        $header_begin  = "<tr>";
        $header_top    = '';
        $header_bottom = '';
        $header_end    = '';
        if (
            $canedit
            && $number
            && ($withtemplate < 2)
        ) {
            $header_top    .= "<th width='11'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $params['rand']);
            $header_top    .= "</th>";
            $header_bottom .= "<th width='11'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $params['rand']);
            $header_bottom .= "</th>";
        }

        foreach ($columns as $key => $val) {
            $header_end .= "<th" . ($sort == $key ? " class='order_$order'" : '') . ">" .
                           "<a href='javascript:reloadTab(\"sort=$key&amp;order=" .
                             (($order == "ASC") ? "DESC" : "ASC") . "&amp;start=0\");'>$val</a></th>";
        }

        $header_end .= "</tr>";
        echo $header_begin . $header_top . $header_end;

        if ($number) {
            foreach ($os as $data) {
                $linkname = $data['name'];
                if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                    $linkname = sprintf(__('%1$s (%2$s)'), $linkname, $data["assocID"]);
                }
                $link = Toolbox::getItemTypeFormURL(self::getType());
                $name = "<a href=\"" . $link . "?id=" . $data["assocID"] . "\">" . $linkname . "</a>";

                echo "<tr class='tab_bg_1'>";
                if (
                    $canedit
                    && ($withtemplate < 2)
                ) {
                    echo "<td width='10'>";
                    Html::showMassiveActionCheckBox(__CLASS__, $data["assocID"]);
                    echo "</td>";
                }
                echo "<td class='center'>{$name}</td>";
                echo "<td class='center'>{$data['version']}</td>";
                echo "<td class='center'>{$data['architecture']}</td>";
                echo "<td class='center'>{$data['servicepack']}</td>";

                echo "</tr>";
                $i++;
            }
            echo $header_begin . $header_bottom . $header_end;
        }

        echo "</table>";
        if ($canedit && $number && ($withtemplate < 2)) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
        }
        echo "</div>";
    }

    public function getConnexityItem(
        $itemtype,
        $items_id,
        $getFromDB = true,
        $getEmpty = true,
        $getFromDBOrEmpty = true
    ) {
        //overrided to set $getFromDBOrEmpty to true
        return parent::getConnexityItem($itemtype, $items_id, $getFromDB, $getEmpty, $getFromDBOrEmpty);
    }

    public function showPrimaryForm($options = [])
    {
        //overrided to set expected values for new item
        $fields = $options;
        if (isset($fields['id']) && $fields['id'] == 0) {
            unset($fields['id']);
        }
        foreach ($fields as $field => $value) {
            $this->fields[$field] = $value;
        }
        parent::showPrimaryForm($options);
    }


    public function showForm($ID, $options = [])
    {

        if (!$this->isNewID($ID)) {
            $this->getFromDB($ID);
        }
        $form = [
           'action' => $this->getFormURL(),
           'itemtype' => $this::class,
           'content' => [
              '' => [
                 'visible' => true,
                 'inputs' => [
                    !$this->isNewID($ID) ? [
                       'type' => 'hidden',
                       'name' => 'id',
                       'value' => $ID,
                    ] : [],
                    [
                       'type' => 'hidden',
                       'name' => 'itemtype',
                       'value' => $this->fields['itemtype'] ?? '',
                    ],
                    [
                       'type' => 'hidden',
                       'name' => 'items_id',
                       'value' => $this->fields['items_id'] ?? '',
                    ],
                    __("Name") => [
                       'type' => 'select',
                       'name' => 'operatingsystems_id',
                       'values' => getOptionForItems(OperatingSystem::class),
                       'value' => $this->fields['operatingsystems_id'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], OperatingSystem::class)
                    ],
                    _n('Version', 'Versions', 1) => [
                       'type' => 'select',
                       'name' => 'operatingsystemversions_id',
                       'values' => getOptionForItems(OperatingSystemVersion::class),
                       'value' => $this->fields['operatingsystemversions_id'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], OperatingSystemVersion::class)
                    ],
                    _n('Architecture', 'Architectures', 1) => [
                       'type' => 'select',
                       'name' => 'operatingsystemarchitectures_id',
                       'values' => getOptionForItems(OperatingSystemArchitecture::class),
                       'value' => $this->fields['operatingsystemarchitectures_id'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], OperatingSystemArchitecture::class)
                    ],
                    OperatingSystemServicePack::getTypeName(1) => [
                       'type' => 'select',
                       'name' => 'operatingsystemservicepacks_id',
                       'values' => getOptionForItems(OperatingSystemServicePack::class),
                       'value' => $this->fields['operatingsystemservicepacks_id'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], OperatingSystemServicePack::class)
                    ],
                    _n('Kernel', 'Kernels', 1) => [
                       'type' => 'select',
                       'name' => 'operatingsystemkernelversions_id',
                       'values' => getOptionForItems(OperatingSystemKernelVersion::class),
                       'value' => $this->fields['operatingsystemkernelversions_id'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], OperatingSystemKernelVersion::class)
                    ],
                    _n('Edition', 'Editions', 1) => [
                       'type' => 'select',
                       'name' => 'operatingsystemeditions_id',
                       'values' => getOptionForItems(OperatingSystemEdition::class),
                       'value' => $this->fields['operatingsystemeditions_id'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], OperatingSystemEdition::class)
                    ],
                    __('Product ID') => [
                       'type' => 'text',
                       'name' => 'licenseid',
                       'value' => $this->fields['licenseid'] ?? '',
                    ],
                    __('Serial number') => [
                       'type' => 'text',
                       'name' => 'license_number',
                       'value' => $this->fields['license_number'] ?? '',
                    ],
                 ]
              ]
           ]
        ];

        renderTwigForm($form, '', $this->fields);
        return true;
    }

    protected function computeFriendlyName()
    {
        $item = getItemForItemtype($this->fields['itemtype']);
        $item->getFromDB($this->fields['items_id']);
        $name = $item->getTypeName(1) . ' ' . $item->getName();

        return $name;
    }


    /**
     * Duplicate operating system from an item template to its clone
     *
     * @deprecated 9.5
     *
     * @param string  $itemtype    itemtype of the item
     * @param integer $oldid       ID of the item to clone
     * @param integer $newid       ID of the item cloned
     * @param string  $newitemtype itemtype of the new item (= $itemtype if empty) (default '')
     *
     * @return void
     */
    public static function cloneItem($itemtype, $oldid, $newid, $newitemtype = '')
    {
        global $DB;

        Toolbox::deprecated('Use clone');
        $request = self::getAdapter()->request([
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'itemtype'  => $itemtype,
              'items_id'  => $oldid
           ]
        ]);

        while ($row = $request->fetchAssociative()) {
            $input             = Toolbox::addslashes_deep($row);
            $input['items_id'] = $newid;
            if (!empty($newitemtype)) {
                $input['itemtype'] = $newitemtype;
            }
            unset($input["id"]);
            unset($input["date_mod"]);
            unset($input["date_creation"]);
            $ios = new self();
            $ios->add($input);
        }
    }

    public function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
           'id'                 => 'common',
           'name'               => __('Characteristics')
        ];

        $tab[] = [
           'id'                 => '2',
           'table'              => $this->getTable(),
           'field'              => 'license_number',
           'name'               => __('Serial number'),
           'datatype'           => 'string',
           'massiveaction'      => false,
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '3',
           'table'              => $this->getTable(),
           'field'              => 'licenseid',
           'name'               => __('Product ID'),
           'datatype'           => 'string',
           'massiveaction'      => false,
           'autocomplete'       => true,
        ];

        return $tab;
    }

    public static function rawSearchOptionsToAdd($itemtype)
    {
        $tab = [];
        $tab[] = [
            'id'                => 'operatingsystem',
            'name'              => __('Operating System')
        ];

        $tab[] = [
           'id'                 => '45',
           'table'              => 'glpi_operatingsystems',
           'field'              => 'name',
           'name'               => __('Name'),
           'datatype'           => 'dropdown',
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_items_operatingsystems',
                 'joinparams'         => [
                    'jointype'           => 'itemtype_item',
                    'specific_itemtype'  => $itemtype
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '46',
           'table'              => 'glpi_operatingsystemversions',
           'field'              => 'name',
           'name'               => _n('Version', 'Versions', 1),
           'datatype'           => 'dropdown',
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_items_operatingsystems',
                 'joinparams'         => [
                    'jointype'           => 'itemtype_item',
                    'specific_itemtype'  => $itemtype
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '41',
           'table'              => 'glpi_operatingsystemservicepacks',
           'field'              => 'name',
           'name'               => OperatingSystemServicePack::getTypeName(1),
           'datatype'           => 'dropdown',
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_items_operatingsystems',
                 'joinparams'         => [
                    'jointype'           => 'itemtype_item',
                    'specific_itemtype'  => $itemtype
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '43',
           'table'              => 'glpi_items_operatingsystems',
           'field'              => 'license_number',
           'name'               => __('Serial number'),
           'datatype'           => 'string',
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'itemtype_item',
              'specific_itemtype'  => $itemtype
           ]
        ];

        $tab[] = [
           'id'                 => '44',
           'table'              => 'glpi_items_operatingsystems',
           'field'              => 'licenseid',
           'name'               => __('Product ID'),
           'datatype'           => 'string',
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'itemtype_item',
              'specific_itemtype'  => $itemtype
           ]
        ];

        $tab[] = [
           'id'                 => '61',
           'table'              => 'glpi_operatingsystemarchitectures',
           'field'              => 'name',
           'name'               => _n('Architecture', 'Architectures', 1),
           'datatype'           => 'dropdown',
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_items_operatingsystems',
                 'joinparams'         => [
                    'jointype'           => 'itemtype_item',
                    'specific_itemtype'  => $itemtype
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '64',
           'table'              => 'glpi_operatingsystemkernels',
           'field'              => 'name',
           'name'               => _n('Kernel', 'Kernels', 1),
           'datatype'           => 'dropdown',
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_operatingsystemkernelversions',
                 'joinparams'         => [
                    'beforejoin'   => [
                       'table'        => 'glpi_items_operatingsystems',
                       'joinparams'   => [
                          'jointype'           => 'itemtype_item',
                          'specific_itemtype'  => $itemtype
                       ]
                    ]
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '48',
           'table'              => 'glpi_operatingsystemkernelversions',
           'field'              => 'name',
           'name'               => _n('Kernel version', 'Kernel versions', 1),
           'datatype'           => 'dropdown',
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_items_operatingsystems',
                 'joinparams'         => [
                    'jointype'           => 'itemtype_item',
                    'specific_itemtype'  => $itemtype
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '63',
           'table'              => 'glpi_operatingsystemeditions',
           'field'              => 'name',
           'name'               => _n('Edition', 'Editions', 1),
           'datatype'           => 'dropdown',
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_items_operatingsystems',
                 'joinparams'         => [
                    'jointype'           => 'itemtype_item',
                    'specific_itemtype'  => $itemtype
                 ]
              ]
           ]
        ];

        return $tab;
    }


    public static function getRelationMassiveActionsSpecificities()
    {
        global $CFG_GLPI;

        $specificities              = parent::getRelationMassiveActionsSpecificities();

        $specificities['itemtypes'] = $CFG_GLPI['operatingsystem_types'];
        return $specificities;
    }
    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {

        switch ($ma->getAction()) {
            case 'update':
                static::showFormMassiveUpdate($ma);
                return true;
        }

        return parent::showMassiveActionsSubForm($ma);
    }

    public static function showFormMassiveUpdate($ma)
    {
        global $CFG_GLPI;

        $inputs = [
           OperatingSystem::getTypeName() => [
              'type' => 'select',
              'name' => 'os_field',
              'id'   => 'DropdownForOsTypeMassiveUpdate',
              'values' => [
                 Dropdown::EMPTY_VALUE,
                 'OperatingSystem'             => __('Name'),
                 'OperatingSystemVersion'      => _n('Version', 'Versions', 1),
                 'OperatingSystemArchitecture' => _n('Architecture', 'Architectures', 1),
                 'OperatingSystemKernel'       => OperatingSystemKernel::getTypeName(1),
                 'OperatingSystemKernelVersion' => OperatingSystemKernelVersion::getTypeName(1),
                 'OperatingSystemEdition'      => _n('Edition', 'Editions', 1)
              ],
              'col_lg' => 12,
              'col_md' => 12,
              'hooks' => [
                 'change' => <<<JS
                  var value = $('#DropdownForOsTypeMassiveUpdate').val();
                  $('#DropdownForOsFieldMassiveUpdate').empty();
                  if (value == 0) {
                     $('#DropdownForOsFieldMassiveUpdate').prop('disabled', true);
                     return
                  }
                  $('#DropdownForOsFieldMassiveUpdate').prop('disabled', false);
                  $.ajax({
                     url: '{$CFG_GLPI["root_doc"]}/ajax/dropdownMassiveActionOs.php',
                     type: 'POST',
                     data: {
                        itemtype: value
                     },
                     success: function(data) {
                        const jsonData = JSON.parse(data);
                        const options = jsonData.options;
                        $('#DropdownForOsFieldMassiveUpdate').attr('name', jsonData.name);
                        for (var key in options) {
                           $('#DropdownForOsFieldMassiveUpdate').append('<option value="' + key + '">' + options[key] + '</option>');
                        }
                     }
                  });
               JS,
              ]
           ],
           '' => [
              'type' => 'select',
              'id'   => 'DropdownForOsFieldMassiveUpdate',
              'disabled' => '',
              'col_lg' => 12,
              'col_md' => 12,
           ]
        ];
        foreach ($inputs as $title => $input) {
            renderTwigTemplate('macros/wrappedInput.twig', [
               'title' => $title,
               'input' => $input
            ]);
        };
        echo Html::submit(__('Update'), ['class' => 'btn btn-secondary', 'name' => 'update']);
        echo Html::submit(__('Clone'), ['class' => 'btn btn-secondary', 'name' => 'clone']);
    }

    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {

        switch ($ma->getAction()) {
            case 'update':
                $input = $ma->getInput();
                unset($input['update']);
                unset($input['os_field']);
                $ios = new Item_OperatingSystem();
                foreach ($ids as $id) {
                    if ($item->getFromDB($id)) {
                        if ($item->can($id, UPDATE, $input)) {
                            $exists = $ios->getFromDBByCrit([
                               'itemtype'  => $item->getType(),
                               'items_id'  => $item->getID()
                            ]);
                            $ok = false;
                            if ($exists) {
                                $ok = $ios->update(['id'  => $ios->getID()] + $input);
                            } else {
                                $ok = $ios->add(['itemtype' => $item->getType(), 'items_id' => $item->getID()] + $input);
                            }

                            if ($ok != false) {
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                            } else {
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                                $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                            }
                        } else {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                            $ma->addMessage($item->getErrorMessage(ERROR_NOT_FOUND));
                        }
                    } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        $ma->addMessage($item->getErrorMessage(ERROR_NOT_FOUND));
                    }
                }
                break;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    public function prepareInputForAdd($input)
    {
        $item = getItemForItemtype($input['itemtype']);
        $item->getFromDB($input['items_id']);
        $input['entities_id'] = $item->fields['entities_id'];
        $input['is_recursive'] = $item->fields['is_recursive'];
        return $input;
    }
}
