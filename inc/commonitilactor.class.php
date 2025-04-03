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
 * CommonITILActor Class
**/
abstract class CommonITILActor extends CommonDBRelation
{
    // items_id_1, items_id_2, itemtype_1 and itemtype_2 are defined inside the inherited classes
    public static $checkItem_2_Rights  = self::DONT_CHECK_ITEM_RIGHTS;
    public static $logs_for_item_2     = false;
    public $auto_message_on_action     = false;

    // Requester
    public const REQUESTER = 1;
    // Assign
    public const ASSIGN    = 2;
    // Observer
    public const OBSERVER  = 3;


    public function getActorForeignKey()
    {
        return static::$items_id_2;
    }


    public static function getItilObjectForeignKey()
    {
        return static::$items_id_1;
    }


    /**
     * @since 0.84
     *
     * @param $input  array of data to be added
     *
     * @see CommonDBRelation::isAttach2Valid()
    **/
    public function isAttach2Valid(array &$input)
    {

        // Anonymous user is valid if 'alternative_email' field is not empty
        if (
            isset($input['users_id']) && ($input['users_id'] == 0)
            && isset($input['alternative_email']) && !empty($input['alternative_email'])
        ) {
            return true;
        }
        // Anonymous supplier is valid if 'alternative_email' field is not empty
        if (
            isset($input['suppliers_id']) && ($input['suppliers_id'] == 0)
            && isset($input['alternative_email']) && !empty($input['alternative_email'])
        ) {
            return true;
        }
        return false;
    }


    /**
     * @param $items_id
    **/
    public function getActors($items_id)
    {

        $users = [];
        $request = $this::getAdapter()->request([
           'FROM'   => $this->getTable(),
           'WHERE'  => [static::getItilObjectForeignKey() => $items_id],
           'ORDER'  => 'id ASC'
        ]);
        while ($data = $request->fetchAssociative()) {
            $users[$data['type']][] = $data;
        }
        return $users;
    }


    /**
     * @param $items_id
     * @param $email
    **/
    public function isAlternateEmailForITILObject($items_id, $email)
    {
        $request = $this::getAdapter()->request([
           'FROM'   => $this->getTable(),
           'WHERE'  => [
              static::getItilObjectForeignKey()   => $items_id,
              'alternative_email'                 => $email
           ],
           'START'  => 0,
           'LIMIT'  => 1
        ]);
        $result = $request->fetchAssociative();
        if ($result) {
            return true;
        }
        return false;
    }


    public function canUpdateItem()
    {

        return (parent::canUpdateItem()
                || (isset($this->fields['users_id'])
                    && ($this->fields['users_id'] == Session::getLoginUserID())));
    }


    /**
     * @since 0.84
    **/
    public function canDeleteItem()
    {

        return (parent::canDeleteItem()
                || (isset($this->fields['users_id'])
                    && ($this->fields['users_id'] == Session::getLoginUserID())));
    }

