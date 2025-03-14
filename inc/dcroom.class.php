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
 * DCRoom Class
**/
class DCRoom extends CommonDBTM
{
    use Glpi\Features\DCBreadcrumb;

    // From CommonDBTM
    public $dohistory                   = true;
    protected $usenotepad               = true;
    public static $rightname                   = 'datacenter';

    public static function getTypeName($nb = 0)
    {
        //TRANS: Test of comment for translation (mark : //TRANS)
        return _n('Server room', 'Server rooms', $nb);
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $this
           ->addStandardTab('Rack', $ong, $options)
           ->addDefaultFormTab($ong)
           ->addImpactTab($ong, $options)
           ->addStandardTab('Infocom', $ong, $options)
           ->addStandardTab('Contract_Item', $ong, $options)
           ->addStandardTab('Document_Item', $ong, $options)
           ->addStandardTab('Link', $ong, $options)
           ->addStandardTab('Ticket', $ong, $options)
           ->addStandardTab('Item_Problem', $ong, $options)
           ->addStandardTab('Change_Item', $ong, $options)
           ->addStandardTab('Log', $ong, $options);
        return $ong;
    }

    public function showForm($ID, $options = [])
    {
        global $CFG_GLPI;
        $form = [
           'action' => self::getFormURL(),
           'itemtype' => $this::class,
           'content' => [
              $this->getTypeName() => [
                  'visible' => true,
                  'inputs' => [
                      $this->isNewID($ID) ? [] : [
                          'type' => 'hidden',
                          'name' => 'id',
                          'value' => $ID
                      ],
                      __('Name') => [
                          'type' => 'text',
                          'name' => 'name',
                          'value' => $this->fields["name"] ?? $options['name'] ?? '',
                          'size' => 50,
                          'max' => 255,
                          'comment' => false
                      ],
                      Location::getTypeName(1) => [
                          'type' => 'select',
                          'id' => 'dropdown_locations_id',
                          'name' => 'locations_id',
                          'value' => $this->fields["locations_id"] ?? $options['locations_id'] ?? 0,
                          'values' => getOptionForItems(Location::class),
                      ],
                      Datacenter::getTypeName(1) => [
                          'type' => 'select',
                          'id' => 'dropdown_datacenters_id',
                          'name' => 'datacenters_id',
                          'value' => $this->fields["datacenters_id"] ?? $options['datacenters_id'] ?? 0,
                          'values' => getOptionForItems(Datacenter::class),
                          'hooks' => [
                              'change' => <<<JS
                            var datacenter = $(this).val();
                            $.ajax({
                                url: "{$CFG_GLPI['root_doc']}/ajax/dropdownLocation.php",
                                data: {
                                    items_id: datacenter,
                                    itemtype: 'Datacenter'
                                },
                                success: function(data) {
                                    let jsonData = JSON.parse(data);
                                    for (let i = 0; i < jsonData.length; i++) {
                                        let option = new Option(jsonData[i].name, jsonData[i].id);
                                        $('#dropdown_locations_id').append(option);
                                    }
                                    if (jsonData.selected != undefined) {
                                        $('#dropdown_locations_id').val(jsonData.selected);
                                    }

                                }
                            });
                            JS
                          ]
                      ],
                      __('Number of columns') => [
                          'type' => 'number',
                          'name' => 'vis_cols',
                          'value' => $this->fields["vis_cols"] ?? $options['vis_cols'] ?? 1,
                          'min' => 1,
                          'max' => 100,
                          'step' => 1,
                          'col_lg' => 6,
                      ],
                      __('Number of rows') => [
                          'type' => 'number',
                          'name' => 'vis_rows',
                          'value' => $this->fields["vis_rows"] ?? $options['vis_rows'] ?? 1,
                          'min' => 1,
                          'max' => 100,
                          'step' => 1,
                          'col_lg' => 6,
                      ],
                      __('Background picture (blueprint)') => [
                          'type' => 'imageUpload',
                          'id' => 'blueprintImagePick',
                          'name' => 'blueprint',
                          'accept' => 'image/*',
                          'value' => $this->fields["blueprint"] ?? '',
                          'col_lg' => 12,
                          'col_md' => 12,
                      ]
                  ]
              ]
           ]
        ];
        renderTwigForm($form, '', $this->fields);
        return true;
    }

