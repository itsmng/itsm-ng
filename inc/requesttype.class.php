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

/// Class RequestType
class RequestType extends CommonDropdown
{
    public static function getTypeName($nb = 0)
    {
        return _n('Request source', 'Request sources', $nb);
    }


    public function getAdditionalFields()
    {

        return [
           __('Active') => [
              'name'  => 'is_active',
              'type'  => 'checkbox',
              'value' => $this->fields['is_active']
           ],
           __('Default for tickets') => [
              'name'  => 'is_helpdesk_default',
              'type'  => 'checkbox',
              'value' => $this->fields['is_helpdesk_default']
           ],
           __('Default for followups') => [
              'name'  => 'is_followup_default',
              'type'  => 'checkbox',
              'value' => $this->fields['is_followup_default']
           ],
           __('Default for mail recipients') => [
              'name'  => 'is_mail_default',
              'type'  => 'checkbox',
              'value' => $this->fields['is_mail_default']
           ],
           __('Default for followup mail recipients') => [
              'name'  => 'is_mailfollowup_default',
              'type'  => 'checkbox',
              'value' => $this->fields['is_mailfollowup_default']
           ],
           __('Request source visible for tickets') => [
              'name'  => 'is_ticketheader',
              'type'  => 'checkbox',
              'value' => $this->fields['is_ticketheader']
           ],
           __('Request source visible for followups') => [
              'name'  => 'is_itilfollowup',
              'type'  => 'checkbox',
              'value' => $this->fields['is_itilfollowup']
           ],
           __('Ticket highlight color') => [
              'name' => 'ticket_highlight_color',
              'type' => 'color',
              'value' => ($this->fields['ticket_highlight_color'] ?? '') ?: '#fff2cc',
           ],
           __('Highlight ticket rows') => [
              'name' => 'highlight_ticket_rows',
              'type' => 'checkbox',
              'value' => $this->fields['highlight_ticket_rows'] ?? 0,
           ],
           __('Highlight ticket page background') => [
              'name' => 'highlight_ticket_page',
              'type' => 'checkbox',
              'value' => $this->fields['highlight_ticket_page'] ?? 0,
           ],
        ];
    }

    public function prepareInputForAdd($input)
    {
        $input = parent::prepareInputForAdd($input);
        return $this->prepareHighlightInput($input);
    }

    public function prepareInputForUpdate($input)
    {
        $input = parent::prepareInputForUpdate($input);
        return $this->prepareHighlightInput($input);
    }

    private function prepareHighlightInput($input)
    {
        if (array_key_exists('ticket_highlight_color', $input)) {
            if (!is_string($input['ticket_highlight_color'])
                || !preg_match('/^#[0-9a-fA-F]{6}$/D', $input['ticket_highlight_color'])) {
                Session::addMessageAfterRedirect(__('Invalid highlight color. Use a six-digit hexadecimal color.'), false, ERROR);
                return false;
            }
            $input['ticket_highlight_color'] = strtolower($input['ticket_highlight_color']);
        }
        foreach (['highlight_ticket_rows', 'highlight_ticket_page'] as $field) {
            if (isset($input[$field])) {
                $input[$field] = (int) in_array($input[$field], [1, '1', true], true);
            }
        }
        return $input;
    }

    /**
     * Load source colors once per rendered list, independently of visible columns.
     * Disabled sources still describe existing tickets and retain their highlighting.
     *
     * @return array<int, string>
     */
    public static function getTicketHighlightColors($location)
    {
        global $DB;

        $fields = ['rows' => 'highlight_ticket_rows', 'page' => 'highlight_ticket_page'];
        if (!isset($fields[$location])) {
            return [];
        }
        $colors = [];
        foreach ($DB->request([
            'SELECT' => ['id', 'ticket_highlight_color'],
            'FROM' => self::getTable(),
            'WHERE' => [$fields[$location] => 1],
        ]) as $source) {
            $color = $source['ticket_highlight_color'];
            if (is_string($color) && preg_match('/^#[0-9a-fA-F]{6}$/D', $color)) {
                $colors[$source['id']] = $color;
            }
        }
        return $colors;
    }

