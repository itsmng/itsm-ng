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

/**
 * Class Supplier_Ticket
 *
 * @since 0.84
**/
class Supplier_Ticket extends CommonITILActor
{
    // From CommonDBRelation
    public static $itemtype_1 = 'Ticket';
    public static $items_id_1 = 'tickets_id';
    public static $itemtype_2 = 'Supplier';
    public static $items_id_2 = 'suppliers_id';


    /**
     * @param $items_id
     * @param $email
     *
     * @since 0.85
    **/
    public function isSupplierEmail($items_id, $email)
    {
        $request = $this::getAdapter()->request([
           'FROM'      => $this->getTable(),
           'LEFT JOIN' => [
              'glpi_suppliers'  => [
                 'ON' => [
                    $this->getTable() => 'suppliers_id',
                    'glpi_suppliers'  => 'id'
                 ]
              ]
           ],
           'WHERE'     => [
              $this->getTable() . '.tickets_id'   => $items_id,
              'glpi_suppliers.email'              => $email
           ]
        ]);

        while ($data = $request->fetchAssociative()) {
            return true;
        }
        return false;
    }

    public function post_addItem()
    {

        switch ($this->input['type']) { // Values from CommonITILObject::getSearchOptionsActors()
            case CommonITILActor::ASSIGN:
                $this->_force_log_option = 6;
                break;
        }
        parent::post_addItem();
        unset($this->_force_log_option);
    }
}
