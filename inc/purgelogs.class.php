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

class PurgeLogs extends CommonDBTM
{
    protected static $notable = true;

    public static function getTypeName($nb = 0)
    {
        return __('Logs purge');
    }

    public static function cronPurgeLogs($task)
    {
        $cron_status = 0;

        $logs_before = self::getLogsCount();
        if ($logs_before) {
            self::purgeSoftware();
            self::purgeInfocom();
            self::purgeUserInfos();
            self::purgeDevices();
            self::purgeRelations();
            self::purgeItems();
            self::purgeOthers();
            self::purgePlugins();
            self::purgeAll();
            $logs_after = self::getLogsCount();
            Log::history(0, __CLASS__, [0, $logs_before, $logs_after], '', Log::HISTORY_LOG_SIMPLE_MESSAGE);
            $task->addVolume($logs_before - $logs_after);
            $cron_status = 1;
        } else {
            $task->addVolume(0);
        }
        return $cron_status;
    }

    public static function cronInfo($name)
    {
        return ['description' => __("Purge history")];
    }

    /**
     * Purge softwares logs
     *
     * @return void
     */
    public static function purgeSoftware()
    {
        global $CFG_GLPI;
        $adapter = self::getAdapter();

        $month = self::getDateModRestriction($CFG_GLPI['purge_item_software_install']);
        if ($month) {
            $logs = $adapter->request([
                'SELECT' => ['id'],
                'FROM'   => 'glpi_logs',
                'WHERE'  => [
                    'itemtype'      => $CFG_GLPI['software_types'],
                    'linked_action' => [
                        Log::HISTORY_INSTALL_SOFTWARE,
                        Log::HISTORY_UNINSTALL_SOFTWARE
                    ]
                ] + $month
            ]);
            foreach ($logs->fetchAllAssociative() as $data) {
                $log = new Log();
                if ($log->getFromDB($data['id'])) {
                    $log->deleteFromDB();
                }
            }
        }

        $month = self::getDateModRestriction($CFG_GLPI['purge_software_item_install']);
        if ($month) {
            $logs = $adapter->request([
                'SELECT' => ['id'],
                'FROM'   => 'glpi_logs',
                'WHERE'  => [
                    'itemtype'      => 'SoftwareVersion',
                    'linked_action' => [
                        Log::HISTORY_INSTALL_SOFTWARE,
                        Log::HISTORY_UNINSTALL_SOFTWARE
                    ]
                ] + $month
            ]);
            foreach ($logs->fetchAllAssociative() as $data) {
                $log = new Log();
                if ($log->getFromDB($data['id'])) {
                    $log->deleteFromDB();
                }
            }
        }

        $month = self::getDateModRestriction($CFG_GLPI['purge_software_version_install']);
        if ($month) {
            //Delete software version association
            $logs = $adapter->request([
                'SELECT' => ['id'],
                'FROM'   => 'glpi_logs',
                'WHERE'  => [
                    'itemtype'      => 'Software',
                    'itemtype_link' => 'SoftwareVersion',
                    'linked_action' => [
                        Log::HISTORY_ADD_SUBITEM,
                        Log::HISTORY_UPDATE_SUBITEM,
                        Log::HISTORY_DELETE_SUBITEM
                    ]
                ] + $month
            ]);
            foreach ($logs->fetchAllAssociative() as $data) {
                $log = new Log();
                if ($log->getFromDB($data['id'])) {
                    $log->deleteFromDB();
                }
            }
        }
    }

    /**
     * Purge infocom logs
     *
     * @return void
     */
    public static function purgeInfocom()
    {
        global $CFG_GLPI;
        $adapter = self::getAdapter();

        $month = self::getDateModRestriction($CFG_GLPI['purge_infocom_creation']);
        if ($month) {
            //Delete add infocom
            $logs = $adapter->request([
                'SELECT' => ['id'],
                'FROM'   => 'glpi_logs',
                'WHERE'  => [
                    'itemtype'      => 'Software',
                    'itemtype_link' => 'Infocom',
                    'linked_action' => Log::HISTORY_ADD_SUBITEM
                ] + $month
            ]);
            foreach ($logs->fetchAllAssociative() as $data) {
                $log = new Log();
                if ($log->getFromDB($data['id'])) {
                    $log->deleteFromDB();
                }
            }

            $logs = $adapter->request([
                'SELECT' => ['id'],
                'FROM'   => 'glpi_logs',
                'WHERE'  => [
                    'itemtype'     => 'Infocom',
                    'linked_action' => Log::HISTORY_CREATE_ITEM
                ] + $month
            ]);

            foreach ($logs->fetchAllAssociative() as $data) {
                $log = new Log();
                if ($log->getFromDB($data['id'])) {
                    $log->deleteFromDB();
                }
            }
        }
    }

