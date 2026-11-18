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
 * Disk Class
**/
class Item_Disk extends CommonDBChild
{
    // From CommonDBChild
    public static $itemtype = 'itemtype';
    public static $items_id = 'items_id';
    public $dohistory       = true;

    // Encryption status
    public const ENCRYPTION_STATUS_NO = 0;
    public const ENCRYPTION_STATUS_YES = 1;
    public const ENCRYPTION_STATUS_PARTIALLY = 2;

    public static function getTypeName($nb = 0)
    {
        return _n('Volume', 'Volumes', $nb);
    }

    public function post_getEmpty()
    {

        $this->fields["totalsize"] = '0';
        $this->fields["freesize"]  = '0';
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        // can exists for template
        if ($item::canView()) {
            $nb = 0;
            if ($_SESSION['glpishow_count_on_tabs']) {
                $nb = countElementsInTable(
                    self::getTable(),
                    [
                      'items_id'     => $item->getID(),
                      'itemtype'     => $item->getType(),
                      'is_deleted'   => 0
                    ]
                );
            }
            return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
        }
        return '';
    }


    /**
     * @param $item            CommonGLPI object
     * @param $tabnum          (default 1)
     * @param $withtemplate    (default 0)
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        self::showForItem($item, $withtemplate);
        return true;
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    /**
     * Duplicate all disks from an item template to his clone
     *
     * @deprecated 9.5
     * @since 0.84
     *
     * @param string  $type  Item type
     * @param integer $oldid Old ID
     * @param integer $newid New id
     *
     * @return void
    **/
    public static function cloneItem($type, $oldid, $newid)
    {
        global $DB;

        Toolbox::deprecated('Use clone');
        $iterator = $DB->request([
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'itemtype'  => $type,
              'items_id'  => $oldid
           ]
        ]);
        while ($data = $iterator->next()) {
            $cd                  = new self();
            unset($data['id']);
            $data['items_id']    = $newid;
            $data                = Toolbox::addslashes_deep($data);
            $cd->add($data);
        }
    }


    /**
     * Print the version form
     *
     * @param $ID        integer ID of the item
     * @param $options   array
     *     - target for the Form
     *     - itemtype type of the item for add process
     *     - items_id ID of the item for add process
     *
     * @return true if displayed  false if item not found or not right to display
    **/
    public function showForm($ID, $options = [])
    {
        $itemtype = null;
        if (isset($options['itemtype']) && !empty($options['itemtype'])) {
            $itemtype = $options['itemtype'];
        } elseif (isset($this->fields['itemtype']) && !empty($this->fields['itemtype'])) {
            $itemtype = $this->fields['itemtype'];
        } else {
            throw new \RuntimeException('Unable to retrieve itemtype');
        }

        if (!Session::haveRight($itemtype::$rightname, READ)) {
            return false;
        }

        $item = new $itemtype();
        if ($ID > 0) {
            $this->check($ID, READ);
            $item->getFromDB($this->fields['items_id']);
        } else {
            $this->check(-1, CREATE, $options);
            $item->getFromDB($options['items_id']);
        }

        $inline = !empty($options['inline']) && $this->isNewID($ID);
        $form_title = $inline ? __('Add a volume') : __('New item') . ' ' . $this->getTypeName(1);
        $form = [
           'action' => self::getFormURL(),
           'buttons' => [
              [
                 'type' => 'submit',
                 'name' => $this->isNewID($ID) ? 'add' : 'update',
                 'value' => $this->isNewID($ID) ? __('Add') : __('Update'),
                 'class' => $this->isNewID($ID) ? 'btn btn-primary' : 'btn btn-secondary',
                 'icon' => $this->isNewID($ID) ? 'fas fa-plus' : null
              ]
           ],
           'content' => [
              [
                 'visible' => false,
                 'attributes' => ['hidden' => 'hidden'],
                 'inputs' => [
                    $this->isNewID($ID) ? [
                       'type' => 'hidden',
                       'name' => 'itemtype',
                       'value' => $options['itemtype']
                    ] : [],
                    $this->isNewID($ID) ? [
                       'type' => 'hidden',
                       'name' => 'items_id',
                       'value' => $options['items_id']
                    ] : [],
                    $inline ? [
                       'type' => 'hidden',
                       'name' => '_inline',
                       'value' => 1,
                    ] : [],
                    $inline && !empty($options['withtemplate']) ? [
                       'type' => 'hidden',
                       'name' => 'withtemplate',
                       'value' => $options['withtemplate'],
                    ] : [],
                 ]
                    ],
              $form_title => [
                 'visible' => true,
                 'inputs' => [
                    __('Name') => [
                       'type' => 'text',
                       'name' => 'name',
                       'value' => $this->fields['name'],
                    ],
                    __('Partition') => [
                       'type' => 'text',
                       'name' => 'device',
                       'value' => $this->fields['device'],
                    ],
                    __('Mount point') => [
                       'type' => 'text',
                       'name' => 'mountpoint',
                       'value' => $this->fields['mountpoint'],
                    ],
                    Filesystem::getTypeName(1) => [
                       'type' => 'select',
                       'name' => 'filesystems_id',
                       ...getAjaxDropdownOptions('Filesystem'),
                       'value' => $this->fields['filesystems_id'],
                       'actions' => getItemActionButtons(['info', 'add'], 'Filesystem'),
                    ],
                    __('Global size') => [
                       'type' => 'text',
                       'name' => 'totalsize',
                       'value' => $this->fields['totalsize'],
                       'after' => __('Mio'),
                    ],
                    __('Free size') => [
                       'type' => 'text',
                       'name' => 'freesize',
                       'value' => $this->fields['freesize'],
                       'after' => __('Mio'),
                    ],
                    __('Encryption') => [
                       'type' => 'select',
                       'name' => 'encryption_status',
                       'values' => self::getAllEncryptionStatus(),
                       'value' => $this->fields['encryption_status'],
                    ],
                    __('Encryption tool') => [
                       'type' => 'text',
                       'name' => 'encryption_tool',
                       'value' => $this->fields['encryption_tool'],
                    ],
                    __('Encryption algorithm') => [
                       'type' => 'text',
                       'name' => 'encryption_algorithm',
                       'value' => $this->fields['encryption_algorithm'],
                    ],
                    __('Encryption type') => [
                       'type' => 'text',
                       'name' => 'encryption_type',
                       'value' => $this->fields['encryption_type'],
                    ],
                 ]
              ]
           ]
        ];
        if (Plugin::haveImport() && $ID && $this->fields['is_dynamic']) {
            ob_start();
            Plugin::doHook("autoinventory_form", $this, $form);
            $hook = ob_get_clean();
            $form[__('New item') . ' ' . $this->getTypeName(1)][__('Automatic inventory')] = [
               'content' => $hook,
            ];
        }
        $readonly = !$item->canEdit($item->getID()) || (($options['withtemplate'] ?? $_GET['withtemplate'] ?? 0) == 2);
        if ($readonly) {
            $form['buttons'] = [[]];
            echo '<fieldset disabled>';
        }
        $form_fields = $this->fields;
        if ($inline) {
            $form_fields['noEntity'] = true;
        }
        renderTwigForm($form, '', $form_fields);
        if ($readonly) {
            echo '</fieldset>';
        }

        return true;
    }

    /**
     * Get disks related to a given item
     *
     * @param CommonDBTM $item  Item instance
     * @param string     $sort  Field to sort on
     * @param string     $order Sort order
     *
     * @return DBmysqlIterator
     */
    public static function getFromItem(CommonDBTM $item, $sort = null, $order = null): DBmysqlIterator
    {
        global $DB;

        $iterator = $DB->request([
           'SELECT'    => [
              Filesystem::getTable() . '.name AS fsname',
              self::getTable() . '.*'
           ],
           'FROM'      => self::getTable(),
           'LEFT JOIN' => [
              Filesystem::getTable() => [
                 'FKEY' => [
                    self::getTable()        => 'filesystems_id',
                    Filesystem::getTable()  => 'id'
                 ]
              ]
           ],
           'WHERE'     => [
              'itemtype'     => $item->getType(),
              'items_id'     => $item->fields['id']
           ]
        ]);
        return $iterator;
    }

    /**
     * Print the disks
     *
     * @param CommonDBTM $item          Item object
     * @param boolean    $withtemplate  Template or basic item (default 0)
     *
     * @return void
    **/
    public static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {
        global $DB;

        $ID = $item->fields['id'];
        $itemtype = $item->getType();

        if (
            !$item->getFromDB($ID)
            || !$item->can($ID, READ)
        ) {
            return false;
        }
        $canedit = $item->canEdit($ID);

        if (
            $canedit
            && !(!empty($withtemplate) && ($withtemplate == 2))
        ) {
            $disk = new self();
            $disk->showForm(-1, [
                'itemtype' => $itemtype,
                'items_id' => $ID,
                'withtemplate' => $withtemplate,
                'inline' => true,
            ]);
        }

        $fields = [__('Name')];
        $import = Plugin::haveImport();
        if ($import) {
            $fields[] = __('Automatic inventory');
        }
        $fields = array_merge($fields, [__('Partition'), __('Mount point'), Filesystem::getTypeName(1),
            __('Global size'), __('Free size'), __('Free percentage'), __('Encryption')]);
        $values = [];
        Session::initNavigateListItems(__CLASS__, sprintf(__('%1$s = %2$s'), $item::getTypeName(1), $item->getName()));
        $disk = new self();
        foreach (self::getFromItem($item) as $data) {
            $disk->getFromResultSet($data);
            $row = [$disk->getLink()];
            if (!empty($data['is_deleted'])) {
                $row[0] .= ' <span class="badge text-bg-warning">' . Lock::getTypeName(1) . '</span>';
            }
            if ($import) {
                $row[] = Dropdown::getYesNo($data['is_dynamic']);
            }
            $row[] = htmlspecialchars($data['device'] ?? '', ENT_QUOTES, 'UTF-8', false);
            $row[] = htmlspecialchars($data['mountpoint'] ?? '', ENT_QUOTES, 'UTF-8', false);
            $row[] = htmlspecialchars($data['fsname'] ?? '', ENT_QUOTES, 'UTF-8', false);
            $row[] = Toolbox::getSize($data['totalsize'] * 1024 * 1024);
            $row[] = Toolbox::getSize($data['freesize'] * 1024 * 1024);
            $percent = $data['totalsize'] > 0 ? round(100 * $data['freesize'] / $data['totalsize']) : 0;
            $barPercent = max(0, min(100, $percent));
            $row[] = '<div class="progress" role="progressbar" aria-valuenow="' . $barPercent
                . '" aria-valuemin="0" aria-valuemax="100" aria-label="' . __s('Free percentage') . '">'
                . '<div class="progress-bar" style="width: ' . $barPercent . '%">' . $percent . ' %</div></div>';
            $encryption = Dropdown::getYesNo($data['encryption_status'] != self::ENCRYPTION_STATUS_NO);
            if ($data['encryption_status'] != self::ENCRYPTION_STATUS_NO) {
                $details = [__('Partial encryption') . ': ' . Dropdown::getYesNo($data['encryption_status'] == self::ENCRYPTION_STATUS_PARTIALLY),
                    __('Encryption tool') . ': ' . ($data['encryption_tool'] ?? ''),
                    __('Encryption algorithm') . ': ' . ($data['encryption_algorithm'] ?? ''),
                    __('Encryption type') . ': ' . ($data['encryption_type'] ?? '')];
                $encryption = '<span title="' . htmlspecialchars(implode("\n", $details), ENT_QUOTES, 'UTF-8', false)
                    . '"><i class="fas fa-lock" aria-hidden="true"></i> ' . $encryption . '</span>';
            }
            $row[] = $encryption;
            $values[] = $row;
            Session::addToNavigateListItems(__CLASS__, $data['id']);
        }
        renderTwigTemplate('table.twig', [
            'id' => 'volumes_' . $itemtype . '_' . $ID,
            'state_key' => 'Item_Disk_for_' . $itemtype,
            'pageSize' => (int) ($_SESSION['glpilist_limit'] ?? 15),
            'fields' => $fields,
            'values' => $values,
        ]);
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
           'field'              => 'name',
           'name'               => __('Name'),
           'datatype'           => 'itemlink',
           'massiveaction'      => false,
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '2',
           'table'              => $this->getTable(),
           'field'              => 'device',
           'name'               => __('Partition'),
           'datatype'           => 'string',
           'massiveaction'      => false,
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '3',
           'table'              => $this->getTable(),
           'field'              => 'mountpoint',
           'name'               => __('Mount point'),
           'datatype'           => 'string',
           'massiveaction'      => false,
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '4',
           'table'              => $this->getTable(),
           'field'              => 'totalsize',
           'unit'               => 'auto',
           'name'               => __('Global size'),
           'datatype'           => 'number',
           'width'              => 1000,
           'massiveaction'      => false,
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '5',
           'table'              => $this->getTable(),
           'field'              => 'freesize',
           'unit'               => 'auto',
           'name'               => __('Free size'),
           'datatype'           => 'number',
           'width'              => 1000,
           'massiveaction'      => false,
           'autocomplete'       => true,
        ];

        return $tab;
    }

    public static function rawSearchOptionsToAdd($itemtype)
    {
        $tab = [];

        $name = _n('Volume', 'Volumes', Session::getPluralNumber());
        $tab[] = [
            'id'                 => 'disk',
            'name'               => $name
        ];

        $tab[] = [
           'id'                 => '156',
           'table'              => self::getTable(),
           'field'              => 'name',
           'name'               => __('Name'),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'datatype'           => 'dropdown',
           'joinparams'         => [
              'jointype'           => 'itemtype_item'
           ]
        ];

        $tab[] = [
           'id'                 => '150',
           'table'              => self::getTable(),
           'field'              => 'totalsize',
           'unit'               => 'auto',
           'name'               => __('Global size'),
           'forcegroupby'       => true,
           'usehaving'          => true,
           'datatype'           => 'number',
           'width'              => 1000,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'itemtype_item'
           ]
        ];

        $tab[] = [
           'id'                 => '151',
           'table'              => self::getTable(),
           'field'              => 'freesize',
           'unit'               => 'auto',
           'name'               => __('Free size'),
           'forcegroupby'       => true,
           'datatype'           => 'number',
           'width'              => 1000,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'itemtype_item'
           ]
        ];

        $tab[] = [
           'id'                 => '152',
           'table'              => self::getTable(),
           'field'              => 'freepercent',
           'name'               => __('Free percentage'),
           'forcegroupby'       => true,
           'datatype'           => 'progressbar',
           'width'              => 2,
           'computation'        => 'ROUND(100*TABLE.freesize/TABLE.totalsize)',
           'computationgroupby' => true,
           'unit'               => '%',
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'itemtype_item'
           ]
        ];

        $tab[] = [
           'id'                 => '153',
           'table'              => self::getTable(),
           'field'              => 'mountpoint',
           'name'               => __('Mount point'),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'datatype'           => 'string',
           'joinparams'         => [
              'jointype'           => 'itemtype_item'
           ]
        ];

        $tab[] = [
           'id'                 => '154',
           'table'              => self::getTable(),
           'field'              => 'device',
           'name'               => __('Partition'),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'datatype'           => 'string',
           'joinparams'         => [
              'jointype'           => 'itemtype_item'
           ]
        ];

        $tab[] = [
           'id'                 => '155',
           'table'              => 'glpi_filesystems',
           'field'              => 'name',
           'name'               => Filesystem::getTypeName(1),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'datatype'           => 'dropdown',
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => self::getTable(),
                 'joinparams'         => [
                    'jointype'           => 'itemtype_item'
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '174',
           'table'              => self::getTable(),
           'field'              => 'encryption_status',
           'name'               => __('Encryption status'),
           'searchtype'         => 'equals',
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'searchequalsonfield' => true,
           'datatype'           => 'specific',
           'joinparams'         => [
              'jointype'           => 'itemtype_item'
           ]
        ];

        $tab[] = [
           'id'                 => '175',
           'table'              => self::getTable(),
           'field'              => 'encryption_tool',
           'name'               => __('Encryption tool'),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'datatype'           => 'string',
           'joinparams'         => [
              'jointype'           => 'itemtype_item'
           ]
        ];

        $tab[] = [
           'id'                 => '176',
           'table'              => self::getTable(),
           'field'              => 'encryption_algorithm',
           'name'               => __('Encryption algorithm'),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'datatype'           => 'string',
           'joinparams'         => [
              'jointype'           => 'itemtype_item'
           ]
        ];

        $tab[] = [
           'id'                 => '177',
           'table'              => self::getTable(),
           'field'              => 'encryption_type',
           'name'               => __('Encryption type'),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'datatype'           => 'string',
           'joinparams'         => [
              'jointype'           => 'itemtype_item'
           ]
        ];

        return $tab;
    }

    /**
     * Get all the possible value for the "encryption_status" field
     *
     * @return array The list of possible values
     */
    public static function getAllEncryptionStatus()
    {
        return [
           self::ENCRYPTION_STATUS_NO          => __('Not encrypted'),
           self::ENCRYPTION_STATUS_PARTIALLY   => __('Partially encrypted'),
           self::ENCRYPTION_STATUS_YES         => __('Encrypted'),
        ];
    }

    /**
     * Get the correct label for each encryption status
     *
     * @return string The appropriate label
     */
    public static function getEncryptionStatus($status)
    {
        $all = self::getAllEncryptionStatus();
        if (!isset($all[$status])) {
            Toolbox::logWarning(
                sprintf(
                    'Encryption status %1$s does not exixts!',
                    $status
                )
            );
            return NOT_AVAILABLE;
        }
        return $all[$status];
    }

    /**
     * Print the encryption status dropdown
     *
     * @param integer $value   Current value (defaut self::ENCRYPTION_STATUS_NO)
     * @param array   $options Array of possible options:
     *    - name : name of the dropdown (default encryption_status)
     *
     * @return string the string to display
     */
    public static function getEncryptionStatusDropdown($value = self::ENCRYPTION_STATUS_NO, $options = [])
    {
        $name = 'encryption_status';
        if (isset($options['name'])) {
            $name = $options['name'];
        }
        $values = self::getAllEncryptionStatus();

        return Dropdown::showFromArray(
            $name,
            $values,
            [
              'value'   => $value,
              'display' => false
            ]
        );
    }

    /**
     * List specifics value for selection
     *
     * @param string       $field   Name of the field
     * @param string       $name    Name of the select (if empty use linkfield) (default '')
     * @param string|array $values  Value(s) to select (default '')
     * @param array        $options Array of options
     *
     * @return string the string to display
     */
    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;

        switch ($field) {
            case 'encryption_status':
                return self::getEncryptionStatusDropdown($values[$field], [
                   'name'  => $name,
                ]);
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }

    /**
     * Display a specific field value
     *
     * @param string       $field   Name of the field
     * @param string|array $values  Value(s) to display
     * @param array        $options Array of options
     *
     * @return string the string to display
    **/
    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }

        switch ($field) {
            case 'encryption_status':
                return self::getEncryptionStatus($values[$field]);
        }

        return parent::getSpecificValueToDisplay($field, $values, $options);
    }
}
