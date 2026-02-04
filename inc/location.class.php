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

/// Location class
class Location extends CommonTreeDropdown
{
    use MapGeolocation;

    // From CommonDBTM
    public $dohistory          = true;
    public $can_be_translated  = true;

    public static $rightname          = 'location';



    public function getAdditionalFields()
    {

        return [
           __('As child of') => [
              'name'  => $this->getForeignKeyField(),
              'type'  => 'select',
              'values'  => getOptionForItems('Location', ['NOT' => ['id' => $this->getID()]]),
              'value' => $this->fields[$this->getForeignKeyField()],
           ],
           __('Address') => [
              'name'   => 'address',
              'type'   => 'text',
              'value' => $this->fields['address'],
           ],
           __('Postal code') => [
              'name'   => 'postcode',
              'type'   => 'text',
              'value' => $this->fields['postcode'],
           ],
           __('Town') => [
              'name'   => 'town',
              'type'   => 'text',
              'value' => $this->fields['town'],
           ],
           _x('location', 'State') => [
              'name'   => 'state',
              'type'   => 'text',
              'value' => $this->fields['state'],
           ],
           __('Country') => [
              'name'   => 'country',
              'type'   => 'text',
              'value' => $this->fields['country'],
           ],
           __('Building number') => [
              'name'  => 'building',
              'type'  => 'text',
              'value' => $this->fields['building'],
           ],
           __('Room number') => [
              'name'  => 'room',
              'type'  => 'text',
              'value' => $this->fields['room'],
           ],
           // __('Location on map') => [
           //    'name'   => 'setlocation',
           //    'type'   => 'setlocation',
           //    'list'   => false
           // ],
           __('Latitude') => [
              'name'  => 'latitude',
              'type'  => 'text',
              'value' => $this->fields['latitude'],
           ],
           __('Longitude') => [
              'name'  => 'longitude',
              'type'  => 'text',
              'value' => $this->fields['longitude'],
           ],
           __('Altitude') => [
              'name'  => 'altitude',
              'type'  => 'text',
              'value' => $this->fields['altitude'],
           ]
        ];
    }


    public static function getTypeName($nb = 0)
    {
        return _n('Location', 'Locations', $nb);
    }


    public static function rawSearchOptionsToAdd()
    {
        $tab = [];

        $tab[] = [
           'id'                 => '3',
           'table'              => 'glpi_locations',
           'field'              => 'completename',
           'name'               => Location::getTypeName(1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '101',
           'table'              => 'glpi_locations',
           'field'              => 'address',
           'name'               => __('Address'),
           'massiveaction'      => false,
           'datatype'           => 'string'
        ];

        $tab[] = [
           'id'                 => '102',
           'table'              => 'glpi_locations',
           'field'              => 'postcode',
           'name'               => __('Postal code'),
           'massiveaction'      => false,
           'datatype'           => 'string'
        ];

        $tab[] = [
           'id'                 => '103',
           'table'              => 'glpi_locations',
           'field'              => 'town',
           'name'               => __('Town'),
           'massiveaction'      => false,
           'datatype'           => 'string'
        ];

        $tab[] = [
           'id'                 => '104',
           'table'              => 'glpi_locations',
           'field'              => 'state',
           'name'               => _x('location', 'State'),
           'massiveaction'      => false,
           'datatype'           => 'string'
        ];

        $tab[] = [
           'id'                 => '105',
           'table'              => 'glpi_locations',
           'field'              => 'country',
           'name'               => __('Country'),
           'massiveaction'      => false,
           'datatype'           => 'string'
        ];

        $tab[] = [
           'id'                 => '91',
           'table'              => 'glpi_locations',
           'field'              => 'building',
           'name'               => __('Building number'),
           'massiveaction'      => false,
           'datatype'           => 'string'
        ];

        $tab[] = [
           'id'                 => '92',
           'table'              => 'glpi_locations',
           'field'              => 'room',
           'name'               => __('Room number'),
           'massiveaction'      => false,
           'datatype'           => 'string'
        ];

        $tab[] = [
           'id'                 => '93',
           'table'              => 'glpi_locations',
           'field'              => 'comment',
           'name'               => __('Location comments'),
           'massiveaction'      => false,
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '998',
           'table'              => 'glpi_locations',
           'field'              => 'latitude',
           'name'               => __('Latitude'),
           'massiveaction'      => false,
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '999',
           'table'              => 'glpi_locations',
           'field'              => 'longitude',
           'name'               => __('Longitude'),
           'massiveaction'      => false,
           'datatype'           => 'text'
        ];

        return $tab;
    }

    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '11',
           'table'              => 'glpi_locations',
           'field'              => 'building',
           'name'               => __('Building number'),
           'datatype'           => 'text',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '12',
           'table'              => 'glpi_locations',
           'field'              => 'room',
           'name'               => __('Room number'),
           'datatype'           => 'text',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '15',
           'table'              => 'glpi_locations',
           'field'              => 'address',
           'name'               => __('Address'),
           'massiveaction'      => false,
           'datatype'           => 'string'
        ];