    /**
     * Print the object user form for notification
     *
     * @param $ID              integer ID of the item
     * @param $options   array
     *
     * @return void
    **/
    public function showUserNotificationForm($ID, $options = [])
    {

        $this->check($ID, UPDATE);

        if (!isset($this->fields['users_id'])) {
            return false;
        }
        $item = new static::$itemtype_1();

        echo "<br><form aria-label='Notification' method='post' action='" . $_SERVER['PHP_SELF'] . "'>";
        echo "<div class='center'>";
        echo "<table class='tab_cadre' width='80%' aria-label='User Notification'>";
        echo "<tr class='tab_bg_2'><td>" . $item->getTypeName(1) . "</td>";
        echo "<td>";
        if ($item->getFromDB($this->fields[static::getItilObjectForeignKey()])) {
            echo $item->getField('name');
        }
        echo "</td></tr>";

        $user          = new User();
        $default_email = "";
        $emails = [];
        if ($user->getFromDB($this->fields["users_id"])) {
            $default_email = $user->getDefaultEmail();
            $emails        = $user->getAllEmails();
        }

        echo "<tr class='tab_bg_2'><td>" . User::getTypeName(1) . "</td>";
        echo "<td>" . $user->getName() . "</td></tr>";

        echo "<tr class='tab_bg_1'><td>" . __('Email Followup') . "</td>";
        echo "<td>";
        Dropdown::showYesNo('use_notification', $this->fields['use_notification']);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'><td>" . _n('Email', 'Emails', 1) . "</td>";
        echo "<td>";
        if (
            (count($emails) ==  1)
            && !empty($default_email)
            && NotificationMailing::isUserAddressValid($default_email)
        ) {
            echo $default_email;
        } elseif (count($emails) > 1) {
            // Several emails : select in the list
            $emailtab = [];
            foreach ($emails as $new_email) {
                $emailtab[$new_email] = $new_email;
            }
            Dropdown::showFromArray(
                "alternative_email",
                $emailtab,
                ['value'   => $this->fields['alternative_email']]
            );
        } else {
            echo "<input type='text' size='40' name='alternative_email' value='" .
                   $this->fields['alternative_email'] . "'>";
        }
        echo "</td></tr>";

        echo "<tr class='tab_bg_2'>";
        echo "<td class='center' colspan='2'>";
        echo "<input type='submit' name='update' value=\"" . _sx('button', 'Save') . "\" class='submit'>";
        echo "<input type='hidden' name='id' value='$ID'>";
        echo "</td></tr>";

        echo "</table></div>";
        Html::closeForm();
    }


    /**
     * Print the object user form for notification
     *
     * @since 0.85
     *
     * @param $ID              integer ID of the item
     * @param $options   array
     *
     * @return void
    **/
    public function showSupplierNotificationForm($ID, $options = [])
    {

        $this->check($ID, UPDATE);

        if (!isset($this->fields['suppliers_id'])) {
            return false;
        }
        $item = new static::$itemtype_1();

        echo "<br><form aria-label='Supplier Notification' method='post' action='" . $_SERVER['PHP_SELF'] . "'>";
        echo "<div class='center'>";
        echo "<table class='tab_cadre' width='80%' aria-label='Supplier Notifications'>";
        echo "<tr class='tab_bg_2'><td>" . $item->getTypeName(1) . "</td>";
        echo "<td>";
        if ($item->getFromDB($this->fields[static::getItilObjectForeignKey()])) {
            echo $item->getField('name');
        }
        echo "</td></tr>";

        $supplier      = new Supplier();
        $default_email = "";
        if ($supplier->getFromDB($this->fields["suppliers_id"])) {
            $default_email = $supplier->fields['email'];
        }

        echo "<tr class='tab_bg_2'><td>" . Supplier::getTypeName(1) . "</td>";
        echo "<td>" . $supplier->getName() . "</td></tr>";

        echo "<tr class='tab_bg_1'><td>" . __('Email Followup') . "</td>";
        echo "<td>";
        Dropdown::showYesNo('use_notification', $this->fields['use_notification']);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'><td>" . _n('Email', 'Emails', 1) . "</td>";
        echo "<td>";
        if (empty($this->fields['alternative_email'])) {
            $this->fields['alternative_email'] = $default_email;
        }
        echo "<input type='text' size='40' name='alternative_email' value='" .
               $this->fields['alternative_email'] . "'>";
        echo "</td></tr>";

        echo "<tr class='tab_bg_2'>";
        echo "<td class='center' colspan='2'>";
        echo "<input type='submit' name='update' value=\"" . _sx('button', 'Save') . "\" class='submit'>";
        echo "<input type='hidden' name='id' value='$ID'>";
        echo "</td></tr>";

        echo "</table></div>";
        Html::closeForm();
    }


