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

use Glpi\CalDAV\Contracts\CalDAVCompatibleItemInterface;
use Glpi\CalDAV\Traits\VobjectConverterTrait;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VJournal;
use Sabre\VObject\Component\VTodo;

/**
 * Reminder Class
**/
class Reminder extends CommonDBVisible implements
   CalDAVCompatibleItemInterface,
   ExtraVisibilityCriteria
{
   use Glpi\Features\PlanningEvent {
      post_getEmpty as trait_post_getEmpty;
   }
   use VobjectConverterTrait;

   // From CommonDBTM
   public $dohistory                   = true;
   public $can_be_translated           = true;
   // For visibility checks
   protected $users     = [];
   protected $groups    = [];
   protected $profiles  = [];
   protected $entities  = [];

   static $rightname    = 'reminder_public';



   static function getTypeName($nb = 0) {

      if (Session::haveRight('reminder_public', READ)) {
         return _n('Reminder', 'Reminders', $nb);
      }
      return _n('Personal reminder', 'Personal reminders', $nb);
   }


   static function canCreate() {

      return (Session::haveRight(self::$rightname, CREATE)
              || Session::getCurrentInterface() != 'helpdesk');
   }


   static function canView() {

      return (Session::haveRight(self::$rightname, READ)
              || Session::getCurrentInterface() != 'helpdesk');
   }


   function canViewItem() {

      // Is my reminder or is in visibility
      return ($this->fields['users_id'] == Session::getLoginUserID()
              || (Session::haveRight(self::$rightname, READ)
                  && $this->haveVisibilityAccess()));
   }


   function canCreateItem() {
      // Is my reminder
      return ($this->fields['users_id'] == Session::getLoginUserID());
   }


   function canUpdateItem() {

      return ($this->fields['users_id'] == Session::getLoginUserID()
              || (Session::haveRight(self::$rightname, UPDATE)
                  && $this->haveVisibilityAccess()));
   }


   /**
    * @since 0.85
    *
    * @see CommonDBTM::canPurgeItem()
   **/
   function canPurgeItem() {

      return ($this->fields['users_id'] == Session::getLoginUserID()
              || (Session::haveRight(self::$rightname, PURGE)
                  && $this->haveVisibilityAccess()));
   }


   /**
    * @since 0.85
    * for personnal reminder
   **/
   static function canUpdate() {
      return (Session::getCurrentInterface() != 'helpdesk');
   }


   /**
    * @since 0.85
    * for personnal reminder
   **/
   static function canPurge() {
      return (Session::getCurrentInterface() != 'helpdesk');
   }


   function post_getFromDB() {

      // Users
      $this->users    = Reminder_User::getUsers($this->fields['id']);

      // Entities
      $this->entities = Entity_Reminder::getEntities($this);

      // Group / entities
      $this->groups   = Group_Reminder::getGroups($this->fields['id']);

      // Profile / entities
      $this->profiles = Profile_Reminder::getProfiles($this->fields['id']);
   }


   /**
    * @see CommonDBTM::cleanDBonPurge()
    *
    * @since 0.83.1
   **/
   function cleanDBonPurge() {

      $this->deleteChildrenAndRelationsFromDb(
         [
            Entity_Reminder::class,
            Group_Reminder::class,
            PlanningRecall::class,
            Profile_Reminder::class,
            Reminder_User::class,
            VObject::class,
            ReminderTranslation::class,
         ]
      );
   }

   public function haveVisibilityAccess() {
      if (!self::canView()) {
         return false;
      }

      return parent::haveVisibilityAccess();
   }

   /**
    * Return visibility joins to add to SQL
    *
    * @param $forceall force all joins (false by default)
    *
    * @return string joins to add
   **/
   static function addVisibilityJoins($forceall = false) {
      //not deprecated because used in Search
      global $DB;

      //get and clean criteria
      $criteria = self::getVisibilityCriteria();
      unset($criteria['WHERE']);
      $criteria['FROM'] = self::getTable();

      $it = new \DBmysqlIterator(null);
      $it->buildQuery($criteria);
      $sql = $it->getSql();
      $sql = trim(str_replace(
         'SELECT * FROM '.$DB->quoteName(self::getTable()),
         '',
         $sql
      ));
      return $sql;
   }

   /**
    * Return visibility SQL restriction to add
    *
    * @return string restrict to add
   **/
   static function addVisibilityRestrict() {
      //not deprecated because used in Search

      //get and clean criteria
      $criteria = self::getVisibilityCriteria();
      unset($criteria['LEFT JOIN']);
      $criteria['FROM'] = self::getTable();

      $it = new \DBmysqlIterator(null);
      $it->buildQuery($criteria);
      $sql = $it->getSql();
      $sql = preg_replace('/.*WHERE /', '', $sql);

      return $sql;
   }

   /**
    * Return visibility joins to add to DBIterator parameters
    *
    * @since 9.4
    *
    * @param boolean $forceall force all joins (false by default)
    *
    * @return array
    */
   static public function getVisibilityCriteria(bool $forceall = false): array {
      if (!Session::haveRight(self::$rightname, READ)) {
         return [
            'WHERE' => ['glpi_reminders.users_id' => Session::getLoginUserID()],
         ];
      }

      $join = [];
      $where = [];

      // Users
      $join['glpi_reminders_users'] = [
         'FKEY' => [
            'glpi_reminders_users'  => 'reminders_id',
            'glpi_reminders'        => 'id'
         ]
      ];

      if (Session::getLoginUserID()) {
         $where['OR'] = [
               'glpi_reminders.users_id'        => Session::getLoginUserID(),
               'glpi_reminders_users.users_id'  => Session::getLoginUserID(),
         ];
      } else {
         $where = [
            0
         ];
      }

      // Groups
      if ($forceall
          || (isset($_SESSION["glpigroups"]) && count($_SESSION["glpigroups"]))) {
         $join['glpi_groups_reminders'] = [
            'FKEY' => [
               'glpi_groups_reminders' => 'reminders_id',
               'glpi_reminders'        => 'id'
            ]
         ];

         $or = ['glpi_groups_reminders.entities_id' => ['<', 0]];
         $restrict = getEntitiesRestrictCriteria('glpi_groups_reminders', '', '', true);
         if (count($restrict)) {
            $or = $or + $restrict;
         }
         $where['OR'][] = [
            'glpi_groups_reminders.groups_id' => count($_SESSION["glpigroups"])
                                                      ? $_SESSION["glpigroups"]
                                                      : [-1],
            'OR' => $or
         ];
      }

      // Profiles
      if ($forceall
          || (isset($_SESSION["glpiactiveprofile"])
              && isset($_SESSION["glpiactiveprofile"]['id']))) {
         $join['glpi_profiles_reminders'] = [
            'FKEY' => [
               'glpi_profiles_reminders'  => 'reminders_id',
               'glpi_reminders'           => 'id'
            ]
         ];

         $or = ['glpi_profiles_reminders.entities_id' => ['<', 0]];
         $restrict = getEntitiesRestrictCriteria('glpi_profiles_reminders', '', '', true);
         if (count($restrict)) {
            $or = $or + $restrict;
         }
         $where['OR'][] = [
            'glpi_profiles_reminders.profiles_id' => $_SESSION["glpiactiveprofile"]['id'],
            'OR' => $or
         ];
      }

      // Entities
      if ($forceall
          || (isset($_SESSION["glpiactiveentities"]) && count($_SESSION["glpiactiveentities"]))) {
         $join['glpi_entities_reminders'] = [
            'FKEY' => [
               'glpi_entities_reminders'  => 'reminders_id',
               'glpi_reminders'           => 'id'
            ]
         ];
      }
      if (isset($_SESSION["glpiactiveentities"]) && count($_SESSION["glpiactiveentities"])) {
         $restrict = getEntitiesRestrictCriteria('glpi_entities_reminders', '', '', true, true);
         if (count($restrict)) {
            $where['OR'] = $where['OR'] + $restrict;
         }
      }

      $criteria = [
         'LEFT JOIN' => $join,
         'WHERE'     => $where
      ];

      return $criteria;
   }


   function rawSearchOptions() {
      $tab = [];

      $tab[] = [
         'id'                 => 'common',
         'name'               => __('Characteristics')
      ];

      $tab[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __('Title'),
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
         'forcegroupby'       => true,
         'autocomplete'       => true,
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => 'glpi_users',
         'field'              => 'name',
         'name'               => __('Writer'),
         'datatype'           => 'dropdown',
         'massiveaction'      => false,
         'right'              => 'all'
      ];

      $tab[] = [
         'id'                 => '3',
         'table'              => $this->getTable(),
         'field'              => 'state',
         'name'               => __('Status'),
         'datatype'           => 'specific',
         'massiveaction'      => false,
         'searchtype'         => ['equals', 'notequals']
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'text',
         'name'               => __('Description'),
         'massiveaction'      => false,
         'datatype'           => 'text',
         'htmltext'           => true
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => $this->getTable(),
         'field'              => 'begin_view_date',
         'name'               => __('Visibility start date'),
         'datatype'           => 'datetime'
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => $this->getTable(),
         'field'              => 'end_view_date',
         'name'               => __('Visibility end date'),
         'datatype'           => 'datetime'
      ];

      $tab[] = [
         'id'                 => '7',
         'table'              => $this->getTable(),
         'field'              => 'is_planned',
         'name'               => __('Planning'),
         'datatype'           => 'bool',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '8',
         'table'              => $this->getTable(),
         'field'              => 'begin',
         'name'               => __('Planning start date'),
         'datatype'           => 'datetime'
      ];

      $tab[] = [
         'id'                 => '9',
         'table'              => $this->getTable(),
         'field'              => 'end',
         'name'               => __('Planning end date'),
         'datatype'           => 'datetime'
      ];

      $tab[] = [
         'id'                 => '19',
         'table'              => $this->getTable(),
         'field'              => 'date_mod',
         'name'               => __('Last update'),
         'datatype'           => 'datetime',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '121',
         'table'              => $this->getTable(),
         'field'              => 'date_creation',
         'name'               => __('Creation date'),
         'datatype'           => 'datetime',
         'massiveaction'      => false
      ];

      // add objectlock search options
      $tab = array_merge($tab, ObjectLock::rawSearchOptionsToAdd(get_class($this)));

      return $tab;
   }


   /**
    * @since 0.84
    *
    * @param $field
    * @param $values
    * @param $options   array
   **/
   static function getSpecificValueToDisplay($field, $values, array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'state':
            return Planning::getState($values[$field]);
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }


   /**
    * @since 0.84
    *
    * @param $field
    * @param $name               (default '')
    * @param $values             (default '')
    * @param $options      array
    **/
   static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;

      switch ($field) {
         case 'state' :
            return Planning::dropdownState($name, $values[$field], false);
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }


   /**
    * @see CommonGLPI::getTabNameForItem()
   **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (self::canView()) {
         $nb = 0;
         switch ($item->getType()) {
            case 'Reminder' :
               if (Session::haveRight('reminder_public', CREATE)) {
                  if ($_SESSION['glpishow_count_on_tabs']) {
                     $nb = $item->countVisibilities();
                  }
                  return [1 => self::createTabEntry(_n('Target', 'Targets',
                                                            Session::getPluralNumber()), $nb)];
               }
         }
      }
      return '';
   }


   /**
    * @see CommonGLPI::defineTabs()
   **/
   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Reminder', $ong, $options);
      $this->addStandardTab('ReminderTranslation', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }


   /**
    * @param $item         CommonGLPI object
    * @param $tabnum       (default 1)
    * @param $withtemplate (default 0)
   **/
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      switch ($item->getType()) {
         case 'Reminder' :
            $item->showVisibility();
            return true;
      }
      return false;
   }


   function post_getEmpty() {
      $this->fields["name"]        = __('New note');

      $this->trait_post_getEmpty();
   }


   /**
    * Print the reminder form
    *
    * @param $ID        integer  Id of the item to print
    * @param $options   array of possible options:
    *     - target filename : where to go when done.
    *     - from_planning_ajax : set to disable planning form part
    **/
   function showForm($ID, $options = []) {
      global $CFG_GLPI;

      // $this->initForm($ID, $options);
      // $rand = mt_rand();

      // // Show Reminder or blank form
      // $onfocus = "";
      // if (!$ID > 0) {
      //    // Create item : do getempty before check right to set default values
      //    $onfocus="onfocus=\"if (this.value=='".$this->fields['name']."') this.value='';\"";
      // }

      // $canedit = $this->can($ID, UPDATE);

      // $this->showFormHeader($options);

      // echo "<tr class='tab_bg_2'><td>".__('Title')."</td>";
      // echo "<td colspan='2'>";
      // if (!$ID) {
      //    echo "<input type='hidden' name='users_id' value='".$this->fields['users_id']."'>\n";
      // }
      // if ($canedit) {
      //    Html::autocompletionTextField($this, "name",
      //                                  ['size'   => '80',
      //                                        'entity' => -1,
      //                                        'user'   => $this->fields["users_id"],
      //                                        'option' => $onfocus]);
      // } else {
      //    echo $this->fields['name'];
      // }
      // if (isset($options['from_planning_edit_ajax']) && $options['from_planning_edit_ajax']) {
      //    echo Html::hidden('from_planning_edit_ajax');
      // }
      // echo "</td>";
      // echo "</tr>";

      // if (!isset($options['from_planning_ajax'])) {
      //    echo "<tr class='tab_bg_2'>";
      //    echo "<td>".__('Visibility')."</td>";
      //    echo "<td colspan='2'>";
      //    echo '<table><tr><td>';
      //    echo __('Begin').'</td><td>';
      //    Html::showDateTimeField("begin_view_date",
      //                            ['value'      => $this->fields["begin_view_date"],
      //                                  'maybeempty' => true,
      //                                  'canedit'    => $canedit]);
      //    echo '</td><td>'.__('End').'</td><td>';
      //    Html::showDateTimeField("end_view_date",
      //                            ['value'      => $this->fields["end_view_date"],
      //                                  'maybeempty' => true,
      //                                  'canedit'    => $canedit]);
      //    echo '</td></tr></table>';
      //    echo "</td>";
      //    echo "</tr>";
      // }

      // echo "<tr class='tab_bg_2'>";
      // echo "<td>".__('Status')."</td>";
      // echo "<td colspan='2'>";
      // if ($canedit) {
      //    Planning::dropdownState("state", $this->fields["state"]);
      // } else {
      //    echo Planning::getState($this->fields["state"]);
      // }
      // echo "</td>\n";
      // echo "</tr>\n";

      // echo "<tr class='tab_bg_2'><td>"._n('Calendar', 'Calendars', 1)."</td>";
      // $active_recall = ($ID && $this->fields["is_planned"] && PlanningRecall::isAvailable());

      // echo "<td";
      // if (!$active_recall) {
      //    echo " colspan='2'";
      // }
      // echo ">";
      // if (isset($options['from_planning_ajax'])
      //     && $options['from_planning_ajax']) {
      //    echo Html::hidden('plan[begin]', ['value' => $options['begin']]);
      //    echo Html::hidden('plan[end]', ['value' => $options['end']]);
      //    printf(__('From %1$s to %2$s'), Html::convDateTime($options["begin"]),
      //                                    Html::convDateTime($options["end"]));
      //    echo "</td>";
      // } else {
      //    if ($canedit) {
      //       echo "<script type='text/javascript' >\n";
      //       echo "function showPlan$rand() {\n";
      //       echo Html::jsHide("plan$rand");
      //       $params = ['action'   => 'add_event_classic_form',
      //                  'form'     => 'remind',
      //                  'users_id' => $this->fields["users_id"],
      //                  'itemtype' => $this->getType(),
      //                  'items_id' => $this->getID()];

      //       if ($ID && $this->fields["is_planned"]) {
      //             $params['begin'] = $this->fields["begin"];
      //             $params['end']   = $this->fields["end"];
      //       }

      //       Ajax::updateItemJsCode("viewplan$rand", $CFG_GLPI["root_doc"]."/ajax/planning.php", $params);
      //       echo "}";
      //       echo "</script>\n";
      //    }

      //    if (!$ID
      //        || !$this->fields["is_planned"]) {

      //       if (Session::haveRightsOr("planning", [Planning::READMY, Planning::READGROUP,
      //                                                   Planning::READALL])) {

      //          echo "<div id='plan$rand' onClick='showPlan$rand()'>\n";
      //          echo "<a href='#' class='vsubmit'>".__('Add to schedule')."</a>";
      //       }

      //    } else {
      //       if ($canedit) {
      //          echo "<div id='plan$rand' onClick='showPlan$rand()'>\n";
      //          echo "<span class='showplan'>";
      //       }

      //       //TRANS: %1$s is the begin date, %2$s is the end date
      //       printf(__('From %1$s to %2$s'), Html::convDateTime($this->fields["begin"]),
      //              Html::convDateTime($this->fields["end"]));

      //       if ($canedit) {
      //          echo "</span>";
      //       }
      //    }

      //    if ($canedit) {
      //       echo "</div>\n";
      //       echo "<div id='viewplan$rand'>\n</div>\n";
      //    }
      //    echo "</td>";

      //    if ($active_recall) {
      //       echo "<td><table><tr><td>"._x('Planning', 'Reminder')."</td>";
      //       echo "<td>";
      //       if ($canedit) {
      //          PlanningRecall::dropdown(['itemtype' => 'Reminder',
      //                                         'items_id' => $ID]);
      //       } else { // No edit right : use specific Planning Recall Form
      //          PlanningRecall::specificForm(['itemtype' => 'Reminder',
      //                                             'items_id' => $ID]);
      //       }
      //       echo "</td></tr></table></td>";
      //    }
      // }
      // echo "</tr>\n";

      // echo "<tr class='tab_bg_2'><td>".__('Description')."</td>".
      //      "<td colspan='3'>";

      // if ($canedit) {
      //    Html::textarea(['name'              => 'text',
      //                    'value'             => $this->fields["text"],
      //                    'enable_richtext'   => true,
      //                    'enable_fileupload' => true]);
      // } else {
      //    echo "<div  id='kbanswer'>";
      //    echo Toolbox::unclean_html_cross_side_scripting_deep($this->fields["text"]);
      //    echo "</div>";
      // }

      // echo "</td></tr>\n";

      // $this->showFormButtons($options);

      if(isset($this->fields['begin_view_date']) && isset($this->fields['end_view_date'])){
         preg_match('/^\d{4}-\d{2}-\d{2}/', $this->fields['begin_view_date'], $begin_view_date);
         $begin_view_date = $begin_view_date[0] ?? '';

         preg_match('/^\d{4}-\d{2}-\d{2}/', $this->fields['end_view_date'], $end_view_date);
         $end_view_date = $end_view_date[0] ?? '';

      }

      $form = [
         'action' => Toolbox::getItemTypeFormURL('reminder'),
         'buttons' => [
            [
               'type' => 'submit',
               'name' => 'add',
               'value' => __('Add'),
               'class' => 'submit-button btn btn-warning',
            ],
         ],
         'content' => [
            __('New item') . " - " . __('Note') => [
               'visible' => true,
               'inputs' => [
                  __('Title') => [
                     'name' => 'name',
                     'type' => 'text',
                     'value' => $this->fields['name'],
                  ],
                  __('begin_view_date') => [
                     'name' => 'begin_view_date',
                     'type' => 'date',
                     'value' => $begin_view_date,
                  ],
                  __('end_view_date') => [
                     'name' => 'end_view_date',
                     'type' => 'date',
                     'value' => $end_view_date,
                  ],
                  __('Status') => [
                     'name' => 'state',
                     'type' => 'select',
                     'values' => [
                        '0' => __('Information'),
                        '1' => __('To do'),
                        '2' => __('Done'),
                     ]
                  ],
                  __('Description') => [
                     'name' => 'text',
                     'type' => 'textarea',
                     'value' => $this->fields['text'],
                  ],
                  __('fileupload') => [
                     'id' => mt_rand(),
                     'name' => 'fileupload',
                     'type' => 'file',
                  ]
               ]
            ]
         ]
      ];

      require_once GLPI_ROOT . '/src/twig/twig.utils.php';
      renderTwigForm($form);

      return true;
   }



   /**
    * Display a Planning Item
    *
    * @param $val       array of the item to display
    * @param $who             ID of the user (0 if all)
    * @param $type            position of the item in the time block (in, through, begin or end)
    *                         (default '')
    * @param $complete        complete display (more details) (default 0)
    *
    * @return string
   **/
   static function displayPlanningItem(array $val, $who, $type = "", $complete = 0) {
      global $CFG_GLPI;

      $html = "";
      $rand     = mt_rand();
      $users_id = "";  // show users_id reminder
      $img      = "rdv_private.png"; // default icon for reminder

      if ($val["users_id"] != Session::getLoginUserID()) {
         $users_id = "<br>".sprintf(__('%1$s: %2$s'), __('By'), getUserName($val["users_id"]));
         $img      = "rdv_public.png";
      }

      $html.= "<img src='".$CFG_GLPI["root_doc"]."/pics/".$img."' alt='' title=\"".
             self::getTypeName(1)."\">&nbsp;";
      $html.= "<a id='reminder_".$val["reminders_id"].$rand."' href='".
             Reminder::getFormURLWithID($val["reminders_id"])."'>";

      $html.= $users_id;
      $html.= "</a>";
      $recall = '';
      if (isset($val['reminders_id'])) {
         $pr = new PlanningRecall();
         if ($pr->getFromDBForItemAndUser($val['itemtype'], $val['reminders_id'],
                                          Session::getLoginUserID())) {
            $recall = "<br><span class='b'>".sprintf(__('Recall on %s'),
                                                     Html::convDateTime($pr->fields['when'])).
                      "<span>";
         }
      }
      $text = $val['text'];
      if (isset($val['transtext']) && !empty($val['transtext'])) {
         $text = $val['transtext'];
      }
      if ($complete) {
         $html.= "<span>".Planning::getState($val["state"])."</span><br>";
         $html.= "<div class='event-description rich_text_container'>".$text.$recall."</div>";
      } else {
         $html.= Html::showToolTip("<span class='b'>".Planning::getState($val["state"])."</span><br>
                                   ".$text.$recall,
                                   ['applyto' => "reminder_".$val["reminders_id"].$rand,
                                         'display' => false]);
      }
      return $html;
   }


   /**
    * Show list for central view
    *
    * @param $personal boolean : display reminders created by me ? (true by default)
    *
    * @return void
    **/
   static function showListForCentral($personal = true) {
      global $DB, $CFG_GLPI;

      $users_id = Session::getLoginUserID();
      $today    = date('Y-m-d');
      $now      = date('Y-m-d H:i:s');

      $visibility_criteria = [
         [
            'OR' => [
               ['glpi_reminders.begin_view_date' => null],
               ['glpi_reminders.begin_view_date' => ['<', $now]]
            ]
         ], [
            'OR' => [
               ['glpi_reminders.end_view_date'   => null],
               ['glpi_reminders.end_view_date'   => ['>', $now]]
            ]
         ]
      ];

      if ($personal) {

         /// Personal notes only for central view
         if (Session::getCurrentInterface() == 'helpdesk') {
            return false;
         }

         $criteria = [
            'SELECT' => ['glpi_reminders.*'],
            'FROM'   => 'glpi_reminders',
            'WHERE'  => array_merge([
               'glpi_reminders.users_id'  => $users_id,
               [
                  'OR'        => [
                     'end'          => ['>=', $today],
                     'is_planned'   => 0
                  ]
               ]
            ], $visibility_criteria),
            'ORDER'  => 'glpi_reminders.name'
         ];

         $titre = "<a href='".$CFG_GLPI["root_doc"]."/front/reminder.php'>".
                    _n('Personal reminder', 'Personal reminders', Session::getPluralNumber())."</a>";

      } else {
         // Show public reminders / not mines : need to have access to public reminders
         if (!self::canView()) {
            return false;
         }

         $criteria = array_merge_recursive(
            [
               'SELECT'          => ['glpi_reminders.*'],
               'DISTINCT'        => true,
               'FROM'            => 'glpi_reminders',
               'WHERE'           => $visibility_criteria,
               'ORDERBY'         => 'name'
            ],
            self::getVisibilityCriteria()
         );

         // Only personal on central so do not keep it
         if (Session::getCurrentInterface() == 'central') {
            $criteria['WHERE']['glpi_reminders.users_id'] = ['<>', $users_id];
         }

         if (Session::getCurrentInterface() != 'helpdesk') {
            $titre = "<a href=\"".$CFG_GLPI["root_doc"]."/front/reminder.php\">".
                       _n('Public reminder', 'Public reminders', Session::getPluralNumber())."</a>";
         } else {
            $titre = _n('Public reminder', 'Public reminders', Session::getPluralNumber());
         }
      }

      if (ReminderTranslation::isReminderTranslationActive()) {
         $criteria['LEFT JOIN']['glpi_remindertranslations'] = [
            'ON'  => [
               'glpi_reminders'             => 'id',
               'glpi_remindertranslations'  => 'reminders_id', [
               'AND'                            => [
                  'glpi_remindertranslations.language' => $_SESSION['glpilanguage']
                  ]
               ]
            ]
         ];
         $criteria['SELECT'][] = "glpi_remindertranslations.name AS transname";
         $criteria['SELECT'][] = "glpi_remindertranslations.text AS transtext";
      }

      $iterator = $DB->request($criteria);
      $nb = count($iterator);

      echo "<br><table class='tab_cadrehov'>";
      echo "<tr class='noHover'><th><div class='relative'><span>$titre</span>";

      if (($personal && self::canCreate())
        || (!$personal && Session::haveRight(self::$rightname, CREATE))) {
         echo "<span class='floatright'>";
         echo "<a href='".Reminder::getFormURL()."'>";
         echo "<img src='".$CFG_GLPI["root_doc"]."/pics/plus.png' alt='".__s('Add')."'
                title=\"". __s('Add')."\"></a></span>";
      }

      echo "</div></th></tr>\n";

      if ($nb) {
         $rand = mt_rand();

         while ($data = $iterator->next()) {

            echo "<tr class='tab_bg_2'><td>";
            $name = $data['name'];

            if (isset($data['transname']) && !empty($data['transname'])) {
               $name = $data['transname'];
            }
            $link = "<a id='content_reminder_".$data["id"].$rand."'
                      href='".Reminder::getFormURLWithID($data["id"])."'>".
                    $name."</a>";
            $text = $data["text"];
            if (isset($data['transtext']) && !empty($data['transtext'])) {
               $text = $data['transtext'];
            }
            $tooltip = Html::showToolTip(Toolbox::unclean_html_cross_side_scripting_deep($text),
                                         ['applyto' => "content_reminder_".$data["id"].$rand,
                                          'display' => false]);
            printf(__('%1$s %2$s'), $link, $tooltip);

            if ($data["is_planned"]) {
               $tab      = explode(" ", $data["begin"]);
               $date_url = $tab[0];
               echo "<a href='".$CFG_GLPI["root_doc"]."/front/planning.php?date=".$date_url.
                     "&amp;type=day' class='pointer floatright' title=\"".sprintf(__s('From %1$s to %2$s'),
                                           Html::convDateTime($data["begin"]),
                                           Html::convDateTime($data["end"]))."\">";
               echo "<i class='fa fa-bell'></i>";
               echo "<pan class='sr-only'>" . __s('Planning') . "</span>";
               echo "</a>";
            }

            echo "</td></tr>\n";
         }

      }
      echo "</table>\n";

   }

   /**
    * @since 0.85
    *
    * @see commonDBTM::getRights()
   **/
   function getRights($interface = 'central') {

      if ($interface == 'helpdesk') {
         $values = [READ => __('Read')];
      } else {
         $values = parent::getRights();
      }
      return $values;
   }

   public static function getGroupItemsAsVCalendars($groups_id) {

      return self::getItemsAsVCalendars(
         [
            'DISTINCT'  => true,
            'FROM'      => self::getTable(),
            'LEFT JOIN' => [
               Group_Reminder::getTable() => [
                  'ON' => [
                     Group_Reminder::getTable() => 'reminders_id',
                     self::getTable()           => 'id',
                  ],
               ]
            ],
            'WHERE'     => [
               Group_Reminder::getTableField('groups_id') => $groups_id,
            ],
         ]
      );
   }

   public static function getUserItemsAsVCalendars($users_id) {

      return self::getItemsAsVCalendars(
         [
            'FROM'  => self::getTable(),
            'WHERE' => [
               self::getTableField('users_id') => $users_id,
            ],
         ]
      );
   }

   /**
    * Returns items as VCalendar objects.
    *
    * @param array $query
    *
    * @return \Sabre\VObject\Component\VCalendar[]
    */
   private static function getItemsAsVCalendars(array $query) {

      global $DB;

      $reminder_iterator = $DB->request($query);

      $vcalendars = [];
      foreach ($reminder_iterator as $reminder) {
         $item = new self();
         $item->getFromResultSet($reminder);
         $vcalendar = $item->getAsVCalendar();
         if (null !== $vcalendar) {
            $vcalendars[] = $vcalendar;
         }
      }

      return $vcalendars;
   }

   public function getAsVCalendar() {

      if (!$this->canViewItem()) {
         return null;
      }

      // Transform HTML text to plain text
      $this->fields['text'] = Html::clean(
         Toolbox::unclean_cross_side_scripting_deep(
            $this->fields['text']
         )
      );

      $is_task = in_array($this->fields['state'], [Planning::DONE, Planning::TODO]);
      $is_planned = !empty($this->fields['begin']) && !empty($this->fields['end']);
      $target_component = $this->getTargetCaldavComponent($is_planned, $is_task);
      if (null === $target_component) {
         return null;
      }

      $vcalendar = $this->getVCalendarForItem($this, $target_component);

      return $vcalendar;
   }

   public function getInputFromVCalendar(VCalendar $vcalendar) {

      $vcomp = $vcalendar->getBaseComponent();

      $input = $this->getCommonInputFromVcomponent($vcomp, $this->isNewItem());

      $input['text'] = $input['content'];
      unset($input['content']);

      if ($vcomp instanceof VTodo && !array_key_exists('state', $input)) {
         // Force default state to TODO or reminder will be considered as VEVENT
         $input['state'] = \Planning::TODO;
      }

      return $input;
   }


   static function getIcon() {
      return "far fa-sticky-note";
   }
}
