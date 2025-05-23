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

class RuleDictionnarySoftwareCollection extends RuleCollection
{
    // From RuleCollection

    public $stop_on_first_match = true;
    public $can_replay_rules    = true;
    public $menu_type           = 'dictionnary';
    public $menu_option         = 'software';

    public static $rightname           = 'rule_dictionnary_software';

    /**
     * @see RuleCollection::getTitle()
    **/
    public function getTitle()
    {
        //TRANS: software in plural
        return __('Dictionnary of software');
    }


    /**
     * @see RuleCollection::cleanTestOutputCriterias()
    **/
    public function cleanTestOutputCriterias(array $output)
    {

        //If output array contains keys begining with _ : drop it
        foreach ($output as $criteria => $value) {
            if (($criteria[0] == '_') && ($criteria != '_ignore_import')) {
                unset($output[$criteria]);
            }
        }
        return $output;
    }


    /**
     * @see RuleCollection::warningBeforeReplayRulesOnExistingDB()
    **/
    public function warningBeforeReplayRulesOnExistingDB($target)
    {
        global $CFG_GLPI;

        echo "<form aria-label='Soft Dictionnary Confirmation' name='testrule_form' id='softdictionnary_confirmation' method='post' action=\"" .
               $target . "\">\n";
        echo "<div class='center'>";
        echo "<table class='tab_cadre_fixe' aria-label='Warning'>";
        echo "<tr><th colspan='2' class='b'>" .
              __('Warning before running rename based on the dictionary rules') . "</th></tr>\n";
        echo "<tr><td class='tab_bg_2 center'>";
        echo "<img src=\"" . $CFG_GLPI["root_doc"] . "/pics/warning.png\"></td>";
        echo "<td class='tab_bg_2 center'>" .
              __('Warning! This operation can put merged software in the trashbin.<br>Sure to notify your users.') .
             "</td></tr>\n";
        echo "<tr><th colspan='2' class='b'>" . __('Manufacturer choice') . "</th></tr>\n";
        echo "<tr><td class='tab_bg_2 center'>" .
              __('Replay dictionary rules for manufacturers (----- = All)') . "</td>";
        echo "<td class='tab_bg_2 center'>";
        Manufacturer::dropdown(['name' => 'manufacturer']);
        echo "</td></tr>\n";

        echo "<tr><td class='tab_bg_2 center' colspan='2'>";
        echo "<input type='submit' name='replay_rule' value=\"" . _sx('button', 'Post') . "\"
             class='submit'>";
        echo "<input type='hidden' name='replay_confirm' value='replay_confirm'>";
        echo "</td></tr>";
        echo "</table>\n";
        echo "</div>\n";
        Html::closeForm();
        return true;
    }


