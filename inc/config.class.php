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
use Glpi\Cache\SimpleCache;
use PHPMailer\PHPMailer\PHPMailer;
use Glpi\System\RequirementsManager;
use Glpi\Exception\PasswordTooWeakException;
use Laminas\Cache\Storage\FlushableInterface;
use Laminas\Cache\Storage\TotalSpaceCapableInterface;
use Laminas\Cache\Storage\AvailableSpaceCapableInterface;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}


/**
 *  Config class
 **/
class Config extends CommonDBTM
{
    public const DELETE_ALL = -1;
    public const KEEP_ALL = 0;

    // From CommonGLPI
    protected $displaylist         = false;

    // From CommonDBTM
    public $auto_message_on_action = false;
    public $showdebug              = true;

    public static $rightname              = 'config';

    public static $undisclosedFields      = ['proxy_passwd', 'smtp_passwd'];
    public static $saferUndisclosedFields = ['admin_email', 'admin_reply'];

    public static function getTypeName($nb = 0)
    {
        return __('Setup');
    }


    public static function getMenuContent()
    {
        $menu = [];
        if (static::canView()) {
            $menu['title']   = _x('setup', 'General');
            $menu['page']    = Config::getFormURL(false);
            $menu['icon']    = Config::getIcon();

            $menu['options']['apiclient']['title']           = APIClient::getTypeName(Session::getPluralNumber());
            $menu['options']['apiclient']['page']            = Config::getFormURL(false) . '?forcetab=Config$8';
            $menu['options']['apiclient']['links']['search'] = Config::getFormURL(false) . '?forcetab=Config$8';
            $menu['options']['apiclient']['links']['add']    = '/front/apiclient.form.php';
        }
        if (count($menu)) {
            return $menu;
        }
        return false;
    }


    public static function canCreate()
    {
        return false;
    }


