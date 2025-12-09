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

use function PHPSTORM_META\map;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

/**
 * @since 9.4.0
 */
class ITILFollowup extends CommonDBChild
{
    // From CommonDBTM
    public $auto_message_on_action = false;
    public static $rightname              = 'followup';
    private $item                  = null;

    public static $log_history_add    = Log::HISTORY_LOG_SIMPLE_MESSAGE;
    public static $log_history_update = Log::HISTORY_LOG_SIMPLE_MESSAGE;
    public static $log_history_delete = Log::HISTORY_LOG_SIMPLE_MESSAGE;

    public const SEEPUBLIC       =    1;
    public const UPDATEMY        =    2;
    public const ADDMYTICKET     =    4;
    public const UPDATEALL       = 1024;
    public const ADDGROUPTICKET  = 2048;
    public const ADDALLTICKET    = 4096;
    public const SEEPRIVATE      = 8192;

    public static $itemtype = 'itemtype';
    public static $items_id = 'items_id';


    public function getItilObjectItemType()
    {
        return str_replace('Followup', '', $this->getType());
    }


    public static function getTypeName($nb = 0)
    {
        return _n('Followup', 'Followups', $nb);
    }


    /**
     * can read the parent ITIL Object ?
     *
     * @return boolean
     */
    public function canReadITILItem()
    {

        $itemtype = $this->getItilObjectItemType();
        $item     = new $itemtype();
        if (!$item->can($this->getField($item->getForeignKeyField()), READ)) {
            return false;
        }
        return true;
    }


    public static function canView()
    {
        return (Session::haveRightsOr(self::$rightname, [self::SEEPUBLIC, self::SEEPRIVATE])
                || Session::haveRight('ticket', Ticket::OWN))
                || Session::haveRight('ticket', READ)
                || Session::haveRight('change', READ)
                || Session::haveRight('problem', READ);
    }


    public static function canCreate()
    {
        return Session::haveRight('change', UPDATE)
               || Session::haveRight('problem', UPDATE)
               || (Session::haveRightsOr(
                   self::$rightname,
                   [self::ADDALLTICKET, self::ADDMYTICKET, self::ADDGROUPTICKET]
               )
               || Session::haveRight('ticket', Ticket::OWN));
    }


    public function canViewItem()
    {

        $itilobject = new $this->fields['itemtype']();
        if (!$itilobject->can($this->getField('items_id'), READ)) {
            return false;
        }
        if (Session::haveRight(self::$rightname, self::SEEPRIVATE)) {
            return true;
        }
        if (
            !$this->fields['is_private']
            && Session::haveRight(self::$rightname, self::SEEPUBLIC)
        ) {
            return true;
        }
        if ($itilobject instanceof Ticket) {
            if ($this->fields["users_id"] === Session::getLoginUserID()) {
                return true;
            }
        } else {
            return Session::haveRight($itilobject::$rightname, READ);
        }
        return false;
    }


    public function canCreateItem()
    {
        if (
            !isset($this->fields['itemtype'])
            || strlen($this->fields['itemtype']) == 0
        ) {
            return false;
        }

        $itilobject = new $this->fields['itemtype']();

        if (
            !$itilobject->can($this->getField('items_id'), READ)
            // No validation for closed tickets
            || in_array($itilobject->fields['status'], $itilobject->getClosedStatusArray())
            && !$itilobject->canReopen()
        ) {
            return false;
        }
        return $itilobject->canAddFollowups();
    }


    public function canPurgeItem()
    {

        $itilobject = new $this->fields['itemtype']();
        if (!$itilobject->can($this->getField('items_id'), READ)) {
            return false;
        }

        if (Session::haveRight(self::$rightname, PURGE)) {
            return true;
        }

        return false;
    }


    public function canUpdateItem()
    {

        if (
            ($this->fields["users_id"] != Session::getLoginUserID())
            && !Session::haveRight(self::$rightname, self::UPDATEALL)
        ) {
            return false;
        }

        $itilobject = new $this->fields['itemtype']();
        if (!$itilobject->can($this->getField('items_id'), READ)) {
            return false;
        }

        if ($this->fields["users_id"] === Session::getLoginUserID()) {
            if (!Session::haveRight(self::$rightname, self::UPDATEMY)) {
                return false;
            }
            return true;
        }

        // Only the technician
        return (Session::haveRight(self::$rightname, self::UPDATEALL)
                || $itilobject->isUser(CommonITILActor::ASSIGN, Session::getLoginUserID())
                || (isset($_SESSION["glpigroups"])
                    && $itilobject->haveAGroup(CommonITILActor::ASSIGN, $_SESSION['glpigroups'])));
    }