    /**
     * @see RuleCollection::replayRulesOnExistingDB()
    **/
    public function replayRulesOnExistingDB($offset = 0, $maxtime = 0, $items = [], $params = [])
    {
        if (isCommandLine()) {
            echo "replayRulesOnExistingDB started : " . date("r") . "\n";
        }
        $nb = 0;
        $i  = $offset;

        if (count($items) == 0) {
            //Select all the differents software
            $criteria = [
               'SELECT'          => [
                  'glpi_softwares.name',
                  'glpi_manufacturers.name AS manufacturer',
                  'glpi_softwares.manufacturers_id AS manufacturers_id',
                  'glpi_softwares.entities_id AS entities_id',
                  'glpi_softwares.is_helpdesk_visible AS helpdesk',
                  'glpi_softwares.softwarecategories_id AS softwarecategories_id',
               ],
               'DISTINCT'        => true,
               'FROM'            => 'glpi_softwares',
               'LEFT JOIN'       => [
                  'glpi_manufacturers' => [
                     'ON' => [
                        'glpi_manufacturers' => 'id',
                        'glpi_softwares'     => 'manufacturers_id'
                     ]
                  ]
               ],
               'WHERE'           => [
                  // Do not replay on trashbin and templates
                  'glpi_softwares.is_deleted'   => 0,
                  'glpi_softwares.is_template'  => 0
               ]
            ];

            if (isset($params['manufacturer']) && $params['manufacturer']) {
                $criteria['WHERE']['glpi_softwares.manufacturers_id'] = $params['manufacturer'];
            }
            if ($offset) {
                $criteria['START'] = (int)$offset;
            }

            $request = $this::getAdapter()->request($criteria)->fetchAllAssociative();
            $nb   = count($request) + $offset;
            $step = (($nb > 1000) ? 50 : (($nb > 20) ? floor(count($request) / 20) : 1));

            foreach ($request as $input) {
                if (!($i % $step)) {
                    if (isCommandLine()) {
                        printf(
                            __('%1$s - replay rules on existing database: %2$s/%3$s (%4$s Mio)') . "\n",
                            date("H:i:s"),
                            $i,
                            $nb,
                            round(memory_get_usage() / (1024 * 1024), 2)
                        );
                    } else {
                        Html::changeProgressBarPosition($i, $nb, "$i / $nb");
                    }
                }

                //If manufacturer is set, then first run the manufacturer's dictionnary
                if (isset($input["manufacturer"])) {
                    $input["manufacturer"] = Manufacturer::processName($input["manufacturer"]);
                }

                //Replay software dictionnary rules
                $res_rule = $this->processAllRules($input, [], []);

                if (
                    (isset($res_rule["name"]) && (strtolower($res_rule["name"]) != strtolower($input["name"])))
                    || (isset($res_rule["version"]) && ($res_rule["version"] != ''))
                    || (isset($res_rule['new_entities_id'])
                        && ($res_rule['new_entities_id'] != $input['entities_id']))
                    || (isset($res_rule['is_helpdesk_visible'])
                        && ($res_rule['is_helpdesk_visible'] != $input['helpdesk']))
                    || (isset($res_rule['manufacturer'])
                        && ($res_rule['manufacturer'] != $input['manufacturer']))
                    || (isset($res_rule['softwarecategories_id'])
                        && ($res_rule['softwarecategories_id'] != $input['softwarecategories_id']))
                ) {
                    $IDs = [];
                    //Find all the softwares in the database with the same name and manufacturer
                    $same_iterator = $this::getAdapter()->request([
                       'SELECT' => 'id',
                       'FROM'   => 'glpi_softwares',
                       'WHERE'  => [
                          'name'               => addslashes($input['name']),
                          'manufacturers_id'   => $input['manufacturers_id']
                       ]
                    ])->fetchAllAssociative();

                    if (count($same_iterator)) {
                        //Store all the software's IDs in an array
                        foreach ($same_iterator as $result) {
                            $IDs[] = $result["id"];
                        }
                        //Replay dictionnary on all the softwares
                        $this->replayDictionnaryOnSoftwaresByID($IDs, $res_rule);
                    }
                }
                $i++;
                if ($maxtime) {
                    $crt = explode(" ", microtime());
                    if (($crt[0] + $crt[1]) > $maxtime) {
                        break;
                    }
                }
            } // each distinct software

            if (isCommandLine()) {
                printf(__('Replay rules on existing database: %1$s/%2$s') . "   \n", $i, $nb);
            } else {
                Html::changeProgressBarPosition($i, $nb, "$i / $nb");
            }
        } else {
            $this->replayDictionnaryOnSoftwaresByID($items);
            return true;
        }

        if (isCommandLine()) {
            printf(__('Replay rules on existing database ended on %s') . "\n", date("r"));
        }

        return (($i == $nb) ? -1 : $i);
    }


