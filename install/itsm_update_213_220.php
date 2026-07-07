<?php

/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2026 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ITSM-NG.
 *
 * ITSM-NG is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ITSM-NG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ITSM-NG. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

/**
 * Update ITSM-NG from 2.1.3 to 2.2.0
 *
 * @return bool for success (will die for most error)
 **/
function update213to220(): bool
{
    /** @global Migration $migration */
    global $DB, $migration;

    $migration->displayTitle(sprintf(__('Update to %s'), '2.2.0'));
    $migration->setVersion('2.2.0');

    if (!$DB->fieldExists('glpi_entities', 'lock_ticket_date')) {
        $migration->addField(
            'glpi_entities',
            'lock_ticket_date',
            "tinyint(1) NOT NULL DEFAULT '-2'",
            [
               'after'     => 'anonymize_support_agents',
               'value'     => -2,  // Inherit as default value
               'update'    => 0,   // Not enabled for root entity
               'condition' => 'WHERE `id` = 0',
            ]
        );
    }

    if (!$DB->fieldExists('glpi_entities', 'requesters_private_ticket_content')) {
        $migration->addField(
            'glpi_entities',
            'requesters_private_ticket_content',
            'integer',
            [
               'after'     => 'lock_ticket_date',
               'value'     => -2,  // Inherit as default value
               'update'    => 0,   // Not enabled for root entity
               'condition' => 'WHERE `id` = 0',
            ]
        );
    }

    $migration->displayMessage("Add group restrictions for task and solution templates");

    $DB->queryOrDie(
        "CREATE TABLE IF NOT EXISTS `glpi_groups_tasktemplates` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tasktemplates_id` int(11) NOT NULL DEFAULT '0',
            `groups_id` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `unicity` (`tasktemplates_id`,`groups_id`),
            KEY `groups_id` (`groups_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
        $DB->error()
    );

    $DB->queryOrDie(
        "CREATE TABLE IF NOT EXISTS `glpi_groups_solutiontemplates` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `solutiontemplates_id` int(11) NOT NULL DEFAULT '0',
            `groups_id` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `unicity` (`solutiontemplates_id`,`groups_id`),
            KEY `groups_id` (`groups_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
        $DB->error()
    );

    $task_tables = [
        'glpi_tickettasks'  => 'tickets_id',
        'glpi_problemtasks' => 'problems_id',
        'glpi_changetasks'  => 'changes_id',
    ];

    foreach ($task_tables as $table => $after) {
        $migration->addField($table, 'title', 'string', ['after' => $after]);
    }
    $migration->addField('glpi_tasktemplates', 'title', 'string', ['after' => 'name']);

    $migration->addField('glpi_queuednotifications', 'generated_attachments', 'text');

    if (!$DB->tableExists('glpi_appointmenttargets')) {
        $query = "CREATE TABLE `glpi_appointmenttargets` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `itemtype` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
          `items_id` int(11) NOT NULL DEFAULT '0',
          `entities_id` int(11) NOT NULL DEFAULT '0',
          `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
          `comment` text COLLATE utf8_unicode_ci,
          `is_active` tinyint(1) NOT NULL DEFAULT '1',
          `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`),
          UNIQUE KEY `item` (`itemtype`,`items_id`),
          KEY `entities_id` (`entities_id`),
          KEY `is_recursive` (`is_recursive`),
          KEY `is_active` (`is_active`),
          KEY `is_deleted` (`is_deleted`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $DB->queryOrDie($query, '2.2.0 add appointment targets');
    }
    $DB->queryOrDie(
        "UPDATE `glpi_appointmenttargets`
         SET `entities_id` = 0
         WHERE `entities_id` < 0",
        '2.2.0 normalize appointment target entities'
    );

    if (!$DB->tableExists('glpi_appointmentavailabilities')) {
        $query = "CREATE TABLE `glpi_appointmentavailabilities` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `appointmenttargets_id` int(11) NOT NULL DEFAULT '0',
          `day` tinyint(1) NOT NULL DEFAULT '1',
          `begin` time DEFAULT NULL,
          `end` time DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `appointmenttargets_id` (`appointmenttargets_id`),
          KEY `day` (`day`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $DB->queryOrDie($query, '2.2.0 add appointment availabilities');
    }

    if (!$DB->tableExists('glpi_appointmentunavailabilities')) {
        $query = "CREATE TABLE `glpi_appointmentunavailabilities` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `appointmenttargets_id` int(11) NOT NULL DEFAULT '0',
          `begin` datetime DEFAULT NULL,
          `end` datetime DEFAULT NULL,
          `is_available` tinyint(1) NOT NULL DEFAULT '0',
          `comment` text COLLATE utf8_unicode_ci,
          PRIMARY KEY (`id`),
          KEY `appointmenttargets_id` (`appointmenttargets_id`),
          KEY `begin` (`begin`),
          KEY `end` (`end`),
          KEY `is_available` (`is_available`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $DB->queryOrDie($query, '2.2.0 add appointment unavailabilities');
    }

    if (!$DB->tableExists('glpi_appointments')) {
        $query = "CREATE TABLE `glpi_appointments` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `uuid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `appointmenttargets_id` int(11) NOT NULL DEFAULT '0',
          `users_id` int(11) NOT NULL DEFAULT '0',
          `entities_id` int(11) NOT NULL DEFAULT '0',
          `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
          `date` timestamp NULL DEFAULT NULL,
          `users_id_requester` int(11) NOT NULL DEFAULT '0',
          `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `text` text COLLATE utf8_unicode_ci,
          `begin` datetime DEFAULT NULL,
          `end` datetime DEFAULT NULL,
          `state` int(11) NOT NULL DEFAULT '0',
          `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
          `date_mod` timestamp NULL DEFAULT NULL,
          `date_creation` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uuid` (`uuid`),
          KEY `appointmenttargets_id` (`appointmenttargets_id`),
          KEY `users_id` (`users_id`),
          KEY `entities_id` (`entities_id`),
          KEY `is_recursive` (`is_recursive`),
          KEY `users_id_requester` (`users_id_requester`),
          KEY `begin` (`begin`),
          KEY `end` (`end`),
          KEY `is_deleted` (`is_deleted`),
          KEY `date_mod` (`date_mod`),
          KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $DB->queryOrDie($query, '2.2.0 add appointments');
    }

    if (countElementsInTable('glpi_notificationtemplates', ['itemtype' => 'Appointment']) == 0) {
        $DB->insert(
            'glpi_notificationtemplates',
            [
              'name'     => 'Appointments',
              'itemtype' => 'Appointment',
            ]
        );
        $template_id = $DB->insertId();
        $DB->insert(
            'glpi_notificationtemplatetranslations',
            [
              'notificationtemplates_id' => $template_id,
              'language'                 => '',
              'subject'                  => '##appointment.action##',
              'content_text'             => "##lang.appointment.title##: ##appointment.title##\n"
                 . "##lang.appointment.requester##: ##appointment.requester##\n"
                 . "##lang.appointment.target##: ##appointment.target##\n"
                 . "##lang.appointment.begin##: ##appointment.begin##\n"
                 . "##lang.appointment.end##: ##appointment.end##\n"
                 . "##lang.appointment.comment##: ##appointment.comment##\n"
                 . "##appointment.url##",
              'content_html'             => '&lt;p&gt;&lt;strong&gt;##appointment.title##&lt;/strong&gt;&lt;/p&gt;'
                 . '&lt;p&gt;##lang.appointment.requester##: ##appointment.requester##&lt;br /&gt;'
                 . '##lang.appointment.target##: ##appointment.target##&lt;br /&gt;'
                 . '##lang.appointment.begin##: ##appointment.begin##&lt;br /&gt;'
                 . '##lang.appointment.end##: ##appointment.end##&lt;br /&gt;'
                 . '##lang.appointment.comment##: ##appointment.comment##&lt;/p&gt;'
                 . '&lt;p&gt;&lt;a href="##appointment.url##"&gt;##appointment.url##&lt;/a&gt;&lt;/p&gt;',
            ]
        );
    } else {
        $template = new NotificationTemplate();
        $template->getFromDBByCrit(['itemtype' => 'Appointment']);
        $template_id = $template->fields['id'];
    }

    foreach (['new' => 'New Appointment', 'update' => 'Update Appointment', 'delete' => 'Delete Appointment'] as $event => $name) {
        if (countElementsInTable('glpi_notifications', ['itemtype' => 'Appointment', 'event' => $event]) > 0) {
            continue;
        }
        $DB->insert(
            'glpi_notifications',
            [
              'name'         => $name,
              'itemtype'     => 'Appointment',
              'event'        => $event,
              'is_recursive' => 1,
              'is_active'    => 1,
            ]
        );
        $notification_id = $DB->insertId();
        $DB->insert(
            'glpi_notifications_notificationtemplates',
            [
              'notifications_id'         => $notification_id,
              'mode'                     => Notification_NotificationTemplate::MODE_MAIL,
              'notificationtemplates_id' => $template_id,
            ]
        );
        foreach ([Notification::AUTHOR, NotificationTargetAppointment::APPOINTMENT_RECEIVER] as $target) {
            $DB->insert(
                'glpi_notificationtargets',
                [
                  'items_id'         => $target,
                  'type'             => Notification::USER_TYPE,
                  'notifications_id' => $notification_id,
                ]
            );
        }
    }

    $migration->addRight('appointment', CREATE, [
       'planning' => Planning::READMY
    ]);

    $DB->updateOrDie(
        'glpi_profilerights AS appointment_right',
        [
          'appointment_right.rights' => new QueryExpression(
              $DB->quoteName('appointment_right.rights') . ' | ' . UPDATE
          ),
        ],
        [
          'appointment_right.name' => 'appointment',
          'config_right.name'     => 'config',
          new QueryExpression(
              '(' . $DB->quoteName('config_right.rights') . ' & ' . (READ | UPDATE) . ') = ' . (READ | UPDATE)
          ),
        ],
        '2.2.0 add appointment management rights',
        [
          'INNER JOIN' => [
             'glpi_profilerights AS config_right' => [
                'FKEY' => [
                   'appointment_right' => 'profiles_id',
                   'config_right'     => 'profiles_id',
                ],
             ],
          ],
        ]
    );

    $migration->executeMigration();
    return true;
}
