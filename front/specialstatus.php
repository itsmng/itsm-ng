<?php
/**
 * ---------------------------------------------------------------------
 * ITSM-NG 
 * Copyright (C) 2022 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org/
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

use Glpi\Dashboard\Provider;

include ('../inc/includes.php');

$dropdown = new SpecialStatus();

$dropdown->displayHeader();
$dropdown->title();
$dropdown->statusForm();
$dropdown->oldStatusOrder();
/*/
global $DB;
$oidc_result = [
   'name'   => "New",
   'weight'   => 1,
   'is_active'  => 1,
   'color'  => "Default"
];
$DB->updateOrInsert("glpi_specialstatuses", $oidc_result, ['id'   => 0]);
$oidc_result = [
   'name'   => "Processing (assigned)",
   'weight'   => 2,
   'is_active'  => 1,
   'color'  => "Default"
];
$DB->updateOrInsert("glpi_specialstatuses", $oidc_result, ['id'   => 0]);
$oidc_result = [
   'name'   => "Processing (planned)",
   'weight'   => 3,
   'is_active'  => 1,
   'color'  => "Default"
];
$DB->updateOrInsert("glpi_specialstatuses", $oidc_result, ['id'   => 0]);
$oidc_result = [
   'name'   => "Pending",
   'weight'   => 4,
   'is_active'  => 1,
   'color'  => "Default"
];
$DB->updateOrInsert("glpi_specialstatuses", $oidc_result, ['id'   => 0]);
$oidc_result = [
   'name'   => "Solved",
   'weight'   => 5,
   'is_active'  => 1,
   'color'  => "Default"
];
$DB->updateOrInsert("glpi_specialstatuses", $oidc_result, ['id'   => 0]);
$oidc_result = [
   'name'   => "Closed",
   'weight'   => 6,
   'is_active'  => 1,
   'color'  => "Default"
];
$DB->updateOrInsert("glpi_specialstatuses", $oidc_result, ['id'   => 0]);


/*/
/*/
UPDATE glpi_specialstatuses SET     weight = '5' WHERE    id = 2;
INSERT INTO glpi_profilerights ('profiles_id','name', 'rights') VALUES('2', 'specialstatus', '0');
$oidc_result = [
   'profiles_id'   => 2,
   'name'   => "specialstatus",
   'rights'  => 0
];
$DB->updateOrInsert("glpi_profilerights", $oidc_result, ['id'   => 0]);
/*/





Html::footer();
//include (GLPI_ROOT . "/front/dropdown.common.php");