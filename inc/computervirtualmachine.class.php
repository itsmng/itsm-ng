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
 * Virtual machine management
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * ComputerVirtualMachine Class
 *
 * Class to manage virtual machines
**/
class ComputerVirtualMachine extends CommonDBChild
{
    // From CommonDBChild
    public static $itemtype = 'Computer';
    public static $items_id = 'computers_id';
    public $dohistory       = true;


    public static function getTypeName($nb = 0)
    {
        return __('Virtualization');
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (
            !$withtemplate
            && ($item->getType() == 'Computer')
            && Computer::canView()
        ) {
            $nb = 0;
            if ($_SESSION['glpishow_count_on_tabs']) {
                $nb = countElementsInTable(
                    self::getTable(),
                    ['computers_id' => $item->getID(), 'is_deleted' => 0 ]
                );
            }
            return self::createTabEntry(self::getTypeName(), $nb);
        }
        return '';
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);

        return $ong;
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        self::showForVirtualMachine($item);
        self::showForComputer($item, $withtemplate);
        return true;
    }


    public function post_getEmpty()
    {

        $this->fields["vcpu"] = '0';
        $this->fields["ram"]  = '0';
    }


    /**
     * Print the version form
     *
     * @param $ID        integer ID of the item
     * @param $options   array
     *     - target for the Form
     *     - computers_id ID of the computer for add process
     *
     * @return true if displayed  false if item not found or not right to display
    **/
    public function showForm($ID, $options = [])
    {

        if (!Session::haveRight("computer", UPDATE)) {
            return false;
        }

        $inline = !empty($options['inline']);
        $is_new = self::isNewID($ID);
        $comp = new Computer();

        if (!$is_new) {
            $this->check($ID, READ);
            $comp->getFromDB($this->fields['computers_id']);
        } else {
            // Create item
            $this->check(-1, CREATE, $options);
            $comp->getFromDB($options['computers_id']);
        }

        if ($inline && (($options['withtemplate'] ?? 0) == 2 || !$comp->canEdit($comp->getID()))) {
            return false;
        }

        $autoinventory_information = '';
        if (!$is_new && $this->fields['is_dynamic']) {
            ob_start();
            Plugin::doHook("autoinventory_information", $this);
            $autoinventory_information = ob_get_clean();
        }

        $computerValue = '';
        if ($link_computer = self::findVirtualMachine($this->fields)) {
            $computer = new Computer();
            if ($computer->getFromDB($link_computer)) {
                $computerValue = $computer->getLink(['comments' => true]);
            } else {
                $computerValue = NOT_AVAILABLE;
            }
        }

        $form = [
           'action' => $this->getFormURL(),
           'buttons' => [
              'submit' => [
                 'type' => 'submit',
                 'name' => self::isNewID($ID) ? 'add' : 'update',
                 'value' => self::isNewID($ID) ? __('Add') : __('Update'),
                 'class' => self::isNewID($ID) ? 'btn btn-primary' : 'btn btn-secondary',
                 'icon' => self::isNewID($ID) ? 'fas fa-plus' : null
              ],
           ],
           'content' => [
              ($is_new ? __('Add a virtual machine') : self::getTypeName(1)) => [
                 'visible' => true,
                 'inputs' => [
                    $is_new ? [
                       'type' => 'hidden',
                       'name' => 'computers_id',
                       'value' => $options['computers_id'],
                    ] : [
                       'type' => 'hidden',
                       'name' => 'id',
                       'value' => $ID,
                    ],
                    $inline ? [
                       'type' => 'hidden',
                       'name' => '_inline',
                       'value' => 1,
                    ] : [],
                    Computer::getTypeName() => [
                       'content' => $comp->getLink(['comments' => true]),
                    ],
                    __('Automatic inventory') => Plugin::haveImport() ? [
                          'content' => !$is_new && $this->fields['is_dynamic'] ? $autoinventory_information : __('No'),
                    ] : [],
                    __('Name') => [
                       'type' => 'text',
                       'name' => 'name',
                       'value' => $this->fields['name'],
                    ],
                    __('Comments') => [
                       'type' => 'textarea',
                       'name' => 'comment',
                       'value' => $this->fields['comment'],
                    ],
                    VirtualMachineType::getTypeName(1) => [
                       'type' => 'select',
                       'name' => 'virtualmachinetypes_id',
                       ...getAjaxDropdownOptions('VirtualMachineType'),
                       'value' => $this->fields['virtualmachinetypes_id'],
                       'actions' => getItemActionButtons(['info', 'add'], 'VirtualMachineType')
                    ],
                    VirtualMachineSystem::getTypeName(1) => [
                       'type' => 'select',
                       'name' => 'virtualmachinesystems_id',
                       ...getAjaxDropdownOptions('VirtualMachineSystem'),
                       'value' => $this->fields['virtualmachinesystems_id'],
                       'actions' => getItemActionButtons(['info', 'add'], 'VirtualMachineSystem')
                    ],
                    VirtualMachineState::getTypeName(1) => [
                       'type' => 'select',
                       'name' => 'virtualmachinestates_id',
                       ...getAjaxDropdownOptions('VirtualMachineState'),
                       'value' => $this->fields['virtualmachinestates_id'],
                       'actions' => getItemActionButtons(['info', 'add'], 'VirtualMachineState')
                    ],
                    __('UUID') => [
                       'type' => 'text',
                       'name' => 'uuid',
                       'value' => $this->fields['uuid'],
                    ],
                    __('Machine') => !$is_new ? [
                       'content' => $computerValue,
                    ] : [],
                    sprintf(__('%1$s (%2$s)'), _n('Memory', 'Memories', 1), __('Mio')) => [
                       'type' => 'number',
                       'name' => 'ram',
                       'value' => $this->fields['ram'],
                       'min'    => 0
                    ],
                    _x('quantity', 'Processors number') => [
                       'type' => 'number',
                       'name' => 'vcpu',
                       'value' => $this->fields['vcpu'],
                       'min'    => 0
                    ],
                 ]
              ]
           ]
        ];
        renderTwigForm($form);

        return true;
    }


    /**
     * Show hosts for a virtualmachine
     *
     * @param $comp   Computer object that represents the virtual machine
     *
     * @return void
    **/
    public static function showForVirtualMachine(Computer $comp)
    {

        $ID = $comp->fields['id'];

        if (!$comp->getFromDB($ID) || !$comp->can($ID, READ)) {
            return;
        }

        if (empty($comp->fields['uuid'])) {
            return;
        }

        $hosts = getAllDataFromTable(self::getTable(), [
            'RAW' => [
                'LOWER(uuid)' => self::getUUIDRestrictCriteria($comp->fields['uuid']),
            ],
        ]);
        if (empty($hosts)) {
            return;
        }

        $values = [];
        foreach ($hosts as $host) {
            $computer = new Computer();
            if (!$computer->getFromDB($host['computers_id'])) {
                continue;
            }
            $values[] = [
                'name' => self::getComputerTableLink($computer),
                'entity' => htmlspecialchars(
                    Dropdown::getDropdownName('glpi_entities', $computer->fields['entities_id']),
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8',
                    false
                ),
            ];
        }

        renderTwigTemplate('computer/virtualmachines.twig', [
            'title' => __('List of virtualized environments'),
            'id' => 'computerVirtualMachineHostsTable_' . $ID,
            'state_key' => 'ComputerVirtualMachineHosts_' . $ID,
            'fields' => [
                'name' => __('Name'),
                'entity' => Entity::getTypeName(1),
            ],
            'values' => $values,
            'pageSize' => (int) ($_SESSION['glpilist_limit'] ?? 15),
        ]);
    }


    /**
     * Print the virtual machines hosted by a computer.
     *
     * @param Computer $comp Computer object
     * @param integer $withtemplate Template mode (2 disables editing)
     *
     * @return void|boolean Returns false if there is a rights error.
     */
    public static function showForComputer(Computer $comp, $withtemplate = 0)
    {
        $ID = $comp->fields['id'];
        if (!$comp->getFromDB($ID) || !$comp->can($ID, READ)) {
            return false;
        }

        $escape = static function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
        };
        $has_import = Plugin::haveImport();
        $virtualmachines = getAllDataFromTable(self::getTable(), [
            'WHERE' => [
                'computers_id' => $ID,
                'is_deleted' => 0,
            ],
            'ORDER' => ['name', 'id'],
        ]);
        $fields = [
            'name' => __('Name'),
            'comment' => _n('Comment', 'Comments', 1),
        ];
        if ($has_import) {
            $fields['is_dynamic'] = __('Automatic inventory');
        }
        $fields += [
            'type' => VirtualMachineType::getTypeName(1),
            'system' => VirtualMachineSystem::getTypeName(1),
            'state' => __('State'),
            'uuid' => __('UUID'),
            'vcpu' => _x('quantity', 'Processors number'),
            'ram' => sprintf(__('%1$s (%2$s)'), _n('Memory', 'Memories', 1), __('Mio')),
            'computer' => __('Machine'),
        ];
        Session::initNavigateListItems(
            __CLASS__,
            sprintf(
                __('%1$s = %2$s'),
                Computer::getTypeName(1),
                empty($comp->fields['name']) ? "($ID)" : $comp->fields['name']
            )
        );

        $values = [];
        $vm = new self();
        foreach ($virtualmachines as $virtualmachine) {
            $vm->getFromDB($virtualmachine['id']);
            $name = $escape($vm->getNameID());
            if ($vm->can($virtualmachine['id'], READ)) {
                $name = '<a href="' . $escape($vm->getLinkURL()) . '">' . $name . '</a>';
            }
            $computer_link = '';
            if ($link_computer = self::findVirtualMachine($virtualmachine)) {
                $computer = new Computer();
                if ($computer->getFromDB($link_computer)) {
                    $computer_link = self::getComputerTableLink($computer);
                }
            }
            $values[] = [
                'name' => $name,
                'comment' => $escape($virtualmachine['comment']),
                'type' => $escape(Dropdown::getDropdownName(
                    'glpi_virtualmachinetypes',
                    $virtualmachine['virtualmachinetypes_id']
                )),
                'system' => $escape(Dropdown::getDropdownName(
                    'glpi_virtualmachinesystems',
                    $virtualmachine['virtualmachinesystems_id']
                )),
                'state' => $escape(Dropdown::getDropdownName(
                    'glpi_virtualmachinestates',
                    $virtualmachine['virtualmachinestates_id']
                )),
                'uuid' => $escape($virtualmachine['uuid']),
                'vcpu' => $escape($virtualmachine['vcpu']),
                'ram' => $escape($virtualmachine['ram']),
                'computer' => $computer_link,
            ];
            if ($has_import) {
                $values[array_key_last($values)]['is_dynamic'] = $escape(Dropdown::getYesNo($vm->isDynamic()));
            }
            Session::addToNavigateListItems(__CLASS__, $virtualmachine['id']);
        }

        if ($withtemplate != 2 && $comp->canEdit($ID) && Session::haveRight('computer', UPDATE)) {
            $virtualmachine = new self();
            $virtualmachine->showForm(-1, [
                'computers_id' => $ID,
                'inline' => true,
                'withtemplate' => $withtemplate,
            ]);
        }

        renderTwigTemplate('computer/virtualmachines.twig', [
            'title' => __('List of virtualized environments'),
            'empty_message' => __('No virtualized environment associated with the computer'),
            'id' => 'computerVirtualMachinesTable_' . $ID,
            'state_key' => 'ComputerVirtualMachines_' . $ID,
            'fields' => $fields,
            'values' => $values,
            'pageSize' => (int) ($_SESSION['glpilist_limit'] ?? 15),
        ]);
    }


    /**
     * Build an escaped computer link and inventory tooltip for the VM tables.
     */
    private static function getComputerTableLink(Computer $computer)
    {
        $escape = static function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
        };
        $name = $escape($computer->fields['name']);
        if (!$computer->can($computer->getID(), READ)) {
            return $name;
        }

        $tooltip = '<table><tr><th>' . $escape(__('Name')) . '</th><td>' . $name . '</td></tr>';
        $tooltip .= '<tr><th>' . $escape(__('Serial number')) . '</th><td>'
            . $escape($computer->fields['serial']) . '</td></tr>';
        $tooltip .= '<tr><th>' . $escape(__('Comments')) . '</th><td>'
            . $escape($computer->fields['comment']) . '</td></tr></table>';

        return '<a href="' . $escape($computer->getLinkURL()) . '">' . $name . '</a> '
            . Html::showToolTip($tooltip, ['display' => false]);
    }


    /**
     * Get correct uuid sql search for virtualmachines
     *
     * @since 9.3.1
     *
     * @param string $uuid the uuid given
     *
     * @return array the restrict SQL clause which contains uuid, uuid with first block flipped,
     * uuid with 3 first block flipped
    **/
    public static function getUUIDRestrictCriteria($uuid)
    {

        //More infos about uuid, please see wikipedia :
        //http://en.wikipedia.org/wiki/Universally_unique_identifier
        //Some uuid are not conform, so preprocessing is necessary
        //A good uuid likes lik : 550e8400-e29b-41d4-a716-446655440000

        //Case one : for example some uuid are like that :
        //56 4d 77 d0 6b ef 3d da-4d 67 5c 80 a9 52 e2 c9
        $pattern  = "/([\w]{2})\ ([\w]{2})\ ([\w]{2})\ ([\w]{2})\ ";
        $pattern .= "([\w]{2})\ ([\w]{2})\ ([\w]{2})\ ([\w]{2})-";
        $pattern .= "([\w]{2})\ ([\w]{2})\ ([\w]{2})\ ([\w]{2})\ ";
        $pattern .= "([\w]{2})\ ([\w]{2})\ ([\w]{2})\ ([\w]{2})/";
        if (preg_match($pattern, $uuid)) {
            $uuid = preg_replace($pattern, "$1$2$3$4-$5$6-$7$8-$9$10-$11$12$13$14$15$16", $uuid);
        }

        //Case two : why this code ? Because some dmidecode < 2.10 is buggy.
        //On unix is flips first block of uuid and on windows flips 3 first blocks...
        $in      = [strtolower((string) $uuid)];
        $regexes = [
           "/([\w]{2})([\w]{2})([\w]{2})([\w]{2})(.*)/"                                        => "$4$3$2$1$5",
           "/([\w]{2})([\w]{2})([\w]{2})([\w]{2})-([\w]{2})([\w]{2})-([\w]{2})([\w]{2})(.*)/"  => "$4$3$2$1-$6$5-$8$7$9"
        ];
        foreach ($regexes as $pattern => $replace) {
            $reverse_uuid = preg_replace($pattern, $replace, (string) $uuid);
            if ($reverse_uuid) {
                $in[] = strtolower($reverse_uuid);
            }
        }

        return $in;
    }


    /**
     * Find a virtual machine by uuid
     *
     * @param array $fields  Array of virtualmachine fields
     *
     * @return integer|boolean ID of the computer that have this uuid or false otherwise
    **/
    public static function findVirtualMachine($fields = [])
    {
        global $DB;

        if (!isset($fields['uuid']) || empty($fields['uuid'])) {
            return false;
        }

        $iterator = $DB->request([
           'SELECT' => 'id',
           'FROM'   => 'glpi_computers',
           'WHERE'  => [
              'RAW' => [
                 'LOWER(uuid)'  => self::getUUIDRestrictCriteria($fields['uuid'])
              ]
           ]
        ]);

        //Virtual machine found, return ID
        if (count($iterator) == 1) {
            $result = $iterator->next();
            return $result['id'];
        } elseif (count($iterator) > 1) {
            Toolbox::logWarning(
                sprintf(
                    'findVirtualMachine expects to get one result, %1$s found!',
                    count($iterator)
                )
            );
        }

        return false;
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
           'field'              => 'uuid',
           'name'               => __('UUID'),
           'datatype'           => 'string',
           'massiveaction'      => false,
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '3',
           'table'              => $this->getTable(),
           'field'              => 'ram',
           'name'               => _n('Memory', 'Memories', 1),
           'datatype'           => 'string',
           'massiveaction'      => false,
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '4',
           'table'              => $this->getTable(),
           'field'              => 'vcpu',
           'name'               => __('processor number'),
           'datatype'           => 'string',
           'massiveaction'      => false,
           'autocomplete'       => true,
        ];

        return $tab;
    }

    public static function rawSearchOptionsToAdd($itemtype)
    {
        $tab = [];

        $name = _n('Virtual machine', 'Virtual machines', Session::getPluralNumber());
        $tab[] = [
           'id'                 => 'virtualmachine',
           'name'               => $name
        ];

        $tab[] = [
           'id'                 => '160',
           'table'              => self::getTable(),
           'field'              => 'name',
           'name'               => __('Name'),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'datatype'           => 'dropdown',
           'joinparams'         => [
              'jointype'           => 'child'
           ]
        ];

        $tab[] = [
           'id'                 => '161',
           'table'              => 'glpi_virtualmachinestates',
           'field'              => 'name',
           'name'               => __('State'),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'datatype'           => 'dropdown',
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => self::getTable(),
                 'joinparams'         => [
                    'jointype'           => 'child'
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '162',
           'table'              => 'glpi_virtualmachinesystems',
           'field'              => 'name',
           'name'               => VirtualMachineSystem::getTypeName(1),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'datatype'           => 'dropdown',
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => self::getTable(),
                 'joinparams'         => [
                    'jointype'           => 'child'
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '163',
           'table'              => 'glpi_virtualmachinetypes',
           'field'              => 'name',
           'name'               => VirtualMachineType::getTypeName(1),
           'datatype'           => 'dropdown',
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => self::getTable(),
                 'joinparams'         => [
                    'jointype'           => 'child'
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '164',
           'table'              => self::getTable(),
           'field'              => 'vcpu',
           'name'               => __('processor number'),
           'datatype'           => 'number',
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'child'
           ]
        ];

        $tab[] = [
           'id'                 => '165',
           'table'              => self::getTable(),
           'field'              => 'ram',
           'name'               => _n('Memory', 'Memories', 1),
           'datatype'           => 'string',
           'unit'               => 'auto',
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'child'
           ]
        ];

        $tab[] = [
           'id'                 => '166',
           'table'              => self::getTable(),
           'field'              => 'uuid',
           'name'               => __('UUID'),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'child'
           ]
        ];

        $tab[] = [
           'id'                 => '179',
           'table'              => self::getTable(),
           'field'              => 'comment',
           'name'               => __('Virtual machine Comment'),
           'forcegroupby'       => true,
           'datatype'           => 'string',
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'child'
           ]
        ];

        return $tab;
    }
}
