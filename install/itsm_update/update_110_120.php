<?php
/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2022 ITSM-NG and contributors.
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
 * Update ITSM-NG from 1.1.0 to 1.2.0
 *
 * @return bool for success (will die for most error)
 **/
function update110to120() {
   /** @global Migration $migration */
   global $DB, $migration;

   $current_config   = Config::getConfigurationValues('core');
   $updateresult     = true;
   $ADDTODISPLAYPREF = [];

   $migration->displayTitle(sprintf(__('Update to %s'), '1.2.0'));


   /** Create new table for Open ID connect's config */
   if (!$DB->tableExists("glpi_specialstatuses")) {
    $config = "CREATE TABLE `glpi_specialstatuses` (
        `id` int(11) NOT NULL auto_increment,
        `name` varchar(255) DEFAULT NULL,
        `weight`   int(11) NOT NULL DEFAULT 0,
        `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
        `color` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $DB->queryOrDie($config, "erreur lors de la création de la table de configuration ".$DB->error());
    }

    $status = [
       'name'   => "New",
       'weight'   => 1,
       'is_active'  => 1,
       'color'  => "Default"
    ];
    $DB->updateOrInsert("glpi_specialstatuses", $status, ['id'   => 0]);
    $status = [
       'name'   => "Processing (assigned)",
       'weight'   => 2,
       'is_active'  => 1,
       'color'  => "Default"
    ];
    $DB->updateOrInsert("glpi_specialstatuses", $status, ['id'   => 0]);
    $status = [
       'name'   => "Processing (planned)",
       'weight'   => 3,
       'is_active'  => 1,
       'color'  => "Default"
    ];
    $DB->updateOrInsert("glpi_specialstatuses", $status, ['id'   => 0]);
    $status = [
       'name'   => "Pending",
       'weight'   => 4,
       'is_active'  => 1,
       'color'  => "Default"
    ];
    $DB->updateOrInsert("glpi_specialstatuses", $status, ['id'   => 0]);
    $status = [
       'name'   => "Solved",
       'weight'   => 5,
       'is_active'  => 1,
       'color'  => "Default"
    ];
    $DB->updateOrInsert("glpi_specialstatuses", $status, ['id'   => 0]);
    $status = [
       'name'   => "Closed",
       'weight'   => 6,
       'is_active'  => 1,
       'color'  => "Default"
    ];
    $DB->updateOrInsert("glpi_specialstatuses", $status, ['id'   => 0]);

    $status = [
      'profiles_id' => '4',
      'name'        => 'status_ticket',
      'rights'      => 23,
    ];
    $DB->updateOrInsert("glpi_profilerights", $status, ['id'   => 0]);
    /*/
    /*/

    /** /Create new table for Open ID connect's config */

   // ************ Keep it at the end **************
   $migration->executeMigration();
   return $updateresult;
}
