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

class RuleDictionnaryDropdownCollection extends RuleCollection
{
    public static $rightname = 'rule_dictionnary_dropdown';

    public $menu_type = 'dictionnary';

    // Specific ones
    /// dropdown table
    public $item_table = "";

    public $stop_on_first_match = true;
    public $can_replay_rules    = true;



    /**
     * @see RuleCollection::replayRulesOnExistingDB()
    **/
    public function replayRulesOnExistingDB($offset = 0, $maxtime = 0, $items = [], $params = [])
    {
        // Model check : need to check using manufacturer extra data so specific function
        if (strpos($this->item_table, 'models')) {
            return $this->replayRulesOnExistingDBForModel($offset, $maxtime);
        }

        if (isCommandLine()) {
            printf(__('Replay rules on existing database started on %s') . "\n", date("r"));
        }

        // Get All items
        $criteria = ['FROM' => $this->item_table];
        if ($offset) {
            $criteria['START'] = $offset;
            $criteria['LIMIT'] = 999999999;
        }
        $request   = $this::getAdapter()->request($criteria)->fetchAllAssociative();
        $nb         = count($request) + $offset;
        $i          = $offset;
        if ($nb > $offset) {
            // Step to refresh progressbar
            $step              = (($nb > 20) ? floor($nb / 20) : 1);
            $send              = [];
            $send["tablename"] = $this->item_table;

            foreach ($request as $data) {
                if (!($i % $step)) {
                    if (isCommandLine()) {
                        //TRANS: %1$s is a row, %2$s is total rows
                        printf(__('Replay rules on existing database: %1$s/%2$s') . "\r", $i, $nb);
                    } else {
                        Html::changeProgressBarPosition($i, $nb, "$i / $nb");
                    }
                }

                //Replay Type dictionnary
                $ID = Dropdown::importExternal(
                    getItemTypeForTable($this->item_table),
                    addslashes($data["name"]),
                    -1,
                    [],
                    addslashes($data["comment"])
                );
                if ($data['id'] != $ID) {
                    $tomove[$data['id']] = $ID;
                    $type                = getItemTypeForTable($this->item_table);

                    if ($dropdown = getItemForItemtype($type)) {
                        $dropdown->delete(['id'          => $data['id'],
                                                '_replace_by' => $ID]);
                    }
                }
                $i++;

                if ($maxtime) {
                    $crt = explode(" ", microtime());
                    if (($crt[0] + $crt[1]) > $maxtime) {
                        break;
                    }
                }
            } // end while
        }

        if (isCommandLine()) {
            printf(__('Replay rules on existing database started on %s') . "\n", date("r"));
        } else {
            Html::changeProgressBarPosition($i, $nb, "$i / $nb");
        }
        return (($i == $nb) ? -1 : $i);
    }


