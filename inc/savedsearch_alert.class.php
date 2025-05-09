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
 * Saved search alerts
**/
class SavedSearch_Alert extends CommonDBChild
{
    // From CommonDBChild
    public static $itemtype = 'SavedSearch';
    public static $items_id = 'savedsearches_id';
    public $dohistory       = true;
    protected $displaylist  = false;

    public const OP_LESS     = 0;
    public const OP_LESSEQ   = 1;
    public const OP_EQ       = 2;
    public const OP_NOT      = 3;
    public const OP_GREATEQ  = 4;
    public const OP_GREAT    = 5;

    public static function getTypeName($nb = 0)
    {
        return _n('Saved search alert', 'Saved searches alerts', $nb);
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        // can exists for template
        if (
            ($item->getType() == 'SavedSearch')
            && SavedSearch::canView()
        ) {
            $nb = 0;
            if ($_SESSION['glpishow_count_on_tabs']) {
                $nb = countElementsInTable(
                    $this->getTable(),
                    ['savedsearches_id' => $item->getID()]
                );
            }
            return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showForSavedSearch($item, $withtemplate);
        return true;
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    /**
     * Print the form
     *
     * @param integer $ID      integer ID of the item
     * @param array   $options array
     *     - target for the Form
     *     - computers_id ID of the computer for add process
     *
     * @return true if displayed  false if item not found or not right to display
    **/
    public function showForm($ID, $options = [])
    {

        /*if (!Session::haveRight("savedsearch", UPDATE)) {
           return false;
        }*/

        $search = new SavedSearch();
        if ($ID > 0) {
            $this->check($ID, READ);
            $search->getFromDB($this->fields['savedsearches_id']);
        } else {
            $this->check(-1, CREATE, $options);
            $search->getFromDB($options['savedsearches_id']);
        }

        $count = null;
        try {
            if ($data = $search->execute()) {
                $count = $data['data']['totalcount'];
            }
        } catch (\RuntimeException $e) {
            Toolbox::logError($e);
        }

        $form = [
           'action' => $this->getFormURL(),
           'itemtype' => self::class,
           'content' => [
              self::getTypeName(1) => [
                 'visible' => 'true',
                 'inputs' => [
                    [
                       'type' => 'hidden',
                       'name' => 'id',
                       'value' => $ID,
                    ],
                    ($this->isNewID($ID)) ? [
                       'type' => 'hidden',
                       'name' => 'savedsearches_id',
                       'value' => $options['savedsearches_id'],
                    ] : [],
                    SavedSearch::getTypeName(1) => [
                       'content' => $search->getLink() . $count ?? '',
                    ],
                    __('Name') => [
                       'type' => 'text',
                       'name' => 'name',
                       'value' => $this->fields['name'],
                    ],
                    __('Operator') => [
                       'type' => 'select',
                       'name' => 'operator',
                       'values' => $this->getOperators(),
                       'value' => $this->getField('operator'),
                       'title' => __('Compare number of results the search returns against the specified value with selected operator')
                    ],
                    __('Value') => [
                       'type' => 'number',
                       'name' => 'value',
                       'value' => $this->getField('value'),
                       'min' => 0,
                       'required' => ''
                    ],
                    __('Active') => [
                       'type' => 'checkbox',
                       'name' => 'is_active',
                       'value' => $this->getField('is_active'),
                    ],
                 ]
              ]
           ]
        ];
        renderTwigForm($form, '', $this->fields);

        return true;
    }


    /**
     * Print the searches alerts
     *
     * @param SavedSearch $search       Object instance
     * @param boolean     $withtemplate Template or basic item (default '')
     *
     * @return void
    **/
    public static function showForSavedSearch(SavedSearch $search, $withtemplate = 0)
    {
        $ID = $search->getID();

        if (
            !$search->getFromDB($ID)
            || !$search->can($ID, READ)
        ) {
            return false;
        }
        $canedit = $search->canEdit($ID);

        echo "<div class='center'>";

        echo "<div class='firstbloc'>";

        $request = self::getAdapter()->request([
           'FROM'   => Notification::getTable(),
           'WHERE'  => [
              'itemtype'  => self::getType(),
              'event'     => 'alert' . ($search->getField('is_private') ? '' : '_' . $search->getID())
           ]
        ]);
        $results = $request->fetchAllAssociative();
        if (!count($results)) {
            echo "<span class='required'><strong>" . __('Notification does not exists!') . "</strong></span>";
            if ($canedit) {
                echo "<br/><a href='{$search->getFormURLWithID($search->fields['id'])}&amp;create_notif=true'>"
                   . __('create it now') . "</a>";
                $canedit = false;
            }
        } else {
            echo _n('Notification used:', 'Notifications used:', count($results)) . "&nbsp;";
            $first = true;
            foreach ($results as $row) {
                if (!$first) {
                    echo ', ';
                }
                if (Session::haveRight('notification', UPDATE)) {
                    $url = Notification::getFormURLWithID($row['id']);
                    echo "<a href='$url'>" . $row['name'] . "</a>";
                } else {
                    echo $row['name'];
                }
                $first = false;
            }
        }
        echo '</div>';

        if (
            $canedit
            && !(!empty($withtemplate) && ($withtemplate == 2))
        ) {
            echo "<div class='firstbloc'>" .
                  "<a class='btn btn-secondary' href='" . self::getFormURL() . "?savedsearches_id=$ID&amp;withtemplate=" .
                     $withtemplate . "'>";
            echo __('Add an alert');
            echo "</a></div>\n";
        }

        $request = self::getAdapter()->request([
           'FROM'   => self::getTable(),
           'WHERE'  => ['savedsearches_id' => $ID]
        ])->fetchAllAssociative();

        echo "<table class='tab_cadre_fixehov' aria-label'Tables for active item'>";

        $colspan = 4;
        if (count($request)) {
            echo "<tr class='noHover'><th colspan='$colspan'>" . self::getTypeName(count($request)) .
               "</th></tr>";

            $header = "<tr><th>" . __('Name') . "</th>";
            $header .= "<th>" . __('Operator') . "</th>";
            $header .= "<th>" . __('Value') . "</th>";
            $header .= "<th>" . __('Active') . "</th>";
            $header .= "</tr>";
            echo $header;

            $alert = new self();
            foreach ($request as $data) {
                $alert->getFromDB($data['id']);
                echo "<tr class='tab_bg_2'>";
                echo "<td>" . $alert->getLink() . "</td>";
                echo "<td>" . self::getOperators($data['operator']) . "</td>";
                echo "<td>" . $data['value'] . "</td>";
                echo "<td>" . Dropdown::getYesNo($data['is_active']) . "</td>";
                echo "</tr>";
                Session::addToNavigateListItems(__CLASS__, $data['id']);
            }
            echo $header;
        } else {
            echo "<tr class='tab_bg_2'><th colspan='$colspan'>" . __('No item found') . "</th></tr>";
        }

        echo "</table>";
        echo "</div>";
    }

    /**
     * Get operators
     *
     * @param integer $id ID for the operator to retrieve, or null for the full list
     *
     * @return string|array
     */
    public static function getOperators($id = null)
    {
        $ops = [
           self::OP_LESS     => '<',
           self::OP_LESSEQ   => '<=',
           self::OP_EQ       => '=',
           self::OP_NOT      => '!=',
           self::OP_GREATEQ  => '>=',
           self::OP_GREAT    => '>'
        ];
        return ($id === null ? $ops : $ops[$id]);
    }

    public static function cronInfo($name)
    {
        switch ($name) {
            case 'send':
                return ['description' => __('Saved searches alerts')];
        }
        return [];
    }

    /**
     * Summary of saveContext
     *
     * Save $_SESSION and $CFG_GLPI into the returned array
     *
     * @return array[] which contains a copy of $_SESSION and $CFG_GLPI
     */
    private static function saveContext()
    {
        global $CFG_GLPI;
        $context = [];
        $context['$_SESSION'] = $_SESSION;
        $context['$CFG_GLPI'] = $CFG_GLPI;
        return $context;
    }

    /**
     * Summary of restoreContext
     *
     * restore former $_SESSION and $CFG_GLPI
     * to be sure that logs will be in GLPI default datetime and language
     * and that session is restored for the next crontaskaction
     *
     * @param mixed $context is the array returned by saveContext
     */
    private static function restoreContext($context)
    {
        global $CFG_GLPI;
        $_SESSION = $context['$_SESSION'];
        $CFG_GLPI = $context['$CFG_GLPI'];
        Session::loadLanguage();
        Plugin::doHook("init_session");
    }

    /**
     * Send saved searches alerts
     *
     * @param CronTask $task CronTask instance
     *
     * @return int : <0 : need to run again, 0:nothing to do, >0:ok
     */
    public static function cronSavedSearchesAlerts($task)
    {
        $request = self::getAdapter()->request([
           'FROM'   => self::getTable(),
           'WHERE'  => ['is_active' => true]
        ])->fetchAllAssociative();

        if (count($request)) {
            $savedsearch = new SavedSearch();

            if (!isset($_SESSION['glpiname'])) {
                //required from search class
                $_SESSION['glpiname'] = 'crontab';
            }

            // Will save $_SESSION and $CFG_GLPI cron context into an array
            $context = self::saveContext();

            foreach ($request as $row) {
                //execute saved search to get results
                try {
                    $savedsearch->getFromDB($row['savedsearches_id']);
                    if (isCommandLine()) {
                        //search requires a logged in user...
                        $user = new User();
                        $user->getFromDB($savedsearch->fields['users_id']);
                        $auth = new Auth();
                        $auth->user = $user;
                        $auth->auth_succeded = true;
                        Session::init($auth);
                    }

                    $data = $savedsearch->execute(true);
                    $count = (int)$data['data']['totalcount'];
                    $value = (int)$row['value'];

                    $notify = false;
                    $tr_op = null;

                    switch ($row['operator']) {
                        case self::OP_LESS:
                            $notify = $count < $value;
                            $tr_op = __('less than');
                            break;
                        case self::OP_LESSEQ:
                            $notify = $count <= $value;
                            $tr_op = __('less or equals than');
                            break;
                        case self::OP_EQ:
                            $notify = $count == $value;
                            $tr_op = __('equals to');
                            break;
                        case self::OP_NOT:
                            $notify = $count != $value;
                            $tr_op = __('not equals to');
                            break;
                        case self::OP_GREATEQ:
                            $notify = $count >= $value;
                            $tr_op = __('greater or equals than');
                            break;
                        case self::OP_GREAT:
                            $notify = $count > $value;
                            $tr_op = __('greater than');
                            break;
                        default:
                            throw new \RuntimeException("Unknonw operator '{$row['operator']}'");
                    }

                    //TRANS : %1$s is the name of the saved search,
                    //        %2$s is the comparison translated text
                    //        %3$s is the value compared to
                    $data['msg'] = sprintf(
                        __('Results count for %1$s is %2$s %3$s'),
                        $savedsearch->getName(),
                        $tr_op,
                        $value
                    );

                    // Will restore previously saved $_SESSION and $CFG_GLPI:
                    //  To be sure that logs will be in GLPI with default datetime and language
                    //  and that notifications are sent even if $_SESSION['glpinotification_to_myself'] is false
                    //  and to restore default cron $_SESSION and $CFG_GLPI global variables for next cron task
                    self::restoreContext($context);

                    if ($notify) {
                        $event = 'alert' . ($savedsearch->getField('is_private') ? '' : '_' . $savedsearch->getID());
                        $alert = new self();
                        $alert->getFromDB($row['id']);
                        $data['savedsearch'] = $savedsearch;
                        NotificationEvent::raiseEvent($event, $alert, $data);
                        $task->addVolume(1);
                    }
                } catch (\Exception $e) {
                    self::restoreContext($context);
                    Toolbox::logError($e);
                }
            }
            return 1;
        }
        return 0;
    }

    public function getItemsForLog($itemtype, $items_id)
    {
        return ['new' => $this];
    }
}