    public function canViewItem()
    {
        if (
            isset($this->fields['context']) &&
            ($this->fields['context'] == 'core' ||
            Plugin::isPluginActive($this->fields['context']))
        ) {
            return true;
        }
        return false;
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addStandardTab(__CLASS__, $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }

    public function prepareInputForUpdate($input)
    {
        global $CFG_GLPI;

        // Unset _no_history to not save it as a configuration value
        unset($input['_no_history']);

        // Update only an item
        if (isset($input['context'])) {
            return $input;
        }

        // Process configuration for plugins
        if (!empty($input['config_context'])) {
            $config_context = $input['config_context'];
            unset($input['id']);
            unset($input['_glpi_csrf_token']);
            unset($input['update']);
            unset($input['config_context']);
            if (
                (!empty($input['config_class']))
                && (class_exists($input['config_class']))
                && (method_exists($input['config_class'], 'configUpdate'))
            ) {
                $config_method = $input['config_class'] . '::configUpdate';
                unset($input['config_class']);
                $input = call_user_func($config_method, $input);
            }
            $this->setConfigurationValues($config_context, $input);
            return false;
        }

        // Trim automatically endig slash for url_base config as, for all existing occurences,
        // this URL will be prepended to something that starts with a slash.
        if (isset($input["url_base"]) && !empty($input["url_base"])) {
            if (Toolbox::isValidWebUrl($input["url_base"])) {
                $input["url_base"] = rtrim($input["url_base"], '/');
            } else {
                Session::addMessageAfterRedirect(__('Invalid base URL!'), false, ERROR);
                return false;
            }
        }

        if (isset($input["url_base_api"]) && !empty($input["url_base_api"])) {
            if (!Toolbox::isValidWebUrl($input["url_base_api"])) {
                Session::addMessageAfterRedirect(__('Invalid API base URL!'), false, ERROR);
                return false;
            }
        }

        if (isset($input['allow_search_view']) && !$input['allow_search_view']) {
            // Global search need "view"
            $input['allow_search_global'] = 0;
        }

        if (isset($input["smtp_passwd"]) && empty($input["smtp_passwd"])) {
            unset($input["smtp_passwd"]);
        }
        if (isset($input["_blank_smtp_passwd"]) && $input["_blank_smtp_passwd"]) {
            $input['smtp_passwd'] = '';
        }

        if (isset($input["proxy_passwd"]) && empty($input["proxy_passwd"])) {
            unset($input["proxy_passwd"]);
        }
        if (isset($input["_blank_proxy_passwd"]) && $input["_blank_proxy_passwd"]) {
            $input['proxy_passwd'] = '';
        }

        // Manage DB Slave process
        if (isset($input['_dbslave_status'])) {
            $already_active = DBConnection::isDBSlaveActive();

            if ($input['_dbslave_status']) {
                DBConnection::changeCronTaskStatus(true);

                if (!$already_active) {
                    // Activate Slave from the "system" tab
                    DBConnection::createDBSlaveConfig();
                } elseif (isset($input["_dbreplicate_dbhost"])) {
                    // Change parameter from the "replicate" tab
                    DBConnection::saveDBSlaveConf(
                        $input["_dbreplicate_dbhost"],
                        $input["_dbreplicate_dbuser"],
                        $input["_dbreplicate_dbpassword"],
                        $input["_dbreplicate_dbdefault"]
                    );
                }
            }

            if (!$input['_dbslave_status'] && $already_active) {
                DBConnection::deleteDBSlaveConfig();
                DBConnection::changeCronTaskStatus(false);
            }
        }

        // Matrix for Impact / Urgence / Priority
        if (isset($input['_matrix'])) {
            $tab = [];

            for ($urgency = 1; $urgency <= 5; $urgency++) {
                for ($impact = 1; $impact <= 5; $impact++) {
                    $priority               = $input["_matrix_{$urgency}_{$impact}"];
                    $tab[$urgency][$impact] = $priority;
                }
            }

            $input['priority_matrix'] = exportArrayToDB($tab);
            $input['urgency_mask']    = 0;
            $input['impact_mask']     = 0;

            for ($i = 1; $i <= 5; $i++) {
                if ($input["_urgency_{$i}"]) {
                    $input['urgency_mask'] += (1 << $i);
                }

                if ($input["_impact_{$i}"]) {
                    $input['impact_mask'] += (1 << $i);
                }
            }
        }

        if (isset($input['_update_devices_in_menu'])) {
            $input['devices_in_menu'] = exportArrayToDB(
                (isset($input['devices_in_menu']) ? $input['devices_in_menu'] : [])
            );
        }

        // lock mechanism update
        if (isset($input['lock_use_lock_item'])) {
            $input['lock_item_list'] = exportArrayToDB((isset($input['lock_item_list'])
                ? $input['lock_item_list'] : []));
        }

        if (isset($input[Impact::CONF_ENABLED])) {
            $input[Impact::CONF_ENABLED] = exportArrayToDB($input[Impact::CONF_ENABLED]);
        }

        // Beware : with new management system, we must update each value
        unset($input['id']);
        unset($input['_glpi_csrf_token']);
        unset($input['update']);

        // Add skipMaintenance if maintenance mode update
        if (isset($input['maintenance_mode']) && $input['maintenance_mode']) {
            $_SESSION['glpiskipMaintenance'] = 1;
            $url = $CFG_GLPI['root_doc'] . "/index.php?skipMaintenance=1";
            Session::addMessageAfterRedirect(
                sprintf(
                    __('Maintenance mode activated. Backdoor using: %s'),
                    "<a href='$url'>$url</a>"
                ),
                false,
                WARNING
            );
        }

        $this->setConfigurationValues('core', $input);

        return false;
    }

    public static function unsetUndisclosedFields(&$fields)
    {
        if (isset($fields['context']) && isset($fields['name'])) {
            if (
                $fields['context'] == 'core'
                && in_array($fields['name'], self::$undisclosedFields)
            ) {
                unset($fields['value']);
            } else {
                $fields = Plugin::doHookFunction('undiscloseConfigValue', $fields);
            }
        }
    }

    /**
     * Print the config form for display
     *
     * @return void
     **/
    public function showFormDisplay()
    {
        global $CFG_GLPI;

        if (!self::canView()) {
            return;
        }

        $rand = mt_rand();
        $canedit = Session::haveRight(self::$rightname, UPDATE);

        $form = [
            'action' => $canedit ? Toolbox::getItemTypeFormURL('config') : '',
            'buttons' => $canedit ? [
                [
                    'type' => 'submit',
                    'name' => 'update',
                    'value' => __('Update'),
                    'class' => 'btn btn-secondary'
                ]
            ] : [],
            'content' => [
               __('General setup') => [
                   'visible' => true,
                   'inputs' => [
                       __('URL of the application') => [
                           'name' => 'url_base',
                           'type' => 'text',
                           'value' => $CFG_GLPI["url_base"],
                           'col_lg' => 12,
                           'col_md' => 12,
                       ],
                       __('Text in the login box') => [
                        'name' => 'text_login',
                        'type' => 'textarea',
                        'value' => $CFG_GLPI["text_login"],
                        'col_lg' => 12,
                        'col_md' => 12,
                       ],
                       __('Simplified interface help link') => [
                        'name' => 'helpdesk_doc_url',
                        'type' => 'text',
                        'value' => $CFG_GLPI["helpdesk_doc_url"],
                        'col_lg' => 6,
                       ],
                       __('Standard interface help link') => [
                        'name' => 'central_doc_url',
                        'type' => 'text',
                        'value' => $CFG_GLPI["central_doc_url"],
                        'col_lg' => 6,
                       ],
                       __('Allow FAQ anonymous access') => [
                        'name' => 'use_public_faq',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["use_public_faq"],
                        'col_lg' => 6,
                       ],
                       __('Default characters limit (summary text boxes)') => [
                        'name' => 'cut',
                        'type' => 'number',
                        'value' => $CFG_GLPI["cut"],
                        'col_lg' => 6,
                       ],
                       __('Default url length limit') => [
                        'name' => 'url_maxlength',
                        'type' => 'number',
                        'value' => $CFG_GLPI["url_maxlength"],
                        'min' => 20,
                        'max' => 80,
                        'step' => 5,
                        'col_lg' => 6,
                       ],
                       __('Default decimals limit') => [
                        'name' => 'decimal_number',
                        'type' => 'number',
                        'value' => $CFG_GLPI["decimal_number"],
                        'min' => 1,
                        'max' => 4,
                       ],
                       __("Translation of dropdowns") => [
                        'name' => 'translate_dropdowns',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["translate_dropdowns"],
                       ],
                       __("Knowledge base translation") => [
                        'name' => 'translate_kb',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["translate_kb"],
                       ],
                       __("Translation of reminders") => [
                        'name' => 'translate_reminders',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["translate_reminders"],
                       ],
                   ]
               ],
               __('Dynamic display') => [
                  'visible' => true,
                  'inputs' => [
                      __('Page size for dropdown (paging using scroll)') => [
                          'name' => 'dropdown_max',
                          'type' => 'number',
                          'value' => $CFG_GLPI["dropdown_max"],
                          'min' => 1,
                          'max' => 200,
                          'col_lg' => 6,
                      ],
                      __('Autocompletion of text fields') => [
                        'name' => 'use_ajax_autocompletion',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["use_ajax_autocompletion"],
                        'col_lg' => 6,
                      ],
                      __("Don't show search engine in dropdowns if the number of items is less than") => [
                        'name' => 'ajax_limit_count',
                        'type' => 'number',
                        'value' => $CFG_GLPI["ajax_limit_count"],
                        'min' => 1,
                        'max' => 200,
                        'step' => 1,
                        'after' => "0 => " . __('Never'),
                        'col_lg' => 12,
                        'col_mg' => 12,
                      ],
                  ]
               ],
               __('Search engine') => [
                  'visible' => true,
                  'inputs' => [
                      __('Items seen') => [
                          'name' => 'allow_search_view',
                          'type' => 'select',
                          'values' => [
                              0 => __('No'),
                              1 => sprintf(__('%1$s (%2$s)'), __('Yes'), __('last criterion')),
                              2 => sprintf(__('%1$s (%2$s)'), __('Yes'), __('default criterion'))
                          ],
                          'value' => $CFG_GLPI['allow_search_view'],
                      ],
                      __('Global search') => [
                        'name' => 'allow_search_global',
                        'type' => 'select',
                        'values' => [
                            0 => __('No'),
                            1 => sprintf(__('%1$s (%2$s)'), __('Yes'), __('last criterion'))
                        ],
                        'value' => $CFG_GLPI['allow_search_global'],
                      ],
                      __('All') => [
                        'name' => 'allow_search_all',
                        'type' => 'select',
                        'values' => [
                            0 => __('No'),
                            1 => sprintf(__('%1$s (%2$s)'), __('Yes'), __('last criterion'))
                        ],
                        'value' => $CFG_GLPI['allow_search_all'],
                      ],
                  ]
               ],
               __('Item locks') => [
                  'visible' => true,
                  'inputs' => [
                      __('Use locks') => [
                          'name' => 'lock_use_lock_item',
                          'type' => 'checkbox',
                          'value' => $CFG_GLPI["lock_use_lock_item"],
                      ],
                      __('Profile to be used when locking items') => ($CFG_GLPI["lock_use_lock_item"]) ? [
                        'name' => 'lock_lockprofile_id',
                        'type' => 'select',
                        'values' => getOptionForItems('Profile'),
                        'value' => $CFG_GLPI["lock_lockprofile_id"],
                        'action' => getItemActionButtons(['info'], 'Profile'),
                      ] : [
                        'content' => Dropdown::getDropdownName(Profile::getTable(), $CFG_GLPI['lock_lockprofile_id']),
                      ],
                      __('List of items to lock') => [
                        'name' => 'lock_item_list',
                        'type' => 'checklist',
                        'options' => ObjectLock::getLockableObjects(),
                        'values' => $CFG_GLPI['lock_item_list'],
                        !$CFG_GLPI["lock_use_lock_item"] ? 'disabled' : '',
                      ],
                  ]
               ],
               __('Auto Login') => [
                  'visible' => true,
                  'inputs' => [
                      __('Time to allow "Remember Me"') => [
                          'name' => 'login_remember_time',
                          'type' => 'select',
                          'values' => array_merge([__('Disabled')], Timezone::GetTimeStamp([
                              'value' => $CFG_GLPI["login_remember_time"],
                              'min'   => 0,
                              'max'   => MONTH_TIMESTAMP * 2,
                              'step'  => DAY_TIMESTAMP,
                              'toadd' => [HOUR_TIMESTAMP, HOUR_TIMESTAMP * 2, HOUR_TIMESTAMP * 6, HOUR_TIMESTAMP * 12],
                              'rand'  => $rand
                          ])),
                       'value' => $CFG_GLPI["login_remember_time"],
                      ],
                      __("Default state of checkbox") => [
                        'name' => 'login_remember_default',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["login_remember_default"],
                      ],
                      __('Display source dropdown on login page') => [
                        'name' => 'display_login_source',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["display_login_source"],
                      ],
                  ]
               ],
            ]
        ];
        renderTwigForm($form);
    }


    /**
     * Print the config form for restrictions
     *
     * @return void
     **/
    public function showFormInventory()
    {
        global $CFG_GLPI;

        if (!self::canView()) {
            return;
        }

        $rand = mt_rand();
        $canedit = Config::canUpdate();

        $item_devices_types = [];
        foreach ($CFG_GLPI['itemdevices'] as $key => $itemtype) {
            if ($item = getItemForItemtype($itemtype)) {
                $item_devices_types[$itemtype] = $item->getTypeName();
            } else {
                unset($CFG_GLPI['itemdevices'][$key]);
            }
        }

        $form = [
            'action' => $canedit ? Toolbox::getItemTypeFormURL('config') : '',
            'buttons' => [
                $canedit ? [
                    'type' => 'submit',
                    'name' => 'update',
                    'value' => __('Update'),
                    'class' => 'btn btn-secondary'
                ] : [],
            ],
            'content' => [
               __('Assets') => [
                   'visible' => true,
                   'inputs' => [
                       __('Enable the financial and administrative information by default') => [
                           'name' => 'auto_create_infocoms',
                           'type' => 'checkbox',
                           'value' => $CFG_GLPI["auto_create_infocoms"],
                           'col_lg' => 6,
                       ],
                       __('Software category deleted by the dictionary rules') => [
                        'name' => 'softwarecategories_id_ondelete',
                        'type' => 'select',
                        'values' => getOptionForItems('SoftwareCategory'),
                        'value' => $CFG_GLPI["softwarecategories_id_ondelete"],
                        'col_lg' => 6,
                       ],
                       __('Restrict monitor management') => [
                        'name' => 'monitors_management_restrict',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["monitors_management_restrict"],
                        'col_lg' => 6,
                       ],
                       __('Restrict device management') => [
                        'name' => 'peripherals_management_restrict',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["peripherals_management_restrict"],
                        'col_lg' => 6,
                       ],
                       __('Restrict phone management') => [
                        'name' => 'phones_management_restrict',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["phones_management_restrict"],
                        'col_lg' => 6,
                       ],
                       __('Restrict printer management') => [
                        'name' => 'printers_management_restrict',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["printers_management_restrict"],
                        'col_lg' => 6,
                       ],
                       __('End of fiscal year') => [
                        'name' => 'date_tax',
                        'type' => 'date',
                        'value' => $CFG_GLPI["date_tax"],
                        'rand' => $rand,
                        'col_lg' => 6,
                        'required' => true,
                       ],
                       __('Automatic fields (marked by *)') => [
                        'name' => 'use_autoname_by_entity',
                        'type' => 'select',
                        'values' => [
                            0 => __('Global'),
                            1 => __('By entity')
                        ],
                        'value' => $CFG_GLPI["use_autoname_by_entity"],
                        'rand' => $rand,
                        'col_lg' => 6,
                       ],
                       __('Devices displayed in menu') => [
                        'type' => 'checklist',
                        'name' => 'devices_in_menu',
                        'options' => $item_devices_types,
                        'values' => $CFG_GLPI['devices_in_menu'],
                       ],
                       [
                        'name' => '_update_devices_in_menu',
                        'type' => 'hidden',
                        'value' => 1,
                       ],
                       __('Automatic transfer of computers') => (Session::haveRightsOr("transfer", [CREATE, UPDATE])
                       && Session::isMultiEntitiesMode()) ? [
                        'name' => 'transfers_id_auto',
                        'type' => 'select',
                        'values' => array_merge([__('No automatic transfer')], getOptionForItems('Transfer')),
                        'value' => $CFG_GLPI["transfers_id_auto"],
                       ] : [],
                   ]
               ],
               __('Automatically update of the elements related to the computers') . ' : ' . __('Unit management') => [
                  'visible' => true,
                  'inputs' => [
                      __('Alternate username') . '(' . __('When connecting or updating') . ')' => [
                          'name' => 'is_contact_autoupdate',
                          'type' => 'select',
                          'values' => [
                              0 => __('Do not copy'),
                              1 => __('Copy'),
                          ],
                          'value' => $CFG_GLPI["is_contact_autoupdate"],
                          'col_lg' => 6,
                      ],
                      __('Alternate username') . '(' . __('When disconnecting') . ')' => [
                        'name' => 'is_contact_autoclean',
                        'type' => 'select',
                        'values' => [
                            0 => __('Do not delete'),
                            1 => __('Clear'),
                        ],
                        'value' => $CFG_GLPI["is_contact_autoclean"],
                        'col_lg' => 6,
                      ],
                      User::getTypeName(1) . '(' . __('When connecting or updating') . ')' => [
                        'name' => 'is_user_autoupdate',
                        'type' => 'select',
                        'values' => [
                            0 => __('Do not copy'),
                            1 => __('Copy'),
                        ],
                        'value' => $CFG_GLPI["is_user_autoupdate"],
                        'col_lg' => 6,
                      ],
                      User::getTypeName(1) . '(' . __('When disconnecting') . ')' => [
                        'name' => 'is_user_autoclean',
                        'type' => 'select',
                        'values' => [
                            0 => __('Do not delete'),
                            1 => __('Clear'),
                        ],
                        'value' => $CFG_GLPI["is_user_autoclean"],
                        'col_lg' => 6,
                      ],
                      Group::getTypeName(1) . '(' . __('When connecting or updating') . ')' => [
                        'name' => 'is_group_autoupdate',
                        'type' => 'select',
                        'values' => [
                            0 => __('Do not copy'),
                            1 => __('Copy'),
                        ],
                        'value' => $CFG_GLPI["is_group_autoupdate"],
                        'col_lg' => 6,
                      ],
                      Group::getTypeName(1) . '(' . __('When disconnecting') . ')' => [
                        'name' => 'is_group_autoclean',
                        'type' => 'select',
                        'values' => [
                            0 => __('Do not delete'),
                            1 => __('Clear'),
                        ],
                        'value' => $CFG_GLPI["is_group_autoclean"],
                        'col_lg' => 6,
                      ],
                      Location::getTypeName(1) . '(' . __('When connecting or updating') . ')' => [
                        'name' => 'is_location_autoupdate',
                        'type' => 'select',
                        'values' => [
                            0 => __('Do not copy'),
                            1 => __('Copy'),
                        ],
                        'value' => $CFG_GLPI["is_location_autoupdate"],
                        'col_lg' => 6,
                      ],
                      Location::getTypeName(1) . '(' . __('When disconnecting') . ')' => [
                        'name' => 'is_location_autoclean',
                        'type' => 'select',
                        'values' => [
                            0 => __('Do not delete'),
                            1 => __('Clear'),
                        ],
                        'value' => $CFG_GLPI["is_location_autoclean"],
                        'col_lg' => 6,
                      ],
                      __('Status') . '(' . __('When connecting or updating') . ')' => [
                        'name' => 'state_autoupdate_mode',
                        'type' => 'select',
                        'values' => [
                            0 => __('Do not copy'),
                            1 => __('Copy computer status'),
                        ],
                        'value' => $CFG_GLPI["state_autoupdate_mode"],
                        'col_lg' => 6,
                      ],
                      __('Status') . '(' . __('When disconnecting') . ')' => [
                        'name' => 'state_autoclean_mode',
                        'type' => 'select',
                        'values' => [
                            0 => __('Do not delete'),
                            1 => __('Clear status'),
                        ],
                        'value' => $CFG_GLPI["state_autoclean_mode"],
                        'col_lg' => 6,
                      ],
                  ]
               ]
            ]
        ];
        renderTwigForm($form);
    }


    /**
     * Print the config form for restrictions
     *
     * @return void
     **/
    public function showFormAuthentication()
    {
        global $CFG_GLPI;

        if (!Config::canUpdate()) {
            return;
        }

        $form = [
            'action' => Toolbox::getItemTypeFormURL('config'),
            'buttons' => [
                [
                    'type' => 'submit',
                    'name' => 'update_auth',
                    'value' => _sx('button', 'Save'),
                    'class' => 'btn btn-secondary',
                  ],
            ],
            'content' => [
               __('Authentication') => [
                   'visible' => true,
                   'inputs' => [
                       __('Automatically add users from an external authentication source') => [
                           'name' => 'is_users_auto_add',
                           'type' => 'checkbox',
                           'value' => $CFG_GLPI["is_users_auto_add"],
                       ],
                       __('Add a user without accreditation from a LDAP directory') => [
                        'name' => 'use_noright_users_add',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["use_noright_users_add"],
                       ],
                       __('Action when a user is deleted from the LDAP directory') => [
                        'name' => 'user_deleted_ldap',
                        'type' => 'select',
                        'values' => AuthLDAP::getLdapDeletedUserActionOptions(),
                        'value' => $CFG_GLPI["user_deleted_ldap"],
                       ],
                       __('ITSM-NG server time zone') => [
                        'name' => 'time_offset',
                        'type' => 'select',
                        'values' => Timezone::showGMT(),
                        'value' => $CFG_GLPI["time_offset"],
                       ],
                   ],
               ],
            ]
        ];

        renderTwigForm($form);
    }


    /**
     * Print the config form for slave DB
     *
     * @return void
     **/
    public function showFormDBSlave()
    {
        global $DB, $CFG_GLPI, $DBslave;

        if (!Config::canUpdate()) {
            return;
        }

        echo "<form aria-label='DB Slave' name='form' action=\"" . Toolbox::getItemTypeFormURL(__CLASS__) . "\" method='post' data-track-changes='true'>";
        echo "<div class='center' id='tabsbody'>";
        echo "<input type='hidden' name='_dbslave_status' value='1'>";
        echo "<table class='tab_cadre_fixe' aria-label='DB Slave form'>";

        echo "<tr class='tab_bg_2'><th colspan='4'>" . _n('SQL replica', 'SQL replicas', Session::getPluralNumber()) .
            "</th></tr>";
        $DBslave = DBConnection::getDBSlaveConf();

        if (is_array($DBslave->dbhost)) {
            $host = implode(' ', $DBslave->dbhost);
        } else {
            $host = $DBslave->dbhost;
        }
        echo "<tr class='tab_bg_2'>";
        echo "<td>" . __('SQL server (MariaDB or MySQL)') . "</td>";
        echo "<td><input type='text' name='_dbreplicate_dbhost' size='40' value='$host'></td>";
        echo "<td>" . __('Database') . "</td>";
        echo "<td><input type='text' name='_dbreplicate_dbdefault' value='" . $DBslave->dbdefault . "'>";
        echo "</td></tr>";

        echo "<tr class='tab_bg_2'>";
        echo "<td>" . __('SQL user') . "</td>";
        echo "<td><input type='text' name='_dbreplicate_dbuser' value='" . $DBslave->dbuser . "'></td>";
        echo "<td>" . __('SQL password') . "</td>";
        echo "<td><input type='password' name='_dbreplicate_dbpassword' value='" .
            rawurldecode($DBslave->dbpassword) . "'>";
        echo "</td></tr>";

        echo "<tr class='tab_bg_2'>";
        echo "<td>" . __('Use the slave for the search engine') . "</td><td>";
        $values = [0 => __('Never'),
            1 => __('If synced (all changes)'),
            2 => __('If synced (current user changes)'),
            3 => __('If synced or read-only account'),
            4 => __('Always')];
        Dropdown::showFromArray(
            'use_slave_for_search',
            $values,
            ['value' => $CFG_GLPI["use_slave_for_search"]]
        );
        echo "<td colspan='2'>&nbsp;</td>";
        echo "</tr>";

        if ($DBslave->connected && !$DB->isSlave()) {
            echo "<tr class='tab_bg_2'><td colspan='4' class='center'>";
            DBConnection::showAllReplicateDelay();
            echo "</td></tr>";
        }

        echo "<tr class='tab_bg_2'><td colspan='4' class='center'>";
        echo "<input type='submit' name='update' class='submit' value=\"" . _sx('button', 'Save') . "\">";
        echo "</td></tr>";

        echo "</table></div>";
        Html::closeForm();
    }


    /**
     * Print the config form for External API
     *
     * @since 9.1
     * @return void
     **/
    public function showFormAPI()
    {
        global $CFG_GLPI;

        if (!self::canView()) {
            return;
        }

        $inline_doc_api = trim($CFG_GLPI['url_base_api'], '/') . "/";
        $form = [
            'action' => Toolbox::getItemTypeFormURL(__CLASS__),
            'method' => 'post',
            'content' => [
                __('API') => [
                    'visible' => true,
                    'inputs' => [
                        __('URL of the API') => [
                            'name' => 'url_base_api',
                            'type' => 'text',
                            'size' => 80,
                            'value' => $CFG_GLPI["url_base_api"],
                            'col_lg' => 6,
                        ],
                        __('Enable Rest API') => [
                        'name' => 'enable_api',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["enable_api"],
                        'col_lg' => 6,
                        ],
                        '' => $CFG_GLPI['enable_api'] ? [
                        'content' => "<a href='$inline_doc_api'>" . __("API inline Documentation") . "</a>"
                        ] : [],
                    ]
                ],
                __('Authentication') => [
                  'visible' => true,
                  'inputs' => [
                      __('Enable login with credentials') => [
                          'name' => 'enable_api_login_credentials',
                          'type' => 'checkbox',
                          'value' => $CFG_GLPI["enable_api_login_credentials"]
                      ],
                      __('Enable login with external token') => [
                        'name' => 'enable_api_login_external_token',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["enable_api_login_external_token"]
                      ],
                      [
                        'name' => 'update',
                        'type' => 'hidden',
                        'value' => 1
                      ]
                  ]
                ],
            ]
        ];

        renderTwigForm($form);

        $buttons = [
            'apiclient.form.php' => __('Add API client'),
        ];
        Html::displayTitle(
            "",
            self::getTypeName(Session::getPluralNumber()),
            "",
            $buttons
        );
        Search::show("APIClient");
    }


    /**
     * Print the config form for connections
     *
     * @return void
     **/
    public function showFormHelpdesk()
    {
        global $CFG_GLPI;

        if (!self::canView()) {
            return;
        }

        $canedit = Config::canUpdate();

        for ($index = 0; $index <= 100; $index += 10) {
            $sizes[$index * 1048576] = sprintf(__('%s Mio'), $index);
        }
        $sizes[0] = __('No import');

        $urgencynames = [
            1 => Ticket::getUrgencyName(1),
            2 => Ticket::getUrgencyName(2),
            3 => Ticket::getUrgencyName(3),
            4 => Ticket::getUrgencyName(4),
            5 => Ticket::getUrgencyName(5),
        ];

        $headers = [];
        $headers['title'] = __('Urgency') . ' / ' . __('Impact');
        for ($i = 5; $i > 0; $i--) {
            ob_start();
            if ($i != 3) {
                renderTwigTemplate('macros/input.twig', [
                    'name' => "_impact_{$i}",
                    'type' => 'checkbox',
                    'value' => ($CFG_GLPI['impact_mask'] & (1 << $i)) > 0,
                ]);
            } else {
                echo "<input type='hidden' name='_impact_{$i}' value='1' />";
            }
            $headers['x'][$i] = Ticket::getImpactName($i) . ob_get_clean();
            ob_start();
            if ($i != 3) {
                renderTwigTemplate('macros/input.twig', [
                    'name' => "_urgency_{$i}",
                    'type' => 'checkbox',
                    'value' => ($CFG_GLPI['urgency_mask'] & (1 << $i)) > 0,
                ]);
            } else {
                echo "<input type='hidden' name='_urgency_{$i}' value='1' />";
            }
            $headers['y'][$i] = Ticket::getUrgencyName($i) . ob_get_clean();
        }

        $matrix = [];
        for ($urgency = 1; $urgency <= 5; $urgency++) {
            for ($impact = 5; $impact > 0; $impact--) {
                $pri = round(($urgency + $impact) / 2);

                if (isset($CFG_GLPI['priority_matrix'][$urgency][$impact])) {
                    $pri = $CFG_GLPI['priority_matrix'][$urgency][$impact];
                }
                if (
                    ($CFG_GLPI['impact_mask'] & (1 << $impact)) != 0
                    && ($CFG_GLPI['urgency_mask'] & (1 << $urgency)) != 0
                ) {
                    ob_start();
                    renderTwigTemplate('macros/input.twig', [
                        'name' => "_matrix_{$urgency}_{$impact}",
                        'type' => 'select',
                        'values' => $urgencynames,
                        'value' => $CFG_GLPI['priority_matrix'][$urgency][$impact]
                    ]);
                    $content = ob_get_clean();
                    $matrix[$urgency][$impact] = [
                        'content' => $content,
                        'style' => "background:" . $_SESSION['glpipriority_' . $pri],
                    ];
                } else {
                    $matrix[$urgency][$impact] = [
                        'content' => "<input
                    type='hidden'
                    name='_matrix_{$urgency}_{$impact}'
                    value='{$CFG_GLPI['priority_matrix'][$urgency][$impact]}' />",
                    ];
                }
            }
        }

        $form = [
            'action' => $canedit ? Toolbox::getItemTypeFormURL('config') : '',
            'buttons' => [
                $canedit ? [
                    'type' => 'submit',
                    'name' => 'update',
                    'value' => __('Update'),
                    'class' => 'btn btn-secondary'
                ] : [],
            ],
            'content' => [
               __('Assistance') => [
                   'visible' => true,
                   'inputs' => [
                       __('Limit of the schedules for planning : From') => [
                           'name' => 'planning_begin',
                           'type' => 'time',
                           'value' => $CFG_GLPI["planning_begin"],
                           'col_lg' => 6,
                       ],
                       __('To') => [
                        'name' => 'planning_end',
                        'type' => 'time',
                        'value' => $CFG_GLPI["planning_end"],
                        'col_lg' => 6,
                       ],
                       __('Step for the hours (minutes)') => [
                        'name' => 'time_step',
                        'type' => 'number',
                        'value' => $CFG_GLPI["time_step"],
                        'min' => 1,
                        'max' => 60,
                        'step' => 1,
                       ],
                       __('Default file size limit imported by the mails receiver') => [
                        'name' => 'default_mailcollector_filesize_max',
                        'type' => 'select',
                        'values' => $sizes,
                        'value' => $CFG_GLPI["default_mailcollector_filesize_max"],
                       ],
                       __('Default heading when adding a document to a ticket') => [
                        'name' => 'documentcategories_id_forticket',
                        'type' => 'select',
                        'values' => getOptionForItems('DocumentCategory'),
                        'value' => $CFG_GLPI["documentcategories_id_forticket"],
                        'actions' => getItemActionButtons(['info', 'add'], 'DocumentCategory'),
                       ],
                       __('By default, a software may be linked to a ticket') => [
                        'name' => 'default_software_helpdesk_visible',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["default_software_helpdesk_visible"],
                       ],
                       __('Keep tickets when purging hardware in the inventory') => [
                        'name' => 'keep_tickets_on_delete',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["keep_tickets_on_delete"],
                       ],
                       __('Show personnal information in new ticket form (simplified interface)') => [
                        'name' => 'use_check_pref',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["use_check_pref"],
                       ],
                       __('Allow anonymous ticket creation (helpdesk.receiver)') => [
                        'name' => 'use_anonymous_helpdesk',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["use_anonymous_helpdesk"],
                       ],
                       __('Allow anonymous followups (receiver)') => [
                        'name' => 'use_anonymous_followups',
                        'type' => 'checkbox',
                        'value' => $CFG_GLPI["use_anonymous_followups"],
                       ],
                       [
                        'type' => 'hidden',
                        'name' => '_matrix',
                        'value' => 1,
                       ],
                       __('Matrix of calculus for priority') => [
                        'type' => 'twig',
                        'template' => 'matrix.twig',
                        'col_lg' => 12,
                        'col_md' => 12,
                        'headers' => $headers,
                        'matrix' => $matrix,
                       ]
                   ]
               ]
            ]
        ];
        //debug matrix
        renderTwigForm($form);
        // if ($canedit) {
        //    echo "<form name='form' action=\"".Toolbox::getItemTypeFormURL(__CLASS__)."\" method='post' data-track-changes='true'>";
        // }
        // echo "<div class='center spaced' id='tabsbody'>";

        // echo "<table class='tab_cadre_fixe'>";
        // echo "<tr><th colspan='7'>" . __('Matrix of calculus for priority');
        // echo "<input type='hidden' name='_matrix' value='1'></th></tr>";

        // echo "<tr class='tab_bg_2'>";
        // echo "<td class='b right' colspan='2'>".__('Impact')."</td>";

        // $isimpact = [];
        // for ($impact=5; $impact>=1; $impact--) {
        //    echo "<td class='center'>".Ticket::getImpactName($impact).'<br>';

        //    if ($impact==3) {
        //       $isimpact[3] = 1;
        //       echo "<input type='hidden' name='_impact_3' value='1'>";

        //    } else {
        //       $isimpact[$impact] = (($CFG_GLPI['impact_mask']&(1<<$impact)) >0);
        //       Dropdown::showYesNo("_impact_{$impact}", $isimpact[$impact]);
        //    }
        //    echo "</td>";
        // }
        // echo "</tr>";

        // echo "<tr class='tab_bg_1'>";
        // echo "<td class='b' colspan='2'>".__('Urgency')."</td>";

        // for ($impact=5; $impact>=1; $impact--) {
        //    echo "<td>&nbsp;</td>";
        // }
        // echo "</tr>";

        // $isurgency = [];
        // for ($urgency=5; $urgency>=1; $urgency--) {
        //    echo "<tr class='tab_bg_1'>";
        //    echo "<td>".Ticket::getUrgencyName($urgency)."&nbsp;</td>";
        //    echo "<td>";

        //    if ($urgency==3) {
        //       $isurgency[3] = 1;
        //       echo "<input type='hidden' name='_urgency_3' value='1'>";

        //    } else {
        //       $isurgency[$urgency] = (($CFG_GLPI['urgency_mask']&(1<<$urgency)) >0);
        //       Dropdown::showYesNo("_urgency_{$urgency}", $isurgency[$urgency]);
        //    }
        //    echo "</td>";

        //    for ($impact=5; $impact>=1; $impact--) {
        //       $pri = round(($urgency+$impact)/2);

        //       if (isset($CFG_GLPI['priority_matrix'][$urgency][$impact])) {
        //          $pri = $CFG_GLPI['priority_matrix'][$urgency][$impact];
        //       }

        //       if ($isurgency[$urgency] && $isimpact[$impact]) {
        //          $bgcolor=$_SESSION["glpipriority_$pri"];
        //          echo "<td class='center' bgcolor='$bgcolor'>";
        //          Ticket::dropdownPriority(['value' => $pri,
        //                                         'name'  => "_matrix_{$urgency}_{$impact}"]);
        //          echo "</td>";
        //       } else {
        //          echo "<td><input type='hidden' name='_matrix_{$urgency}_{$impact}' value='$pri'>
        //                </td>";
        //       }
        //    }
        //    echo "</tr>\n";
        // }
        // if ($canedit) {
        //    echo "<tr class='tab_bg_2'>";
        //    echo "<td colspan='7' class='center'>";
        //    echo "<input type='submit' name='update' class='submit' value=\""._sx('button', 'Save')."\">";
        //    echo "</td></tr>";
        // }

        // echo "</table></div>";
        // Html::closeForm();
    }


    /**
     * Print the config form for default user prefs
     *
     * @param $data array containing datas
     * (CFG_GLPI for global config / glpi_users fields for user prefs)
     *
     * @return void
     **/
    public function showFormUserPrefs($data = [])
    {
        global $CFG_GLPI, $DB;

        $oncentral = (Session::getCurrentInterface() == "central");
        $userpref  = false;

        $oncentral = (Session::getCurrentInterface() == "central");
        $userpref  = false;
        $url       = Toolbox::getItemTypeFormURL(__CLASS__);

        if (array_key_exists('last_login', $data)) {
           $userpref = true;
           if ($data["id"] === Session::getLoginUserID()) {
              $url  = $CFG_GLPI['root_doc']."/front/preference.php";
           } else {
              $url  = User::getFormURL();
           }
        }

        echo Html::scriptBlock("
         function formatThemes(theme) {
             if (!theme.id) {
                return theme.text;
             }

             return $('<span></span>').html('<img src=\'../css/palettes/previews/' + theme.text.toLowerCase() + '.png\'/>'
                      + '&nbsp;' + theme.text);
         }
         $(\"#theme-selector\").select2({
             templateResult: formatThemes,
             templateSelection: formatThemes,
             width: '100%',
             escapeMarkup: function(m) { return m; }
         });
         $('label[for=theme-selector]').on('click', function(){ $('#theme-selector').select2('open'); });
      ");
        $tz_warning = '';

        $form = [
           'action' => $url,
           'buttons' => [
              [
                 'type' => 'submit',
                 'name' => 'update',
                 'value' => _sx('button', 'Save'),
                 'class' => 'btn btn-secondary',
              ],
           ],
           'content' => [
              __('Personalization') => [
                 'visible' => true,
                 'inputs' => [
                    ($userpref && isset($data['id'])) ? [
                       'type' => 'hidden',
                       'name' => 'id',
                       'value' => $data['id'],
                    ] : [],
                    (Config::canUpdate() || !GLPI_DEMO_MODE) && $userpref ? __('Language') : __('Default language') => [
                       'type' => 'select',
                       'name' => 'language',
                       'values' => Dropdown::getLanguages(),
                       'value' => $data["language"],
                    ],
                    __('Date format') => [
                       'type' => 'select',
                       'name' => 'date_format',
                       'values' => Toolbox::phpDateFormats(),
                       'value' => $data["date_format"],
                    ],
                    __('Display order of surnames firstnames') => [
                       'type' => 'select',
                       'name' => 'names_format',
                       'values' => [
                          User::REALNAME_BEFORE  => __('Surname, First name'),
                          User::FIRSTNAME_BEFORE => __('First name, Surname'),
                       ],
                       'value' => $data["names_format"],
                    ],
                    __('Number format') => [
                       'type' => 'select',
                       'name' => 'number_format',
                       'values' => [
                          0 => '1 234.56',
                          1 => '1,234.56',
                          2 => '1 234,56',
                          3 => '1234.56',
                          4 => '1234,56',
                       ],
                       'value' => $data["number_format"],
                    ],
                    __('Results to display by page') => [
                       'type' => 'number',
                       'name' => 'list_limit',
                       'min' => 5,
                       'max' => $CFG_GLPI['list_limit_max'],
                       'step' => 5,
                       'value' => $data['list_limit'],
                    ],
                    __('Go to created item after creation') => [
                       'type' => 'select',
                       'name' => 'backcreated',
                       'values' => [
                          0 => __('No'),
                          1 => __('Yes'),
                       ],
                       'value' => $data['backcreated'],
                    ],
                    __('Display the complete name in tree dropdowns') => [
                       'type' => 'select',
                       'name' => 'use_flat_dropdowntree',
                       'values' => [
                          0 => __('No'),
                          1 => __('Yes'),
                       ],
                       'value' => $data["use_flat_dropdowntree"],
                       $oncentral ? 'disabled' : '' => '',
                    ],
                    __('Display counters') => (!$userpref || ($CFG_GLPI['show_count_on_tabs'] != -1)) ? [
                       'type' => 'select',
                       'name' => 'show_count_on_tabs',
                       'values' => [
                          0 => __('No'),
                          1 => __('Yes')
                       ] + (!$userpref ? [-1 => __('Never')] : []),
                       'value' => $data["show_count_on_tabs"],
                    ] : [],
                    __('Display the number of items in the menu') => $oncentral ? [
                       'type' => 'select',
                       'name' => 'is_ids_visible',
                       'values' => [
                          0 => __('No'),
                          1 => __('Yes')
                       ],
                       'value' => $data["is_ids_visible"],
                    ] : [],
                    __('Keep devices when purging an item') => [
                       'type' => 'select',
                       'name' => 'keep_devices_when_purging_item',
                       'values' => [
                          0 => __('No'),
                          1 => __('Yes')
                       ],
                       'value' => $data["keep_devices_when_purging_item"],
                    ],
                    __('Notifications for my changes') => [
                       'type' => 'select',
                       'name' => 'notification_to_myself',
                       'values' => [
                          0 => __('No'),
                          1 => __('Yes')
                       ],
                       'value' => $data["notification_to_myself"],
                    ],
                    __('Results to display on home page') => $oncentral ? [
                       'type' => 'number',
                       'name' => 'display_count_on_home',
                       'min' => 0,
                       'max' => 30,
                       'value' => $data['display_count_on_home'],
                    ] : [],
                    __('PDF export font') => [
                       'type' => 'select',
                       'name' => 'pdffont',
                       'values' => GLPIPDF::getFontList(),
                       'value' => $data["pdffont"],
                    ],
                    __('CSV delimiter') => [
                       'type' => 'select',
                       'name' => 'csv_delimiter',
                       'values' => [
                          ';' => ';',
                          ',' => ','
                       ],
                       'value' => $data["csv_delimiter"],
                    ],
                    __('Timezone') => $DB->areTimezonesAvailable($tz_warning) ? [
                       'type' => 'select',
                       'name' => 'timezone',
                       'values' => array_merge([0 => __('Use server configuration')], $DB->getTimezones()),
                       'value' => $data["timezone"] ?? 0,
                    ] : [],
                 ],
              ],
              __('Assistance') => [
                 'visible' => $oncentral,
                 'inputs' => [
                    __('Private followups by default') => [
                       'type' => 'select',
                       'name' => 'followup_private',
                       'values' => [
                          0 => __('No'),
                          1 => __('Yes')
                       ],
                       'value' => $data["followup_private"],
                    ],
                    __('Show new tickets on the home page') =>
                    Session::haveRightsOr("ticket", [Ticket::READMY, Ticket::READALL, Ticket::READASSIGN]) ? [
                       'type' => 'select',
                       'name' => 'show_jobs_at_login',
                       'values' => [
                          0 => __('No'),
                          1 => __('Yes')
                       ],
                       'value' => $data["show_jobs_at_login"],
                    ] : [],
                    __('Private tasks by default') => [
                       'type' => 'select',
                       'name' => 'task_private',
                       'values' => [
                          0 => __('No'),
                          1 => __('Yes')
                       ],
                       'value' => $data["task_private"],
                    ],
                    __('Request sources by default') => [
                       'type' => 'select',
                       'name' => 'default_requesttypes_id',
                       'values' => getOptionForItems('RequestType', ['is_active' => 1, 'is_ticketheader' => 1]),
                       'value' => $data["default_requesttypes_id"],
                    ],
                    __('Tasks state by default') => [
                       'type' => 'select',
                       'name' => 'task_state',
                       'values' => [
                          Planning::INFO => _n('Information', 'Information', 1),
                          Planning::TODO => __('To do'),
                          Planning::DONE => __('Done')
                       ],
                       'value' => $data["task_state"],
                    ],
                    __('Automatically refresh data (tickets list, project kanban) in minutes') => [
                       'type' => 'number',
                       'name' => 'refresh_views',
                       'min' => 0,
                       'max' => 30,
                       'step' => 1,
                       'after' => "0 => " . __('Never'),
                       'value' => $data["refresh_views"],
                    ],
                    __('Pre-select me as a technician when creating a ticket') =>
                    !$userpref || Session::haveRight('ticket', Ticket::OWN) ? [
                       'type' => 'select',
                       'name' => 'set_default_tech',
                       'values' => [
                          0 => __('No'),
                          1 => __('Yes')
                       ],
                       'value' => $data["set_default_tech"],
                       'col_md' => '6',
                       'col_lg' => '6',
                    ] : [],
                    __('Pre-select me as a requester when creating a ticket') =>
                    !$userpref || Session::haveRight('ticket', CREATE) ? [
                       'type' => 'select',
                       'name' => 'set_default_requester',
                       'values' => [
                          0 => __('No'),
                          1 => __('Yes')
                       ],
                       'value' => $data["set_default_requester"],
                       'col_md' => '6',
                       'col_lg' => '6',
                    ] : [],
                    __('Priority color') . ' 1' => [
                       'type' => 'color',
                       'name' => 'priority_1',
                       'value' => $data["priority_1"],
                       'col_md' => '2',
                       'col_lg' => '2',
                    ],
                    __('Priority color') . ' 2' => [
                       'type' => 'color',
                       'name' => 'priority_2',
                       'value' => $data["priority_2"],
                       'col_md' => '2',
                       'col_lg' => '2',
                    ],
                    __('Priority color') . '3' => [
                       'type' => 'color',
                       'name' => 'priority_3',
                       'value' => $data["priority_3"],
                       'col_md' => '2',
                       'col_lg' => '2',
                    ],
                    __('Priority color') . ' 4' => [
                       'type' => 'color',
                       'name' => 'priority_4',
                       'value' => $data["priority_4"],
                       'col_md' => '2',
                       'col_lg' => '2',
                    ],
                    __('Priority color') . ' 5' => [
                       'type' => 'color',
                       'name' => 'priority_5',
                       'value' => $data["priority_5"],
                       'col_md' => '2',
                       'col_lg' => '2',
                    ],
                    __('Priority color') . ' 6' => [
                       'type' => 'color',
                       'name' => 'priority_6',
                       'value' => $data["priority_6"],
                       'col_md' => '2',
                       'col_lg' => '2',
                    ],
                 ]
              ],
              __('Due date progression') => [
                 'visible' => true,
                 'inputs' => [
                    __('OK state color') => [
                    'type' => 'color',
                    'name' => 'duedateok_color',
                    'value' => $data["duedateok_color"],
                    'col_md' => '4',
                    'col_lg' => '4',
                    ],
                    __('Warning state color') => [
                       'type' => 'color',
                       'name' => 'duedatewarning_color',
                       'value' => $data["duedatewarning_color"],
                       'col_md' => '4',
                       'col_lg' => '4',
                    ],
                    __('Critical state color') => [
                       'type' => 'color',
                       'name' => 'duedatecritical_color',
                       'value' => $data["duedatecritical_color"],
                       'col_md' => '4',
                       'col_lg' => '4',
                    ],
                    __('Warning state threshold') => [
                       'type' => 'number',
                       'name' => 'duedatewarning_less',
                       'min' => 0,
                       'max' => 100,
                       'step' => 1,
                       'value' => $data["duedatewarning_less"],
                       'col_md' => '3',
                       'col_lg' => '3',
                    ],
                    __('Warning state unit') => [
                       'type' => 'select',
                       'name' => 'duedatewarning_unit',
                       'values' => [
                          '%' => '%',
                          'hours' => _n('Hour', 'Hours', Session::getPluralNumber()),
                          'days' => _n('Day', 'Days', Session::getPluralNumber()),
                       ],
                       'value' => $data["duedatewarning_unit"],
                       'col_md' => '3',
                       'col_lg' => '3',
                    ],
                    __('Critical state threshold') => [
                       'type' => 'number',
                       'name' => 'duedatecritical_less',
                       'min' => 0,
                       'max' => 100,
                       'step' => 1,
                       'value' => $data["duedatecritical_less"],
                       'col_md' => '3',
                       'col_lg' => '3',
                    ],
                    __('Critical state unit') => [
                       'type' => 'select',
                       'name' => 'duedatecritical_unit',
                       'values' => [
                          '%' => '%',
                          'hours' => _n('Hour', 'Hours', Session::getPluralNumber()),
                          'days' => _n('Day', 'Days', Session::getPluralNumber()),
                       ],
                       'value' => $data["duedatecritical_unit"],
                       'col_md' => '3',
                       'col_lg' => '3',
                    ],
                 ],
              ],
              __('Item locks') =>
              ($oncentral && $CFG_GLPI["lock_use_lock_item"]) ? [
                 'visible' => true,
                 'inputs' => [
                    __('Auto-lock Mode') => [
                       'type' => 'select',
                       'name' => 'lock_autolock_mode',
                       'values' => [
                          0 => __('No'),
                          1 => __('Yes')
                       ],
                       'value' => $data["lock_autolock_mode"],
                       'col_md' => '6',
                       'col_lg' => '6',

                    ],
                    __('Direct Notification (requester for unlock will be the notification sender)') => [
                       'type' => 'select',
                       'name' => 'lock_directunlock_notification',
                       'values' => [
                          0 => __('No'),
                          1 => __('Yes')
                       ],
                       'value' => $data["lock_directunlock_notification"],
                       'col_md' => '6',
                       'col_lg' => '6',
                    ],
                 ],
              ] : []
           ]
        ];
        renderTwigForm($form);
    }

    /**
     * Check if the "use_password_security" parameter is enabled
     *
     * @return bool
     */
    public static function arePasswordSecurityChecksEnabled(): bool
    {
        global $CFG_GLPI;

        return $CFG_GLPI["use_password_security"];
    }

    /**
     * Display security checks on password
     *
     * @param $field string id of the field containing password to check (default 'password')
     *
     * @since 0.84
    **/
    public static function displayPasswordSecurityChecks($field = 'password')
    {
        global $CFG_GLPI;

        $needs = [];

        if ($CFG_GLPI["use_password_security"]) {
            printf(
                __('%1$s: %2$s'),
                __('Password minimum length'),
                "<span id='password_min_length' class='red'>" . $CFG_GLPI['password_min_length'] .
                      "</span>"
            );
        }

        echo "<script type='text/javascript' >\n";
        echo "function passwordCheck() {\n";
        if ($CFG_GLPI["use_password_security"]) {
            echo "var pwd = " . Html::jsGetElementbyID($field) . ";";
            echo "if (pwd.val().length < " . $CFG_GLPI['password_min_length'] . ") {
               " . Html::jsGetElementByID('password_min_length') . ".addClass('red');
               " . Html::jsGetElementByID('password_min_length') . ".removeClass('green');
         } else {
               " . Html::jsGetElementByID('password_min_length') . ".addClass('green');
               " . Html::jsGetElementByID('password_min_length') . ".removeClass('red');
         }";
            if ($CFG_GLPI["password_need_number"]) {
                $needs[] = "<span id='password_need_number' class='red'>" . __('Digit') . "</span>";
                echo "var numberRegex = new RegExp('[0-9]', 'g');
             if (false == numberRegex.test(pwd.val())) {
                 " . Html::jsGetElementByID('password_need_number') . ".addClass('red');
                 " . Html::jsGetElementByID('password_need_number') . ".removeClass('green');
            } else {
                " . Html::jsGetElementByID('password_need_number') . ".addClass('green');
                " . Html::jsGetElementByID('password_need_number') . ".removeClass('red');
            }";
            }
            if ($CFG_GLPI["password_need_letter"]) {
                $needs[] = "<span id='password_need_letter' class='red'>" . __('Lowercase') . "</span>";
                echo "var letterRegex = new RegExp('[a-z]', 'g');
             if (false == letterRegex.test(pwd.val())) {
                 " . Html::jsGetElementByID('password_need_letter') . ".addClass('red');
                 " . Html::jsGetElementByID('password_need_letter') . ".removeClass('green');
            } else {
                " . Html::jsGetElementByID('password_need_letter') . ".addClass('green');
                " . Html::jsGetElementByID('password_need_letter') . ".removeClass('red');
            }";
            }
            if ($CFG_GLPI["password_need_caps"]) {
                $needs[] = "<span id='password_need_caps' class='red'>" . __('Uppercase') . "</span>";
                echo "var capsRegex = new RegExp('[A-Z]', 'g');
             if (false == capsRegex.test(pwd.val())) {
                 " . Html::jsGetElementByID('password_need_caps') . ".addClass('red');
                 " . Html::jsGetElementByID('password_need_caps') . ".removeClass('green');
            } else {
                " . Html::jsGetElementByID('password_need_caps') . ".addClass('green');
                " . Html::jsGetElementByID('password_need_caps') . ".removeClass('red');
            }";
            }
            if ($CFG_GLPI["password_need_symbol"]) {
                $needs[] = "<span id='password_need_symbol' class='red'>" . __('Symbol') . "</span>";
                echo "var capsRegex = new RegExp('[^a-zA-Z0-9_]', 'g');
             if (false == capsRegex.test(pwd.val())) {
                 " . Html::jsGetElementByID('password_need_symbol') . ".addClass('red');
                 " . Html::jsGetElementByID('password_need_symbol') . ".removeClass('green');
            } else {
                " . Html::jsGetElementByID('password_need_symbol') . ".addClass('green');
                " . Html::jsGetElementByID('password_need_symbol') . ".removeClass('red');
            }";
            }
        }
        echo "}";
        echo '</script>';
        if (count($needs)) {
            echo "<br>";
            printf(__('%1$s: %2$s'), __('Password must contains'), implode(', ', $needs));
        }
    }