    /**
     * Replay collection rules on an existing DB for model dropdowns
     *
     * @param $offset    offset used to begin (default 0)
     * @param $maxtime   maximum time of process (reload at the end) (default 0)
     *
     * @return -1 on completion else current offset
    **/
    public function replayRulesOnExistingDBForModel($offset = 0, $maxtime = 0)
    {
        global $DB;

        if (isCommandLine()) {
            printf(__('Replay rules on existing database started on %s') . "\n", date("r"));
        }

        // Model check : need to check using manufacturer extra data
        if (strpos($this->item_table, 'models') === false) {
            echo __('Error replaying rules');
            return false;
        }

        $model_table = getPlural(str_replace('models', '', $this->item_table));
        $model_field = getForeignKeyFieldForTable($this->item_table);

        // Need to give manufacturer from item table
        $criteria = [
           'SELECT'          => [
              'glpi_manufacturers.id AS idmanu',
              'glpi_manufacturers.name AS manufacturer',
              $this->item_table . '.id',
              $this->item_table . '.name AS name',
              $this->item_table . '.comment'
           ],
           'DISTINCT'        => true,
           'FROM'            => $this->item_table,
           'INNER JOIN'      => [
              $model_table         => [
                 'ON' => [
                    $this->item_table => 'id',
                    $model_table      => $model_field
                 ]
              ]
           ],
           'LEFT JOIN'       => [
              'glpi_manufacturers' => [
                 'ON' => [
                    'glpi_manufacturers' => 'id',
                    $model_table         => 'manufacturers_id'
                 ]
              ]
           ]
        ];

        if ($offset) {
            $criteria['START'] = (int)$offset;
        }

        $request = $this::getAdapter()->request($criteria)->fetchAllAssociative();
        $nb      = count($request) + $offset;
        $i       = $offset;

        if ($nb > $offset) {
            // Step to refresh progressbar
            $step    = (($nb > 20) ? floor($nb / 20) : 1);
            $tocheck = [];

            foreach ($request as $data) {
                if (!($i % $step)) {
                    if (isCommandLine()) {
                        printf(__('Replay rules on existing database: %1$s/%2$s') . "\r", $i, $nb);
                    } else {
                        Html::changeProgressBarPosition($i, $nb, "$i / $nb");
                    }
                }

                // Model case
                if (isset($data["manufacturer"])) {
                    $data["manufacturer"] = Manufacturer::processName(addslashes($data["manufacturer"]));
                }

                //Replay Type dictionnary
                $ID = Dropdown::importExternal(
                    getItemTypeForTable($this->item_table),
                    addslashes($data["name"]),
                    -1,
                    $data,
                    addslashes($data["comment"])
                );

                if ($data['id'] != $ID) {
                    $tocheck[$data["id"]][] = $ID;
                    $where = [
                       $model_field => $data['id']
                    ];

                    if (empty($data['idmanu'])) {
                        $where['OR'] = [
                           ['manufacturers_id'  => null],
                           ['manufacturers_id'  => 0]
                        ];
                    } else {
                        $where['manufacturers_id'] = $data['idmanu'];
                    }
                    $DB->update(
                        $model_table,
                        [$model_field => $ID],
                        $where
                    );
                }

                $i++;
                if ($maxtime) {
                    $crt = explode(" ", microtime());
                    if (($crt[0] + $crt[1]) > $maxtime) {
                        break;
                    }
                }
            }

            foreach ($tocheck as $ID => $tab) {
                $result = $this::getAdapter()->request([
                   'COUNT'  => 'cpt',
                   'FROM'   => $model_table,
                   'WHERE'  => [$model_field => $ID]
                ])->fetchAssociative();

                $deletecartmodel  = false;

                // No item left : delete old item
                if (
                    $result
                    && ($result['cpt'] == 0)
                ) {
                    $DB->delete(
                        $this->item_table,
                        [
                          'id'  => $ID
                        ]
                    );
                    $deletecartmodel  = true;
                }

                // Manage cartridge assoc Update items
                if ($this->getRuleClassName() == 'RuleDictionnaryPrinterModel') {
                    $request2 = $this::getAdapter()->request([
                       'FROM'   => 'glpi_cartridgeitems_printermodels',
                       'WHERE'  => ['printermodels_id' => $ID]
                    ])->fetchAllAssociative();

                    if (count($request2)) {
                        // Get compatible cartridge type
                        $carttype = [];
                        foreach ($request2 as $data) {
                            $carttype[] = $data['cartridgeitems_id'];
                        }
                        // Delete cartrodges_assoc
                        if ($deletecartmodel) {
                            $DB->delete(
                                'glpi_cartridgeitems_printermodels',
                                [
                                  'printermodels_id'   => $ID
                                ]
                            );
                        }
                        // Add new assoc
                        $ct = new CartridgeItem();
                        foreach ($carttype as $cartID) {
                            foreach ($tab as $model) {
                                $ct->addCompatibleType($cartID, $model);
                            }
                        }
                    }
                }
            } // each tocheck
        }

        if (isCommandLine()) {
            printf(__('Replay rules on existing database ended on %s') . "\n", date("r"));
        } else {
            Html::changeProgressBarPosition($i, $nb, "$i / $nb");
        }
        return ($i == $nb ? -1 : $i);
    }
}
