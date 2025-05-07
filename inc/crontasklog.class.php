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
 * CronTaskLog class
**/
class CronTaskLog extends CommonDBTM
{
    // Class constant
    public const STATE_START = 0;
    public const STATE_RUN   = 1;
    public const STATE_STOP  = 2;
    public const STATE_ERROR = 3;


    /**
     * Clean old event for a task
     *
     * @param $id     integer  ID of the CronTask
     * @param $days   integer  number of day to keep
     *
     * @return integer number of events deleted
    **/
    public static function cleanOld($id, $days)
    {
        $adapter = self::getAdapter();
        $timestamp = time() - ($days * DAY_TIMESTAMP);

        $sql = "
            SELECT id FROM glpi_crontasklogs
            WHERE crontasks_id = :id
            AND UNIX_TIMESTAMP(date) < :threshold
        ";

        $result = $adapter->query($sql, [
            'id' => $id,
            'threshold' => $timestamp
        ]);

        $rows = $result->fetchAllAssociative();
        $count = 0;

        foreach ($rows as $row) {
            $adapter->deleteByCriteria(['id' => $row['id']]);
            $count++;
        }

        return $count;
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            $nb = 0;
            switch ($item->getType()) {
                case 'CronTask':
                    $ong    = [];
                    $ong[1] = __('Statistics');
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb =  countElementsInTable(
                            $this->getTable(),
                            ['crontasks_id' => $item->getID(),
                                                     'state'        => self::STATE_STOP ]
                        );
                    }
                    $ong[2] = self::createTabEntry(_n('Log', 'Logs', Session::getPluralNumber()), $nb);
                    return $ong;
            }
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == 'CronTask') {
            switch ($tabnum) {
                case 1:
                    $item->showStatistics();
                    break;

                case 2:
                    $item->showHistory();
                    break;
            }
        }
        return true;
    }
}