    /**
     * Validate password based on security rules
     *
     * @since 0.84
     *
     * @param $password  string   password to validate
     * @param $display   boolean  display errors messages? (true by default)
     *
     * @throws PasswordTooWeakException when $display is false and the password does not matches the requirements
     *
     * @return boolean is password valid?
     **/
    public static function validatePassword($password, $display = true)
    {
        global $CFG_GLPI;

        $ok = true;
        $exception = new PasswordTooWeakException();
        if ($CFG_GLPI["use_password_security"]) {
            if (Toolbox::strlen($password) < $CFG_GLPI['password_min_length']) {
                $ok = false;
                if ($display) {
                    Session::addMessageAfterRedirect(__('Password too short!'), false, ERROR);
                } else {
                    $exception->addMessage(__('Password too short!'));
                }
            }
            if (
                $CFG_GLPI["password_need_number"]
                && !preg_match("/[0-9]+/", $password)
            ) {
                $ok = false;
                if ($display) {
                    Session::addMessageAfterRedirect(
                        __('Password must include at least a digit!'),
                        false,
                        ERROR
                    );
                } else {
                    $exception->addMessage(__('Password must include at least a digit!'));
                }
            }
            if (
                $CFG_GLPI["password_need_letter"]
                && !preg_match("/[a-z]+/", $password)
            ) {
                $ok = false;
                if ($display) {
                    Session::addMessageAfterRedirect(
                        __('Password must include at least a lowercase letter!'),
                        false,
                        ERROR
                    );
                } else {
                    $exception->addMessage(__('Password must include at least a lowercase letter!'));
                }
            }
            if (
                $CFG_GLPI["password_need_caps"]
                && !preg_match("/[A-Z]+/", $password)
            ) {
                $ok = false;
                if ($display) {
                    Session::addMessageAfterRedirect(
                        __('Password must include at least a uppercase letter!'),
                        false,
                        ERROR
                    );
                } else {
                    $exception->addMessage(__('Password must include at least a uppercase letter!'));
                }
            }
            if (
                $CFG_GLPI["password_need_symbol"]
                && !preg_match("/\W+/", $password)
            ) {
                $ok = false;
                if ($display) {
                    Session::addMessageAfterRedirect(
                        __('Password must include at least a symbol!'),
                        false,
                        ERROR
                    );
                } else {
                    $exception->addMessage(__('Password must include at least a symbol!'));
                }
            }
        }
        if (!$ok && !$display) {
            throw $exception;
        }
        return $ok;
    }