    public function prepareInputForAdd($input)
    {
        return $this->manageBlueprint($input);
    }

    public function prepareInputForUpdate($input)
    {
        return $this->manageBlueprint($input);
    }

    public function cleanDBonPurge()
    {
        Toolbox::deletePicture($this->fields['blueprint']);
    }

    /**
     * Add/remove blueprint picture
     * @param  array $input the form input
     * @return array        the altered input
     */
    public function manageBlueprint($input)
    {
        if (
            isset($input["clearPictureForblueprint"])
            && $input["clearPictureForblueprint"]
        ) {
            $input['blueprint'] = '';

            if (array_key_exists('blueprint', $this->fields)) {
                unlink($this->fields['blueprint']);
            }
        }

        if (isset($input["blueprint"])) {
            $file = json_decode(stripslashes($input["blueprint"]), true)[0];

            $path = ItsmngUploadHandler::uploadFile(
                $file['path'],
                $file['name'],
                ItsmngUploadHandler::PICTURE
            );
            if (!$path) {
                Session::addMessageAfterRedirect(__('Unable to save picture file.'), true, ERROR);
                return $input;
            }
            $input['blueprint'] = $path;

            if (array_key_exists('blueprint', $this->fields)) {
                unlink($this->fields['blueprint']);
            }
        }

        return $input;
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

        $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

        $tab[] = [
           'id'                 => '4',
           'table'              => Datacenter::getTable(),
           'field'              => 'name',
           'name'               => Datacenter::getTypeName(1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '5',
           'table'              => $this->getTable(),
           'field'              => 'vis_cols',
           'name'               => __('Number of columns'),
           'datatype'           => 'number'
        ];

        $tab[] = [
           'id'                 => '6',
           'table'              => $this->getTable(),
           'field'              => 'vis_rows',
           'name'               => __('Number of rows'),
           'datatype'           => 'number'
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
           'id'                 => '121',
           'table'              => $this->getTable(),
           'field'              => 'date_creation',
           'name'               => __('Creation date'),
           'datatype'           => 'datetime',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '80',
           'table'              => 'glpi_entities',
           'field'              => 'completename',
           'name'               => Entity::getTypeName(1),
           'datatype'           => 'dropdown'
        ];

        $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

        $tab = array_merge($tab, Datacenter::rawSearchOptionsToAdd(get_class($this)));

        return $tab;
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case Datacenter::getType():
                $nb = 0;
                if ($_SESSION['glpishow_count_on_tabs']) {
                    $nb = countElementsInTable(
                        self::getTable(),
                        [
                          'datacenters_id'  => $item->getID(),
                          'is_deleted'      => 0
                        ]
                    );
                }
                return self::createTabEntry(
                    self::getTypeName(Session::getPluralNumber()),
                    $nb
                );
                break;
        }
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showForDatacenter($item);
    }

