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

namespace Glpi\System\Requirement;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * @since 9.5.0
 */
class DbClient extends AbstractRequirement
{
    public function __construct()
    {
        $this->title = __('Database client extension test');
    }

    protected function check()
    {
        $mysql_available = extension_loaded('pdo_mysql');
        $pgsql_available = extension_loaded('pdo_pgsql');

        $this->validated = $mysql_available || $pgsql_available;

        if ($mysql_available) {
            $this->validation_messages[] = __('pdo_mysql extension is installed');
            return;
        }

        if ($pgsql_available) {
            $this->validation_messages[] = __('pdo_pgsql extension is installed');
            return;
        }

        $this->validation_messages[] = __('Neither pdo_mysql nor pdo_pgsql extensions are available');
    }
}