    /** Choose black or white text for a validated highlight color. */
    public static function getTicketHighlightTextColor($color)
    {
        $channels = array_map(static function ($channel) {
            $value = hexdec($channel) / 255;
            return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, str_split(substr($color, 1), 2));
        $luminance = $channels[0] * 0.2126 + $channels[1] * 0.7152 + $channels[2] * 0.0722;
        return $luminance > 0.179 ? '#000000' : '#ffffff';
    }


    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
           'id'                 => '14',
           'table'              => $this->getTable(),
           'field'              => 'is_helpdesk_default',
           'name'               => __('Default for tickets'),
           'datatype'           => 'bool',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '182',
           'table'              => $this->getTable(),
           'field'              => 'is_followup_default',
           'name'               => __('Default for followups'),
           'datatype'           => 'bool',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '15',
           'table'              => $this->getTable(),
           'field'              => 'is_mail_default',
           'name'               => __('Default for mail recipients'),
           'datatype'           => 'bool',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '183',
           'table'              => $this->getTable(),
           'field'              => 'is_mailfollowup_default',
           'name'               => __('Default for followup mail recipients'),
           'datatype'           => 'bool',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '8',
           'table'              => $this->getTable(),
           'field'              => 'is_active',
           'name'               => __('Active'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '180',
           'table'              => $this->getTable(),
           'field'              => 'is_ticketheader',
           'name'               => __('Request source visible for tickets'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '181',
           'table'              => $this->getTable(),
           'field'              => 'is_itilfollowup',
           'name'               => __('Request source visible for followups'),
           'datatype'           => 'bool'
        ];

        return $tab;
    }


    public function post_addItem()
    {
        global $DB;

        $update = [];

        if (isset($this->input["is_helpdesk_default"]) && $this->input["is_helpdesk_default"]) {
            $update['is_helpdesk_default'] = 0;
        }

        if (isset($this->input["is_followup_default"]) && $this->input["is_followup_default"]) {
            $update['is_followup_default'] = 0;
        }

        if (isset($this->input["is_mail_default"]) && $this->input["is_mail_default"]) {
            $update['is_mail_default'] = 0;
        }

        if (isset($this->input["is_mailfollowup_default"]) && $this->input["is_mailfollowup_default"]) {
            $update['is_mailfollowup_default'] = 0;
        }

        if (count($update)) {
            $DB->update(
                $this->getTable(),
                $update,
                [
                  'id' => ['<>', $this->fields['id']]
                ]
            );
        }
    }


    /**
     * @see CommonDBTM::post_updateItem()
    **/
    public function post_updateItem($history = 1)
    {
        global $DB;
        $update = [];

        if (in_array('is_helpdesk_default', $this->updates)) {
            if ($this->input["is_helpdesk_default"]) {
                $update['is_helpdesk_default'] = 0;
            } else {
                Session::addMessageAfterRedirect(__('Be careful: there is no default value'), true);
            }
        }

        if (in_array('is_followup_default', $this->updates)) {
            if ($this->input["is_followup_default"]) {
                $update['is_followup_default'] = 0;
            } else {
                Session::addMessageAfterRedirect(__('Be careful: there is no default value'), true);
            }
        }

        if (in_array('is_mail_default', $this->updates)) {
            if ($this->input["is_mail_default"]) {
                $update['is_mail_default'] = 0;
            } else {
                Session::addMessageAfterRedirect(__('Be careful: there is no default value'), true);
            }
        }

        if (in_array('is_mailfollowup_default', $this->updates)) {
            if ($this->input["is_mailfollowup_default"]) {
                $update['is_mailfollowup_default'] = 0;
            } else {
                Session::addMessageAfterRedirect(__('Be careful: there is no default value'), true);
            }
        }

        if (count($update)) {
            $DB->update(
                $this->getTable(),
                $update,
                [
                  'id' => ['<>', $this->fields['id']]
                ]
            );
        }
    }


    /**
     * Get the default request type for a given source (mail, helpdesk)
     *
     * @param $source string
     *
     * @return requesttypes_id
    **/
    public static function getDefault($source)
    {
        global $DB;

        if (!in_array($source, ['mail', 'mailfollowup', 'helpdesk', 'followup'])) {
            return 0;
        }

        foreach ($DB->request('glpi_requesttypes', ['is_' . $source . '_default' => 1, 'is_active' => 1]) as $data) {
            return $data['id'];
        }
        return 0;
    }


    public function cleanDBonPurge()
    {
        Rule::cleanForItemCriteria($this);
    }


    public function cleanRelationData()
    {

        parent::cleanRelationData();

        if ($this->isUsedAsDefaultRequestType()) {
            $newval = (isset($this->input['_replace_by']) ? $this->input['_replace_by'] : 0);

            Config::setConfigurationValues(
                'core',
                [
                  'default_requesttypes_id' => $newval,
                ]
            );
        }
    }


    public function isUsed()
    {

        if (parent::isUsed()) {
            return true;
        }

        return $this->isUsedAsDefaultRequestType();
    }


    /**
     * Check if type is used as default for new tickets.
     *
     * @return boolean
     */
    private function isUsedAsDefaultRequestType()
    {

        $config_values = Config::getConfigurationValues('core', ['default_requesttypes_id']);

        return array_key_exists('default_requesttypes_id', $config_values)
           && $config_values['default_requesttypes_id'] == $this->fields['id'];
    }
}
