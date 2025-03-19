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
 * @since 9.1
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class ComputerAntivirus extends CommonDBChild
{
    // From CommonDBChild
    public static $itemtype = 'Computer';
    public static $items_id = 'computers_id';
    public $dohistory       = true;



    public static function getTypeName($nb = 0)
    {
        return _n('Antivirus', 'Antiviruses', $nb);
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        // can exists for template
        if (
            ($item->getType() == 'Computer')
            && Computer::canView()
        ) {
            $nb = 0;
            if ($_SESSION['glpishow_count_on_tabs']) {
                $nb = countElementsInTable(
                    'glpi_computerantiviruses',
                    ["computers_id" => $item->getID(), 'is_deleted' => 0 ]
                );
            }
            return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        self::showForComputer($item, $withtemplate);
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
     * Duplicate all antirivuses from a computer template to his clone
     *
     * @deprecated 9.5
     *
     * @param $oldid
     * @param $newid
    **/
    public static function cloneComputer($oldid, $newid)
    {
        global $DB;

        Toolbox::deprecated('Use clone');
        $result = $DB->request(
            [
              'FROM'  => ComputerAntivirus::getTable(),
              'WHERE' => ['computers_id' => $oldid],
            ]
        );
        foreach ($result as $data) {
            $antirivus            = new self();
            unset($data['id']);
            $data['computers_id'] = $newid;
            $data                 = Toolbox::addslashes_deep($data);
            $antirivus->add($data);
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
           'field'              => 'antivirus_version',
           'name'               => _n('Version', 'Versions', 1),
           'datatype'           => 'string',
           'massiveaction'      => false,
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '3',
           'table'              => $this->getTable(),
           'field'              => 'signature_version',
           'name'               => __('Signature database version'),
           'datatype'           => 'string',
           'massiveaction'      => false,
           'autocomplete'       => true,
        ];

        return $tab;
    }


    public static function rawSearchOptionsToAdd()
    {
        $tab = [];
        $name = _n('Antivirus', 'Antiviruses', Session::getPluralNumber());

        $tab[] = [
           'id'                 => 'antivirus',
           'name'               => $name
        ];

        $tab[] = [
           'id'                 => '167',
           'table'              => 'glpi_computerantiviruses',
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
           'id'                 => '168',
           'table'              => 'glpi_computerantiviruses',
           'field'              => 'antivirus_version',
           'name'               => _n('Version', 'Versions', 1),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'datatype'           => 'text',
           'joinparams'         => [
              'jointype'           => 'child'
           ]
        ];

        $tab[] = [
           'id'                 => '169',
           'table'              => 'glpi_computerantiviruses',
           'field'              => 'is_active',
           'linkfield'          => '',
           'name'               => __('Active'),
           'datatype'           => 'bool',
           'joinparams'         => [
              'jointype'           => 'child'
           ],
           'massiveaction'      => false,
           'forcegroupby'       => true,
           'searchtype'         => ['equals']
        ];

        $tab[] = [
           'id'                 => '170',
           'table'              => 'glpi_computerantiviruses',
           'field'              => 'is_uptodate',
           'linkfield'          => '',
           'name'               => __('Is up to date'),
           'datatype'           => 'bool',
           'joinparams'         => [
              'jointype'           => 'child'
           ],
           'massiveaction'      => false,
           'forcegroupby'       => true,
           'searchtype'         => ['equals']
        ];

        $tab[] = [
           'id'                 => '171',
           'table'              => 'glpi_computerantiviruses',
           'field'              => 'signature_version',
           'name'               => __('Signature database version'),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'datatype'           => 'text',
           'joinparams'         => [
              'jointype'           => 'child'
           ]
        ];

        $tab[] = [
           'id'                 => '172',
           'table'              => 'glpi_computerantiviruses',
           'field'              => 'date_expiration',
           'name'               => __('Expiration date'),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'datatype'           => 'date',
           'joinparams'         => [
              'jointype'           => 'child'
           ]
        ];

        return $tab;
    }

    /**
     * Display form for antivirus
     *
     * @param integer $ID      id of the antivirus
     * @param array   $options
     *
     * @return boolean TRUE if form is ok
    **/
    public function showForm($ID, $options = [])
    {

        if (!Session::haveRight("computer", READ)) {
            return false;
        }

        $comp = new Computer();
        if ($ID > 0) {
            $this->check($ID, READ);
            $comp->getFromDB($this->fields['computers_id']);
        } else {
            $this->check(-1, CREATE, $options);
            $comp->getFromDB($options['computers_id']);
        }

        $plugin = __('No');
        if (Plugin::haveImport() && $ID && $this->fields['is_dynamic']) {
            ob_start();
            Plugin::doHook("autoinventory_form", $this);
            $plugin = ob_get_clean();
        }

        $form = [
           'action' => self::getFormURL(),
           'buttons' => [
              Session::haveRight("computer", UPDATE) ? [
                 'type' => 'submit',
                 'name' => self::isNewID($ID) ? 'add' : 'update',
                 'value' => self::isNewID($ID) ? __('Add') : __('Update'),
                 'class' => 'btn btn-secondary'
              ] : []
           ],
           'content' => [
              [
                 'visible' => false,
                 'inputs' => [
                    $this->isNewID($ID) ? [
                       'type' => 'hidden',
                       'name' => 'computers_id',
                       'value' => $options['computers_id'],
                    ] : [
                       'type' => 'hidden',
                       'name' => 'id',
                       'value' => $ID,
                    ]
                 ]
              ],
              'New item - ' . self::getTypeName(1) => [
                 'visible' => true,
                 'inputs' => [
                    Computer::getTypeName(1) => [
                       'content' => $comp->getLink(),
                    ],
                    __('Automatic inventory') => Plugin::haveImport() ? [
                       'content' => $plugin,
                    ] : [],
                    __('Name') => [
                       'type' => 'text',
                       'name' => 'name',
                       'value' => $this->fields['name'],
                    ],
                    __('Active') => [
                       'type' => 'checkbox',
                       'name' => 'is_active',
                       'value' => $this->fields['is_active'],
                    ],
                    Manufacturer::getTypeName(1) => [
                       'type' => 'select',
                       'name' => 'manufacturers_id',
                       'values' => getOptionForItems('Manufacturer'),
                       'value' => $this->fields['manufacturers_id'],
                    ],
                    __('Up to date') => [
                       'type' => 'checkbox',
                       'name' => 'is_uptodate',
                       'value' => $this->fields['is_uptodate'],
                    ],
                    __('Antivirus version') => [
                       'type' => 'text',
                       'name' => 'antivirus_version',
                       'value' => $this->fields['antivirus_version'],
                    ],
                    __('Signature database version') => [
                       'type' => 'text',
                       'name' => 'signature_version',
                       'value' => $this->fields['signature_version'],
                    ],
                    __('Expiration date') => [
                       'type' => 'date',
                       'name' => 'date_expiration',
                       'value' => $this->fields['date_expiration'],
                    ],
                 ]
              ],
           ]
        ];
        renderTwigForm($form);

        return true;
    }


    /**
     * Print the computers antiviruses
     *
     * @param Computer $comp          Computer object
     * @param integer  $withtemplate  Template or basic item (default 0)
     *
     * @return void
    **/
    public static function showForComputer(Computer $comp, $withtemplate = 0)
    {
        global $DB;

        $ID = $comp->fields['id'];

        if (
            !$comp->getFromDB($ID)
            || !$comp->can($ID, READ)
        ) {
            return;
        }
        $canedit = $comp->canEdit($ID);

        if (
            $canedit
            && !(!empty($withtemplate) && ($withtemplate == 2))
        ) {
            echo "<div class='center firstbloc'>" .
                  "<a class='btn btn-secondary' href='" . ComputerAntivirus::getFormURL() . "?computers_id=$ID&amp;withtemplate=" .
                     $withtemplate . "'>";
            echo __('Add an antivirus');
            echo "</a></div>\n";
        }

        echo "<div class='spaced center'>";

        $result = $DB->request(
            [
              'FROM'  => ComputerAntivirus::getTable(),
              'WHERE' => [
                 'computers_id' => $ID,
                 'is_deleted'   => 0,
              ],
            ]
        );

        echo "<table class='tab_cadre_fixehov' aria-label='Antivirus information'>";
        $colspan = 7;
        if (Plugin::haveImport()) {
            $colspan++;
        }
        echo "<tr class='noHover'><th colspan='$colspan'>" . self::getTypeName($result->numrows()) .
             "</th></tr>";

        if ($result->numrows() != 0) {
            $header = "<tr><th>" . __('Name') . "</th>";
            if (Plugin::haveImport()) {
                $header .= "<th>" . __('Automatic inventory') . "</th>";
            }
            $header .= "<th>" . Manufacturer::getTypeName(1) . "</th>";
            $header .= "<th>" . __('Antivirus version') . "</th>";
            $header .= "<th>" . __('Signature database version') . "</th>";
            $header .= "<th>" . __('Active') . "</th>";
            $header .= "<th>" . __('Up to date') . "</th>";
            $header .= "<th>" . __('Expiration date') . "</th>";
            $header .= "</tr>";
            echo $header;

            Session::initNavigateListItems(
                __CLASS__,
                //TRANS : %1$s is the itemtype name,
                //        %2$s is the name of the item (used for headings of a list)
                sprintf(
                    __('%1$s = %2$s'),
                    Computer::getTypeName(1),
                    $comp->getName()
                )
            );

            $antivirus = new self();
            foreach ($result as $data) {
                $antivirus->getFromDB($data['id']);
                echo "<tr class='tab_bg_2'>";
                echo "<td>" . $antivirus->getLink() . "</td>";
                if (Plugin::haveImport()) {
                    echo "<td>" . Dropdown::getYesNo($data['is_dynamic']) . "</td>";
                }
                echo "<td>";
                if ($data['manufacturers_id']) {
                    echo Dropdown::getDropdownName(
                        'glpi_manufacturers',
                        $data['manufacturers_id']
                    ) . "</td>";
                } else {
                    echo "</td>";
                }
                echo "<td>" . $data['antivirus_version'] . "</td>";
                echo "<td>" . $data['signature_version'] . "</td>";
                echo "<td>" . Dropdown::getYesNo($data['is_active']) . "</td>";
                echo "<td>" . Dropdown::getYesNo($data['is_uptodate']) . "</td>";
                echo "<td>" . Html::convDate($data['date_expiration']) . "</td>";
                echo "</tr>";
                Session::addToNavigateListItems(__CLASS__, $data['id']);
            }
            echo $header;
        } else {
            echo "<tr class='tab_bg_2'><th colspan='$colspan'>" . __('No item found') . "</th></tr>";
        }

        echo "</table>";
        echo "</div>";
    }
}
