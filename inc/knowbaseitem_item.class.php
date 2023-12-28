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
 *  Class KnowbaseItem_Item
 *
 *  @author Johan Cwiklinski <jcwiklinski@teclib.com>
 *
 *  @since 9.2
 */
class KnowbaseItem_Item extends CommonDBRelation {

   // From CommonDBRelation
   static public $itemtype_1          = 'KnowbaseItem';
   static public $items_id_1          = 'knowbaseitems_id';
   static public $itemtype_2          = 'itemtype';
   static public $items_id_2          = 'items_id';
   static public $checkItem_2_Rights  = self::HAVE_VIEW_RIGHT_ON_ITEM;

   // From CommonDBTM
   public $dohistory          = true;

   static function getTypeName($nb = 0) {
      return _n('Knowledge base item', 'Knowledge base items', $nb);
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (static::canView()) {
         $nb = 0;
         if ($_SESSION['glpishow_count_on_tabs']) {
            if ($item->getType() == KnowbaseItem::getType()) {
               $nb = countElementsInTable(
                  'glpi_knowbaseitems_items',
                  ['knowbaseitems_id' => $item->getID()]
               );
            } else {
               $nb = countElementsInTable(
                  'glpi_knowbaseitems_items',
                  [
                     'itemtype' => $item::getType(),
                     'items_id' => $item->getId()
                  ]
               );
            }
         }

         $type_name = null;
         if ($item->getType() == KnowbaseItem::getType()) {
            $type_name = _n('Associated element', 'Associated elements', $nb);
         } else {
            $type_name = __('Knowledge base');
         }

         return self::createTabEntry($type_name, $nb);
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      self::showForItem($item, $withtemplate);
      return true;
   }

   /**
    * Show linked items of a knowbase item
    *
    * @param $item                     CommonDBTM object
    * @param $withtemplate    integer  withtemplate param (default 0)

   **/
   static function showForItem(CommonDBTM $item, $withtemplate = 0) {
      $item_id = $item->getID();
      $item_type = $item::getType();

      if (isset($_GET["start"])) {
         $start = intval($_GET["start"]);
      } else {
         $start = 0;
      }

      $canedit = $item->can($item_id, UPDATE);

      // Total Number of events
      if ($item_type == KnowbaseItem::getType()) {
         $number = countElementsInTable("glpi_knowbaseitems_items", ['knowbaseitems_id' => $item_id]);
      } else {
         $number = countElementsInTable(
            'glpi_knowbaseitems_items',
            [
               'itemtype' => $item::getType(),
               'items_id' => $item_id
            ]
         );
      }

      $ok_state = true;
      if ($item instanceof CommonITILObject) {
         $ok_state = !in_array(
            $item->fields['status'],
            array_merge(
               $item->getClosedStatusArray(),
               $item->getSolvedStatusArray()
            )
         );
      }

      if ($canedit && $ok_state) {
         $form = [
            'action' => Toolbox::getItemTypeFormURL(__CLASS__),
            'buttons' => [
               [
                  'type' => 'submit',
                  'name' => 'add',
                  'value' => _sx('button', 'Add'),
                  'class' => 'btn btn-secondary'
               ]
            ],
            'content' => [
               [
                  'visible' => false,
                  'inputs' => [
                     $item_type == KnowbaseItem::getType() ? [
                        'type' => 'hidden',
                        'name' => 'knowbaseitems_id',
                        'value' => $item->getID(),
                     ] :
                     [
                        'type' => 'hidden',
                        'name' => 'itemtype',
                        'value' => $item->getType(),
                     ],
                     $item_type != KnowbaseItem::getType() ? [
                        'type' => 'hidden',
                        'name' => 'items_id',
                        'value' => $item->getID(),
                     ] : [
                     ]

                  ]
               ],
               ($item_type == KnowbaseItem::getType()) ? __('Add a linked item') : __('Link a knowledge base entry') => [
                  'visible' => true,
                  'inputs' => [
                     __('Link') => ($item_type == KnowbaseItem::getType()) ? [
                        'content' => '<h1>TODO</h1>'
                     ] : [
                        'type' => 'select',
                        'name' => 'knowbaseitems_id',
                        'values' => getOptionForItems('KnowbaseItem',
                           (isset(KnowbaseItem::getVisibilityCriteria()['WHERE'])
                              && count(KnowbaseItem::getVisibilityCriteria()['WHERE'])) 
                                 ? KnowbaseItem::getVisibilityCriteria()['WHERE'] : [],
                        ),
                        'value' => '',
                     ]
                  ]
               ]
            ]
         ];
         renderTwigForm($form);
      }

      // No Events in database
      if ($number < 1) {
         $no_txt = ($item_type == KnowbaseItem::getType()) ?
            __('No linked items') :
            __('No knowledge base entries linked');
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th>$no_txt</th></tr>";
         echo "</table>";
         echo "</div>";
         return;
      }

      // Display the pager
      $type_name = null;
      if ($item->getType() == KnowbaseItem::getType()) {
         $type_name = _n('Linked item', 'Linked items', 1);
      } else {
         $type_name = self::getTypeName(1);
      }
      Html::printAjaxPager($type_name, $start, $number);

      // Output events
      echo "<div class='center'>";

      if ($canedit) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams
            = ['num_displayed'
                        => min($_SESSION['glpilist_limit'], $number),
                    'container'
                        => 'mass'.__CLASS__.$rand];
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixehov'>";

      $header = '<tr>';

      if ($canedit) {
         $header    .= "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand) . "</th>";
      }

      $header .= "<th>" . _n('Type', 'Types', 1) . "</th>";
      $header .= "<th>"._n('Item', 'Items', 1)."</th>";
      $header .= "<th>".__('Creation date')."</th>";
      $header .= "<th>".__('Update date')."</th>";
      $header .= "</tr>";
      echo $header;

      foreach (self::getItems($item, $start, $_SESSION['glpilist_limit']) as $data) {
         $linked_item = null;
         if ($item->getType() == KnowbaseItem::getType()) {
            $linked_item = getItemForItemtype($data['itemtype']);
            $linked_item->getFromDB($data['items_id']);
         } else {
            $linked_item = getItemForItemtype(KnowbaseItem::getType());
            $linked_item->getFromDB($data['knowbaseitems_id']);
         }

         $name = $linked_item->fields['name'];
         if ($_SESSION["glpiis_ids_visible"]
            || empty($name)) {
            $name = sprintf(__('%1$s (%2$s)'), $name, $linked_item->getID());
         }

         $link = $linked_item::getFormURLWithID($linked_item->getID());

         $createdate = $item::getType() == KnowbaseItem::getType() ? 'date_creation' : 'date';
         // show line
         echo "<tr class='tab_bg_2'>";

         if ($canedit) {
            echo "<td width='10'>";
            Html::showMassiveActionCheckBox(__CLASS__, $data['id']);
            echo "</td>";
         }

         $type = $linked_item->getTypeName(1);
         if (isset($linked_item->fields['is_template']) && $linked_item->fields['is_template'] == 1) {
             $type .= ' (' . __('template') . ')';
         }

         echo "<td>" . $type . "</td>" .
                 "<td><a href=\"" . $link . "\">" . $name . "</a></td>".
                 "<td class='tab_date'>".Html::convDateTime($linked_item->fields[$createdate])."</td>".
                 "<td class='tab_date'>".Html::convDateTime($linked_item->fields['date_mod'])."</td>";
         echo "</tr>";
      }
      echo $header;
      echo "</table>";

      $massiveactionparams['ontop'] = false;
      Html::showMassiveActions($massiveactionparams);

      echo "</div>";
      Html::printAjaxPager($type_name, $start, $number);
   }

   /**
    * Displays linked dropdowns to add linked items
    *
    * @param CommonDBTM $item Item instance
    * @param string     $name Field name
    *
    * @return string
    */
   static function dropdownAllTypes(CommonDBTM $item, $name) {
      global $CFG_GLPI;

      $onlyglobal = 0;
      $entity_restrict = -1;
      $checkright = true;

      $rand = Dropdown::showSelectItemFromItemtypes([
         'items_id_name'   => $name,
         'entity_restrict' => $entity_restrict,
         'itemtypes'       => $CFG_GLPI['kb_types'],
         'onlyglobal'      => $onlyglobal,
         'checkright'      => $checkright
      ]);

      return $rand;
   }

   /**
    * Retrieve items for a knowbase item
    *
    * @param CommonDBTM $item      CommonDBTM object
    * @param integer    $start     first line to retrieve (default 0)
    * @param integer    $limit     max number of line to retrive (0 for all) (default 0)
    * @param boolean    $used      whether to retrieve data for "used" records
    *
    * @return array of linked items
   **/
   static function getItems(CommonDBTM $item, $start = 0, $limit = 0, $used = false) {
      global $DB;

      $criteria = [
         'FROM'      => ['glpi_knowbaseitems_items'],
         'FIELDS'    => ['glpi_knowbaseitems_items' => '*'],
         'INNER JOIN' => [
            'glpi_knowbaseitems' => [
               'ON'  => [
                  'glpi_knowbaseitems_items' => 'knowbaseitems_id',
                  'glpi_knowbaseitems'       => 'id'
               ]
            ]
         ],
         'WHERE'     => [],
         'ORDER'     => ['itemtype', 'items_id DESC'],
         'GROUPBY'   => [
            'glpi_knowbaseitems_items.id',
            'glpi_knowbaseitems_items.knowbaseitems_id',
             'glpi_knowbaseitems_items.itemtype',
             'glpi_knowbaseitems_items.items_id',
             'glpi_knowbaseitems_items.date_creation',
             'glpi_knowbaseitems_items.date_mod'
         ]
      ];
      $where = [];

      $items_id  = (int)$item->getField('id');

      if ($item::getType() == KnowbaseItem::getType()) {
         $id_field = 'glpi_knowbaseitems_items.knowbaseitems_id';
         $visibility = KnowbaseItem::getVisibilityCriteria();
         if (count($visibility['LEFT JOIN'])) {
            $criteria['LEFT JOIN'] = $visibility['LEFT JOIN'];
            if (isset($visibility['WHERE'])) {
               $where = $visibility['WHERE'];
            }
         }
      } else {
         $id_field = 'glpi_knowbaseitems_items.items_id';
         $where = getEntitiesRestrictCriteria($item->getTable(), '', '', $item->maybeRecursive());
         $where[] = ['glpi_knowbaseitems_items.itemtype' => $item::getType()];
         if (count($where)) {
            $criteria['INNER JOIN'][$item->getTable()] = [
               'ON' => [
                  'glpi_knowbaseitems_items' => 'items_id',
                  $item->getTable()          => 'id'
               ]
            ];
         }
      }

      $criteria['WHERE'] = [$id_field => $items_id];
      if (count($where)) {
         $criteria['WHERE'] = array_merge($criteria['WHERE'], $where);
      }

      if ($limit) {
         $criteria['START'] = intval($start);
         $criteria['LIMIT'] = intval($limit);
      }

      $linked_items = [];
      $results = $DB->request($criteria);
      while ($data = $results->next()) {
         if ($used === false) {
            $linked_items[] = $data;
         } else {
            $key = $item::getType() == KnowbaseItem::getType() ? 'items_id' : 'knowbaseitems_id';
            $linked_items[$data[$key]] = $data[$key];
         }
      }
      return $linked_items;
   }

   /**
    * Duplicate KB links from an item template to its clone
    *
    * @deprecated 9.5
    * @since 9.2
    *
    * @param string  $itemtype     itemtype of the item
    * @param integer $oldid        ID of the item to clone
    * @param integer $newid        ID of the item cloned
    * @param string  $newitemtype  itemtype of the new item (= $itemtype if empty) (default '')
   **/
   static function cloneItem($itemtype, $oldid, $newid, $newitemtype = '') {
      global $DB;

      Toolbox::deprecated('Use clone');
      if (empty($newitemtype)) {
         $newitemtype = $itemtype;
      }

      $iterator = $DB->request([
         'FROM'   => 'glpi_knowbaseitems_items',
         'FIELDS' => 'knowbaseitems_id',
         'WHERE'  => [
            'items_id'  => $oldid,
            'itemtype'  => $itemtype
         ]
      ]);

      while ($data = $iterator->next()) {
         $kb_link = new self();
         $kb_link->add(['knowbaseitems_id' => $data['knowbaseitems_id'],
                                  'itemtype'    => $newitemtype,
                                  'items_id'    => $newid]);
      }
   }

   function getForbiddenStandardMassiveAction() {
      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }

   static function getMassiveActionsForItemtype(array &$actions, $itemtype, $is_deleted = 0,
                                                CommonDBTM $checkitem = null) {

      $kb_item = new KnowbaseItem();
      $kb_item->getEmpty();
      if ($kb_item->canViewItem()) {
         $action_prefix = __CLASS__.MassiveAction::CLASS_ACTION_SEPARATOR;

         $actions[$action_prefix.'add']
            = "<i class='ma-icon fas fa-book'></i>".
              _x('button', 'Link knowledgebase article');
      }

      parent::getMassiveActionsForItemtype($actions, $itemtype, $is_deleted, $checkitem);
   }
}
