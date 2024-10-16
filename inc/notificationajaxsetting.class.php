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
 *  This class manages the ajax notifications settings
 */
class NotificationAjaxSetting extends NotificationSetting
{
    public static function getTypeName($nb = 0)
    {
        return __('Browser followups configuration');
    }


    public function getEnableLabel()
    {
        return __('Enable followups from browser');
    }


    public static function getMode()
    {
        return Notification_NotificationTemplate::MODE_AJAX;
    }


    public function showFormConfig()
    {
        global $CFG_GLPI;

        $sounds = [
           'sound_a' => __('Sound') . ' A',
           'sound_b' => __('Sound') . ' B',
           'sound_c' => __('Sound') . ' C',
           'sound_d' => __('Sound') . ' D',
        ];

        $form = [
          'action' => Toolbox::getItemTypeFormURL(__CLASS__),
          'buttons' => [
              [
                  'name'  => 'update',
                  'value' => __('Save'),
                  'type'  => 'submit',
                  'class' => 'btn btn-secondary'
              ],
              !$CFG_GLPI['notifications_ajax'] ? [] : [
                  'name' => 'test_ajax_send',
                  'value' => __('Send a test browser notification to you'),
                  'type' => 'submit',
                  'class' => 'btn btn-warning'
              ]
          ],
          'content' => [
              _n('Browser notification', 'Browser notifications', Session::getPluralNumber()) => [
                  'visible' => true,
                  'inputs' => !$CFG_GLPI['notifications_ajax'] ? [
                      '' => [
                          'content' => __('Notifications are disabled.') .
                              "<a href='{$CFG_GLPI['root_doc']}/front/setup.notification.php'>" .
                              __('See configuration') .  "</a>",
                          'col_lg' => 12,
                          'col_md' => 12,
                      ]
                  ] : [
                      [
                          'type'    => 'hidden',
                          'name'    => 'id',
                          'value'   => 1,
                      ],
                      __('Default notification sound') => [
                          'type'    => 'select',
                          'name'    => 'notifications_ajax_sound',
                          'value'   => $CFG_GLPI['notifications_ajax_sound'],
                          'values' => [__('Disabled')] + $sounds,
                          'col_lg' => 12,
                          'col_md' => 12,
                      ],
                      __('Time to check for new notifications (in seconds)') => [
                          'type'    => 'number',
                          'name'    => 'notifications_ajax_check_interval',
                          'value'   => $CFG_GLPI['notifications_ajax_check_interval'],
                          'min'     => 5,
                          'max'     => 120,
                          'step'    => 5,
                          'col_lg' => 6
                      ],
                      __('URL of the icon') => [
                          'type'    => 'text',
                          'name'    => 'notifications_ajax_icon_url',
                          'value'   => $CFG_GLPI['notifications_ajax_icon_url'],
                          'placeholder' => "{$CFG_GLPI['root_doc']}/pics/glpi.png",
                          'col_lg' => 6,
                      ],
                  ],
              ]
          ]
        ];
        renderTwigForm($form);
    }
}
