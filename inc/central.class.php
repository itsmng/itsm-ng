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

use Glpi\Event;
use PharIo\Manifest\License;
use Sabre\HTTP\HttpException;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * Central class
**/
class Central extends CommonGLPI
{
    public static function getTypeName($nb = 0)
    {

        // No plural
        return __('Standard interface');
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addStandardTab(__CLASS__, $ong, $options);

        return $ong;
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if ($item->getType() == __CLASS__) {
            $tabs = Session::haveRight('dashboard', READ) ? [ 0 => __('Dashboard') ] : [];
            $tabs += [
               1 => __('Personal View'),
               2 => __('Group View'),
               3 => __('Global View'),
               4 => _n('RSS feed', 'RSS feeds', Session::getPluralNumber()),
            ];
            return $tabs;
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->getType() == __CLASS__) {
            switch ($tabnum) {
                case 0:
                    $item->showGlobalDashboard();
                    break;

                case 1:
                    $item->showMyView();
                    break;

                case 2:
                    $item->showGroupView();
                    break;

                case 3:
                    $item->showGlobalView();
                    break;

                case 4:
                    $item->showRSSView();
                    break;
            }
        }
        return true;
    }

    public function showGlobalDashboard()
    {
        $dashboard = new Dashboard();
        $grid = [
           __('General') => [
               'right' => true,
               'items' => [
                   Entity::class,
                   User::class,
                   Budget::class,
               ]
           ],
           __('Assets') => [
               'right' => true,
               'items' => [
                   Computer::class,
                   Monitor::class,
                   NetworkEquipment::class,
                   Peripheral::class,
                   Phone::class,
                   Printer::class,
                   Software::class,
               ]
           ],
           __('Tickets') => [
               'right' => true,
               'items' => [
                   Ticket::class,
                   Problem::class,
                   Change::class,
               ]
           ],
        ];
        foreach ($grid as $title => $section) {
            if (!$section['right']) {
                continue;
            }
            echo <<<HTML
             <div class="card my-3">
                 <div class="card-header">
                     <h5 class="card-title">$title</h5>
                 </div>
                 <div class="card-body">
         HTML;
            $dashboard = ['widgetGrid' => [[]]];
            foreach ($section['items'] as $item) {
                $dashboard['widgetGrid'][0][] = [
                      'title' => $item::getTypeName(2),
                      'value' => countElementsInTableForMyEntities($item::getTable()),
                      'icon' => $item::getIcon(),
                ];
            }
            renderTwigTemplate('dashboard/dashboard.twig', $dashboard);
            echo "</div></div>";
        }

        Html::accessibilityHeader();
    }


    /**
     * Show the central global view
    **/
    public static function showGlobalView()
    {

        $showticket  = Session::haveRight("ticket", Ticket::READALL);
        $showproblem = Session::haveRight("problem", Problem::READALL);

        echo "<table class='tab_cadre_central'><tr class='noHover' aria-label='Global View Outer Table'>";

        echo "<td class='top' width='33%'>";
        echo "<table class='central' aria-label='Table Description'>";
        echo "<tr class='noHover'><td>";
        if ($showticket) {
            Ticket::showCentralCount();
        } else {
            Ticket::showCentralCount(true);
        }
        echo "</td></tr>";
        echo "</table></td>";

        echo "<td class='top' width='33%'>";
        echo "<table class='central' aria-label='Problem followup'>";
        echo "<tr class='noHover'><td>";
        if ($showproblem) {
            Problem::showCentralCount();
        }
        echo "</td></tr>";
        echo "</table></td>";

        echo "<td class='top' width='33%'>";
        echo "<table class='central' aria-label='Contracts Table'>";
        echo "<tr class='noHover'><td>";
        if (Contract::canView()) {
            Contract::showCentral();
        }
        echo "</td></tr>";
        echo "</table></td>";

        echo "</tr></table>";

        if (Session::haveRight("logs", READ)) {
            echo "<td class='top'  width='50%'>";

            //Show last add events
            Event::showForUser($_SESSION["glpiname"]);
            echo "</td>";
        }
        echo "</tr></table>";

        if ($_SESSION["glpishow_jobs_at_login"] && $showticket) {
            echo "<br>";
            Ticket::showCentralNewList();
        }
    }



