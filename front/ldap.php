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

include('../inc/includes.php');

Session::checkRight("user", User::IMPORTEXTAUTHUSERS);

Html::header(__('LDAP directory link'), $_SERVER['PHP_SELF'], "admin", "user", "ldap");

if (isset($_SESSION["ldap_sortorder"])) {
    unset($_SESSION["ldap_sortorder"]);
}

AuthLDAP::manageValuesInSession([], true);
echo "<nav class='list-group' aria-label='" . __s('Bulk import users from a LDAP directory') . "'>";
echo "<a class='list-group-item list-group-item-action' href='ldap.import.php?mode=1&amp;action=show'>"
    . __('Synchronizing already imported users') . '</a>';
echo "<a class='list-group-item list-group-item-action' href='ldap.import.php?mode=0&amp;action=show'>"
    . __('Import new users') . '</a>';
echo '</nav>';

Html::footer();