    /**
     * Replay dictionnary on several softwares
     *
     * @param $IDs       array of software IDs to replay
     * @param $res_rule  array of rule results
     *
     * @return Query result handler
    **/
    public function replayDictionnaryOnSoftwaresByID(array $IDs, $res_rule = [])
    {
        $new_softs  = [];
        $delete_ids = [];

        foreach ($IDs as $ID) {
            $request = $this::getAdapter()->request([
               'SELECT'    => [
                  'gs.id',
                  'gs.name AS name',
                  'gs.entities_id AS entities_id',
                  'gm.name AS manufacturer'
               ],
               'FROM'      => 'glpi_softwares AS gs',
               'LEFT JOIN' => [
                  'glpi_manufacturers AS gm' => [
                     'ON' => [
                        'gs'  => 'manufacturers_id',
                        'gm'  => 'id'
                     ]
                  ]
               ],
               'WHERE'     => [
                  'gs.is_template'  => 0,
                  'gs.id'           => $ID
               ]
            ])->fetchAllAssociative();

            if (count($request)) {
                $soft = $request[0];
                //For each software
                $this->replayDictionnaryOnOneSoftware(
                    $new_softs,
                    $res_rule,
                    $ID,
                    (
                        isset($res_rule['new_entities_id'])
                    ? $res_rule['new_entities_id']
                    : $soft["entities_id"]
                    ),
                    $soft['name'] ?? '',
                    $soft['manufacturer'] ?? '',
                    $delete_ids
                );
            }
        }
        //Delete software if needed
        $this->putOldSoftsInTrash($delete_ids);
    }


    /**
     * Replay dictionnary on one software
     *
     * @param &$new_softs      array containing new softwares already computed
     * @param $res_rule        array of rule results
     * @param $ID                    ID of the software
     * @param $entity                working entity ID
     * @param $name                  softwrae name
     * @param $manufacturer          manufacturer name
     * @param &$soft_ids       array containing replay software need to be put in trashbin
    **/
    public function replayDictionnaryOnOneSoftware(
        array &$new_softs,
        array $res_rule,
        $ID,
        $entity,
        $name,
        $manufacturer,
        array &$soft_ids
    ) {
        $input["name"]         = $name;
        $input["manufacturer"] = $manufacturer;
        $input["entities_id"]  = $entity;

        if (empty($res_rule)) {
            $res_rule = $this->processAllRules($input, [], []);
        }
        $soft = new Software();
        if (isset($res_rules['_ignore_import']) && ($res_rules['_ignore_import'] == 1)) {
            $soft->putInTrash($ID, __('Software deleted by ITSM-NG dictionary rules'));
            return;
        }

        //Software's name has changed or entity
        if (
            (isset($res_rule["name"]) && (strtolower($res_rule["name"]) != strtolower($name)))
              //Entity has changed, and new entity is a parent of the current one
            || (!isset($res_rule["name"])
                && isset($res_rule['new_entities_id'])
                && in_array(
                    $res_rule['new_entities_id'],
                    getAncestorsOf('glpi_entities', $entity)
                ))
        ) {
            if (isset($res_rule["name"])) {
                $new_name = $res_rule["name"];
            } else {
                $new_name = addslashes($name);
            }

            if (isset($res_rule["manufacturer"]) && $res_rule["manufacturer"]) {
                $manufacturer = $res_rule["manufacturer"];
            } else {
                $manufacturer = addslashes($manufacturer);
            }

            //New software not already present in this entity
            if (!isset($new_softs[$entity][$new_name])) {
                // create new software or restore it from trashbin
                $new_software_id               = $soft->addOrRestoreFromTrash(
                    $new_name,
                    $manufacturer,
                    $entity,
                    '',
                    true
                );
                $new_softs[$entity][$new_name] = $new_software_id;
            } else {
                $new_software_id = $new_softs[$entity][$new_name];
            }
            // Move licenses to new software
            $this->moveLicenses($ID, $new_software_id);
        } else {
            $new_software_id = $ID;
            $res_rule["id"]  = $ID;
            if (isset($res_rule["manufacturer"]) && $res_rule["manufacturer"]) {
                $res_rule["manufacturers_id"] = Dropdown::importExternal(
                    'Manufacturer',
                    $res_rule["manufacturer"]
                );
                unset($res_rule["manufacturer"]);
            }
            $soft->update($res_rule);
        }

        // Add to software to deleted list
        if ($new_software_id != $ID) {
            $soft_ids[] = $ID;
        }

        //Get all the different versions for a software
        $request = $this::getAdapter()->request([
           'FROM'   => 'glpi_softwareversions',
           'WHERE'  => ['softwares_id' => $ID]
        ]);

        while ($version = $request->fetchAssociative()) {
            $input["version"] = addslashes($version["name"]);
            $old_version_name = $input["version"];

            if (isset($res_rule['version_append']) && $res_rule['version_append'] != '') {
                $new_version_name = $old_version_name . $res_rule['version_append'];
            } elseif (isset($res_rule["version"]) && $res_rule["version"] != '') {
                $new_version_name = $res_rule["version"];
            } else {
                $new_version_name = $version["name"];
            }
            if (
                ($ID != $new_software_id)
                || ($new_version_name != $old_version_name)
            ) {
                $this->moveVersions(
                    $ID,
                    $new_software_id,
                    $version["id"],
                    $old_version_name,
                    $new_version_name,
                    $entity
                );
            }
        }
    }