    public function post_getEmpty()
    {

        if (isset($_SESSION['glpifollowup_private']) && $_SESSION['glpifollowup_private']) {
            $this->fields['is_private'] = 1;
        }

        if (isset($_SESSION["glpiname"])) {
            $this->fields['requesttypes_id'] = RequestType::getDefault('followup');
        }
    }


    public function post_addItem()
    {

        global $CFG_GLPI;

        // Add screenshots if needed, without notification
        $this->input = $this->addFiles($this->input, [
           'force_update'  => true,
           'name'          => 'content',
           'content_field' => 'content',
           'date' => $this->fields['date'],
        ]);

        // Add documents if needed, without notification
        $this->input = $this->addFiles($this->input, [
           'force_update'  => true,
           'date' => $this->fields['date'],
        ]);

        $donotif = !isset($this->input['_disablenotif']) && $CFG_GLPI["use_notifications"];

        // Check if stats should be computed after this change
        $no_stat = isset($this->input['_do_not_compute_takeintoaccount']);

        $parentitem = $this->input['_job'];
        $parentitem->updateDateMod(
            $this->input["items_id"],
            $no_stat,
            $this->input["users_id"]
        );

        if (
            isset($this->input["_close"])
            && $this->input["_close"]
            && ($parentitem->fields["status"] == CommonITILObject::SOLVED)
        ) {
            $update = [
               'id'        => $parentitem->fields['id'],
               'status'    => CommonITILObject::CLOSED,
               'closedate' => $_SESSION["glpi_currenttime"],
               '_accepted' => true,
            ];

            // Use update method for history
            $this->input["_job"]->update($update);
            $donotif = false; // Done for ITILObject update (new status)
        }

        //manage reopening of ITILObject
        $reopened = false;
        if (!isset($this->input['_status'])) {
            $this->input['_status'] = $parentitem->fields["status"];
        }
        // if reopen set (from followup form or mailcollector)
        // and status is reopenable and not changed in form
        if (
            isset($this->input["_reopen"])
            && $this->input["_reopen"]
            && in_array($parentitem->fields["status"], $parentitem::getReopenableStatusArray())
            && $this->input['_status'] == $parentitem->fields["status"]
        ) {
            $needupdateparent = false;
            if (
                ($parentitem->countUsers(CommonITILActor::ASSIGN) > 0)
                || ($parentitem->countGroups(CommonITILActor::ASSIGN) > 0)
                || ($parentitem->countSuppliers(CommonITILActor::ASSIGN) > 0)
            ) {
                //check if lifecycle allowed new status
                if (
                    Session::isCron()
                    || Session::getCurrentInterface() == "helpdesk"
                    || $parentitem::isAllowedStatus($parentitem->fields["status"], CommonITILObject::ASSIGNED)
                ) {
                    $needupdateparent = true;
                    $update['status'] = CommonITILObject::ASSIGNED;
                }
            } else {
                //check if lifecycle allowed new status
                if (
                    Session::isCron()
                    || Session::getCurrentInterface() == "helpdesk"
                    || $parentitem::isAllowedStatus($parentitem->fields["status"], CommonITILObject::INCOMING)
                ) {
                    $needupdateparent = true;
                    $update['status'] = CommonITILObject::INCOMING;
                }
            }

            if ($needupdateparent) {
                $update['id'] = $parentitem->fields['id'];

                // Use update method for history
                $parentitem->update($update);
                $reopened     = true;
            }
        }

        //change ITILObject status only if imput change
        if (
            !$reopened
            && $this->input['_status'] != $parentitem->fields['status']
        ) {
            $update['status'] = $this->input['_status'];
            $update['id']     = $parentitem->fields['id'];

            // don't notify on ITILObject - update event
            $update['_disablenotif'] = true;

            // Use update method for history
            $parentitem->update($update);
        }

        if ($donotif) {
            $options = ['followup_id' => $this->fields["id"],
                             'is_private'  => $this->fields['is_private']];
            NotificationEvent::raiseEvent("add_followup", $parentitem, $options);
        }

        // Add log entry in the ITILObject
        $changes = [
           0,
           '',
           $this->fields['id'],
        ];
        Log::history(
            $this->getField('items_id'),
            get_class($parentitem),
            $changes,
            $this->getType(),
            Log::HISTORY_ADD_SUBITEM
        );
    }


