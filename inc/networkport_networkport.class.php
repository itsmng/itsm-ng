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

/// NetworkPort_NetworkPort class
class NetworkPort_NetworkPort extends CommonDBRelation
{
    // From CommonDBRelation
    public static $itemtype_1           = 'NetworkPort';
    public static $items_id_1           = 'networkports_id_1';
    public static $itemtype_2           = 'NetworkPort';
    public static $items_id_2           = 'networkports_id_2';

    public static $log_history_1_add    = Log::HISTORY_CONNECT_DEVICE;
    public static $log_history_2_add    = Log::HISTORY_CONNECT_DEVICE;

    public static $log_history_1_delete = Log::HISTORY_DISCONNECT_DEVICE;
    public static $log_history_2_delete = Log::HISTORY_DISCONNECT_DEVICE;


    /**
     * Retrieve an item from the database
     *
     * @param integer $ID ID of the item to get
     *
     * @return boolean  true if succeed else false
    **/
    public function getFromDBForNetworkPort($ID)
    {

        return $this->getFromDBByCrit([
           'OR'  => [
              $this->getTable() . '.networkports_id_1'  => $ID,
              $this->getTable() . '.networkports_id_2'  => $ID
           ]
        ]);
    }


    /**
     * Get port opposite port ID
     *
     * @param integer $ID networking port ID
     *
     * @return integer|false  ID of opposite port. false if not found
    **/
    public function getOppositeContact($ID)
    {
        if ($this->getFromDBForNetworkPort($ID)) {
            if ($this->fields['networkports_id_1'] == $ID) {
                return $this->fields['networkports_id_2'];
            }
            if ($this->fields['networkports_id_2'] == $ID) {
                return $this->fields['networkports_id_1'];
            }
            return false;
        }
    }
}
