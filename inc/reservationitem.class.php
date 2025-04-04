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

use itsmng\Timezone;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * ReservationItem Class
**/
class ReservationItem extends CommonDBChild
{
    /// From CommonDBChild
    public static $itemtype          = 'itemtype';
    public static $items_id          = 'items_id';

    public static $checkParentRights = self::HAVE_VIEW_RIGHT_ON_ITEM;

    public static $rightname                = 'reservation';

    public const RESERVEANITEM              = 1024;

    public $get_item_to_display_tab = false;
    public $showdebug               = false;


    /**
     * @since 0.85
    **/
    public static function canView()
    {
        return Session::haveRightsOr(self::$rightname, [READ, self::RESERVEANITEM]);
    }


    public static function getTypeName($nb = 0)
    {
        return _n('Reservable item', 'Reservable items', $nb);
    }


    /**
     * @see CommonGLPI::getMenuName()
     *
     * @since 0.85
    **/
    public static function getMenuName()
    {
        return Reservation::getTypeName(Session::getPluralNumber());
    }


    /**
     * @see CommonGLPI::getForbiddenActionsForMenu()
     *
     * @since 0.85
    **/
    public static function getForbiddenActionsForMenu()
    {
        return ['add'];
    }


    /**
     * @see CommonGLPI::getAdditionalMenuLinks()
     *
     * @since 0.85
    **/
    public static function getAdditionalMenuLinks()
    {

        if (static::canView()) {
            return ['showall' => Reservation::getSearchURL(false)];
        }
        return false;
    }


