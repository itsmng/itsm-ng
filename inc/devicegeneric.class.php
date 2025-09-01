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
    die("Sorry. You can't access directly to this file");
}

/// Class DeviceGeneric
class DeviceGeneric extends CommonDevice
{
    protected static $forward_entity_to = ['Item_DeviceGeneric', 'Infocom'];

    public static function getTypeName($nb = 0)
    {
        return _n('Generic device', 'Generic devices', $nb);
    }


    public function getAdditionalFields()
    {

        return array_merge(
            parent::getAdditionalFields(),
            [
              _n('Type', 'Types', 1) => [
                 'name'  => 'devicegenerictypes_id',
                 'type'  => 'select',
                 'values' => getOptionForItems('DeviceGenericType'),
                 'value' => $this->fields['devicegenerictypes_id']?? null,
                 'actions' => getItemActionButtons(['add', 'info'], 'DeviceGenericType')
              ]
            ]
        );
    }


    public function rawSearchOptions()
    {
        $tab                 = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '12',
           'table'              => 'glpi_devicegenerictypes',
           'field'              => 'name',
           'name'               => _n('Type', 'Types', 1),
           'datatype'           => 'dropdown'
        ];

        return $tab;
    }


    public static function getHTMLTableHeader(
        $itemtype,
        HTMLTableBase $base,
        HTMLTableSuperHeader $super = null,
        HTMLTableHeader $father = null,
        array $options = []
    ) {

        $column = parent::getHTMLTableHeader($itemtype, $base, $super, $father, $options);

        if ($column == $father) {
            return $father;
        }

        switch ($itemtype) {
            case 'Computer':
                Manufacturer::getHTMLTableHeader(__CLASS__, $base, $super, $father, $options);
                break;
        }
    }


    public function getHTMLTableCellForItem(
        HTMLTableRow $row = null,
        CommonDBTM $item = null,
        HTMLTableCell $father = null,
        array $options = []
    ) {

        $column = parent::getHTMLTableCellForItem($row, $item, $father, $options);

        if ($column == $father) {
            return $father;
        }

        switch ($item->getType()) {
            case 'Computer':
                Manufacturer::getHTMLTableCellsForItem($row, $this, null, $options);
                break;
        }
    }


    /**
     * Criteria used for import function
     *
     * @see CommonDevice::getImportCriteria()
     *
     * @since 0.84
    **/
    public function getImportCriteria()
    {

        return ['designation'       => 'equal',
                     'manufacturers_id'  => 'equal',
                     'devicecasetypes_id' => 'equal',
                     'locations_id'      => 'equal',
                     ];
    }
}
