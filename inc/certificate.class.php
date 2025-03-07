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
 * @since 9.2
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Class to declare a certificate
 */
class Certificate extends CommonDBTM
{
    use Glpi\Features\Clonable;

    public $dohistory           = true;
    public static $rightname           = "certificate";
    protected $usenotepad       = true;

    public function getCloneRelations(): array
    {
        return [
           Infocom::class,
           Contract_Item::class,
           Document_Item::class,
           KnowbaseItem_Item::class
        ];
    }

    public static function getTypeName($nb = 0)
    {
        return _n('Certificate', 'Certificates', $nb);
    }

    /**
     * Clean certificate items
     */
    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
              Certificate_Item::class,
            ]
        );
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
           'massiveaction'      => false, // implicit key==1
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '2',
           'table'              => $this->getTable(),
           'field'              => 'id',
           'name'               => __('ID'),
           'massiveaction'      => false, // implicit field is id
           'datatype'           => 'number'
        ];

        $tab[] = [
           'id'                 => '5',
           'table'              => $this->getTable(),
           'field'              => 'serial',
           'name'               => __('Serial number'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '6',
           'table'              => $this->getTable(),
           'field'              => 'otherserial',
           'name'               => __('Inventory number'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

        $tab[] = [
           'id'                 => '7',
           'table'              => 'glpi_certificatetypes',
           'field'              => 'name',
           'name'               => _n('Type', 'Types', 1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '8',
           'table'              => $this->getTable(),
           'field'              => 'dns_suffix',
           'name'               => __('DNS suffix'),
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '9',
           'table'              => $this->getTable(),
           'field'              => 'is_autosign',
           'name'               => __('Self-signed'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '10',
           'table'              => $this->getTable(),
           'field'              => 'date_expiration',
           'name'               => __('Expiration date'),
           'datatype'           => 'date',
           'maybefuture'        => true,
        ];

        $tab[] = [
           'id'                 => '11',
           'table'              => $this->getTable(),
           'field'              => 'command',
           'name'               => __('Command used'),
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '12',
           'table'              => $this->getTable(),
           'field'              => 'certificate_request',
           'name'               => __('Certificate request (CSR)'),
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '13',
           'table'              => $this->getTable(),
           'field'              => 'certificate_item',
           'name'               => self::getTypeName(1),
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '14',
           'table'              => 'glpi_certificates_items',
           'field'              => 'items_id',
           'name'               => _n('Associated item', 'Associated items', Session::getPluralNumber()),
           'nosearch'           => true,
           'massiveaction'      => false,
           'forcegroupby'       => true,
           'additionalfields'   => ['itemtype'],
           'joinparams'         => ['jointype' => 'child']
        ];

        $tab[] = [
           'id'                 => '15',
           'table'              => $this->getTable(),
           'field'              => 'comment',
           'name'               => __('Comments'),
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '16',
           'table'              => $this->getTable(),
           'field'              => 'contact',
           'name'               => __('Alternate username'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '17',
           'table'              => $this->getTable(),
           'field'              => 'contact_num',
           'name'               => __('Alternate username number'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '18',
           'table'              => $this->getTable(),
           'field'              => 'dns_name',
           'name'               => __('DNS name'),
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '19',
           'table'              => $this->getTable(),
           'field'              => 'date_mod',
           'name'               => __('Last update'),
           'datatype'           => 'datetime',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '23',
           'table'              => 'glpi_manufacturers',
           'field'              => 'name',
           'name'               => Manufacturer::getTypeName(1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '24',
           'table'              => 'glpi_users',
           'field'              => 'name',
           'linkfield'          => 'users_id_tech',
           'name'               => __('Technician in charge of the hardware'),
           'datatype'           => 'dropdown',
           'right'              => 'own_ticket'
        ];

        $tab[] = [
           'id'                 => '31',
           'table'              => 'glpi_states',
           'field'              => 'completename',
           'name'               => __('Status'),
           'datatype'           => 'dropdown',
           'condition'          => ['is_visible_certificate' => 1]
        ];

        $tab[] = [
           'id'                 => '49',
           'table'              => 'glpi_groups',
           'field'              => 'completename',
           'linkfield'          => 'groups_id_tech',
           'name'               => __('Group in charge of the hardware'),
           'condition'          => ['is_assign' => 1],
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '61',
           'table'              => $this->getTable(),
           'field'              => 'template_name',
           'name'               => __('Template name'),
           'datatype'           => 'text',
           'massiveaction'      => false,
           'nosearch'           => true,
           'nodisplay'          => true,
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '70',
           'table'              => 'glpi_users',
           'field'              => 'name',
           'name'               => User::getTypeName(1),
           'datatype'           => 'dropdown',
           'right'              => 'all'
        ];

        $tab[] = [
           'id'                 => '71',
           'table'              => 'glpi_groups',
           'field'              => 'completename',
           'name'               => Group::getTypeName(1),
           'condition'          => ['is_itemgroup' => 1],
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '72',
           'table'              => 'glpi_certificates_items',
           'field'              => 'id',
           'name'               => _x('quantity', 'Number of associated items'),
           'forcegroupby'       => true,
           'usehaving'          => true,
           'datatype'           => 'count',
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'child'
           ]
        ];

        $tab[] = [
           'id'                 => '80',
           'table'              => 'glpi_entities',
           'field'              => 'completename',
           'name'               => Entity::getTypeName(1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '86',
           'table'              => $this->getTable(),
           'field'              => 'is_recursive',
           'name'               => __('Child entities'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '121',
           'table'              => $this->getTable(),
           'field'              => 'date_creation',
           'name'               => __('Creation date'),
           'datatype'           => 'datetime',
           'massiveaction'      => false
        ];

        // add objectlock search options
        $tab = array_merge($tab, ObjectLock::rawSearchOptionsToAdd(get_class($this)));
        $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

        return $tab;
    }

    public static function rawSearchOptionsToAdd($itemtype = null)
    {
        $tab = [];
        $name = static::getTypeName(Session::getPluralNumber());

        if (!self::canView()) {
            return $tab;
        }

        $joinparams = [
           'beforejoin'         => [
              'table'              => Certificate_Item::getTable(),
              'joinparams'         => [
                 'jointype'           => 'itemtype_item',
                 'specific_itemtype'  => $itemtype
              ]
           ]
        ];

        $tab[] = [
           'id'                 => 'certificate',
           'name'               => $name
        ];

        $tab[] = [
           'id'                 => '1300',
           'table'              => self::getTable(),
           'field'              => 'name',
           'name'               => __('Name'),
           'datatype'           => 'itemlink',
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'joinparams'         => $joinparams
        ];

        $tab[] = [
           'id'                 => '1301',
           'table'              => self::getTable(),
           'field'              => 'serial',
           'datatype'           => 'string',
           'name'               => __('Serial number'),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'joinparams'         => $joinparams
        ];

        $tab[] = [
           'id'                 => '1302',
           'table'              => self::getTable(),
           'field'              => 'otherserial',
           'datatype'           => 'string',
           'name'               => __('Inventory number'),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'joinparams'         => $joinparams
        ];

        $tab[] = [
           'id'                 => '1304',
           'table'              => CertificateType::getTable(),
           'field'              => 'name',
           'datatype'           => 'dropdown',
           'name'               => _n('Type', 'Types', 1),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => self::getTable(),
                 'joinparams'         => $joinparams
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '1305',
           'table'              => self::getTable(),
           'field'              => 'comment',
           'name'               => __('Comments'),
           'forcegroupby'       => true,
           'datatype'           => 'text',
           'massiveaction'      => false,
           'joinparams'         => $joinparams
        ];

        $tab[] = [
           'id'                 => '1306',
           'table'              => self::getTable(),
           'field'              => 'date_expiration',
           'name'               => __('Expiration'),
           'forcegroupby'       => true,
           'datatype'           => 'date',
           'emptylabel'         => __('Never expire'),
           'massiveaction'      => false,
           'joinparams'         => $joinparams
        ];

        return $tab;
    }

    /**
     * @param array $options
     * @return array
     */
    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong)
           ->addStandardTab(__CLASS__, $ong, $options)
           ->addStandardTab('Certificate_Item', $ong, $options)
           ->addStandardTab('Domain_Item', $ong, $options)
           ->addStandardTab('Infocom', $ong, $options)
           ->addStandardTab('Contract_Item', $ong, $options)
           ->addStandardTab('Document_Item', $ong, $options)
           ->addStandardTab('KnowbaseItem_Item', $ong, $options)
           ->addStandardTab('Ticket', $ong, $options)
           ->addStandardTab('Item_Problem', $ong, $options)
           ->addStandardTab('Change_Item', $ong, $options)
           ->addStandardTab('Link', $ong, $options)
           ->addStandardTab('Lock', $ong, $options)
           ->addStandardTab('Notepad', $ong, $options)
           ->addStandardTab('Log', $ong, $options);

        return $ong;
    }

    public function prepareInputForAdd($input)
    {

        if (isset($input["id"]) && ($input["id"] > 0)) {
            $input["_oldID"] = $input["id"];
        }
        unset($input['id']);
        unset($input['withtemplate']);

        return $input;
    }


    /**
     * @param $ID
     * @param array $options
     * @return bool
     */
    public function showForm($ID)
    {
        $form = [
           'action' => Toolbox::getItemTypeFormURL('certificate'),
           'itemtype' => $this::class,
           'content' => [
              __('Certificate') => [
                 'visible' => true,
                 'inputs' => [
                    $this->isNewID($ID) ? [] : [
                       'type' => 'hidden',
                       'name' => 'id',
                       'value' => $ID
                    ],
                    __('Name') => [
                       'name' => 'name',
                       'type' => 'text',
                       'value' => $this->fields['name'] ?? '',
                    ],
                    __('Location') => [
                       'name' => 'locations_id',
                       'type' => 'select',
                       'values' => getOptionForItems("Location"),
                       'value' => $this->fields['locations_id'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], "Location"),
                    ],
                    __('Technician in charge of the hardware') => [
                       'name' => 'users_id_tech',
                       'type' => 'select',
                       'values' => getOptionsForUsers('own_ticket', ['entities_id' => $this->fields['entities_id']  ?? '']),
                       'value' => $this->fields['users_id_tech'] ?? '',
                       'actions' => getItemActionButtons(['info'], "User"),
                    ],
                    __('Types') => [
                       'name' => 'certificatetypes_id',
                       'type' => 'select',
                       'values' => getOptionForItems("CertificateType"),
                       'value' => $this->fields['certificatetypes_id'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], "CertificateType"),
                    ],
                    __('Manufacturer') . ' (' .  __('Root CA') . ')' => [
                       'name' => 'manufacturers_id',
                       'type' => 'select',
                       'values' => getOptionForItems("Manufacturer"),
                       'value' => $this->fields['manufacturers_id'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], "Manufacturer"),
                    ],
                    __('Serial number') => [
                       'name' => 'serial',
                       'type' => 'text',
                       'value' => $this->fields['serial'] ?? '',
                    ],
                    __('Inventory number') => [
                       'name' => 'otherserial',
                       'type' => 'text',
                       'value' => $this->fields['otherserial'] ?? '',
                    ],
                    __('Group in charge of the hardware') => [
                       'name' => 'groups_id_tech',
                       'type' => 'select',
                       'values' => getOptionForItems("Group"),
                       'value' => $this->fields['groups_id_tech'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], "Group"),
                    ],
                    __('User') => [
                       'name' => 'users_id',
                       'type' => 'select',
                       'values' => getOptionForItems("User"),
                       'value' => $this->fields['users_id'] ?? '',
                       'actions' => getItemActionButtons(['info'], "User"),
                    ],
                    __('Group') => [
                       'name' => 'groups_id',
                       'type' => 'select',
                       'values' => getOptionForItems("Group"),
                       'value' => $this->fields['groups_id'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], "Group"),
                    ],
                    __('Alternate username number') => [
                       'name' => 'contact_num',
                       'type' => 'text',
                       'value' => $this->fields['contact_num'] ?? '',
                    ],
                    __('Alternate username') => [
                       'name' => 'contact',
                       'type' => 'text',
                       'value' => $this->fields['contact'] ?? '',
                    ],
                    __('Self-signed') => [
                       'name' => 'is_autosign',
                       'type' => 'checkbox',
                       'value' => $this->fields['is_autosign'] ?? '',
                    ],
                    __('DNS name') => [
                       'name' => 'dns_name',
                       'type' => 'text',
                       'value' => $this->fields['dns_name'] ?? '',
                    ],
                    __('DNS suffix') => [
                       'name' => 'dns_suffix',
                       'type' => 'text',
                       'value' => $this->fields['dns_suffix'] ?? '',
                    ],
                    __('Comments') => [
                       'name' => 'comment',
                       'type' => 'textarea',
                       'value' => $this->fields['comment'] ?? '',
                    ],
                    __('Expiration date') => [
                       'name' => 'date_expiration',
                       'type' => 'date',
                       'value' => $this->fields['date_expiration'] ?? '',
                    ],
                    __('Command used') => [
                       'name' => 'command',
                       'type' => 'textarea',
                       'value' => $this->fields['command'] ?? '',
                    ],
                    __('Certificate request (CSR)') => [
                       'name' => 'certificate_request',
                       'type' => 'textarea',
                       'value' => $this->fields['certificate_request'] ?? '',
                    ],
                    __('Certificat') => [
                       'name' => 'certificate_item',
                       'type' => 'textarea',
                       'value' => $this->fields['certificate_item'] ?? '',
                    ],
                 ]
              ]
           ]
        ];
        renderTwigForm($form, '', $this->fields);

        return true;
    }

    /**
     * @since 0.85
     *
     * @see CommonDBTM::getSpecificMassiveActions()
     * @param null $checkitem
     * @return array
     */
    public function getSpecificMassiveActions($checkitem = null)
    {
        $actions = parent::getSpecificMassiveActions($checkitem);

        if (Session::getCurrentInterface() == 'central') {
            if (self::canUpdate()) {
                $actions[__CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'install']
                   = _x('button', 'Associate certificate');
                $actions[__CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'uninstall']
                   = _x('button', 'Dissociate certificate');
            }
        }
        return $actions;
    }


    /**
     * @since 0.85
     *
     * @see CommonDBTM::showMassiveActionsSubForm()
     * @param MassiveAction $ma
     * @return bool|false
     */
    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {

        switch ($ma->getAction()) {
            case __CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'install':
                Dropdown::showSelectItemFromItemtypes([
                   'items_id_name' => 'item_item',
                   'itemtype_name' => 'typeitem',
                   'itemtypes'     => self::getTypes(true),
                   'checkright'   => true
                ]);
                echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                return true;
                break;
            case __CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'uninstall':
                Dropdown::showSelectItemFromItemtypes([
                   'items_id_name' => 'item_item',
                   'itemtype_name' => 'typeitem',
                   'itemtypes'     => self::getTypes(true),
                   'checkright'    => true
                ]);
                echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                return true;
                break;
        }
        return parent::showMassiveActionsSubForm($ma);
    }


    /**
     * @since 0.85
     *
     * @see CommonDBTM::processMassiveActionsForOneItemtype()
     * @param MassiveAction $ma
     * @param CommonDBTM $item
     * @param array $ids
     * @return void
     */
    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {

        $certif_item = new Certificate_Item();

        switch ($ma->getAction()) {
            case __CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'add_item':
                $input = $ma->getInput();
                foreach ($ids as $id) {
                    $input = [
                       'certificates_id' => $input['certificates_id'],
                       'items_id'        => $id,
                       'itemtype'        => $item->getType()
                    ];
                    if ($certif_item->can(-1, UPDATE, $input)) {
                        if ($certif_item->add($input)) {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                        } else {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        }
                    } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                    }
                }

                return;

            case __CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'install':
                $input = $ma->getInput();
                foreach ($ids as $key) {
                    if ($item->can($key, UPDATE)) {
                        $values = [
                           'certificates_id' => $key,
                           'items_id' => $input["item_item"],
                           'itemtype' => $input['typeitem']
                        ];
                        if ($certif_item->add($values)) {
                            $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                        } else {
                            $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                        }
                    } else {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_NORIGHT);
                        $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
                    }
                }
                return;

            case __CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'uninstall':
                $input = $ma->getInput();
                foreach ($ids as $key) {
                    if ($certif_item->deleteItemByCertificatesAndItem($key, $input['item_item'], $input['typeitem'])) {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                    } else {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                    }
                }
                return;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    /**
     * Type than could be linked to a certificate
     *
     * @param boolean $all Get all possible types or only allowed ones
     *
     * @return array of types
     **/
    public static function getTypes($all = false)
    {
        global $CFG_GLPI;

        $types = $CFG_GLPI['certificate_types'];
        foreach ($types as $key => $type) {
            if (!class_exists($type)) {
                continue;
            }

            if (!$type::canView()) {
                unset($types[$key]);
            }
        }
        return $types;
    }

    /**
     * Give cron information
     *
     * @param $name : task's name
     *
     * @return array
     **/
    public static function cronInfo($name)
    {
        return ['description' => __('Send alarms on expired certificate')];
    }

    /**
     * Cron action on certificates : alert on expired certificates
     *
     * @param CronTask $task CronTask to log, if NULL display (default NULL)
     *
     * @return integer 0 : nothing to do 1 : done with success
     **/
    public static function cronCertificate($task = null)
    {
        global $DB, $CFG_GLPI;

        $cron_status = 1;

        if (!$CFG_GLPI['use_notifications']) {
            return 0;
        }

        $message      = [];
        foreach (array_keys(Entity::getEntitiesToNotify('use_certificates_alert')) as $entity) {
            $before = Entity::getUsedConfig('send_certificates_alert_before_delay', $entity);
            // Check licenses
            $result = $DB->request(
                [
                  'SELECT'    => [
                     'glpi_certificates.*',
                  ],
                  'FROM'      => self::getTable(),
                  'LEFT JOIN' => [
                     'glpi_alerts' => [
                        'FKEY'   => [
                           'glpi_alerts'       => 'items_id',
                           'glpi_certificates' => 'id',
                           [
                              'AND' => [
                                 'glpi_alerts.itemtype' => __CLASS__,
                                 'glpi_alerts.type'     => Alert::END,
                              ],
                           ],
                        ]
                     ]
                  ],
                  'WHERE'     => [
                     'glpi_alerts.date'              => null,
                     'glpi_certificates.is_deleted'  => 0,
                     'glpi_certificates.is_template' => 0,
                     [
                        'NOT' => ['glpi_certificates.date_expiration' => null],
                     ],
                     [
                        'RAW' => [
                           'DATEDIFF(' . DBmysql::quoteName('glpi_certificates.date_expiration') . ', CURDATE())' => ['<', $before]
                        ]
                     ],
                     'glpi_certificates.entities_id' => $entity,
                  ],
                ]
            );

            $message = "";
            $items   = [];

            foreach ($result as $certificate) {
                $name     = $certificate['name'] . ' - ' . $certificate['serial'];
                //TRANS: %1$s the license name, %2$s is the expiration date
                $message .= sprintf(
                    __('Certificate %1$s expired on %2$s'),
                    Html::convDate($certificate["date_expiration"]),
                    $name
                ) . "<br>\n";
                $items[$certificate['id']] = $certificate;
            }

            if (!empty($items)) {
                $alert   = new Alert();
                $options = [
                   'entities_id'  => $entity,
                   'certificates' => $items,
                ];

                if (NotificationEvent::raiseEvent('alert', new self(), $options)) {
                    $entityname = Dropdown::getDropdownName("glpi_entities", $entity);
                    if ($task) {
                        //TRANS: %1$s is the entity, %2$s is the message
                        $task->log(sprintf(__('%1$s: %2$s') . "\n", $entityname, $message));
                        $task->addVolume(1);
                    } else {
                        Session::addMessageAfterRedirect(sprintf(
                            __('%1$s: %2$s'),
                            $entityname,
                            $message
                        ));
                    }

                    $input = [
                       'type'     => Alert::END,
                       'itemtype' => __CLASS__,
                    ];

                    // add alerts
                    foreach ($items as $ID => $certificate) {
                        $input["items_id"] = $ID;
                        $alert->add($input);
                        unset($alert->fields['id']);
                    }
                } else {
                    $entityname = Dropdown::getDropdownName('glpi_entities', $entity);
                    //TRANS: %s is entity name
                    $msg = sprintf(__('%1$s: %2$s'), $entityname, __('Send Certificates alert failed'));
                    if ($task) {
                        $task->log($msg);
                    } else {
                        Session::addMessageAfterRedirect($msg, false, ERROR);
                    }
                }
            }
        }
        return $cron_status;
    }

    /**
     * Display debug information for current object
     **/
    public function showDebug()
    {
        NotificationEvent::debugEvent($this);
    }


    public static function getIcon()
    {
        return "fas fa-certificate";
    }
}
