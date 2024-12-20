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

namespace Glpi\CalDAV\Node;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Calendar node properties constants.
 *
 * @since 9.5.0
 */
class Property
{
    public const CAL_COLOR                = '{http://apple.com/ns/ical/}calendar-color';
    public const CAL_DESCRIPTION          = '{urn:ietf:params:xml:ns:caldav}calendar-description';
    public const CAL_SUPPORTED_COMPONENTS = '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set';
    public const CAL_USER_TYPE            = '{urn:ietf:params:xml:ns:caldav}calendar-user-type';
    public const DISPLAY_NAME             = '{DAV:}displayname';
    public const PRIMARY_EMAIL            = '{http://sabredav.org/ns}email-address';
    public const RESOURCE_TYPE            = '{DAV:}resourcetype';
    public const USERNAME                 = '{http://glpi-project.org/ns}username';
}