    /**
     * Display a report about system performance
     * - opcode cache (opcache)
     * - user data cache (apcu / apcu-bc)
     *
     * @since 9.1
     **/
    public function showPerformanceInformations()
    {
        $GLPI_CACHE = self::getCache('cache_db', 'core', false);

        if (!Config::canUpdate()) {
            return false;
        }

        echo "<div class='center' id='tabsbody'>";
        echo "<table class='tab_cadre_fixe' aria_label='Performance Informations'>";

        echo "<tr><th colspan='4'>" . __('PHP opcode cache') . "</th></tr>";
        $ext = 'Zend OPcache';
        if (extension_loaded($ext) && ($info = opcache_get_status(false))) {
            $msg = sprintf(__s('%s extension is installed'), $ext);
            echo "<tr><td>" . sprintf(__('The "%s" extension is installed'), $ext) . "</td>
              <td>" . phpversion($ext) . "</td>
              <td></td>
              <td class='icons_block'><i class='fa fa-check-circle ok' title='$msg'><span class='sr-only'>$msg</span></td></tr>";

            // Memory
            $used = $info['memory_usage']['used_memory'];
            $free = $info['memory_usage']['free_memory'];
            $rate = round(100.0 * $used / ($used + $free));
            $max  = Toolbox::getSize($used + $free);
            $used = Toolbox::getSize($used);
            echo "<tr><td>" . _n('Memory', 'Memories', 1) . "</td>
              <td>" . sprintf(__('%1$s / %2$s'), $used, $max) . "</td><td>";
            Html::displayProgressBar('100', $rate, ['simple'       => true,
                'forcepadding' => false]);

            $class   = 'info-circle missing';
            $msg     = sprintf(__s('%1$s memory usage is too low or too high'), $ext);
            if ($rate > 5 && $rate < 75) {
                $class   = 'check-circle ok';
                $msg     = sprintf(__s('%1$s memory usage is correct'), $ext);
            }
            echo "</td><td class='icons_block'><i title='$msg' class='fa fa-$class'></td></tr>";

            // Hits
            $hits = $info['opcache_statistics']['hits'];
            $miss = $info['opcache_statistics']['misses'];
            $max  = $hits + $miss;
            $rate = round($info['opcache_statistics']['opcache_hit_rate']);
            echo "<tr><td>" . __('Hits rate') . "</td>
             <td>" . sprintf(__('%1$s / %2$s'), $hits, $max) . "</td><td>";
            Html::displayProgressBar('100', $rate, ['simple'       => true,
                'forcepadding' => false]);

            $class   = 'info-circle missing';
            $msg     = sprintf(__s('%1$s hits rate is low'), $ext);
            if ($rate > 90) {
                $class   = 'check-circle ok';
                $msg     = sprintf(__s('%1$s hits rate is correct'), $ext);
            }
            echo "</td><td class='icons_block'><i title='$msg' class='fa fa-$class'></td></tr>";

            // Restart (1 seems ok, can happen)
            $max = $info['opcache_statistics']['oom_restarts'];
            echo "<tr><td>" . __('Out of memory restart') . "</td>
             <td>$max</td><td>";

            $class   = 'info-circle missing';
            $msg     = sprintf(__s('%1$s restart rate is too high'), $ext);
            if ($max < 2) {
                $class   = 'check-circle ok';
                $msg     = sprintf(__s('%1$s restart rate is correct'), $ext);
            }
            echo "</td><td class='icons_block'><i title='$msg' class='fa fa-$class'></td></tr>";

            if ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE) {
                echo "<tr><td></td><td colspan='3'>";
                echo '<form aria-label="Reset" method="POST" action="' . static::getFormURL() . '" style="display:inline;">';
                echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
                echo Html::hidden('reset_opcache', ['value' => 1]);
                echo '<button type="submit" class="btn btn-secondary" aria-label="Reset">';
                echo __('Reset');
                echo '</button>';
                echo '</form>';
                echo "</td></tr>";
            }
        } else {
            $msg = sprintf(__s('%s extension is not present'), $ext);
            echo "<tr><td colspan='3'>" . sprintf(__('Installing and enabling the "%s" extension may improve ITSM-NG performance'), $ext) . "</td>
              <td class='icons_block'><i class='fa fa-info-circle missing' title='$msg'></i><span class='sr-only'>$msg</span></td></tr>";
        }

        echo "<tr><th colspan='4'>" . __('User data cache') . "</th></tr>";
        $ext = strtolower(get_class($GLPI_CACHE));
        $ext = substr($ext, strrpos($ext, '\\') + 1);
        if (in_array($ext, ['apcu', 'memcache', 'memcached', 'wincache', 'redis'])) {
            $msg = sprintf(__s('The "%s" cache extension is installed'), $ext);
        } else {
            $msg = sprintf(__s('"%s" cache system is used'), $ext);
        }
        echo "<tr><td>" . $msg . "</td>
          <td>" . phpversion($ext) . "</td>
          <td></td>
          <td class='icons_block'><i class='fa fa-check-circle ok' title='$msg'></i><span class='sr-only'>$msg</span></td></tr>";

        if ($ext != 'filesystem' && $GLPI_CACHE instanceof AvailableSpaceCapableInterface && $GLPI_CACHE instanceof TotalSpaceCapableInterface) {
            $free = $GLPI_CACHE->getAvailableSpace();
            $max  = $GLPI_CACHE->getTotalSpace();
            $used = $max - $free;
            $rate = round(100.0 * $used / $max);
            $max  = Toolbox::getSize($max);
            $used = Toolbox::getSize($used);

            echo "<tr><td>" . _n('Memory', 'Memories', 1) . "</td>
              <td>" . sprintf(__('%1$s / %2$s'), $used, $max) . "</td><td>";
            Html::displayProgressBar('100', $rate, ['simple'       => true,
                'forcepadding' => false]);
            $class   = 'info-circle missing';
            $msg     = sprintf(__s('%1$s memory usage is too high'), $ext);
            if ($rate < 80) {
                $class   = 'check-circle ok';
                $msg     = sprintf(__s('%1$s memory usage is correct'), $ext);
            }
            echo "</td><td class='icons_block'><i title='$msg' class='fa fa-$class'></td></tr>";
        }

