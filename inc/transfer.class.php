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

class Transfer extends CommonDBTM
{
    // Specific ones
    /// Already transfer item
    public $already_transfer      = [];
    /// Items simulate to move - non recursive item or recursive item not visible in destination entity
    public $needtobe_transfer     = [];
    /// Items simulate to move - recursive item visible in destination entity
    public $noneedtobe_transfer   = [];
    /// Options used to transfer
    public $options               = [];
    /// Destination entity id
    public $to                    = -1;
    /// type of initial item transfered
    public $inittype              = 0;

    public static $rightname = 'transfer';

    public function maxActionsCount()
    {
        return 0;
    }


    /**
     * @see CommonGLPI::defineTabs()
     *
     * @since 0.85
    **/
    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);

        return $ong;
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
           'id'                 => '19',
           'table'              => $this->getTable(),
           'field'              => 'date_mod',
           'name'               => __('Last update'),
           'datatype'           => 'datetime',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '16',
           'table'              => $this->getTable(),
           'field'              => 'comment',
           'name'               => __('Comments'),
           'datatype'           => 'text'
        ];

        return $tab;
    }


    /**
     * Transfer items
     *
     *@param $items      items to transfer
     *@param $to         entity destination ID
     *@param $options    options used to transfer
    **/
    public function moveItems($items, $to, $options)
    {
        global $DB;

        // unset notifications
        NotificationSetting::disableAll();

        $this->options = ['keep_ticket'         => 0,
                               'keep_networklink'    => 0,
                               'keep_reservation'    => 0,
                               'keep_history'        => 0,
                               'keep_device'         => 0,
                               'keep_infocom'        => 0,

                               'keep_dc_monitor'     => 0,
                               'clean_dc_monitor'    => 0,

                               'keep_dc_phone'       => 0,
                               'clean_dc_phone'      => 0,

                               'keep_dc_peripheral'  => 0,
                               'clean_dc_peripheral' => 0,

                               'keep_dc_printer'     => 0,
                               'clean_dc_printer'    => 0,

                               'keep_supplier'       => 0,
                               'clean_supplier'      => 0,

                               'keep_contact'        => 0,
                               'clean_contact'       => 0,

                               'keep_contract'       => 0,
                               'clean_contract'      => 0,

                               'keep_disk'           => 0,

                               'keep_software'       => 0,
                               'clean_software'      => 0,

                               'keep_document'       => 0,
                               'clean_document'      => 0,

                               'keep_cartridgeitem'  => 0,
                               'clean_cartridgeitem' => 0,
                               'keep_cartridge'      => 0,

                               'keep_consumable'     => 0];

        if ($to >= 0) {
            // Store to
            $this->to = $to;
            // Store options
            if (is_array($options) && count($options)) {
                foreach ($options as $key => $val) {
                    $this->options[$key] = $val;
                }
            }

            $intransaction = $DB->inTransaction();
            try {
                if (!$intransaction) {
                    $DB->beginTransaction();
                }

                // Simulate transfers To know which items need to be transfer
                $this->simulateTransfer($items);

                // Inventory Items : MONITOR....
                $INVENTORY_TYPES = [
                   'Software', // Software first (to avoid copy during computer transfer)
                   'Computer', // Computer before all other items
                   'CartridgeItem',
                   'ConsumableItem',
                   'Monitor',
                   'NetworkEquipment',
                   'Peripheral',
                   'Phone',
                   'Printer',
                   'SoftwareLicense',
                   'Contact',
                   'Contract',
                   'Document',
                   'Supplier',
                   'Group',
                   'Link',
                   'Ticket',
                   'Problem',
                   'Change'
                ];

                foreach ($INVENTORY_TYPES as $itemtype) {
                    $this->inittype = $itemtype;
                    if (isset($items[$itemtype]) && count($items[$itemtype])) {
                        foreach ($items[$itemtype] as $ID) {
                            $this->transferItem($itemtype, $ID, $ID);
                        }
                    }
                }

                //handle all other types
                foreach (array_keys($items) as $itemtype) {
                    if (!in_array($itemtype, $INVENTORY_TYPES)) {
                        $this->inittype = $itemtype;
                        if (isset($items[$itemtype]) && count($items[$itemtype])) {
                            foreach ($items[$itemtype] as $ID) {
                                $this->transferItem($itemtype, $ID, $ID);
                            }
                        }
                    }
                }

                // Clean unused
                // FIXME: only if Software or SoftwareLicense has been changed?
                $this->cleanSoftwareVersions();
                if (!$intransaction && $DB->inTransaction()) {
                    $DB->commit();
                }
            } catch (Exception $e) {
                if (!$intransaction && $DB->inTransaction()) {
                    $DB->rollBack();
                }
                Toolbox::logError($e->getMessage());
            }
        } // $to >= 0
    }


    /**
     * Add an item in the needtobe_transfer list
     *
     * @param $itemtype  of the item
     * @param $ID        of the item
    **/
    public function addToBeTransfer($itemtype, $ID)
    {

        if (!isset($this->needtobe_transfer[$itemtype])) {
            $this->needtobe_transfer[$itemtype] = [];
        }

        // Can't be in both list (in fact, always false)
        if (isset($this->noneedtobe_transfer[$itemtype][$ID])) {
            unset($this->noneedtobe_transfer[$itemtype][$ID]);
        }

        $this->needtobe_transfer[$itemtype][$ID] = $ID;
    }


    /**
     * Add an item in the noneedtobe_transfer list
     *
     * @param $itemtype  of the item
     * @param $ID        of the item
    **/
    public function addNotToBeTransfer($itemtype, $ID)
    {

        if (!isset($this->noneedtobe_transfer[$itemtype])) {
            $this->noneedtobe_transfer[$itemtype] = [];
        }

        // Can't be in both list (in fact, always true)
        if (!isset($this->needtobe_transfer[$itemtype][$ID])) {
            $this->noneedtobe_transfer[$itemtype][$ID] = $ID;
        }
    }


    /**
     * simulate the transfer to know which items need to be transfer
     *
     * @param $items Array of the items to transfer
    **/
    public function simulateTransfer($items)
    {
        global $DB, $CFG_GLPI;

        // Init types :
        $types = ['Computer', 'CartridgeItem', 'Change', 'ConsumableItem', 'Contact', 'Contract',
                       'Document', 'Link', 'Monitor', 'NetworkEquipment', 'Peripheral', 'Phone',
                       'Printer', 'Problem', 'Software', 'SoftwareLicense', 'SoftwareVersion',
                       'Supplier', 'Ticket'];
        $types = array_merge($types, $CFG_GLPI['device_types']);
        $types = array_merge($types, Item_Devices::getDeviceTypes());
        foreach ($types as $t) {
            if (!isset($this->needtobe_transfer[$t])) {
                $this->needtobe_transfer[$t] = [];
            }
            if (!isset($this->noneedtobe_transfer[$t])) {
                $this->noneedtobe_transfer[$t] = [];
            }
        }

        $to_entity_ancestors = getAncestorsOf("glpi_entities", $this->to);

        // Copy items to needtobe_transfer
        foreach ($items as $key => $tab) {
            if (count($tab)) {
                foreach ($tab as $ID) {
                    $this->addToBeTransfer($key, $ID);
                }
            }
        }

        // DIRECT CONNECTIONS

        $DC_CONNECT = [];
        if ($this->options['keep_dc_monitor']) {
            $DC_CONNECT[] = 'Monitor';
        }
        if ($this->options['keep_dc_phone']) {
            $DC_CONNECT[] = 'Phone';
        }
        if ($this->options['keep_dc_peripheral']) {
            $DC_CONNECT[] = 'Peripheral';
        }
        if ($this->options['keep_dc_printer']) {
            $DC_CONNECT[] = 'Printer';
        }

        if (count($DC_CONNECT)
            && (count($this->needtobe_transfer['Computer']) > 0)) {

            foreach ($DC_CONNECT as $itemtype) {
                $itemtable = getTableForItemType($itemtype);

                // Clean DB / Search unexisting links and force disconnect
                $computer_item = new Computer_Item();
                $join = [
                'LEFT JOIN' => [
                    $itemtable => [
                        'ON' => [
                            'glpi_computers_items' => 'items_id',
                            $itemtable => 'id'
                        ]
                    ]
                ]
                ];
                $criteria = [
                "$itemtable.id" => null,
                'glpi_computers_items.itemtype' => $itemtype
                ];
                
                $orphaned_items = $computer_item->find($criteria, [], 0, 0, $join);
                foreach ($orphaned_items as $data) {
                    $computer_item->delete(['id' => $data['id']], true);
                }

                if (!($item = getItemForItemtype($itemtype))) {
                    continue;
                }

                $request = $this::getAdapter()->request([
                   'SELECT'          => 'items_id',
                   'DISTINCT'        => true,
                   'FROM'            => 'glpi_computers_items',
                   'WHERE'           => [
                      'itemtype'     => $itemtype,
                      'computers_id' => $this->needtobe_transfer['Computer']
                   ]
                ]);

                while ($data = $request->fetchAssociative()) {
                    if ($item->getFromDB($data['items_id'])
                          && $item->isRecursive()
                          && in_array($item->getEntityID(), $to_entity_ancestors)) {
                        $this->addNotToBeTransfer($itemtype, $data['items_id']);
                    } else {
                        $this->addToBeTransfer($itemtype, $data['items_id']);
                    }
                }
            }
        } // End of direct connections

        // License / Software :  keep / delete + clean unused / keep unused
        if ($this->options['keep_software']) {
            // Clean DB
            $item_version = new Item_SoftwareVersion();
            $join = [
                'LEFT JOIN' => [
                    'glpi_softwareversions' => [
                        'ON' => [
                            'glpi_items_softwareversions' => 'softwareversions_id',
                            'glpi_softwareversions' => 'id'
                        ]
                    ]
                ]
            ];
            $criteria = ['glpi_softwareversions.id' => null];
            $orphaned_items = $item_version->find($criteria, [], 0, 0, $join);
            foreach ($orphaned_items as $data) {
                $item_version->delete(['id' => $data['id']], true);
            }


            // Clean DB
           $version = new SoftwareVersion();
            $join = [
                'LEFT JOIN' => [
                    'glpi_softwares' => [
                        'ON' => [
                            'glpi_softwareversions' => 'softwares_id',
                            'glpi_softwares' => 'id'
                        ]
                    ]
                ]
            ];
            $criteria = ['glpi_softwares.id' => null];
            $orphaned_items = $version->find($criteria, [], 0, 0, $join);
            foreach ($orphaned_items as $data) {
                $version->delete(['id' => $data['id']], true);
            }
            foreach ($CFG_GLPI['software_types'] as $itemtype) {
                $itemtable = getTableForItemType($itemtype);
                // Clean DB
                 $item_version = new Item_SoftwareVersion();
                $join = [
                    'LEFT JOIN' => [
                        $itemtable => [
                            'ON' => [
                                'glpi_items_softwareversions' => 'items_id',
                                $itemtable => 'id'
                            ]
                        ]
                    ]
                ];
                $criteria = [
                    "{$itemtable}.id" => null,
                    'glpi_items_softwareversions.itemtype' => $itemtype
                ];
                $orphaned_items = $item_version->find($criteria, [], 0, 0, $join);
                foreach ($orphaned_items as $data) {
                    $item_version->delete(['id' => $data['id']], true);
                }

                if (count($this->needtobe_transfer[$itemtype])) {
                    $request = $this::getAdapter()->request([
                       'SELECT'       => [
                          'glpi_softwares.id',
                          'glpi_softwares.entities_id',
                          'glpi_softwares.is_recursive',
                          'glpi_softwareversions.id AS vID'
                       ],
                       'FROM'         => 'glpi_items_softwareversions',
                       'INNER JOIN'   => [
                          'glpi_softwareversions' => [
                             'ON' => [
                                'glpi_items_softwareversions' => 'softwareversions_id',
                                'glpi_softwareversions'       => 'id'
                             ]
                          ],
                          'glpi_softwares'        => [
                             'ON' => [
                                'glpi_softwareversions' => 'softwares_id',
                                'glpi_softwares'        => 'id'
                             ]
                          ]
                       ],
                       'WHERE'        => [
                          'glpi_items_softwareversions.items_id' => $this->needtobe_transfer[$itemtype],
                          'glpi_items_softwareversions.itemtype' => $itemtype
                       ]
                    ]);
                    $results = $request->fetchAllAssociative();
                    if (count($results)) {
                        foreach ($results as $data) {
                            if ($data['is_recursive']
                               && in_array($data['entities_id'], $to_entity_ancestors)) {
                                $this->addNotToBeTransfer('SoftwareVersion', $data['vID']);
                            } else {
                                $this->addToBeTransfer('SoftwareVersion', $data['vID']);
                            }
                        }
                    }
                }
            }
        }

        if (count($this->needtobe_transfer['Software'])) {
            // Move license of software
            // TODO : should we transfer "affected license" ?
            $request = $this::getAdapter()->request([
               'SELECT' => ['id', 'softwareversions_id_buy', 'softwareversions_id_use'],
               'FROM'   => 'glpi_softwarelicenses',
               'WHERE'  => ['softwares_id' => $this->needtobe_transfer['Software']]
            ]);

            while ($lic = $request->fetchAssociative()) {
                $this->addToBeTransfer('SoftwareLicense', $lic['id']);

                // Force version transfer
                if ($lic['softwareversions_id_buy'] > 0) {
                    $this->addToBeTransfer('SoftwareVersion', $lic['softwareversions_id_buy']);
                }
                if ($lic['softwareversions_id_use'] > 0) {
                    $this->addToBeTransfer('SoftwareVersion', $lic['softwareversions_id_use']);
                }
            }
        }

        // Devices
        if ($this->options['keep_device']) {
            foreach (Item_Devices::getConcernedItems() as $itemtype) {
                $itemtable = getTableForItemType($itemtype);
                if (isset($this->needtobe_transfer[$itemtype]) && count($this->needtobe_transfer[$itemtype])) {
                    foreach (Item_Devices::getItemAffinities($itemtype) as $itemdevicetype) {
                        $itemdevicetable = getTableForItemType($itemdevicetype);
                        $devicetype      = $itemdevicetype::getDeviceType();
                        $devicetable     = getTableForItemType($devicetype);
                        $fk              = getForeignKeyFieldForTable($devicetable);
                        $request = $this::getAdapter()->request([
                           'SELECT'          => [
                              "$itemdevicetable.$fk",
                              "$devicetable.entities_id",
                              "$devicetable.is_recursive"
                           ],
                           'DISTINCT'        => true,
                           'FROM'            => $itemdevicetable,
                           'LEFT JOIN'       => [
                              $devicetable   => [
                                 'ON' => [
                                    $itemdevicetable  => $fk,
                                    $devicetable      => 'id'
                                 ]
                              ]
                           ],
                           'WHERE'           => [
                              "$itemdevicetable.itemtype"   => $itemtype,
                              "$itemdevicetable.items_id"   => $this->needtobe_transfer[$itemtype]
                           ]
                        ]);

                        while ($data = $request->fetchAssociative()) {
                            if ($data['is_recursive']
                                && in_array($data['entities_id'], $to_entity_ancestors)) {
                                $this->addNotToBeTransfer($devicetype, $data[$fk]);
                            } else {
                                if (!isset($this->needtobe_transfer[$devicetype][$data[$fk]])) {
                                    $this->addToBeTransfer($devicetype, $data[$fk]);
                                    $request2 = $this::getAdapter()->request([
                                       'SELECT' => 'id',
                                       'FROM'   => $itemdevicetable,
                                       'WHERE'  => [
                                          $fk   => $data[$fk],
                                          'itemtype'  => $itemtype,
                                          'items_id'  => $this->needtobe_transfer[$itemtype]
                                       ]
                                    ]);
                                    while ($data2 = $request2->fetchAssociative()) {
                                        $this->addToBeTransfer($itemdevicetype, $data2['id']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Tickets
        if ($this->options['keep_ticket']) {
            foreach ($CFG_GLPI["ticket_types"] as $itemtype) {
                if (isset($this->needtobe_transfer[$itemtype]) && count($this->needtobe_transfer[$itemtype])) {
                    $request = $this::getAdapter()->request([
                       'SELECT'    => 'glpi_tickets.id',
                       'FROM'      => 'glpi_tickets',
                       'LEFT JOIN' => [
                          'glpi_items_tickets' => [
                             'ON' => [
                                'glpi_items_tickets' => 'tickets_id',
                                'glpi_tickets'       => 'id'
                             ]
                          ]
                       ],
                       'WHERE'     => [
                          'itemtype'  => $itemtype,
                          'items_id'  => $this->needtobe_transfer[$itemtype]
                       ]
                    ]);

                    while ($data = $request->fetchAssociative()) {
                        $this->addToBeTransfer('Ticket', $data['id']);
                    }
                }
            }
        }

        // Contract : keep / delete + clean unused / keep unused
        if ($this->options['keep_contract']) {
            foreach ($CFG_GLPI["contract_types"] as $itemtype) {
                if (isset($this->needtobe_transfer[$itemtype]) && count($this->needtobe_transfer[$itemtype])) {
                    $contracts_items = [];
                    $itemtable = getTableForItemType($itemtype);

                    // Clean DB
                     $contract_item = new Contract_Item();
                    $join = [
                        'LEFT JOIN' => [
                            $itemtable => [
                                'ON' => [
                                    'glpi_contracts_items' => 'items_id',
                                    $itemtable => 'id'
                                ]
                            ]
                        ]
                    ];
                    $criteria = [
                        "$itemtable.id" => null,
                        "glpi_contracts_items.itemtype" => $itemtype
                    ];
                    $orphaned_items = $contract_item->find($criteria, [], 0, 0, $join);
                    foreach ($orphaned_items as $data) {
                        $contract_item->delete(['id' => $data['id']], true);
                    }

                    // Clean DB
                    $join = [
                        'LEFT JOIN' => [
                            'glpi_contracts' => [
                                'ON' => [
                                    'glpi_contracts_items' => 'contracts_id',
                                    'glpi_contracts' => 'id'
                                ]
                            ]
                        ]
                    ];
                    $criteria = ['glpi_contracts.id' => null];
                    $orphaned_items = $contract_item->find($criteria, [], 0, 0, $join);
                    foreach ($orphaned_items as $data) {
                        $contract_item->delete(['id' => $data['id']], true);
                    }

                    $request = $this::getAdapter()->request([
                       'SELECT'    => [
                          'contracts_id',
                          'glpi_contracts.entities_id',
                          'glpi_contracts.is_recursive'
                       ],
                       'FROM'      => 'glpi_contracts_items',
                       'LEFT JOIN' => [
                          'glpi_contracts' => [
                             'ON' => [
                                'glpi_contracts_items'  => 'contracts_id',
                                'glpi_contracts'        => 'id'
                             ]
                          ]
                       ],
                       'WHERE'     => [
                          'itemtype'  => $itemtype,
                          'items_id'  => $this->needtobe_transfer[$itemtype]
                       ]
                    ]);

                    while ($data = $request->fetchAssociative()) {
                        if ($data['is_recursive']
                              && in_array($data['entities_id'], $to_entity_ancestors)) {
                            $this->addNotToBeTransfer('Contract', $data['contracts_id']);
                        } else {
                            $this->addToBeTransfer('Contract', $data['contracts_id']);
                        }
                    }
                }
            }
        }
        // Supplier (depending of item link) / Contract - infocoms : keep / delete + clean unused / keep unused
        if ($this->options['keep_supplier']) {
            $contracts_suppliers = [];
            // Clean DB
            $contract_supplier = new Contract_Supplier();
            $join = [
                'LEFT JOIN' => [
                    'glpi_contracts' => [
                        'ON' => [
                            'glpi_contracts_suppliers' => 'contracts_id',
                            'glpi_contracts' => 'id'
                        ]
                    ]
                ]
            ];
            $criteria = ['glpi_contracts.id' => null];
            $orphaned_items = $contract_supplier->find($criteria, [], 0, 0, $join);
            foreach ($orphaned_items as $data) {
                $contract_supplier->delete(['id' => $data['id']], true);
            }

            // Clean DB
            $join = [
                'LEFT JOIN' => [
                    'glpi_suppliers' => [
                        'ON' => [
                            'glpi_contracts_suppliers' => 'suppliers_id',
                            'glpi_suppliers' => 'id'
                        ]
                    ]
                ]
            ];
            $criteria = ['glpi_suppliers.id' => null];
            $orphaned_items = $contract_supplier->find($criteria, [], 0, 0, $join);
            foreach ($orphaned_items as $data) {
                $contract_supplier->delete(['id' => $data['id']], true);
            }

            if (isset($this->needtobe_transfer['Contract']) && count($this->needtobe_transfer['Contract'])) {
                // Supplier Contract
                $request = $this::getAdapter()->request([
                   'SELECT'    => [
                      'suppliers_id',
                      'glpi_suppliers.entities_id',
                      'glpi_suppliers.is_recursive'
                   ],
                   'FROM'      => 'glpi_contracts_suppliers',
                   'LEFT JOIN' => [
                      'glpi_suppliers' => [
                         'ON' => [
                            'glpi_contracts_suppliers' => 'suppliers_id',
                            'glpi_suppliers'           => 'id'
                         ]
                      ]
                   ],
                   'WHERE'     => [
                      'contracts_id' => $this->needtobe_transfer['Contract']
                   ]
                ]);

                while ($data = $request->fetchAssociative()) {
                    if ($data['is_recursive']
                          && in_array($data['entities_id'], $to_entity_ancestors)) {
                        $this->addNotToBeTransfer('Supplier', $data['suppliers_id']);
                    } else {
                        $this->addToBeTransfer('Supplier', $data['suppliers_id']);
                    }
                }
            }

            if (isset($this->needtobe_transfer['Ticket']) && count($this->needtobe_transfer['Ticket'])) {
                // Ticket Supplier
                $request = $this::getAdapter()->request([
                   'SELECT'    => [
                      'glpi_suppliers_tickets.suppliers_id',
                      'glpi_suppliers.entities_id',
                      'glpi_suppliers.is_recursive'
                   ],
                   'FROM'      => 'glpi_tickets',
                   'LEFT JOIN' => [
                      'glpi_suppliers_tickets'   => [
                         'ON' => [
                            'glpi_suppliers_tickets'   => 'tickets_id',
                            'glpi_tickets'             => 'id'
                         ]
                      ],
                      'glpi_suppliers'           => [
                         'ON' => [
                            'glpi_suppliers_tickets'   => 'suppliers_id',
                            'glpi_suppliers'           => 'id'
                         ]
                      ]
                   ],
                   'WHERE'     => [
                      'glpi_suppliers_tickets.suppliers_id'  => ['>', 0],
                      'glpi_tickets.id'                      => $this->needtobe_transfer['Ticket']
                   ]
                ]);

                while ($data = $request->fetchAssociative()) {
                    if ($data['is_recursive']
                          && in_array($data['entities_id'], $to_entity_ancestors)) {
                        $this->addNotToBeTransfer('Supplier', $data['suppliers_id']);
                    } else {
                        $this->addToBeTransfer('Supplier', $data['suppliers_id']);
                    }

                }
            }

            if (isset($this->needtobe_transfer['Problem']) && count($this->needtobe_transfer['Problem'])) {
                // Problem Supplier
                $request = $this::getAdapter()->request([
                   'SELECT'    => [
                      'glpi_problems_suppliers.suppliers_id',
                      'glpi_suppliers.entities_id',
                      'glpi_suppliers.is_recursive'
                   ],
                   'FROM'      => 'glpi_problems',
                   'LEFT JOIN' => [
                      'glpi_problems_suppliers'   => [
                         'ON' => [
                            'glpi_problems_suppliers'  => 'problems_id',
                            'glpi_problems'            => 'id'
                         ]
                      ],
                      'glpi_suppliers'           => [
                         'ON' => [
                            'glpi_problems_suppliers'  => 'suppliers_id',
                            'glpi_suppliers'           => 'id'
                         ]
                      ]
                   ],
                   'WHERE'     => [
                      'glpi_problems_suppliers.suppliers_id' => ['>', 0],
                      'glpi_problems.id'                     => $this->needtobe_transfer['Problem']
                   ]
                ]);

                while ($data = $request->fetchAssociative()) {
                    if ($data['is_recursive']
                          && in_array($data['entities_id'], $to_entity_ancestors)) {
                        $this->addNotToBeTransfer('Supplier', $data['suppliers_id']);
                    } else {
                        $this->addToBeTransfer('Supplier', $data['suppliers_id']);
                    }
                }
            }

            if (isset($this->needtobe_transfer['Change']) && count($this->needtobe_transfer['Change'])) {
                // Change Supplier
                $request = $this::getAdapter()->request([
                   'SELECT'    => [
                      'glpi_changes_suppliers.suppliers_id',
                      'glpi_suppliers.entities_id',
                      'glpi_suppliers.is_recursive'
                   ],
                   'FROM'      => 'glpi_changes',
                   'LEFT JOIN' => [
                      'glpi_changes_suppliers'   => [
                         'ON' => [
                            'glpi_changes_suppliers'  => 'changes_id',
                            'glpi_changes'            => 'id'
                         ]
                      ],
                      'glpi_suppliers'           => [
                         'ON' => [
                            'glpi_changes_suppliers'   => 'suppliers_id',
                            'glpi_suppliers'           => 'id'
                         ]
                      ]
                   ],
                   'WHERE'     => [
                      'glpi_changes_suppliers.suppliers_id' => ['>', 0],
                      'glpi_changes.id'                     => $this->needtobe_transfer['Change']
                   ]
                ]);

                while ($data = $request->fetchAssociative()) {
                    if ($data['is_recursive']
                          && in_array($data['entities_id'], $to_entity_ancestors)) {
                        $this->addNotToBeTransfer('Supplier', $data['suppliers_id']);
                    } else {
                        $this->addToBeTransfer('Supplier', $data['suppliers_id']);
                    }
                }
            }

            // Supplier infocoms
            if ($this->options['keep_infocom']) {
                foreach (Infocom::getItemtypesThatCanHave() as $itemtype) {
                    if (isset($this->needtobe_transfer[$itemtype]) && count($this->needtobe_transfer[$itemtype])) {
                        $itemtable = getTableForItemType($itemtype);

                        // Clean DB
                        $infocom = new Infocom();
                        $join = [
                            'LEFT JOIN' => [
                                $itemtable => [
                                    'ON' => [
                                        'glpi_infocoms' => 'items_id',
                                        $itemtable => 'id'
                                    ]
                                ]
                            ]
                        ];
                        $criteria = [
                            "$itemtable.id" => null,
                            'glpi_infocoms.itemtype' => $itemtype
                        ];
                        $orphaned_items = $infocom->find($criteria, [], 0, 0, $join);
                        foreach ($orphaned_items as $data) {
                            $infocom->delete(['id' => $data['id']], true);
                        }

                        $request = $this::getAdapter()->request([
                           'SELECT'    => [
                              'suppliers_id',
                              'glpi_suppliers.entities_id',
                              'glpi_suppliers.is_recursive'
                           ],
                           'FROM'      => 'glpi_infocoms',
                           'LEFT JOIN' => [
                              'glpi_suppliers'  => [
                                 'ON' => [
                                    'glpi_infocoms'   => 'suppliers_id',
                                    'glpi_suppliers'  => 'id'
                                 ]
                              ]
                           ],
                           'WHERE'     => [
                              'suppliers_id' => ['>', 0],
                              'itemtype'     => $itemtype,
                              'items_id'     => $this->needtobe_transfer[$itemtype]
                           ]
                        ]);

                        while ($data = $request->fetchAssociative()) {
                            if ($data['is_recursive']
                                  && in_array($data['entities_id'], $to_entity_ancestors)) {
                                $this->addNotToBeTransfer('Supplier', $data['suppliers_id']);
                            } else {
                                $this->addToBeTransfer('Supplier', $data['suppliers_id']);
                            }
                        }
                    }
                }
            }

        }

        // Contact / Supplier : keep / delete + clean unused / keep unused
        if ($this->options['keep_contact']) {
            $contact_suppliers = [];
            // Clean DB
           $contact_supplier = new Contact_Supplier();
            $join = [
                'LEFT JOIN' => [
                    'glpi_contacts' => [
                        'ON' => [
                            'glpi_contacts_suppliers' => 'contacts_id',
                            'glpi_contacts' => 'id'
                        ]
                    ]
                ]
            ];
            $criteria = ['glpi_contacts.id' => null];
            $orphaned_items = $contact_supplier->find($criteria, [], 0, 0, $join);
            foreach ($orphaned_items as $data) {
                $contact_supplier->delete(['id' => $data['id']], true);
            }

            // Clean DB
            $join = [
                'LEFT JOIN' => [
                    'glpi_suppliers' => [
                        'ON' => [
                            'glpi_contacts_suppliers' => 'suppliers_id',
                            'glpi_suppliers' => 'id'
                        ]
                    ]
                ]
            ];
            $criteria = ['glpi_suppliers.id' => null];
            $orphaned_items = $contact_supplier->find($criteria, [], 0, 0, $join);
            foreach ($orphaned_items as $data) {
                $contact_supplier->delete(['id' => $data['id']], true);
            }

            if (isset($this->needtobe_transfer['Supplier']) && count($this->needtobe_transfer['Supplier'])) {
                // Supplier Contact
                $request = $this::getAdapter()->request([
                   'SELECT'    => [
                      'contacts_id',
                      'glpi_contacts.entities_id',
                      'glpi_contacts.is_recursive'
                   ],
                   'FROM'      => 'glpi_contacts_suppliers',
                   'LEFT JOIN' => [
                      'glpi_contacts'  => [
                         'ON' => [
                            'glpi_contacts_suppliers'  => 'contacts_id',
                            'glpi_contacts'            => 'id'
                         ]
                      ]
                   ],
                   'WHERE'     => [
                      'suppliers_id' => $this->needtobe_transfer['Supplier']
                   ]
                ]);

                while ($data = $request->fetchAssociative()) {
                    if ($data['is_recursive']
                          && in_array($data['entities_id'], $to_entity_ancestors)) {
                        $this->addNotToBeTransfer('Contact', $data['contacts_id']);
                    } else {
                        $this->addToBeTransfer('Contact', $data['contacts_id']);
                    }
                }
            }
        }

        // Document : keep / delete + clean unused / keep unused
        if ($this->options['keep_document']) {
            foreach (Document::getItemtypesThatCanHave() as $itemtype) {
                if (isset($this->needtobe_transfer[$itemtype]) && count($this->needtobe_transfer[$itemtype])) {
                    $itemtable = getTableForItemType($itemtype);
                    // Clean DB
                   $document_item = new Document_Item();
                    $join = [
                        'LEFT JOIN' => [
                            $itemtable => [
                                'ON' => [
                                    'glpi_documents_items' => 'items_id',
                                    $itemtable => 'id'
                                ]
                            ]
                        ]
                    ];
                    $criteria = [
                        "$itemtable.id" => null,
                        'glpi_documents_items.itemtype' => $itemtype
                    ];
                    $orphaned_items = $document_item->find($criteria, [], 0, 0, $join);
                    foreach ($orphaned_items as $data) {
                        $document_item->delete(['id' => $data['id']], true);
                    }

                    $request = $this::getAdapter()->request([
                       'SELECT'    => [
                          'documents_id',
                          'glpi_documents.entities_id',
                          'glpi_documents.is_recursive'
                       ],
                       'FROM'      => 'glpi_documents_items',
                       'LEFT JOIN' => [
                          'glpi_documents'  => [
                             'ON' => [
                                'glpi_documents_items'  => 'documents_id',
                                'glpi_documents'        => 'id', [
                                   'AND' => [
                                      'itemtype' => $itemtype
                                   ]
                                ]
                             ]
                          ]
                       ],
                       'WHERE'     => [
                          'items_id' => $this->needtobe_transfer[$itemtype]
                       ]
                    ]);

                    while ($data = $request->fetchAssociative()) {
                        if ($data['is_recursive']
                              && in_array($data['entities_id'], $to_entity_ancestors)) {
                            $this->addNotToBeTransfer('Document', $data['documents_id']);
                        } else {
                            $this->addToBeTransfer('Document', $data['documents_id']);
                        }
                    }
                }
            }
        }

        // printer -> cartridges : keep / delete + clean
        if ($this->options['keep_cartridgeitem']) {
            if (isset($this->needtobe_transfer['Printer']) && count($this->needtobe_transfer['Printer'])) {
                $request = $this::getAdapter()->request([
                   'SELECT' => 'cartridgeitems_id',
                   'FROM'   => 'glpi_cartridges',
                   'WHERE'  => ['printers_id' => $this->needtobe_transfer['Printer']]
                ]);

                while ($data = $request->fetchAssociative()) {
                    $this->addToBeTransfer('CartridgeItem', $data['cartridgeitems_id']);
                }
            }
        }

        // Init all types if not defined
        foreach ($types as $itemtype) {
            if (!isset($this->needtobe_transfer[$itemtype])) {
                $this->needtobe_transfer[$itemtype] = [-1];
            }
        }

    }


    /**
     * transfer an item to another item (may be the same) in the new entity
     *
     * @param $itemtype     item type to transfer
     * @param $ID           ID of the item to transfer
     * @param $newID        new ID of the ite
     *
     * Transfer item to a new Item if $ID==$newID : only update entities_id field :
     *                                $ID!=$new ID -> copy datas (like template system)
     * @return void
    **/
    public function transferItem($itemtype, $ID, $newID)
    {
        global $CFG_GLPI;

        if (!($item = getItemForItemtype($itemtype))) {
            return;
        }

        // Is already transfer ?
        if (!isset($this->already_transfer[$itemtype][$ID])) {
            // Check computer exists ?
            if ($item->getFromDB($newID)) {

                // Network connection ? keep connected / keep_disconnected / delete
                if (in_array($itemtype, $CFG_GLPI['networkport_types'])) {
                    $this->transferNetworkLink($itemtype, $ID, $newID);
                }

                // Device : keep / delete : network case : delete if net connection delete in import case
                if (in_array($itemtype, Item_Devices::getConcernedItems())) {
                    $this->transferDevices($itemtype, $ID, $newID);
                }

                // Reservation : keep / delete
                if (in_array($itemtype, $CFG_GLPI["reservation_types"])) {
                    $this->transferReservations($itemtype, $ID, $newID);
                }

                // History : keep / delete
                $this->transferHistory($itemtype, $ID, $newID);
                // Ticket : delete / keep and clean ref / keep and move
                $this->transferTickets($itemtype, $ID, $newID);
                // Infocoms : keep / delete

                if (Infocom::canApplyOn($itemtype)) {
                    $this->transferInfocoms($itemtype, $ID, $newID);
                }

                if ($itemtype == 'Software') {
                    $this->transferSoftwareLicensesAndVersions($ID);
                }

                // Connected item is transfered
                if (in_array($itemtype, $CFG_GLPI["directconnect_types"])) {
                    $this->manageConnectionComputer($itemtype, $ID);
                }

                // Contract : keep / delete + clean unused / keep unused
                if (in_array($itemtype, $CFG_GLPI["contract_types"])) {
                    $this->transferContracts($itemtype, $ID, $newID);
                }

                // Contact / Supplier : keep / delete + clean unused / keep unused
                if ($itemtype == 'Supplier') {
                    $this->transferSupplierContacts($ID, $newID);
                }

                // Document : keep / delete + clean unused / keep unused
                if (Document::canApplyOn($itemtype)) {
                    $this->transferDocuments($itemtype, $ID, $newID);

                    if (is_a($itemtype, CommonITILObject::class, true)) {
                        // Transfer ITIL childs documents too
                        $itil_item = getItemForItemtype($itemtype);
                        $itil_item->getFromDB($ID);
                        $document_item_obj = new Document_Item();
                        $document_items = $document_item_obj->find(
                            $itil_item->getAssociatedDocumentsCriteria(true)
                        );
                        foreach ($document_items as $document_item) {
                            $this->transferDocuments(
                                $document_item['itemtype'],
                                $document_item['items_id'],
                                $document_item['items_id']
                            );
                        }
                    }
                }

                // Transfer compatible printers
                if ($itemtype == 'CartridgeItem') {
                    $this->transferCompatiblePrinters($ID, $newID);
                }

                // Cartridges  and cartridges items linked to printer
                if ($itemtype == 'Printer') {
                    $this->transferPrinterCartridges($ID, $newID);
                }

                // Transfer Item
                $input = [
                   'id'          => $newID,
                   'entities_id' => $this->to,
                   '_transfer'   => 1
                ];

                // Manage Location dropdown
                if (isset($item->fields['locations_id'])) {
                    $input['locations_id'] = $this->transferDropdownLocation($item->fields['locations_id']);
                }

                if (in_array($itemtype, ['Ticket', 'Problem', 'Change'])) {
                    $input2 = $this->transferHelpdeskAdditionalInformations($item->fields);
                    $input  = array_merge($input, $input2);
                    $this->transferTaskCategory($itemtype, $ID, $newID);
                    $this->transferLinkedSuppliers($itemtype, $ID, $newID);
                }

                $item->update($input);
                $this->addToAlreadyTransfer($itemtype, $ID, $newID);

                // Do it after item transfer for entity checks
                if ($itemtype == 'Computer') {
                    // Monitor Direct Connect : keep / delete + clean unused / keep unused
                    $this->transferDirectConnection($itemtype, $ID, 'Monitor');
                    // Peripheral Direct Connect : keep / delete + clean unused / keep unused
                    $this->transferDirectConnection($itemtype, $ID, 'Peripheral');
                    // Phone Direct Connect : keep / delete + clean unused / keep unused
                    $this->transferDirectConnection($itemtype, $ID, 'Phone');
                    // Printer Direct Connect : keep / delete + clean unused / keep unused
                    $this->transferDirectConnection($itemtype, $ID, 'Printer');
                    // Computer Disks :  delete them or not ?
                    $this->transferItem_Disks($itemtype, $ID);
                }

                if (in_array($itemtype, $CFG_GLPI['software_types'])) {
                    // License / Software :  keep / delete + clean unused / keep unused
                    $this->transferItemSoftwares($itemtype, $ID);
                }

                Plugin::doHook("item_transfer", ['type'        => $itemtype,
                                                      'id'          => $ID,
                                                      'newID'       => $newID,
                                                      'entities_id' => $this->to]);
            }
        }
    }


    /**
     * Add an item to already transfer array
     *
     * @param $itemtype  item type
     * @param $ID        item original ID
     * @param $newID     item new ID
    **/
    public function addToAlreadyTransfer($itemtype, $ID, $newID)
    {

        if (!isset($this->already_transfer[$itemtype])) {
            $this->already_transfer[$itemtype] = [];
        }
        $this->already_transfer[$itemtype][$ID] = $newID;
    }


    /**
     * Transfer location
     *
     * @param $locID location ID
     *
     * @return new location ID
    **/
    public function transferDropdownLocation($locID)
    {
        if ($locID > 0) {
            if (isset($this->already_transfer['locations_id'][$locID])) {
                return $this->already_transfer['locations_id'][$locID];
            }
            // else  // Not already transfer
            // Search init item
            $location = new Location();
            if ($location->getFromDB($locID)) {
                $data = Toolbox::addslashes_deep($location->fields);

                $input['entities_id']  = $this->to;
                $input['completename'] = $data['completename']?? '';
                $newID                 = $location->findID($input);

                if ($newID < 0) {
                    $newID = $location->import($input);
                }

                $this->addToAlreadyTransfer('locations_id', $locID, $newID);
                return $newID;
            }
        }
        return 0;
    }


    /**
     * Transfer netpoint
     *
     * @param $netpoints_id netpoint ID
     *
     * @return new netpoint ID
    **/
    public function transferDropdownNetpoint($netpoints_id)
    {
        global $DB;

        if ($netpoints_id > 0) {
            if (isset($this->already_transfer['netpoints_id'][$netpoints_id])) {
                return $this->already_transfer['netpoints_id'][$netpoints_id];
            }
            // else  // Not already transfer
            // Search init item
            $netpoint = new Netpoint();
            if ($netpoint->getFromDB($netpoints_id)) {
                $data  = Toolbox::addslashes_deep($netpoint->fields);
                $locID = $this->transferDropdownLocation($netpoint->fields['locations_id']);

                // Search if the locations_id already exists in the destination entity
                $request = $this::getAdapter()->request([
                   'SELECT' => 'id',
                   'FROM'   => 'glpi_netpoints',
                   'WHERE'  => [
                      'entities_id'  => $this->to,
                      'name'         => Toolbox::addslashes_deep($netpoint->fields['name']),
                      'locations_id' => $locID
                   ]
                ]);
                $results = $request->fetchAllAssociative();
                if (count($results)) {
                    // Found : -> use it
                    $row = $results[0];
                    $newID = $row['id'];
                    $this->addToAlreadyTransfer('netpoints_id', $netpoints_id, $newID);
                    return $newID;
                }

                // Not found :
                // add item
                $newID    = $netpoint->add(['name'         => $data['name'],
                                                 'comment'      => $data['comment'],
                                                 'entities_id'  => $this->to,
                                                 'locations_id' => $locID]);

                $this->addToAlreadyTransfer('netpoints_id', $netpoints_id, $newID);
                return $newID;
            }
        }
        return 0;
    }


    /**
     * Transfer cartridges of a printer
     *
     * @param $ID     original ID of the printer
     * @param $newID  new ID of the printer
    **/
    public function transferPrinterCartridges($ID, $newID)
    {
        global $DB;

        // Get cartrdiges linked
        $request = $this::getAdapter()->request([
           'FROM'   => 'glpi_cartridges',
           'WHERE'  => ['printers_id' => $ID]
        ]);
        $results = $request->fetchAllAssociative();
        if (count($results)) {
            $cart     = new Cartridge();
            $carttype = new CartridgeItem();

            foreach ($results as $data) {
                $need_clean_process = false;

                // Foreach cartridges
                // if keep
                if ($this->options['keep_cartridgeitem']) {
                    $newcartID     = - 1;
                    $newcarttypeID = -1;

                    // 1 - Search carttype destination ?
                    // Already transfer carttype :
                    if (isset($this->already_transfer['CartridgeItem'][$data['cartridgeitems_id']])) {
                        $newcarttypeID
                              = $this->already_transfer['CartridgeItem'][$data['cartridgeitems_id']];

                    } else {
                        if (isset($this->needtobe_transfer['Printer']) && count($this->needtobe_transfer['Printer'])) {
                            // Not already transfer cartype
                            $ccriteria = [
                               'COUNT'  => 'cpt',
                               'FROM'   => 'glpi_cartridges',
                               'WHERE'  => [
                                  'cartridgeitems_id'  => $data['cartridgeitems_id'],
                                  'printers_id'        => ['>', 0],
                                  'NOT'                => [
                                     'printers_id'  => $this->needtobe_transfer['Printer']
                                  ]
                               ]
                            ];

                            $result = $this::getAdapter()->request($ccriteria)->fetchAssociative();

                            // Is the carttype will be completly transfer ?
                            if ($result['cpt'] == 0) {
                                // Yes : transfer
                                $need_clean_process = false;
                                $this->transferItem(
                                    'CartridgeItem',
                                    $data['cartridgeitems_id'],
                                    $data['cartridgeitems_id']
                                );
                                $newcarttypeID = $data['cartridgeitems_id'];

                            } else {
                                // No : copy carttype
                                $need_clean_process = true;
                                $carttype->getFromDB($data['cartridgeitems_id']);
                                // Is existing carttype in the destination entity ?
                                $request = $this::getAdapter()->request([
                                   'FROM'   => 'glpi_cartridgeitems',
                                   'WHERE'  => [
                                      'entities_id'  => $this->to,
                                      'name'         => addslashes($carttype->fields['name'])
                                   ]
                                ]);
                                $items_iterator = $request->fetchAllAssociative();
                                if (count($items_iterator)) {
                                    $row = $items_iterator[0];
                                    $newcarttypeID = $row['id'];
                                }

                                // Not found -> transfer copy
                                if ($newcarttypeID < 0) {
                                    // 1 - create new item
                                    unset($carttype->fields['id']);
                                    $input                = $carttype->fields;
                                    $input['entities_id'] = $this->to;
                                    unset($carttype->fields);
                                    $newcarttypeID        = $carttype->add(Toolbox::addslashes_deep($input));
                                    // 2 - transfer as copy
                                    $this->transferItem(
                                        'CartridgeItem',
                                        $data['cartridgeitems_id'],
                                        $newcarttypeID
                                    );
                                }
                            }

                            // Found -> use to link : nothing to do
                        }

                    }

                    // Update cartridge if needed
                    if (($newcarttypeID > 0)
                          && ($newcarttypeID != $data['cartridgeitems_id'])) {
                        $cart->update(['id'                => $data['id'],
                                             'cartridgeitems_id' => $newcarttypeID]);
                    }

                } else { // Do not keep
                    // If same printer : delete cartridges
                    if ($ID == $newID) {
                        $cartridge = new Cartridge();
                        $cartridges_to_delete = $cartridge->find(['printers_id' => $ID]);
                        foreach ($cartridges_to_delete as $data) {
                            $cartridge->delete(['id' => $data['id']], true);
                        }
                    }
                    $need_clean_process = true;
                }

                // CLean process
                if ($need_clean_process
                      && $this->options['clean_cartridgeitem']) {

                    // Clean carttype
                    $result = $this::getAdapter()->request([
                       'COUNT'  => 'cpt',
                       'FROM'   => 'glpi_cartridges',
                       'WHERE'  => [
                          'cartridgeitems_id'  => $data['cartridgeitems_id']
                       ]
                    ])->fetchAssociative();

                    if ($result['cpt'] == 0) {
                        if ($this->options['clean_cartridgeitem'] == 1) { // delete
                            $carttype->delete(['id' => $data['cartridgeitems_id']]);
                        }
                        if ($this->options['clean_cartridgeitem'] == 2) { // purge
                            $carttype->delete(['id' => $data['cartridgeitems_id']], 1);
                        }
                    }
                }

            }
        }
    }


    /**
     * Copy (if needed) One software to the destination entity
     *
     * @param $ID of the software
     *
     * @return $ID of the new software (could be the same)
    **/
    public function copySingleSoftware($ID)
    {
        global $DB;

        if (isset($this->already_transfer['Software'][$ID])) {
            return $this->already_transfer['Software'][$ID];
        }

        $soft = new Software();
        if ($soft->getFromDB($ID)) {
            if ($soft->fields['is_recursive']
                && in_array($soft->fields['entities_id'], getAncestorsOf(
                    "glpi_entities",
                    $this->to
                ))) {
                // no need to copy
                $newsoftID = $ID;

            } else {
                $manufacturer = [];
                if (isset($soft->fields['manufacturers_id'])
                    && ($soft->fields['manufacturers_id'] > 0)) {
                    $manufacturer = ['manufacturers_id' => $soft->fields['manufacturers_id']];
                }

                $request = $this::getAdapter()->request([
                   'SELECT' => 'id',
                   'FROM'   => 'glpi_softwares',
                   'WHERE'  => [
                      'entities_id'  => $this->to,
                      'name'         => addslashes($soft->fields['name'])
                   ] + $manufacturer
                ]);

                if ($data = $request->fetchAssociative()) {
                    $newsoftID = $data["id"];

                } else {
                    // create new item (don't check if move possible => clean needed)
                    unset($soft->fields['id']);
                    $input                = $soft->fields;
                    $input['entities_id'] = $this->to;
                    unset($soft->fields);
                    $newsoftID            = $soft->add(Toolbox::addslashes_deep($input));
                }

            }

            $this->addToAlreadyTransfer('Software', $ID, $newsoftID);
            return $newsoftID;
        }

        return -1;
    }


    /**
     * Copy (if needed) One softwareversion to the Dest Entity
     *
     * @param $ID of the version
     *
     * @return $ID of the new version (could be the same)
    **/
    public function copySingleVersion($ID)
    {
        global $DB;

        if (isset($this->already_transfer['SoftwareVersion'][$ID])) {
            return $this->already_transfer['SoftwareVersion'][$ID];
        }

        $vers = new SoftwareVersion();
        if ($vers->getFromDB($ID)) {
            $newsoftID = $this->copySingleSoftware($vers->fields['softwares_id']);

            if ($newsoftID == $vers->fields['softwares_id']) {
                // no need to copy
                $newversID = $ID;

            } else {
                $request = $this::getAdapter()->request([
                   'SELECT' => 'id',
                   'FROM'   => 'glpi_softwareversions',
                   'WHERE'  => [
                      'softwares_id' => $newsoftID,
                      'name'         => addslashes($vers->fields['name'])
                   ]
                ]);

                if ($data = $request->fetchAssociative()) {
                    $newversID = $data["id"];

                } else {
                    // create new item (don't check if move possible => clean needed)
                    unset($vers->fields['id']);
                    $input                 = $vers->fields;
                    $vers->fields = [];
                    // entities_id and is_recursive from new software are set in prepareInputForAdd
                    $input['softwares_id'] = $newsoftID;
                    $newversID             = $vers->add(Toolbox::addslashes_deep($input));
                }

            }

            $this->addToAlreadyTransfer('SoftwareVersion', $ID, $newversID);
            return $newversID;
        }

        return -1;
    }


    /**
     * Transfer disks of an item
     *
     * @param string  $itemtype Item type
     * @param integer $ID       ID of the item
     */
    public function transferItem_Disks($itemtype, $ID)
    {
        if (!$this->options['keep_disk']) {
            $disk = new Item_Disk();
            $disk->cleanDBonItemDelete($itemtype, $ID);
        }
    }

    /**
     * Transfer softwares of a computer
     *
     * @param $ID           ID of the computer
    **/
    public function transferComputerSoftwares($ID)
    {
        Toolbox::deprecated('Use transferItemSoftwares()');
        return $this->transferItemSoftwares('Computer', $ID);
    }

    /**
     * Transfer software of an item
     *
     * @param string $itemtype  Type of the item
     * @param int    $ID        ID of the item
    **/
    public function transferItemSoftwares($itemtype, $ID)
    {
        global $DB;

        // Get Installed version
        $criteria = [
           'FROM'   => 'glpi_items_softwareversions',
           'WHERE'  => [
              'items_id'     => $ID,
              'itemtype'     => $itemtype,
           ]
        ];

        if (count($this->noneedtobe_transfer['SoftwareVersion'] ?? [])) {
            $criteria['WHERE']['NOT'] = [
               'softwareversions_id' => $this->noneedtobe_transfer['SoftwareVersion'],
            ];
        }

        $request = $this::getAdapter()->request($criteria);

        while ($data = $request->fetchAssociative()) {
            if ($this->options['keep_software']) {
                $newversID = $this->copySingleVersion($data['softwareversions_id']);

                if (($newversID > 0)
                      && ($newversID != $data['softwareversions_id'])) {
                    $item_version = new Item_SoftwareVersion();
                    $item_version->update([
                        'id' => $data['id'],
                        'softwareversions_id' => $newversID
                    ]);
                }

            } else { // Do not keep
                // Delete inst software for item
                $item_version = new Item_SoftwareVersion();
                $item_version->delete(['id' => $data['id']], true);
            }
        } // each installed version

        // Affected licenses
        if ($this->options['keep_software']) {
            $request = $this::getAdapter()->request([
               'SELECT' => 'id',
               'FROM'   => 'glpi_items_softwarelicenses',
               'WHERE'  => [
                  'items_id'  => $ID,
                  'itemtype'  => $itemtype
               ]
            ]);
            while ($data = $request->fetchAssociative()) {
                $this->transferAffectedLicense($data['id']);
            }
        } else {
            $item_softwarelicense = new Item_SoftwareLicense();
            $items_to_delete = $item_softwarelicense->find([
                'items_id' => $ID,
                'itemtype' => $itemtype
            ]);
            foreach ($items_to_delete as $data) {
                $item_softwarelicense->delete(['id' => $data['id']], true);
            }
        }
    }


    /**
     * Transfer affected licenses to an item
     *
     * @param $ID ID of the License
    **/
    public function transferAffectedLicense($ID)
    {
        global $DB;

        $item_softwarelicense = new Item_SoftwareLicense();
        $license                  = new SoftwareLicense();

        if ($item_softwarelicense->getFromDB($ID)) {
            if ($license->getFromDB($item_softwarelicense->getField('softwarelicenses_id'))) {

                //// Update current : decrement number by 1 if valid
                if ($license->getField('number') > 1) {
                    $license->update(['id'     => $license->getID(),
                                           'number' => ($license->getField('number') - 1)]);
                } elseif ($license->getField('number') == 1) {
                    // Drop license
                    $license->delete(['id' => $license->getID()]);
                }

                // Create new license : need to transfer softwre and versions before
                $input     = [];
                $newsoftID = $this->copySingleSoftware($license->fields['softwares_id']);

                if ($newsoftID > 0) {
                    //// If license already exists : increment number by one
                    $request = $this::getAdapter()->request([
                       'SELECT' => ['id', 'number'],
                       'FROM'   => 'glpi_softwarelicenses',
                       'WHERE'  => [
                          'softwares_id' => $newsoftID,
                          'name'         => addslashes($license->fields['name']),
                          'serial'       => addslashes($license->fields['serial'])
                       ]
                    ]);
                    $results = $request->fetchAllAssociative();
                    $newlicID = -1;
                    //// If exists : increment number by 1
                    if (count($results)) {
                        // $data     = $iterator->next();
                        $data      = $results[0];
                        $newlicID = $data['id'];
                        $license->update(['id'     => $data['id'],
                                                'number' => $data['number'] + 1]);

                    } else {
                        //// If not exists : create with number = 1
                        $input = $license->fields;
                        foreach (['softwareversions_id_buy',
                                       'softwareversions_id_use'] as $field) {
                            if ($license->fields[$field] > 0) {
                                $newversID = $this->copySingleVersion($license->fields[$field]);
                                if (($newversID > 0)
                                      && ($newversID != $license->fields[$field])) {
                                    $input[$field] = $newversID;
                                }
                            }
                        }

                        unset($input['id']);
                        $input['number']       = 1;
                        $input['entities_id']  = $this->to;
                        $input['softwares_id'] = $newsoftID;
                        $newlicID              = $license->add(Toolbox::addslashes_deep($input));
                    }

                    if ($newlicID > 0) {
                        $input = ['id'                  => $ID,
                                       'softwarelicenses_id' => $newlicID];
                        $item_softwarelicense->update($input);
                    }
                }
            }
        } // getFromDB

    }


    /**
     * Transfer License and Version of a Software
     *
     * @param $ID ID of the Software
    **/
    public function transferSoftwareLicensesAndVersions($ID)
    {
        $request = $this::getAdapter()->request([
           'SELECT' => 'id',
           'FROM'   => 'glpi_softwarelicenses',
           'WHERE'  => ['softwares_id' => $ID]
        ]);

        while ($data = $request->fetchAssociative()) {
            $this->transferItem('SoftwareLicense', $data['id'], $data['id']);
        }

        $request = $this::getAdapter()->request([
           'SELECT' => 'id',
           'FROM'   => 'glpi_softwareversions',
           'WHERE'  => ['softwares_id' => $ID]
        ]);

        while ($data = $request->fetchAssociative()) {
            // Just Store the info.
            $this->addToAlreadyTransfer('SoftwareVersion', $data['id'], $data['id']);
        }
    }


    public function cleanSoftwareVersions()
    {

        if (!isset($this->already_transfer['SoftwareVersion'])) {
            return;
        }

        $vers = new SoftwareVersion();
        foreach ($this->already_transfer['SoftwareVersion'] as $old => $new) {
            if ((countElementsInTable("glpi_softwarelicenses", ['softwareversions_id_buy' => $old]) == 0)
                && (countElementsInTable("glpi_softwarelicenses", ['softwareversions_id_use' => $old]) == 0)
                && (countElementsInTable(
                    "glpi_items_softwareversions",
                    ['softwareversions_id' => $old]
                ) == 0)) {

                $vers->delete(['id' => $old]);
            }
        }
    }


    public function cleanSoftwares()
    {

        if (!isset($this->already_transfer['Software'])) {
            return;
        }

        $soft = new Software();
        foreach ($this->already_transfer['Software'] as $old => $new) {
            if ((countElementsInTable("glpi_softwarelicenses", ['softwares_id' => $old]) == 0)
                && (countElementsInTable("glpi_softwareversions", ['softwares_id' => $old]) == 0)) {

                if ($this->options['clean_software'] == 1) { // delete
                    $soft->delete(['id' => $old], 0);

                } elseif ($this->options['clean_software'] ==  2) { // purge
                    $soft->delete(['id' => $old], 1);
                }
            }
        }

    }


    /**
     * Transfer contracts
     *
     * @param $itemtype  original type of transfered item
     * @param $ID        original ID of the contract
     * @param $newID     new ID of the contract
    **/
    public function transferContracts($itemtype, $ID, $newID)
    {
        global $DB;

        $need_clean_process = false;

        // if keep
        if ($this->options['keep_contract'] && isset($this->noneedtobe_transfer['Contract'])
           && count($this->noneedtobe_transfer['Contract'])) {
            $contract = new Contract();
            // Get contracts for the item
            $request = $this::getAdapter()->request([
               'FROM'   => 'glpi_contracts_items',
               'WHERE'  => [
                  'items_id'  => $ID,
                  'itemtype'  => $itemtype,
                  'NOT'       => ['contracts_id' => $this->noneedtobe_transfer['Contract']]
               ]
            ]);

            // Foreach get item
            while ($data = $request->fetchAssociative()) {
                $need_clean_process = false;
                $item_ID            = $data['contracts_id'];
                $newcontractID      = -1;

                // is already transfer ?
                if (isset($this->already_transfer['Contract'][$item_ID])) {
                    $newcontractID = $this->already_transfer['Contract'][$item_ID];
                    if ($newcontractID != $item_ID) {
                        $need_clean_process = true;
                    }

                } else {
                    // No
                    // Can be transfer without copy ? = all linked items need to be transfer (so not copy)
                    $canbetransfer = true;
                    $types_iterator = Contract_Item::getDistinctTypes($item_ID);

                    while (($data_type = $types_iterator->next())
                             && $canbetransfer) {
                        $dtype = $data_type['itemtype'];

                        if (isset($this->needtobe_transfer[$dtype]) && count($this->needtobe_transfer[$dtype])) {
                            // No items to transfer -> exists links
                            $result = $this::getAdapter()->request([
                               'COUNT'  => 'cpt',
                               'FROM'   => 'glpi_contracts_items',
                               'WHERE'  => [
                                  'contracts_id' => $item_ID,
                                  'itemtype'     => $dtype,
                                  'NOT'          => ['items_id' => $this->needtobe_transfer[$dtype]]
                               ]
                            ])->fetchAssociative();

                            if ($result['cpt'] > 0) {
                                $canbetransfer = false;
                            }
                        } else {
                            $canbetransfer = false;
                        }

                    }

                    // Yes : transfer
                    if ($canbetransfer) {
                        $this->transferItem('Contract', $item_ID, $item_ID);
                        $newcontractID = $item_ID;

                    } else {
                        $need_clean_process = true;
                        $contract->getFromDB($item_ID);
                        // No : search contract
                        $request = $this::getAdapter()->request([
                           'SELECT' => 'id',
                           'FROM'   => 'glpi_contracts',
                           'WHERE'  => [
                              'entities_id'  => $this->to,
                              'name'         => addslashes($contract->fields['name'])
                           ]
                        ]);
                        $contract_request = $request->fetchAllAssociative();
                        if (count($contract_request)) {
                            $result = $contract_request[0];
                            $newcontractID = $result['id'];
                            $this->addToAlreadyTransfer('Contract', $item_ID, $newcontractID);
                        }

                        // found : use it
                        // not found : copy contract
                        if ($newcontractID < 0) {
                            // 1 - create new item
                            unset($contract->fields['id']);
                            $input                = $contract->fields;
                            $input['entities_id'] = $this->to;
                            unset($contract->fields);
                            $newcontractID        = $contract->add(Toolbox::addslashes_deep($input));
                            // 2 - transfer as copy
                            $this->transferItem('Contract', $item_ID, $newcontractID);
                        }

                    }
                }

                // Update links
                if ($ID == $newID) {
                    if ($item_ID != $newcontractID) {
                        $contract_item = new Contract_Item();
                        $contract_item->update([
                            'id' => $data['id'],
                            'contracts_id' => $newcontractID
                        ]);
                    }
                } else { // Same Item -> update links
                    // Copy Item -> copy links
                    if ($item_ID != $newcontractID) {
                        $contract_item = new Contract_Item();
                        $contract_item->add([
                            'contracts_id' => $newcontractID,
                            'items_id'     => $newID,
                            'itemtype'     => $itemtype
                        ]);
                    } else { // same contract for new item update link
                        $contract_item = new Contract_Item();
                        $contract_item->update([
                            'id' => $data['id'],
                            'items_id' => $newID
                        ]);
                    }
                }

                // If clean and unused ->
                if ($need_clean_process
                      && $this->options['clean_contract']) {
                    $remain = $this::getAdapter()->request([
                       'COUNT'  => 'cpt',
                       'FROM'   => 'glpi_contracts_items',
                       'WHERE'  => ['contracts_id' => $item_ID]
                    ])->fetchAssociative();

                    if ($remain['cpt'] == 0) {
                        $contract = new Contract();
                        if ($this->options['clean_contract'] == 1) {
                            $contract->delete(['id' => $item_ID]);
                        }
                        if ($this->options['clean_contract'] == 2) { // purge
                            $contract->delete(['id' => $item_ID], 1);
                        }
                    }
                }
            }
        } else {// else unlink
            $contract_item = new Contract_Item();
            $items_to_delete = $contract_item->find([
                'items_id' => $ID,
                'itemtype' => $itemtype
            ]);
            foreach ($items_to_delete as $data) {
                $contract_item->delete(['id' => $data['id']], true);
            }
        }
    }


    /**
     * Transfer documents
     *
     * @param $itemtype  original type of transfered item
     * @param $ID        original ID of the document
     * @param $newID     new ID of the document
    **/
    public function transferDocuments($itemtype, $ID, $newID)
    {
        global $DB;

        $need_clean_process = false;
        // if keep
        if ($this->options['keep_document']) {
            $document = new Document();
            // Get documents for the item
            $documents_items_query = [
               'FROM'   => 'glpi_documents_items',
               'WHERE'  => [
                  'items_id'  => $ID,
                  'itemtype'  => $itemtype,
               ]
            ];
            if (isset($this->noneedtobe_transfer['Document'])
                && count($this->noneedtobe_transfer['Document']) > 0) {
                $documents_items_query['WHERE'][] = [
                   'NOT' => ['documents_id' => $this->noneedtobe_transfer['Document']]
                ];
            }
            $request = $this::getAdapter()->request($documents_items_query);

            // Foreach get item
            while ($data = $request->fetchAssociative()) {
                $need_clean_process = false;
                $item_ID            = $data['documents_id'];
                $newdocID           = -1;

                // is already transfer ?
                if (isset($this->already_transfer['Document'][$item_ID])) {
                    $newdocID = $this->already_transfer['Document'][$item_ID];
                    if ($newdocID != $item_ID) {
                        $need_clean_process = true;
                    }

                } else {
                    // No
                    // Can be transfer without copy ? = all linked items need to be transfer (so not copy)
                    $canbetransfer = true;
                    $types_iterator = Document_Item::getDistinctTypes($item_ID);

                    while (($data_type = $types_iterator->next())
                             && $canbetransfer) {
                        $dtype = $data_type['itemtype'];
                        if (isset($this->needtobe_transfer[$dtype])) {
                            // No items to transfer -> exists links
                            $NOT = $this->needtobe_transfer[$dtype];

                            // contacts, contracts, and enterprises are linked as device.
                            if (isset($this->noneedtobe_transfer[$dtype])) {
                                $NOT = array_merge($NOT, $this->noneedtobe_transfer[$dtype]);
                            }

                            $where = [
                               'documents_id' => $item_ID,
                               'itemtype'     => $dtype
                            ];
                            if (count($NOT)) {
                                $where['NOT'] = ['items_id' => $NOT];
                            }

                            $result = $this::getAdapter()->request([
                               'COUNT'  => 'cpt',
                               'FROM'   => 'glpi_documents_items',
                               'WHERE'  => $where
                            ])->fetchAssociative();

                            if ($result['cpt'] > 0) {
                                $canbetransfer = false;
                            }

                        }
                    }

                    // Yes : transfer
                    if ($canbetransfer) {
                        $this->transferItem('Document', $item_ID, $item_ID);
                        $newdocID = $item_ID;

                    } else {
                        $need_clean_process = true;
                        $document->getFromDB($item_ID);
                        // No : search contract
                        $request = $this::getAdapter()->request([
                           'SELECT' => 'id',
                           'FROM'   => 'glpi_documents',
                           'WHERE'  => [
                              'entities_id'  => $this->to,
                              'name'         => addslashes($document->fields['name'])
                           ]
                        ]);
                        $doc_iterator = $request->fetchAllAssociative();
                        if (count($doc_iterator)) {
                            $result = $doc_iterator[0];
                            $newdocID = $result['id'];
                            $this->addToAlreadyTransfer('Document', $item_ID, $newdocID);
                        }

                        // found : use it
                        // not found : copy doc
                        if ($newdocID < 0) {
                            // 1 - create new item
                            unset($document->fields['id']);
                            $input    = $document->fields;
                            // Not set new entity Do by transferItem
                            unset($document->fields);
                            $newdocID = $document->add(Toolbox::addslashes_deep($input));
                            // 2 - transfer as copy
                            $this->transferItem('Document', $item_ID, $newdocID);
                        }
                    }
                }

                // Update links
                if ($ID == $newID) {
                    if ($item_ID != $newdocID) {
                         $document_item = new Document_Item();
                        $document_item->update([
                            'id' => $data['id'],
                            'documents_id' => $newdocID
                        ]);
                    }

                } else { // Same Item -> update links
                    // Copy Item -> copy links
                    if ($item_ID != $newdocID) {
                        $document_item = new Document_Item();
                        $document_item->add([
                            'documents_id' => $newdocID,
                            'items_id'     => $newID,
                            'itemtype'     => $itemtype
                        ]);
                    } else { // same doc for new item update link
                         $document_item = new Document_Item();
                        $document_item->update([
                            'id' => $data['id'],
                            'items_id' => $newID
                        ]);
                    }

                }

                // If clean and unused ->
                if ($need_clean_process
                      && $this->options['clean_document']) {
                    $remain = $this::getAdapter()->request([
                       'COUNT'  => 'cpt',
                       'FROM'   => 'glpi_documents_items',
                       'WHERE'  => [
                          'documents_id' => $item_ID
                       ]
                    ])->fetchAssociative();

                    if ($remain['cpt'] == 0) {
                        $document = new Document();
                        if ($this->options['clean_document'] == 1) {
                            $document->delete(['id' => $item_ID]);
                        }
                        if ($this->options['clean_document'] == 2) { // purge
                            $document->delete(['id' => $item_ID], 1);
                        }
                    }

                }
            }
        } else {// else unlink
            $document_item = new Document_Item();
            $items_to_delete = $document_item->find([
                'items_id' => $ID,
                'itemtype' => $itemtype
            ]);
            foreach ($items_to_delete as $data) {
                $document_item->delete(['id' => $data['id']], true);
            }
        }
    }


    /**
     * Delete direct connection for a linked item
     *
     * @param $itemtype        original type of transfered item
     * @param $ID              ID of the item
     * @param $link_type       type of the linked items to transfer
    **/
    public function transferDirectConnection($itemtype, $ID, $link_type)
    {
        global $DB;

        // Only same Item case : no duplication of computers
        // Default : delete
        $keep      = 0;
        $clean     = 0;

        switch ($link_type) {
            case 'Printer':
                $keep      = $this->options['keep_dc_printer'];
                $clean     = $this->options['clean_dc_printer'];
                break;

            case 'Monitor':
                $keep      = $this->options['keep_dc_monitor'];
                $clean     = $this->options['clean_dc_monitor'];
                break;

            case 'Peripheral':
                $keep      = $this->options['keep_dc_peripheral'];
                $clean     = $this->options['clean_dc_peripheral'];
                break;

            case 'Phone':
                $keep  = $this->options['keep_dc_phone'];
                $clean = $this->options['clean_dc_phone'];
                break;
        }

        if (!($link_item = getItemForItemtype($link_type))) {
            return;
        }

        // Get connections
        $criteria = [
           'FROM'   => 'glpi_computers_items',
           'WHERE'  => [
              'computers_id' => $ID,
              'itemtype'     => $link_type
           ]
        ];

        if ($link_item->maybeRecursive() && count($this->noneedtobe_transfer[$link_type])) {
            $criteria['WHERE']['NOT'] = ['items_id' => $this->noneedtobe_transfer[$link_type]];
        }

        $request = $$this::getAdapter()->request($criteria);

        // Foreach get item
        while ($data = $request->fetchAssociative()) {
            $item_ID = $data['items_id'];
            if ($link_item->getFromDB($item_ID)) {
                // If global :
                if ($link_item->fields['is_global'] == 1) {
                    $need_clean_process = false;
                    // if keep
                    if ($keep) {
                        $newID = -1;

                        // Is already transfer ?
                        if (isset($this->already_transfer[$link_type][$item_ID])) {
                            $newID = $this->already_transfer[$link_type][$item_ID];
                            // Already transfer as a copy : need clean process
                            if ($newID != $item_ID) {
                                $need_clean_process = true;
                            }

                        } else { // Not yet tranfer
                            // Can be managed like a non global one ?
                            // = all linked computers need to be transfer (so not copy)
                            $comp_criteria = [
                               'COUNT'  => 'cpt',
                               'FROM'   => 'glpi_computers_items',
                               'WHERE'  => [
                                  'itemtype'  => $link_type,
                                  'items_id'  => $item_ID
                               ]
                            ];
                            if (count($this->needtobe_transfer['Computer'])) {
                                $comp_criteria['WHERE']['NOT'] = ['computers_id' => $this->needtobe_transfer['Computer']];
                            }
                            $result = $this::getAdapter()->request($comp_criteria)->fetchAssociative();

                            // All linked computers need to be transfer -> use unique transfer system
                            if ($result['cpt'] == 0) {
                                $need_clean_process = false;
                                $this->transferItem($link_type, $item_ID, $item_ID);
                                $newID = $item_ID;

                            } else { // else Transfer by Copy
                                $need_clean_process = true;
                                // Is existing global item in the destination entity ?
                                $request = $this::getAdapter()->request([
                                   'SELECT' => 'id',
                                   'FROM'   => getTableForItemType($link_type),
                                   'WHERE'  => [
                                      'is_global'    => 1,
                                      'entities_id'  => $this->to,
                                      'name'         => addslashes($link_item->getField('name'))
                                   ]
                                ]);
                                $type_iterator = $request->fetchAllAssociative();
                                if (count($type_iterator)) {
                                    $result = $type_iterator[0];
                                    $newID = $result['id'];
                                    $this->addToAlreadyTransfer($link_type, $item_ID, $newID);
                                }

                                // Not found -> transfer copy
                                if ($newID < 0) {
                                    // 1 - create new item
                                    unset($link_item->fields['id']);
                                    $input                = $link_item->fields;
                                    $input['entities_id'] = $this->to;
                                    unset($link_item->fields);
                                    $newID = $link_item->add(Toolbox::addslashes_deep($input));
                                    // 2 - transfer as copy
                                    $this->transferItem($link_type, $item_ID, $newID);
                                }

                                // Found -> use to link : nothing to do
                            }
                        }

                        // Finish updated link if needed
                        if (($newID > 0)
                              && ($newID != $item_ID)) {
                            $conn = new Computer_Item();
                            $conn->update([
                                'id' => $data['id'],
                                'items_id' => $newID
                            ]);
                        }

                    } else {
                        // Else delete link
                        // Call Disconnect for global device (no disconnect behavior, but history )
                        $conn = new Computer_Item();
                        $conn->delete(['id' => $data['id'], '_no_auto_action' => true]);
    

                        $need_clean_process = true;

                    }
                    // If clean and not linked dc -> delete
                    if ($need_clean_process && $clean) {
                        $result = $this::getAdapter()->request([
                           'COUNT'  => 'cpt',
                           'FROM'   => 'glpi_computers_items',
                           'WHERE'  => [
                              'items_id'  => $item_ID,
                              'itemtype'  => $link_type
                           ]
                        ])->fetchAssociative();

                        if ($result['cpt'] == 0) {
                            if ($clean == 1) {
                                $link_item->delete(['id' => $item_ID]);
                            }
                            if ($clean == 2) { // purge
                                $link_item->delete(['id' => $item_ID], 1);
                            }
                        }

                    }

                } else { // If unique :
                    //if keep -> transfer list else unlink
                    if ($keep) {
                        $this->transferItem($link_type, $item_ID, $item_ID);

                    } else {
                        // Else delete link (apply disconnect behavior)
                        $conn = new Computer_Item();
                        $conn->delete(['id' => $data['id']]);

                        //if clean -> delete
                        if ($clean == 1) {
                            $link_item->delete(['id' => $item_ID]);

                        } elseif ($clean == 2) { // purge
                            $link_item->delete(['id' => $item_ID], 1);
                        }

                    }

                }

            } else {
                // Unexisting item / Force disconnect
                $conn = new Computer_Item();
                $conn->delete(['id'             => $data['id'],
                                     '_no_history'    => true,
                                     '_no_auto_action' => true]);
            }

        }
    }


    /**
     * Delete direct connection beetween an item and a computer when transfering the item
     *
     * @param $itemtype        itemtype to tranfer
     * @param $ID              ID of the item
     *
     * @since 0.84.4
     **/
    public function manageConnectionComputer($itemtype, $ID)
    {
        global $DB;

        // Get connections
        $criteria = [
           'FROM'   => 'glpi_computers_items',
           'WHERE'  => [
              'itemtype'  => $itemtype,
              'items_id'  => $ID
           ]
        ];
        if (count($this->needtobe_transfer['Computer'])) {
            $criteria['WHERE']['NOT'] = ['computers_id' => $this->needtobe_transfer['Computer']];
        }
        $request = $this::getAdapter()->request($criteria);
        $results = $request->fetchAllAssociative();
        if (count($results)) {
            // Foreach get item
            $conn = new Computer_Item();
            $comp = new Computer();
            foreach ($results as $data) {
                $item_ID = $data['items_id'];
                if ($comp->getFromDB($item_ID)) {
                    $conn->delete(['id' => $data['id']]);
                } else {
                    // Unexisting item / Force disconnect
                    $conn->delete(['id'             => $data['id'],
                          '_no_history'    => true,
                          '_no_auto_action' => true]);
                }

            }
        }
    }


    /**
     * Transfer tickets
     *
     * @param $itemtype  type of transfered item
     * @param $ID        original ID of the ticket
     * @param $newID     new ID of the ticket
    **/
    public function transferTickets($itemtype, $ID, $newID)
    {
        global $DB;

        $job   = new Ticket();
        $rel   = new Item_Ticket();

        $request = $this::getAdapter()->request([
           'SELECT'    => [
              'glpi_tickets.*',
              'glpi_items_tickets.id AS _relid'
           ],
           'FROM'      => 'glpi_tickets',
           'LEFT JOIN' => [
              'glpi_items_tickets' => [
                 'ON' => [
                    'glpi_items_tickets' => 'tickets_id',
                    'glpi_tickets'       => 'id'
                 ]
              ]
           ],
           'WHERE'     => [
              'items_id'  => $ID,
              'itemtype'  => $itemtype
           ]
        ]);
        $results = $request->fetchAllAssociative();
        if (count($results)) {
            switch ($this->options['keep_ticket']) {
                // Transfer
                case 2:
                    // Same Item / Copy Item -> update entity
                    foreach ($results as $data) {
                        $input                = $this->transferHelpdeskAdditionalInformations($data);
                        $input['id']          = $data['id'];
                        $input['entities_id'] = $this->to;

                        $job->update($input);

                        $input = [];
                        $input['id']          = $data['_relid'];
                        $input['items_id']    = $newID;
                        $input['itemtype']    = $itemtype;

                        $rel->update($input);

                        $this->addToAlreadyTransfer('Ticket', $data['id'], $data['id']);
                        $this->transferTaskCategory('Ticket', $data['id'], $data['id']);
                    }
                    break;

                    // Clean ref : keep ticket but clean link
                case 1:
                    // Same Item / Copy Item : keep and clean ref
                    foreach ($results as $data) {
                        $rel->delete(['id'       => $data['relid']]);
                        $this->addToAlreadyTransfer('Ticket', $data['id'], $data['id']);
                    }
                    break;

                    // Delete
                case 0:
                    // Same item -> delete
                    if ($ID == $newID) {
                        foreach ($results as $data) {
                            $job->delete(['id' => $data['id']]);
                        }
                    }
                    // Copy Item : nothing to do
                    break;
            }
        }

    }

    /**
     * Transfer suppliers for specified tickets or problems
     *
     * @since 0.84
     *
     * @param $itemtype  itemtype : Problem / Ticket
     * @param $ID        original ticket ID
     * @param $newID     new ticket ID
    **/
    public function transferLinkedSuppliers($itemtype, $ID, $newID)
    {
        global $DB;

        switch ($itemtype) {
            case 'Ticket':
                $table = 'glpi_suppliers_tickets';
                $field = 'tickets_id';
                $link  = new Supplier_Ticket();
                break;

            case 'Problem':
                $table = 'glpi_problems_suppliers';
                $field = 'problems_id';
                $link  = new Problem_Supplier();
                break;

            case 'Change':
                $table = 'glpi_changes_suppliers';
                $field = 'changes_id';
                $link  = new Change_Supplier();
                break;
        }

        $request = $this::getAdapter()->request([
           'FROM'   => $table,
           'WHERE'  => [$field => $ID]
        ]);

        while ($data = $request->fetchAssociative()) {
            $input = [];

            if ($data['suppliers_id'] > 0) {
                $supplier = new Supplier();

                if ($supplier->getFromDB($data['suppliers_id'])) {
                    $newID = -1;
                    $request = $this::getAdapter()->request([
                       'SELECT' => 'id',
                       'FROM'   => 'glpi_suppliers',
                       'WHERE'  => [
                          'entities_id'  => $this->to,
                          'name'         => addslashes($supplier->fields['name'])
                       ]
                    ]);
                    $results = $request->fetchAllAssociative();
                    if (count($results)) {
                        $result = $results[0];
                        $newID = $result['id'];
                    }
                    if ($newID < 0) {
                        // 1 - create new item
                        unset($supplier->fields['id']);
                        $input                 = $supplier->fields;
                        $input['entities_id']  = $this->to;
                        // Not set new entity Do by transferItem
                        unset($supplier->fields);
                        $newID                 = $supplier->add(Toolbox::addslashes_deep($input));
                    }

                    $input2['id']           = $data['id'];
                    $input2[$field]         = $ID;
                    $input2['suppliers_id'] = $newID;
                    $link->update($input2);
                }

            }

        }

    }


    /**
     * Transfer task categories for specified tickets
     *
     * @since 0.83
     *
     * @param $itemtype  itemtype : Problem / Ticket
     * @param $ID        original ticket ID
     * @param $newID     new ticket ID
    **/
    public function transferTaskCategory($itemtype, $ID, $newID)
    {
        global $DB;

        switch ($itemtype) {
            case 'Ticket':
                $table = 'glpi_tickettasks';
                $field = 'tickets_id';
                $task  = new TicketTask();
                break;

            case 'Problem':
                $table = 'glpi_problemtasks';
                $field = 'problems_id';
                $task  = new ProblemTask();
                break;

            case 'Change':
                $table = 'glpi_changetasks';
                $field = 'changes_id';
                $task  = new ProblemTask();
                break;
        }

        $request = $this::getAdapter()->request([
           'FROM'   => $table,
           'WHERE'  => [$field => $ID]
        ]);

        while ($data = $request->fetchAssociative()) {
            $input = [];

            if ($data['taskcategories_id'] > 0) {
                $categ = new TaskCategory();

                if ($categ->getFromDB($data['taskcategories_id'])) {
                    $inputcat['entities_id']  = $this->to;
                    $inputcat['completename'] = addslashes($categ->fields['completename']);
                    $catid                    = $categ->findID($inputcat);
                    if ($catid < 0) {
                        $catid = $categ->import($inputcat);
                    }
                    $input['id']                = $data['id'];
                    $input[$field]              = $ID;
                    $input['taskcategories_id'] = $catid;
                    $task->update($input);
                }

            }
        }
    }


    /**
     * Transfer ticket/problem infos
     *
     * @param $data ticket data fields
     *
     * @since 0.85 (before transferTicketAdditionalInformations)
    **/
    public function transferHelpdeskAdditionalInformations($data)
    {

        $input               = [];
        $suppliers_id_assign = 0;

        // if ($data['suppliers_id_assign'] > 0) {
        //   $suppliers_id_assign = $this->transferSingleSupplier($data['suppliers_id_assign']);
        // }

        // Transfer ticket category
        $catid = 0;
        if ($data['itilcategories_id'] > 0) {
            $categ = new ITILCategory();

            if ($categ->getFromDB($data['itilcategories_id'])) {
                $inputcat['entities_id']  = $this->to;
                $inputcat['completename'] = addslashes($categ->fields['completename']);
                $catid                    = $categ->findID($inputcat);
                if ($catid < 0) {
                    $catid = $categ->import($inputcat);
                }
            }

        }

        $input['itilcategories_id'] = $catid;
        return $input;
    }


    /**
     * Transfer history
     *
     * @param $itemtype  original type of transfered item
     * @param $ID        original ID of the history
     * @param $newID     new ID of the history
    **/
    public function transferHistory($itemtype, $ID, $newID)
    {
        global $DB;

        switch ($this->options['keep_history']) {
            // delete
            case 0:
                // Same item -> delete
                if ($ID == $newID) {
                    $log = new Log();
                    $logs_to_delete = $log->find([
                        'items_id' => $ID,
                        'itemtype' => $itemtype
                    ]);
                    foreach ($logs_to_delete as $data) {
                        $log->delete(['id' => $data['id']], true);
                    }
                }
                // Copy -> nothing to do
                break;

                // Keep history
            default:
                // Copy -> Copy datas
                if ($ID != $newID) {
                    $request = $this::getAdapter()->request([
                       'FROM'   => 'glpi_logs',
                       'WHERE'  => [
                          'itemtype'  => $itemtype,
                          'items_id'  => $ID
                       ]
                    ]);

                    while ($data = $request->fetchAssociative()) {
                        unset($data['id']);
                        $data = Toolbox::addslashes_deep($data);
                        $data = [
                           'items_id'  => $newID,
                           'itemtype'  => $itemtype
                        ] + $data;
                        $log = new Log();
                        $log->add($data);
                    }

                }
                // Same item -> nothing to do
                break;
        }
    }


    /**
     * Transfer compatible printers for a cartridge type
     *
     * @param $ID     original ID of the cartridge type
     * @param $newID  new ID of the cartridge type
    **/
    public function transferCompatiblePrinters($ID, $newID)
    {
        global $DB;

        if ($ID != $newID) {
            $request = $this::getAdapter()->request([
               'FROM'   => 'glpi_cartridgeitems_printermodels',
               'WHERE'  => ['cartridgeitems_id' => $ID]
            ]);
            $results = $request->fetchAllAssociative();
            if (count($results)) {
                $cartitem = new CartridgeItem();

                foreach ($results as $data) {
                    $data = Toolbox::addslashes_deep($data);
                    $cartitem->addCompatibleType($newID, $data["printermodels_id"]);
                }

            }

        }
    }


    /**
     * Transfer infocoms of an item
     *
     * @param $itemtype  type of the item to transfer
     * @param $ID        original ID of the item
     * @param $newID     new ID of the item
    **/
    public function transferInfocoms($itemtype, $ID, $newID)
    {
        global $DB;

        $ic = new Infocom();
        if ($ic->getFromDBforDevice($itemtype, $ID)) {
            switch ($this->options['keep_infocom']) {
                // delete
                case 0:
                    // Same item -> delete
                    if ($ID == $newID) {
                        $infocom = new Infocom();
                        $infocom_to_delete = $infocom->find([
                            'items_id' => $ID,
                            'itemtype' => $itemtype
                        ]);
                        foreach ($infocom_to_delete as $data) {
                            $infocom->delete(['id' => $data['id']], true);
                        }
                    }
                    // Copy : nothing to do
                    break;

                    // Keep
                default:
                    // transfer enterprise
                    $suppliers_id = 0;
                    if ($ic->fields['suppliers_id'] > 0) {
                        $suppliers_id = $this->transferSingleSupplier($ic->fields['suppliers_id']);
                    }

                    // Copy : copy infocoms
                    if ($ID != $newID) {
                        // Copy items
                        $input                 = $ic->fields;
                        $input['items_id']     = $newID;
                        $input['suppliers_id'] = $suppliers_id;
                        unset($input['id']);
                        unset($ic->fields);
                        $ic->add(Toolbox::addslashes_deep($input));

                    } else {
                        // Same Item : manage only enterprise move
                        // Update enterprise
                        if (($suppliers_id > 0)
                            && ($suppliers_id != $ic->fields['suppliers_id'])) {
                            $ic->update(['id'           => $ic->fields['id'],
                                              'suppliers_id' => $suppliers_id]);
                        }
                    }

                    break;
            }
        }
    }


    /**
     * Transfer an enterprise
     *
     * @param $ID ID of the enterprise
    **/
    public function transferSingleSupplier($ID)
    {
        global $DB;

        // TODO clean system : needed ?
        $ent = new Supplier();
        if ($this->options['keep_supplier']
            && $ent->getFromDB($ID)) {

            if (isset($this->noneedtobe_transfer['Supplier'][$ID])) {
                // recursive enterprise
                return $ID;
            }
            if (isset($this->already_transfer['Supplier'][$ID])) {
                // Already transfer
                return $this->already_transfer['Supplier'][$ID];
            }

            $newID           = -1;
            // Not already transfer
            $links_remaining = 0;
            // All linked items need to be transfer so transfer enterprise ?
            // Search for contract
            $criteria = [
               'COUNT'  => 'cpt',
               'FROM'   => 'glpi_contracts_suppliers',
               'WHERE'  => [
                  'suppliers_id' => $ID
               ]
            ];
            if (count($this->needtobe_transfer['Contract'])) {
                $criteria['WHERE']['NOT'] = ['contracts_id' => $this->needtobe_transfer['Contract']];
            }

            $result = $this::getAdapter()->request($criteria)->fetchAssociative();
            $links_remaining = $result['cpt'];

            if ($links_remaining == 0) {
                // Search for infocoms
                if ($this->options['keep_infocom']) {
                    foreach (Infocom::getItemtypesThatCanHave() as $itemtype) {
                        if (isset($this->needtobe_transfer[$itemtype])) {
                            $icriteria = [
                               'COUNT'  => 'cpt',
                               'FROM'   => 'glpi_infocoms',
                               'WHERE'  => [
                                  'suppliers_id' => $ID,
                                  'itemtype'     => $itemtype
                               ]
                            ];
                            if (count($this->needtobe_transfer[$itemtype])) {
                                $icriteria['WHERE']['NOT'] = ['items_id' => $this->needtobe_transfer[$itemtype]];
                            }

                            $result = $this::getAdapter()->request($icriteria)->fetchAssociative();
                            $links_remaining += $result['cpt'];
                        }
                    }
                }
            }

            // All linked items need to be transfer -> use unique transfer system
            if ($links_remaining == 0) {
                $this->transferItem('Supplier', $ID, $ID);
                $newID = $ID;

            } else { // else Transfer by Copy
                // Is existing item in the destination entity ?
                $request = $this::getAdapter()->request([
                   'FROM'   => 'glpi_suppliers',
                   'WHERE'  => [
                      'entities_id'  => $this->to,
                      'name'         => addslashes($ent->fields['name'])
                   ]
                ]);
                $results = $request->fetchAllAssociative();
                if (count($results)) {
                    $result = $results[0];
                    $newID = $result['id'];
                    $this->addToAlreadyTransfer('Supplier', $ID, $newID);
                }

                // Not found -> transfer copy
                if ($newID < 0) {
                    // 1 - create new item
                    unset($ent->fields['id']);
                    $input                = $ent->fields;
                    $input['entities_id'] = $this->to;
                    unset($ent->fields);
                    $newID                = $ent->add(Toolbox::addslashes_deep($input));
                    // 2 - transfer as copy
                    $this->transferItem('Supplier', $ID, $newID);
                }

                // Found -> use to link : nothing to do
            }
            return $newID;
        }
        return 0;
    }


    /**
     * Transfer contacts of an enterprise
     *
     * @param $ID     original ID of the enterprise
     * @param $newID  new ID of the enterprise
    **/
    public function transferSupplierContacts($ID, $newID)
    {
        global $DB;

        $need_clean_process = false;
        // if keep
        if ($this->options['keep_contact']) {
            $contact = new Contact();
            // Get contracts for the item
            $criteria = [
               'FROM'   => 'glpi_contacts_suppliers',
               'WHERE'  => [
                  'suppliers_id' => $ID,
               ]
            ];
            if (count($this->noneedtobe_transfer['Contact'])) {
                $criteria['WHERE']['NOT'] = ['contacts_id' => $this->noneedtobe_transfer['Contact']];
            }
            $request = $this::getAdapter()->request($criteria);

            // Foreach get item
            while ($data = $request->fetchAssociative()) {
                $need_clean_process = false;
                $item_ID            = $data['contacts_id'];
                $newcontactID       = -1;

                // is already transfer ?
                if (isset($this->already_transfer['Contact'][$item_ID])) {
                    $newcontactID = $this->already_transfer['Contact'][$item_ID];
                    if ($newcontactID != $item_ID) {
                        $need_clean_process = true;
                    }

                } else {
                    $canbetransfer = true;
                    // Transfer enterprise : is the contact used for another enterprise ?
                    if ($ID == $newID) {
                        $scriteria = [
                           'COUNT'  => 'cpt',
                           'FROM'   => 'glpi_contacts_suppliers',
                           'WHERE'  => [
                              'contacts_id'  => $item_ID
                           ]
                        ];
                        if (count($this->needtobe_transfer['Supplier'])
                            || count($this->noneedtobe_transfer['Supplier'])
                        ) {
                            $scriteria['WHERE']['NOT'] = ['suppliers_id' => $this->needtobe_transfer['Supplier'] + $this->noneedtobe_transfer['Supplier']];
                        }

                        $result = $this::getAdapter()->request($scriteria)->fetchAssociative();
                        if ($result['cpt'] > 0) {
                            $canbetransfer = false;
                        }
                    }

                    // Yes : transfer
                    if ($canbetransfer) {
                        $this->transferItem('Contact', $item_ID, $item_ID);
                        $newcontactID = $item_ID;

                    } else {
                        $need_clean_process = true;
                        $contact->getFromDB($item_ID);
                        // No : search contract
                        $request = $this::getAdapter()->request([
                           'SELECT' => 'id',
                           'FROM'   => 'glpi_contacts',
                           'WHERE'  => [
                              'entities_id'  => $this->to,
                              'name'         => addslashes($contact->fields['name']),
                              'firstname'    => addslashes($contact->fields['firstname'])
                           ]
                        ]);
                        $contact_iterator = $request->fetchAllAssociative();
                        if (count($contact_iterator)) {
                            $result = $contact_iterator[0];
                            $newcontactID = $result['id'];
                            $this->addToAlreadyTransfer('Contact', $item_ID, $newcontactID);
                        }

                        // found : use it
                        // not found : copy contract
                        if ($newcontactID < 0) {
                            // 1 - create new item
                            unset($contact->fields['id']);
                            $input                = $contact->fields;
                            $input['entities_id'] = $this->to;
                            unset($contact->fields);
                            $newcontactID         = $contact->add(Toolbox::addslashes_deep($input));
                            // 2 - transfer as copy
                            $this->transferItem('Contact', $item_ID, $newcontactID);
                        }

                    }
                }

                // Update links
                if ($ID == $newID) {
                    if ($item_ID != $newcontactID) {
                        $contacts_suppliers = new Contact_Supplier();
                        $contacts_suppliers->update([
                            'id'           => $data['id'],
                            'contacts_id'  => $newcontactID
                        ]);
                    }

                } else { // Same Item -> update links
                    // Copy Item -> copy links
                    if ($item_ID != $newcontactID) {
                        $contacts_suppliers = new Contact_Supplier();
                        $contacts_suppliers->add([
                            'id'           => $data['id'],
                            'contacts_id'  => $newcontactID
                        ]);
                    } else { // transfer contact but copy enterprise : update link
                        $contacts_suppliers = new Contact_Supplier();
                        $contacts_suppliers->update([
                            'id'           => $data['id'],
                            'suppliers_id' => $newID
                        ]);                        
                        
                    }
                }

                // If clean and unused ->
                if ($need_clean_process
                      && $this->options['clean_contact']) {
                    $remain = $this::getAdapter()->request([
                       'COUNT'  => 'cpt',
                       'FROM'   => 'glpi_contacts_suppliers',
                       'WHERE'  => ['contacts_id' => $item_ID]
                    ])->fetchAssociative();

                    if ($remain['cpt'] == 0) {
                        if ($this->options['clean_contact'] == 1) {
                            $contact->delete(['id' => $item_ID]);
                        }
                        if ($this->options['clean_contact'] == 2) { // purge
                            $contact->delete(['id' => $item_ID], 1);
                        }
                    }
                }

            }
        } else {// else unlink
            $supplier = new Supplier();
            $supplier_to_delete = $supplier->find([
                'suppliers_id' => $ID
            ]);
            foreach ($supplier_to_delete as $data) {
                $supplier->delete(['id' => $data['id']]);
            }
        }
    }


    /**
     * Transfer reservations of an item
     *
     * @param $itemtype  original type of transfered item
     * @param $ID        original ID of the item
     * @param $newID     new ID of the item
    **/
    public function transferReservations($itemtype, $ID, $newID)
    {
        $ri = new ReservationItem();

        if ($ri->getFromDBbyItem($itemtype, $ID)) {
            switch ($this->options['keep_reservation']) {
                // delete
                case 0:
                    // Same item -> delete
                    if ($ID == $newID) {
                        $ri->delete(['id' => $ri->fields['id']], true);
                    }
                    // Copy : nothing to do
                    break;

                    // Keep
                default:
                    // Copy : set item as reservable
                    if ($ID != $newID) {
                        $input['itemtype']  = $itemtype;
                        $input['items_id']  = $newID;
                        $input['is_active'] = $ri->fields['is_active'];
                        unset($ri->fields);
                        $ri->add(Toolbox::addslashes_deep($input));
                    }
                    // Same item -> nothing to do
                    break;
            }
        }
    }


    /**
     * Transfer devices of an item
     *
     * @param $itemtype        original type of transfered item
     * @param $ID              ID of the item
     * @param $newID           new ID of the item
    **/
    public function transferDevices($itemtype, $ID, $newID)
    {
        global $DB;

        // Only same case because no duplication of computers
        switch ($this->options['keep_device']) {
            // delete devices
            case 0:
                foreach (Item_Devices::getItemAffinities($itemtype) as $type) {
                    $item_device = new $type();
                    $devices_to_delete = $item_device->find([
                        'items_id'  => $ID,
                        'itemtype'  => $itemtype
                    ]);
                    foreach ($devices_to_delete as $data) {
                        $item_device->delete(['id' => $data['id']], true);
                    }
                }

                // no break
            default: // Keep devices
                foreach (Item_Devices::getItemAffinities($itemtype) as $itemdevicetype) {
                    $itemdevicetable = getTableForItemType($itemdevicetype);
                    $devicetype      = $itemdevicetype::getDeviceType();
                    $devicetable     = getTableForItemType($devicetype);
                    $fk              = getForeignKeyFieldForTable($devicetable);

                    $device          = new $devicetype();
                    // Get contracts for the item
                    $criteria = [
                       'FROM'   => $itemdevicetable,
                       'WHERE'  => [
                          'items_id'  => $ID,
                          'itemtype'  => $itemtype
                       ]
                    ];
                    if (isset($this->noneedtobe_transfer[$devicetype])
                       && count($this->noneedtobe_transfer[$devicetype])
                    ) {
                        $criteria['WHERE']['NOT'] = [$fk => $this->noneedtobe_transfer[$devicetype]];
                    }
                    $request = $this::getAdapter()->request($criteria);
                    $results = $request->fetchAllAssociative();
                    if (count($results)) {
                        // Foreach get item
                        foreach ($results as $data) {
                            $item_ID     = $data[$fk];
                            $newdeviceID = -1;

                            // is already transfer ?
                            if (isset($this->already_transfer[$devicetype][$item_ID])) {
                                $newdeviceID = $this->already_transfer[$devicetype][$item_ID];

                            } else {
                                // No
                                // Can be transfer without copy ? = all linked items need to be transfer (so not copy)
                                $canbetransfer = true;
                                $type_request = $this::getAdapter()->request([
                                   'SELECT'          => 'itemtype',
                                   'DISTINCT'        => true,
                                   'FROM'            => $itemdevicetable,
                                   'WHERE'           => [$fk => $item_ID]
                                ]);

                                while (($data_type = $type_request->fetchAssociative())
                                         && $canbetransfer) {
                                    $dtype = $data_type['itemtype'];

                                    if (isset($this->needtobe_transfer[$dtype]) && count($this->needtobe_transfer[$dtype])) {
                                        // No items to transfer -> exists links
                                        $dcriteria = [
                                           'COUNT'  => 'cpt',
                                           'FROM'   => $itemdevicetable,
                                           'WHERE'  => [
                                              $fk         => $item_ID,
                                              'itemtype'  => $dtype,
                                              'NOT'       => [
                                                 'items_id'  => $this->needtobe_transfer[$dtype]
                                              ]
                                           ]
                                        ];

                                        $result = $this::getAdapter()->request($dcriteria)->fetchAssociative();

                                        if ($result['cpt'] > 0) {
                                            $canbetransfer = false;
                                        }

                                    } else {
                                        $canbetransfer = false;
                                    }

                                }

                                // Yes : transfer
                                if ($canbetransfer) {
                                    $this->transferItem($devicetype, $item_ID, $item_ID);
                                    $newdeviceID = $item_ID;

                                } else {
                                    $device->getFromDB($item_ID);
                                    // No : search device
                                    $field = "name";
                                    if (!$DB->fieldExists($devicetable, "name")) {
                                        $field = "designation";
                                    }

                                    $request = $this::getAdapter()->request([
                                       'SELECT' => 'id',
                                       'FROM'   => $devicetable,
                                       'WHERE'  => [
                                          'entities_id'  => $this->to,
                                          $field         => addslashes($device->fields[$field])
                                       ]
                                    ]);
                                    $device_iterator = $request->fetchAllAssociative();
                                    if (count($device_iterator)) {
                                        $result = $device_iterator[0];
                                        $newdeviceID = $result['id'];
                                        $this->addToAlreadyTransfer($devicetype, $item_ID, $newdeviceID);
                                    }

                                    // found : use it
                                    // not found : copy contract
                                    if ($newdeviceID < 0) {
                                        // 1 - create new item
                                        unset($device->fields['id']);
                                        $input                = $device->fields;
                                        // Fix for fields with NULL in DB
                                        foreach ($input as $key => $value) {
                                            if ($value == '') {
                                                unset($input[$key]);
                                            }
                                        }
                                        $input['entities_id'] = $this->to;
                                        unset($device->fields);
                                        $newdeviceID = $device->add(Toolbox::addslashes_deep($input));
                                        // 2 - transfer as copy
                                        $this->transferItem($devicetype, $item_ID, $newdeviceID);
                                    }
                                }
                            }

                            // Update links
                            $itemdevice = new $itemdevicetype();
                            $itemdevice->update([
                                'id'        => $data['id'],
                                $fk         => $newdeviceID,
                                'items_id'  => $newID
                            ]);
                            $this->transferItem($itemdevicetype, $data['id'], $data['id']);
                        }
                    }
                }
                break;
        }
    }


    /**
     * Transfer network links
     *
     * @param $itemtype     original type of transfered item
     * @param $ID           original ID of the item
     * @param $newID        new ID of the item
    **/
    public function transferNetworkLink($itemtype, $ID, $newID)
    {
        global $DB;
        /// TODO manage with new network system
        $np = new NetworkPort();
        $nn = new NetworkPort_NetworkPort();

        $request = $this::getAdapter()->request([
           'SELECT'    => [
              'glpi_networkports.*',
              'glpi_networkportethernets.netpoints_id'
           ],
           'FROM'      => 'glpi_networkports',
           'LEFT JOIN' => [
              'glpi_networkportethernets'   => [
                 'ON' => [
                    'glpi_networkportethernets'   => 'networkports_id',
                    'glpi_networkports'           => 'id'
                 ]
              ]
           ],
           'WHERE'     => [
              'glpi_networkports.items_id'  => $ID,
              'glpi_networkports.itemtype'  => $itemtype
           ]
        ]);
        $results = $request->fetchAllAssociative();
        if (count($results)) {

            switch ($this->options['keep_networklink']) {
                // Delete netport
                case 0:
                    // Not a copy -> delete
                    if ($ID == $newID) {
                        foreach ($results as $data) {
                            $np->delete(['id' => $data['id']]);
                        }
                    }
                    // Copy -> do nothing
                    break;

                    // Disconnect
                case 1:
                    // Not a copy -> disconnect
                    if ($ID == $newID) {
                        foreach ($results as $data) {
                            if ($nn->getFromDBForNetworkPort($data['id'])) {
                                $nn->delete($data);
                            }
                            if ($data['netpoints_id']) {
                                $netpointID  = $this->transferDropdownNetpoint($data['netpoints_id']);
                                $input['id']           = $data['id'];
                                $input['netpoints_id'] = $netpointID;
                                $np->update($input);
                            }
                        }
                    } else { // Copy -> copy netports
                        // while ($data = $iterator->next()) {
                        foreach ($results as $data) {
                            $data             = Toolbox::addslashes_deep($data);
                            unset($data['id']);
                            $data['items_id'] = $newID;
                            $data['netpoints_id']
                                              = $this->transferDropdownNetpoint($data['netpoints_id']);
                            unset($np->fields);
                            $np->add(Toolbox::addslashes_deep($data));
                        }
                    }
                    break;

                    // Keep network links
                default:
                    // Copy -> Copy netpoints (do not keep links)
                    if ($ID != $newID) {
                        while ($data = $iterator->next()) {
                            unset($data['id']);
                            $data['items_id'] = $newID;
                            $data['netpoints_id']
                                              = $this->transferDropdownNetpoint($data['netpoints_id']);
                            unset($np->fields);
                            $np->add(Toolbox::addslashes_deep($data));
                        }
                    } else {
                        while ($data = $iterator->next()) {
                            // Not a copy -> only update netpoint
                            if ($data['netpoints_id']) {
                                $netpointID  = $this->transferDropdownNetpoint($data['netpoints_id']);
                                $input['id']           = $data['id'];
                                $input['netpoints_id'] = $netpointID;
                                $np->update($input);
                            }
                        }
                    }

            }
        }
    }


    /**
     * Print the transfer form
     *
     * @param $ID        integer : Id of the contact to print
     * @param $options   array
     *     - target filename : where to go when done.
     *     - withtemplate boolean : template or basic item
     *
     * @return boolean item found
    **/
    public function showForm($ID, $options = [])
    {
        $edit_form = true;
        if (strpos($_SERVER['HTTP_REFERER'], "transfer.form.php") === false) {
            $edit_form = false;
        }

        $this->initForm($ID, $options);

        $params = [];
        if (!Session::haveRightsOr("transfer", [CREATE, UPDATE, PURGE])) {
            $params['readonly'] = true;
        }

        $general = [0 => _x('button', 'Delete permanently'),
                         1 => _x('button', 'Disconnect') ,
                         2 => __('Keep') ];
        $keep  = [0 => _x('button', 'Delete permanently'),
                       1 => __('Preserve')];

        $clean = [0 => __('Preserve'),
                       1 => _x('button', 'Put in trashbin'),
                       2 => _x('button', 'Delete permanently')];
        $form = [
          'action' => $edit_form ? $this->getFormURL() : $options['target'],
          'buttons' => [
              [
                  'name' => $this->isNewID($ID) ? 'add' : 'update',
                  'value' => $this->isNewID($ID) ? __('Add') : __('Update'),
                  'class' => 'btn btn-secondary'
              ],
          ],
          'content' => [
              __('General') => [
                  'visible' => true,
                  'inputs' => [
                      $this->isNewID($ID)  => [
                          'type' => 'hidden',
                          'name' => 'id',
                          'value' => $ID
                      ],
                      Entity::getTypeName() => $edit_form ? [] : [
                          'type' => 'select',
                          'name' => 'to_entity',
                          'itemtype' => Entity::class,
                          'value' => $this->to,
                          'col_lg' => 8,
                      ],
                      '' => $edit_form ? [] : [
                          'content' => "<input type='submit' name='transfer' value=\"".__s('Execute')."\"
                            class='btn btn-sm btn-warning'>",
                      ],
                      __('Name') => !$edit_form ? [] : [
                          'type' => 'text',
                          'name' => 'name',
                          'value' => $this->fields['name'],
                      ],
                      __('Last update') => !$edit_form ? [] : [
                          'content' => ($this->fields["date_mod"] ?
                              Html::convDateTime($this->fields["date_mod"]) :
                              __('Never')),
                      ],
                      __('Comments') => !$edit_form ? [] : [
                          'type' => 'textarea',
                          'name' => 'comments',
                          'value' => $this->fields['comment'],
                          'col_lg' => 12,
                          'col_md' => 12,
                      ],
                      __('Historical') => [
                          'type' => 'select',
                          'name' => 'keep_history',
                          'values' => $keep,
                          'value' => $this->fields['keep_history'],
                      ],
                  ],
              ],
              __('Assets') => [
                  'visible' => true,
                  'inputs' => [
                      _n('Network port', 'Network ports', Session::getPluralNumber()) => [
                          'type' => 'select',
                          'name' => 'keep_networklink',
                          'values' => $general,
                          'value' => $this->fields['keep_networklink'],
                      ],
                      _n('Ticket', 'Tickets', Session::getPluralNumber()) => [
                          'type' => 'select',
                          'name' => 'keep_ticket',
                          'values' => $general,
                          'value' => $this->fields['keep_ticket'],
                      ],
                      __('Software of items') => [
                          'type' => 'select',
                          'name' => 'keep_software',
                          'values' => $keep,
                          'value' => $this->fields['keep_software'],
                      ],
                      __('If software are no longer used') => [
                          'type' => 'select',
                          'name' => 'clean_software',
                          'values' => $clean,
                          'value' => $this->fields['clean_software'],
                      ],
                      _n('Reservation', 'Reservations', Session::getPluralNumber()) => [
                          'type' => 'select',
                          'name' => 'keep_reservation',
                          'values' => $keep,
                          'value' => $this->fields['keep_reservation'],
                      ],
                      _n('Component', 'Components', Session::getPluralNumber()) => [
                          'type' => 'select',
                          'name' => 'keep_device',
                          'values' => $keep,
                          'value' => $this->fields['keep_device'],
                      ],
                      __('Links between printers and cartridge types and cartridges') => [
                          'type' => 'select',
                          'name' => 'keep_cartridgeitem',
                          'values' => $keep,
                          'value' => $this->fields['keep_cartridgeitem'],
                      ],
                      __('If the cartridge types are no longer used') => [
                          'type' => 'select',
                          'name' => 'clean_cartridgeitem',
                          'values' => $clean,
                          'value' => $this->fields['clean_cartridgeitem'],
                      ],
                      __('Links between cartridge types and cartridges') => [
                          'type' => 'select',
                          'name' => 'keep_cartridge',
                          'values' => $keep,
                          'value' => $this->fields['keep_cartridge'],
                      ],
                      __('Financial and administrative information') => [
                          'type' => 'select',
                          'name' => 'keep_infocom',
                          'values' => $keep,
                          'value' => $this->fields['keep_infocom'],
                      ],
                      __('Links between consumable types and consumables') => [
                          'type' => 'select',
                          'name' => 'keep_consumable',
                          'values' => $keep,
                          'value' => $this->fields['keep_consumable'],
                      ],
                      __('Links between computers and volumes') => [
                          'type' => 'select',
                          'name' => 'keep_disk',
                          'values' => $keep,
                          'value' => $this->fields['keep_disk'],
                      ],
                  ],
              ],
              __('Direct connections') => [
                  'visible' => true,
                  'inputs' => [
                      _n('Monitor', 'Monitors', Session::getPluralNumber()) => [
                          'type' => 'select',
                          'name' => 'keep_dc_monitor',
                          'values' => $keep,
                          'value' => $this->fields['keep_dc_monitor'],
                      ],
                      __('If monitors are no longer used') => [
                          'type' => 'select',
                          'name' => 'clean_dc_monitor',
                          'values' => $clean,
                          'value' => $this->fields['clean_dc_monitor'],
                      ],
                      _n('Printer', 'Printers', Session::getPluralNumber()) => [
                          'type' => 'select',
                          'name' => 'keep_dc_printer',
                          'values' => $keep,
                          'value' => $this->fields['keep_dc_printer'],
                      ],
                      __('If printers are no longer used') => [
                          'type' => 'select',
                          'name' => 'clean_dc_printer',
                          'values' => $clean,
                          'value' => $this->fields['clean_dc_printer'],
                      ],
                      Peripheral::getTypeName(Session::getPluralNumber()) => [
                          'type' => 'select',
                          'name' => 'keep_dc_peripheral',
                          'values' => $keep,
                          'value' => $this->fields['keep_dc_peripheral'],
                      ],
                      __('If devices are no longer used') => [
                          'type' => 'select',
                          'name' => 'clean_dc_peripheral',
                          'values' => $clean,
                          'value' => $this->fields['clean_dc_peripheral'],
                      ],
                      _n('Phone', 'Phones', Session::getPluralNumber()) => [
                          'type' => 'select',
                          'name' => 'keep_dc_phone',
                          'values' => $keep,
                          'value' => $this->fields['keep_dc_phone'],
                      ],
                      __('If phones are no longer used') => [
                          'type' => 'select',
                          'name' => 'clean_dc_phone',
                          'values' => $clean,
                          'value' => $this->fields['clean_dc_phone'],
                      ],
                  ],
              ],
              __('Management') => [
                  'visible' => true,
                  'inputs' => [
                      _n('Supplier', 'Suppliers', Session::getPluralNumber()) => [
                          'type' => 'select',
                          'name' => 'keep_supplier',
                          'values' => $keep,
                          'value' => $this->fields['keep_supplier'],
                      ],
                      __('If suppliers are no longer used') => [
                          'type' => 'select',
                          'name' => 'clean_supplier',
                          'values' => $clean,
                          'value' => $this->fields['clean_supplier'],
                      ],
                      __('Links between suppliers and contacts') => [
                          'type' => 'select',
                          'name' => 'keep_contact',
                          'values' => $keep,
                          'value' => $this->fields['keep_contact'],
                      ],
                      __('If contacts are no longer used') => [
                          'type' => 'select',
                          'name' => 'clean_contact',
                          'values' => $clean,
                          'value' => $this->fields['clean_contact'],
                      ],
                      Document::getTypeName(Session::getPluralNumber()) => [
                          'type' => 'select',
                          'name' => 'keep_document',
                          'values' => $keep,
                          'value' => $this->fields['keep_document'],
                      ],
                      __('If documents are no longer used') => [
                          'type' => 'select',
                          'name' => 'clean_document',
                          'values' => $clean,
                          'value' => $this->fields['clean_document'],
                      ],
                      _n('Contract', 'Contracts', Session::getPluralNumber()) => [
                          'type' => 'select',
                          'name' => 'keep_contract',
                          'values' => $keep,
                          'value' => $this->fields['keep_contract'],
                      ],
                      __('If contracts are no longer used') => [
                          'type' => 'select',
                          'name' => 'clean_contract',
                          'values' => $clean,
                          'value' => $this->fields['clean_contract'],
                      ],
                  ],
              ],
          ],
        ];
        renderTwigForm($form);
    }


    // Display items to transfers
    public function showTransferList()
    {
        global $DB, $CFG_GLPI;

        if (isset($_SESSION['glpitransfer_list']) && count($_SESSION['glpitransfer_list'])) {
            echo "<div class='center b'>".
                   __('You can continue to add elements to be transferred or execute the transfer now');
            echo "<br>".__('Think of making a backup before transferring items.')."</div>";
            echo "<table class='tab_cadre_fixe' aria-label='Items to transfer'>";
            echo '<tr><th>'.__('Items to transfer').'</th><th>'.__('Transfer mode')."&nbsp;";
            $rand = mt_rand();
            renderTwigTemplate('macros/input.twig', [
               'type' => 'select',
               'id' => 'dropdown_id'.$rand,
               'name' => 'id',
               'values' => getOptionForItems(Transfer::class),
               'hooks' => [
                   'change' => <<<JS
                    var value = document.getElementById('dropdown_id$rand').value;
                    $('#transfer_form').load('$CFG_GLPI[root_doc]/ajax/transfers.php', {action: 'showform', id: value});
                JS,
               ]
            ]);
            echo '</th></tr>';

            echo "<tr><td class='tab_bg_1 top'>";
            foreach ($_SESSION['glpitransfer_list'] as $itemtype => $tab) {
                if (count($tab)) {
                    if (!($item = getItemForItemtype($itemtype))) {
                        continue;
                    }
                    $table = getTableForItemType($itemtype);

                    $request = $this::getAdapter()->request([
                       'SELECT'    => [
                          "$table.id",
                          "$table.name",
                          'entities.completename AS locname',
                          'entities.id AS entID'
                       ],
                       'FROM'      => $table,
                       'LEFT JOIN' => [
                          'glpi_entities AS entities'   => [
                             'ON' => [
                                'entities' => 'id',
                                $table     => 'entities_id'
                             ]
                          ]
                       ],
                       'WHERE'     => ["$table.id" => $tab],
                       'ORDERBY'   => ['locname', "$table.name"]
                    ]);
                    $entID = -1;
                    $results = $request->fetchAllAssociative();
                    if (count($results)) {
                        echo '<h3>'.$item->getTypeName().'</h3>';
                        foreach ($results as $data) {
                            if ($entID != $data['entID']) {
                                if ($entID != -1) {
                                    echo '<br>';
                                }
                                $entID = $data['entID'];
                                echo "<span class='b spaced'>".$data['locname']."</span><br>";
                            }
                            echo ($data['name'] ? $data['name'] : "(".$data['id'].")")."<br>";
                        }
                    }
                }
            }
            echo "</td><td class='tab_bg_2 top'>";

            if (countElementsInTable('glpi_transfers') == 0) {
                echo __('No item found');
            } else {
                $params = ['id' => '__VALUE__'];
                Ajax::updateItemOnSelectEvent(
                    "dropdown_id$rand",
                    "transfer_form",
                    $CFG_GLPI["root_doc"]."/ajax/transfers.php",
                    $params
                );
            }

            echo "<div class='center' id='transfer_form'><br>";
            Html::showSimpleForm(
                $CFG_GLPI["root_doc"]."/front/transfer.action.php",
                'clear',
                __('To empty the list of elements to be transferred')
            );
            echo "</div>";
            echo '</td></tr>';
            echo '</table>';

        } else {
            echo __('No selected element or badly defined operation');
        }
    }


    public function cleanRelationData()
    {

        parent::cleanRelationData();

        if ($this->isUsedAsAutomaticTransferModel()) {
            Config::setConfigurationValues(
                'core',
                [
                  'transfers_id_auto' => 0,
            ]
            );
        }
    }


    /**
     * Check if used as automatic transfer model.
     *
     * @return boolean
     */
    private function isUsedAsAutomaticTransferModel()
    {

        $config_values = Config::getConfigurationValues('core', ['transfers_id_auto']);

        return array_key_exists('transfers_id_auto', $config_values)
           && $config_values['transfers_id_auto'] == $this->fields['id'];
    }
}
