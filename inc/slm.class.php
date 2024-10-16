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
 * @since 9.2
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * SLM Class
 **/
class SLM extends CommonDBTM
{
    // From CommonDBTM
    public $dohistory                   = true;

    protected static $forward_entity_to = ['SLA', 'OLA'];

    public static $rightname                   = 'slm';

    public const TTR = 0; // Time to resolve
    public const TTO = 1; // Time to own

    public static function getTypeName($nb = 0)
    {
        return _n('Service level', 'Service levels', $nb);
    }

    /**
     * Force calendar of the SLM if value -1: calendar of the entity
     *
     * @param integer $calendars_id calendars_id of the ticket
     **/
    public function setTicketCalendar($calendars_id)
    {

        if ($this->fields['calendars_id'] == -1) {
            $this->fields['calendars_id'] = $calendars_id;
        }
    }

    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addImpactTab($ong, $options);
        $this->addStandardTab('SLA', $ong, $options);
        $this->addStandardTab('OLA', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }

    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
              SLA::class,
              OLA::class,
         ]
        );
    }

    /**
     * Print the slm form
     *
     * @param integer $ID ID of the item
     *
     * @return boolean item found
     **/
    public function showForm($ID)
    {
        $form = [
           'action' => Toolbox::getItemTypeFormURL('slm'),
           'itemtype' => $this->getType(),
           'content' => [
              __('Niveau de services') => [
                 'visible' => true,
                 'inputs' => [
                    $this->isNewID($ID) ? [] : [
                       'type' => 'hidden',
                       'name' => 'id',
                       'value' => $ID
                    ],
                    __('Name') => [
                       'name' => 'name',
                       'type' => 'text',
                       'value' => $this->fields['name'] ?? '',
                    ],
                    __('Calendar') => [
                       'name' => 'calendars_id',
                       'type' => 'select',
                       'values' => [-1 => __('Calendar of the ticket'), 0 => __('24/7')] +
                           getItemByEntity(Calendar::class, Session::getActiveEntity()),
                       'value' => $this->fields['calendars_id'] ?? '',
                       'actions' => getItemActionButtons(['info', 'add'], "Calendar"),
                    ],
                    __('Comments') => [
                       'name' => 'comment',
                       'type' => 'textarea',
                       'value' => $this->fields['comment'] ?? '',
                    ],
                 ]
              ]
           ]
        ];
        renderTwigForm($form, '', $this->fields);

        return true;
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
           'field'              => 'id',
           'name'               => __('ID'),
           'massiveaction'      => false,
           'datatype'           => 'number'
        ];

        $tab[] = [
           'id'                 => '4',
           'table'              => 'glpi_calendars',
           'field'              => 'name',
           'name'               => _n('Calendar', 'Calendars', 1),
           'datatype'           => 'dropdown'
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


    public static function getMenuContent()
    {

        $menu = [];
        if (static::canView()) {
            $menu['title']           = self::getTypeName(2);
            $menu['page']            = static::getSearchURL(false);
            $menu['icon']            = static::getIcon();
            $menu['links']['search'] = static::getSearchURL(false);
            if (static::canCreate()) {
                $menu['links']['add'] = SLM::getFormURL(false);
            }

            $menu['options']['sla']['title']           = SLA::getTypeName(1);
            $menu['options']['sla']['page']            = SLA::getSearchURL(false);
            $menu['options']['sla']['links']['search'] = SLA::getSearchURL(false);

            $menu['options']['ola']['title']           = OLA::getTypeName(1);
            $menu['options']['ola']['page']            = OLA::getSearchURL(false);
            $menu['options']['ola']['links']['search'] = OLA::getSearchURL(false);

            $menu['options']['slalevel']['title']           = SlaLevel::getTypeName(Session::getPluralNumber());
            $menu['options']['slalevel']['page']            = SlaLevel::getSearchURL(false);
            $menu['options']['slalevel']['links']['search'] = SlaLevel::getSearchURL(false);

            $menu['options']['olalevel']['title']           = OlaLevel::getTypeName(Session::getPluralNumber());
            $menu['options']['olalevel']['page']            = OlaLevel::getSearchURL(false);
            $menu['options']['olalevel']['links']['search'] = OlaLevel::getSearchURL(false);
        }
        if (count($menu)) {
            return $menu;
        }
        return false;
    }


    public static function getIcon()
    {
        return "fas fa-file-contract";
    }
}
