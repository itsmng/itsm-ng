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
 * Notifications settings configuration class
 */
class NotificationSettingConfig extends CommonDBTM
{
    public $table           = 'glpi_configs';
    protected $displaylist  = false;
    public static $rightname       = 'config';

    public function update(array $input, $history = 1, $options = [])
    {
        if (isset($input['use_notifications'])) {
            $config = new Config();
            $tmp = [
               'id'                 => 1,
               'use_notifications'  => $input['use_notifications']
            ];
            $config->update($tmp);
            //disable all notifications types if notifications has been disabled
            if ($tmp['use_notifications'] == 0) {
                $modes = Notification_NotificationTemplate::getModes();
                foreach (array_keys($modes) as $mode) {
                    $input['notifications_' . $mode] = 0;
                }
            }
        }

        $config = new Config();
        foreach ($input as $k => $v) {
            if (substr((string) $k, 0, strlen('notifications_')) === 'notifications_') {
                $tmp = [
                   'id'  => 1,
                   $k    => $v
                ];
                $config->update($tmp);
            }
        }
    }

    /**
     * Show configuration form
     *
     * @return string|void
     */
    public function showForm($options = [])
    {
        global $CFG_GLPI;

        if (!isset($options['display'])) {
            $options['display'] = true;
        }

        $modes = Notification_NotificationTemplate::getModes();

        $out = '';
        $modes_settings = [];
        if (Session::haveRight("config", UPDATE)) {
            $form = [
               'action' => $CFG_GLPI['root_doc'] . '/front/setup.notification.php',
               'buttons' => [
                  [
                     'type' => 'submit',
                     'value' => __('Save'),
                     'class' => 'submit-button btn btn-secondary',
                  ],
               ],
               'content' => [
                  __('Notifications configuration') => [
                     'visible' => true,
                     'inputs' => [
                        __('Enable followup') => [
                           'name' => 'use_notifications',
                           'type' => 'select',
                           'values' => [
                              '0' => __('No'),
                              '1' => __('Yes'),
                           ],
                           'value' => $CFG_GLPI['use_notifications']
                        ],
                        __('Enable followups via email') => [
                           'name' => 'notifications_mailing',
                           'type' => 'select',
                           'values' => [
                              '0' => __('No'),
                              '1' => __('Yes'),
                           ],
                           'value' => $CFG_GLPI['notifications_mailing']
                        ],
                        __('Enable followups from browser') => [
                           'name' => 'notifications_ajax',
                           'type' => 'select',
                           'values' => [
                              '0' => __('No'),
                              '1' => __('Yes'),
                           ],
                           'value' => $CFG_GLPI['notifications_ajax']
                        ],
                        __('Enable followups via chat') => [
                           'name' => 'notifications_chat',
                           'type' => 'select',
                           'values' => [
                              '0' => __('No'),
                              '1' => __('Yes'),
                           ],
                           'value' => $CFG_GLPI['notifications_chat']
                        ],
                     ]
                  ]
               ]
            ];

            renderTwigForm($form);

            foreach (array_keys($modes) as $mode) {
                $settings_class = Notification_NotificationTemplate::getModeClass($mode, 'setting');
                $settings = new $settings_class();
                $modes_settings[$mode] = $settings;
            }
        }

        $notifs_on = false;
        if ($CFG_GLPI['use_notifications']) {
            foreach (array_keys($modes) as $mode) {
                if ($CFG_GLPI['notifications_' . $mode]) {
                    $notifs_on = true;
                    break;
                }
            }
        }

        if ($notifs_on) {
            $notificationLabel = _n('Notification', 'Notifications', Session::getPluralNumber());
            $links = [];

            /* Glocal parameters */
            if (Session::haveRight("config", READ)) {
                $links[] = ['url'   => $CFG_GLPI['root_doc'] . "/front/notificationtemplate.php",
                            'title' => _n('Notification template', 'Notification templates', Session::getPluralNumber())];
            }

            if (Session::haveRight("notification", READ) && $notifs_on) {
                $links[] = ['url'   => $CFG_GLPI['root_doc'] . "/front/notification.php",
                            'title' => _n('Notification', 'Notifications', Session::getPluralNumber())];
            } else {
                $links[] = ['url' => '#',
                            'title' => __('Unable to configure notifications: please configure at least one followup type using the above configuration.')];
            }

            /* Per notification parameters */
            foreach (array_keys($modes) as $mode) {
                if (Session::haveRight("config", UPDATE) && $CFG_GLPI['notifications_' . $mode]) {
                    $settings = $modes_settings[$mode];
                    $links[] = ['url'   => $settings->getFormURL(),
                                'title' => $settings->getTypeName()];
                }
            }

            $out .= <<<HTML
             <div class="container center mt-3">
                 <h2>$notificationLabel</h2>
                 <div class="w-50 mx-auto border rounded p-3">
         HTML;
            foreach ($links as $link) {
                $out .= <<<HTML
                     <a class="d-block text-start btn btn-outline-secondary" href="{$link['url']}">
                         {$link['title']}
                     </a><br>
            HTML;
            }
            $out .= <<<HTML
                 </div>
             </div>
         HTML;
        }

        if ($options['display']) {
            echo $out;
        } else {
            return $out;
        }
    }
}