        $tab[] = [
           'id'                 => '17',
           'table'              => 'glpi_locations',
           'field'              => 'postcode',
           'name'               => __('Postal code'),
           'massiveaction'      => true,
           'datatype'           => 'string'
        ];

        $tab[] = [
           'id'                 => '18',
           'table'              => 'glpi_locations',
           'field'              => 'town',
           'name'               => __('Town'),
           'massiveaction'      => true,
           'datatype'           => 'string'
        ];

        $tab[] = [
           'id'                 => '21',
           'table'              => 'glpi_locations',
           'field'              => 'latitude',
           'name'               => __('Latitude'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '20',
           'table'              => 'glpi_locations',
           'field'              => 'longitude',
           'name'               => __('Longitude'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '22',
           'table'              => 'glpi_locations',
           'field'              => 'altitude',
           'name'               => __('Altitude'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '101',
           'table'              => 'glpi_locations',
           'field'              => 'address',
           'name'               => __('Address'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '102',
           'table'              => 'glpi_locations',
           'field'              => 'postcode',
           'name'               => __('Postal code'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '103',
           'table'              => 'glpi_locations',
           'field'              => 'town',
           'name'               => __('Town'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '104',
           'table'              => 'glpi_locations',
           'field'              => 'state',
           'name'               => _x('location', 'State'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '105',
           'table'              => 'glpi_locations',
           'field'              => 'country',
           'name'               => __('Country'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        return $tab;
    }


    public function defineTabs($options = [])
    {

        $ong = parent::defineTabs($options);
        $this->addImpactTab($ong, $options);
        $this->addStandardTab('Netpoint', $ong, $options);
        $this->addStandardTab('Document_Item', $ong, $options);
        $this->addStandardTab(__CLASS__, $ong, $options);

        return $ong;
    }


    public function cleanDBonPurge()
    {

        Rule::cleanForItemAction($this);
        Rule::cleanForItemCriteria($this, '_locations_id%');
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            switch ($item->getType()) {
                case __CLASS__:
                    $ong    = [];
                    $ong[1] = $this->getTypeName(Session::getPluralNumber());
                    $ong[2] = _n('Item', 'Items', Session::getPluralNumber());
                    return $ong;
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == __CLASS__) {
            switch ($tabnum) {
                case 1:
                    $item->showChildren();
                    break;
                case 2:
                    $item->showItems();
                    break;
            }
        }
        return true;
    }


    /**
     * Print the HTML array of items for a location
     *
     * @since 0.85
     *
     * @return void
    **/
    public function showItems()
    {
        global $DB, $CFG_GLPI;

        $locations_id = $this->fields['id'];

        $current_itemtype = Session::getSavedOption(__CLASS__, 'criterion', 0);
        if ($current_itemtype && !in_array($current_itemtype, $CFG_GLPI['location_types'], true)) {
            $current_itemtype = 0;
        }

        if (!$this->can($locations_id, READ)) {
            return false;
        }

        $rand = mt_rand();
        $queries = [];
        $itemtypes = $current_itemtype ? [$current_itemtype] : $CFG_GLPI['location_types'];
        foreach ($itemtypes as $itemtype) {
            $item = new $itemtype();
            if (!$item->maybeLocated()) {
                continue;
            }
            $table = getTableForItemType($itemtype);
            $itemtype_criteria = [
               'SELECT' => [
                  "$table.id",
                  new \QueryExpression($DB->quoteValue($itemtype) . ' AS ' . $DB->quoteName('type')),
               ],
               'FROM'   => $table,
               'WHERE'  => [
                  "$table.locations_id"   => $locations_id,
               ] + getEntitiesRestrictCriteria($table, 'entities_id')
            ];
            if ($item->maybeDeleted()) {
                $itemtype_criteria['WHERE']['is_deleted'] = 0;
            }
            $queries[] = $itemtype_criteria;
        }
        $criteria = null;
        if (count($queries) === 1) {
            $criteria = $queries[0];
        } elseif (count($queries) > 1) {
            $criteria = ['FROM' => new \QueryUnion($queries)];
        }

        $filter_options = [0 => Dropdown::EMPTY_VALUE];
        foreach ($CFG_GLPI['location_types'] as $type) {
            $type_item = getItemForItemtype($type);
            if ($type_item && $type_item->maybeLocated()) {
                $filter_options[$type] = $type_item->getTypeName(1);
            }
        }
        asort($filter_options);

        echo "<div class='spaced'>";
        echo "<div class='form-section'>";
        echo "<div class='form-section-content'>";
        echo "<div class='row row-cols-12'>";
        echo "<div class='col-lg-6'>";
        echo "<label class='form-label w-100'>" . _n('Type', 'Types', 1) . "</label>";
        echo "<div class='d-flex flex-nowrap align-items-center w-100'>";
        echo "<select class='form-select form-select-sm' name='criterion' id='location_items_criterion_$rand'>";
        foreach ($filter_options as $value => $label) {
            $selected = (string)$current_itemtype == (string)$value ? ' selected="selected"' : '';
            echo "<option value=\"$value\"$selected>$label</option>";
        }
        echo "</select>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo <<<JS
<script>
$(function() {
    $('#location_items_criterion_$rand').on('change', function() {
        var body = $(this).closest('.item-body');
        var container = body.parent();
        if (container.is('#main-accordion-view') && typeof mainTab !== 'undefined') {
            container = $('#' + mainTab);
        }
        var url = container.data('url');
        var params = container.data('params');
        var searchParams = new URLSearchParams(params);
        searchParams.set('criterion', $(this).val());
        searchParams.set('start', 0);
        var updatedParams = searchParams.toString();
        $.ajax({
            url: url,
            data: updatedParams,
            success: function(response) {
                body.html(response);
                container.data('params', updatedParams);
                container.attr('data-params', updatedParams);
            }
        });
    });
});
</script>
JS;
        echo "</div>";

        if ($criteria === null) {
            echo "<p class='center b'>" . __('No item found') . "</p>";
            return;
        }

        $iterator = $DB->request($criteria);
        $fields = [
           'type' => _n('Type', 'Types', 1),
           'entity' => Entity::getTypeName(1),
           'name' => __('Name'),
           'serial' => __('Serial number'),
           'inventory' => __('Inventory number'),
        ];
        $values = [];
        while ($data = $iterator->next()) {
            $item = getItemForItemtype($data['type']);
            if (!$item || !$item->getFromDB($data['id'])) {
                continue;
            }
            $values[] = [
               'type' => $item->getTypeName(),
               'entity' => Dropdown::getDropdownName(
                   'glpi_entities',
                   $item->getEntityID()
               ),
               'name' => $item->getLink(),
               'serial' => (isset($item->fields['serial']) && $item->fields['serial'] !== '')
                   ? $item->fields['serial']
                   : '-',
               'inventory' => (isset($item->fields['otherserial']) && $item->fields['otherserial'] !== '')
                   ? $item->fields['otherserial']
                   : '-',
            ];
        }

        if (count($values)) {
            echo "<div class='spaced'>";
            renderTwigTemplate('table.twig', [
               'fields' => $fields,
               'values' => $values,
               'pageSize' => $_SESSION['glpilist_limit'],
            ]);
            echo "</div>";
        } else {
            echo "<p class='center b'>" . __('No item found') . "</p>";
        }
    }

    public function displaySpecificTypeField($ID, $field = [])
    {
        switch ($field['type']) {
            case 'setlocation':
                $this->showMap();
                break;
            default:
                throw new \RuntimeException("Unknown {$field['type']}");
        }
    }

    public static function getIcon()
    {
        return "fas fa-map-marker-alt";
    }
}