    // From CommonDBTM
    /**
     * Retrieve an item from the database for a specific item
     *
     * @param $itemtype   type of the item
     * @param $ID         ID of the item
     *
     * @return true if succeed else false
    **/
    public function getFromDBbyItem($itemtype, $ID)
    {

        return $this->getFromDBByCrit([
        'itemtype'  => $itemtype,
        'items_id'  => $ID
        ]);
    }


    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
              Reservation::class,
            ]
        );

        // Alert does not extends CommonDBConnexity
        $alert = new Alert();
        $alert->cleanDBonItemDelete($this->getType(), $this->fields['id']);
    }


    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
           'id'                 => '4',
           'table'              => $this->getTable(),
           'field'              => 'comment',
           'name'               => __('Comments'),
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '5',
           'table'              => $this->getTable(),
           'field'              => 'is_active',
           'name'               => __('Active'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => 'common',
           'name'               => __('Characteristics')
        ];

        $tab[] = [
           'id'                 => '1',
           'table'              => 'reservation_types',
           'field'              => 'name',
           'name'               => __('Name'),
           'datatype'           => 'itemlink',
           'massiveaction'      => false,
           'addobjectparams'    => [
              'forcetab'           => 'Reservation$1'
           ]
        ];

        $tab[] = [
           'id'                 => '2',
           'table'              => 'reservation_types',
           'field'              => 'id',
           'name'               => __('ID'),
           'massiveaction'      => false,
           'datatype'           => 'number'
        ];

        $tab[] = [
           'id'                 => '9',
           'table'              => $this->getTable(),
           'field'              => '_virtual',
           'name'               => __('Planning'),
           'datatype'           => 'specific',
           'massiveaction'      => false,
           'nosearch'           => true,
           'nosort'             => true,
           'additionalfields'   => ['is_active']
        ];

        $loc = Location::rawSearchOptionsToAdd();
        // Force massive actions to false
        foreach ($loc as &$val) {
            $val['massiveaction'] = false;
        }
        $tab = array_merge($tab, $loc);

        $tab[] = [
           'id'                 => '6',
           'table'              => 'reservation_types',
           'field'              => 'otherserial',
           'name'               => __('Inventory number'),
           'datatype'           => 'string'
        ];

        $tab[] = [
           'id'                 => '16',
           'table'              => 'reservation_types',
           'field'              => 'comment',
           'name'               => __('Comments'),
           'datatype'           => 'text',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '70',
           'table'              => 'glpi_users',
           'field'              => 'name',
           'name'               => User::getTypeName(1),
           'datatype'           => 'dropdown',
           'right'              => 'all',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '71',
           'table'              => 'glpi_groups',
           'field'              => 'completename',
           'name'               => Group::getTypeName(1),
           'datatype'           => 'dropdown',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '19',
           'table'              => 'reservation_types',
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
           'datatype'           => 'dropdown',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '24',
           'table'              => 'glpi_users',
           'field'              => 'name',
           'linkfield'          => 'tech_users_id',
           'name'               => __('Technician in charge of the hardware'),
           'datatype'           => 'dropdown',
           'right'              => 'interface',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '80',
           'table'              => 'glpi_entities',
           'field'              => 'completename',
           'name'               => Entity::getTypeName(1),
           'massiveaction'      => false,
           'datatype'           => 'dropdown'
        ];

        return $tab;
    }


    /**
     * @param $item   CommonDBTM object
    **/
    public static function showActivationFormForItem(CommonDBTM $item)
    {

        if (!self::canUpdate()) {
            return false;
        }
        if ($item->getID()) {
            // Recursive type case => need entity right
            if ($item->isRecursive()) {
                if (!Session::haveAccessToEntity($item->fields["entities_id"])) {
                    return false;
                }
            }
        } else {
            return false;
        }

        $ri = new self();

        echo "<div>";
        echo "<table class='tab_cadre_fixe' aria-label='Reserve an item'>";
        echo "<tr><th colspan='2'>" . __('Reserve an item') . "</th></tr>";
        echo "<tr class='tab_bg_1'>";
        if ($ri->getFromDBbyItem($item->getType(), $item->getID())) {
            echo "<td class='center'>";
            //Switch reservation state

            if ($ri->fields["is_active"]) {
                Html::showSimpleForm(
                    static::getFormURL(),
                    'update',
                    __('Make unavailable'),
                    ['id'        => $ri->fields['id'],
                                           'is_active' => 0]
                );
            } else {
                Html::showSimpleForm(
                    static::getFormURL(),
                    'update',
                    __('Make available'),
                    ['id'        => $ri->fields['id'],
                                           'is_active' => 1]
                );
            }

            echo '</td><td>';
            Html::showSimpleForm(
                static::getFormURL(),
                'purge',
                __('Prohibit reservations'),
                ['id' => $ri->fields['id']],
                '',
                '',
                [__('Are you sure you want to return this non-reservable item?'),
                                       __('That will remove all the reservations in progress.')]
            );

            echo "</td>";
        } else {
            echo "<td class='center'>";
            Html::showSimpleForm(
                static::getFormURL(),
                'add',
                __('Authorize reservations'),
                ['items_id'     => $item->getID(),
                                       'itemtype'     => $item->getType(),
                                       'entities_id'  => $item->getEntityID(),
                                       'is_recursive' => $item->isRecursive(),]
            );
            echo "</td>";
        }
        echo "</tr></table>";
        echo "</div>";
    }


    public function showForm($ID, $options = [])
    {

        if (!self::canView()) {
            return false;
        }

        $r = new self();

        if ($r->getFromDB($ID)) {
            $type = $r->fields["itemtype"];
            $name = NOT_AVAILABLE;
            if ($item = getItemForItemtype($r->fields["itemtype"])) {
                $type = $item->getTypeName();
                if ($item->getFromDB($r->fields["items_id"])) {
                    $name = $item->getName();
                }
            }

            echo "<div class='center'><form aria-label='Modify Comment' method='post' name=form action='" . $this->getFormURL() . "'>";
            echo "<input type='hidden' name='id' value='$ID'>";
            echo "<table class='tab_cadre' aria-label='Modify the comment'>";
            echo "<tr><th colspan='2'>" . __s('Modify the comment') . "</th></tr>";

            // Ajouter le nom du materiel
            echo "<tr class='tab_bg_1'><td>" . _n('Item', 'Items', 1) . "</td>";
            echo "<td class='b'>" . sprintf(__('%1$s - %2$s'), $type, $name) . "</td></tr>\n";

            echo "<tr class='tab_bg_1'><td>" . __('Comments') . "</td>";
            echo "<td><textarea name='comment' cols='30' rows='10' >" . $r->fields["comment"];
            echo "</textarea></td></tr>\n";

            echo "<tr class='tab_bg_2'><td colspan='2' class='top center'>";
            echo "<input type='submit' name='update' value=\"" . _sx('button', 'Save') . "\" class='btn btn-secondary'>";
            echo "</td></tr>\n";

            echo "</table>";
            Html::closeForm();
            echo "</div>";
            return true;
        }
        return false;
    }


    public static function showListSimple()
    {
        global $DB, $CFG_GLPI;

        if (!Session::haveRight(self::$rightname, self::RESERVEANITEM)) {
            return false;
        }

        $ok         = false;
        $showentity = Session::isMultiEntitiesMode();
        $values     = [];

        if (isset($_SESSION['glpi_saved']['ReservationItem'])) {
            $_POST = $_SESSION['glpi_saved']['ReservationItem'];
        }

        if (isset($_POST['reserve'])) {
            echo "<div id='viewresasearch'  class='center'>";
            Toolbox::manageBeginAndEndPlanDates($_POST['reserve']);
            echo "<div id='nosearch' class='center firstbloc'>" .
                 "<a href=\"" . $CFG_GLPI['root_doc'] . "/front/reservationitem.php\">";
            echo __('See all reservable items') . "</a></div>\n";
        } else {
            echo "<div id='makesearch' class='center firstbloc'>" .
                 "<a class='pointer' onClick=\"javascript:showHideDiv('viewresasearch','','','');" .
                   "showHideDiv('makesearch','','','')\">";
            echo __('Find a free item in a specific period') . "</a></div>\n";

            echo "<div id='viewresasearch' style=\"display:none;\" class='center'>";
            $begin_time                 = time();
            $begin_time                -= ($begin_time % HOUR_TIMESTAMP);
            $_POST['reserve']["begin"]  = date("Y-m-d H:i:s", $begin_time);
            $_POST['reserve']["end"]    = date("Y-m-d H:i:s", $begin_time + HOUR_TIMESTAMP);
            $_POST['reservation_types'] = '';
        }

        $request = self::getAdapter()->request([
           'SELECT'          => 'itemtype',
           'DISTINCT'        => true,
           'FROM'            => 'glpi_reservationitems',
           'WHERE'           => [
              'is_active' => 1
           ] + getEntitiesRestrictCriteria('glpi_reservationitems', 'entities_id', $_SESSION['glpiactiveentities'])
        ]);

        while ($data = $request->fetchAssociative()) {
            $values[$data['itemtype']] = $data['itemtype']::getTypeName();
        }

        $request = self::getAdapter()->request([
           'SELECT'    => [
              'glpi_peripheraltypes.name',
              'glpi_peripheraltypes.id'
           ],
           'FROM'      => 'glpi_peripheraltypes',
           'LEFT JOIN' => [
              'glpi_peripherals'      => [
                 'ON' => [
                    'glpi_peripheraltypes'  => 'id',
                    'glpi_peripherals'      => 'peripheraltypes_id'
                 ]
              ],
              'glpi_reservationitems' => [
                 'ON' => [
                    'glpi_reservationitems' => 'items_id',
                    'glpi_peripherals'      => 'id'
                 ]
              ]
           ],
           'WHERE'     => [
              'itemtype'           => 'Peripheral',
              'is_active'          => 1,
              'peripheraltypes_id' => ['>', 0]
           ] + getEntitiesRestrictCriteria('glpi_reservationitems', 'entities_id', $_SESSION['glpiactiveentities']),
           'ORDERBY'   => 'glpi_peripheraltypes.name'
        ]);

        while ($ptype = $request->fetchAssociative()) {
            $id = $ptype['id'];
            $values["Peripheral#$id"] = $ptype['name'];
        }

        $form = [
           'action'      => Toolbox::getItemTypeSearchURL(__CLASS__),
           'buttons'     => [
              'submit' => [
                 'name'  => 'submit',
                 'value' => __('Search'),
                 'class' => 'btn btn-secondary',
              ]
           ],
           'content' => [
              __('Find a free item in a specific period') => [
                  'visible' => true,
                  'inputs' => [
                      __('Start date') => [
                          'type'        => 'datetime-local',
                          'name'        => 'reserve[begin]',
                          'value'       => $_POST['reserve']["begin"],
                          'required'    => true,
                          'col_lg'     => 6,
                      ],
                      __('Duration') => [
                          'type'        => 'select',
                          'name'        => 'reserve[_duration]',
                          'values'      => [__('Specify an end date')] + Timezone::GetTimeStamp([
                              'min'        => 0,
                              'max'        => 48 * HOUR_TIMESTAMP,
                          ]),
                          'value'       => $_POST['reserve']["_duration"] ?? 3600,
                          'required'    => true,
                          'hooks'       => [
                              'change' => <<<JS
                                const value = this.value;
                                const endDatetime = $('#endDatetime');
                                if (value > 0) {
                                    endDatetime.prop('disabled', true);
                                } else {
                                    endDatetime.prop('disabled', false);
                                }
                            JS
                          ],
                          'col_lg'     => 6,
                      ],
                      __('End date') => [
                          'type'        => 'datetime-local',
                          'id'          => 'endDatetime',
                          'name'        => 'reserve[end]',
                          'value'       => $_POST['reserve']["end"],
                          'disabled'     => true,
                          'col_lg'     => 6,
                      ],
                      __('Item type') => [
                          'type'        => 'select',
                          'name'        => 'reservation_types',
                          'values'      => $values,
                          'value'       => $_POST['reservation_types'],
                          'required'    => true,
                          'col_lg'     => 6,
                      ],
                  ]
              ],
           ],
        ];
        renderTwigForm($form);
        echo "</div>";

        // GET method passed to form creation
        echo "<div id='nosearch' class='center'>";
        echo "<form aria-label='Resvervation' name='form' method='GET' action='" . Reservation::getFormURL() . "'>";
        echo "<table class='tab_cadre_fixehov' aria-label='Reservation table'>";
        echo "<tr><th colspan='" . ($showentity ? "5" : "4") . "'>" . self::getTypeName(1) . "</th></tr>\n";

        foreach ($CFG_GLPI["reservation_types"] as $itemtype) {
            if (!($item = getItemForItemtype($itemtype))) {
                continue;
            }
            $itemtable = getTableForItemType($itemtype);
            $itemname  = $item->getNameField();

            $otherserial = new \QueryExpression($DB->quote('') . ' AS ' . $DB->quoteName('otherserial'));
            if ($item->isField('otherserial')) {
                $otherserial = "$itemtable.otherserial AS otherserial";
            }
            $criteria = [
               'SELECT' => [
                  'glpi_reservationitems.id',
                  'glpi_reservationitems.comment',
                  "$itemtable.$itemname AS name",
                  "$itemtable.entities_id AS entities_id",
                  $otherserial,
                  'glpi_locations.id AS location',
                  'glpi_reservationitems.items_id AS items_id'
               ],
               'FROM'   => self::getTable(),
               'INNER JOIN'   => [
                  $itemtable  => [
                     'ON'  => [
                        'glpi_reservationitems' => 'items_id',
                        $itemtable              => 'id', [
                           'AND' => [
                              'glpi_reservationitems.itemtype' => $itemtype
                           ]
                        ]
                     ]
                  ]
               ],
               'LEFT JOIN'    =>  [
                  'glpi_locations'  => [
                     'ON'  => [
                        $itemtable        => 'locations_id',
                        'glpi_locations'  => 'id'
                     ]
                  ]
               ],
               'WHERE'        => [
                  'glpi_reservationitems.is_active'   => 1,
                  'glpi_reservationitems.is_deleted'  => 0,
                  "$itemtable.is_deleted"             => 0,
               ] + getEntitiesRestrictCriteria($itemtable, '', $_SESSION['glpiactiveentities'], $item->maybeRecursive()),
               'ORDERBY'      => [
                  "$itemtable.entities_id",
                  "$itemtable.$itemname"
               ]
            ];

            $begin = $_POST['reserve']["begin"];
            $end   = $_POST['reserve']["end"];
            if (isset($_POST['submit']) && isset($begin) && isset($end)) {
                $criteria['LEFT JOIN']['glpi_reservations'] = [
                   'ON'  => [
                      'glpi_reservationitems' => 'id',
                      'glpi_reservations'     => 'reservationitems_id', [
                         'AND' => [
                            'glpi_reservations.end'    => ['>=', $begin],
                            'glpi_reservations.begin'  => ['<=', $end]
                         ]
                      ]
                   ]
                ];
                $criteria['WHERE'][] = ['glpi_reservations.id' => null];
            }
            if (isset($_POST["reservation_types"]) && !empty($_POST["reservation_types"])) {
                $tmp = explode('#', $_POST["reservation_types"]);
                $criteria['WHERE'][] = ['glpi_reservationitems.itemtype' => $tmp[0]];
                if (
                    isset($tmp[1]) && ($tmp[0] == 'Peripheral')
                    && ($itemtype == 'Peripheral')
                ) {
                    $criteria['LEFT JOIN']['glpi_peripheraltypes'] = [
                       'ON' => [
                          'glpi_peripherals'      => 'peripheraltypes_id',
                          'glpi_peripheraltypes'  => 'id'
                       ]
                    ];
                    $criteria['WHERE'][] = ["$itemtable.peripheraltypes_id" => $tmp[1]];
                }
            }

            $request = self::getAdapter()->request($criteria);
            while ($row = $request->fetchAssociative()) {
                echo "<tr class='tab_bg_2'><td>";
                echo "<input type='checkbox' name='item[" . $row["id"] . "]' value='" . $row["id"] . "'>" .
                      "</td>";
                $typename = $item->getTypeName();
                if ($itemtype == 'Peripheral') {
                    $item->getFromDB($row['items_id']);
                    if (
                        isset($item->fields["peripheraltypes_id"])
                          && ($item->fields["peripheraltypes_id"] != 0)
                    ) {
                        $typename = Dropdown::getDropdownName(
                            "glpi_peripheraltypes",
                            $item->fields["peripheraltypes_id"]
                        );
                    }
                }
                echo "<td><a href='reservation.php?reservationitems_id=" . $row['id'] . "'>" .
                            sprintf(__('%1$s - %2$s'), $typename, $row["name"]) . "</a></td>";
                echo "<td>" . Dropdown::getDropdownName("glpi_locations", $row["location"]) . "</td>";
                echo "<td>" . nl2br($row["comment"] ?? '') . "</td>";
                if ($showentity) {
                    echo "<td>" . Dropdown::getDropdownName("glpi_entities", $row["entities_id"]) .
                          "</td>";
                }
                echo "</tr>\n";
                $ok = true;
            }
        }
        if ($ok) {
            echo "<tr class='tab_bg_1 center'><td colspan='" . ($showentity ? "5" : "4") . "'>";
            if (isset($_POST['reserve'])) {
                echo Html::hidden('begin', ['value' => $_POST['reserve']["begin"]]);
                echo Html::hidden('end', ['value'   => $_POST['reserve']["end"]]);
            }
            echo "<input type='submit' value=\"" . _sx('button', 'Add') . "\" class='btn btn-secondary'></td></tr>\n";
        }
        echo "</table>\n";
        echo "<input type='hidden' name='id' value=''>";
        echo "</form>";// No CSRF token needed
        echo "</div>\n";
    }


    /**
     * @param $name
     *
     * @return array
    **/
    public static function cronInfo($name)
    {
        return ['description' => __('Alerts on reservations')];
    }


    /**
     * Cron action on reservation : alert on end of reservations
     *
     * @param $task to log, if NULL use display (default NULL)
     *
     * @return 0 : nothing to do 1 : done with success
    **/
    public static function cronReservation($task = null)
    {
        global $DB, $CFG_GLPI;

        if (!$CFG_GLPI["use_notifications"]) {
            return 0;
        }

        $message        = [];
        $cron_status    = 0;
        $items_infos    = [];
        $items_messages = [];

        foreach (Entity::getEntitiesToNotify('use_reservations_alert') as $entity => $value) {
            $secs = $value * HOUR_TIMESTAMP;

            // Reservation already begin and reservation ended in $value hours
            $criteria = [
               'SELECT' => [
                  'glpi_reservationitems.*',
                  'glpi_reservations.end AS end',
                  'glpi_reservations.id AS resaid'
               ],
               'FROM'   => 'glpi_reservations',
               'LEFT JOIN' => [
                  'glpi_alerts'  => [
                     'ON'  => [
                        'glpi_reservations'  => 'id',
                        'glpi_alerts'        => 'items_id', [
                           'AND' => [
                              'glpi_alerts.itemtype'  => 'Reservation',
                              'glpi_alerts.type'      => Alert::END
                           ]
                        ]
                     ]
                  ],
                  'glpi_reservationitems' => [
                     'ON'  => [
                        'glpi_reservations'     => 'reservationitems_id',
                        'glpi_reservationitems' => 'id'
                     ]
                  ]
               ],
               'WHERE'     => [
                  'glpi_reservationitems.entities_id' => $entity,
                  new QueryExpression('(UNIX_TIMESTAMP(' . $DB->quoteName('glpi_reservations.end') . ') - ' . $secs . ') < UNIX_TIMESTAMP()'),
                  'glpi_reservations.begin'  => ['<', new \QueryExpression('NOW()')],
                  'glpi_alerts.date'         => null
               ]
            ];
            $request = self::getAdapter()->request($criteria);

            while ($data = $request->fetchAssociative()) {
                if ($item_resa = getItemForItemtype($data['itemtype'])) {
                    if ($item_resa->getFromDB($data["items_id"])) {
                        $data['item_name']                     = $item_resa->getName();
                        $data['entity']                        = $entity;
                        $items_infos[$entity][$data['resaid']] = $data;

                        if (!isset($items_messages[$entity])) {
                            $items_messages[$entity] = __('Device reservations expiring today') . "<br>";
                        }
                        $items_messages[$entity] .= sprintf(
                            __('%1$s - %2$s'),
                            $item_resa->getTypeName(),
                            $item_resa->getName()
                        ) . "<br>";
                    }
                }
            }
        }

        foreach ($items_infos as $entity => $items) {
            $resitem = new self();
            if (
                NotificationEvent::raiseEvent(
                    "alert",
                    new Reservation(),
                    ['entities_id' => $entity,
                                                    'items'       => $items]
                )
            ) {
                $message     = $items_messages[$entity];
                $cron_status = 1;
                if ($task) {
                    $task->addVolume(1);
                    $task->log(sprintf(
                        __('%1$s: %2$s') . "\n",
                        Dropdown::getDropdownName("glpi_entities", $entity),
                        $message
                    ));
                } else {
                    //TRANS: %1$s is a name, %2$s is text of message
                    Session::addMessageAfterRedirect(sprintf(
                        __('%1$s: %2$s'),
                        Dropdown::getDropdownName(
                            "glpi_entities",
                            $entity
                        ),
                        $message
                    ));
                }

                $alert             = new Alert();
                $input["itemtype"] = 'Reservation';
                $input["type"]     = Alert::END;
                foreach ($items as $resaid => $item) {
                    $input["items_id"] = $resaid;
                    $alert->add($input);
                    unset($alert->fields['id']);
                }
            } else {
                $entityname = Dropdown::getDropdownName('glpi_entities', $entity);
                //TRANS: %s is entity name
                $msg = sprintf(__('%1$s: %2$s'), $entityname, __('Send reservation alert failed'));
                if ($task) {
                    $task->log($msg);
                } else {
                    Session::addMessageAfterRedirect($msg, false, ERROR);
                }
            }
        }
        return $cron_status;
    }


    /**
     * Display debug information for reservation of current object
    **/
    public function showDebugResa()
    {

        $resa                                = new Reservation();
        $resa->fields['id']                  = '1';
        $resa->fields['reservationitems_id'] = $this->getField('id');
        $resa->fields['begin']               = $_SESSION['glpi_currenttime'];
        $resa->fields['end']                 = $_SESSION['glpi_currenttime'];
        $resa->fields['users_id']            = Session::getLoginUserID();
        $resa->fields['comment']             = '';

        NotificationEvent::debugEvent($resa);
    }


    /**
     * @since 0.85
     *
     * @see commonDBTM::getRights()
    **/
    public function getRights($interface = 'central')
    {

        if ($interface == 'central') {
            $values = parent::getRights();
        }
        $values[self::RESERVEANITEM] = __('Make a reservation');

        return $values;
    }


    /**
     * @see CommonGLPI::defineTabs()
     *
     * @since 0.85
    **/
    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addStandardTab(__CLASS__, $ong, $options);
        $ong['no_all_tab'] = true;
        return $ong;
    }


    /**
     * @see CommonGLPI::getTabNameForItem()
     *
     * @since 0.85
    **/
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if ($item->getType() == __CLASS__) {
            if (Session::haveRight("reservation", ReservationItem::RESERVEANITEM)) {
                $tabs[1] = Reservation::getTypeName(1);
            }
            if (
                (Session::getCurrentInterface() == "central")
                && Session::haveRight("reservation", READ)
            ) {
                $tabs[2] = __('Administration');
            }
            return $tabs;
        }
        return '';
    }

    /**
     * @param $item         CommonGLPI object
     * @param $tabnum       (default1)
     * @param $withtemplate (default0)
     **/
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == __CLASS__) {
            switch ($tabnum) {
                case 1:
                    $item->showListSimple();
                    break;

                case 2:
                    Search::show('ReservationItem');
                    break;
            }
        }
        return true;
    }

    /**
     * @see CommonDBTM::isNewItem()
     *
     * @since 0.85
    **/
    public function isNewItem()
    {
        return false;
    }


    public static function getIcon()
    {
        return Reservation::getIcon();
    }
}