    public function post_deleteFromDB()
    {
        global $CFG_GLPI;

        $donotif = $CFG_GLPI["use_notifications"];
        if (isset($this->input['_disablenotif'])) {
            $donotif = false;
        }

        $job = new $this->fields['itemtype']();
        $job->getFromDB($this->fields[self::$items_id]);
        $job->updateDateMod($this->fields[self::$items_id]);

        // Add log entry in the ITIL Object
        $changes = [
           0,
           '',
           $this->fields['id'],
        ];
        Log::history(
            $this->getField(self::$items_id),
            $this->fields['itemtype'],
            $changes,
            $this->getType(),
            Log::HISTORY_DELETE_SUBITEM
        );

        if ($donotif) {
            $options = ['followup_id' => $this->fields["id"],
                              // Force is_private with data / not available
                             'is_private'  => $this->fields['is_private']];
            NotificationEvent::raiseEvent('delete_followup', $job, $options);
        }
    }


    public function prepareInputForAdd($input)
    {

        $input["_job"] = new $input['itemtype']();

        if (
            empty($input['content'])
            && !isset($input['add_close'])
            && !isset($input['add_reopen'])
        ) {
            Session::addMessageAfterRedirect(
                __("You can't add a followup without description"),
                false,
                ERROR
            );
            return false;
        }
        if (!$input["_job"]->getFromDB($input["items_id"])) {
            return false;
        }

        $input['_close'] = 0;

        if (!isset($input["users_id"])) {
            $input["users_id"] = 0;
            if ($uid = Session::getLoginUserID()) {
                $input["users_id"] = $uid;
            }
        }
        // if ($input["_isadmin"] && $input["_type"]!="update") {
        if (isset($input["add_close"])) {
            $input['_close'] = 1;
            if (empty($input['content'])) {
                $input['content'] = __('Solution approved');
            }
        }

        unset($input["add_close"]);

        if (!isset($input["is_private"])) {
            $input['is_private'] = 0;
        }

        if (isset($input["add_reopen"])) {
            if ($input["content"] == '') {
                if (isset($input["_add"])) {
                    // Reopen using add form
                    Session::addMessageAfterRedirect(
                        __('If you want to reopen this item, you must specify a reason'),
                        false,
                        ERROR
                    );
                } else {
                    // Refuse solution
                    Session::addMessageAfterRedirect(
                        __('If you reject the solution, you must specify a reason'),
                        false,
                        ERROR
                    );
                }
                return false;
            }
            $input['_reopen'] = 1;
        }
        unset($input["add_reopen"]);
        // }
        unset($input["add"]);

        $itemtype = $input['itemtype'];
        $input['timeline_position'] = $itemtype::getTimelinePosition($input["items_id"], $this->getType(), $input["users_id"]);

        if (!isset($input['date'])) {
            $input["date"] = $_SESSION["glpi_currenttime"];
        }
        return $input;
    }


    public function prepareInputForUpdate($input)
    {
        if (!isset($this->fields['itemtype'])) {
            return false;
        }
        $input["_job"] = new $this->fields['itemtype']();
        if (!$input["_job"]->getFromDB($this->fields["items_id"])) {
            return false;
        }

        // update last editor if content change
        if (
            ($uid = Session::getLoginUserID())
            && isset($input['content']) && ($input['content'] != $this->fields['content'])
        ) {
            $input["users_id_editor"] = $uid;
        }

        return $input;
    }


    public function post_updateItem($history = 1)
    {
        global $CFG_GLPI;

        $job      = new $this->fields['itemtype']();

        if (!$job->getFromDB($this->fields['items_id'])) {
            return;
        }

        // Add screenshots if needed, without notification
        $this->input = $this->addFiles($this->input, [
           'force_update' => true,
           'name'          => 'content',
           'content_field' => 'content',
        ]);

        // Add documents if needed, without notification
        $this->input = $this->addFiles($this->input, [
           'force_update' => true,
        ]);

        //Get user_id when not logged (from mailgate)
        $uid = Session::getLoginUserID();
        if ($uid === false) {
            if (isset($this->fields['users_id_editor'])) {
                $uid = $this->fields['users_id_editor'];
            } else {
                $uid = $this->fields['users_id'];
            }
        }
        $job->updateDateMod($this->fields['items_id'], false, $uid);

        if (count($this->updates)) {
            if (
                !isset($this->input['_disablenotif'])
                && $CFG_GLPI["use_notifications"]
                && (in_array("content", $this->updates)
                    || isset($this->input['_need_send_mail']))
            ) {
                //FIXME: _need_send_mail does not seems to be used

                $options = ['followup_id' => $this->fields["id"],
                                 'is_private'  => $this->fields['is_private']];

                NotificationEvent::raiseEvent("update_followup", $job, $options);
            }
        }

        // change ITIL Object status (from splitted button)
        if (
            isset($this->input['_status'])
            && ($this->input['_status'] != $this->input['_job']->fields['status'])
        ) {
            $update = [
               'status'        => $this->input['_status'],
               'id'            => $this->input['_job']->fields['id'],
               '_disablenotif' => true,
            ];
            $this->input['_job']->update($update);
        }

        // Add log entry in the ITIL Object
        $changes = [
           0,
           '',
           $this->fields['id'],
        ];
        Log::history(
            $this->getField('items_id'),
            $this->fields['itemtype'],
            $changes,
            $this->getType(),
            Log::HISTORY_UPDATE_SUBITEM
        );
    }