    /**
     * Delete a list of softwares
     *
     * @param $soft_ids array containing replay software need to be put in trashbin
    **/
    public function putOldSoftsInTrash(array $soft_ids)
    {
        if (count($soft_ids) > 0) {
            //Try to delete all the software that are not used anymore
            // (which means that don't have version associated anymore)
            $request = $this::getAdapter()->request([
               'SELECT'    => [
                  'glpi_softwares.id',
                  'COUNT' => 'glpi_softwareversions.softwares_id AS cpt'
               ],
               'FROM'      => 'glpi_softwares',
               'LEFT JOIN' => [
                  'glpi_softwareversions' => [
                     'ON' => [
                        'glpi_softwareversions' => 'softwares_id',
                        'glpi_softwares'        => 'id'
                     ]
                  ]
               ],
               'WHERE'     => [
                  'glpi_softwares.id'  => $soft_ids,
                  'is_deleted'         => 0
               ],
               'GROUPBY'   => 'glpi_softwares.id',
               'HAVING'    => ['cpt' => 0]
            ]);

            $software = new Software();
            while ($soft = $request->fetchAssociative()) {
                $software->putInTrash($soft["id"], __('Software deleted by ITSM-NG dictionary rules'));
            }
        }
    }


    /**
     * Change software's name, and move versions if needed
     *
     * @param int $ID                    old software ID
     * @param int $new_software_id       new software ID
     * @param int $version_id            version ID to move
     * @param string $old_version        old version name
     * @param string $new_version        new version name
     * @param int $entity                entity ID
     * @return void
    */
    public function moveVersions($ID, $new_software_id, $version_id, $old_version, $new_version, $entity)
    {
        $new_versionID = $this->versionExists($new_software_id, $new_version);

        // Do something if it is not the same version
        if ($new_versionID != $version_id) {
            //A version does not exist : update existing one
            if ($new_versionID == -1) {
                //Transfer versions from old software to new software for a specific version
                $softwareVersion = new SoftwareVersion();
                if ($softwareVersion->getFromDB($version_id)) {
                    $softwareVersion->update([
                        'id'           => $version_id,
                        'name'         => $new_version,
                        'softwares_id' => $new_software_id
                    ]);
                }
            } else {
                // Delete software can be in double after update
                $item_softwareversion_table = Item_SoftwareVersion::getTable();
                $request = $this::getAdapter()->request([
                   'SELECT'    => ['gcs_2.*'],
                   'FROM'      => $item_softwareversion_table,
                   'LEFT JOIN' => [
                      "{$item_softwareversion_table} AS gcs_2" => [
                         'FKEY'   => [
                            'gcs_2'                       => 'items_id',
                            $item_softwareversion_table   => 'items_id', [
                               'AND' => [
                                  'gcs_2.itemtype' => $item_softwareversion_table . '.itemtype'
                               ]
                            ]
                         ]
                      ]
                   ],
                   'WHERE'     => [
                      "{$item_softwareversion_table}.softwareversions_id"   => $new_versionID,
                      'gcs_2.softwareversions_id'                           => $version_id
                   ]
                ]);
                while ($data = $request->fetchAssociative()) {
                    $item_version = new Item_SoftwareVersion();
                    if ($item_version->getFromDB($data['id'])) {
                        $item_version->delete(['id' => $data['id']]);
                    }
                }

                //Change ID of the version in glpi_items_softwareversions
               $adapter = self::getAdapter();
                $items = $adapter->request([
                    'SELECT' => ['id'],
                    'FROM'   => $item_softwareversion_table,
                    'WHERE'  => [
                        'softwareversions_id' => $version_id
                    ]
                ]);
                
                foreach ($items->fetchAllAssociative() as $data) {
                    $item_version = new Item_SoftwareVersion();
                    if ($item_version->getFromDB($data['id'])) {
                        $item_version->update([
                            'id'                 => $data['id'],
                            'softwareversions_id' => $new_versionID
                        ]);
                    }
                }

                // Update licenses version link
                $adapter = self::getAdapter();
                $licenses_buy = $adapter->request([
                    'SELECT' => ['id'],
                    'FROM'   => 'glpi_softwarelicenses',
                    'WHERE'  => [
                        'softwareversions_id_buy' => $version_id
                    ]
                ]);
                
                foreach ($licenses_buy->fetchAllAssociative() as $data) {
                    $license = new SoftwareLicense();
                    if ($license->getFromDB($data['id'])) {
                        $license->update([
                            'id'                    => $data['id'],
                            'softwareversions_id_buy' => $new_versionID
                        ]);
                    }
                }

                $licenses_use = $adapter->request([
                    'SELECT' => ['id'],
                    'FROM'   => 'glpi_softwarelicenses',
                    'WHERE'  => [
                        'softwareversions_id_use' => $version_id
                    ]
                ]);
                
                foreach ($licenses_use->fetchAllAssociative() as $data) {
                    $license = new SoftwareLicense();
                    if ($license->getFromDB($data['id'])) {
                        $license->update([
                            'id'                    => $data['id'],
                            'softwareversions_id_use' => $new_versionID
                        ]);
                    }
                }

                //Delete old version
                $old_version = new SoftwareVersion();
                $old_version->delete(["id" => $version_id]);
            }
        }
    }


