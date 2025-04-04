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

class TicketTask extends CommonITILTask
{
    public static $rightname = 'task';


    public static function getTypeName($nb = 0)
    {
        return _n('Ticket task', 'Ticket tasks', $nb);
    }


    public static function canCreate()
    {
        return (Session::haveRight(self::$rightname, parent::ADDALLITEM)
                || Session::haveRight('ticket', Ticket::OWN));
    }


    public static function canView()
    {
        return (Session::haveRightsOr(self::$rightname, [parent::SEEPUBLIC, parent::SEEPRIVATE])
                || Session::haveRight('ticket', Ticket::OWN));
    }


    public static function canUpdate()
    {
        return (Session::haveRight(self::$rightname, parent::UPDATEALL)
                || Session::haveRight('ticket', Ticket::OWN));
    }


    public function canViewPrivates()
    {
        return Session::haveRight(self::$rightname, parent::SEEPRIVATE);
    }


    public function canEditAll()
    {
        return Session::haveRight(self::$rightname, parent::UPDATEALL);
    }


    /**
     * Does current user have right to show the current task?
     *
     * @return boolean
    **/
    public function canViewItem()
    {

        if (!$this->canReadITILItem()) {
            return false;
        }

        if (Session::haveRight(self::$rightname, parent::SEEPRIVATE)) {
            return true;
        }

        if (
            !$this->fields['is_private']
            && Session::haveRight(self::$rightname, parent::SEEPUBLIC)
        ) {
            return true;
        }

        // see task created or affected to me
        if (
            Session::getCurrentInterface() == "central"
            && ($this->fields["users_id"] === Session::getLoginUserID())
                || ($this->fields["tech_users_id"] === Session::getLoginUserID())
        ) {
            return true;
        }

        if (
            $this->fields["tech_groups_id"] && ($this->fields["tech_groups_id"] > 0)
            && isset($_SESSION["glpigroups"])
            && in_array($this->fields["tech_groups_id"], $_SESSION["glpigroups"])
        ) {
            return true;
        }

        return false;
    }


    /**
     * Does current user have right to create the current task?
     *
     * @return boolean
    **/
    public function canCreateItem()
    {

        if (!$this->canReadITILItem()) {
            return false;
        }

        $ticket = new Ticket();
        if (
            $ticket->getFromDB($this->fields['tickets_id'])
            // No validation for closed tickets
            && !in_array($ticket->fields['status'], $ticket->getClosedStatusArray())
        ) {
            return (Session::haveRight(self::$rightname, parent::ADDALLITEM)
                    || $ticket->isUser(CommonITILActor::ASSIGN, Session::getLoginUserID())
                    || (isset($_SESSION["glpigroups"])
                        && $ticket->haveAGroup(
                            CommonITILActor::ASSIGN,
                            $_SESSION['glpigroups']
                        )));
        }
        return false;
    }


    /**
     * Does current user have right to update the current task?
     *
     * @return boolean
    **/
    public function canUpdateItem()
    {

        if (!$this->canReadITILItem()) {
            return false;
        }

        $ticket = new Ticket();
        if (
            $ticket->getFromDB($this->fields['tickets_id'])
            && in_array($ticket->fields['status'], $ticket->getClosedStatusArray())
        ) {
            return false;
        }

        if (
            ($this->fields["users_id"] != Session::getLoginUserID())
            && !Session::haveRight(self::$rightname, parent::UPDATEALL)
        ) {
            return false;
        }

        return true;
    }


    /**
     * Does current user have right to purge the current task?
     *
     * @return boolean
    **/
    public function canPurgeItem()
    {
        $ticket = new Ticket();
        if (
            $ticket->getFromDB($this->fields['tickets_id'])
            && in_array($ticket->fields['status'], $ticket->getClosedStatusArray())
        ) {
            return false;
        }

        return Session::haveRight(self::$rightname, PURGE);
    }