    /**
     * Show the central personal view
    **/
    public static function showMyView()
    {
        global $CFG_GLPI;
        $objects = $CFG_GLPI['globalsearch_types'];
        asort($objects);
        $values = [];
        foreach ($objects as $object) {
            $values[$object] = ((string) $object)::getTypeName();
        }
        if (Session::haveRight('dashboard', Ticket::READALL)) {
            $dashboard = new Dashboard();
            $dashboard->getForUser();
            $dashboard->show(null, true);
        }
        $showticket  = Session::haveRightsOr(
            "ticket",
            [Ticket::READMY, Ticket::READALL, Ticket::READASSIGN]
        );

        $showproblem = Session::haveRightsOr('problem', [Problem::READALL, Problem::READMY]);


        echo "<div id='main-container' style='width: 100%;'>";

        echo "<div id='table1' style='width: 100%; margin-bottom: 20px;'>";
        echo "<table class='central' style='width: 100%;' aria-label='My View Inner Table'>";
        echo "<tr class='noHover'><td>";
        if (Session::haveRightsOr('ticketvalidation', TicketValidation::getValidateRights())) {
            Ticket::showCentralList(0, "tovalidate", false);
        }
        if ($showticket) {
            if (Ticket::isAllowedStatus(Ticket::SOLVED, Ticket::CLOSED)) {
                Ticket::showCentralList(0, "toapprove", false);
            }

            Ticket::showCentralList(0, "survey", false);

            Ticket::showCentralList(0, "validation.rejected", false);
            Ticket::showCentralList(0, "solution.rejected", false);
            Ticket::showCentralList(0, "requestbyself", false);
            Ticket::showCentralList(0, "observed", false);

            Ticket::showCentralList(0, "process", false);
            Ticket::showCentralList(0, "waiting", false);

            TicketTask::showCentralList(0, "todo", false);
        }
        if ($showproblem) {
            Problem::showCentralList(0, "process", false);
            ProblemTask::showCentralList(0, "todo", false);
        }
        echo "</td></tr>";
        echo "</table>";
        echo "</div>";

        echo "<div id='table2' style='width: 100%;'>";
        echo "<table class='central' style='width: 100%;' aria-label='Central Table'>";
        echo "<tr class='noHover'><td>";
        Planning::showCentral(Session::getLoginUserID());
        Reminder::showListForCentral();
        if (Session::haveRight("reminder_public", READ)) {
            Reminder::showListForCentral(false);
        }
        echo "</td></tr>";
        echo "</table>";
        echo "</div>";

        echo "<div style='text-align: center; margin-top: 20px;'>";
        echo "<button onclick='swapTables()' style='padding: 10px 20px; font-size: 16px;'>
        <i class='fas fa-exchange-alt' title='Inverser les tableaux' style='transform: rotate(90deg);'></i>
      </button>";

        echo "</div>";

        echo "</div>";

        echo "<script type='text/javascript'>
           function swapTables() {
               var container = document.getElementById(\"main-container\");
               var table1 = document.getElementById(\"table1\");
               var table2 = document.getElementById(\"table2\");

               // Vérifie l'ordre actuel des tableaux et les inverse
               if (table1.nextSibling === table2) {
                   container.insertBefore(table2, table1);
               } else {
                   container.insertBefore(table1, table2);
               }
           }
         </script>";
    }



    /**
     * Show the central RSS view
     *
     * @since 0.84
    **/
    public static function showRSSView()
    {

        echo "<table class='tab_cadre_central' aria-label='RSS View Table'>";

        echo "<tr class='noHover'><td class='top' width='50%'>";
        RSSFeed::showListForCentral();
        echo "</td><td class='top' width='50%'>";
        if (RSSFeed::canView()) {
            RSSFeed::showListForCentral(false);
        } else {
            echo "&nbsp;";
        }
        echo "</td></tr>";
        echo "</table>";
    }


    /**
     * Show the central group view
    **/
    public static function showGroupView()
    {

        $showticket = Session::haveRightsOr("ticket", [Ticket::READALL, Ticket::READASSIGN]);

        $showproblem = Session::haveRightsOr('problem', [Problem::READALL, Problem::READMY]);

        echo "<div class='group-view-container'>";
        echo "<div class='group-view-table'>";
        echo "<table class='central' aria-label='Group View'>";
        echo "<tr class='noHover'><td>";
        if ($showticket) {
            Ticket::showCentralList(0, "process", true);
            TicketTask::showCentralList(0, "todo", true);
        }
        if (Session::haveRight('ticket', Ticket::READGROUP)) {
            Ticket::showCentralList(0, "waiting", true);
        }
        if ($showproblem) {
            Problem::showCentralList(0, "process", true);
            ProblemTask::showCentralList(0, "todo", true);
        }

        echo "</td></tr>";
        echo "</table>";
        echo "</div>";

        echo "<div class='group-view-table'>";
        echo "<table class='central' aria-label='Group View'>";
        echo "<tr class='noHover'><td>";
        if (Session::haveRight('ticket', Ticket::READGROUP)) {
            Ticket::showCentralList(0, "observed", true);
            Ticket::showCentralList(0, "toapprove", true);
            Ticket::showCentralList(0, "requestbyself", true);
        } else {
            Ticket::showCentralList(0, "waiting", true);
        }
        echo "</td></tr>";
        echo "</table>";
        echo "</div>";

        echo "</div>";
    }



    public static function showMessages()
    {
        global $DB, $CFG_GLPI;

        $warnings = [];

        Plugin::doHook('display_central');
        $user = new User();
        $user->getFromDB(Session::getLoginUserID());
        if ($user->fields['authtype'] == Auth::DB_GLPI && $user->shouldChangePassword()) {
            $expiration_msg = sprintf(
                __('Your password will expire on %s.'),
                Html::convDateTime(date('Y-m-d H:i:s', $user->getPasswordExpirationTime()))
            );
            $warnings[] = $expiration_msg
               . ' '
               . '<a href="' . $CFG_GLPI['root_doc'] . '/front/updatepassword.php">'
               . __('Update my password')
               . '</a>';
        }

        if (Session::haveRight("config", UPDATE)) {
            $logins = User::checkDefaultPasswords();
            $user   = new User();
            if (!empty($logins)) {
                $accounts = [];
                foreach ($logins as $login) {
                    $user->getFromDBbyNameAndAuth($login, Auth::DB_GLPI, 0);
                    $accounts[] = $user->getLink();
                }
                $warnings[] = sprintf(
                    __('For security reasons, please change the password for the default users: %s'),
                    implode(" ", $accounts)
                );
            }

            if (file_exists(GLPI_ROOT . "/install/install.php")) {
                $warnings[] = sprintf(
                    __('For security reasons, please remove file: %s'),
                    "install/install.php"
                );
            }

            $myisam_tables = $DB->getMyIsamTables();
            if (count($myisam_tables)) {
                $warnings[] = sprintf(
                    __('%1$s tables not migrated to InnoDB engine.'),
                    count($myisam_tables)
                );
            }
            if ($DB->areTimezonesAvailable()) {
                $not_tstamp = $DB->notTzMigrated();
                if ($not_tstamp > 0) {
                    $warnings[] = sprintf(
                        __('%1$s columns are not compatible with timezones usage.'),
                        $not_tstamp
                    );
                }
            }
        }

        if (
            $DB->isSlave()
            && !$DB->first_connection
        ) {
            $warnings[] = __('SQL replica: read only');
        }

        if (count($warnings)) {
            ?>
         <div class='alert alert-warning'>
            <?php echo "<ul><li>" . implode('</li><li>', $warnings) . "</li></ul>" ?>
         </div>
            <?php
        }
    }

}
