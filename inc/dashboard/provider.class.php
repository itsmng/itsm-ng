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

namespace Glpi\Dashboard;

use CommonGLPI;
use DBConnection;
use QueryExpression;
use Change;
use CommonITILActor;
use CommonITILValidation;
use CommonTreeDropdown;
use CommonDBTM;
use CommonITILObject;
use Group;
use Group_Ticket;
use Problem;
use QuerySubQuery;
use Session;
use Search;
use Stat;
use Ticket;
use Ticket_User;
use Toolbox;
use User;

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 * Provider class
**/
class Provider {
   static function number(CommonDBTM $item, $conditions = []) {
      global $DB;
  
      $table = $item->getTable();
  
      // Build the SQL query
      $query = <<<SQL
         SELECT COUNT(*) as count
         FROM `$table`
      SQL;
      if (count($conditions)) {
         $query .= ' WHERE `entities_id` = '.Session::getActiveEntity().' AND ';
         foreach ($conditions as $field => $value) {
            $query .= "`$field` = '$value' AND ";
         }
         $query = substr($query, 0, -5);
      }
  
      // Execute the query
      $iter = $DB->query($query);
  
      die(var_dump(iterator_to_array($iter)));
  }
}
