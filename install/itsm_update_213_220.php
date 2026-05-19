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

    if (!$DB->fieldExists('glpi_entities', 'requesters_private_ticket_content')) {
        $migration->addField(
            'glpi_entities',
            'requesters_private_ticket_content',
            'integer',
            [
               'after'     => 'anonymize_support_agents',
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

    $migration->executeMigration();
    return true;
}