    /**
     * Print datacenter's roms
     *
     * @param Datacenter $datacenter Datacenter object
     *
     * @return void|boolean (display) Returns false if there is a rights error.
    **/
    public static function showForDatacenter(Datacenter $datacenter)
    {
        global $DB;

        $ID = $datacenter->getID();
        $rand = mt_rand();

        if (
            !$datacenter->getFromDB($ID)
            || !$datacenter->can($ID, READ)
        ) {
            return false;
        }
        $canedit = $datacenter->canEdit($ID);

        // $rooms = $DB->request([
        //    'FROM'   => self::getTable(),
        //    'WHERE'  => [
        //       'datacenters_id' => $datacenter->getID()
        //    ]
        // ]);
        $dql = "SELECT r
        FROM Itsmng\\Domain\\Entities\\DcRoom r
        WHERE r.datacenter = :datacenter_id";

        $rooms = self::getAdapter()->request($dql, [
            'datacenter_id' => $datacenter->getID()
        ]);

        echo "<div class='firstbloc'>";
        Html::showSimpleForm(
            self::getFormURL(),
            '_add_fromitem',
            __('New room for this datacenter...'),
            ['datacenters_id' => $datacenter->getID()]
        );
        echo "</div>";

        if ($canedit) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = [
               'num_displayed'   => min($_SESSION['glpilist_limit'], count($rooms)),
               'container'       => 'mass' . __CLASS__ . $rand,
               'deprecated'      => true
            ];
            Html::showMassiveActions($massiveactionparams);
        }

        Session::initNavigateListItems(
            self::getType(),
            //TRANS : %1$s is the itemtype name,
            //        %2$s is the name of the item (used for headings of a list)
            sprintf(
                __('%1$s = %2$s'),
                $datacenter->getTypeName(1),
                $datacenter->getName()
            )
        );

        if (!count($rooms)) {
            echo "<table class='tab_cadre_fixe' aria-label='Server Rooms Table'><tr><th>" . __('No server room found') . "</th></tr>";
            echo "</table>";
        } else {
            echo "<table class='tab_cadre_fixehov' aria-label='Server Rooms Table'>";
            $header = "<tr>";
            if ($canedit) {
                $header .= "<th width='10'>";
                $header .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
                $header .= "</th>";
            }
            $header .= "<th>" . __('Name') . "</th>";
            $header .= "</tr>";

            $dcroom = new self();
            echo $header;
            // while ($room = $rooms->next()) {
            foreach($rooms as $room) {
                $dcroom->getFromResultSet($room);
                echo "<tr lass='tab_bg_1'>";
                if ($canedit) {
                    echo "<td>";
                    Html::showMassiveActionCheckBox(__CLASS__, $room["id"]);
                    echo "</td>";
                }
                echo "<td>" . $dcroom->getLink() . "</td>";
                echo "</tr>";
            }
            echo $header;
            echo "</table>";
        }

        if ($canedit && count($rooms)) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
        }
        if ($canedit) {
            Html::closeForm();
        }
    }

    /**
     * Get already filled places
     *
     * @param string $current Current position to exclude; defaults to null
     *
     * @return array [x => [pos_x], y => [pos_y]]]
     */
    public function getFilled($current = null)
    {
        global $DB;

        // $iterator = $DB->request([
        //    'FROM'   => Rack::getTable(),
        //    'WHERE'  => [
        //       'dcrooms_id'   => $this->getID(),
        //       'is_deleted'   => 0
        //    ]
        // ]);
        $dql = "SELECT r
        FROM Itsmng\\Domain\\Entities\\Rack r
        WHERE r.dcroom = :dcroom_id AND r.isDeleted = 0";

        $results = self::getAdapter()->request($dql, [
            'dcroom_id' => $this->getID()
        ]);

        $filled = [];
        // while ($rack = $iterator->next()) {
        foreach($results as $rack) {
            if (preg_match('/(\d+),\s?(\d+)/', $rack['position'])) {
                $position = $rack['position'];
                if (empty($current) || $current != $position) {
                    $filled[$position] = $position;
                }
            }
        }

        return $filled;
    }

    /**
     * Get all possible positions for current room
     *
     * @return array
     */
    public function getAllPositions()
    {
        $positions = [];
        for ($x = 1; $x < (int)$this->fields['vis_cols'] + 1; ++$x) {
            for ($y = 1; $y < (int)$this->fields['vis_rows'] + 1; ++$y) {
                $positions["$x,$y"] = sprintf(
                    __('col: %1$s, row: %2$s'),
                    Toolbox::getBijectiveIndex($x),
                    $y
                );
            }
        }
        return $positions;
    }
}
