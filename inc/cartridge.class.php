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
 * Cartridge class.
 * This class is used to manage printer cartridges.
 * @see CartridgeItem
 * @author Julien Dombre
 **/
class Cartridge extends CommonDBChild
{
    use Glpi\Features\Clonable;

    // From CommonDBTM
    protected static $forward_entity_to = ['Infocom'];
    public $dohistory                   = true;
    public $no_form_page                = true;

    // From CommonDBChild
    public static $itemtype             = 'CartridgeItem';
    public static $items_id             = 'cartridgeitems_id';

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


    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {

        switch ($ma->getAction()) {
            case 'updatepages':
                if (!isset($input['maxpages'])) {
                    $input['maxpages'] = '';
                }
                $input = $ma->getInput();
                renderTwigTemplate('macros/wrappedInput.twig', [
                   'title' => __('Pages'),
                   'input' => [
                      'type'  => 'number',
                      'name'  => 'pages',
                      'value' => $input['maxpages'],
                      'col_lg' => 12,
                      'col_md' => 12,
                   ],
                ]);
                echo Html::submit(_x('button', 'Update'), ['name' => 'massiveaction', 'class' => 'btn btn-secondary mt-3']);
                return true;
        }
        return parent::showMassiveActionsSubForm($ma);
    }


    public static function getNameField()
    {
        return 'id';
    }


    public static function getTypeName($nb = 0)
    {
        return _n('Cartridge', 'Cartridges', $nb);
    }


    public function prepareInputForAdd($input)
    {

        $item = static::getItemFromArray(static::$itemtype, static::$items_id, $input);
        if ($item === false) {
            return false;
        }

        return ["cartridgeitems_id" => $item->fields["id"],
                     "entities_id"       => $item->getEntityID(),
                     "date_in"           => date("Y-m-d")];
    }


    public function post_updateItem($history = 1)
    {

        if (in_array('pages', $this->updates)) {
            $printer = new Printer();
            if (
                $printer->getFromDB($this->fields['printers_id'])
                && (($this->fields['pages'] > $printer->getField('last_pages_counter'))
                    || ($this->oldvalues['pages'] == $printer->getField('last_pages_counter')))
            ) {
                $printer->update(['id'                 => $printer->getID(),
                                       'last_pages_counter' => $this->fields['pages'] ]);
            }
        }
        parent::post_updateItem($history);
    }


