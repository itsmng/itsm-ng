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

class DeviceFirmware extends CommonDevice
{
    protected static $forward_entity_to = ['Item_DeviceFirmware', 'Infocom'];

    public static function getTypeName($nb = 0)
    {
        return _n('Firmware', 'Firmware', $nb);
    }


    public function getAdditionalFields()
    {

        return array_merge(
            parent::getAdditionalFields(),
            [
              _n('Type', 'Types', 1) => [
                 'name'  => 'devicefirmwaretypes_id',
                 'type'  => 'select',
                 'values' => getOptionForItems('DeviceFirmwareType'),
                 'value' => $this->fields['devicefirmwaretypes_id'],
                 'actions' => getItemActionButtons(['info', 'add'], 'DeviceFirmwareType')
              ],
              __('Installation date') => [
                 'name'   => 'date',
                 'type'   => 'date',
                 'value' => $this->fields['date']
              ],
              _n('Version', 'Versions', 1) => [
                 'name'   => 'version',
                 'type'   => 'text',
                 'value' => $this->fields['version']
              ],
              _n('Model', 'Models', 1) => [
                 'name'   => 'devicefirmwaremodels_id',
                 'type'   => 'select',
                 'values' => getOptionForItems('DeviceFirmwareModel'),
                 'value' => $this->fields['devicefirmwaremodels_id'],
                 'actions' => getItemActionButtons(['info', 'add'], 'DeviceFirmwareModel')
              ]
            ]
        );
    }


    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '11',
           'table'              => $this->getTable(),
           'field'              => 'date',
           'name'               => __('Installation date'),
           'datatype'           => 'date'
        ];

        $tab[] = [
           'id'                 => '12',
           'table'              => 'glpi_devicefirmwaremodels',
           'field'              => 'name',
           'name'               => _n('Model', 'Models', 1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '13',
           'table'              => 'glpi_devicefirmwaretypes',
           'field'              => 'name',
           'name'               => _n('Type', 'Types', 1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '14',
           'table'              => 'glpi_devicefirmwares',
           'field'              => 'version',
           'name'               => _n('Version', 'Versions', 1),
           'autocomplete'       => true,
        ];

        return $tab;
    }

    public static function getHTMLTableHeader(
        $itemtype,
        HTMLTableBase $base,
        ?HTMLTableSuperHeader $super = null,
        ?HTMLTableHeader $father = null,
        array $options = []
    ) {
        global $CFG_GLPI;
        $column = parent::getHTMLTableHeader($itemtype, $base, $super, $father, $options);

        if ($column == $father) {
            return $father;
        }

        if (in_array($itemtype, $CFG_GLPI['itemdevicefirmware_types'])) {
            Manufacturer::getHTMLTableHeader(__CLASS__, $base, $super, $father, $options);
            $base->addHeader('devicefirmware_type', _n('Type', 'Types', 1), $super, $father);
            $base->addHeader('version', _n('Version', 'Versions', 1), $super, $father);
            $base->addHeader('date', __('Installation date'), $super, $father);
        }
    }

    public function getHTMLTableCellForItem(
        ?HTMLTableRow $row = null,
        ?CommonDBTM $item = null,
        ?HTMLTableCell $father = null,
        array $options = []
    ) {
        global $CFG_GLPI;
        $column = parent::getHTMLTableCellForItem($row, $item, $father, $options);

        if ($column == $father) {
            return $father;
        }

        if (in_array($item->getType(), $CFG_GLPI['itemdevicefirmware_types'])) {
            Manufacturer::getHTMLTableCellsForItem($row, $this, null, $options);

            if ($this->fields["devicefirmwaretypes_id"]) {
                $row->addCell(
                    $row->getHeaderByName('devicefirmware_type'),
                    Dropdown::getDropdownName(
                        "glpi_devicefirmwaretypes",
                        $this->fields["devicefirmwaretypes_id"]
                    ),
                    $father
                );
            }
            $row->addCell(
                $row->getHeaderByName('version'),
                $this->fields["version"],
                $father
            );

            if ($this->fields["date"]) {
                $row->addCell(
                    $row->getHeaderByName('date'),
                    Html::convDate($this->fields["date"]),
                    $father
                );
            }
        }
    }

    public function getImportCriteria()
    {

        return [
           'designation'              => 'equal',
           'devicefirmwaretypes_id'   => 'equal',
           'manufacturers_id'         => 'equal',
           'version'                  => 'equal'
        ];
    }


    public static function getIcon()
    {
        return "fas fa-microchip";
    }
}