    /**
     * Move licenses from a software to another
     *
     * @param $old_software_id    old software ID
     * @param $new_software_id    new software ID
     * @return true if move was successful
    **/
    public function moveLicenses($old_software_id, $new_software_id)
    {
        global $DB;

        //Return false if one of the 2 softwares doesn't exists
        if (
            !countElementsInTable('glpi_softwares', ['id' => $old_software_id])
            || !countElementsInTable('glpi_softwares', ['id' => $new_software_id])
        ) {
            return false;
        }

        //Transfer licenses to new software if needed
        if ($old_software_id != $new_software_id) {
            $adapter = self::getAdapter();
        $licenses = $adapter->request([
            'SELECT' => ['id'],
            'FROM'   => 'glpi_softwarelicenses',
            'WHERE'  => [
                'softwares_id' => $old_software_id
            ]
        ]);
        
        foreach ($licenses->fetchAllAssociative() as $data) {
            $license = new SoftwareLicense();
            if ($license->getFromDB($data['id'])) {
                $license->update([
                    'id'           => $data['id'],
                    'softwares_id' => $new_software_id
                ]);
            }
        }
    }
        return true;
    }


    /**
     * Check if a version exists
     *
     * @param $software_id  software ID
     * @param $version      version name
    **/
    public function versionExists($software_id, $version)
    {
        //Check if the version exists
        $request = $this::getAdapter()->request([
           'FROM'   => 'glpi_softwareversions',
           'WHERE'  => [
              'softwares_id' => $software_id,
              'name'         => $version
           ]
        ])->fetchAllAssociative();
        if (count($request)) {
            $current = $request[0];
            return $current['id'];
        }
        return -1;
    }
}
