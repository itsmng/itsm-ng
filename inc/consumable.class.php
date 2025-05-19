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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

//!  Consumable Class
/**
  This class is used to manage the consumables.
  @see ConsumableItem
  @author Julien Dombre
**/
class Consumable extends CommonDBChild
{
    use Glpi\Features\Clonable;

    // From CommonDBTM
    protected static $forward_entity_to = ['Infocom'];
    public $no_form_page                = true;

    public static $rightname                   = 'consumable';

    // From CommonDBChild
    public static $itemtype             = 'ConsumableItem';
    public static $items_id             = 'consumableitems_id';

    public function getCloneRelations(): array
    {
        return [
           Infocom::class
        ];
    }

    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    public static function getNameField()
    {
        return 'id';
    }


    public static function getTypeName($nb = 0)
    {
        return _n('Consumable', 'Consumables', $nb);
    }


    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
              Infocom::class,
            ]
        );
    }


    public function prepareInputForAdd($input)
    {

        $item = new ConsumableItem();
        if ($item->getFromDB($input["consumableitems_id"])) {
            return ["consumableitems_id" => $item->fields["id"],
                         "entities_id"        => $item->getEntityID(),
                         "date_in"            => date("Y-m-d")];
        }
        return [];
    }


    /**
     * send back to stock
     *
     * @param array $input Array of item fields. Only the ID field is used here.
     * @param int $history Not used
     *
     * @return bool
     */
    public function backToStock(array $input, $history = 1)
    {

        $fields = [
            'id'        => $input['id'],
            'date_out'  => null
        ];

        if ($this->update($fields)) {
            return true;
        }

        return false;
    }


    public function getPreAdditionalInfosForName()
    {

        $ci = new ConsumableItem();
        if ($ci->getFromDB($this->fields['consumableitems_id'])) {
            return $ci->getName();
        }
        return '';
    }


    /**
     * UnLink a consumable linked to a printer
     *
     * UnLink the consumable identified by $ID
     *
     * @param integer $ID       consumable identifier
     * @param string  $itemtype itemtype of who we give the consumable
     * @param integer $items_id ID of the item giving the consumable
     *
     * @return boolean
    **/
    public function out($ID, $itemtype = '', $items_id = 0)
    {

        if (!empty($itemtype) && ($items_id > 0)) {

            $fields = [
                'id'        => $ID,
                'date_out'  => date('Y-m-d'),
                'itemtype'  => $itemtype,
                'items_id'  => $items_id
            ];

            if ($this->update($fields)) {
                return true;
            }
        }
        return false;
    }


    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        global $CFG_GLPI;

        $input = $ma->getInput();
        switch ($ma->getAction()) {
            case 'give':
                if (isset($input["entities_id"])) {
                    Dropdown::showSelectItemFromItemtypes(['itemtype_name'
                                                                   => 'give_itemtype',
                                                                'items_id_name'
                                                                   => 'give_items_id',
                                                                'entity_restrict'
                                                                   => $input["entities_id"],
                                                                'itemtypes'
                                                                   => $CFG_GLPI["consumables_types"]]);
                    echo "<br><br>" . Html::submit(
                        _x('button', 'Give'),
                        ['name' => 'massiveaction']
                    );
                    return true;
                }
        }
        return parent::showMassiveActionsSubForm($ma);
    }


    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {

        switch ($ma->getAction()) {
            case 'backtostock':
                foreach ($ids as $id) {
                    if ($item->can($id, UPDATE)) {
                        if ($item->backToStock(["id" => $id])) {
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
                return;
            case 'give':
                $input = $ma->getInput();
                if (
                    ($input["give_items_id"] > 0)
                    && !empty($input['give_itemtype'])
                ) {
                    foreach ($ids as $key) {
                        if ($item->can($key, UPDATE)) {
                            if ($item->out($key, $input['give_itemtype'], $input["give_items_id"])) {
                                $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                            } else {
                                $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                                $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                            }
                        } else {
                            $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_NORIGHT);
                            $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
                        }
                    }
                    Event::log(
                        $item->fields['consumableitems_id'],
                        "consumableitems",
                        5,
                        "inventory",
                        //TRANS: %s is the user login
                        sprintf(__('%s gives a consumable'), $_SESSION["glpiname"])
                    );
                } else {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                }
                return;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }


    /**
     * count how many consumable for the consumable item $tID
     *
     * @param integer $tID consumable item identifier.
     *
     * @return integer number of consumable counted.
     **/
    public static function getTotalNumber($tID)
    {
        $result = self::getAdapter()->request([
           'COUNT'  => 'cpt',
           'FROM'   => 'glpi_consumables',
           'WHERE'  => ['consumableitems_id' => $tID]
        ])->fetchAssociative();
        return (int)$result['cpt'];
    }


    /**
     * count how many old consumable for the consumable item $tID
     *
     * @param integer $tID consumable item identifier.
     *
     * @return integer number of old consumable counted.
    **/
    public static function getOldNumber($tID)
    {

        $result = self::getAdapter()->request([
           'COUNT'  => 'cpt',
           'FROM'   => 'glpi_consumables',
           'WHERE'  => [
              'consumableitems_id' => $tID,
              'NOT'                => ['date_out' => null]
           ]
        ])->fetchAssociative();
        return (int)$result['cpt'];
    }


    /**
     * count how many consumable unused for the consumable item $tID
     *
     * @param integer $tID consumable item identifier.
     *
     * @return integer number of consumable unused counted.
    **/
    public static function getUnusedNumber($tID)
    {
        $result = self::getAdapter()->request([
           'COUNT'  => 'cpt',
           'FROM'   => 'glpi_consumables',
           'WHERE'  => [
              'consumableitems_id' => $tID,
              'date_out'           => null
           ]
        ])->fetchAssociative();
        return(int) $result['cpt'];
    }


    /**
     * Get the consumable count HTML array for a defined consumable type
     *
     * @param integer $tID             consumable item identifier.
     * @param integer $alarm_threshold threshold alarm value.
     * @param boolean $nohtml          Return value without HTML tags.
     *
     * @return string to display
    **/
    public static function getCount($tID, $alarm_threshold, $nohtml = 0)
    {

        // Get total
        $total = self::getTotalNumber($tID);

        if ($total != 0) {
            $unused = self::getUnusedNumber($tID);
            $old    = self::getOldNumber($tID);

            $highlight = "";
            if ($unused <= $alarm_threshold) {
                $highlight = "class='tab_bg_1_2'";
            }
            //TRANS: For consumable. %1$d is total number, %2$d is unused number, %3$d is old number
            $tmptxt = sprintf(__('Total: %1$d, New: %2$d, Used: %3$d'), $total, $unused, $old);
            if ($nohtml) {
                $out = $tmptxt;
            } else {
                $out = "<div $highlight>" . $tmptxt . "</div>";
            }
        } else {
            $out = '';
        }
        return $out;
    }


    /**
     * Check if a Consumable is New (not used, in stock)
     *
     * @param integer $cID consumable ID.
     *
     * @return boolean
    **/
    public static function isNew($cID)
    {
        $result = self::getAdapter()->request([
           'COUNT'  => 'cpt',
           'FROM'   => 'glpi_consumables',
           'WHERE'  => [
              'id'        => $cID,
              'date_out'  => null
           ]
        ])->fetchAssociative();
        return $result['cpt'] == 1;
    }


    /**
     * Check if a consumable is Old (used, not in stock)
     *
     * @param integer $cID consumable ID.
     *
     * @return boolean
    **/
    public static function isOld($cID)
    {
        $result = self::getAdapter()->request([
           'COUNT'  => 'cpt',
           'FROM'   => 'glpi_consumables',
           'WHERE'  => [
              'id'     => $cID,
              'NOT'   => ['date_out' => null]
           ]
        ])->fetchAssociative();
        return $result['cpt'] == 1;
    }


    /**
     * Get the localized string for the status of a consumable
     *
     * @param integer $cID consumable ID.
     *
     * @return string
    **/
    public static function getStatus($cID)
    {

        if (self::isNew($cID)) {
            return _nx('consumable', 'New', 'New', 1);
        } elseif (self::isOld($cID)) {
            return _nx('consumable', 'Used', 'Used', 1);
        }
    }


    /**
     * Print out a link to add directly a new consumable from a consumable item.
     *
     * @param ConsumableItem $consitem
     *
     * @return void
    **/
    public static function showAddForm(ConsumableItem $consitem)
    {

        $ID = $consitem->getField('id');

        if (!$consitem->can($ID, UPDATE)) {
            return;
        }

        if ($ID > 0) {
            $form = [
               'action' => static::getFormURL(),
               'buttons' => [
                  [
                     'name' => 'add_several',
                     'value' => _x('button', 'Add consumables'),
                     'class' => 'btn btn-secondary',
                  ]
               ],
               'content' => [
                  '' => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'consumableitems_id',
                           'value' => $ID,
                        ],
                        '' => [
                           'type' => 'number',
                           'name' => 'to_add',
                           'min' => 1,
                           'max' => 100,
                           'step' => 1,
                           'value' => 1,
                           'col_lg' => 12,
                           'col_md' => 12,
                        ]
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }
    }


    /**
     * Print out the consumables of a defined type
     *
     * @param ConsumableItem $consitem
     * @param boolean        $show_old show old consumables or not. (default 0)
     *
     * @return void
    **/
    public static function showForConsumableItem(ConsumableItem $consitem, $show_old = 0)
    {
        global $DB;

        $tID = $consitem->getField('id');
        if (!$consitem->can($tID, READ)) {
            return;
        }

        if (isset($_GET["start"])) {
            $start = $_GET["start"];
        } else {
            $start = 0;
        }

        $canedit = $consitem->can($tID, UPDATE);
        $rand = mt_rand();
        $where = ['consumableitems_id' => $tID];
        $order = ['date_in', 'id'];
        if (!$show_old) { // NEW
            $where += ['date_out' => 'NULL'];
        } else { //OLD
            $where += ['NOT'   => ['date_out' => 'NULL']];
            $order = ['date_out DESC'] + $order;
        }

        $number = countElementsInTable("glpi_consumables", $where);

        $result = self::getAdapter()->request([
           'FROM'   => self::getTable(),
           'WHERE'  => $where,
           'ORDER'  => $order,
           'START'  => (int)$start,
           'LIMIT'  => (int)$_SESSION['glpilist_limit']
        ]);

        if ($canedit && $number) {
            $actions = [];
            if ($consitem->can($tID, PURGE)) {
                $actions['delete'] = _x('button', 'Delete permanently');
            }
            $actions['Infocom' . MassiveAction::CLASS_ACTION_SEPARATOR . 'activate']
               = __('Enable the financial and administrative information');

            if ($show_old) {
                $actions['Consumable' . MassiveAction::CLASS_ACTION_SEPARATOR . 'backtostock']
                         = __('Back to stock');
            } else {
                $actions[__CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'give'] = _x('button', 'Give');
            }
            $entparam = ['entities_id' => $consitem->getEntityID()];
            if ($consitem->isRecursive()) {
                $entparam = ['entities_id' => getSonsOf('glpi_entities', $consitem->getEntityID())];
            }
            $massFormContainerId = 'tableForConsumable' . rand();
            $massiveactionparams = [
               'specific_actions' => $actions,
               'container'        => $massFormContainerId,
               'extraparams'      => $entparam,
               'display_arrow' => false,
            ];
            Html::showMassiveActions($massiveactionparams);
        }

        $fields = [
           __('ID'),
           _x('item', 'State'),
           __('Add date'),
        ];
        if ($show_old) {
            $fields[] = __('Use date');
            $fields[] = __('Given to');
        }
        $fields[] = __('Financial and administrative information');

        $values = [];
        $massive_action = [];
        if ($number) {
            while ($data = $result->fetchAssociative()) {
                $newValue = [];
                $date_in  = Html::convDate($data["date_in"]);
                $date_out = Html::convDate($data["date_out"]);

                $newValue[] = $data['id'];
                $newValue[] = self::getStatus($data["id"]);
                $newValue[] = $date_in;
                if ($show_old) {
                    $newValue[] = $date_out;
                    if ($item = getItemForItemtype($data['itemtype'])) {
                        if ($item->getFromDB($data['items_id'])) {
                            $newValue[] = $item->getLink();
                        }
                    }
                }
                ob_start();
                Infocom::showDisplayLink('Consumable', $data["id"]);
                $newValue[] = ob_get_clean();
                $values[] = $newValue;
                $massive_action[] = sprintf('item[%s][%s]', self::class, $data['id']);
            }
        }

        if (!$show_old) {
            echo self::getCount($tID, -1);
        } else { // Old
            echo __('Used consumables');
        }
        renderTwigTemplate('table.twig', [
           'id' => $massFormContainerId ?? '',
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action,
        ]);
    }


    /**
     * Show the usage summary of consumables by user
     *
     * @return void
     **/
    public static function showSummary()
    {
        global $DB;

        if (!Consumable::canView()) {
            return;
        }

        $result = self::getAdapter()->request([
           'SELECT' => [
              'COUNT'  => ['* AS count'],
              'consumableitems_id',
              'itemtype',
              'items_id'
           ],
           'FROM'   => 'glpi_consumables',
           'WHERE'  => [
              'NOT'                => ['date_out' => null],
              'consumableitems_id' => new \QuerySubQuery([
                 'SELECT' => 'id',
                 'FROM'   => 'glpi_consumableitems',
                 'WHERE'  => getEntitiesRestrictCriteria('glpi_consumableitems')
              ])
           ],
           'GROUP'  => ['itemtype', 'items_id', 'consumableitems_id']
        ]);
        $used = [];

        while ($data = $result->fetchAssociative()) {
            $used[$data['itemtype'] . '####' . $data['items_id']][$data["consumableitems_id"]]
               = $data["count"];
        }

        $result = self::getAdapter()->request([
           'SELECT' => [
              'COUNT'  => '* AS count',
              'consumableitems_id',
           ],
           'FROM'   => 'glpi_consumables',
           'WHERE'  => [
              'date_out'           => null,
              'consumableitems_id' => new \QuerySubQuery([
                 'SELECT' => 'id',
                 'FROM'   => 'glpi_consumableitems',
                 'WHERE'  => getEntitiesRestrictCriteria('glpi_consumableitems')
              ])
           ],
           'GROUP'  => ['consumableitems_id']
        ]);
        $new = [];

        while ($data = $result->fetchAssociative()) {
            $new[$data["consumableitems_id"]] = $data["count"];
        }

        $result = self::getAdapter()->request([
           'FROM'   => 'glpi_consumableitems',
           'WHERE'  => getEntitiesRestrictCriteria('glpi_consumableitems')
        ]);
        $types = [];

        while ($data = $result->fetchAssociative()) {
            $types[$data["id"]] = $data["name"];
        }

        asort($types);
        $total = [];
        if (count($types) > 0) {
            // Produce headline
            echo "<div class='center'><table class='tab_cadrehov' aria-label='summary'><tr>";

            // Type
            echo "<th>" . __('Give to') . "</th>";

            foreach ($types as $key => $type) {
                echo "<th>$type</th>";
                $total[$key] = 0;
            }
            echo "<th>" . __('Total') . "</th>";
            echo "</tr>";

            // new
            echo "<tr class='tab_bg_2'><td class='b'>" . __('In stock') . "</td>";
            $tot = 0;
            foreach ($types as $id_type => $type) {
                if (!isset($new[$id_type])) {
                    $new[$id_type] = 0;
                }
                echo "<td class='center'>" . $new[$id_type] . "</td>";
                $total[$id_type] += $new[$id_type];
                $tot             += $new[$id_type];
            }
            echo "<td class='numeric'>" . $tot . "</td>";
            echo "</tr>";

            foreach ($used as $itemtype_items_id => $val) {
                echo "<tr class='tab_bg_2'><td>";
                list($itemtype, $items_id) = explode('####', $itemtype_items_id);
                $item = new $itemtype();
                if ($item->getFromDB($items_id)) {
                    //TRANS: %1$s is a type name - %2$s is a name
                    printf(__('%1$s - %2$s'), $item->getTypeName(1), $item->getNameID());
                }
                echo "</td>";
                $tot = 0;
                foreach ($types as $id_type => $type) {
                    if (!isset($val[$id_type])) {
                        $val[$id_type] = 0;
                    }
                    echo "<td class='center'>" . $val[$id_type] . "</td>";
                    $total[$id_type] += $val[$id_type];
                    $tot             += $val[$id_type];
                }
                echo "<td class='numeric'>" . $tot . "</td>";
                echo "</tr>";
            }
            echo "<tr class='tab_bg_1'><td class='b'>" . __('Total') . "</td>";
            $tot = 0;
            foreach ($types as $id_type => $type) {
                $tot += $total[$id_type];
                echo "<td class='numeric'>" . $total[$id_type] . "</td>";
            }
            echo "<td class='numeric'>" . $tot . "</td>";
            echo "</tr>";
            echo "</table></div>";
        } else {
            echo "<div class='center b'>" . __('No consumable found') . "</div>";
        }
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate && Consumable::canView()) {
            $nb = 0;
            switch ($item->getType()) {
                case 'ConsumableItem':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb =  self::countForConsumableItem($item);
                    }
                    return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
            }
        }
        return '';
    }


    /**
     * @param ConsumableItem $item
     *
     * @return integer
    **/
    public static function countForConsumableItem(ConsumableItem $item)
    {

        return countElementsInTable(['glpi_consumables'], ['glpi_consumables.consumableitems_id' => $item->getField('id')]);
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'ConsumableItem':
                self::showAddForm($item);
                self::showForConsumableItem($item);
                self::showForConsumableItem($item, 1);
                return true;
        }
    }

    public function getRights($interface = 'central')
    {
        $ci = new ConsumableItem();
        return $ci->getRights($interface);
    }


    public static function getIcon()
    {
        return "fas fa-box-open";
    }
}