    /**
     * Purge users logs
     *
     * @return void
     */
    public static function purgeUserinfos()
    {
        global $CFG_GLPI;
        $adapter = self::getAdapter();

        $month = self::getDateModRestriction($CFG_GLPI['purge_profile_user']);
        if ($month) {
            //Delete software version association
            $logs = $adapter->request([
                'SELECT' => ['id'],
                'FROM'   => 'glpi_logs',
                'WHERE'  => [
                    'itemtype'      => 'User',
                    'itemtype_link' => 'Profile_User',
                    'linked_action' => [
                        Log::HISTORY_ADD_SUBITEM,
                        Log::HISTORY_UPDATE_SUBITEM,
                        Log::HISTORY_DELETE_SUBITEM
                    ]
                ] + $month
            ]);

            foreach ($logs->fetchAllAssociative() as $data) {
                $log = new Log();
                if ($log->getFromDB($data['id'])) {
                    $log->deleteFromDB();
                }
            }
        }

        $month = self::getDateModRestriction($CFG_GLPI['purge_group_user']);
        if ($month) {
            //Delete software version association
            $logs = $adapter->request([
                'SELECT' => ['id'],
                'FROM'   => 'glpi_logs',
                'WHERE'  => [
                    'itemtype'      => 'User',
                    'itemtype_link' => 'Group_User',
                    'linked_action' => [
                        Log::HISTORY_ADD_SUBITEM,
                        Log::HISTORY_UPDATE_SUBITEM,
                        Log::HISTORY_DELETE_SUBITEM
                    ]
                ] + $month
            ]);

            foreach ($logs->fetchAllAssociative() as $data) {
                $log = new Log();
                if ($log->getFromDB($data['id'])) {
                    $log->deleteFromDB();
                }
            }
        }

        $month = self::getDateModRestriction($CFG_GLPI['purge_userdeletedfromldap']);
        if ($month) {
            //Delete software version association
            $logs = $adapter->request([
                'SELECT' => ['id'],
                'FROM'   => 'glpi_logs',
                'WHERE'  => [
                    'itemtype'     => 'User',
                    'linked_action' => Log::HISTORY_LOG_SIMPLE_MESSAGE
                ] + $month
            ]);

            foreach ($logs->fetchAllAssociative() as $data) {
                $log = new Log();
                if ($log->getFromDB($data['id'])) {
                    $log->deleteFromDB();
                }
            }
        }

        $month = self::getDateModRestriction($CFG_GLPI['purge_user_auth_changes']);
        if ($month) {
            //Delete software version association
            $logs = $adapter->request([
                'SELECT' => ['id'],
                'FROM'   => 'glpi_logs',
                'WHERE'  => [
                    'itemtype'     => 'User',
                    'linked_action' => Log::HISTORY_ADD_RELATION
                ] + $month
            ]);

            foreach ($logs->fetchAllAssociative() as $data) {
                $log = new Log();
                if ($log->getFromDB($data['id'])) {
                    $log->deleteFromDB();
                }
            }
        }
    }


    /**
     * Purge devices logs
     *
     * @return void
     */
    public static function purgeDevices()
    {
        global $CFG_GLPI;
        $adapter = self::getAdapter();

        $actions = [
           Log::HISTORY_ADD_DEVICE          => "adddevice",
           Log::HISTORY_UPDATE_DEVICE       => "updatedevice",
           Log::HISTORY_DELETE_DEVICE       => "deletedevice",
           Log::HISTORY_CONNECT_DEVICE      => "connectdevice",
           Log::HISTORY_DISCONNECT_DEVICE   => "disconnectdevice"
        ];
        foreach ($actions as $key => $value) {
            $month = self::getDateModRestriction($CFG_GLPI['purge_' . $value]);
            if ($month) {
                //Delete software version association
                $logs = $adapter->request([
                    'SELECT' => ['id'],
                    'FROM'   => 'glpi_logs',
                    'WHERE'  => [
                        'linked_action' => $key
                    ] + $month
                ]);

                foreach ($logs->fetchAllAssociative() as $data) {
                    $log = new Log();
                    if ($log->getFromDB($data['id'])) {
                        $log->deleteFromDB();
                    }
                }
            }
        }
    }