    public function post_getFromDB()
    {

        $this->item = new $this->fields['itemtype']();
        $this->item->getFromDB($this->fields['items_id']);
    }


    protected function computeFriendlyName()
    {

        if (isset($this->fields['requesttypes_id'])) {
            if ($this->fields['requesttypes_id']) {
                return Dropdown::getDropdownName('glpi_requesttypes', $this->fields['requesttypes_id']);
            }
            return $this->getTypeName();
        }
        return '';
    }


    public function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
           'id'                 => 'common',
           'name'               => __('Characteristics')
        ];

        $tab[] = [
           'id'                 => '1',
           'table'              => $this->getTable(),
           'field'              => 'content',
           'name'               => __('Description'),
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '2',
           'table'              => 'glpi_requesttypes',
           'field'              => 'name',
           'name'               => RequestType::getTypeName(1),
           'forcegroupby'       => true,
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '3',
           'table'              => $this->getTable(),
           'field'              => 'date',
           'name'               => _n('Date', 'Dates', 1),
           'datatype'           => 'datetime'
        ];

        $tab[] = [
           'id'                 => '4',
           'table'              => $this->getTable(),
           'field'              => 'is_private',
           'name'               => __('Private'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '5',
           'table'              => 'glpi_users',
           'field'              => 'name',
           'name'               => User::getTypeName(1),
           'datatype'           => 'dropdown',
           'right'              => 'all'
        ];

        $tab[] = [
           'id'                 => '6',
           'table'              => $this->getTable(),
           'field'              => 'itemtype',
           'name'               => RequestType::getTypeName(1),
           'datatype'           => 'dropdown'
        ];

        return $tab;
    }


    public static function rawSearchOptionsToAdd($itemtype = null)
    {

        $tab = [];

        $tab[] = [
           'id'                 => 'followup',
           'name'               => _n('Followup', 'Followups', Session::getPluralNumber())
        ];

        $followup_condition = '';
        if (!Session::haveRight('followup', self::SEEPRIVATE)) {
            $followup_condition = "AND (`NEWTABLE`.`is_private` = 0
                                     OR `NEWTABLE`.`users_id` = '" . Session::getLoginUserID() . "')";
        }

        $tab[] = [
           'id'                 => '25',
           'table'              => static::getTable(),
           'field'              => 'content',
           'name'               => __('Description'),
           'forcegroupby'       => true,
           'splititems'         => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'itemtype_item',
              'condition'          => $followup_condition
           ],
           'datatype'           => 'text',
           'htmltext'           => true
        ];

        $tab[] = [
           'id'                 => '36',
           'table'              => static::getTable(),
           'field'              => 'date',
           'name'               => _n('Date', 'Dates', 1),
           'datatype'           => 'datetime',
           'massiveaction'      => false,
           'forcegroupby'       => true,
           'joinparams'         => [
              'jointype'           => 'itemtype_item',
              'condition'          => $followup_condition
           ]
        ];

        $tab[] = [
           'id'                 => '27',
           'table'              => static::getTable(),
           'field'              => 'id',
           'name'               => _x('quantity', 'Number of followups'),
           'forcegroupby'       => true,
           'usehaving'          => true,
           'datatype'           => 'count',
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'itemtype_item',
              'condition'          => $followup_condition
           ]
        ];

        $tab[] = [
           'id'                 => '29',
           'table'              => 'glpi_requesttypes',
           'field'              => 'name',
           'name'               => RequestType::getTypeName(1),
           'datatype'           => 'dropdown',
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => static::getTable(),
                 'joinparams'         => [
                    'jointype'           => 'itemtype_item',
                    'condition'          => $followup_condition
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '91',
           'table'              => static::getTable(),
           'field'              => 'is_private',
           'name'               => __('Private followup'),
           'datatype'           => 'bool',
           'forcegroupby'       => true,
           'splititems'         => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'itemtype_item',
              'condition'          => $followup_condition
           ]
        ];

        $tab[] = [
           'id'                 => '93',
           'table'              => 'glpi_users',
           'field'              => 'name',
           'name'               => __('Writer'),
           'datatype'           => 'itemlink',
           'right'              => 'all',
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => static::getTable(),
                 'joinparams'         => [
                    'jointype'           => 'itemtype_item',
                    'condition'          => $followup_condition
                 ]
              ]
           ]
        ];

        return $tab;
    }


    /**
     * form for soluce's approbation
     *
     * @param CommonITILObject $itilobject
     */
    public function showApprobationForm($itilobject)
    {
        $form = null;

        if (
            ($itilobject->fields["status"] == CommonITILObject::SOLVED)
            && $itilobject->canApprove()
            && $itilobject->isAllowedStatus($itilobject->fields['status'], CommonITILObject::CLOSED)
        ) {
            $form = [
               'action' => $this->getFormURL(),
               'buttons' => [
                  [
                     'type' => 'submit',
                     'name' => 'add_reopen',
                     'value' => __('Refuse the solution'),
                     'class' => 'submit-button btn btn-secondary',
                  ],
                  [
                     'type' => 'submit',
                     'name' => 'add_close',
                     'value' => __('Approve the solution'),
                     'class' => 'submit-button btn btn-secondary',
                  ],
               ],
               'content' => [
                  __('Approbation de la solution') => [
                     'visible' => true,
                     'inputs' => [
                        __('itemtype') => [
                           'name' => 'itemtype',
                           'type' => 'hidden',
                           'value' => $itilobject->getType(),
                        ],
                        __('items_id') => [
                           'name' => 'items_id',
                           'type' => 'hidden',
                           'value' => $itilobject->getField('id'),
                        ],
                        __('requesttypes_id') => [
                           'name' => 'requesttypes_id',
                           'type' => 'hidden',
                           'value' => RequestType::getDefault('followup'),
                        ],
                        __('Comments') => [
                           'name' => 'content',
                           'type' => 'textarea',
                        ],
                     ]
                  ]
               ]
            ];
        }

        if ($form === null) {
            return true;
        }

        renderTwigForm($form);
        return true;
    }


    public static function getFormURL($full = true)
    {
        return Toolbox::getItemTypeFormURL("ITILFollowup", $full);
    }


    /** form for Followup
     *
     *@param $ID      integer : Id of the followup
     *@param $options array of possible options:
     *     - item Object : the ITILObject parent
    **/
    public function showForm($ID, $options = [])
    {
        global $CFG_GLPI;

        if ($this->isNewItem()) {
            $this->getEmpty();
        }

        if (!isset($options['item']) && isset($options['parent'])) {
            //when we came from aja/viewsubitem.php
            $options['item'] = $options['parent'];
        }
        $options['formoptions'] = ($options['formoptions'] ?? '') . ' data-track-changes=true';

        $item = $options['item'];
        $this->item = $item;

        if ($ID > 0) {
            $this->check($ID, READ);
        } else {
            // Create item
            $options['itemtype'] = $item->getType();
            $options['items_id'] = $item->getField('id');
            $this->check(-1, CREATE, $options);
        }
        $tech = (Session::haveRight(self::$rightname, self::ADDALLTICKET)
                 || $item->isUser(CommonITILActor::ASSIGN, Session::getLoginUserID())
                 || (isset($_SESSION["glpigroups"])
                     && $item->haveAGroup(CommonITILActor::ASSIGN, $_SESSION['glpigroups'])));

        $requester = ($item->isUser(CommonITILActor::REQUESTER, Session::getLoginUserID())
                      || (isset($_SESSION["glpigroups"])
                          && $item->haveAGroup(CommonITILActor::REQUESTER, $_SESSION['glpigroups'])));

        $reopen_case = false;
        if ($this->isNewID($ID)) {
            if ($item->canReopen()) {
                $reopen_case = true;
                echo "<div class='center b'>" . __('If you want to reopen the ticket, you must specify a reason') . "</div>";
            }

            // the reqester triggers the reopening on close/solve/waiting status
            if (
                $requester
                && in_array($item->fields['status'], $item::getReopenableStatusArray())
            ) {
                $reopen_case = true;
            }
        }

        $cols    = 100;
        $rows    = 10;

        if ($tech) {
            $form = [
               'action' => $this->getFormURL(),
               'itemtype' => $this::class,
               'content' => [
                  $this->getTypeName() => [
                     'visible' => true,
                     'inputs' => [
                        $this->isNewID($ID) ? [
                           'type' => 'hidden',
                           'name' => 'id',
                           'value' => $ID,
                        ] : [],
                        [
                           'type' => 'hidden',
                           'name' => 'itemtype',
                           'value' => $item->getType(),
                        ],
                        [
                           'type' => 'hidden',
                           'name' => 'items_id',
                           'value' => $item->getID(),
                        ],
                        $reopen_case ? [
                           'type' => 'hidden',
                           'name' => 'add_reopen',
                           'value' => 1,
                        ] : [],
                        '' => [
                           'type' => 'richtextarea',
                           'name' => 'content',
                           'id' => 'TextareaForContentFolloupPopup',
                           'value' => $this->fields["content"],
                           'col_lg' => 12,
                           'col_md' => 12,
                        ],
                        _n('Date', 'Dates', 1) => ($this->fields["date"]) ? [
                           'content' => Html::convDateTime($this->fields["date"]),
                        ] : [],
                        ITILFollowupTemplate::getTypeName() => [
                           'type' => 'select',
                           'name' => 'itilfollowuptemplates_id',
                           'id' => 'ITILFollowupTemplateDropdown',
                           'values' => getOptionForItems(ITILFollowupTemplate::class),
                           'actions' => getItemActionButtons(['info', 'add'], ITILFollowupTemplate::class),
                           'hooks' => [
                              'change' => <<<JS
                                 $.ajax({
                                    url: "{$CFG_GLPI["root_doc"]}/ajax/itilfollowup.php",
                                    type: 'POST',
                                    data: {
                                       itilfollowuptemplates_id: $(this).val()
                                    }
                                 }).done(function(data) {
                                    var requesttypes_id = isNaN(parseInt(data.requesttypes_id))
                                       ? 0
                                       : parseInt(data.requesttypes_id);

                                    TextareaForContentFolloupPopup.setData(data.content)
                                    $("#dropdownForRequestType").val(requesttypes_id).trigger('change');
                                    $("#is_privateswitch")
                                       .prop("checked", data.is_private == "0" ? false : true);
                                 });
                           JS,
                           ],
                        ],
                        __('Source of followup') => [
                           'type' => 'select',
                           'id' => 'dropdownForRequestType',
                           'name' => 'requesttypes_id',
                           'noLib' => true,
                           'value' => $this->fields["requesttypes_id"],
                           'values' => getOptionForItems(RequestType::class, ['is_active' => 1, 'is_itilfollowup' => 1]),
                           'actions' => getItemActionButtons(['info', 'add'], RequestType::class),
                        ],
                        __('Private') => [
                           'type' => 'checkbox',
                           'id' => 'is_privateswitch',
                           'name' => 'is_private',
                           'value' => $this->fields["is_private"]
                        ],
                        sprintf(__('%1$s (%2$s)'), __('File'), Document::getMaxUploadSize()) => [
                           'type' => 'file',
                           'name' => 'files',
                           'id' => 'fileSelectorForDocument',
                           'multiple' => true,
                           'data-max-size' => Document::getMaxUploadSizeInBytes(),
                           'values' => getLinkedDocumentsForItem('Ticket', $ID),
                           'col_lg' => 12,
                           'col_md' => 12,
                        ],
                        __('Status') => [
                           'type' => 'select',
                           'noLib' => 'true',
                           'name' => '_status',
                           'values' => $item->getAllowedStatusArray($item->fields['status']),
                           'value' => $item->getField('status'),
                           'required' => true,
                        ],
                     ]
                  ]
               ]
            ];
            renderTwigForm($form, '', $this->fields);
        } else {
            $options['colspan'] = 1;

            $this->showFormHeader($options);

            $rand = mt_rand();
            $rand_text = mt_rand();
            $content_id = "content$rand";
            echo "<tr class='tab_bg_1'>";
            echo "<td class='middle right'>" . __('Description') . "</td>";
            echo "<td class='center middle'>";

            Html::textarea(['name'              => 'content',
                            'value'             => $this->fields["content"],
                            'rand'              => $rand_text,
                            'editor_id'         => $content_id,
                            'enable_fileupload' => true,
                            'enable_richtext'   => true,
                            'cols'              => $cols,
                            'rows'              => $rows]);

            echo Html::hidden('itemtype', ['value' => $item->getType()]);
            echo Html::hidden('items_id', ['value' => $item->getID()]);
            echo Html::hidden('requesttypes_id', ['value' => RequestType::getDefault('followup')]);
            // Reopen case
            if ($reopen_case) {
                echo "<input type='hidden' name='add_reopen' value='1'>";
            }

            echo "</td></tr>\n";

            $this->showFormButtons($options);
        }
        return true;
    }


    public function showFormButtons($options = [])
    {

        // for single object like config
        $ID = 1;
        if (isset($this->fields['id'])) {
            $ID = $this->fields['id'];
        }

        $params = [
           'colspan'  => 2,
           'candel'   => true,
           'canedit'  => true,
        ];

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
            echo $params['item']::getSplittedSubmitButtonHtml($this->fields['items_id'], 'add');
        } else {
            if (
                $params['candel']
                && !$this->can($ID, DELETE)
                && !$this->can($ID, PURGE)
            ) {
                $params['candel'] = false;
            }

            if ($params['canedit'] && $this->can($ID, UPDATE)) {
                echo $params['item']::getSplittedSubmitButtonHtml($this->fields['items_id'], 'update');
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
     * @param $ID  integer  ID of the ITILObject
     * @param $itemtype  string   parent itemtype
     *
     * @deprecated 9.5.6
    **/
    public static function showShortForITILObject($ID, $itemtype)
    {
        Toolbox::deprecated();

        global $DB, $CFG_GLPI;

        // Print Followups for a job
        $showprivate = Session::haveRight(self::$rightname, self::SEEPRIVATE);

        $where = [
           'itemtype'  => $itemtype,
           'items_id'  => $ID
        ];
        if (!$showprivate) {
            $where['OR'] = [
               'is_private'   => 0,
               'users_id'     => Session::getLoginUserID()
            ];
        }

        // Get Followups
        $iterator = $DB->request([
           'FROM'   => 'glpi_itilfollowups',
           'WHERE'  => $where,
           'ORDER'  => 'date DESC'
        ]);

        $out = "";
        if (count($iterator)) {
            $out .= "<div class='center' aria-label='ITIL Objects Information'><table class='tab_cadre' width='100%'>\n
                  <tr><th>" . _n('Date', 'Dates', 1) . "</th><th>" . _n('Requester', 'Requesters', 1) . "</th>
                  <th>" . __('Description') . "</th></tr>\n";

            $showuserlink = 0;
            if (Session::haveRight('user', READ)) {
                $showuserlink = 1;
            }
            while ($data = $iterator->next()) {
                $out .= "<tr class='tab_bg_3'>
                     <td class='center'>" . Html::convDateTime($data["date"]) . "</td>
                     <td class='center'>" . getUserName($data["users_id"], $showuserlink) . "</td>
                     <td width='70%' class='b'>" . Html::resume_text(
                    $data["content"],
                    $CFG_GLPI["cut"]
                ) . "
                     </td></tr>";
            }
            $out .= "</table></div>";
        }
        return $out;
    }


    public function getRights($interface = 'central')
    {

        $values = parent::getRights();
        unset($values[UPDATE], $values[CREATE], $values[READ]);

        if ($interface == 'central') {
            $values[self::UPDATEALL]      = __('Update all');
            $values[self::ADDALLTICKET]   = __('Add to all tickets');
            $values[self::SEEPRIVATE]     = __('See private ones');
        }

        $values[self::ADDGROUPTICKET]
                                   = ['short' => __('Add followup (associated groups)'),
                                           'long'  => __('Add a followup to tickets of associated groups')];
        $values[self::UPDATEMY]    = __('Update followups (author)');
        $values[self::ADDMYTICKET] = ['short' => __('Add followup (requester)'),
                                           'long'  => __('Add a followup to tickets (requester)')];
        $values[self::SEEPUBLIC]   = __('See public ones');

        if ($interface == 'helpdesk') {
            unset($values[PURGE]);
        }

        return $values;
    }

    public static function showMassiveActionAddFollowupForm()
    {
        $inputs = [
           __('Source of followup') => [
              'type' => 'select',
              'name' => 'requesttypes_id',
              'values' => getOptionForItems(RequestType::class, ['is_active' => 1, 'is_itilfollowup' => 1]),
              'col_lg' => 12,
              'col_md' => 12,
           ],
           __('Description') => [
              'type' => 'textarea',
              'name' => 'content',
              'rows' => 6,
              'col_lg' => 12,
              'col_md' => 12,
           ],
           [
              'type' => 'hidden',
              'name' => 'is_private',
              'value' => $_SESSION['glpifollowup_private']
           ]
        ];
        echo "<div class='center row'>";
        foreach ($inputs as $title => $input) {
            renderTwigTemplate('macros/wrappedInput.twig', [
               'title' => $title,
               'input' => $input,
            ]);
        };
        echo '</div>';
        echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='btn btn-secondary mt-3'>";
    }

    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {

        switch ($ma->getAction()) {
            case 'add_followup':
                static::showMassiveActionAddFollowupForm();
                return true;
        }

        return parent::showMassiveActionsSubForm($ma);
    }

    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {
        switch ($ma->getAction()) {
            case 'add_followup':
                $input = $ma->getInput();
                $fup   = new self();
                foreach ($ids as $id) {
                    if ($item->getFromDB($id)) {
                        if (in_array($item->fields['status'], $item->getClosedStatusArray())) {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                            $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
                        } else {
                            $input2 = [
                               'items_id'        => $id,
                               'itemtype'        => $item->getType(),
                               'is_private'      => $input['is_private'],
                               'requesttypes_id' => $input['requesttypes_id'],
                               'content'         => $input['content']
                            ];
                            if ($fup->can(-1, CREATE, $input2)) {
                                if ($fup->add($input2)) {
                                    $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                                } else {
                                    $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                                    $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                                }
                            } else {
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_NORIGHT);
                                $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
                            }
                        }
                    } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        $ma->addMessage($item->getErrorMessage(ERROR_NOT_FOUND));
                    }
                }
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    /**
     * Build parent condition for ITILFollowup, used in addDefaultWhere
     *
     * @param string $itemtype
     * @param string $target
     * @param string $user_table
     * @param string $group_table keys
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public static function buildParentCondition(
        $itemtype,
        $target = "",
        $user_table = "",
        $group_table = ""
    ) {
        $itilfup_table = static::getTable();

        // An ITILFollowup parent can only by a CommonItilObject
        if (!is_a($itemtype, "CommonITILObject", true)) {
            throw new InvalidArgumentException(
                "'$itemtype' is not a CommonITILObject"
            );
        }

        $rightname = $itemtype::$rightname;
        // Can see all items, no need to go further
        if (Session::haveRight($rightname, $itemtype::READALL)) {
            return "(`$itilfup_table`.`itemtype` = '$itemtype') ";
        }

        $user   = Session::getLoginUserID();
        $groups = "'" . implode("','", $_SESSION['glpigroups']) . "'";
        $table = getTableNameForForeignKeyField(
            getForeignKeyFieldForItemType($itemtype)
        );

        // Avoid empty IN ()
        if ($groups == "''") {
            $groups = '-1';
        }

        // We need to do some specific checks for tickets
        if ($itemtype == "Ticket") {
            // Default condition
            $condition = "(`itemtype` = '$itemtype' AND (0 = 1 ";
            return $condition . Ticket::buildCanViewCondition("items_id") . ")) ";
        } else {
            if (Session::haveRight($rightname, $itemtype::READMY)) {
                // Subquery for affected/assigned/observer user
                $user_query = "SELECT `$target`
               FROM `$user_table`
               WHERE `users_id` = '$user'";

                // Subquery for affected/assigned/observer group
                $group_query = "SELECT `$target`
               FROM `$group_table`
               WHERE `groups_id` IN ($groups)";

                // Subquery for recipient
                $recipient_query = "SELECT `id`
               FROM `$table`
               WHERE `users_id_recipient` = '$user'";

                return "(
               `$itilfup_table`.`itemtype` = '$itemtype' AND (
                  `$itilfup_table`.`items_id` IN ($user_query) OR
                  `$itilfup_table`.`items_id` IN ($group_query) OR
                  `$itilfup_table`.`items_id` IN ($recipient_query)
               )
            ) ";
            } else {
                // Can't see any items
                return "(`$itilfup_table`.`itemtype` = '$itemtype' AND 0 = 1) ";
            }
        }
    }

    public static function getNameField()
    {
        return 'id';
    }

    /**
     * Check if this item author is a support agent
     *
     * @return bool
     */
    public function isFromSupportAgent()
    {
        global $DB;

        // Get parent item
        $commonITILObject = new $this->fields['itemtype']();
        $commonITILObject->getFromDB($this->fields['items_id']);

        $actors = $commonITILObject->getITILActors();
        $user_id = $this->fields['users_id'];
        $roles = $actors[$user_id] ?? [];

        if (in_array(CommonITILActor::ASSIGN, $roles)) {
            // The author is assigned -> support agent
            return true;
        } elseif (in_array(CommonITILActor::OBSERVER, $roles)) {
            // The author is an observer or a requester -> can be support agent OR
            // requester depending on how GLPI is used so we must check the user's
            // profiles
            $central_profiles = $DB->request([
               'COUNT' => 'total',
               'FROM' => Profile::getTable(),
               'WHERE' => [
                  'interface' => 'central',
                  'id' => new QuerySubQuery([
                     'SELECT' => ['profiles_id'],
                     'FROM' => Profile_User::getTable(),
                     'WHERE' => [
                        'users_id' => $user_id
                     ]
                  ])
               ]
            ]);

            // No profiles, let's assume it is a support agent to be safe
            if (!count($central_profiles)) {
                return false;
            }

            return $central_profiles->next()['total'] > 0;
        } elseif (in_array(CommonITILActor::REQUESTER, $roles)) {
            // The author is a requester -> not from support agent
            return false;
        } else {
            // The author is not an actor of the ticket -> he was most likely a
            // support agent that is no longer assigned to the ticket
            return true;
        }
    }
}
