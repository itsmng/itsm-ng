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
 * CartridgeItem Class
 * This class is used to manage the various types of cartridges.
 * \see Cartridge
**/
class CartridgeItem extends CommonDBTM
{
    // From CommonDBTM
    protected static $forward_entity_to = ['Cartridge', 'Infocom'];
    public $dohistory                   = true;
    protected $usenotepad               = true;

    public static $rightname                   = 'cartridge';

    public static function getTypeName($nb = 0)
    {
        return _n('Cartridge model', 'Cartridge models', $nb);
    }


    /**
     * @see CommonGLPI::getMenuName()
     *
     * @since 0.85
    **/
    public static function getMenuName()
    {
        return Cartridge::getTypeName(Session::getPluralNumber());
    }


    /**
     * @since 0.84
     *
     * @see CommonDBTM::getPostAdditionalInfosForName
    **/
    public function getPostAdditionalInfosForName()
    {

        if (isset($this->fields["ref"]) && !empty($this->fields["ref"])) {
            return $this->fields["ref"];
        }
        return '';
    }


    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
              Cartridge::class,
              CartridgeItem_PrinterModel::class,
            ]
        );

        $class = new Alert();
        $class->cleanDBonItemDelete($this->getType(), $this->fields['id']);
    }


    public function post_getEmpty()
    {

        $this->fields["alarm_threshold"] = Entity::getUsedConfig(
            "cartriges_alert_repeat",
            $this->fields["entities_id"],
            "default_cartridges_alarm_threshold",
            10
        );
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addImpactTab($ong, $options);
        $this->addStandardTab('Cartridge', $ong, $options);
        $this->addStandardTab('CartridgeItem_PrinterModel', $ong, $options);
        $this->addStandardTab('Infocom', $ong, $options);
        $this->addStandardTab('Document_Item', $ong, $options);
        $this->addStandardTab('Link', $ong, $options);
        $this->addStandardTab('Notepad', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    ///// SPECIFIC FUNCTIONS

    /**
     * Count cartridge of the cartridge type
     *
     * @param integer $id Item id
     *
     * @return number of cartridges
     *
     * @since 9.2 add $id parameter
     **/
    public static function getCount($id)
    {
        $result = self::getAdapter()->request([
           'COUNT'  => 'cpt',
           'FROM'   => 'glpi_cartridges',
           'WHERE'  => ['cartridgeitems_id' => $id]
        ])->fetchAssociative();
        return $result['cpt'];
    }


    /**
     * Add a compatible printer type for a cartridge type
     *
     * @param $cartridgeitems_id  integer: cartridge type identifier
     * @param printermodels_id    integer: printer type identifier
     *
     * @return boolean : true for success
    **/
    public function addCompatibleType($cartridgeitems_id, $printermodels_id)
    {
        if (($cartridgeitems_id > 0) && ($printermodels_id > 0)) {
            $link = new CartridgeItem_PrinterModel();

            $link->fields['cartridgeitems_id'] = $cartridgeitems_id;
            $link->fields['printermodels_id'] = $printermodels_id;

            if ($link->addToDB()) {
                return true;
            }
        }
        return false;
    }


    /**
     * Print the cartridge type form
     *
     * @param $ID        integer ID of the item
     * @param $options   array os possible options:
     *     - target for the Form
     *     - withtemplate : 1 for newtemplate, 2 for newobject from template
     *
     * @return boolean
    **/
    public function showForm($ID, $options = [])
    {

        $form = [
           'action' => $this->getFormURL(),
           'itemtype' => $this::class,
           'content' => [
              __('General') => [
                 'inputs' => [
                    __('Name') => [
                    'name' => 'name',
                    'type' => 'text',
                    'value' => $this->fields["name"] ?? '',
                    ],
                    _n('Type', 'Types', 1) => [
                       'name' => 'cartridgeitemtypes_id',
                       'type' => 'select',
                       'values' => getOptionForItems('CartridgeItemType'),
                       'value' => $this->fields["cartridgeitemtypes_id"] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], "CartridgeItemType"),
                    ],
                    __('Reference') => [
                       'name' => 'ref',
                       'type' => 'text',
                       'value' => $this->fields["ref"] ?? '',
                    ],
                    Manufacturer::getTypeName(1) => [
                       'name' => 'manufacturers_id',
                       'type' => 'select',
                       'values' => getOptionForItems('Manufacturer'),
                       'value' => $this->fields["manufacturers_id"] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], "Manufacturer"),
                    ],
                    __('Technician in charge of the hardware') => [
                       'name' => 'tech_users_id',
                       'type' => 'select',
                       'values' => getOptionsForUsers('own_ticket', ['entities_id' => $this->fields['entities_id']]),
                       'entity' => $this->fields["entities_id"],
                       'value' => $this->fields["tech_users_id"] ?? '',
                       'actions' => getItemActionButtons(['info'], "User"),
                    ],
                    __('Comments') => [
                       'name' => 'comment',
                       'type' => 'textarea',
                       'value' => $this->fields["comment"] ?? '',
                    ],
                    __('Group in charge of the hardware') => [
                       'name' => 'tech_groups_id',
                       'type' => 'select',
                       'values' => getOptionForItems('Group', ['is_assign' => 1,]),
                       'value' => $this->fields["groups_id_tech"] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], "Group"),
                    ],
                    __('Stock location') => [
                       'name' => 'locations_id',
                       'type' => 'select',
                       'values' => getOptionForItems('Location', ['entities_id' => $this->fields['entities_id']]),
                       'value' => $this->fields["locations_id"] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], "Location"),
                    ],
                    __('Alert threshold') => [
                       'name' => 'alarm_threshold',
                       'type' => 'number',
                       'value' => $this->fields["alarm_threshold"] ?? '',
                       'min' => 0,
                       'max' => 100,
                       'step' => 1,
                    ]
                 ]
              ],
           ]
        ];

        renderTwigForm($form, '', $this->fields);
        return true;
    }


    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '2',
           'table'              => $this->getTable(),
           'field'              => 'id',
           'name'               => __('ID'),
           'massiveaction'      => false,
           'datatype'           => 'number'
        ];

        $tab[] = [
           'id'                 => '34',
           'table'              => $this->getTable(),
           'field'              => 'ref',
           'name'               => __('Reference'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '4',
           'table'              => 'glpi_cartridgeitemtypes',
           'field'              => 'name',
           'name'               => _n('Type', 'Types', 1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '23',
           'table'              => 'glpi_manufacturers',
           'field'              => 'name',
           'name'               => Manufacturer::getTypeName(1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '9',
           'table'              => $this->getTable(),
           'field'              => '_virtual',
           'name'               => _n('Cartridge', 'Cartridges', Session::getPluralNumber()),
           'datatype'           => 'specific',
           'massiveaction'      => false,
           'nosearch'           => true,
           'nosort'             => true,
           'additionalfields'   => ['alarm_threshold']
        ];

        $tab[] = [
           'id'                 => '17',
           'table'              => 'glpi_cartridges',
           'field'              => 'id',
           'name'               => __('Number of used cartridges'),
           'datatype'           => 'count',
           'forcegroupby'       => true,
           'usehaving'          => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'child',
              'condition'          => 'AND NEWTABLE.`date_use` IS NOT NULL
                                     AND NEWTABLE.`date_out` IS NULL'
           ]
        ];

        $tab[] = [
           'id'                 => '18',
           'table'              => 'glpi_cartridges',
           'field'              => 'id',
           'name'               => __('Number of worn cartridges'),
           'datatype'           => 'count',
           'forcegroupby'       => true,
           'usehaving'          => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'child',
              'condition'          => 'AND NEWTABLE.`date_out` IS NOT NULL'
           ]
        ];

        $tab[] = [
           'id'                 => '19',
           'table'              => 'glpi_cartridges',
           'field'              => 'id',
           'name'               => __('Number of new cartridges'),
           'datatype'           => 'count',
           'forcegroupby'       => true,
           'usehaving'          => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'child',
              'condition'          => 'AND NEWTABLE.`date_use` IS NULL
                                     AND NEWTABLE.`date_out` IS NULL'
           ]
        ];

        $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

        $tab[] = [
           'id'                 => '24',
           'table'              => 'glpi_users',
           'field'              => 'name',
           'linkfield'          => 'tech_users_id',
           'name'               => __('Technician in charge of the hardware'),
           'datatype'           => 'dropdown',
           'right'              => 'own_ticket'
        ];

        $tab[] = [
           'id'                 => '49',
           'table'              => 'glpi_groups',
           'field'              => 'completename',
           'linkfield'          => 'tech_groups_id',
           'name'               => __('Group in charge of the hardware'),
           'condition'          => ['is_assign' => 1],
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '8',
           'table'              => $this->getTable(),
           'field'              => 'alarm_threshold',
           'name'               => __('Alert threshold'),
           'datatype'           => 'number',
           'toadd'              => [
              '-1'                 => 'Never'
           ]
        ];

        $tab[] = [
           'id'                 => '16',
           'table'              => $this->getTable(),
           'field'              => 'comment',
           'name'               => __('Comments'),
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '80',
           'table'              => 'glpi_entities',
           'field'              => 'completename',
           'name'               => Entity::getTypeName(1),
           'massiveaction'      => false,
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '40',
           'table'              => 'glpi_printermodels',
           'field'              => 'name',
           'datatype'           => 'dropdown',
           'name'               => _n('Printer model', 'Printer models', Session::getPluralNumber()),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_cartridgeitems_printermodels',
                 'joinparams'         => [
                    'jointype'           => 'child'
                 ]
              ]
           ]
        ];

        $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

        return $tab;
    }


    public static function cronInfo($name)
    {
        return ['description' => __('Send alarms on cartridges')];
    }


    /**
     * Cron action on cartridges : alert if a stock is behind the threshold
     *
     * @param CronTask $task CronTask for log, display information if NULL? (default NULL)
     *
     * @return void
    **/
    public static function cronCartridge($task = null)
    {
        global $CFG_GLPI;

        $cron_status = 1;
        if ($CFG_GLPI["use_notifications"]) {
            $message = [];
            $alert   = new Alert();

            foreach (Entity::getEntitiesToNotify('cartridges_alert_repeat') as $entity => $repeat) {
                // if you change this query, please don't forget to also change in showDebug()
                $result = self::getAdapter()->request(
                    [
                      'SELECT'    => [
                         'glpi_cartridgeitems.id AS cartID',
                         'glpi_cartridgeitems.entities_id AS entity',
                         'glpi_cartridgeitems.ref AS ref',
                         'glpi_cartridgeitems.name AS name',
                         'glpi_cartridgeitems.alarm_threshold AS threshold',
                         'glpi_alerts.id AS alertID',
                         'glpi_alerts.date',
                      ],
                      'FROM'      => self::getTable(),
                      'LEFT JOIN' => [
                         'glpi_alerts' => [
                            'FKEY' => [
                               'glpi_alerts'         => 'items_id',
                               'glpi_cartridgeitems' => 'id',
                               [
                                  'AND' => ['glpi_alerts.itemtype' => 'CartridgeItem'],
                               ],
                            ]
                         ]
                      ],
                      'WHERE'     => [
                         'glpi_cartridgeitems.is_deleted'      => 0,
                         'glpi_cartridgeitems.alarm_threshold' => ['>=', 0],
                         'glpi_cartridgeitems.entities_id'     => $entity,
                         'OR'                                  => [
                            ['glpi_alerts.date' => null],
                            ['glpi_alerts.date' => ['<', new QueryExpression('CURRENT_TIMESTAMP() - INTERVAL ' . $repeat . ' second')]],
                         ],
                      ],
                    ]
                );

                $message = "";
                $items   = [];

                foreach ($result as $cartridge) {
                    if (($unused = Cartridge::getUnusedNumber($cartridge["cartID"])) <= $cartridge["threshold"]) {
                        //TRANS: %1$s is the cartridge name, %2$s its reference, %3$d the remaining number
                        $message .= sprintf(
                            __('Threshold of alarm reached for the type of cartridge: %1$s - Reference %2$s - Remaining %3$d'),
                            $cartridge["name"],
                            $cartridge["ref"],
                            $unused
                        );
                        $message .= '<br>';

                        $items[$cartridge["cartID"]] = $cartridge;

                        // if alert exists -> delete
                        if (!empty($cartridge["alertID"])) {
                            $alert->delete(["id" => $cartridge["alertID"]]);
                        }
                    }
                }

                if (!empty($items)) {
                    $options = [
                       'entities_id' => $entity,
                       'items'       => $items,
                    ];

                    $entityname = Dropdown::getDropdownName("glpi_entities", $entity);
                    if (NotificationEvent::raiseEvent('alert', new CartridgeItem(), $options)) {
                        if ($task) {
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
                           'type'     => Alert::THRESHOLD,
                           'itemtype' => 'CartridgeItem',
                        ];

                        // add alerts
                        foreach (array_keys($items) as $ID) {
                            $input["items_id"] = $ID;
                            $alert->add($input);
                            unset($alert->fields['id']);
                        }
                    } else {
                        //TRANS: %s is entity name
                        $msg = sprintf(__('%s: send cartridge alert failed'), $entityname);
                        if ($task) {
                            $task->log($msg);
                        } else {
                            //TRANS: %s is the entity
                            Session::addMessageAfterRedirect($msg, false, ERROR);
                        }
                    }
                }
            }
        }

        return $cron_status;
    }


    /**
     * Print a select with compatible cartridge
     *
     * @param $printer Printer object
     *
     * @return string|boolean
    **/
    public static function dropdownForPrinter(Printer $printer)
    {

        $request = self::getAdapter()->request([
           'SELECT'       => [
              'COUNT'  => '* AS cpt',
              'glpi_locations.completename AS location',
              'glpi_cartridgeitems.ref AS ref',
              'glpi_cartridgeitems.name AS name',
              'glpi_cartridgeitems.id AS tID'
           ],
           'FROM'         => self::getTable(),
           'INNER JOIN'   => [
              'glpi_cartridgeitems_printermodels' => [
                 'ON' => [
                    'glpi_cartridgeitems_printermodels' => 'cartridgeitems_id',
                    'glpi_cartridgeitems'               => 'id'
                 ]
              ],
              'glpi_cartridges'                   => [
                 'ON' => [
                    'glpi_cartridgeitems'   => 'id',
                    'glpi_cartridges'       => 'cartridgeitems_id', [
                       'AND' => [
                          'glpi_cartridges.date_use' => null
                       ]
                    ]
                 ]
              ]
           ],
           'LEFT JOIN'    => [
              'glpi_locations'                    => [
                 'ON' => [
                    'glpi_cartridgeitems'   => 'locations_id',
                    'glpi_locations'        => 'id'
                 ]
              ]
           ],
           'WHERE'        => [
              'glpi_cartridgeitems_printermodels.printermodels_id'  => $printer->fields['printermodels_id']
           ] + getEntitiesRestrictCriteria('glpi_cartridgeitems', '', $printer->fields['entities_id'], true),
           'GROUPBY'      => 'tID',
           'ORDERBY'      => ['name', 'ref']
        ]);
        $results = [];
        while ($data = $request->fetchAssociative()) {
            $text = sprintf(__('%1$s - %2$s'), $data["name"], $data["ref"]);
            $text = sprintf(__('%1$s (%2$s)'), $text, $data["cpt"]);
            $text = sprintf(__('%1$s - %2$s'), $text, $data["location"]);
            $results[$data["tID"]] = $text;
        }
        if (count($results)) {
            return $results;
        }
        return false;
    }


    public function getEvents()
    {
        return ['alert' => __('Send alarms on cartridges')];
    }


    /**
     * Display debug information for current object
    **/
    public function showDebug()
    {

        // see query_alert in cronCartridge()
        $item = ['cartID'    => $this->fields['id'],
                      'entity'    => $this->fields['entities_id'],
                      'ref'       => $this->fields['ref'],
                      'name'      => $this->fields['name'],
                      'threshold' => $this->fields['alarm_threshold']];

        $options = [];
        $options['entities_id'] = $this->getEntityID();
        $options['items']       = [$item];
        NotificationEvent::debugEvent($this, $options);
    }


    public static function getIcon()
    {
        return Cartridge::getIcon();
    }
}
