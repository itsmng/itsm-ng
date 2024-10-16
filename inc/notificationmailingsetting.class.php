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
 *  This class manages the mail settings
 */
class NotificationMailingSetting extends NotificationSetting
{
    public static function getTypeName($nb = 0)
    {
        return __('Email followups configuration');
    }


    public function getEnableLabel()
    {
        return __('Enable followups via email');
    }


    public static function getMode()
    {
        return Notification_NotificationTemplate::MODE_MAIL;
    }


    public function showFormConfig($options = [])
    {
        global $CFG_GLPI;

        if (!isset($options['display'])) {
            $options['display'] = true;
        }

        $form = [
          'action' => Toolbox::getItemTypeFormURL(__CLASS__),
          'buttons' => [
              [
                  'type' => 'submit',
                  'name' => 'update',
                  'value' => _x('button', 'Save'),
                  'class' => 'btn btn-secondary',
                  'icon' => 'fa fa-save',
              ],
              [
                 'type' => 'submit',
                  'name' => 'test_smtp_send',
                  'value' => __('Send a test email to the administrator'),
                  'class' => 'btn btn-secondary',
                  'icon' => 'fa fa-envelope',
                  'aria-hidden' => 'true',
              ]
          ],
          'content' => [
              _n('Email notification', 'Email notifications', Session::getPluralNumber()) => [
                  'visible' => true,
                  'inputs' => [
                      [
                          'type' => 'hidden',
                          'name' => 'id',
                          'value' => '1'
                      ],
                      __('Administrator email') => [
                          'name' => 'admin_email',
                          'type' => 'text',
                          'value' => $CFG_GLPI["admin_email"] ?? '',
                      ],
                      __('Administrator name') => [
                          'name' => 'admin_email_name',
                          'type' => 'text',
                          'value' => $CFG_GLPI["admin_email_name"] ?? '',
                      ],
                      __('From email') => [
                          'name' => 'from_email',
                          'type' => 'text',
                          'value' => $CFG_GLPI['from_email'] ?? '',
                      ],
                      __('From name') => [
                          'name' => 'from_email_name',
                          'type' => 'text',
                          'value' => $CFG_GLPI['from_email_name'] ?? '',
                      ],
                      __('Reply-to address') => [
                          'name' => 'admin_reply',
                          'type' => 'text',
                          'value' => $CFG_GLPI['admin_reply'] ?? '',
                      ],
                      __('Reply-to name') => [
                          'name' => 'admin_reply_name',
                          'type' => 'text',
                          'value' => $CFG_GLPI['admin_reply_name'] ?? '',
                      ],
                      __('No-Reply address') => [
                          'name' => 'admin_email_noreply',
                          'type' => 'text',
                          'value' => $CFG_GLPI['admin_email_noreply'] ?? '',
                      ],
                      __('No-Reply name') => [
                          'name' => 'admin_email_noreply_name',
                          'type' => 'text',
                          'value' => $CFG_GLPI['admin_email_noreply_name'] ?? '',
                      ],
                      __('Add documents into ticket notifications') => [
                          'name' => 'attach_ticket_documents_to_mail',
                          'type' => 'checkbox',
                          'value' => $CFG_GLPI['attach_ticket_documents_to_mail'] ?? '0',
                      ],
                      __('Email signature') => [
                          'name' => 'mailing_signature',
                          'type' => 'textarea',
                          'value' => $CFG_GLPI['mailing_signature'] ?? '',
                      ],
                      __('Way of sending emails') => [
                          'name' => 'smtp_mode',
                          'type' => 'select',
                          'values' => [
                              MAIL_MAIL => __('PHP'),
                              MAIL_SMTP => __('SMTP'),
                              MAIL_SMTPSSL => __('SMTP+SSL'),
                              MAIL_SMTPTLS => __('SMTP+TLS')
                          ],
                          'value' => $CFG_GLPI['smtp_mode'] ?? '',
                      ],
                      __('Max. delivery retries') => [
                          'name' => 'smtp_max_retries',
                          'type' => 'text',
                          'value' => $CFG_GLPI['smtp_max_retries'] ?? '',
                      ],
                      __('Try to deliver again in (minutes)') => [
                          'name' => 'smtp_retry_time',
                          'type' => 'number',
                          'value' => $CFG_GLPI['smtp_retry_time'] ?? '',
                          'min' => 0,
                          'max' => 60,
                          'step' => 1
                      ],
                      __('Check certificate') => [
                          'name' => 'smtp_check_certificate',
                          'type' => 'checkbox',
                          'value' => $CFG_GLPI['smtp_check_certificate'] ?? '1',
                      ],
                      __('SMTP host') => [
                          'name' => 'smtp_host',
                          'type' => 'text',
                          'value' => $CFG_GLPI['smtp_host'] ?? '',
                      ],
                      __('Port') => [
                          'name' => 'smtp_port',
                          'type' => 'number',
                          'min' => 0,
                          'max' => 65535,
                          'value' => $CFG_GLPI['smtp_port'] ?? '25',
                      ],
                      __('SMTP login (optional)') => [
                          'name' => 'smtp_username',
                          'type' => 'text',
                          'value' => $CFG_GLPI['smtp_username'] ?? '',
                      ],
                      __('SMTP password (optional)') => [
                          'name' => 'smtp_passwd',
                          'type' => 'password',
                          'autocomplete' => 'new-password'
                      ],
                      __('Email sender') => [
                          'name' => 'smtp_sender',
                          'type' => 'text',
                          'value' => $CFG_GLPI['smtp_sender'] ?? '',
                      ],


                  ]
              ]
          ]
    ];
        renderTwigForm($form);

    }

}