        if ($GLPI_CACHE instanceof FlushableInterface) {
            echo "<tr><td></td><td colspan='3'>";
            echo '<form aria-label="Reset" method="POST" action="' . static::getFormURL() . '" style="display:inline;">';
            echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
            echo Html::hidden('reset_cache', ['value' => 1]);
            echo Html::hidden('optname', ['value' => 'cache_db']);
            echo '<button type="submit" class="btn btn-secondary" aria-label="Reset">';
            echo __('Reset');
            echo '</button>';
            echo '</form>';
            echo "</td></tr>";
        }

        echo "<tr><th colspan='4'>" . __('Translation cache') . "</th></tr>";
        $translation_cache = self::getCache('cache_trans', 'core', false);
        $adapter_class = strtolower(get_class($translation_cache));
        $adapter = substr($adapter_class, strrpos($adapter_class, '\\') + 1);
        $msg = sprintf(__s('"%s" cache system is used'), $adapter);
        echo "<tr><td colspan='3'>" . $msg . "</td>
          <td class='icons_block'><i class='fa fa-check-circle ok' title='$msg'></i><span class='sr-only'>$msg</span></td></tr>";

        if ($translation_cache instanceof FlushableInterface) {
            echo "<tr><td></td><td colspan='3'>";
            echo '<form aria-label="Reset" method="POST" action="' . static::getFormURL() . '" style="display:inline;">';
            echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
            echo Html::hidden('reset_cache', ['value' => 1]);
            echo Html::hidden('optname', ['value' => 'cache_trans']);
            echo '<button type="submit" class="btn btn-secondary" aria-label="Reset">';
            echo __('Reset');
            echo '</button>';
            echo '</form>';
            echo "</td></tr>";
        }