    /**
     * Populate the planning with planned ticket tasks
     *
     * @param $options   array of possible options:
     *    - who          ID of the user (0 = undefined)
     *    - whogroup     ID of the group of users (0 = undefined)
     *    - begin        Date
     *    - end          Date
     *
     * @return array of planning item
    **/
    public static function populatePlanning($options = []): array
    {
        return parent::genericPopulatePlanning(__CLASS__, $options);
    }


    /**
     * Populate the planning with planned ticket tasks
     *
     * @param $options   array of possible options:
     *    - who          ID of the user (0 = undefined)
     *    - whogroup     ID of the group of users (0 = undefined)
     *    - begin        Date
     *    - end          Date
     *
     * @return array of planning item
    **/
    public static function populateNotPlanned($options = []): array
    {
        return parent::genericPopulateNotPlanned(__CLASS__, $options);
    }


    /**
     * Display a Planning Item
     *
     * @param array           $val       array of the item to display
     * @param integer         $who       ID of the user (0 if all)
     * @param string          $type      position of the item in the time block (in, through, begin or end)
     * @param integer|boolean $complete  complete display (more details)
     *
     * @return string
     */
    public static function displayPlanningItem(array $val, $who, $type = "", $complete = 0)
    {
        return parent::genericDisplayPlanningItem(__CLASS__, $val, $who, $type, $complete);
    }


    /**
     * @since 0.85
     *
     * @see commonDBTM::getRights()
     **/
    public function getRights($interface = 'central')
    {

        $values = parent::getRights();
        unset($values[UPDATE], $values[CREATE], $values[READ]);

        if ($interface == 'central') {
            $values[parent::UPDATEALL]      = __('Update all');
            $values[parent::ADDALLITEM  ]   = __('Add to all items');
            $values[parent::SEEPRIVATE]     = __('See private ones');
        }

        $values[parent::SEEPUBLIC]   = __('See public ones');

        if ($interface == 'helpdesk') {
            unset($values[PURGE]);
        }

        return $values;
    }


    /**
     * @since 0.90
     *
     * @see CommonDBTM::showFormButtons()
    **/
    public function showFormButtons($options = [])
    {
        // for single object like config
        $ID = 1;
        if (isset($this->fields['id'])) {
            $ID = $this->fields['id'];
        }

        $params['colspan']      = 2;
        $params['candel']       = true;
        $params['canedit']      = true;

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $params[$key] = $val;
            }
        }

        if (!$this->isNewID($ID)) {
            echo "<input type='hidden' name='id' value='$ID'>";
        }

        Plugin::doHook("post_item_form", ['item' => $this, 'options' => &$params]);

        echo "<tr class='tab_bg_2'>";
        echo "<td class='center' colspan='" . ($params['colspan'] * 2) . "'>";

        if ($this->isNewID($ID)) {
            echo Ticket::getSplittedSubmitButtonHtml($this->fields['tickets_id'], 'add');
        } else {
            if (
                $params['candel']
                  // no trashbin in tickettask
                //   && !$this->can($ID, DELETE)
                && !$this->can($ID, PURGE)
            ) {
                $params['candel'] = false;
            }

            if ($params['canedit'] && $this->canUpdateItem()) {
                echo Ticket::getSplittedSubmitButtonHtml($this->fields['tickets_id'], 'update');
                echo "</td></tr><tr class='tab_bg_2'>\n";
            }

            if ($params['candel']) {
                echo "<td class='right' colspan='" . ($params['colspan'] * 2) . "' >\n";
                if ($this->can($ID, PURGE)) {
                    echo Html::submit(
                        _x('button', 'Delete permanently'),
                        ['name'    => 'purge',
                                            'confirm' => __('Confirm the final deletion?')]
                    );
                }
            }

            if ($this->isField('date_mod')) {
                echo "<input type='hidden' name='_read_date_mod' value='" . $this->getField('date_mod') . "'>";
            }
        }

        echo "</td></tr></table></div>";
        Html::closeForm();
    }

    /**
     * Build parent condition for search
     *
     * @return string
     */
    public static function buildParentCondition()
    {
        return "(0 = 1 " . Ticket::buildCanViewCondition("tickets_id") . ") ";
    }
}
