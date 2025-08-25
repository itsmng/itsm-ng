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

use Glpi\Event;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Entity class
 */
class Entity extends CommonTreeDropdown
{
    use MapGeolocation;

    public $must_be_replace              = true;
    public $dohistory                    = true;

    public $first_level_menu             = "admin";
    public $second_level_menu            = "entity";

    public static $rightname                    = 'entity';
    protected $usenotepad                = true;

    public const READHELPDESK                   = 1024;
    public const UPDATEHELPDESK                 = 2048;

    public const CONFIG_PARENT                  = -2;
    public const CONFIG_NEVER                   = -10;

    public const AUTO_ASSIGN_HARDWARE_CATEGORY  = 1;
    public const AUTO_ASSIGN_CATEGORY_HARDWARE  = 2;

    // Array of "right required to update" => array of fields allowed
    // Missing field here couldn't be update (no right)
    private static $field_right = [
       'entity' => [
          // Address
          'address', 'country', 'email', 'fax', 'notepad',
          'longitude','latitude','altitude',
          'phonenumber', 'postcode', 'state', 'town',
          'website',
          // Advanced (could be user_authtype ?)
          'authldaps_id', 'entity_ldapfilter', 'ldap_dn',
          'mail_domain', 'tag',
          // Inventory
          'entities_id_software', 'level', 'name',
          'completename', 'entities_id',
          'ancestors_cache', 'sons_cache', 'comment'
       ],
       // Inventory
       'infocom' => [
          'autofill_buy_date', 'autofill_delivery_date',
          'autofill_order_date', 'autofill_use_date',
          'autofill_warranty_date',
          'autofill_decommission_date'
       ],
       // Notification
       'notification' => [
          'admin_email', 'admin_reply', 'admin_email_name',
          'admin_reply_name', 'delay_send_emails',
          'is_notif_enable_default',
          'default_cartridges_alarm_threshold',
          'default_consumables_alarm_threshold',
          'default_contract_alert', 'default_infocom_alert',
          'mailing_signature', 'cartridges_alert_repeat',
          'consumables_alert_repeat', 'notclosed_delay',
          'use_licenses_alert', 'use_certificates_alert',
          'send_licenses_alert_before_delay',
          'send_certificates_alert_before_delay',
          'use_contracts_alert',
          'send_contracts_alert_before_delay',
          'use_reservations_alert', 'use_infocoms_alert',
          'send_infocoms_alert_before_delay',
          'notification_subject_tag', 'use_domains_alert',
          'send_domains_alert_close_expiries_delay', 'send_domains_alert_expired_delay'
       ],
       // Helpdesk
       'entity_helpdesk' => [
          'calendars_id', 'tickettype', 'auto_assign_mode',
          'autoclose_delay', 'inquest_config',
          'inquest_rate', 'inquest_delay',
          'inquest_duration','inquest_URL',
          'max_closedate', 'tickettemplates_id',
          'changetemplates_id', 'problemtemplates_id',
          'suppliers_as_private', 'autopurge_delay', 'anonymize_support_agents'
       ],
       // Configuration
       'config' => ['enable_custom_css', 'custom_css_code']
    ];


    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'delete';
        $forbidden[] = 'purge';
        $forbidden[] = 'restore';
        $forbidden[] = 'CommonDropdown' . MassiveAction::CLASS_ACTION_SEPARATOR . 'merge';
        return $forbidden;
    }

    /**
     * @since 0.84
    **/
    public function pre_deleteItem()
    {
        global $GLPI_CACHE;

        // Security do not delete root entity
        if ($this->input['id'] == 0) {
            return false;
        }

        //Cleaning sons calls getAncestorsOf and thus... Re-create cache. Call it before clean.
        $this->cleanParentsSons();
        if (Toolbox::useCache()) {
            $ckey = 'ancestors_cache_' . $this->getTable() . '_' . $this->getID();
            $GLPI_CACHE->delete($ckey);
        }
        return true;
    }


    public static function getTypeName($nb = 0)
    {
        return _n('Entity', 'Entities', $nb);
    }


    public function canCreateItem()
    {
        // Check the parent
        return Session::haveRecursiveAccessToEntity($this->getField('entities_id'));
    }


    /**
    * @since 0.84
    **/
    public static function canUpdate()
    {

        return (Session::haveRightsOr(self::$rightname, [UPDATE, self::UPDATEHELPDESK])
                || Session::haveRight('notification', UPDATE));
    }


    public function canUpdateItem()
    {
        // Check the current entity
        return Session::haveAccessToEntity($this->getField('id'));
    }


    public function canViewItem()
    {
        // Check the current entity
        return Session::haveAccessToEntity($this->getField('id'));
    }


    public static function isNewID($ID)
    {
        return (($ID < 0) || !strlen($ID));
    }

    /**
     * Can object have a location
     *
     * @since 9.3
     *
     * @return boolean
     */
    public function maybeLocated()
    {
        return true;
    }

    /**
     * Check right on each field before add / update
     *
     * @since 0.84 (before in entitydata.class)
     *
     * @param $input array (form)
     *
     * @return array (filtered input)
    **/
    private function checkRightDatas($input)
    {

        $tmp = [];

        if (isset($input['id'])) {
            $tmp['id'] = $input['id'];
        }

        foreach (self::$field_right as $right => $fields) {
            if ($right == 'entity_helpdesk') {
                if (Session::haveRight(self::$rightname, self::UPDATEHELPDESK)) {
                    foreach ($fields as $field) {
                        if (isset($input[$field])) {
                            $tmp[$field] = $input[$field];
                        }
                    }
                }
            } else {
                if (Session::haveRight($right, UPDATE)) {
                    foreach ($fields as $field) {
                        if (isset($input[$field])) {
                            $tmp[$field] = $input[$field];
                        }
                    }
                }
            }
        }
        // Add framework  / internal ones
        foreach ($input as $key => $val) {
            if ($key[0] == '_') {
                $tmp[$key] = $input[$key];
            }
        }

        return $tmp;
    }


    /**
     * @since 0.84 (before in entitydata.class)
    **/
    public function prepareInputForAdd($input)
    {
        global $DB;

        $input['name'] = isset($input['name']) ? trim($input['name']) : '';
        if (empty($input["name"])) {
            Session::addMessageAfterRedirect(
                __("You can't add an entity without name"),
                false,
                ERROR
            );
            return false;
        }

        $input = parent::prepareInputForAdd($input);

        $result = $this::getAdapter()->request([
           'SELECT' => new \QueryExpression(
               'MAX(' . $DB->quoteName('id') . ')+1 AS newID'
           ),
           'FROM'   => $this->getTable()
        ])->fetchAssociative();
        $input['id'] = $result['newID'];

        $input['max_closedate'] = $_SESSION["glpi_currenttime"];

        if (!Session::isCron()) { // Filter input for connected
            $input = $this->checkRightDatas($input);
        }
        return $input;
    }


    /**
     * @since 0.84 (before in entitydata.class)
    **/
    public function prepareInputForUpdate($input)
    {

        $input = parent::prepareInputForUpdate($input);

        // Si on change le taux de déclenchement de l'enquête (enquête activée) ou le type de l'enquete,
        // cela s'applique aux prochains tickets - Pas à l'historique
        if (
            (isset($input['inquest_rate'])
             && (($this->fields['inquest_rate'] == 0)
                 || is_null($this->fields['max_closedate']))
             && ($input['inquest_rate'] != $this->fields['inquest_rate']))
            || (isset($input['inquest_config'])
                && (($this->fields['inquest_config'] == self::CONFIG_PARENT)
                    || is_null($this->fields['max_closedate']))
                && ($input['inquest_config'] != $this->fields['inquest_config']))
        ) {
            $input['max_closedate'] = $_SESSION["glpi_currenttime"];
        }

        // Force entities_id = -1 for root entity
        if ($input['id'] == 0) {
            $input['entities_id'] = -1;
            $input['level']       = 1;
        }
        if (!Session::isCron()) { // Filter input for connected
            $input = $this->checkRightDatas($input);
        }
        return $input;
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addImpactTab($ong, $options);
        $this->addStandardTab(__CLASS__, $ong, $options);
        $this->addStandardTab('Profile_User', $ong, $options);
        $this->addStandardTab('Rule', $ong, $options);
        $this->addStandardTab('Document_Item', $ong, $options);
        $this->addStandardTab('Notepad', $ong, $options);
        $this->addStandardTab('KnowbaseItem_Item', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    /**
     * @since 0.84 (before in entitydata.class)
    **/
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            switch ($item->getType()) {
                case __CLASS__:
                    $ong    = [];
                    $ong[1] = $this->getTypeName(Session::getPluralNumber());
                    $ong[2] = __('Address');
                    $ong[3] = __('Advanced information');
                    if (Notification::canView()) {
                        $ong[4] = _n('Notification', 'Notifications', Session::getPluralNumber());
                    }
                    if (
                        Session::haveRightsOr(
                            self::$rightname,
                            [self::READHELPDESK, self::UPDATEHELPDESK]
                        )
                    ) {
                        $ong[5] = __('Assistance');
                    }
                    $ong[6] = __('Assets');
                    if (Session::haveRight(Config::$rightname, UPDATE)) {
                        $ong[7] = __('UI customization');
                    }

                    return $ong;
            }
        }
        return '';
    }


    /**
     * @since 0.84 (before in entitydata.class)
    **/
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == __CLASS__) {
            switch ($tabnum) {
                case 1:
                    $item->showChildren();
                    break;

                case 2:
                    self::showStandardOptions($item);
                    break;

                case 3:
                    self::showAdvancedOptions($item);
                    break;

                case 4:
                    self::showNotificationOptions($item);
                    break;

                case 5:
                    self::showHelpdeskOptions($item);
                    break;

                case 6:
                    self::showInventoryOptions($item);
                    break;

                case 7:
                    self::showUiCustomizationOptions($item);
                    break;
            }
        }
        return true;
    }


    /**
     * Print a good title for entity pages
     *
     *@return void
     **/
    public function title()
    {
        // Empty title for entities
    }


    public function displayHeader()
    {
        Html::header($this->getTypeName(1), '', "admin", "entity");
    }


    /**
     * Get the ID of entity assigned to the object
     *
     * simply return ID
     *
     * @return integer ID of the entity
    **/
    public function getEntityID()
    {

        if (isset($this->fields["id"])) {
            return $this->fields["id"];
        }
        return -1;
    }


    public function isEntityAssign()
    {
        return true;
    }


    public function maybeRecursive()
    {
        return true;
    }


    /**
     * Is the object recursive
     *
     * Entity are always recursive
     *
     * @return integer (0/1)
    **/
    public function isRecursive()
    {
        return true;
    }


    public function post_addItem()
    {

        parent::post_addItem();

        // Add right to current user - Hack to avoid login/logout
        $_SESSION['glpiactiveentities'][$this->fields['id']] = $this->fields['id'];
        $_SESSION['glpiactiveentities_string']              .= ",'" . $this->fields['id'] . "'";
    }


    public function cleanDBonPurge()
    {

        // most use entities_id, RuleDictionnarySoftwareCollection use new_entities_id
        Rule::cleanForItemAction($this, '%entities_id');
        Rule::cleanForItemCriteria($this);

        $pu = new Profile_User();
        $pu->deleteByCriteria(['entities_id' => $this->fields['id']]);

        $this->deleteChildrenAndRelationsFromDb(
            [
              Entity_KnowbaseItem::class,
              Entity_Reminder::class,
              Entity_RSSFeed::class,
            ]
        );
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
           'field'              => 'completename',
           'name'               => __('Complete name'),
           'datatype'           => 'itemlink',
           'massiveaction'      => false
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
           'id'                 => '14',
           'table'              => $this->getTable(),
           'field'              => 'name',
           'name'               => __('Name'),
           'datatype'           => 'itemlink',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '3',
           'table'              => $this->getTable(),
           'field'              => 'address',
           'name'               => __('Address'),
           'massiveaction'      => false,
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '4',
           'table'              => $this->getTable(),
           'field'              => 'website',
           'name'               => __('Website'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '5',
           'table'              => $this->getTable(),
           'field'              => 'phonenumber',
           'name'               => Phone::getTypeName(1),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '6',
           'table'              => $this->getTable(),
           'field'              => 'email',
           'name'               => _n('Email', 'Emails', 1),
           'datatype'           => 'email',
           'massiveaction'      => false,
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '10',
           'table'              => $this->getTable(),
           'field'              => 'fax',
           'name'               => __('Fax'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '25',
           'table'              => $this->getTable(),
           'field'              => 'postcode',
           'name'               => __('Postal code'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '11',
           'table'              => $this->getTable(),
           'field'              => 'town',
           'name'               => __('City'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '12',
           'table'              => $this->getTable(),
           'field'              => 'state',
           'name'               => _x('location', 'State'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '13',
           'table'              => $this->getTable(),
           'field'              => 'country',
           'name'               => __('Country'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '67',
           'table'              => $this->getTable(),
           'field'              => 'latitude',
           'name'               => __('Latitude'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '68',
           'table'              => $this->getTable(),
           'field'              => 'longitude',
           'name'               => __('Longitude'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '69',
           'table'              => $this->getTable(),
           'field'              => 'altitude',
           'name'               => __('Altitude'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '16',
           'table'              => $this->getTable(),
           'field'              => 'comment',
           'name'               => __('Comments'),
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '122',
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

        // add objectlock search options
        $tab = array_merge($tab, ObjectLock::rawSearchOptionsToAdd(get_class($this)));

        $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

        $tab[] = [
           'id'                 => 'advanced',
           'name'               => __('Advanced information')
        ];

        $tab[] = [
           'id'                 => '7',
           'table'              => $this->getTable(),
           'field'              => 'ldap_dn',
           'name'               => __('LDAP directory information attribute representing the entity'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '8',
           'table'              => $this->getTable(),
           'field'              => 'tag',
           'name'               => __('Information in inventory tool (TAG) representing the entity'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '9',
           'table'              => 'glpi_authldaps',
           'field'              => 'name',
           'name'               => __('LDAP directory of an entity'),
           'massiveaction'      => false,
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '17',
           'table'              => $this->getTable(),
           'field'              => 'entity_ldapfilter',
           'name'               => __('Search filter (if needed)'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '20',
           'table'              => $this->getTable(),
           'field'              => 'mail_domain',
           'name'               => __('Mail domain'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => 'notif',
           'name'               => __('Notification options')
        ];

        $tab[] = [
           'id'                 => '60',
           'table'              => $this->getTable(),
           'field'              => 'delay_send_emails',
           'name'               => __('Delay to send email notifications'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'number',
           'min'                => 0,
           'max'                => 60,
           'step'               => 1,
           'unit'               => 'minute',
           'toadd'              => [self::CONFIG_PARENT => __('Inheritance of the parent entity')]
        ];

        $tab[] = [
           'id'                 => '61',
           'table'              => $this->getTable(),
           'field'              => 'is_notif_enable_default',
           'name'               => __('Enable notifications by default'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'string'
        ];

        $tab[] = [
           'id'                 => '18',
           'table'              => $this->getTable(),
           'field'              => 'admin_email',
           'name'               => __('Administrator email'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '19',
           'table'              => $this->getTable(),
           'field'              => 'admin_reply',
           'name'               => __('Administrator reply-to email (if needed)'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '21',
           'table'              => $this->getTable(),
           'field'              => 'notification_subject_tag',
           'name'               => __('Prefix for notifications'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '22',
           'table'              => $this->getTable(),
           'field'              => 'admin_email_name',
           'name'               => __('Administrator name'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '23',
           'table'              => $this->getTable(),
           'field'              => 'admin_reply_name',
           'name'               => __('Response address (if needed)'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '24',
           'table'              => $this->getTable(),
           'field'              => 'mailing_signature',
           'name'               => __('Email signature'),
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '26',
           'table'              => $this->getTable(),
           'field'              => 'cartridges_alert_repeat',
           'name'               => __('Alarms on cartridges'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '27',
           'table'              => $this->getTable(),
           'field'              => 'consumables_alert_repeat',
           'name'               => __('Alarms on consumables'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '29',
           'table'              => $this->getTable(),
           'field'              => 'use_licenses_alert',
           'name'               => __('Alarms on expired licenses'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '53',
           'table'              => $this->getTable(),
           'field'              => 'send_licenses_alert_before_delay',
           'name'               => __('Send license alarms before'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '30',
           'table'              => $this->getTable(),
           'field'              => 'use_contracts_alert',
           'name'               => __('Alarms on contracts'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '54',
           'table'              => $this->getTable(),
           'field'              => 'send_contracts_alert_before_delay',
           'name'               => __('Send contract alarms before'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '31',
           'table'              => $this->getTable(),
           'field'              => 'use_infocoms_alert',
           'name'               => __('Alarms on financial and administrative information'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '55',
           'table'              => $this->getTable(),
           'field'              => 'send_infocoms_alert_before_delay',
           'name'               => __('Send financial and administrative information alarms before'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '32',
           'table'              => $this->getTable(),
           'field'              => 'use_reservations_alert',
           'name'               => __('Alerts on reservations'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '48',
           'table'              => $this->getTable(),
           'field'              => 'default_contract_alert',
           'name'               => __('Default value for alarms on contracts'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '49',
           'table'              => $this->getTable(),
           'field'              => 'default_infocom_alert',
           'name'               => __('Default value for alarms on financial and administrative information'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '50',
           'table'              => $this->getTable(),
           'field'              => 'default_cartridges_alarm_threshold',
           'name'               => __('Default threshold for cartridges count'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'number'
        ];

        $tab[] = [
           'id'                 => '52',
           'table'              => $this->getTable(),
           'field'              => 'default_consumables_alarm_threshold',
           'name'               => __('Default threshold for consumables count'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'number'
        ];

        $tab[] = [
           'id'                 => '57',
           'table'              => $this->getTable(),
           'field'              => 'use_certificates_alert',
           'name'               => __('Alarms on expired certificates'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '58',
           'table'              => $this->getTable(),
           'field'              => 'send_certificates_alert_before_delay',
           'name'               => __('Send Certificate alarms before'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => 'helpdesk',
           'name'               => __('Assistance')
        ];

        $tab[] = [
           'id'                 => '47',
           'table'              => $this->getTable(),
           'field'              => 'tickettemplates_id', // not a dropdown because of special value
           'name'               => _n('Ticket template', 'Ticket templates', 1),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '33',
           'table'              => $this->getTable(),
           'field'              => 'autoclose_delay',
           'name'               => __('Automatic closing of solved tickets after'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'number',
           'min'                => 1,
           'max'                => 99,
           'step'               => 1,
           'unit'               => 'day',
           'toadd'              => [
              self::CONFIG_PARENT  => __('Inheritance of the parent entity'),
              self::CONFIG_NEVER   => __('Never'),
              0                  => __('Immediatly')
           ]
        ];

        $tab[] = [
           'id'                 => '59',
           'table'              => $this->getTable(),
           'field'              => 'autopurge_delay',
           'name'               => __('Automatic purge of closed tickets after'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'number',
           'min'                => 1,
           'max'                => 3650,
           'step'               => 1,
           'unit'               => 'day',
           'toadd'              => [
              self::CONFIG_PARENT  => __('Inheritance of the parent entity'),
              self::CONFIG_NEVER   => __('Never'),
              0                  => __('Immediatly')
           ]
        ];

        $tab[] = [
           'id'                 => '34',
           'table'              => $this->getTable(),
           'field'              => 'notclosed_delay',
           'name'               => __('Alerts on tickets which are not solved'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '35',
           'table'              => $this->getTable(),
           'field'              => 'auto_assign_mode',
           'name'               => __('Automatic assignment of tickets'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '36',
           'table'              => $this->getTable(),
           'field'              => 'calendars_id',// not a dropdown because of special valu
           'name'               => _n('Calendar', 'Calendars', 1),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '37',
           'table'              => $this->getTable(),
           'field'              => 'tickettype',
           'name'               => __('Tickets default type'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => 'assets',
           'name'               => __('Assets')
        ];

        $tab[] = [
           'id'                 => '38',
           'table'              => $this->getTable(),
           'field'              => 'autofill_buy_date',
           'name'               => __('Date of purchase'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '39',
           'table'              => $this->getTable(),
           'field'              => 'autofill_order_date',
           'name'               => __('Order date'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '40',
           'table'              => $this->getTable(),
           'field'              => 'autofill_delivery_date',
           'name'               => __('Delivery date'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '41',
           'table'              => $this->getTable(),
           'field'              => 'autofill_use_date',
           'name'               => __('Startup date'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '42',
           'table'              => $this->getTable(),
           'field'              => 'autofill_warranty_date',
           'name'               => __('Start date of warranty'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '43',
           'table'              => $this->getTable(),
           'field'              => 'inquest_config',
           'name'               => __('Satisfaction survey configuration'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '44',
           'table'              => $this->getTable(),
           'field'              => 'inquest_rate',
           'name'               => __('Satisfaction survey trigger rate'),
           'massiveaction'      => false,
           'datatype'           => 'number'
        ];

        $tab[] = [
           'id'                 => '45',
           'table'              => $this->getTable(),
           'field'              => 'inquest_delay',
           'name'               => __('Create survey after'),
           'massiveaction'      => false,
           'datatype'           => 'number'
        ];

        $tab[] = [
           'id'                 => '46',
           'table'              => $this->getTable(),
           'field'              => 'inquest_URL',
           'name'               => __('URL'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '51',
           'table'              => $this->getTable(),
           'field'              => 'name',
           'linkfield'          => 'entities_id_software', // not a dropdown because of special value
                                   //TRANS: software in plural
           'name'               => __('Entity for software creation'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        $tab[] = [
           'id'                 => '56',
           'table'              => $this->getTable(),
           'field'              => 'autofill_decommission_date',
           'name'               => __('Decommission date'),
           'massiveaction'      => false,
           'nosearch'           => true,
           'datatype'           => 'specific'
        ];

        return $tab;
    }


    /**
     * Display entities of the loaded profile
     *
     * @param string $target target for entity change action
     * @param string $myname select name
    **/
    public static function showSelector($target, $myname)
    {
        global $CFG_GLPI;

        $rand = mt_rand();

        if (Session::getCurrentInterface() == 'helpdesk') {
            $actionurl = $CFG_GLPI["root_doc"] . "/front/helpdesk.public.php?active_entity=";
        } else {
            $actionurl = $CFG_GLPI["root_doc"] . "/front/central.php?active_entity=";
        }

        echo "<div class='center'>";
        echo "<span class='b'>" . __('Select the desired entity') . "<br>( <img src='" . $CFG_GLPI["root_doc"] .
              "/pics/entity_all.png' alt=''> " . __s('to see the entity and its sub-entities') . ")</span>" .
              "<br>";
        echo "<a style='font-size:14px;' href='" . $target . "?active_entity=all' title=\"" .
               __s('Show all') . "\">" . str_replace(" ", "&nbsp;", __('Show all')) . "</a></div>";

        echo "<div class='left' style='width:100%'>";
        echo "<form aria-label='Entity Search' id='entsearchform'>";
        echo Html::input('entsearchtext', ['id' => 'entsearchtext']);
        echo Html::submit(__('Search'), ['id' => 'entsearch']);
        echo "</form>";

        echo "<script type='text/javascript'>";
        echo "   $(function() {
                  $.getScript('{$CFG_GLPI["root_doc"]}/public/lib/jstree.js').done(function(data, textStatus, jqxhr) {
                     $('#tree_projectcategory$rand')
                     // call `.jstree` with the options object
                     .jstree({
                        // the `plugins` array allows you to configure the active plugins on this instance
                        'plugins' : ['search', 'qload', 'conditionalselect'],
                        'search': {
                           'case_insensitive': true,
                           'show_only_matches': true,
                           'ajax': {
                              'type': 'POST',
                              'url': '" . $CFG_GLPI["root_doc"] . "/ajax/entitytreesearch.php'
                           }
                        },
                        'qload': {
                           'prevLimit': 50,
                           'nextLimit': 30,
                           'moreText': '" . __s('Load more entities...') . "'
                        },
                        'conditionalselect': function (node, event) {
                           if (node === false) {
                              return false;
                           }
                           var url = '$actionurl'+node.id;
                           if (event.target.tagName == 'I'
                               && event.target.className == '') {
                              url += '&is_recursive=1';
                           }
                           document.location.href = url;
                           return false;
                        },
                        'core': {
                           'themes': {
                              'name': 'glpi'
                           },
                           'animation': 0,
                           'data': {
                              'url': function(node) {
                                 return node.id === '#' ?
                                    '" . $CFG_GLPI["root_doc"] . "/ajax/entitytreesons.php?node=-1' :
                                    '" . $CFG_GLPI["root_doc"] . "/ajax/entitytreesons.php?node='+node.id;
                              }
                           }
                        }
                     });

                     var searchTree = function() {
                        " . Html::jsGetElementbyID("tree_projectcategory$rand") . ".jstree('close_all');;
                        " . Html::jsGetElementbyID("tree_projectcategory$rand") .
                          ".jstree('search'," . Html::jsGetDropdownValue('entsearchtext') . ");
                     }

                     $('#entsearchform').submit(function( event ) {
                        // cancel submit of entity search form
                        event.preventDefault();

                        // search
                        searchTree();
                     });

                     // autosearch on keypress (delayed and with min length)
                     $('#entsearchtext').keyup(function () {
                        var inputsearch = $(this);
                        typewatch(function () {
                           if (inputsearch.val().length >= 3) {
                              searchTree();
                           }
                        }, 500);
                     })
                     .focus();
                  });
               });";

        echo "</script>";

        echo "<div id='tree_projectcategory$rand' class='entity_tree' ></div>";
        echo "</div>";
    }


    /**
     * @since 0.83 (before addRule)
     *
     * @param $input array of values
    **/
    public function executeAddRule($input)
    {

        $this->check($_POST["affectentity"], UPDATE);

        $collection = RuleCollection::getClassByType($_POST['sub_type']);
        $rule       = $collection->getRuleClass($_POST['sub_type']);
        $ruleid     = $rule->add($_POST);

        if ($ruleid) {
            //Add an action associated to the rule
            $ruleAction = new RuleAction();

            //Action is : affect computer to this entity
            $ruleAction->addActionByAttributes(
                "assign",
                $ruleid,
                "entities_id",
                $_POST["affectentity"]
            );

            switch ($_POST['sub_type']) {
                case 'RuleRight':
                    if ($_POST["profiles_id"]) {
                        $ruleAction->addActionByAttributes(
                            "assign",
                            $ruleid,
                            "profiles_id",
                            $_POST["profiles_id"]
                        );
                    }
                    $ruleAction->addActionByAttributes(
                        "assign",
                        $ruleid,
                        "is_recursive",
                        $_POST["is_recursive"]
                    );
            }
        }

        Event::log(
            $ruleid,
            "rules",
            4,
            "setup",
            //TRANS: %s is the user login
            sprintf(__('%s adds the item'), $_SESSION["glpiname"])
        );

        Html::back();
    }


    /**
     * get all entities with a notification option set
     * manage CONFIG_PARENT (or NULL) value
     *
     * @param $field  String name of the field to search (>0)
     *
     * @return Array of id => value
    **/
    public static function getEntitiesToNotify($field)
    {
        $entities = [];

        // root entity first
        $ent = new self();
        if ($ent->getFromDB(0)) {  // always exists
            $val = $ent->getField($field);
            if ($val > 0) {
                $entities[0] = $val;
            }
        }

        // Others entities in level order (parent first)
        $request = self::getAdapter()->request([
           'SELECT' => [
              'id AS entity',
              'entities_id AS parent',
              $field
           ],
           'FROM'   => self::getTable(),
           'ORDER'  => 'level ASC'
        ]);

        while ($entitydata = $request->fetchAssociative()) {
            if (
                (is_null($entitydata[$field])
                 || ($entitydata[$field] == self::CONFIG_PARENT))
                && isset($entities[$entitydata['parent']])
            ) {
                // config inherit from parent
                $entities[$entitydata['entity']] = $entities[$entitydata['parent']];
            } elseif ($entitydata[$field] > 0) {
                // config found in entity
                $entities[$entitydata['entity']] = $entitydata[$field];
            }
        }

        return $entities;
    }


    /**
     * @since 0.84
     *
     * @param $entity Entity object
    **/
    public static function showStandardOptions(Entity $entity)
    {

        $con_spotted = false;
        $ID          = $entity->getField('id');
        if (!$entity->can($ID, READ)) {
            return false;
        }

        // Entity right applied
        $canedit = $entity->can($ID, UPDATE);

        $form = [
           'action' => $canedit ? Toolbox::getItemTypeFormURL(__CLASS__) : '',
           'buttons' => [
              $canedit ? [
                 'type'  => 'submit',
                 'name'  => 'update',
                 'value' => _sx('button', 'Save'),
                 'class' => 'btn btn-secondary'
              ] : []
           ],
           'content' => [
              __('Address') => [
                 'visible' => true,
                 'inputs' => [
                    [
                       'type'  => 'hidden',
                       'name'  => 'id',
                       'value' => $entity->getField('id'),
                    ],
                    Phone::getTypeName(1) => [
                       'type'  => 'text',
                       'name'  => 'phonenumber',
                       'value' => $entity->getField('phonenumber'),
                    ],
                    __('Fax') => [
                       'type'  => 'text',
                       'name'  => 'fax',
                       'value' => $entity->getField('fax'),
                    ],
                    __('Website') => [
                       'type'  => 'text',
                       'name'  => 'website',
                       'value' => $entity->getField('website'),
                    ],
                    __('Email') => [
                       'type'  => 'text',
                       'name'  => 'email',
                       'value' => $entity->getField('email'),
                    ],
                    __('Postal code') => [
                       'type'  => 'text',
                       'name'  => 'postcode',
                       'value' => $entity->getField('postcode'),
                    ],
                    __('City') => [
                       'type'  => 'text',
                       'name'  => 'town',
                       'value' => $entity->getField('town'),
                    ],
                    __('State') => [
                       'type'  => 'text',
                       'name'  => 'state',
                       'value' => $entity->getField('state'),
                    ],
                    __('Country') => [
                       'type'  => 'text',
                       'name'  => 'country',
                       'value' => $entity->getField('country'),
                    ],
                    __('Longitude') => [
                       'type'  => 'text',
                       'name'  => 'longitude',
                       'value' => $entity->getField('longitude'),
                    ],
                    __('Latitude') => [
                       'type'  => 'text',
                       'name'  => 'latitude',
                       'value' => $entity->getField('latitude'),
                    ],
                    __('Altitude') => [
                       'type'  => 'text',
                       'name'  => 'altitude',
                       'value' => $entity->getField('altitude'),
                    ],
                 ]
              ]
           ]
        ];
        ob_start();
        Plugin::doHook("pre_item_form", ['item' => $entity, 'options' => []]);

        Plugin::doHook("post_item_form", ['item' => $entity, 'options' => []]);
        $additionnal = ob_get_clean();
        renderTwigForm($form, $additionnal);
    }


    /**
     * @since 0.84 (before in entitydata.class)
     *
     * @param $entity Entity object
    **/
    public static function showAdvancedOptions(Entity $entity)
    {
        $con_spotted = false;
        $ID          = $entity->getField('id');
        if (!$entity->can($ID, READ)) {
            return false;
        }

        // Entity right applied (could be User::UPDATEAUTHENT)
        $canedit = $entity->can($ID, UPDATE);

        $form = [
           'action' => $canedit ? Toolbox::getItemTypeFormURL(__CLASS__) : '',
           'buttons' => [
              [
                 'type'  => 'submit',
                 'name'  => 'update',
                 'value' => _sx('button', 'Save'),
                 'class' => 'btn btn-secondary'
              ]
           ],
           'content' => [
              __('Values for the generic rules for assignment to entities') => [
                 'visible' => true,
                 'inputs' => [
                    [
                       'type'  => 'hidden',
                       'name'  => 'id',
                       'value' => $entity->getField('id'),
                    ],
                    '' => [
                       'content' => '<b>' . __('These parameters are used as actions in generic rules for assignment to entities') . '</b>',
                       'col_lg' => 12,
                       'col_md' => 12,
                    ],
                    __('Information in inventory tool (TAG) representing the entity') => [
                       'type'  => 'text',
                       'name'  => 'tag',
                       'value' => $entity->getField('tag'),
                       'col_lg' => 12,
                       'col_md' => 12,
                       'after' => ($ID > 0 && (empty($entity->getField('tag')) || $entity->getField('tag') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getUsedConfig('tag', $ID), false, false) : '',
                    ],
                    __('LDAP directory information attribute representing the entity') => (Toolbox::canUseLdap()) ? [
                       'type'  => 'text',
                       'name'  => 'ldap_dn',
                       'value' => $entity->getField('ldap_dn'),
                       'col_lg' => 12,
                       'col_md' => 12,
                       'after' => ($ID > 0 && (empty($entity->getField('ldap_dn')) || $entity->getField('ldap_dn') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getUsedConfig('ldap_dn', $ID), false, false) : '',
                    ] : [],
                    __('Mail domain surrogates entity') => [
                       'type'  => 'text',
                       'name'  => 'mail_domain',
                       'value' => $entity->getField('mail_domain'),
                       'col_lg' => 12,
                       'col_md' => 12,
                       'after' => ($ID > 0 && (empty($entity->getField('mail_domain')) || $entity->getField('mail_domain') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getUsedConfig('mail_domain', $ID), false, false) : '',
                    ],
                 ]
              ],
              __('Values used in the interface to search users from a LDAP directory') => (Toolbox::canUseLdap()) ? [
                 'visible' => true,
                 'inputs' => [
                    __('LDAP directory of an entity') => [
                       'type'  => 'select',
                       'name'  => 'authldaps_id',
                       'value' => $entity->getField('authldaps_id'),
                       'values' => array_merge([__('Default server')], getOptionForItems(AuthLDAP::class, ['is_active' => 1], false)),
                       'col_lg' => 12,
                       'col_md' => 12,
                       'actions' => getItemActionButtons(['info'], AuthLDAP::class),
                       'after' => ($ID > 0 && ($entity->getField('authldaps_id') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('authldaps_id', ['authldaps_id' => self::getUsedConfig('authldaps_id', $ID)]), false, false) : '',
                    ],
                    __('LDAP filter associated to the entity (if necessary)') => [
                       'type'  => 'text',
                       'name'  => 'entity_ldapfilter',
                       'value' => $entity->getField('entity_ldapfilter'),
                       'col_lg' => 12,
                       'col_md' => 12,
                       'after' => ($ID > 0 && (empty($entity->getField('entity_ldapfilter')) || $entity->getField('entity_ldapfilter') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getUsedConfig('entity_ldapfilter', $ID), false, false) : '',
                    ],
                 ]
              ] : [],
           ]
        ];
        ob_start();
        Plugin::doHook("pre_item_form", ['item' => $entity, 'options' => []]);
        Plugin::doHook("post_item_form", ['item' => $entity, 'options' => &$options]);
        $additionnal = ob_get_clean();
        renderTwigForm($form, $additionnal);
    }


    /**
     * @since 0.84 (before in entitydata.class)
     *
     * @param $entity Entity object
    **/
    public static function showInventoryOptions(Entity $entity)
    {

        $ID = $entity->getField('id');
        if (!$entity->can($ID, READ)) {
            return false;
        }

        // Notification right applied
        $canedit = (Infocom::canUpdate() && Session::haveAccessToEntity($ID));

        $options[0] = __('No autofill');
        if ($ID > 0) {
            $options[self::CONFIG_PARENT] = __('Inheritance of the parent entity');
        }
        $states = getAllDataFromTable('glpi_states');
        foreach ($states as $state) {
            $options[Infocom::ON_STATUS_CHANGE . '_' . $state['id']]
                        //TRANS: %s is the name of the state
               = sprintf(__('Fill when shifting to state %s'), $state['name']);
        }

        $options[Infocom::COPY_WARRANTY_DATE] = __('Copy the start date of warranty');

        $entities = [self::CONFIG_NEVER => __('No change of entity')]; // Keep software in PC entity
        if ($ID > 0) {
            $entities[self::CONFIG_PARENT] = __('Inheritance of the parent entity');
        }
        foreach (getAncestorsOf('glpi_entities', $entity->fields['entities_id']) as $ent) {
            if (Session::haveAccessToEntity($ent)) {
                $entities[] = $ent;
            }
        }

        $form = [
           'action' => $canedit ? Toolbox::getItemTypeFormURL(__CLASS__) : '',
           'buttons' => [
              [
                 'type'  => 'submit',
                 'name'  => 'update',
                 'value' => _sx('button', 'Save'),
                 'class' => 'btn btn-secondary'
              ]
           ],
           'content' => [
              __('Autofill dates for financial and administrative information') => [
                 'visible' => true,
                 'inputs' => [
                    [
                       'type'  => 'hidden',
                       'name'  => 'id',
                       'value' => $entity->getField('id'),
                    ],
                    __('Date of purchase') => [
                       'type' => 'select',
                       'name' => 'autofill_buy_date',
                       'values' => $options,
                       'value' => $entity->getField('autofill_buy_date'),
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('autofill_buy_date') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('autofill_buy_date', ['autofill_buy_date' => self::getUsedConfig('autofill_buy_date', $ID)]), false, false) : '',
                    ],
                    __('Order date') => [
                       'type' => 'select',
                       'name' => 'autofill_order_date',
                       'values' => $options,
                       'value' => $entity->getField('autofill_order_date'),
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('autofill_order_date') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('autofill_order_date', ['autofill_order_date' => self::getUsedConfig('autofill_order_date', $ID)]), false, false) : '',
                    ],
                    __('Delivery date') => [
                       'type' => 'select',
                       'name' => 'autofill_delivery_date',
                       'values' => $options,
                       'value' => $entity->getField('autofill_delivery_date'),
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('autofill_delivery_date') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('autofill_delivery_date', ['autofill_delivery_date' => self::getUsedConfig('autofill_delivery_date', $ID)]), false, false) : '',
                    ],
                    __('Startup date') => [
                       'type' => 'select',
                       'name' => 'autofill_use_date',
                       'values' => $options,
                       'value' => $entity->getField('autofill_use_date'),
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('autofill_use_date') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('autofill_use_date', ['autofill_use_date' => self::getUsedConfig('autofill_use_date', $ID)]), false, false) : '',
                    ],
                    __('Start date of warranty') => [
                       'type' => 'select',
                       'name' => 'autofill_warranty_date',
                       'values' => $options,
                       'value' => $entity->getField('autofill_warranty_date'),
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('autofill_warranty_date') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('autofill_warranty_date', ['autofill_warranty_date' => self::getUsedConfig('autofill_warranty_date', $ID)]), false, false) : '',
                    ],
                    __('Decommission date') => [
                       'type' => 'select',
                       'name' => 'autofill_decommission_date',
                       'values' => $options,
                       'value' => $entity->getField('autofill_decommission_date'),
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('autofill_decommission_date') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('autofill_decommission_date', ['autofill_decommission_date' => self::getUsedConfig('autofill_decommission_date', $ID)]), false, false) : '',
                    ],
                 ]
                 ],
                 _n('Software', 'Software', Session::getPluralNumber()) => [
                    'visible' => true,
                    'inputs' => [
                       __('Entity for software creation') => [
                          'type' => 'select',
                          'name' => 'entities_id_software',
                          'values' => $entities,
                          'value' => $entity->getField('entities_id_software'),
                          'col_lg' => 6,
                          'after' => ($ID > 0 && ($entity->getField('entities_id_software') == self::CONFIG_PARENT)) ? 
                                     self::inheritedValue(self::getSpecificValueToDisplay('entities_id_software', ['entities_id_software' => self::getUsedConfig('entities_id_software', $ID)]), false, false) : '',
                       ],
                    ]
                 ]
           ]
        ];
        renderTwigForm($form);
    }

    /**
     * @since 0.84 (before in entitydata.class)
     *
     * @param $entity Entity object
    **/
    public static function showNotificationOptions(Entity $entity)
    {

        $ID = $entity->getField('id');
        if (
            !$entity->can($ID, READ)
            || !Notification::canView()
        ) {
            return false;
        }

        // Notification right applied
        $canedit = (Notification::canUpdate()
                    && Session::haveAccessToEntity($ID));

        $times = [];

        if ($ID > 0) {
            $times[Entity::CONFIG_PARENT] = __('Inheritance of the parent entity');
        }

        $times[Entity::CONFIG_NEVER]  = __('Never');
        $times[DAY_TIMESTAMP]         = __('Each day');
        $times[WEEK_TIMESTAMP]        = __('Each week');
        $times[MONTH_TIMESTAMP]       = __('Each month');

        $defaultContractOptions = Contract::getAlertName();
        if ($ID > 0) {
            $defaultContractOptions[Entity::CONFIG_PARENT] = __('Inheritance of the parent entity');
        }
        $form = [
           'action' => $canedit ? Toolbox::getItemTypeFormURL(__CLASS__) : '',
           'buttons' => [
              [
                 'type'  => 'submit',
                 'name'  => 'update',
                 'value' => _sx('button', 'Save'),
                 'class' => 'btn btn-secondary'
              ]
           ],
           'content' => [
              __('Notification options') => [
                 'visible' => true,
                 'inputs' => [
                    [
                       'type'  => 'hidden',
                       'name'  => 'id',
                       'value' => $entity->getField('id'),
                    ],
                    __('Administrator name') => [
                       'type'  => 'text',
                       'name'  => 'admin_email_name',
                       'value' => $entity->getField('admin_email_name'),
                       'after' => ($ID > 0 && (empty($entity->getField('admin_email_name')) || $entity->getField('admin_email_name') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getUsedConfig('admin_email_name', $ID), true, false) : '',
                    ],
                    __('Administrator email') => [
                       'type'  => 'text',
                       'name'  => 'admin_email',
                       'value' => $entity->getField('admin_email'),
                       'after' => ($ID > 0 && (empty($entity->getField('admin_email')) || $entity->getField('admin_email') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getUsedConfig('admin_email', $ID), true, false) : '',
                    ],
                    __('Administrator reply-to email (if needed)') => [
                       'type'  => 'text',
                       'name'  => 'admin_reply',
                       'value' => $entity->getField('admin_reply'),
                       'after' => ($ID > 0 && (empty($entity->getField('admin_reply')) || $entity->getField('admin_reply') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getUsedConfig('admin_reply', $ID), true, false) : '',
                    ],
                    __('Response address (if needed)') => [
                       'type'  => 'text',
                       'name'  => 'admin_reply_name',
                       'value' => $entity->getField('admin_reply_name'),
                       'after' => ($ID > 0 && (empty($entity->getField('admin_reply_name')) || $entity->getField('admin_reply_name') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getUsedConfig('admin_reply_name', $ID), true, false) : '',
                    ],
                    __('Prefix for notifications') => [
                       'type'  => 'text',
                       'name'  => 'notification_subject_tag',
                       'value' => $entity->getField('notification_subject_tag'),
                       'after' => ($ID > 0 && (empty($entity->getField('notification_subject_tag')) || $entity->getField('notification_subject_tag') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getUsedConfig('notification_subject_tag', $ID), true, false) : '',
                    ],
                    __('Delay to send email notifications') => [
                       'type'  => 'number',
                       'name'  => 'delay_send_emails',
                       'value' => $entity->getField('delay_send_emails'),
                       'min'   => 0,
                       'max'   => 100,
                       'after'  => 'minute' . (($ID > 0 && ($entity->getField('delay_send_emails') == self::CONFIG_PARENT)) ? 
                                   ' ' . self::inheritedValue(self::getSpecificValueToDisplay('delay_send_emails', ['delay_send_emails' => self::getUsedConfig('delay_send_emails', $ID)]), true, false) : ''),
                    ],
                    __('Enable notifications by default') => [
                       'type'  => 'checkbox',
                       'name'  => 'is_notif_enable_default',
                       'value' => $entity->getField('is_notif_enable_default'),
                       'after' => ($ID > 0 && ($entity->getField('is_notif_enable_default') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('is_notif_enable_default', ['is_notif_enable_default' => self::getUsedConfig('is_notif_enable_default', $ID)]), true, false) : '',
                    ],
                    __('Email signature') => [
                       'type'  => 'textarea',
                       'name'  => 'mailing_signature',
                       'value' => $entity->getField('mailing_signature'),
                       'col_lg' => 12,
                       'col_md' => 12,
                       'after' => ($ID > 0 && (empty($entity->getField('mailing_signature')) || $entity->getField('mailing_signature') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getUsedConfig('mailing_signature', $ID), true, false) : '',
                    ],
                 ]
              ],
              __('Alarms options') => ['visible' => true],
              _n('Cartridge', 'Cartridges', Session::getPluralNumber()) => [
                 'visible' => true,
                 'inputs' => [
                    _n('Cartridge', 'Cartridges', Session::getPluralNumber()) => [
                       'type'  => 'select',
                       'name'  => 'cartridges_alert_repeat',
                       'value' => $entity->getField('cartridges_alert_repeat'),
                       'values' => $times,
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('cartridges_alert_repeat') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('cartridges_alert_repeat', ['cartridges_alert_repeat' => self::getUsedConfig('cartridges_alert_repeat', $ID)]), true, false) : '',
                    ],
                    __('Default threshold for cartridges count') => [
                       'type'  => 'number',
                       'name'  => 'default_cartridges_alarm_threshold',
                       'value' => $entity->getField('default_cartridges_alarm_threshold'),
                       'min'   => 0,
                       'max'   => 100,
                       'step'  => 1,
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('default_cartridges_alarm_threshold') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('default_cartridges_alarm_threshold', ['default_cartridges_alarm_threshold' => self::getUsedConfig('default_cartridges_alarm_threshold', $ID)]), true, false) : '',
                    ],
                 ],
              ],
              _n('Consumable', 'Consumables', Session::getPluralNumber()) => [
                 'visible' => true,
                 'inputs' => [
                    _n('Consumable', 'Consumables', Session::getPluralNumber()) => [
                       'type'  => 'select',
                       'name'  => 'consumables_alert_repeat',
                       'value' => $entity->getField('consumables_alert_repeat'),
                       'values' => $times,
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('consumables_alert_repeat') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('consumables_alert_repeat', ['consumables_alert_repeat' => self::getUsedConfig('consumables_alert_repeat', $ID)]), true, false) : '',
                    ],
                    __('Default threshold for consumables count') => [
                       'type'  => 'number',
                       'name'  => 'default_consumables_alarm_threshold',
                       'value' => $entity->getField('default_consumables_alarm_threshold'),
                       'min'   => 0,
                       'max'   => 100,
                       'step'  => 1,
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('default_consumables_alarm_threshold') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('default_consumables_alarm_threshold', ['default_consumables_alarm_threshold' => self::getUsedConfig('default_consumables_alarm_threshold', $ID)]), true, false) : '',
                    ],
                 ],
              ],
              Contract::getTypeName() => [
                 'visible' => true,
                 'inputs' => [
                    __('Alarms on contracts') => [
                       'type'  => 'select',
                       'name'  => 'use_contracts_alert',
                       'value' => $entity->getField('use_contracts_alert'),
                       'values' => $times,
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('use_contracts_alert') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('use_contracts_alert', ['use_contracts_alert' => self::getUsedConfig('use_contracts_alert', $ID)]), true, false) : '',
                    ],
                    __('Default value') => [
                       'type'  => 'select',
                       'name'  => 'default_contract_alert',
                       'value' => $entity->getField('default_contract_alert'),
                       'values' => $defaultContractOptions,
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('default_contract_alert') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('default_contract_alert', ['default_contract_alert' => self::getUsedConfig('default_contract_alert', $ID)]), true, false) : '',
                    ],
                    __('Send contract alarms before') => [
                       'type'  => 'number',
                       'name'  => 'send_contracts_alert_before_delay',
                       'value' => $entity->getField('send_contracts_alert_before_delay'),
                       'after' => __('days') . (($ID > 0 && ($entity->getField('send_contracts_alert_before_delay') == self::CONFIG_PARENT)) ? 
                                  ' ' . self::inheritedValue(self::getSpecificValueToDisplay('send_contracts_alert_before_delay', ['send_contracts_alert_before_delay' => self::getUsedConfig('send_contracts_alert_before_delay', $ID)]), true, false) : ''),
                       'min'   => 0,
                    ],
                 ]
              ],
              __('Financial and administrative information') => [
                 'visible' => true,
                 'inputs' => [
                    __('Alarms on financial and administrative information') => [
                       'type' => 'checkbox',
                       'name' => 'use_infocoms_alert',
                       'value' => $entity->getField('use_infocoms_alert'),
                       'after' => ($ID > 0 && ($entity->getField('use_infocoms_alert') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('use_infocoms_alert', ['use_infocoms_alert' => self::getUsedConfig('use_infocoms_alert', $ID)]), true, false) : '',
                    ],
                    __('Default value') => [
                       'type' => 'select',
                       'name' => 'default_infocom_alert',
                       'value' => $entity->getField('default_infocom_alert'),
                       'values' => $defaultContractOptions,
                       'after' => ($ID > 0 && ($entity->getField('default_infocom_alert') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('default_infocom_alert', ['default_infocom_alert' => self::getUsedConfig('default_infocom_alert', $ID)]), true, false) : '',
                    ],
                    __('Send financial and administrative information alarms before') => [
                       'type' => 'number',
                       'name' => 'send_infocoms_alert_before_delay',
                       'value' => $entity->getField('send_infocoms_alert_before_delay'),
                       'after' => __('days') . (($ID > 0 && ($entity->getField('send_infocoms_alert_before_delay') == self::CONFIG_PARENT)) ? 
                                  ' ' . self::inheritedValue(self::getSpecificValueToDisplay('send_infocoms_alert_before_delay', ['send_infocoms_alert_before_delay' => self::getUsedConfig('send_infocoms_alert_before_delay', $ID)]), true, false) : ''),
                       'min'   => 0,
                    ],
                 ]
              ],
              SoftwareLicense::getTypeName(Session::getPluralNumber()) => [
                 'visible' => true,
                 'inputs' => [
                    __('Alarms on expired licenses') => [
                       'type' => 'checkbox',
                       'name' => 'use_licenses_alert',
                       'value' => $entity->getField('use_licenses_alert'),
                       'after' => ($ID > 0 && ($entity->getField('use_licenses_alert') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('use_licenses_alert', ['use_licenses_alert' => self::getUsedConfig('use_licenses_alert', $ID)]), true, false) : '',
                    ],
                    __('Send license alarms before') => [
                       'type' => 'number',
                       'name' => 'send_licenses_alert_before_delay',
                       'value' => $entity->getField('send_licenses_alert_before_delay'),
                       'after' => __('days') . (($ID > 0 && ($entity->getField('send_licenses_alert_before_delay') == self::CONFIG_PARENT)) ? 
                                  ' ' . self::inheritedValue(self::getSpecificValueToDisplay('send_licenses_alert_before_delay', ['send_licenses_alert_before_delay' => self::getUsedConfig('send_licenses_alert_before_delay', $ID)]), true, false) : ''),
                       'min'   => 0,
                    ],
                 ]
              ],
              _n('Certificate', 'Certificates', Session::getPluralNumber()) => [
                 'visible' => true,
                 'inputs' => [
                    __('Alarms on expired certificates') => [
                       'type' => 'checkbox',
                       'name' => 'use_certificates_alert',
                       'value' => $entity->getField('use_certificates_alert'),
                       'after' => ($ID > 0 && ($entity->getField('use_certificates_alert') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('use_certificates_alert', ['use_certificates_alert' => self::getUsedConfig('use_certificates_alert', $ID)]), true, false) : '',
                    ],
                    __('Send certificates alarms before') => [
                       'type' => 'number',
                       'name' => 'send_certificates_alert_before_delay',
                       'value' => $entity->getField('send_certificates_alert_before_delay'),
                       'after' => __('days') . (($ID > 0 && ($entity->getField('send_certificates_alert_before_delay') == self::CONFIG_PARENT)) ? 
                                  ' ' . self::inheritedValue(self::getSpecificValueToDisplay('send_certificates_alert_before_delay', ['send_certificates_alert_before_delay' => self::getUsedConfig('send_certificates_alert_before_delay', $ID)]), true, false) : ''),
                       'min'   => 0,
                    ],
                 ]
              ],
              _n('Reservation', 'Reservations', Session::getPluralNumber()) => [
                 'visible' => true,
                 'inputs' => [
                    __('Alarms on reservations') => [
                       'type' => 'number',
                       'name' => 'use_reservations_alert',
                       'value' => $entity->getField('use_reservations_alert'),
                       'after' => __('hours') . (($ID > 0 && ($entity->getField('use_reservations_alert') == self::CONFIG_PARENT)) ? 
                                  ' ' . self::inheritedValue(self::getSpecificValueToDisplay('use_reservations_alert', ['use_reservations_alert' => self::getUsedConfig('use_reservations_alert', $ID)]), true, false) : ''),
                       'min'   => 0,
                    ]
                 ]
              ],
              _n('Ticket', 'Tickets', Session::getPluralNumber()) => [
                 'visible' => true,
                 'inputs' => [
                    __('Alarms on tickets which are not solved since') => [
                       'type' => 'number',
                       'name' => 'notclosed_delay',
                       'value' => $entity->getField('notclosed_delay'),
                       'after' => __('days') . (($ID > 0 && ($entity->getField('notclosed_delay') == self::CONFIG_PARENT)) ? 
                                  ' ' . self::inheritedValue(self::getSpecificValueToDisplay('notclosed_delay', ['notclosed_delay' => self::getUsedConfig('notclosed_delay', $ID)]), true, false) : ''),
                       'min'   => 0,
                    ]
                 ]
              ],
              Domain::getTypeName(Session::getPluralNumber()) => [
                 'visible' => true,
                 'inputs' => [
                    __('Alarms on domains expiries') => [
                       'type' => 'checkbox',
                       'name' => 'use_domains_alert',
                       'value' => $entity->getField('use_domains_alert'),
                       'after' => ($ID > 0 && ($entity->getField('use_domains_alert') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('use_domains_alert', ['use_domains_alert' => self::getUsedConfig('use_domains_alert', $ID)]), true, false) : '',
                    ],
                    __('Domains closes expiries') => [
                       'type' => 'number',
                       'name' => 'send_domains_alert_close_expiries_delay',
                       'value' => $entity->getField('send_domains_alert_close_expiries_delay'),
                       'after' => __('days') . (($ID > 0 && ($entity->getField('send_domains_alert_close_expiries_delay') == self::CONFIG_PARENT)) ? 
                                  ' ' . self::inheritedValue(self::getSpecificValueToDisplay('send_domains_alert_close_expiries_delay', ['send_domains_alert_close_expiries_delay' => self::getUsedConfig('send_domains_alert_close_expiries_delay', $ID)]), true, false) : ''),
                       'min'   => 0,
                    ],
                    __('Domains expired') => [
                       'type' => 'number',
                       'name' => 'send_domains_alert_expired_delay',
                       'value' => $entity->getField('send_domains_alert_expired_delay'),
                       'after' => __('days') . (($ID > 0 && ($entity->getField('send_domains_alert_expired_delay') == self::CONFIG_PARENT)) ? 
                                  ' ' . self::inheritedValue(self::getSpecificValueToDisplay('send_domains_alert_expired_delay', ['send_domains_alert_expired_delay' => self::getUsedConfig('send_domains_alert_expired_delay', $ID)]), true, false) : ''),
                       'min'   => 0,
                    ],
                 ]
              ]
           ]
        ];
        ob_start();
        Plugin::doHook("pre_item_form", ['item' => $entity, 'options' => []]);
        Plugin::doHook("post_item_form", ['item' => $entity, 'options' => &$options]);
        $additionnal = ob_get_clean();
        renderTwigForm($form, $additionnal);
    }

    public function getAdditionalFields()
    {
        return [
           __('As child of') => [
              'name'  => $this->getForeignKeyField(),
              'type'  => 'select',
              'itemtype' => $this->getType(),
              'condition' => ['entities_id' => -1],
              'display_emptychoice' => false,
              'used' => [$this->fields['id']],
              'value' => $this->fields['entities_id'],
           ]
        ];
    }

    /**
     * UI customization configuration form.
     *
     * @param $entity Entity object
     *
     * @return void
     *
     * @since 9.5.0
     */
    public static function showUiCustomizationOptions(Entity $entity)
    {

        global $CFG_GLPI;

        $ID = $entity->getField('id');
        if (!$entity->can($ID, READ) || !Session::haveRight(Config::$rightname, UPDATE)) {
            return false;
        }

        // Codemirror lib
        echo Html::css('public/lib/codemirror.css');
        echo Html::script("public/lib/codemirror.js");

        // Notification right applied
        $canedit = Session::haveRight(Config::$rightname, UPDATE)
           && Session::haveAccessToEntity($ID);

        echo "<div class='spaced'>";
        if ($canedit) {
            echo "<form aria-label='Customization Option' method='post' name=form action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "' data-track-changes='true'>";
        }

        echo "<table class='tab_cadre_fixe custom_css_configuration' aria-label='Customization option'>";

        Plugin::doHook("pre_item_form", ['item' => $entity, 'options' => []]);

        $rand = mt_rand();

        echo "<tr><th colspan='2'>" . __('UI options') . "</th></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Enable CSS customization') . "</td>";
        echo "<td>";
        $values = [];
        if (($ID > 0) ? 1 : 0) {
            $values[Entity::CONFIG_PARENT] = __('Inherits configuration from the parent entity');
        }
        $values[0] = __('No');
        $values[1] = __('Yes');
        echo Dropdown::showFromArray(
            'enable_custom_css',
            $values,
            [
              'display' => false,
              'rand'    => $rand,
              'value'   => $entity->fields['enable_custom_css']
            ]
        );
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'>";
        echo "<div id='custom_css_container' class='custom_css_container'>";
        $value = $entity->fields['enable_custom_css'];
        // wrap call in function to prevent modifying variables from current scope
        call_user_func(function () use ($value, $ID): void {
            $_POST  = [
               'enable_custom_css' => $value,
               'entities_id'       => $ID
            ];
            include GLPI_ROOT . '/ajax/entityCustomCssCode.php';
        });
        echo "</div>\n";
        echo "</td></tr>";

        Ajax::updateItemOnSelectEvent(
            'dropdown_enable_custom_css' . $rand,
            'custom_css_container',
            $CFG_GLPI['root_doc'] . '/ajax/entityCustomCssCode.php',
            [
              'enable_custom_css' => '__VALUE__',
              'entities_id'       => $ID
            ]
        );

        Plugin::doHook("post_item_form", ['item' => $entity, 'options' => &$options]);

        echo "</table>";

        if ($canedit) {
            echo "<div class='center'>";
            echo "<input type='hidden' name='id' value='" . $entity->fields["id"] . "'>";
            echo "<input type='submit' name='update' value=\"" . _sx('button', 'Save') . "\" class='btn btn-secondary'>";
            echo "</div>";
            Html::closeForm();
        }

        echo "</div>";
    }


    /**
     * Returns tag containing custom CSS code applied to entity.
     *
     * @return string
     */
    public function getCustomCssTag()
    {
        if (!isset($this->fields) || !is_array($this->fields)) {
            return '';
        }

        if (!isset($this->fields['id'])) {
            return '';
        }

        $entity_id = $this->fields['id'];

        $enable_custom_css = self::getUsedConfig(
            'enable_custom_css',
            $entity_id
        );


        if (!$enable_custom_css) {
            return '';
        }


        $custom_css_code = self::getUsedConfig(
            'enable_custom_css',
            $entity_id,
            'custom_css_code'
        );

        if (empty($custom_css_code)) {
            return '';
        }

        return '<style>' . strip_tags($custom_css_code) . '</style>';
    }

    /**
     * @since 0.84 (before in entitydata.class)
     *
     * @param string $field
     * @param string $value  must be addslashes
    **/
    private static function getEntityIDByField($field, $value)
    {
        $request = self::getAdapter()->request([
           'SELECT' => 'id',
           'FROM'   => self::getTable(),
           'WHERE'  => [$field => $value]
        ]);
        $result = $request->fetchAllAssociative();
        if (count($result) == 1) {
            return $result[0]['id'];
        }
        return -1;
    }


    /**
     * @since 0.84 (before in entitydata.class)
     *
     * @param $value
    **/
    public static function getEntityIDByDN($value)
    {
        return self::getEntityIDByField("ldap_dn", $value);
    }


    /**
     * @since 0.84
     *
     * @param $value
    **/
    public static function getEntityIDByCompletename($value)
    {
        return self::getEntityIDByField("completename", $value);
    }


    /**
     * @since 0.84 (before in entitydata.class)
     *
     * @param $value
    **/
    public static function getEntityIDByTag($value)
    {
        return self::getEntityIDByField("tag", $value);
    }


    /**
     * @since 0.84 (before in entitydata.class)
     *
     * @param $value
    **/
    public static function getEntityIDByDomain($value)
    {
        return self::getEntityIDByField("mail_domain", $value);
    }


    /**
     * @since 0.84 (before in entitydata.class)
     *
     * @param $entities_id
    **/
    public static function isEntityDirectoryConfigured($entities_id)
    {

        $entity = new self();

        if (
            $entity->getFromDB($entities_id)
            && ($entity->getField('authldaps_id') > 0)
        ) {
            return true;
        }

        //If there's a directory marked as default
        if (AuthLDAP::getDefault()) {
            return true;
        }
        return false;
    }


    /**
     * @since 0.84 (before in entitydata.class)
     *
     * @param $entity Entity object
    **/
    public static function showHelpdeskOptions(Entity $entity)
    {
        global $CFG_GLPI;

        $ID = $entity->getField('id');
        if (
            !$entity->can($ID, READ)
            || !Session::haveRightsOr(
                self::$rightname,
                [self::READHELPDESK, self::UPDATEHELPDESK]
            )
        ) {
            return false;
        }
        $canedit = (Session::haveRight(self::$rightname, self::UPDATEHELPDESK)
                    && Session::haveAccessToEntity($ID));

        $autoassign = self::getAutoAssignMode();
        if ($ID == 0) {
            unset($autoassign[self::CONFIG_PARENT]);
        }

        $supplierValues = self::getSuppliersAsPrivateValues();
        if ($ID == 0) { // Remove parent option for root entity
            unset($supplierValues[self::CONFIG_PARENT]);
        }
        $anonymizeValues = self::getAnonymizeSupportAgentsValues();
        if ($ID == 0) { // Remove parent option for root entity
            unset($anonymizeValues[self::CONFIG_PARENT]);
        }

        $form = [
           'action' => $canedit ? Toolbox::getItemTypeFormURL(__CLASS__) : '',
           'buttons' => [
              [
                 'type'  => 'submit',
                 'name'  => 'update',
                 'value' => _sx('button', 'Save'),
                 'class' => 'btn btn-secondary'
              ]
           ],
           'content' => [
              __('Templates configuration') => [
                 'visible' => true,
                 'inputs' => [
                    _n('Ticket template', 'Ticket templates', 1) => [
                       'type'  => 'select',
                       'name'  => 'tickettemplates_id',
                       'value' => $entity->getField('tickettemplates_id'),
                       'values' => array_merge(
                           ($ID != 0) ? [self::CONFIG_PARENT => __('Inheritance of the parent entity')] : [],
                           getOptionForItems(TicketTemplate::class)
                       ),
                       'actions' => getItemActionButtons(['info', 'add'], TicketTemplate::class),
                       'after' => ($ID > 0 && ($entity->getField('tickettemplates_id') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('tickettemplates_id', ['tickettemplates_id' => self::getUsedConfig('tickettemplates_id', $ID)]), false, false) : '',
                    ],
                    _n('Change template', 'Change templates', 1) => [
                       'type'  => 'select',
                       'name'  => 'changetemplates_id',
                       'value' => $entity->getField('changetemplates_id'),
                       'values' => array_merge(
                           ($ID != 0) ? [self::CONFIG_PARENT => __('Inheritance of the parent entity')] : [],
                           getOptionForItems(ChangeTemplate::class)
                       ),
                       'actions' => getItemActionButtons(['info', 'add'], ChangeTemplate::class),
                       'after' => ($ID > 0 && ($entity->getField('changetemplates_id') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('changetemplates_id', ['changetemplates_id' => self::getUsedConfig('changetemplates_id', $ID)]), false, false) : '',
                    ],
                    _n('Problem template', 'Problem templates', 1) => [
                       'type'  => 'select',
                       'name'  => 'problemtemplates_id',
                       'value' => $entity->getField('problemtemplates_id'),
                       'values' => array_merge(
                           ($ID != 0) ? [self::CONFIG_PARENT => __('Inheritance of the parent entity')] : [],
                           getOptionForItems(ProblemTemplate::class)
                       ),
                       'actions' => getItemActionButtons(['info', 'add'], ProblemTemplate::class),
                       'after' => ($ID > 0 && ($entity->getField('problemtemplates_id') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('problemtemplates_id', ['problemtemplates_id' => self::getUsedConfig('problemtemplates_id', $ID)]), false, false) : '',
                    ],
                 ]
              ],
              __('Tickets configuration') => [
               'visible' => true,
               'inputs' => [
                  _n('Calendar', 'Calendars', 1) => [
                       'type'  => 'select',
                       'name'  => 'calendars_id',
                       'value' => $entity->getField('calendars_id'),
                       'values' => array_merge(
                           [__('24/7')],
                           ($ID != 0) ? [self::CONFIG_PARENT => __('Inheritance of the parent entity')] : [],
                           getOptionForItems(Calendar::class, [], false)
                       ),
                       'actions' => getItemActionButtons(['info', 'add'], Calendar::class),
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('calendars_id') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('calendars_id', ['calendars_id' => self::getUsedConfig('calendars_id', $ID)]), false, false) : '',
                  ],
                  __('Tickets default type') => [
                       'type'  => 'select',
                       'name'  => 'tickettype',
                       'value' => $entity->fields["tickettype"],
                       'values' => (($ID != 0) ? [self::CONFIG_PARENT => __('Inheritance of the parent entity')] : []) +
                          Ticket::getTypes(),
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('tickettype') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('tickettype', ['tickettype' => self::getUsedConfig('tickettype', $ID)]), false, false) : '',
                  ],
                  __('Automatic assignment of tickets') => [
                       'type'  => 'select',
                       'name'  => 'auto_assign_mode',
                       'value' => $entity->fields["auto_assign_mode"],
                       'values' => $autoassign,
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('auto_assign_mode') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('auto_assign_mode', ['auto_assign_mode' => self::getUsedConfig('auto_assign_mode', $ID)]), false, false) : '',
                  ],
                  __('Mark followup added by a supplier though an email collector as private') => [
                       'type'  => 'select',
                       'name'  => 'suppliers_as_private',
                       'value' => $entity->fields["suppliers_as_private"],
                       'values' => $supplierValues,
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('suppliers_as_private') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('suppliers_as_private', ['suppliers_as_private' => self::getUsedConfig('suppliers_as_private', $ID)]), false, false) : '',
                  ],
                  __('Anonymize support agents') => [
                       'type'  => 'select',
                       'name'  => 'anonymize_support_agents',
                       'value' => $entity->fields["anonymize_support_agents"],
                       'values' => $anonymizeValues,
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('anonymize_support_agents') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('anonymize_support_agents', ['anonymize_support_agents' => self::getUsedConfig('anonymize_support_agents', $ID)]), false, false) : '',
                  ],
               ]
              ],
              __('Automatic closing configuration') => [
               'visible' => true,
               'inputs' => [
                  __('Automatic closing of solved tickets after') => [
                       'type'  => 'select',
                       'name'  => 'autoclose_delay',
                       'value' => $entity->fields['autoclose_delay'],
                       'values' => ($ID != 0) ? [self::CONFIG_PARENT => __('Inheritance of the parent entity')] : [] +
                          [self::CONFIG_NEVER => __('Never')] +
                          range(1, 99),
                       'after' => __('days') . (($ID > 0 && ($entity->getField('autoclose_delay') == self::CONFIG_PARENT)) ? 
                                  ' ' . self::inheritedValue(self::getSpecificValueToDisplay('autoclose_delay', ['autoclose_delay' => self::getUsedConfig('autoclose_delay', $ID)]), false, false) : ''),
                       'col_lg' => 6,
                  ],
                  __('Automatic purge of closed tickets after') => [
                       'type'  => 'select',
                       'name'  => 'autopurge_delay',
                       'value' => $entity->fields['autopurge_delay'],
                       'values' => ($ID != 0) ? [self::CONFIG_PARENT => __('Inheritance of the parent entity')] : [] +
                          [self::CONFIG_NEVER => __('Never')] +
                          range(1, 3650),
                       'after' => __('days') . (($ID > 0 && ($entity->getField('autopurge_delay') == self::CONFIG_PARENT)) ? 
                                  ' ' . self::inheritedValue(self::getSpecificValueToDisplay('autopurge_delay', ['autopurge_delay' => self::getUsedConfig('autopurge_delay', $ID)]), false, false) : ''),
                       'col_lg' => 6,
                  ],
               ]
              ],
              __('Configuring the satisfaction survey') => [
               'visible' => true,
               'inputs' => [
                  [
                       'type'  => 'hidden',
                       'name'  => 'id',
                       'value' => $entity->fields["id"],
                  ],
                  __('Configuring the satisfaction survey') => [
                       'type'  => 'select',
                       'name'  => 'inquest_config',
                       'value' => $entity->fields['inquest_config'],
                       'values' => ($ID != 0) ? [self::CONFIG_PARENT => __('Inheritance of the parent entity')] : [] +
                          [1 => __('Internal survey')] +
                          [2 => __('External survey')],
                       'col_lg' => 6,
                       'after' => ($ID > 0 && ($entity->getField('inquest_config') == self::CONFIG_PARENT)) ? 
                                  self::inheritedValue(self::getSpecificValueToDisplay('inquest_config', ['inquest_config' => self::getUsedConfig('inquest_config', $ID)]), false, false) : '',
                  ],
                  __('Create survey after') => [
                       'type'  => 'select',
                       'name'  => 'inquest_delay',
                       'value' => $entity->getfield('inquest_delay'),
                       'values' => array_merge(
                           ($ID != 0) ? [self::CONFIG_PARENT => __('Inheritance of the parent entity')] : [],
                           [self::CONFIG_NEVER => __('As soon as possible')],
                           range(1, 99)
                       ),
                       'after' => __('days') . (($ID > 0 && ($entity->getField('inquest_delay') == self::CONFIG_PARENT)) ? 
                                  ' ' . self::inheritedValue(self::getSpecificValueToDisplay('inquest_delay', ['inquest_delay' => self::getUsedConfig('inquest_delay', $ID)]), false, false) : ''),
                       'col_lg' => 6,
                  ],
                  __('Rate to trigger survey') => [
                       'type'  => 'number',
                       'name'  => 'inquest_rate',
                       'value' => $entity->getfield('inquest_rate'),
                       'col_lg' => 6,
                       'min'   => 0,
                       'max'   => 100,
                       'step'  => 1,
                       'after' => '%' . (($ID > 0 && ($entity->getField('inquest_rate') == self::CONFIG_PARENT)) ? 
                                  ' ' . self::inheritedValue(self::getUsedConfig('inquest_rate', $ID) . '%', false, false) : ''),
                  ],
                  __('Duration of survey') => [
                       'type'  => 'number',
                       'name'  => 'inquest_duration',
                       'value' => $entity->getfield('inquest_duration'),
                       'col_lg' => 6,
                       'min'   => 0,
                       'max'   => 180,
                       'step'  => 1,
                       'after' => __('days') . (($ID > 0 && ($entity->getField('inquest_duration') == self::CONFIG_PARENT)) ? 
                                  ' ' . self::inheritedValue(self::getUsedConfig('inquest_duration', $ID) . ' ' . __('days'), false, false) : ''),
                  ],
                  __('For tickets closed after') => [
                       'type'  => 'datetime-local',
                       'name'  => 'max_closedate',
                       'value' => $entity->getfield('max_closedate'),
                       'col_lg' => 6,
                  ],
               ]
              ]
           ]
        ];
        renderTwigForm($form);

        Plugin::doHook("pre_item_form", ['item' => $entity, 'options' => []]);
        Plugin::doHook("post_item_form", ['item' => $entity, 'options' => &$options]);
    }


    /**
     * Retrieve data of current entity or parent entity
     *
     * @since 0.84 (before in entitydata.class)
     *
     * @param string  $fieldref       name of the referent field to know if we look at parent entity
     * @param integer $entities_id
     * @param string  $fieldval       name of the field that we want value (default '')
     * @param mixed   $default_value  value to return (default -2)
    **/
    public static function getUsedConfig($fieldref, $entities_id = null, $fieldval = '', $default_value = -2)
    {

        // Get for current entity
        if ($entities_id === null) {
            $entities_id = Session::getActiveEntity();
        }

        // for calendar
        if (empty($fieldval)) {
            $fieldval = $fieldref;
        }

        $entity = new self();
        // Search in entity data of the current entity
        if (!empty($entities_id) && ctype_digit((string)$entities_id)) {
            if ($entity->getFromDB((int)$entities_id)) {
                // Value is defined : use it
                if (isset($entity->fields[$fieldref])) {
                    // Numerical value
                    if (
                        is_numeric($default_value)
                        && ($entity->fields[$fieldref] != self::CONFIG_PARENT)
                    ) {
                        return $entity->fields[$fieldval];
                    }
                    // String value
                    if (
                        !is_numeric($default_value)
                        && $entity->fields[$fieldref]
                    ) {
                        return $entity->fields[$fieldval];
                    }
                }
            }
            // Entity data not found or not defined : search in parent one
            if ($entities_id > 0) {
                if ($entity->getFromDB($entities_id)) {
                    $parent_id = isset($entity->fields['entities_id']) ? $entity->fields['entities_id'] : null;

                    if ($parent_id > 0) {
                        $ret = self::getUsedConfig(
                            $fieldref,
                            $parent_id,
                            $fieldval,
                            $default_value
                        );
                        return $ret;
                    }
                }
            }
        }
        return $default_value;
    }


    /**
     * Generate link for ticket satisfaction
     *
     * @since 0.84 (before in entitydata.class)
     *
     * @param $ticket ticket object
     *
     * @return string url contents
    **/
    public static function generateLinkSatisfaction($ticket)
    {
        $url = self::getUsedConfig('inquest_config', $ticket->fields['entities_id'], 'inquest_URL');

        if (strstr($url, "[TICKET_ID]")) {
            $url = str_replace("[TICKET_ID]", $ticket->fields['id'], $url);
        }

        if (strstr($url, "[TICKET_NAME]")) {
            $url = str_replace("[TICKET_NAME]", urlencode($ticket->fields['name']), $url);
        }

        if (strstr($url, "[TICKET_CREATEDATE]")) {
            $url = str_replace("[TICKET_CREATEDATE]", $ticket->fields['date'], $url);
        }

        if (strstr($url, "[TICKET_SOLVEDATE]")) {
            $url = str_replace("[TICKET_SOLVEDATE]", $ticket->fields['solvedate'], $url);
        }

        if (strstr($url, "[REQUESTTYPE_ID]")) {
            $url = str_replace("[REQUESTTYPE_ID]", $ticket->fields['requesttypes_id'], $url);
        }

        if (strstr($url, "[REQUESTTYPE_NAME]")) {
            $url = str_replace(
                "[REQUESTTYPE_NAME]",
                urlencode(Dropdown::getDropdownName(
                    'glpi_requesttypes',
                    $ticket->fields['requesttypes_id']
                )),
                $url
            );
        }

        if (strstr($url, "[TICKET_PRIORITY]")) {
            $url = str_replace("[TICKET_PRIORITY]", $ticket->fields['priority'], $url);
        }

        if (strstr($url, "[TICKET_PRIORITYNAME]")) {
            $url = str_replace(
                "[TICKET_PRIORITYNAME]",
                urlencode(CommonITILObject::getPriorityName($ticket->fields['priority'])),
                $url
            );
        }

        if (strstr($url, "[TICKETCATEGORY_ID]")) {
            $url = str_replace("[TICKETCATEGORY_ID]", $ticket->fields['itilcategories_id'], $url);
        }

        if (strstr($url, "[TICKETCATEGORY_NAME]")) {
            $url = str_replace(
                "[TICKETCATEGORY_NAME]",
                urlencode(Dropdown::getDropdownName(
                    'glpi_itilcategories',
                    $ticket->fields['itilcategories_id']
                )),
                $url
            );
        }

        if (strstr($url, "[TICKETTYPE_ID]")) {
            $url = str_replace("[TICKETTYPE_ID]", $ticket->fields['type'], $url);
        }

        if (strstr($url, "[TICKET_TYPENAME]")) {
            $url = str_replace(
                "[TICKET_TYPENAME]",
                Ticket::getTicketTypeName($ticket->fields['type']),
                $url
            );
        }

        if (strstr($url, "[SOLUTIONTYPE_ID]")) {
            $url = str_replace("[SOLUTIONTYPE_ID]", $ticket->fields['solutiontypes_id'], $url);
        }

        if (strstr($url, "[SOLUTIONTYPE_NAME]")) {
            $url = str_replace(
                "[SOLUTIONTYPE_NAME]",
                urlencode(Dropdown::getDropdownName(
                    'glpi_solutiontypes',
                    $ticket->fields['solutiontypes_id']
                )),
                $url
            );
        }

        if (strstr($url, "[SLA_TTO_ID]")) {
            $url = str_replace("[SLA_TTO_ID]", $ticket->fields['slas_id_tto'], $url);
        }

        if (strstr($url, "[SLA_TTO_NAME]")) {
            $url = str_replace(
                "[SLA_TTO_NAME]",
                urlencode(Dropdown::getDropdownName(
                    'glpi_slas',
                    $ticket->fields['slas_id_tto']
                )),
                $url
            );
        }

        if (strstr($url, "[SLA_TTR_ID]")) {
            $url = str_replace("[SLA_TTR_ID]", $ticket->fields['slas_id_ttr'], $url);
        }

        if (strstr($url, "[SLA_TTR_NAME]")) {
            $url = str_replace(
                "[SLA_TTR_NAME]",
                urlencode(Dropdown::getDropdownName(
                    'glpi_slas',
                    $ticket->fields['slas_id_ttr']
                )),
                $url
            );
        }

        if (strstr($url, "[SLALEVEL_ID]")) {
            $url = str_replace("[SLALEVEL_ID]", $ticket->fields['slalevels_id_ttr'], $url);
        }

        if (strstr($url, "[SLALEVEL_NAME]")) {
            $url = str_replace(
                "[SLALEVEL_NAME]",
                urlencode(Dropdown::getDropdownName(
                    'glpi_slalevels',
                    $ticket->fields['slalevels_id_ttr']
                )),
                $url
            );
        }

        return $url;
    }

    /**
     * get value for auto_assign_mode
     *
     * @since 0.84 (created in version 0.83 in entitydata.class)
     *
     * @param integer|null $val if not set, ask for all values, else for 1 value (default NULL)
     *
     * @return string|array
    **/
    public static function getAutoAssignMode($val = null)
    {

        $tab = [self::CONFIG_PARENT                  => __('Inheritance of the parent entity'),
                     self::CONFIG_NEVER                   => __('No'),
                     self::AUTO_ASSIGN_HARDWARE_CATEGORY  => __('Based on the item then the category'),
                     self::AUTO_ASSIGN_CATEGORY_HARDWARE  => __('Based on the category then the item')];

        if (is_null($val)) {
            return $tab;
        }
        if (isset($tab[$val])) {
            return $tab[$val];
        }
        return NOT_AVAILABLE;
    }

    /**
     * get value for suppliers_as_private
     *
     * @since 9.5
     *
     * @param integer|null $val if not set, ask for all values, else for 1 value (default NULL)
     *
     * @return string|array
    **/
    public static function getSuppliersAsPrivateValues()
    {

        return [
           self::CONFIG_PARENT => __('Inheritance of the parent entity'),
           0                   => __('No'),
           1                   => __('Yes'),
        ];
    }

    /**
     * Get values for anonymize_support_agents
     *
     * @since 9.5
     *
     * @return array
    **/
    public static function getAnonymizeSupportAgentsValues()
    {

        return [
           self::CONFIG_PARENT => __('Inheritance of the parent entity'),
           0 => __('No'),
           1 => __('Yes'),
        ];
    }

    /**
     * @since 0.84
     *
     * @param $options array
    **/
    public static function dropdownAutoAssignMode(array $options)
    {

        $p['name']    = 'auto_assign_mode';
        $p['value']   = 0;
        $p['display'] = true;

        if (count($options)) {
            foreach ($options as $key => $val) {
                $p[$key] = $val;
            }
        }

        $tab = self::getAutoAssignMode();
        return Dropdown::showFromArray($p['name'], $tab, $p);
    }


    /**
     * @since 0.84 (before in entitydata.class)
     *
     * @param $field
     * @param $values
     * @param $options   array
    **/
    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'use_licenses_alert':
            case 'use_certificates_alert':
            case 'use_contracts_alert':
            case 'use_domains_alert':
            case 'use_infocoms_alert':
            case 'is_notif_enable_default':
                if ($values[$field] == self::CONFIG_PARENT) {
                    return __('Inheritance of the parent entity');
                }
                return Dropdown::getYesNo($values[$field]);

            case 'use_reservations_alert':
                switch ($values[$field]) {
                    case self::CONFIG_PARENT:
                        return __('Inheritance of the parent entity');

                    case 0:
                        return __('Never');
                }
                return sprintf(_n('%d hour', '%d hours', $values[$field]), $values[$field]);

            case 'default_cartridges_alarm_threshold':
            case 'default_consumables_alarm_threshold':
                switch ($values[$field]) {
                    case self::CONFIG_PARENT:
                        return __('Inheritance of the parent entity');

                    case 0:
                        return __('Never');
                }
                return $values[$field];

            case 'send_contracts_alert_before_delay':
            case 'send_infocoms_alert_before_delay':
            case 'send_licenses_alert_before_delay':
            case 'send_certificates_alert_before_delay':
            case 'send_domains_alert_close_expiries_delay':
            case 'send_domains_alert_expired_delay':
                switch ($values[$field]) {
                    case self::CONFIG_PARENT:
                        return __('Inheritance of the parent entity');

                    case 0:
                        return __('No');
                }
                return sprintf(_n('%d day', '%d days', $values[$field]), $values[$field]);

            case 'cartridges_alert_repeat':
            case 'consumables_alert_repeat':
                switch ($values[$field]) {
                    case self::CONFIG_PARENT:
                        return __('Inheritance of the parent entity');

                    case self::CONFIG_NEVER:
                    case 0: // For compatibility issue
                        return __('Never');

                    case DAY_TIMESTAMP:
                        return __('Each day');

                    case WEEK_TIMESTAMP:
                        return __('Each week');

                    case MONTH_TIMESTAMP:
                        return __('Each month');

                    default:
                        // Display value if not defined
                        return $values[$field];
                }
                break;

            case 'notclosed_delay':   // 0 means never
                switch ($values[$field]) {
                    case self::CONFIG_PARENT:
                        return __('Inheritance of the parent entity');

                    case 0:
                        return __('Never');
                }
                return sprintf(_n('%d day', '%d days', $values[$field]), $values[$field]);

            case 'auto_assign_mode':
                return self::getAutoAssignMode($values[$field]);

            case 'tickettype':
                if ($values[$field] == self::CONFIG_PARENT) {
                    return __('Inheritance of the parent entity');
                }
                return Ticket::getTicketTypeName($values[$field]);

            case 'autofill_buy_date':
            case 'autofill_order_date':
            case 'autofill_delivery_date':
            case 'autofill_use_date':
            case 'autofill_warranty_date':
            case 'autofill_decommission_date':
                switch ($values[$field]) {
                    case self::CONFIG_PARENT:
                        return __('Inheritance of the parent entity');

                    case Infocom::COPY_WARRANTY_DATE:
                        return __('Copy the start date of warranty');

                    case Infocom::COPY_BUY_DATE:
                        return __('Copy the date of purchase');

                    case Infocom::COPY_ORDER_DATE:
                        return __('Copy the order date');

                    case Infocom::COPY_DELIVERY_DATE:
                        return __('Copy the delivery date');

                    default:
                        if (strstr($values[$field], '_')) {
                            list($type, $sid) = explode('_', $values[$field], 2);
                            if ($type == Infocom::ON_STATUS_CHANGE) {
                                // TRANS %s is the name of the state
                                return sprintf(
                                    __('Fill when shifting to state %s'),
                                    Dropdown::getDropdownName('glpi_states', $sid)
                                );
                            }
                        }
                }
                return __('No autofill');

            case 'inquest_config':
                if ($values[$field] == self::CONFIG_PARENT) {
                    return __('Inheritance of the parent entity');
                }
                return TicketSatisfaction::getTypeInquestName($values[$field]);

            case 'default_contract_alert':
                return Contract::getAlertName($values[$field]);

            case 'default_infocom_alert':
                return Infocom::getAlertName($values[$field]);

            case 'entities_id_software':
                if ($values[$field] == self::CONFIG_NEVER) {
                    return __('No change of entity');
                }
                if ($values[$field] == self::CONFIG_PARENT) {
                    return __('Inheritance of the parent entity');
                }
                return Dropdown::getDropdownName('glpi_entities', $values[$field]);

            case 'tickettemplates_id':
                if ($values[$field] == self::CONFIG_PARENT) {
                    return __('Inheritance of the parent entity');
                }
                return Dropdown::getDropdownName(TicketTemplate::getTable(), $values[$field]);

            case 'calendars_id':
                switch ($values[$field]) {
                    case self::CONFIG_PARENT:
                        return __('Inheritance of the parent entity');

                    case 0:
                        return __('24/7');
                }
                return Dropdown::getDropdownName('glpi_calendars', $values[$field]);
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }


    /**
     * @since 0.84
     *
     * @param $field
     * @param $name               (default '')
     * @param $values             (default '')
     * @param $options      array
    **/
    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;
        switch ($field) {
            case 'use_licenses_alert':
            case 'use_certificates_alert':
            case 'use_contracts_alert':
            case 'use_infocoms_alert':
                $options['name']  = $name;
                $options['value'] = $values[$field];
                return Alert::dropdownYesNo($options);

            case 'cartridges_alert_repeat':
            case 'consumables_alert_repeat':
                $options['name']  = $name;
                $options['value'] = $values[$field];
                return Alert::dropdown($options);

            case 'send_contracts_alert_before_delay':
            case 'send_infocoms_alert_before_delay':
            case 'send_licenses_alert_before_delay':
            case 'send_certificates_alert_before_delay':
                $options['unit']         = 'day';
                $options['never_string'] = __('No');
                return Alert::dropdownIntegerNever($name, $values[$field], $options);

            case 'use_reservations_alert':
                $options['unit']  = 'hour';
                return Alert::dropdownIntegerNever($name, $values[$field], $options);

            case 'notclosed_delay':
                $options['unit']  = 'hour';
                return Alert::dropdownIntegerNever($name, $values[$field], $options);

            case 'auto_assign_mode':
                $options['name']  = $name;
                $options['value'] = $values[$field];

                return self::dropdownAutoAssignMode($options);

            case 'tickettype':
                $options['value'] = $values[$field];
                $options['toadd'] = [self::CONFIG_PARENT => __('Inheritance of the parent entity')];
                return Ticket::dropdownType($name, $options);

            case 'autofill_buy_date':
            case 'autofill_order_date':
            case 'autofill_delivery_date':
            case 'autofill_use_date':
            case 'autofill_decommission_date':
                $tab[0]                   = __('No autofill');
                $tab[self::CONFIG_PARENT] = __('Inheritance of the parent entity');
                $states = getAllDataFromTable('glpi_states');
                foreach ($states as $state) {
                    $tab[Infocom::ON_STATUS_CHANGE . '_' . $state['id']]
                                //TRANS: %s is the name of the state
                       = sprintf(__('Fill when shifting to state %s'), $state['name']);
                }
                $tab[Infocom::COPY_WARRANTY_DATE] = __('Copy the start date of warranty');
                if ($field != 'autofill_buy_date') {
                    $tab[Infocom::COPY_BUY_DATE] = __('Copy the date of purchase');
                    if ($field != 'autofill_order_date') {
                        $tab[Infocom::COPY_ORDER_DATE] = __('Copy the order date');
                        if ($field != 'autofill_delivery_date') {
                            $options[Infocom::COPY_DELIVERY_DATE] = __('Copy the delivery date');
                        }
                    }
                }
                $options['value'] = $values[$field];
                return Dropdown::showFromArray($name, $tab, $options);

            case 'autofill_warranty_date':
                $tab = [0                           => __('No autofill'),
                             Infocom::COPY_BUY_DATE      => __('Copy the date of purchase'),
                             Infocom::COPY_ORDER_DATE    => __('Copy the order date'),
                             Infocom::COPY_DELIVERY_DATE => __('Copy the delivery date'),
                             self::CONFIG_PARENT         => __('Inheritance of the parent entity')];
                $options['value'] = $values[$field];
                return Dropdown::showFromArray($name, $tab, $options);

            case 'inquest_config':
                $typeinquest = [self::CONFIG_PARENT  => __('Inheritance of the parent entity'),
                                     1                    => __('Internal survey'),
                                     2                    => __('External survey')];
                $options['value'] = $values[$field];
                return Dropdown::showFromArray($name, $typeinquest, $options);

            case 'default_contract_alert':
                $options['name']  = $name;
                $options['value'] = $values[$field];
                return Contract::dropdownAlert($options);

            case 'default_infocom_alert':
                $options['name']  = $name;
                $options['value'] = $values[$field];
                return Infocom::dropdownAlert($options);

            case 'entities_id_software':
                $options['toadd'] = [self::CONFIG_NEVER => __('No change of entity')]; // Keep software in PC entity
                $options['toadd'][self::CONFIG_PARENT] = __('Inheritance of the parent entity');

                return self::dropdown($options);
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }


    public function getRights($interface = 'central')
    {

        $values = parent::getRights();
        $values[self::READHELPDESK]   = ['short' => __('Read parameters'),
                                              'long'  => __('Read helpdesk parameters')];
        $values[self::UPDATEHELPDESK] = ['short' => __('Update parameters'),
                                              'long'  => __('Update helpdesk parameters')];

        return $values;
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

    public static function inheritedValue($value = "", bool $inline = false, bool $display = true): string
    {
        if (trim($value) == "") {
            return "";
        }

        $out = "<div class='inherited " . ($inline ? "inline" : "") . "'
                   title='" . __("Value inherited from a parent entity") . "'>
         <i class='fas fa-level-down-alt' aria-hidden='true'></i>
         $value
      </div>";

        if ($display) {
            echo $out;
            return "";
        }

        return $out;
    }

    public static function getIcon()
    {
        return "fas fa-layer-group";
    }
}