    public function post_deleteFromDB()
    {
        global $CFG_GLPI;

        $donotif = !isset($this->input['_disablenotif']) && $CFG_GLPI["use_notifications"];

        $item = $this->getConnexityItem(static::$itemtype_1, static::getItilObjectForeignKey());

        if ($item instanceof CommonDBTM) {
            if (
                ($item->countSuppliers(CommonITILActor::ASSIGN) == 0)
                && ($item->countUsers(CommonITILActor::ASSIGN) == 0)
                && ($item->countGroups(CommonITILActor::ASSIGN) == 0)
                && ($item->fields['status'] != CommonITILObject::CLOSED)
                && ($item->fields['status'] != CommonITILObject::SOLVED)
            ) {
                $status = CommonITILObject::INCOMING;
                if (in_array($item->fields['status'], Change::getNewStatusArray())) {
                    $status = $item->fields['status'];
                }
                $item->update(['id'     => $this->fields[static::getItilObjectForeignKey()],
                                    'status' => $status]);
            } else {
                $item->updateDateMod($this->fields[static::getItilObjectForeignKey()]);

                if ($donotif) {
                    $options = [];
                    if (isset($this->fields['users_id'])) {
                        $options = ['_old_user' => $this->fields];
                    }
                    NotificationEvent::raiseEvent("update", $item, $options);
                }
            }
        }
        parent::post_deleteFromDB();
    }


    /**
     * @since 0.85
     *
     * @see CommonDBRelation::prepareInputForAdd()
    **/
    public function prepareInputForAdd($input)
    {

        if (!isset($input['alternative_email']) || is_null($input['alternative_email'])) {
            $input['alternative_email'] = '';
        } elseif ($input['alternative_email'] != '' && !NotificationMailing::isUserAddressValid($input['alternative_email'])) {
            Session::addMessageAfterRedirect(
                __('Invalid email address'),
                false,
                ERROR
            );
            return false;
        }
        return $input;
    }

    public function prepareInputForUpdate($input)
    {
        if (isset($input['alternative_email']) && $input['alternative_email'] == '') {
            if (isset($input['users_id'])) {
                $actor = new User();
                if ($actor->getFromDB($input["users_id"])) {
                    $input['alternative_email'] = $actor->getDefaultEmail();
                }
            }
            if (isset($input['suppliers_id'])) {
                $actor = new Supplier();
                if ($actor->getFromDB($input["suppliers_id"])) {
                    $input['alternative_email'] = $actor->fields['email'];
                }
            }
        }
        if (
            isset($input['alternative_email']) && $input['alternative_email'] != ''
            && !NotificationMailing::isUserAddressValid($input['alternative_email'])
        ) {
            Session::addMessageAfterRedirect(
                __('Invalid email address'),
                false,
                ERROR
            );
            return false;
        }

        $input = parent::prepareInputForUpdate($input);
        return $input;
    }

    public function post_addItem()
    {

        $item = new static::$itemtype_1();

        $no_stat_computation = true;
        if ($this->input['type'] == CommonITILActor::ASSIGN) {
            // Compute "take into account delay" unless "do not compute" flag was set by business rules
            $no_stat_computation = $item->isTakeIntoAccountComputationBlocked($this->input);
        }
        $item->updateDateMod($this->fields[static::getItilObjectForeignKey()], $no_stat_computation);

        if ($item->getFromDB($this->fields[static::getItilObjectForeignKey()])) {
            // Check object status and update it if needed
            if (
                !isset($this->input['_from_object'])
                && in_array($item->fields["status"], $item->getNewStatusArray())
                && in_array(CommonITILObject::ASSIGNED, array_keys($item->getAllStatusArray()))
            ) {
                $item->update(['id'               => $item->getID(),
                               'status'           => CommonITILObject::ASSIGNED,
                               '_from_assignment' => true]);
            }

            // raise notification for this actor addition
            if (!isset($this->input['_disablenotif'])) {
                $string_type = "";
                switch ($this->input['type']) {
                    case self::REQUESTER:
                        $string_type = "requester";
                        break;
                    case self::OBSERVER:
                        $string_type = "observer";
                        break;
                    case self::ASSIGN:
                        $string_type = "assign";
                        break;
                }
                // example for event: assign_group
                $event = $string_type . "_" . strtolower($this::$itemtype_2);
                NotificationEvent::raiseEvent($event, $item);
            }
        }

        parent::post_addItem();
    }
}
