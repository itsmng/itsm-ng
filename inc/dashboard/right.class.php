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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class Right extends \CommonDBChild
{
    public static $itemtype = "Glpi\\Dashboard\\Dashboard";
    public static $items_id = 'dashboards_dashboards_id';

    // prevent bad getFromDB when bootstraping tests suite
    public static $mustBeAttached = false;

    /**
     * Return rights for the provided dashboard
     *
     * @param int $dashboards_id
     *
     * @return array the rights
     */
    public static function getForDashboard(int $dashboards_id = 0): array
    {
        global $DB;

        $request = self::getAdapter()->request([
           'FROM'  => self::getTable(),
           'WHERE' => [
              'dashboards_dashboards_id' => $dashboards_id
           ]
        ]);
        $dr_request = $request->fetchAllAssociative();
        $rights = [];
        foreach ($dr_request as $right) {
            unset($right['id']);
            $rights[] = $right;
        }

        return $rights;
    }


    /**
     * Save rights in DB for the provided dashboard
     *
     * @param int $dashboards_id id (not key) of the dashboard
     * @param array $rights contains these data:
     * - 'users_id'    => [items_id]
     * - 'groups_id'   => [items_id]
     * - 'entities_id' => [items_id]
     * - 'profiles_id' => [items_id]
     *
     * @return void
     */
    public static function addForDashboard(int $dashboards_id = 0, array $rights = [])
    {
        global $DB;

        $query_rights = $DB->buildInsert(
            self::getTable(),
            [
              'dashboards_dashboards_id' => new \QueryParam(),
              'itemtype' => new \QueryParam(),
              'items_id' => new \QueryParam(),
            ]
        );
        $stmt = $DB->prepare($query_rights);
        foreach ($rights as $fk => $right_line) {
            $itemtype = getItemtypeForForeignKeyField($fk);
            foreach ($right_line as $items_id) {
                $stmt->bind_param(
                    'isi',
                    $dashboards_id,
                    $itemtype,
                    $items_id
                );
                $stmt->execute();
            }
        }
    }
}