    public function getPreAdditionalInfosForName()
    {

        $ci = new CartridgeItem();
        if ($ci->getFromDB($this->fields['cartridgeitems_id'])) {
            return $ci->getName();
        }
        return '';
    }


    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {

        switch ($ma->getAction()) {
            case 'uninstall':
                foreach ($ids as $key) {
                    if ($item->can($key, UPDATE)) {
                        if ($item->uninstall($key)) {
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
                return;

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

            case 'updatepages':
                $input = $ma->getInput();
                if (isset($input['pages'])) {
                    foreach ($ids as $key) {
                        if ($item->can($key, UPDATE)) {
                            if (
                                $item->update(['id' => $key,
                                                    'pages' => $input['pages']])
                            ) {
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
                } else {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                }
                return;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }


    /**
     * Send the cartridge back to stock.
     *
     * @since 0.85 (before name was restore)
     * @param array   $input
     * @param integer $history
     * @return bool
     */
    public function backToStock(array $input, $history = 1)
    {
        $adapter = $this::getAdapter();

        $rows = $adapter->request([
            'SELECT' => ['id'],
            'FROM'   => $this->getTable(),
            'WHERE'  => ['id' => $input['id']]
        ])->fetchAllAssociative();

        if (count($rows)) {
            $id = $rows[0]['id'];

            $updated = $adapter->save([
                'id'          => $id,
                'date_out'    => null,
                'date_use'    => null,
                'printers_id' => 0
            ]);

            if ($updated) {
                return true;
            }
        }
        return false;
    }


    // SPECIFIC FUNCTIONS

    /**
     * Link a cartridge to a printer.
     *
     * Link the first unused cartridge of type $Tid to the printer $pID.
     *
     * @param integer $tID ID of the cartridge
     * @param integer $pID : ID of the printer
     *
     * @return boolean True if successful
    **/
    public function install($pID, $tID)
    {
        global $DB;

        // Get first unused cartridge
        $request = $this::getAdapter()->request([
           'SELECT' => ['id'],
           'FROM'   => $this->getTable(),
           'WHERE'  => [
              'cartridgeitems_id'  => $tID,
              'date_use'           => null
           ],
           'LIMIT'  => 1
        ]);
        $results = $request->fetchAllAssociative();
        if (count($results)) {
            $result = $results[0];
            $cID = $result['id'];
            // Update cartridge taking care of multiple insertion
            $adapter = $this::getAdapter();
            $rows = $adapter->request([
                'SELECT' => ['id'],
                'FROM'   => $this->getTable(),
                'WHERE'  => [
                    'id'        => $cID,
                    'date_use'  => null
                ]
            ])->fetchAllAssociative();

            $cartridgeUpdated = false;
            if (count($rows)) {
                $id = $rows[0]['id'];
                $cartridgeUpdated = $adapter->save([
                    'id'          => $id,
                    'date_use'    => date('Y-m-d'),
                    'printers_id' => $pID
                ]);
            }

            if ($cartridgeUpdated) {
                $changes = [
                   '0',
                   '',
                   __('Installing a cartridge'),
                ];
                Log::history($pID, 'Printer', $changes, 0, Log::HISTORY_LOG_SIMPLE_MESSAGE);
                return true;
            }
        } else {
            Session::addMessageAfterRedirect(__('No free cartridge'), false, ERROR);
        }
        return false;
    }


    /**
     * Unlink a cartridge from a printer by cartridge ID.
     *
     * @param integer $ID ID of the cartridge
     *
     * @return boolean
    **/
    public function uninstall($ID)
    {

        if ($this->getFromDB($ID)) {
            $printer = new Printer();
            $toadd   = [];
            if ($printer->getFromDB($this->getField("printers_id"))) {
                $toadd['pages'] = $printer->fields['last_pages_counter'];
            }

            $adapter = $this::getAdapter();

            $rows = $adapter->request([
                'SELECT' => ['id'],
                'FROM'   => $this->getTable(),
                'WHERE'  => ['id' => $ID]
            ])->fetchAllAssociative();
            if (count($rows)) {
                $fieldsToSave = ['id' => $ID, 'date_out' => date('Y-m-d')] + $toadd;

                $updated = $adapter->save($fieldsToSave);

                if ($updated) {
                    $changes = [
                    '0',
                    '',
                    __('Uninstalling a cartridge'),
                    ];
                    Log::history(
                        $this->getField("printers_id"),
                        'Printer',
                        $changes,
                        0,
                        Log::HISTORY_LOG_SIMPLE_MESSAGE
                    );

                    return true;
                }
                return false;
            }
        }
    }


    /**
     * Print the cartridge count HTML array for the cartridge item $tID
     *
     * @param integer         $tID      ID of the cartridge item
     * @param integer         $alarm_threshold Alarm threshold value
     * @param integer|boolean $nohtml          True if the return value should be without HTML tags (default 0/false)
     *
     * @return string String to display
    **/
    public static function getCount($tID, $alarm_threshold, $nohtml = 0)
    {

        // Get total
        $total = self::getTotalNumber($tID);
        $out   = "";
        if ($total != 0) {
            $unused     = self::getUnusedNumber($tID);
            $used       = self::getUsedNumber($tID);
            $old        = self::getOldNumber($tID);
            $highlight  = "";
            if ($unused <= $alarm_threshold) {
                $highlight = "tab_bg_1_2";
            }


            if (!$nohtml) {
                $fields = [
                   __('Total'),
                   _nx('cartridge', 'New', 'New', $unused),
                   _nx('cartridge', 'Used', 'Used', $used),
                   _nx('cartridge', 'Worn', 'Worn', $old),
                ];
                $values = [ [$total, $unused, $used, $old], ];
                renderTwigTemplate('table.twig', [ 'fields' => $fields, 'values' => $values, 'minimal' => true]);
            } else {
                //TRANS : for display cartridges count : %1$d is the total number,
                //        %2$d the new one, %3$d the used one, %4$d worn one
                $out .= sprintf(
                    __('Total: %1$d (%2$d new, %3$d used, %4$d worn)'),
                    $total,
                    $unused,
                    $used,
                    $old
                );
            }
        } else {
            if (!$nohtml) {
                $out .= "<div class='tab_bg_1_2'><i>" . __('No cartridge') . "</i></div>";
            } else {
                $out .= __('No cartridge');
            }
        }
        return $out;
    }


    /**
     * Print the cartridge count HTML array for the printer $pID
     *
     * @since 0.85
     *
     * @param integer         $pID    ID of the printer
     * @param integer|boolean $nohtml True if the return value should be without HTML tags (default 0/false)
     *
     * @return string String to display
    **/
    public static function getCountForPrinter($pID, $nohtml = 0)
    {

        // Get total
        $total = self::getTotalNumberForPrinter($pID);
        $out   = "";
        if ($total != 0) {
            $used       = self::getUsedNumberForPrinter($pID);
            $old        = self::getOldNumberForPrinter($pID);
            $highlight  = "";
            if ($used == 0) {
                $highlight = "tab_bg_1_2";
            }

            if (!$nohtml) {
                $out .= "<table  class='tab_format $highlight' width='100%' aria-label='Printer Cartridge Details Table'><tr><td>";
                $out .= __('Total') . "</td><td>$total";
                $out .= "</td><td colspan='2'></td><tr>";
                $out .= "<tr><td>";
                $out .= _nx('cartridge', 'Used', 'Used', $used);
                $out .= "</td><td>$used</span></td><td>";
                $out .= _nx('cartridge', 'Worn', 'Worn', $old);
                $out .= "</td><td>$old</span></td></tr></table>";
            } else {
                //TRANS : for display cartridges count : %1$d is the total number,
                //        %2$d the used one, %3$d the worn one
                $out .= sprintf(__('Total: %1$d (%2$d used, %3$d worn)'), $total, $used, $old);
            }
        } else {
            if (!$nohtml) {
                $out .= "<div class='tab_bg_1_2'><i>" . __('No cartridge') . "</i></div>";
            } else {
                $out .= __('No cartridge');
            }
        }
        return $out;
    }


    /**
     * Count the total number of cartridges for the cartridge item $tID.
     *
     * @param integer $tID ID of cartridge item.
     *
     * @return integer Number of cartridges counted.
    **/
    public static function getTotalNumber($tID)
    {
        $row = self::getAdapter()->request([
           'FROM'   => self::getTable(),
           'COUNT'  => 'cpt',
           'WHERE'  => ['cartridgeitems_id' => $tID]
        ])->fetchAssociative();
        return $row['cpt'];
    }


    /**
     * Count the number of cartridges used for the printer $pID
     *
     * @since 0.85
     *
     * @param integer $pID ID of the printer.
     *
     * @return integer Number of cartridges counted.
    **/
    public static function getTotalNumberForPrinter($pID)
    {
        $row = self::getAdapter()->request([
           'FROM'   => self::getTable(),
           'COUNT'  => 'cpt',
           'WHERE'  => ['printers_id' => $pID]
        ])[0];
        return (int)$row['cpt'];
    }


    /**
     * Count the number of used cartridges for the cartridge item $tID.
     *
     * @param integer $tID ID of the cartridge item.
     *
     * @return integer Number of used cartridges counted.
    **/
    public static function getUsedNumber($tID)
    {
        $row = self::getAdapter()->request([
           'COUNT'  => 'cpt',
           'FROM'   => 'glpi_cartridges',
           'WHERE'  => [
              'cartridgeitems_id'  => $tID,
              'date_out'           => null,
              'NOT'                => [
                 'date_use'  => null
              ]
           ]
        ])->fetchAssociative();
        return (int)$row['cpt'];
    }


    /**
     * Count the number of used cartridges used for the printer $pID.
     *
     * @since 0.85
     *
     * @param integer $pID ID of the printer.
     *
     * @return integer Number of used cartridge counted.
    **/
    public static function getUsedNumberForPrinter($pID)
    {
        $result = self::getAdapter()->request([
           'COUNT'  => 'cpt',
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'printers_id'  => $pID,
              'date_out'     => null,
              'NOT'          => ['date_use' => null]
           ]
        ])[0];
        return $result['cpt'];
    }


    /**
     * Count the number of old cartridges for the cartridge item $tID.
     *
     * @param integer $tID ID of the cartridge item.
     *
     * @return integer Number of old cartridges counted.
    **/
    public static function getOldNumber($tID)
    {
        $result = self::getAdapter()->request([
           'COUNT'  => 'cpt',
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'cartridgeitems_id'  => $tID,
              'NOT'                => ['date_out' => null]
           ]
        ])->fetchAssociative();
        return $result['cpt'];
    }


    /**
     * count how many old cartbridge for theprinter $pID
     *
     * @since 0.85
     *
     * @param $pID integer: printer identifier.
     *
     * @return integer : number of old cartridge counted.
    **/
    public static function getOldNumberForPrinter($pID)
    {
        $result = self::getAdapter()->request([
           'COUNT'  => 'cpt',
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'printers_id'  => $pID,
              'NOT'          => ['date_out' => null]
           ]
        ])[0];
        return $result['cpt'];
    }


    /**
     * count how many cartbridge unused for the cartridge item $tID
     *
     * @param $tID integer: cartridge item identifier.
     *
     * @return integer : number of cartridge unused counted.
    **/
    public static function getUnusedNumber($tID)
    {
        $result = self::getAdapter()->request([
           'COUNT'  => 'cpt',
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'cartridgeitems_id'  => $tID,
              'date_use'           => null
           ]
        ])->fetchAssociative();
        return $result['cpt'];
    }


    /**
     * Get the translated value for the status of a cartridge based on the use and out date (if any).
     *
     * @param string $date_use  Date of use (May be null or empty)
     * @param string $date_out  Date of delete (May be null or empty)
     *
     * @return string : Translated value for the cartridge status.
    **/
    public static function getStatus($date_use, $date_out)
    {

        if (is_null($date_use) || empty($date_use)) {
            return _nx('cartridge', 'New', 'New', 1);
        }
        if (is_null($date_out) || empty($date_out)) {
            return _nx('cartridge', 'Used', 'Used', 1);
        }
        return _nx('cartridge', 'Worn', 'Worn', 1);
    }


    /**
     * Print out the cartridges of a defined type
     *
     * @param CartridgeItem   $cartitem  The cartridge item
     * @param boolean|integer $show_old  Show old cartridges or not (default 0/false)
     *
     * @return boolean|void
    **/
    public static function showForCartridgeItem(CartridgeItem $cartitem, $show_old = 0)
    {
        global $DB;

        $tID = $cartitem->getField('id');
        if (!$cartitem->can($tID, READ)) {
            return false;
        }
        $canedit = $cartitem->can($tID, UPDATE);

        $where = ['glpi_cartridges.cartridgeitems_id' => $tID];
        $order = [
           'glpi_cartridges.date_use ASC',
           'glpi_cartridges.date_out DESC',
           'glpi_cartridges.date_in'
        ];

        if (!$show_old) { // NEW
            $where['glpi_cartridges.date_out'] = null;
            $order = [
               'glpi_cartridges.date_out ASC',
               'glpi_cartridges.date_use ASC',
               'glpi_cartridges.date_in'
            ];
        } else { //OLD
            $where['NOT'] = ['glpi_cartridges.date_out' => null];
        }

        $stock_time       = 0;
        $use_time         = 0;
        $pages_printed    = 0;
        $nb_pages_printed = 0;

        $iterator = self::getAdapter()->request([
           'SELECT' => [
              'glpi_cartridges.*',
              'glpi_printers.id AS "printID"',
              'glpi_printers.name AS "printname"',
              'glpi_printers.init_pages_counter'
           ],
           'FROM'   => self::gettable(),
           'LEFT JOIN' => [
              'glpi_printers'   => [
                 'FKEY'   => [
                    self::getTable()  => 'printers_id',
                    'glpi_printers'   => 'id'
                 ]
              ]
           ],
           'WHERE'     => $where,
           'ORDER'     => $order
        ]);

        $number = $iterator->rowCount();

        $massiveActionId = 'tableForCartridgeCartridges' . rand();
        if ($canedit && $number) {
            $actions = [
               'purge' => _x('button', 'Delete permanently'),
               'Infocom' . MassiveAction::CLASS_ACTION_SEPARATOR . 'activate'
                  => __('Enable the financial and administrative information')
            ];
            if (!$show_old) {
                $actions['Cartridge' . MassiveAction::CLASS_ACTION_SEPARATOR . 'backtostock']
                      = __('Back to stock');
            }
            $massiveactionparams = [
               'specific_actions' => $actions,
               'container'        => $massiveActionId,
               'display_arrow' => false,
            ];
            Html::showMassiveActions($massiveactionparams);
        }
        if (!$show_old) {
            self::getCount($tID, -1);
        } else { // Old
            echo __('Worn cartridges');
        }

        $fields = [
           __('ID'),
           _x('item', 'State'),
           __('Add date'),
           __('Use date'),
           __('Used on'),
        ];
        if ($show_old) {
            $fields[] = __('End date');
            $fields[] = __('Printer counter');
        }
        $fields[] = __('Financial and administrative information');

        $values = [];
        $massive_action = [];
        while ($data = $iterator->fetchAssociative()) {
            $date_in  = Html::convDate($data["date_in"]);
            $date_use = Html::convDate($data["date_use"]);
            $date_out = Html::convDate($data["date_out"]);
            $printer  = $data["printers_id"];

            $newValue = [];

            $newValue[] = $data['id'];
            $newValue[] = self::getStatus($data["date_use"], $data["date_out"]);
            $newValue[] = $date_in;
            $newValue[] = $date_use;
            if (!is_null($date_use)) {
                if ($data["printID"] > 0) {
                    $printname = $data["printname"];
                    if ($_SESSION['glpiis_ids_visible'] || empty($printname)) {
                        $printname = sprintf(__('%1$s (%2$s)'), $printname, $data["printID"]);
                    }
                    $newValue[] = "<a href='" . Printer::getFormURLWithID($data["printID"]) . "'><span class='b'>" . $printname . "</span></a>";
                } else {
                    $newValue[] = NOT_AVAILABLE;
                }
                $tmp_dbeg       = explode("-", $data["date_in"]);
                $tmp_dend       = explode("-", $data["date_use"]);
                $stock_time_tmp = mktime(0, 0, 0, $tmp_dend[1], $tmp_dend[2], $tmp_dend[0])
                                  - mktime(0, 0, 0, $tmp_dbeg[1], $tmp_dbeg[2], $tmp_dbeg[0]);
                $stock_time    += $stock_time_tmp;
            } else {
                $newValue[] = '';
            }
            if ($show_old) {
                $newValue[] = $date_out;
                $tmp_dbeg      = explode("-", $data["date_use"]);
                $tmp_dend      = explode("-", $data["date_out"]);
                $use_time_tmp  = mktime(0, 0, 0, $tmp_dend[1], $tmp_dend[2], $tmp_dend[0])
                                  - mktime(0, 0, 0, $tmp_dbeg[1], $tmp_dbeg[2], $tmp_dbeg[0]);
                $use_time     += $use_time_tmp;
            }

            if ($show_old) {
                // Get initial counter page
                if (!isset($pages[$printer])) {
                    $pages[$printer] = $data['init_pages_counter'];
                }
                if ($pages[$printer] < $data['pages']) {
                    $pages_printed   += $data['pages'] - $pages[$printer];
                    $nb_pages_printed++;
                    $pp               = $data['pages'] - $pages[$printer];
                    $newValue[] = sprintf(_n('%d printed page', '%d printed pages', $pp), $pp);
                    $pages[$printer]  = $data['pages'];
                } elseif ($data['pages'] != 0) {
                    $newValue[] = __('Counter error');
                }
            }
            ob_start();
            Infocom::showDisplayLink('Cartridge', $data["id"]);
            $newValue[] = ob_get_clean();
            $values[] = $newValue;
            $massive_action[] = sprintf('item[%s][%s]', self::class, $data['id']);
        }

        renderTwigTemplate('table.twig', [
           'id' => $massiveActionId,
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action,
        ]);
    }


    /**
     * Print out a link to add directly a new cartridge from a cartridge item.
     *
     * @param $cartitem  CartridgeItem object
     *
     * @return boolean|void
    **/
    public static function showAddForm(CartridgeItem $cartitem)
    {

        $ID = $cartitem->getField('id');
        if (!$cartitem->can($ID, UPDATE)) {
            return false;
        }
        if ($ID > 0) {
            $form = [
               'action' => static::getFormURL(),
               'buttons' => [
                  [
                     'value' => __s('Add cartridges'),
                     'name' => 'add',
                     'class' => 'btn btn-secondary',
                  ]
               ],
               'content' => [
                  '' => [
                     'visible' => true,
                     'inputs' => [
                        [
                           'type' => 'hidden',
                           'name' => 'cartridgeitems_id',
                           'value' => $ID,
                        ],
                        __('Number') => [
                           'type' => 'number',
                           'name' => 'to_add',
                           'value' => 1,
                           'min' => 1,
                           'max' => 100,
                        ]
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }
    }


    /**
     * Show installed cartridges
     *
     * @since 0.84 (before showInstalled)
     *
     * @param Printer         $printer Printer object
     * @param boolean|integer $old     Old cartridges or not? (default 0/false)
     *
     * @return boolean|void
    **/
    public static function showForPrinter(Printer $printer, $old = 0)
    {
        global $DB, $CFG_GLPI;

        $instID = $printer->getField('id');
        if (!self::canView()) {
            return false;
        }
        $canedit = Session::haveRight("cartridge", UPDATE);
        $rand    = mt_rand();

        $where = ['glpi_cartridges.printers_id' => $instID];
        if ($old) {
            $where['NOT'] = ['glpi_cartridges.date_out' => null];
        } else {
            $where['glpi_cartridges.date_out'] = null;
        }
        $request = self::getAdapter()->request([
           'SELECT'    => [
              'glpi_cartridgeitems.id AS tID',
              'glpi_cartridgeitems.is_deleted',
              'glpi_cartridgeitems.ref AS ref',
              'glpi_cartridgeitems.name AS type',
              'glpi_cartridges.id',
              'glpi_cartridges.pages AS pages',
              'glpi_cartridges.date_use AS date_use',
              'glpi_cartridges.date_out AS date_out',
              'glpi_cartridges.date_in AS date_in',
              'glpi_cartridgeitemtypes.name AS typename'
           ],
           'FROM'      => self::getTable(),
           'LEFT JOIN' => [
              'glpi_cartridgeitems'      => [
                 'FKEY'   => [
                    self::getTable()        => 'cartridgeitems_id',
                    'glpi_cartridgeitems'   => 'id'
                 ]
              ],
              'glpi_cartridgeitemtypes'  => [
                 'FKEY'   => [
                    'glpi_cartridgeitems'      => 'cartridgeitemtypes_id',
                    'glpi_cartridgeitemtypes'  => 'id'
                 ]
              ]
           ],
           'WHERE'     => $where,
           'ORDER'     => [
              'glpi_cartridges.date_out ASC',
              'glpi_cartridges.date_use DESC',
              'glpi_cartridges.date_in',
           ]
        ]);
        $results = $request->fetchAllAssociative();
        $number = count($results);

        if ($canedit && !$old) {
            $options = CartridgeItem::dropdownForPrinter($printer);
            $form = [
               'action' => static::getFormURL(),
               'buttons' => [
                  $options ? [
                     'value' => _sx('button', 'Install'),
                     'name' => 'install',
                     'class' => 'btn btn-secondary',
                  ] : []
               ],
               'content' => [
                  '' => [
                     'visible' => true,
                     'inputs' => $options ? [
                        [
                           'type' => 'hidden',
                           'name' => 'printers_id',
                           'value' => $instID,
                        ],
                        CartridgeItem::getTypeName() => [
                           'type' => 'select',
                           'name' => 'cartridgeitems_id',
                           'values' => $options
                        ],
                        __('Number') => [
                           'type' => 'number',
                           'name' => 'nbcart',
                           'value' => 1,
                           'min' => 1,
                           'max' => 5,
                        ]
                     ] : [
                        '' => [
                           'content' => __('No cartridge available'),
                        ]
                     ]
                  ]
               ]
            ];
            renderTwigForm($form);
        }


        $pages = $printer->fields['init_pages_counter'];
        $massiveActionContainerId = 'tableForPrinterCartridges' . rand();
        if ($canedit && $number) {
            if (!$old) {
                $actions = [__CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'uninstall'
                => __('End of life'),
                __CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'backtostock'
                => __('Back to stock')
                ];
            } else {
                $actions = [__CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'updatepages'
                => __('Update printer counter'),
                'purge' => _x('button', 'Delete permanently')];
            }
            $massiveactionparams = [
               'specific_actions' => $actions,
               'container'        => $massiveActionContainerId,
               'extraparams'      => [
                  'maxpages' => $printer->fields['last_pages_counter']
               ],
               'display_arrow' => false,
            ];
            Html::showMassiveActions($massiveactionparams);
        }

        $fields = [
           __('ID'),
           _n('Cartridge model', 'Cartridge models', 1),
           _n('Cartridge type', 'Cartridge types', 1),
           __('Add date'),
           __('Use date'),
        ];
        if ($old != 0) {
            $fields[] = __('End date');
            $fields[] = __('Printer counter');
            $fields[] = __('Printed pages');
        }
        $values = [];
        $massive_action = [];

        $stock_time       = 0;
        $use_time         = 0;
        $pages_printed    = 0;
        $nb_pages_printed = 0;

        foreach ($results as $data) {
            $cart_id    = $data["id"];
            $typename   = $data["typename"];
            $date_in    = Html::convDate($data["date_in"]);
            $date_use   = Html::convDate($data["date_use"]);
            $date_out   = Html::convDate($data["date_out"]);
            $viewitemjs = ($canedit ? "style='cursor:pointer' onClick=\"viewEditCartridge" . $cart_id .
                           "$rand();\"" : '');
            $newValue = [
               $data['id'],
               "<a href=\"" . CartridgeItem::getFormURLWithID($data["tID"]) . "\">" .
               sprintf(__('%1$s - %2$s'), $data["type"], $data["ref"]) . "</a>",
               $typename,
               $date_in,
               $date_use,
            ];
            $tmp_dbeg       = explode("-", $data["date_in"]);
            $tmp_dend       = explode("-", $data["date_use"]);

            $stock_time_tmp = mktime(0, 0, 0, $tmp_dend[1], $tmp_dend[2], $tmp_dend[0])
                              - mktime(0, 0, 0, $tmp_dbeg[1], $tmp_dbeg[2], $tmp_dbeg[0]);
            $stock_time    += $stock_time_tmp;
            if ($old != 0) {
                $newValue[] = $date_out;

                $tmp_dbeg      = explode("-", $data["date_use"]);
                $tmp_dend      = explode("-", $data["date_out"]);

                $use_time_tmp  = mktime(0, 0, 0, $tmp_dend[1], $tmp_dend[2], $tmp_dend[0])
                - mktime(0, 0, 0, $tmp_dbeg[1], $tmp_dbeg[2], $tmp_dbeg[0]);
                $use_time     += $use_time_tmp;

                $newValue[] = $data['pages'];
                $newValue[] = ($pages < $data['pages']) ? $data['pages'] - $pages : '';
            }
            $values[] = $newValue;
            $massive_action[] = sprintf('item[%s][%s]', self::class, $data['id']);
        }
        echo "<p>" . (($old == 0)
           ? __('Used cartridges')
           : __('Worn cartridges')) . "</p>";
        renderTwigTemplate('table.twig', [
           'id' => $massiveActionContainerId,
           'fields' => $fields,
           'values' => $values,
           'massive_action' => $massive_action,
        ]);

        if ($old) { // Print average
            $fields = [
               __('Average time in stock'),
               __('Average time in use'),
               __('Average number of printed pages'),
            ];
            $values = [
               ($number > 0) ? [
                  round($stock_time / $number / 60 / 60 / 24 / 30.5, 1),
                  round($use_time / $number / 60 / 60 / 24 / 30.5, 1),
                  round($pages_printed / ($nb_pages_printed ? $nb_pages_printed : 1)),
               ] : [],
            ];
            renderTwigTemplate('table.twig', [
               'fields' => $fields,
               'values' => $values,
               'minimal' => true,
            ]);
        }
    }


    /**
     * Show form for Cartridge
     * @since 0.84
     *
     * @param integer $ID       Id of the cartridge
     * @param array   $options  Array of possible options:
     *     - parent Object : the printers where the cartridge is used
     *
     * @return boolean False if there was a rights issue. Otherwise, returns true.
     */
    public function showForm($ID, $options = [])
    {

        if (isset($options['parent']) && !empty($options['parent'])) {
            $printer = $options['parent'];
        }
        if (!$this->getFromDB($ID)) {
            return false;
        }
        $printer = new Printer();
        $printer->check($this->getField('printers_id'), UPDATE);

        $cartitem = new CartridgeItem();
        $cartitem->getFromDB($this->getField('cartridgeitems_id'));

        $is_old  = !empty($this->fields['date_out']);
        $is_used = !empty($this->fields['date_use']);

        $options['colspan'] = 2;
        $options['candel']  = false; // Do not permit delete here
        $options['canedit'] = $is_used; // Do not permit edit if cart is not used
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . _n('Printer', 'Printers', 1) . "</td><td>";
        echo $printer->getLink();
        echo "<input type='hidden' name='printers_id' value='" . $this->getField('printers_id') . "'>\n";
        echo "<input type='hidden' name='cartridgeitems_id' value='" .
               $this->getField('cartridgeitems_id') . "'>\n";
        echo "</td>\n";
        echo "<td>" . _n('Cartridge model', 'Cartridge models', 1) . "</td>";
        echo "<td>" . $cartitem->getLink() . "</td></tr>\n";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Add date') . "</td>";
        echo "<td>" . Html::convDate($this->fields["date_in"]) . "</td>";

        echo "<td>" . __('Use date') . "</td><td>";
        if ($is_used && !$is_old) {
            Html::showDateField("date_use", ['value'      => $this->fields["date_use"],
                                                  'maybeempty' => false,
                                                  'canedit'    => true,
                                                  'min'        => $this->fields["date_in"]]);
        } else {
            echo Html::convDate($this->fields["date_use"]);
        }
        echo "</td></tr>\n";

        if ($is_old) {
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __('End date') . "</td><td>";
            Html::showDateField("date_out", ['value'      => $this->fields["date_out"],
                                                  'maybeempty' => false,
                                                  'canedit'    => true,
                                                  'min'        => $this->fields["date_use"]]);
            echo "</td>";
            echo "<td>" . __('Printer counter') . "</td><td>";
            echo "<input type='text' name='pages' value=\"" . $this->fields['pages'] . "\">";
            echo "</td></tr>\n";
        }
        $this->showFormButtons($options);

        return true;
    }


    /**
     * Get notification parameters by entity
     *
     * @param integer $entity The entity (default 0)
     * @return array Array of notification parameters
     */
    public static function getNotificationParameters($entity = 0)
    {
        global $CFG_GLPI;

        //Look for parameters for this entity
        $request = self::getAdapter()->request([
           'SELECT' => ['cartridges_alert_repeat'],
           'FROM'   => 'glpi_entities',
           'WHERE'  => ['id' => $entity]
        ]);
        $results = $request->fetchAllAssociative();
        if (!count($results)) {
            //No specific parameters defined, taking global configuration params
            return $CFG_GLPI['cartridges_alert_repeat'];
        } else {
            $data = $results[0];
            //This entity uses global parameters -> return global config
            if ($data['cartridges_alert_repeat'] == -1) {
                return $CFG_GLPI['cartridges_alert_repeat'];
            }
            // ELSE Special configuration for this entity
            return $data['cartridges_alert_repeat'];
        }
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate && self::canView()) {
            $nb = 0;
            switch ($item->getType()) {
                case 'Printer':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = self::countForPrinter($item);
                    }
                    return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);

                case 'CartridgeItem':
                    if ($_SESSION['glpishow_count_on_tabs']) {
                        $nb = self::countForCartridgeItem($item);
                    }
                    return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
            }
        }
        return '';
    }


    /**
     * Count the number of cartridges associated with the given cartridge item.
     * @param CartridgeItem $item CartridgeItem object
     * @return integer
     */
    public static function countForCartridgeItem(CartridgeItem $item)
    {

        return countElementsInTable(['glpi_cartridges'], ['glpi_cartridges.cartridgeitems_id' => $item->getField('id')]);
    }


    /**
     * Count the number of cartridges associated with the given printer.
     * @param Printer $item Printer object
     * @return integer
     */
    public static function countForPrinter(Printer $item)
    {

        return countElementsInTable(['glpi_cartridges'], ['glpi_cartridges.printers_id' => $item->getField('id')]);
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'Printer':
                self::showForPrinter($item);
                self::showForPrinter($item, 1);
                return true;

            case 'CartridgeItem':
                self::showAddForm($item);
                self::showForCartridgeItem($item);
                self::showForCartridgeItem($item, 1);
                return true;
        }
    }

    public function getRights($interface = 'central')
    {
        $ci = new CartridgeItem();
        return $ci->getRights($interface);
    }


    public static function getIcon()
    {
        return "fas fa-fill-drip";
    }
}