    /**
     * Purge relations logs
     *
     * @return void
     */
    public static function purgeRelations()
    {
        global $CFG_GLPI;
        $adapter = self::getAdapter();

        $actions = [
           Log::HISTORY_ADD_RELATION     => "addrelation",
           Log::HISTORY_UPDATE_RELATION  => "addrelation",
           Log::HISTORY_DEL_RELATION     => "deleterelation"
        ];
        foreach ($actions as $key => $value) {
            $month = self::getDateModRestriction($CFG_GLPI['purge_' . $value]);
            if ($month) {
                //Delete software version association
                $logs = $adapter->request([
                    'SELECT' => ['id'],
                    'FROM'   => 'glpi_logs',
                    'WHERE'  => [
                        'linked_action' => $key
                    ] + $month
                ]);

                // Supprimer chaque log individuellement
                foreach ($logs->fetchAllAssociative() as $data) {
                    $log = new Log();
                    if ($log->getFromDB($data['id'])) {
                        $log->deleteFromDB();
                    }
                }
            }
        }
    }

    /**
     * Purge items logs
     *
     * @return void
     */
    public static function purgeItems()
    {
        global $DB, $CFG_GLPI;
        $adapter = self::getAdapter();

        $actions = [
           Log::HISTORY_CREATE_ITEM      => "createitem",
           Log::HISTORY_ADD_SUBITEM      => "createitem",
           Log::HISTORY_DELETE_ITEM      => "deleteitem",
           Log::HISTORY_DELETE_SUBITEM   => "deleteitem",
           Log::HISTORY_UPDATE_SUBITEM   => "updateitem",
           Log::HISTORY_RESTORE_ITEM     => "restoreitem"
        ];
        foreach ($actions as $key => $value) {
            $month = self::getDateModRestriction($CFG_GLPI['purge_' . $value]);
            if ($month) {
                //Delete software version association
                $logs = $adapter->request([
                    'SELECT' => ['id'],
                    'FROM'   => 'glpi_logs',
                    'WHERE'  => [
                        'linked_action' => $key
                    ] + $month
                ]);

                foreach ($logs->fetchAllAssociative() as $data) {
                    $log = new Log();
                    if ($log->getFromDB($data['id'])) {
                        $log->deleteFromDB();
                    }
                }
            }
        }
    }

    /**
     * Purge othr logs
     *
     * @return void
     */
    public static function purgeOthers()
    {
        global $CFG_GLPI;
        $adapter = self::getAdapter();

        $actions = [
           16 => 'comments',
           19 => 'datemod'
        ];
        foreach ($actions as $key => $value) {
            $month = self::getDateModRestriction($CFG_GLPI['purge_' . $value]);
            if ($month) {
                $logs = $adapter->request([
                    'SELECT' => ['id'],
                    'FROM'   => 'glpi_logs',
                    'WHERE'  => [
                        'id_search_option' => $key
                    ] + $month
                ]);

                foreach ($logs->fetchAllAssociative() as $data) {
                    $log = new Log();
                    if ($log->getFromDB($data['id'])) {
                        $log->deleteFromDB();
                    }
                }
            }
        }
    }


    /**
     * Purge plugins logs
     *
     * @return void
     */
    public static function purgePlugins()
    {
        global $CFG_GLPI;
        $adapter = self::getAdapter();

        $month = self::getDateModRestriction($CFG_GLPI['purge_plugins']);
        if ($month) {
            $logs = $adapter->request([
                'SELECT' => ['id'],
                'FROM'   => 'glpi_logs',
                'WHERE'  => [
                    'itemtype' => ['LIKE', 'Plugin%']
                ] + $month
            ]);

            foreach ($logs->fetchAllAssociative() as $data) {
                $log = new Log();
                if ($log->getFromDB($data['id'])) {
                    $log->deleteFromDB();
                }
            }
        }
    }


    /**
     * Purge all logs
     *
     * @return void
     */
    public static function purgeAll()
    {
        global $CFG_GLPI;
        $adapter = self::getAdapter();

        $month = self::getDateModRestriction($CFG_GLPI['purge_all']);
        if ($month) {
            $logs = $adapter->request([
                'SELECT' => ['id'],
                'FROM'   => 'glpi_logs',
                'WHERE'  => [
                    'itemtype' => ['LIKE', 'Plugin%']
                ] + $month
            ]);

            foreach ($logs->fetchAllAssociative() as $data) {
                $log = new Log();
                if ($log->getFromDB($data['id'])) {
                    $log->deleteFromDB();
                }
            }
        }
    }

    /**
     * Get modification date restriction clause
     *
     * @param integer $month Number of months
     *
     * @return array|false
     */
    public static function getDateModRestriction($month)
    {
        if ($month > 0) {
            return ['date_mod' => ['<=', new QueryExpression("DATE_ADD(NOW(), INTERVAL -$month MONTH)")]];
        } elseif ($month == Config::DELETE_ALL) {
            return [1 => 1];
        } elseif ($month == Config::KEEP_ALL) {
            return false;
        }
    }

    /**
     * Count logs
     *
     * @return integer
     */
    public static function getLogsCount()
    {
        return countElementsInTable('glpi_logs');
    }
}