        echo "</table></div>\n";
    }

    /**
     * Display a HTML report about systeme information / configuration
     **/
    public function showSystemInformations()
    {
        global $DB, $CFG_GLPI;

        if (!Config::canUpdate()) {
            return false;
        }

        $clear = __('Clear');
        $oldlang = $_SESSION['glpilanguage'];
        $ver = ITSM_VERSION;
        $width = 128;

        ob_start();
        echo "<table aria-label=System Information'>";
        echo "<tr class='tab_bg_1'><td><pre>\n";
        echo "ITSM-NG $ver (" . $CFG_GLPI['root_doc'] . " => " . GLPI_ROOT . ")\n";
        echo "Installation mode: " . GLPI_INSTALL_MODE . "\n";
        echo "Current language:" . $oldlang . "\n";
        echo "\n</pre></td></tr>";

        echo "<tr><th>Server</th></tr>\n";
        echo "<tr class='tab_bg_1'><td><pre>\n&nbsp;\n";
        echo wordwrap("Operating system: " . php_uname() . "\n", $width, "\n\t");
        $exts = get_loaded_extensions();
        sort($exts);
        echo wordwrap(
            "PHP " . phpversion() . ' ' . php_sapi_name() . " (" . implode(', ', $exts) . ")\n",
            $width,
            "\n\t"
        );
        $msg = "Setup: ";

        foreach (
            ['max_execution_time', 'memory_limit', 'post_max_size', 'safe_mode',
            'session.save_handler', 'upload_max_filesize'] as $key
        ) {
            $msg .= $key . '="' . ini_get($key) . '" ';
        }
        echo wordwrap($msg . "\n", $width, "\n\t");

        $msg = 'Software: ';
        if (isset($_SERVER["SERVER_SOFTWARE"])) {
            $msg .= $_SERVER["SERVER_SOFTWARE"];
        }
        if (isset($_SERVER["SERVER_SIGNATURE"])) {
            $msg .= ' (' . Html::clean($_SERVER["SERVER_SIGNATURE"]) . ')';
        }
        echo wordwrap($msg . "\n", $width, "\n\t");

        if (isset($_SERVER["HTTP_USER_AGENT"])) {
            echo "\t" . Toolbox::clean_cross_side_scripting_deep($_SERVER["HTTP_USER_AGENT"]) . "\n";
        }

        foreach ($DB->getInfo() as $key => $val) {
            echo "$key: $val\n\t";
        }
        echo "\n";

        $core_requirements = (new RequirementsManager())->getCoreRequirementList($DB);
        /* @var \Glpi\System\Requirement\RequirementInterface $requirement */
        foreach ($core_requirements as $requirement) {
            if ($requirement->isOutOfContext()) {
                continue; // skip requirement if not relevant
            }

            $img = $requirement->isValidated()
                ? 'ok'
                : ($requirement->isOptional() ? 'warning' : 'ko');
            $messages = Html::entities_deep($requirement->getValidationMessages());

            echo '<img src="' . $CFG_GLPI['root_doc'] . '/pics/' . $img . '_min.png"'
                . ' alt="' . implode(' ', $messages) . '" title="' . implode(' ', $messages) . '" />';
            echo implode("\n", $messages);

            echo "\n";
        }

        echo "\n</pre></td></tr>";

        echo "<tr><th>GLPI constants</th></tr>\n";
        echo "<tr class='tab_bg_1'><td><pre>\n&nbsp;\n";
        foreach (get_defined_constants() as $constant_name => $constant_value) {
            if (preg_match('/^GLPI_/', $constant_name)) {
                echo $constant_name . ': ' . $constant_value . "\n";
            }
        }
        echo "\n</pre></td></tr>";

        self::showLibrariesInformation();

        foreach ($CFG_GLPI["systeminformations_types"] as $type) {
            $tmp = new $type();
            $tmp->showSystemInformations($width);
        }

        Session::loadLanguage($oldlang);

        $files = array_merge(
            glob(GLPI_LOCAL_I18N_DIR . "/**/*.php"),
            glob(GLPI_LOCAL_I18N_DIR . "/**/*.mo")
        );
        sort($files);
        if (count($files)) {
            echo "<tr><th>Locales overrides</th></tr>\n";
            echo "<tr class='tab_bg_1'><td>\n";
            foreach ($files as $file) {
                echo "$file<br/>\n";
            }
            echo "</td></tr>";
        }

        echo "<tr class='tab_bg_1'><td>[/code]\n</td></tr>";

        echo "<tr class='tab_bg_2'><th>" . __('To copy/paste in your support request') . "</th></tr>\n";

        echo "</table>";
        $serverLogs = ob_get_clean();

        $form = [
            'action' => Toolbox::getItemTypeFormURL(__CLASS__),
            'buttons' => [
                'submit' => [
                    'type' => 'submit',
                    'name' => 'update',
                    'value' => _sx('button', 'Save'),
                    'class' => 'btn btn-secondary',
                ],
            ],
            'content' => [
               __('General setup') => [
                   'visible' => true,
                   'inputs' => [
                       __('Log Level') => [
                           'type' => 'select',
                           'name' => 'event_loglevel',
                           'values' => [
                               1 => __('1- Critical (login error only)'),
                               2 => __('2- Severe (not used)'),
                               3 => __('3- Important (successful logins)'),
                               4 => __('4- Notices (add, delete, tracking)'),
                               5 => __('5- Complete (all)'),
                           ],
                           'value' => $CFG_GLPI["event_loglevel"],
                       ],
                       __('Maximal number of automatic actions (run by CLI)') => [
                        'type' => 'number',
                        'name' => 'cron_limit',
                        'min' => 1,
                        'max' => 30,
                        'value' => $CFG_GLPI["cron_limit"],
                        'col_lg' => 8,
                        'col_md' => 12,
                       ],
                       __('Logs in files (SQL, email, automatic action...)') => [
                        'type' => 'checkbox',
                        'name' => 'use_log_in_files',
                        'value' => $CFG_GLPI["use_log_in_files"],
                        'col_lg' => 6,
                       ],
                       _n('SQL replica', 'SQL replicas', 1) => [
                        'type' => 'checkbox',
                        'name' => 'use_db_slave',
                        'value' => DBConnection::isDBSlaveActive(),
                        'col_lg' => 6,
                       ],
                   ]
               ],
               __('Maintenance mode') => [
                  'visible' => true,
                  'inputs' => [
                      __('Maintenance mode') => [
                          'type' => 'checkbox',
                          'name' => 'maintenance_mode',
                          'value' => $CFG_GLPI["maintenance_mode"],
                      ],
                      __('Maintenance text') => [
                        'type' => 'textarea',
                        'name' => 'maintenance_text',
                        'value' => $CFG_GLPI["maintenance_text"],
                        'col_lg' => 8,
                        'col_md' => 12,
                      ]
                  ]
               ],
               __('Proxy configuration for upgrade check') => [
                  'visible' => true,
                  'inputs' => [
                      __('Server') => [
                          'type' => 'text',
                          'name' => 'proxy_name',
                          'value' => $CFG_GLPI["proxy_name"],
                          'col_lg' => 6,
                      ],
                      __('Port') => [
                        'type' => 'text',
                        'name' => 'proxy_port',
                        'value' => $CFG_GLPI["proxy_port"],
                        'col_lg' => 6,
                      ],
                      __('Login') => [
                        'type' => 'text',
                        'name' => 'proxy_user',
                        'value' => $CFG_GLPI["proxy_user"],
                        'col_lg' => 6,
                      ],
                      __('Password') => [
                        'type' => 'password',
                        'name' => 'proxy_passwd',
                        'value' => '',
                        'autocomplete' => 'new-password',
                        'col_lg' => 6,
                        'after' => <<<HTML
                        <input type='checkbox' name='_blank_proxy_passwd' id='_blank_proxy_passwd'/>
                        <label for='_blank_proxy_passwd'>$clear</label>
                     HTML,
                      ],
                  ]
               ],
               Telemetry::getViewLink() => [
                 'visible' => true,
                 'inputs' => [
                    __('Information about system installation and configuration') => [
                       'content' => $serverLogs
                    ]
                 ]
               ]
            ]
        ];
        renderTwigForm($form);
    }


    /**
     * Retrieve full directory of a lib
     * @param  $libstring  object, class or function
     * @return string       the path or false
     *
     * @since 9.1
     */
    public static function getLibraryDir($libstring)
    {
        if (is_object($libstring)) {
            return realpath(dirname((new ReflectionObject($libstring))->getFileName()));
        } elseif (class_exists($libstring) || interface_exists($libstring)) {
            return realpath(dirname((new ReflectionClass($libstring))->getFileName()));
        } elseif (function_exists($libstring)) {
            // Internal function have no file name
            $path = (new ReflectionFunction($libstring))->getFileName();
            return ($path ? realpath(dirname($path)) : false);
        }
        return false;
    }


    /**
     * get libraries list
     *
     * @param $all   (default false)
     * @return array dependencies list
     *
     * @since 9.4
     */
    public static function getLibraries($all = false)
    {
        $pm = new PHPMailer();
        $sp = new SimplePie\SimplePie();

        // use same name that in composer.json
        $deps = [[ 'name'    => 'htmlawed/htmlawed',
                   'version' => hl_version() ,
                   'check'   => 'hl_version' ],
                 [ 'name'    => 'phpmailer/phpmailer',
                   'version' => $pm::VERSION,
                   'check'   => 'PHPMailer\\PHPMailer\\PHPMailer' ],
                 [ 'name'    => 'simplepie/simplepie',
                   'version' =>  \SimplePie\SimplePie::VERSION,
                   'check'   => $sp ],
                 [ 'name'    => 'tecnickcom/tcpdf',
                   'version' => TCPDF_STATIC::getTCPDFVersion(),
                   'check'   => 'TCPDF' ],
                 [ 'name'    => 'michelf/php-markdown',
                   'check'   => 'Michelf\\Markdown' ],
                 // [ 'name'    => 'true/punycode',
                 //   'check'   => 'TrueBV\\Punycode' ],
                 [ 'name'    => 'iamcal/lib_autolink',
                   'check'   => 'autolink' ],
                 [ 'name'    => 'sabre/dav',
                   'check'   => 'Sabre\\DAV\\Version' ],
                 [ 'name'    => 'sabre/http',
                   'check'   => 'Sabre\\HTTP\\Version' ],
                 [ 'name'    => 'sabre/uri',
                   'check'   => 'Sabre\\Uri\\Version' ],
                 [ 'name'    => 'sabre/vobject',
                   'check'   => 'Sabre\\VObject\\Component' ],
                 [ 'name'    => 'laminas/laminas-cache',
                   'check'   => 'Laminas\\Cache\\Module' ],
                 [ 'name'    => 'laminas/laminas-i18n',
                   'check'   => 'Laminas\\I18n\\Module' ],
                 [ 'name'    => 'laminas/laminas-serializer',
                   'check'   => 'Laminas\\Serializer\\Module' ],
                 [ 'name'    => 'monolog/monolog',
                   'check'   => 'Monolog\\Logger' ],
                 [ 'name'    => 'sebastian/diff',
                   'check'   => 'SebastianBergmann\\Diff\\Diff' ],
                 [ 'name'    => 'elvanto/litemoji',
                   'check'   => 'LitEmoji\\LitEmoji' ],
                 [ 'name'    => 'symfony/console',
                   'check'   => 'Symfony\\Component\\Console\\Application' ],
                 [ 'name'    => 'scssphp/scssphp',
                   'check'   => 'ScssPhp\ScssPhp\Compiler' ],
                 [ 'name'    => 'laminas/laminas-mail',
                   'check'   => 'Laminas\\Mail\\Protocol\\Imap' ],
                 [ 'name'    => 'laminas/laminas-mime',
                   'check'   => 'Laminas\\Mime\\Mime' ],
                 [ 'name'    => 'rlanvin/php-rrule',
                   'check'   => 'RRule\\RRule' ],
                 [ 'name'    => 'blueimp/jquery-file-upload',
                   'check'   => 'UploadHandler' ],
                 [ 'name'    => 'ramsey/uuid',
                   'check'   => 'Ramsey\\Uuid\\Uuid' ],
                 [ 'name'    => 'psr/log',
                   'check'   => 'Psr\\Log\\LoggerInterface' ],
                 [ 'name'    => 'psr/simple-cache',
                   'check'   => 'Psr\\SimpleCache\\CacheInterface' ],
                 [ 'name'    => 'mexitek/phpcolors',
                   'check'   => 'Mexitek\\PHPColors\\Color' ],
                 [ 'name'    => 'guzzlehttp/guzzle',
                   'check'   => 'GuzzleHttp\\Client' ],
                 [ 'name'    => 'guzzlehttp/psr7',
                   'check'   => 'GuzzleHttp\\Psr7\\Response' ],
                 [ 'name'    => 'wapmorgan/unified-archive',
                   'check'   => 'wapmorgan\\UnifiedArchive\\UnifiedArchive' ],
                 [ 'name'    => 'paragonie/sodium_compat',
                   'check'   => 'ParagonIE_Sodium_Compat' ],
        ];
        if (Toolbox::canUseCAS()) {
            $deps[] = [
               'name'    => 'phpCas',
               'version' => phpCAS::getVersion(),
               'check'   => 'phpCAS'
            ];
        }
        return $deps;
    }


    /**
     * show Libraries information in system information
     *
     * @since 0.84
    **/
    public static function showLibrariesInformation()
    {

        // No gettext

        echo "<tr class='tab_bg_2'><th>Libraries</th></tr>\n";
        echo "<tr class='tab_bg_1'><td><pre>\n&nbsp;\n";

        foreach (self::getLibraries() as $dep) {
            $path = self::getLibraryDir($dep['check']);
            if ($path) {
                echo "{$dep['name']} ";
                if (isset($dep['version'])) {
                    echo "version {$dep['version']} ";
                }
                echo "in ($path)\n";
            } else {
                echo "{$dep['name']} not found\n";
            }
        }

        echo "\n</pre></td></tr>";
    }


    /**
     * Dropdown for global management config
     *
     * @param string       $name   select name
     * @param string       $value  default value
     * @param integer|null $rand   rand
    **/
    public static function dropdownGlobalManagement($name, $value, $rand = null)
    {

        $choices = [
           __('Yes - Restrict to unit management for manual add'),
           __('Yes - Restrict to global management for manual add'),
           __('No'),
        ];
        Dropdown::showFromArray($name, $choices, ['value' => $value, 'rand' => $rand]);
    }


    /**
     * Get language in GLPI associated with the value coming from LDAP/SSO
     * Value can be, for example : English, en_EN, en-EN or en
     *
     * @param string $lang the value coming from LDAP/SSO
     *
     * @return string locale's php page in GLPI or '' is no language associated with the value
    **/
    public static function getLanguage($lang)
    {
        global $CFG_GLPI;

        // Alternative language code: en-EN --> en_EN
        $altLang = str_replace("-", "_", $lang);

        // Search in order : ID or extjs dico or tinymce dico / native lang / english name
        //                   / extjs dico / tinymce dico
        // ID  or extjs dico or tinymce dico
        foreach ($CFG_GLPI["languages"] as $ID => $language) {
            if (
                (strcasecmp($lang, $ID) == 0)
                || (strcasecmp($altLang, $ID) == 0)
                || (strcasecmp($lang, $language[2]) == 0)
                || (strcasecmp($lang, $language[3]) == 0)
            ) {
                return $ID;
            }
        }

        // native lang
        foreach ($CFG_GLPI["languages"] as $ID => $language) {
            if (strcasecmp($lang, $language[0]) == 0) {
                return $ID;
            }
        }

        // english lang name
        foreach ($CFG_GLPI["languages"] as $ID => $language) {
            if (strcasecmp($lang, $language[4]) == 0) {
                return $ID;
            }
        }

        return "";
    }


    public static function detectRootDoc()
    {
        global $CFG_GLPI;

        if (!isset($CFG_GLPI["root_doc"])) {
            if (!isset($_SERVER['REQUEST_URI'])) {
                $_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
            }

            $currentdir = getcwd();
            chdir(GLPI_ROOT);
            $glpidir    = str_replace(
                str_replace('\\', '/', getcwd()),
                "",
                str_replace('\\', '/', $currentdir)
            );
            chdir($currentdir);
            $globaldir  = Html::cleanParametersURL($_SERVER['REQUEST_URI']);
            $globaldir  = preg_replace("/\/[0-9a-zA-Z\.\-\_]+\.php/", "", $globaldir);

            // api exception
            if (strpos($globaldir, 'api/') !== false) {
                $globaldir = preg_replace("/(.*\/)api\/.*/", "$1", $globaldir);
            }

            $CFG_GLPI["root_doc"] = str_replace($glpidir, "", $globaldir);
            $CFG_GLPI["root_doc"] = preg_replace("/\/$/", "", $CFG_GLPI["root_doc"]);
            // urldecode for space redirect to encoded URL : change entity
            $CFG_GLPI["root_doc"] = urldecode($CFG_GLPI["root_doc"]);
        }
    }


    /**
     * Display debug information for dbslave
    **/
    public function showDebug()
    {

        $options = [
           'diff' => 0,
           'name' => '',
        ];
        NotificationEvent::debugEvent(new DBConnection(), $options);
    }


    /**
     * Display field unicity criterias form
    **/
    public function showFormFieldUnicity()
    {

        $unicity = new FieldUnicity();
        $unicity->showForm(1, -1);
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'Preference':
                return __('Personalization');

            case 'User':
                if (
                    User::canUpdate()
                    && $item->currentUserHaveMoreRightThan($item->getID())
                ) {
                    return __('Settings');
                }
                break;

            case __CLASS__:
                $tabs = [
                   1 => __('General setup'),  // Display
                   2 => __('Default values'), // Prefs
                   3 => __('Assets'),
                   4 => __('Assistance'),
                ];
                if (Config::canUpdate()) {
                    $tabs[9]  = __('Logs purge');
                    $tabs[5]  = __('System');
                    $tabs[10] = __('Security');
                    $tabs[7]  = __('Performance');
                    $tabs[8]  = __('API');
                    $tabs[11] = Impact::getTypeName();
                }

                if (
                    DBConnection::isDBSlaveActive()
                    && Config::canUpdate()
                ) {
                    $tabs[6]  = _n('SQL replica', 'SQL replicas', Session::getPluralNumber());  // Slave
                }
                return $tabs;

            case Impact::getType():
                return Impact::getTypeName();
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        global $CFG_GLPI;

        if ($item->getType() == 'Preference') {
            $config = new self();
            $user   = new User();
            if ($user->getFromDB(Session::getLoginUserID())) {
                $user->computePreferences();
                $config->showFormUserPrefs($user->fields);
            }
        } elseif ($item->getType() == 'User') {
            $config = new self();
            $user   = new User();
            if ($user->getFromDB(Session::getLoginUserID())) {
                $user->computePreferences();
                $config->showFormUserPrefs($user->fields);
            }
        } elseif ($item->getType() == __CLASS__) {
            switch ($tabnum) {
                case 1:
                    $item->showFormDisplay();
                    break;

                case 2:
                    $item->showFormUserPrefs($CFG_GLPI);
                    break;

                case 3:
                    $item->showFormInventory();
                    break;

                case 4:
                    $item->showFormHelpdesk();
                    break;

                case 5:
                    $item->showSystemInformations();
                    break;

                case 6:
                    $item->showFormDBSlave();
                    break;

                case 7:
                    $item->showPerformanceInformations();
                    break;

                case 8:
                    $item->showFormAPI();
                    break;

                case 9:
                    $item->showFormLogs();
                    break;

                case 10:
                    $item->showFormSecurity();
                    break;

                case 11:
                    Impact::showConfigForm();
                    break;
            }
        }
        return true;
    }

    /**
     * Display database engine checks report
     *
     * @since 9.3
     *
     * @param boolean $fordebug display for debug (no html required) (false by default)
     * @param string  $version  Version to check (mainly from install), defaults to null
     *
     * @return integer 2: missing extension,  1: missing optionnal extension, 0: OK,
     **/
    public static function displayCheckDbEngine($fordebug = false, $version = null)
    {
        global $CFG_GLPI;

        $error = 0;
        $result = self::checkDbEngine($version);
        $version = key($result);
        $db_ver = $result[$version];

        $ok_message = sprintf(__s('Database version seems correct (%s) - Perfect!'), $version);
        $ko_message = sprintf(__s('Your database engine version seems too old: %s.'), $version);

        if (!$db_ver) {
            $error = 2;
        }
        $message = $error > 0 ? $ko_message : $ok_message;

        $img = "<img src='" . $CFG_GLPI['root_doc'] . "/pics/";
        $img .= ($error > 0 ? "ko_min" : "ok_min") . ".png' alt='$message' title='$message'/>";

        if (isCommandLine()) {
            echo $message . "\n";
        } elseif ($fordebug) {
            echo $img . $message . "\n";
        } else {
            $html = "<td";
            if ($error > 0) {
                $html .= " class='red'";
            }
            $html .= ">";
            $html .= $img;
            $html .= '</td>';
            echo $html;
        }
        return $error;
    }

    /**
     * Display extensions checks report
     *
     * @since 9.2
     *
     * @param boolean    $fordebug display for debug (no html required) (false by default)
     *
     * @return integer 2: missing extension,  1: missing optionnal extension, 0: OK,
     *
     * @deprecated 9.5.0
     **/
    public static function displayCheckExtensions($fordebug = false)
    {
        Toolbox::deprecated();

        global $CFG_GLPI;

        $report = self::checkExtensions();

        foreach ($report['good'] as $ext => $msg) {
            if (!$fordebug) {
                echo "<tr class=\"tab_bg_1\"><td class=\"left b\">" . sprintf(__('%s extension test'), $ext) . "</td>";
                echo "<td><img src=\"{$CFG_GLPI['root_doc']}/pics/ok_min.png\"
                           alt=\"$msg\"
                           title=\"$msg\"></td>";
                echo "</tr>";
            } else {
                echo  "<img src=\"{$CFG_GLPI['root_doc']}/pics/ok_min.png\"
                        alt=\"\">$msg\n";
            }
        }

        foreach ($report['may'] as $ext => $msg) {
            if (!$fordebug) {
                echo "<tr class=\"tab_bg_1\"><td class=\"left b\">" . sprintf(__('%s extension test'), $ext) . "</td>";
                echo "<td><img src=\"{$CFG_GLPI['root_doc']}/pics/warning_min.png\"> " . $msg . "</td>";
                echo "</tr>";
            } else {
                echo "<img src=\"{$CFG_GLPI['root_doc']}/pics/warning_min.png\">" . $msg . "\n";
            }
        }

        foreach ($report['missing'] as $ext => $msg) {
            if (!$fordebug) {
                echo "<tr class=\"tab_bg_1\"><td class=\"left b\">" . sprintf(__('%s extension test'), $ext) . "</td>";
                echo "<td class=\"red\"><img src=\"{$CFG_GLPI['root_doc']}/pics/ko_min.png\"> " . $msg . "</td>";
                echo "</tr>";
            } else {
                echo "<img src=\"{$CFG_GLPI['root_doc']}/pics/ko_min.png\">" . $msg . "\n";
            }
        }

        return $report['error'];
    }


    /**
     * Check for needed extensions
     *
     * @since 9.3
     *
     * @param string $raw Raw version to check (mainly from install), defaults to null
     *
     * @return boolean
    **/
    public static function checkDbEngine($raw = null)
    {
        // MySQL >= 5.6 || MariaDB >= 10
        if ($raw === null) {
            global $DB;
            $raw = $DB->getVersion();
        }

        /** @var array $found */
        preg_match('/(\d+(\.)?)+/', $raw, $found);
        $version = $found[0];

        $db_ver = version_compare($version, '5.6', '>=');
        return [$version => $db_ver];
    }


    /**
     * Check for needed extensions
     *
     * @since 9.2 Method signature and return has changed
     *
     * @param null|array $list     Extensions list (from plugins)
     *
     * @return array [
     *                'error'     => integer 2: missing extension,  1: missing optionnal extension, 0: OK,
     *                'good'      => [ext => message],
     *                'missing'   => [ext => message],
     *                'may'       => [ext => message]
     *               ]
    **/
    public static function checkExtensions($list = null)
    {
        if ($list === null) {
            $extensions_to_check = [
               'mysqli'   => [
                  'required'  => true
               ],
               'ctype'    => [
                  'required'  => true,
                  'function'  => 'ctype_digit',
               ],
               'fileinfo' => [
                  'required'  => true,
                  'class'     => 'finfo'
               ],
               'json'     => [
                  'required'  => true,
                  'function'  => 'json_encode'
               ],
               'mbstring' => [
                  'required'  => true,
               ],
               'iconv'    => [
                  'required'  => true,
               ],
               'zlib'     => [
                  'required'  => true,
               ],
               'curl'      => [
                  'required'  => true,
               ],
               'gd'       => [
                  'required'  => true,
               ],
               'simplexml' => [
                  'required'  => true,
               ],
               'xml'        => [
                  'required'  => true,
                  'function'  => 'utf8_decode'
               ],
               //to sync/connect from LDAP
               'ldap'       => [
                  'required'  => false,
               ],
               //to enhance perfs
               'Zend OPcache' => [
                  'required'  => false
               ],
               //to enhance perfs
               'APCu'      => [
                  'required'  => false,
                  'function'  => 'apcu_fetch'
               ],
               //for XMLRPC API
               'xmlrpc'     => [
                  'required'  => false
               ],
               //for CAS lib
               'CAS'     => [
                  'required' => false,
                  'class'    => 'phpCAS'
               ],
               'exif' => [
                  'required'  => false
               ],
               'intl' => [
                  'required' => true
               ],
               'sodium' => [
                  'required' => false
               ]
            ];
        } else {
            $extensions_to_check = $list;
        }

        $report = [
           'error'     => 0,
           'good'      => [],
           'missing'   => [],
           'may'       => []
        ];

        //check for PHP extensions
        foreach ($extensions_to_check as $ext => $params) {
            $success = true;

            if (isset($params['call'])) {
                $success = call_user_func($params['call']);
            } elseif (isset($params['function'])) {
                if (!function_exists($params['function'])) {
                    $success = false;
                }
            } elseif (isset($params['class'])) {
                if (!class_exists($params['class'])) {
                    $success = false;
                }
            } else {
                if (!extension_loaded($ext)) {
                    $success = false;
                }
            }

            if ($success) {
                $msg = sprintf(__('%s extension is installed'), $ext);
                $report['good'][$ext] = $msg;
            } else {
                if (isset($params['required']) && $params['required'] === true) {
                    if ($report['error'] < 2) {
                        $report['error'] = 2;
                    }
                    $msg = sprintf(__('%s extension is missing'), $ext);
                    $report['missing'][$ext] = $msg;
                } else {
                    if ($report['error'] < 1) {
                        $report['error'] = 1;
                    }
                    $msg = sprintf(__('%s extension is not present'), $ext);
                    $report['may'][$ext] = $msg;
                }
            }
        }

        return $report;
    }


    /**
     * Get data directories for checks
     *
     * @return array
     *
     * @deprecated 9.5.0
     */
    private static function getDataDirectories()
    {
        Toolbox::deprecated();

        $dir_to_check = [
           GLPI_CONFIG_DIR      => __('Checking write permissions for setting files'),
           GLPI_DOC_DIR         => __('Checking write permissions for document files'),
           GLPI_DUMP_DIR        => __('Checking write permissions for dump files'),
           GLPI_SESSION_DIR     => __('Checking write permissions for session files'),
           GLPI_CRON_DIR        => __('Checking write permissions for automatic actions files'),
           GLPI_GRAPH_DIR       => __('Checking write permissions for graphic files'),
           GLPI_LOCK_DIR        => __('Checking write permissions for lock files'),
           GLPI_PLUGIN_DOC_DIR  => __('Checking write permissions for plugins document files'),
           GLPI_TMP_DIR         => __('Checking write permissions for temporary files'),
           GLPI_CACHE_DIR       => __('Checking write permissions for cache files'),
           GLPI_RSS_DIR         => __('Checking write permissions for rss files'),
           GLPI_UPLOAD_DIR      => __('Checking write permissions for upload files'),
           GLPI_PICTURE_DIR     => __('Checking write permissions for pictures files')
        ];

        return $dir_to_check;
    }


    /**
     * Check Write Access to needed directories
     *
     * @param boolean $fordebug display for debug (no html, no gettext required) (false by default)
     *
     * @return integer 2 : creation error 1 : delete error 0: OK
     *
     * @deprecated 9.5.0
    **/
    public static function checkWriteAccessToDirs($fordebug = false)
    {
        Toolbox::deprecated();

        global $CFG_GLPI;

        // Only write test for GLPI_LOG as SElinux prevent removing log file.
        if (!$fordebug) {
            echo "<tr class='tab_bg_1'><td class='b left'>" .
                  __('Checking write permissions for log files') . "</td>";
        }

        $can_write_logs = false;

        try {
            global $PHPLOGGER;
            $PHPLOGGER->addRecord(Monolog\Logger::WARNING, "Test logger");
            $can_write_logs = true;
        } catch (\UnexpectedValueException $e) {
            $catched = true;
            //empty catch
        }

        if ($can_write_logs) {
            if ($fordebug) {
                echo "<img src='" . $CFG_GLPI['root_doc'] . "/pics/ok_min.png' alt=\"" . __s('OK') . "\">" .
                       GLPI_LOG_DIR . " : OK\n";
            } else {
                echo "<td><img src='" . $CFG_GLPI['root_doc'] . "/pics/ok_min.png' alt=\"" .
                           __s('A file was created - Perfect!') . "\" title=\"" .
                           __s('A file was created - Perfect!') . "\"></td></tr>";
            }
        } else {
            if ($fordebug) {
                echo "<img src='" . $CFG_GLPI['root_doc'] . "/pics/warning_min.png'>" .
                      sprintf(__('Check permissions to the directory: %s'), GLPI_LOG_DIR) . "\n";
            } else {
                echo "<td><img src='" . $CFG_GLPI['root_doc'] . "/pics/warning_min.png'>" .
                     "<p class='red'>" . __('The file could not be created.') . "</p>" .
                     sprintf(__('Check permissions to the directory: %s'), GLPI_LOG_DIR) . "</td></tr>";
            }
        }

        if ($can_write_logs) {
            $dir_to_check = self::getDataDirectories();
            //log dir is tested differently below
            unset($dir_to_check[GLPI_LOG_DIR]);
            $error = 0;
            foreach ($dir_to_check as $dir => $message) {
                if (!$fordebug) {
                    echo "<tr class='tab_bg_1'><td class='left b'>" . $message . "</td>";
                }
                $tmperror = Toolbox::testWriteAccessToDirectory($dir);

                $errors = [
                   4 => __('The directory could not be created.'),
                   3 => __('The directory was created but could not be removed.'),
                   2 => __('The file could not be created.'),
                   1 => __("The file was created but can't be deleted.")
                ];

                if ($tmperror > 0) {
                    if ($fordebug) {
                        echo "<img src='" . $CFG_GLPI['root_doc'] . "/pics/ko_min.png'> " .
                              sprintf(__('Check permissions to the directory: %s'), $dir) .
                              " " . $errors[$tmperror] . "\n";
                    } else {
                        echo "<td><img src='" . $CFG_GLPI['root_doc'] . "/pics/ko_min.png'><p class='red'>" .
                           $errors[$tmperror] . "</p> " .
                           sprintf(__('Check permissions to the directory: %s'), $dir) .
                           "'</td></tr>";
                    }
                    $error = 2;
                } else {
                    if ($fordebug) {
                        echo "<img src='" . $CFG_GLPI['root_doc'] . "/pics/ok_min.png' alt=\"" . __s('OK') .
                           "\">$dir : OK\n";
                    } else {
                        echo "<td><img src='" . $CFG_GLPI['root_doc'] . "/pics/ok_min.png' alt=\"" .
                                 __s('A file and a directory have be created and deleted - Perfect!') . "\"
                           title=\"" .
                                 __s('A file and a directory have be created and deleted - Perfect!') . "\">" .
                           "</td></tr>";
                    }
                }
            }
        } else {
            $error = 2;
        }

        $check_access = false;
        $directories = array_keys(self::getDataDirectories());

        foreach ($directories as $dir) {
            if (Toolbox::startsWith($dir, GLPI_ROOT)) {
                //only check access if one of the data directories is under GLPI document root.
                $check_access = true;
                break;
            }
        }

        if ($check_access) {
            $oldhand = set_error_handler(function ($errno, $errmsg, $filename, $linenum, $vars) {
                return true;
            });
            $oldlevel = error_reporting(0);

            //create a context to set timeout
            $context = stream_context_create([
               'http' => [
                  'timeout' => 2.0
               ]
            ]);

            /* TODO: could be improved, only default vhost checked */
            $protocol = 'http';
            if (isset($_SERVER['HTTPS'])) {
                $protocol = 'https';
            }
            $uri = $protocol . '://' . $_SERVER['SERVER_NAME'] . $CFG_GLPI['root_doc'];

            if ($fic = fopen($uri . '/index.php?skipCheckWriteAccessToDirs=1', 'r', false, $context)) {
                fclose($fic);
                if (!$fordebug) {
                    echo "<tr class='tab_bg_1'><td class='b left'>" .
                       __('Web access to files directory is protected') . "</td>";
                }
                if ($fic = fopen($uri . '/files/_log/php-errors.log', 'r', false, $context)) {
                    fclose($fic);
                    if ($fordebug) {
                        echo "<img src='" . $CFG_GLPI['root_doc'] . "/pics/warning_min.png'>" .
                              __('Web access to the files directory should not be allowed') . "\n" .
                              __('Check the .htaccess file and the web server configuration.') . "\n";
                    } else {
                        echo "<td><img src='" . $CFG_GLPI['root_doc'] . "/pics/warning_min.png'>" .
                           "<p class='red'>" . __('Web access to the files directory should not be allowed') . "<br/>" .
                           __('Check the .htaccess file and the web server configuration.') . "</p></td></tr>";
                    }
                    $error = 1;
                } else {
                    if ($fordebug) {
                        echo "<img src='" . $CFG_GLPI['root_doc'] . "/pics/ok_min.png' alt=\"" .
                              __s('Web access to files directory is protected') . "\">" .
                              __s('Web access to files directory is protected') . " : OK\n";
                    } else {
                        echo "<td><img src='" . $CFG_GLPI['root_doc'] . "/pics/ok_min.png' alt=\"" .
                              __s('Web access to files directory is protected') . "\" title=\"" .
                              __s('Web access to files directory is protected') . "\"></td></tr>";
                    }
                }
            } else {
                $msg = __('Web access to the files directory should not be allowed but this cannot be checked automatically on this instance.') . "\n" .
                   "Make sure acces to <a href='{$CFG_GLPI['root_doc']}/files/_log/php-errors.log'>" . __('error log file') . "</a> is forbidden; otherwise review .htaccess file and web server configuration.";

                if ($fordebug) {
                    echo "<img src='" . $CFG_GLPI['root_doc'] . "/pics/warning_min.png'>" . $msg;
                } else {
                    echo "<td><img src='" . $CFG_GLPI['root_doc'] . "/pics/warning_min.png'>" .
                          "<p class='red'>" . nl2br($msg) . "</p></td></tr>";
                }
            }

            error_reporting($oldlevel);
            set_error_handler($oldhand);
        }

        return $error;
    }


    /**
     * Get current DB version (compatible with all version of GLPI)
     *
     * @since 0.85
     *
     * @return DB version
    **/
    public static function getCurrentDBVersion()
    {
        global $DB;

        //Default current case
        $select  = 'value AS version';
        $table   = 'glpi_configs';
        $where   = [
           'context'   => 'core',
           'name'      => 'version'
        ];

        if (!$DB->tableExists('glpi_configs')) {
            $select  = 'version';
            $table   = 'glpi_config';
            $where   = ['id' => 1];
        } elseif ($DB->fieldExists('glpi_configs', 'version')) {
            $select  = 'version';
            $where   = ['id' => 1];
        }

        $row = $DB->request([
           'SELECT' => [$select],
           'FROM'   => $table,
           'WHERE'  => $where
        ])->next();

        return trim($row['version']);
    }


    /**
     * Get config values
     *
     * @since 0.85
     *
     * @param $context  string   context to get values (default for glpi is core)
     * @param $names    array    of config names to get
     *
     * @return array of config values
    **/
    public static function getConfigurationValues($context, array $names = [])
    {
        global $DB;

        $query = [
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'context'   => $context
           ]
        ];

        if (count($names) > 0) {
            $query['WHERE']['name'] = $names;
        }

        $iterator = $DB->request($query);
        $result = [];
        while ($line = $iterator->next()) {
            $result[$line['name']] = $line['value'];
        }
        return $result;
    }

    /**
     * Load legacy configuration into $CFG_GLPI global variable.
     *
     * @param boolean $older_to_latest Search on old configuration objects first
     *
     * @return boolean True for success, false if an error occured
     */
    public static function loadLegacyConfiguration($older_to_latest = true)
    {

        global $CFG_GLPI, $DB;

        $config_tables_iterator = $DB->listTables('glpi_config%');
        $config_tables = [];
        foreach ($config_tables_iterator as $config_table) {
            $config_tables[] = $config_table['TABLE_NAME'];
        }

        $get_prior_to_078_config  = function () use ($DB, $config_tables) {
            if (!in_array('glpi_config', $config_tables)) {
                return false;
            }

            $config = new Config();
            $config->forceTable('glpi_config');
            if ($config->getFromDB(1)) {
                return $config->fields;
            }

            return false;
        };

        $get_078_to_latest_config    = function () use ($DB, $config_tables) {
            if (!in_array('glpi_configs', $config_tables)) {
                return false;
            }

            Config::forceTable('glpi_configs');

            $iterator = $DB->request(['FROM' => 'glpi_configs']);
            if ($iterator->count() === 0) {
                return false;
            }

            if ($iterator->count() === 1) {
                // 1 row = 0.78 to 0.84 config table schema
                return $iterator->next();
            }

            // multiple rows = 0.85+ config
            $config = [];
            while ($row = $iterator->next()) {
                if ('core' !== $row['context']) {
                    continue;
                }
                $config[$row['name']] = $row['value'];
            }
            return $config;
        };

        $functions = [];
        if ($older_to_latest) {
            // Try with old config table first : for update process management from < 0.80 to >= 0.80.
            $functions = [
               $get_prior_to_078_config,
               $get_078_to_latest_config,
            ];
        } else {
            // Normal load process : use normal config table. If problem try old one.
            $functions = [
               $get_078_to_latest_config,
               $get_prior_to_078_config,
            ];
        }

        $values = [];

        foreach ($functions as $function) {
            if ($config = $function()) {
                $values = $config;
                break;
            }
        }

        if (count($values) === 0) {
            return false;
        }

        $CFG_GLPI = array_merge($CFG_GLPI, $values);

        if (isset($CFG_GLPI['priority_matrix'])) {
            $CFG_GLPI['priority_matrix'] = importArrayFromDB($CFG_GLPI['priority_matrix']);
        }

        if (isset($CFG_GLPI['devices_in_menu'])) {
            $CFG_GLPI['devices_in_menu'] = importArrayFromDB($CFG_GLPI['devices_in_menu']);
        }

        if (isset($CFG_GLPI['lock_item_list'])) {
            $CFG_GLPI['lock_item_list'] = importArrayFromDB($CFG_GLPI['lock_item_list']);
        }

        if (
            isset($CFG_GLPI['lock_lockprofile_id'])
            && $CFG_GLPI['lock_use_lock_item']
            && $CFG_GLPI['lock_lockprofile_id'] > 0
            && !isset($CFG_GLPI['lock_lockprofile'])
        ) {
            $prof = new Profile();
            $prof->getFromDB($CFG_GLPI['lock_lockprofile_id']);
            $prof->cleanProfile();
            $CFG_GLPI['lock_lockprofile'] = $prof->fields;
        }

        // Path for icon of document type (web mode only)
        if (isset($CFG_GLPI['root_doc'])) {
            $CFG_GLPI['typedoc_icon_dir'] = $CFG_GLPI['root_doc'] . '/pics/icones';
        }

        return true;
    }


    /**
     * Set config values : create or update entry
     *
     * @since 0.85
     *
     * @param $context  string context to get values (default for glpi is core)
     * @param $values   array  of config names to set
     *
     * @return void
    **/
    public static function setConfigurationValues($context, array $values = [])
    {

        $glpikey = new GLPIKey();

        $config = new self();
        foreach ($values as $name => $value) {
            // Encrypt config values according to list declared to GLPIKey service
            if (!empty($value) && $glpikey->isConfigSecured($context, $name)) {
                $value = Toolbox::sodiumEncrypt($value);
            }

            if (
                $config->getFromDBByCrit([
                'context'   => $context,
                'name'      => $name
                ])
            ) {
                $input = ['id'      => $config->getID(),
                          'context' => $context,
                          'value'   => $value];

                $config->update($input);
            } else {
                $input = ['context' => $context,
                          'name'    => $name,
                          'value'   => $value];

                $config->add($input);
            }
        }
    }

    /**
     * Delete config entries
     *
     * @since 0.85
     *
     * @param $context string  context to get values (default for glpi is core)
     * @param $values  array   of config names to delete
     *
     * @return void
    **/
    public static function deleteConfigurationValues($context, array $values = [])
    {

        $config = new self();
        foreach ($values as $value) {
            if (
                $config->getFromDBByCrit([
                'context'   => $context,
                'name'      => $value
                ])
            ) {
                $config->delete(['id' => $config->getID()]);
            }
        }
    }


    public function getRights($interface = 'central')
    {

        $values = parent::getRights();
        unset(
            $values[CREATE],
            $values[DELETE],
            $values[PURGE]
        );

        return $values;
    }

    /**
     * Get message that informs the user he's using a development version
     *
     * @param boolean $bg Display a background
     *
     * @return void
     */
    public static function agreeDevMessage($bg = false)
    {
        $msg = '<p class="' . ($bg ? 'mig' : '') . 'red"><strong>' . __('You are using a development version, be careful!') . '</strong><br/>';
        $msg .= "<input type='checkbox' required='required' id='agree_dev' name='agree_dev'/><label for='agree_dev'>" . __('I know I am using a unstable version.') . "</label></p>";
        $msg .= "<script type=text/javascript>
            $(function() {
               $('[name=from_update]').on('click', function(event){
                  if(!$('#agree_dev').is(':checked')) {
                     event.preventDefault();
                     alert('" . __('Please check the unstable version checkbox.') . "');
                  }
               });
            });
            </script>";
        return $msg;
    }

    /**
     * Get a cache adapter from configuration
     *
     * @param string  $optname name of the configuration field
     * @param string  $context name of the configuration context (default 'core')
     * @param boolean $psr16   Whether to return a PSR16 compliant obkect or not (since Laminas Translator is NOT PSR16 compliant).
     *
     * @return Psr\SimpleCache\CacheInterface|Laminas\Cache\Storage\StorageInterface object
     */
    public static function getCache($optname, $context = 'core', $psr16 = true)
    {
        global $DB;

        /* Tested configuration values
         *
         * - {"adapter":"apcu"}
         * - {"adapter":"redis","options":{"server":{"host":"127.0.0.1"}},"plugins":["serializer"]}
         * - {"adapter":"filesystem"}
         * - {"adapter":"filesystem","options":{"cache_dir":"_cache_trans"},"plugins":["serializer"]}
         * - {"adapter":"dba"}
         * - {"adapter":"dba","options":{"pathname":"trans.db","handler":"flatfile"},"plugins":["serializer"]}
         * - {"adapter":"memcache","options":{"servers":["127.0.0.1"]}}
         * - {"adapter":"memcached","options":{"servers":["127.0.0.1"]}}
         * - {"adapter":"wincache"}
         *
         */
        // Read configuration
        $conf = [];
        if (
            $DB
            && $DB->connected
            && $DB->fieldExists(self::getTable(), 'context')
        ) {
            $conf = self::getConfigurationValues($context, [$optname]);
        }

        // Adapter default options
        $opt = [];
        if (isset($conf[$optname])) {
            $opt = json_decode($conf[$optname], true);
            Toolbox::logDebug("CACHE CONFIG  $optname", $opt);
        }

        if (!isset($opt['options']['namespace'])) {
            $namespace = "glpi_{$optname}_" . ITSM_VERSION;
            if ($DB) {
                $namespace .= md5(
                    (is_array($DB->dbhost) ? implode(' ', $DB->dbhost) : $DB->dbhost) . $DB->dbdefault
                );
            }
            $opt['options']['namespace'] = $namespace;
        }
        if (!isset($opt['adapter'])) {
            //  if (function_exists('apcu_fetch')) {

            //     $opt['adapter'] = 'apcu';
            //  } else {
            $opt['adapter'] = 'filesystem';
            //  }

            // Cannot skip integrity checks if 'adapter' was computed,
            // as computation result may differ for a different context (CLI VS web server).
            $skip_integrity_checks = false;

            $is_computed_config = true;
        } else {
            // Adapter names can be written using case variations.
            // see Laminas\Cache\Storage\AdapterPluginManager::$aliases
            $opt['adapter'] = strtolower($opt['adapter']);

            switch ($opt['adapter']) {
                // Cache adapters that can share their data accross processes
                case 'filesystem':
                case 'memcache':
                case 'memcached':
                case 'redis':
                    $skip_integrity_checks = true;
                    break;

                    // Cache adapters that cannot share their data accross processes
                case 'apcu':
                case 'memory':
                case 'session':
                default:
                    $skip_integrity_checks = false;
                    break;
            }

            $is_computed_config = false;
        }

        // Adapter specific options
        $ser = false;
        switch ($opt['adapter']) {
            case 'filesystem':
                if (!isset($opt['options']['cache_dir'])) {
                    $opt['options']['cache_dir'] = $optname;
                }
                // Make configured directory relative to GLPI cache directory
                $opt['options']['cache_dir'] = GLPI_CACHE_DIR . '/' . $opt['options']['cache_dir'];
                if (!is_dir($opt['options']['cache_dir'])) {
                    mkdir($opt['options']['cache_dir']);
                }
                $ser = true;
                break;

            case 'dba':
                if (!isset($opt['options']['pathname'])) {
                    $opt['options']['pathname'] = "$optname.data";
                }
                // Make configured path relative to GLPI cache directory
                $opt['options']['pathname'] = GLPI_CACHE_DIR . '/' . $opt['options']['pathname'];
                $ser = true;
                break;

            case 'redis':
                $ser = true;
                break;
        }
        // Some know plugins require data serialization
        if ($ser && !isset($opt['plugins'])) {
            $opt['plugins'] = ['serializer'];
        }

        // Create adapter
        try {
            $storage = Laminas\Cache\StorageFactory::factory($opt);
        } catch (Exception $e) {
            if (!$is_computed_config) {
                Toolbox::logError($e->getMessage());
            }

            // fallback to filesystem cache system if adapter was not explicitely defined in config
            $fallback = false;
            if ($is_computed_config && $opt['adapter'] != 'filesystem') {
                $opt = [
                   'adapter'   => 'filesystem',
                   'options'   => [
                      'cache_dir' => GLPI_CACHE_DIR . '/' . $optname,
                      'namespace' => $namespace,
                   ],
                   'plugins'   => ['serializer']
                ];

                if (!is_dir($opt['options']['cache_dir'])) {
                    mkdir($opt['options']['cache_dir']);
                }
                try {
                    $storage = Laminas\Cache\StorageFactory::factory($opt);
                    $fallback = true;
                } catch (Exception $e1) {
                    Toolbox::logError($e1->getMessage());
                    if (
                        isset($_SESSION['glpi_use_mode'])
                        && Session::DEBUG_MODE == $_SESSION['glpi_use_mode']
                    ) {
                        //preivous attempt has faled as well.
                        Toolbox::logDebug($e->getMessage());
                    }
                }
            }

            if ($fallback === false) {
                $opt = ['adapter' => 'memory'];
                $storage = Laminas\Cache\StorageFactory::factory($opt);
            }
            if (
                isset($_SESSION['glpi_use_mode'])
                && Session::DEBUG_MODE == $_SESSION['glpi_use_mode']
            ) {
                Toolbox::logDebug($e->getMessage());
            }
        }

        if ($psr16) {
            return new SimpleCache($storage, GLPI_CACHE_DIR, !$skip_integrity_checks);
        } else {
            return $storage;
        }
    }

    /**
     * Get available palettes
     *
     * @return array
     */
    public function getPalettes()
    {
        $themes_files = scandir(GLPI_ROOT . "/css/palettes/");
        $themes = [];
        foreach ($themes_files as $file) {
            if (strpos($file, ".scss") !== false) {
                $name     = substr($file, 1, -5);
                $themes[$name] = ucfirst($name);
            }
        }
        return $themes;
    }

    /**
     * Logs purge form
     *
     * @since 9.3
     *
     * @return void|boolean (display) Returns false if there is a rights error.
     */
    public function showFormLogs()
    {
        global $CFG_GLPI;

        if (!Config::canUpdate()) {
            return false;
        }

        $values = [
           self::DELETE_ALL => __("Delete all"),
           self::KEEP_ALL   => __("Keep all"),
        ];
        for ($i = 1; $i < 121; $i++) {
            $values[$i] = sprintf(
                _n(
                    "Delete if older than %s month",
                    "Delete if older than %s months",
                    $i
                ),
                $i
            );
        }

        $actions = [
           __('General') => [
              __("Add/update relation between items") => [ 'purge_addrelation', $CFG_GLPI["purge_addrelation"] ],
              __("Delete relation between items") => [ 'purge_deleterelation', $CFG_GLPI["purge_deleterelation"] ],
              __("Add the item") => [ 'purge_createitem', $CFG_GLPI["purge_createitem"] ],
              __("Delete the item") => [ 'purge_deleteitem', $CFG_GLPI["purge_deleteitem"] ],
              __("Restore the item") => [ 'purge_restoreitem', $CFG_GLPI["purge_restoreitem"] ],
              __("Update the item") => [ 'purge_updateitem', $CFG_GLPI["purge_updateitem"] ],
              __("Comments") => [ 'purge_comments', $CFG_GLPI["purge_comments"] ],
              __("Last update") => [ 'purge_datemod', $CFG_GLPI["purge_datemod"] ],
              __("Plugins") => [ 'purge_plugins', $CFG_GLPI["purge_plugins"] ],
           ],
           _n('Software', 'Software', Session::getPluralNumber()) => [
              __("Installation/uninstallation of software on items") => [ 'purge_item_software_install', $CFG_GLPI["purge_item_software_install"] ],
              __("Installation/uninstallation versions on softwares") => [ 'purge_software_version_install', $CFG_GLPI["purge_software_version_install"] ],
              __("Add/Remove items from software versions") => [ 'purge_software_item_install', $CFG_GLPI["purge_software_item_install"] ],
           ],
           __('Financial and administrative information') => [
              __("Add financial information to an item") => [ 'purge_infocom_creation', $CFG_GLPI["purge_infocom_creation"] ],
           ],
           User::getTypeName(Session::getPluralNumber()) => [
              __("Add/remove profiles to users") => [ 'purge_profile_user', $CFG_GLPI["purge_profile_user"] ],
              __("Add/remove groups to users") => [ 'purge_group_user', $CFG_GLPI["purge_group_user"] ],
              __("User authentication method changes") => [ 'purge_user_auth_changes', $CFG_GLPI["purge_user_auth_changes"] ],
              __("Deleted user in LDAP directory") => [ 'purge_userdeletedfromldap', $CFG_GLPI["purge_userdeletedfromldap"] ],
           ],
           _n('Component', 'Components', Session::getPluralNumber()) => [
              __("Add component") => [ 'purge_adddevice', $CFG_GLPI["purge_adddevice"] ],
              __("Update component") => [ 'purge_updatedevice', $CFG_GLPI["purge_updatedevice"] ],
              __("Disconnect a component") => [ 'purge_disconnectdevice', $CFG_GLPI["purge_disconnectdevice"] ],
              __("Connect a component") => [ 'purge_connectdevice', $CFG_GLPI["purge_connectdevice"] ],
              __("Delete component") => [ 'purge_deletedevice', $CFG_GLPI["purge_deletedevice"] ],
           ],
           __("All sections") => [
              __("Purge all log entries") => [ 'purge_all', $CFG_GLPI["purge_all"] ],
           ]
        ];

        $form = [
           'action' => $this->getFormURL(),
           'buttons' => [
              [
                 'type' => 'submit',
                 'name' => 'update',
                 'value' => __('Save'),
                 'class' => 'btn btn-secondary'
              ]
           ],
           'content' => [
              __("Logs purge configuration") => [
                 'visible' => true,
                 'inputs' => [
                    [
                       'type' => 'hidden',
                       'name' => 'id',
                       'value' => '1',
                    ],
                    __("Change all") => [
                       'name' => 'init_all',
                       'type' => 'select',
                       'id' => 'init_all_dropdown',
                       'values' => $values,
                       'hooks' => [
                          'change' => <<<JS
                           $('.purgelog_interval').val($(this).val()).trigger('change');
                        JS
                       ],
                       'col_lg' => 12,
                       'col_md' => 12,
                    ]
                 ],
              ],
           ]
        ];

        foreach ($actions as $section => $action) {
            $form['content'][$section] = [
               'visible' => true,
               'inputs' => []
            ];
            foreach ($action as $name => $value) {
                $form['content'][$section]['inputs'][$name] = [
                   'name' => $value[0],
                   'type' => 'select',
                   'class' => 'purgelog_interval',
                   'values' => $values,
                   'value' => $value[1],
                   'col_lg' => 6
                ];
            }
        }
        renderTwigForm($form);
    }

    /**
     * Show intervals for logs purge
     *
     * @since 9.3
     *
     * @param string $name    Parameter name
     * @param mixed  $value   Parameter value
     * @param array  $options Options
     *
     * @return void
     */
    public static function showLogsInterval($name, $value, $options = [])
    {

        $values = [
           self::DELETE_ALL => __("Delete all"),
           self::KEEP_ALL   => __("Keep all"),
        ];
        for ($i = 1; $i < 121; $i++) {
            $values[$i] = sprintf(
                _n(
                    "Delete if older than %s month",
                    "Delete if older than %s months",
                    $i
                ),
                $i
            );
        }
        $options = array_merge([
           'value'   => $value,
           'display' => false,
           'class'   => 'purgelog_interval'
        ], $options);

        $out = "<div class='{$options['class']}'>";
        $out .= Dropdown::showFromArray($name, $values, $options);
        $out .= "</div>";

        echo $out;
    }

    /**
     * Security policy form
     *
     * @since 9.5.0
     *
     * @return void|boolean (display) Returns false if there is a rights error.
     */
    public function showFormSecurity()
    {
        global $CFG_GLPI;

        if (!Config::canUpdate()) {
            return false;
        }

        $rand = mt_rand();

        $form = [
           'action' => Toolbox::getItemTypeFormURL(__CLASS__),
           'buttons' => [
              [
                 'type' => 'submit',
                 'name' => 'update',
                 'value' => __('Save'),
                 'class' => 'btn btn-secondary'
              ]
           ],
           'content' => [
              __('Password security policy') => [
                 'visible' => true,
                 'inputs' => [
                    __('Password security policy validation') => [
                       'type' => 'checkbox',
                       'name' => 'use_password_security',
                       'value' => $CFG_GLPI['use_password_security'],
                    ],
                    __('Password minimum length') => [
                       'type' => 'number',
                       'name' => 'password_min_length',
                       'value' => $CFG_GLPI['password_min_length'],
                       'min' => 4,
                       'max' => 30,
                       'rand' => $rand
                    ],
                    __('Password need digit') => [
                       'type' => 'checkbox',
                       'name' => 'password_need_number',
                       'value' => $CFG_GLPI['password_need_number'],
                    ],
                    __('Password need lowercase character') => [
                       'type' => 'checkbox',
                       'name' => 'password_need_letter',
                       'value' => $CFG_GLPI['password_need_letter'],
                    ],
                    __('Password need uppercase character') => [
                       'type' => 'checkbox',
                       'name' => 'password_need_caps',
                       'value' => $CFG_GLPI['password_need_caps'],
                    ],
                    __('Password need symbol') => [
                       'type' => 'checkbox',
                       'name' => 'password_need_symbol',
                       'value' => $CFG_GLPI['password_need_symbol'],
                    ],
                 ],
              ],
              __('Password expiration policy') => [
                 'visible' => true,
                 'inputs' => [
                    __('Password expiration delay (in days)') => [
                       'type' => 'number',
                       'name' => 'password_expiration_delay',
                       'min' => -1,
                       'max' => 365,
                       'after' => ' ( -1 = ' . __('Never') . ' )',
                       'value' => $CFG_GLPI['password_expiration_delay'],
                    ],
                    __('Password expiration notice time (in days)') => [
                       'type' => 'number',
                       'name' => 'password_expiration_notice',
                       'min' => -1,
                       'max' => 30,
                       'step' => 1,
                       'after' => ' ( -1 = ' . __('Notification disabled') . ' )',
                       'value' => $CFG_GLPI['password_expiration_notice'],
                    ],
                    __('Delay before account deactivation (in days)') => [
                       'type' => 'number',
                       'name' => 'password_expiration_lock_delay',
                       'min' => -1,
                       'max' => 30,
                       'step' => 1,
                       'after' => ' ( -1 = ' . __('Do not deactivate') . ' )',
                       'value' => $CFG_GLPI['password_expiration_lock_delay'],
                    ],
                 ],
              ]
           ]
        ];
        renderTwigForm($form);
    }

    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id'   => 'common',
            'name' => __('Characteristics')
        ];

        $tab[] = [
           'id'            => 1,
           'table'         => $this->getTable(),
           'field'         => 'value',
           'name'          => __('Value'),
           'massiveaction' => false
        ];

        return $tab;
    }

    public function getLogTypeID()
    {
        return [$this->getType(), 1];
    }

    public function post_addItem()
    {
        $this->logConfigChange($this->fields['context'], $this->fields['name'], (string)$this->fields['value'], '');
    }

    public function post_updateItem($history = 1)
    {
        global $DB;

        // Check if password expiration mechanism has been activated
        if (
            $this->fields['name'] == 'password_expiration_delay'
            && array_key_exists('value', $this->oldvalues)
            && (int)$this->oldvalues['value'] === -1
        ) {
            // As passwords will now expire, consider that "now" is the reference date of expiration delay
            $DB->update(
                User::getTable(),
                ['password_last_update' => $_SESSION['glpi_currenttime']],
                ['authtype' => Auth::DB_GLPI]
            );

            // Activate passwordexpiration automated task
            $DB->update(
                CronTask::getTable(),
                ['state' => 1,],
                ['name' => 'passwordexpiration']
            );
        }

        if (array_key_exists('value', $this->oldvalues)) {
            $this->logConfigChange(
                $this->fields['context'],
                $this->fields['name'],
                (string)$this->fields['value'],
                (string)$this->oldvalues['value']
            );
        }
    }

    public function post_purgeItem()
    {
        $this->logConfigChange($this->fields['context'], $this->fields['name'], '', (string)$this->fields['value']);
    }

    /**
     * Log config change in history.
     *
     * @param string $context
     * @param string $name
     * @param string $newvalue
     * @param string $oldvalue
     *
     * @return void
     */
    private function logConfigChange(string $context, string $name, string $newvalue, string $oldvalue): void
    {
        $glpi_key = new GLPIKey();
        if ($glpi_key->isConfigSecured($context, $name)) {
            $newvalue = $oldvalue = '********';
        }
        $oldvalue = $name . ($context !== 'core' ? ' (' . $context . ') ' : ' ') . $oldvalue;
        Log::constructHistory($this, ['value' => $oldvalue], ['value' => $newvalue]);
    }

    /**
     * Get the GLPI Config without unsafe keys like passwords and emails (true on $safer)
     *
     * @param boolean $safer do we need to clean more (avoid emails disclosure)
     * @return array of $CFG_GLPI without unsafe keys
     *
     * @since 9.5
     */
    public static function getSafeConfig($safer = false)
    {
        global $CFG_GLPI;

        $excludedKeys = array_flip(self::$undisclosedFields);
        $safe_config  = array_diff_key($CFG_GLPI, $excludedKeys);

        if ($safer) {
            $excludedKeys = array_flip(self::$saferUndisclosedFields);
            $safe_config = array_diff_key($safe_config, $excludedKeys);
        }

        return $safe_config;
    }


    public static function getIcon()
    {
        return "fas fa-cog";
    }

    /**
     * Get UUID
     *
     * @param string $type UUID type (e.g. 'instance' or 'registration')
     *
     * @return string
     */
    final public static function getUuid($type)
    {
        $conf = self::getConfigurationValues('core', [$type . '_uuid']);
        $uuid = null;
        if (!isset($conf[$type . '_uuid']) || empty($conf[$type . '_uuid'])) {
            $uuid = self::generateUuid($type);
        } else {
            $uuid = $conf[$type . '_uuid'];
        }
        return $uuid;
    }

    /**
     * Generates an unique identifier and store it
     *
     * @param string $type UUID type (e.g. 'instance' or 'registration')
     *
     * @return string
     */
    final public static function generateUuid($type)
    {
        $uuid = Toolbox::getRandomString(40);
        self::setConfigurationValues('core', [$type . '_uuid' => $uuid]);
        return $uuid;
    }
}
