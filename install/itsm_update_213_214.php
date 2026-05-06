<?php

/**
 * Update ITSM-NG from 2.1.3 to 2.1.4
 *
 * @return bool
 */
function update213to214(): bool
{
    /** @global Migration $migration */
    global $DB, $migration;

    $migration->displayTitle(sprintf(__('Update to %s'), '2.1.4'));
    $migration->setVersion('2.1.4');
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
        $DB->queryOrDie($query, '2.1.4 add appointment targets');
    }
    $DB->queryOrDie(
        "UPDATE `glpi_appointmenttargets`
         SET `entities_id` = 0
         WHERE `entities_id` < 0",
        '2.1.4 normalize appointment target entities'
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
        $DB->queryOrDie($query, '2.1.4 add appointment availabilities');
    }

    if (!$DB->tableExists('glpi_appointmentavailabilityexceptions')) {
        $query = "CREATE TABLE `glpi_appointmentavailabilityexceptions` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `appointmenttargets_id` int(11) NOT NULL DEFAULT '0',
          `begin` timestamp NULL DEFAULT NULL,
          `end` timestamp NULL DEFAULT NULL,
          `is_available` tinyint(1) NOT NULL DEFAULT '0',
          `comment` text COLLATE utf8_unicode_ci,
          PRIMARY KEY (`id`),
          KEY `appointmenttargets_id` (`appointmenttargets_id`),
          KEY `begin` (`begin`),
          KEY `end` (`end`),
          KEY `is_available` (`is_available`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $DB->queryOrDie($query, '2.1.4 add appointment unavailabilities');
    }

    if (!$DB->tableExists('glpi_appointments')) {
        $query = "CREATE TABLE `glpi_appointments` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `uuid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `appointmenttargets_id` int(11) NOT NULL DEFAULT '0',
          `entities_id` int(11) NOT NULL DEFAULT '0',
          `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
          `date` timestamp NULL DEFAULT NULL,
          `users_id_requester` int(11) NOT NULL DEFAULT '0',
          `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `text` text COLLATE utf8_unicode_ci,
          `begin` timestamp NULL DEFAULT NULL,
          `end` timestamp NULL DEFAULT NULL,
          `state` int(11) NOT NULL DEFAULT '0',
          `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
          `date_mod` timestamp NULL DEFAULT NULL,
          `date_creation` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uuid` (`uuid`),
          KEY `appointmenttargets_id` (`appointmenttargets_id`),
          KEY `entities_id` (`entities_id`),
          KEY `is_recursive` (`is_recursive`),
          KEY `users_id_requester` (`users_id_requester`),
          KEY `begin` (`begin`),
          KEY `end` (`end`),
          KEY `is_deleted` (`is_deleted`),
          KEY `date_mod` (`date_mod`),
          KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $DB->queryOrDie($query, '2.1.4 add appointments');
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

    $migration->addRight('appointment', ALLSTANDARDRIGHT, [
       'planning' => Planning::READMY
    ]);

    $migration->executeMigration();
    return true;
}
