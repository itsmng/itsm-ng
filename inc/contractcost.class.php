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

/// ContractCost class
/// since version 0.84
class ContractCost extends CommonDBChild
{
    // From CommonDBChild
    public static $itemtype = 'Contract';
    public static $items_id = 'contracts_id';
    public $dohistory       = true;


    public static function getTypeName($nb = 0)
    {
        return _n('Cost', 'Costs', $nb);
    }


    public function prepareInputForAdd($input)
    {

        if (
            !empty($input['begin_date'])
            && (empty($input['end_date'])
                || ($input['end_date'] == 'NULL')
                || ($input['end_date'] < $input['begin_date']))
        ) {
            $input['end_date'] = $input['begin_date'];
        }

        return parent::prepareInputForAdd($input);
    }


    public function prepareInputForUpdate($input)
    {

        if (
            !empty($input['begin_date'])
            && (empty($input['end_date'])
                || ($input['end_date'] == 'NULL')
                || ($input['end_date'] < $input['begin_date']))
        ) {
            $input['end_date'] = $input['begin_date'];
        }

        return parent::prepareInputForUpdate($input);
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        // can exists for template
        if (
            ($item->getType() == 'Contract')
            && Contract::canView()
        ) {
            $nb = 0;
            if ($_SESSION['glpishow_count_on_tabs']) {
                $nb = countElementsInTable('glpi_contractcosts', ['contracts_id' => $item->getID()]);
            }
            return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        self::showForContract($item, $withtemplate);
        return true;
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
           'field'              => 'name',
           'name'               => __('Title'),
           'searchtype'         => 'contains',
           'datatype'           => 'itemlink',
           'massiveaction'      => false,
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '2',
           'table'              => $this->getTable(),
           'field'              => 'id',
           'name'               => __('ID'),
           'massiveaction'      => false,
           'datatype'           => 'number'
        ];

        $tab[] = [
           'id'                 => '16',
           'table'              => $this->getTable(),
           'field'              => 'comment',
           'name'               => __('Comments'),
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '12',
           'table'              => $this->getTable(),
           'field'              => 'begin_date',
           'name'               => __('Begin date'),
           'datatype'           => 'datetime'
        ];

        $tab[] = [
           'id'                 => '10',
           'table'              => $this->getTable(),
           'field'              => 'end_date',
           'name'               => __('End date'),
           'datatype'           => 'datetime'
        ];

        $tab[] = [
           'id'                 => '14',
           'table'              => $this->getTable(),
           'field'              => 'cost',
           'name'               => _n('Cost', 'Costs', 1),
           'datatype'           => 'decimal'
        ];

        $tab[] = [
           'id'                 => '18',
           'table'              => 'glpi_budgets',
           'field'              => 'name',
           'name'               => Budget::getTypeName(1),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '80',
           'table'              => 'glpi_entities',
           'field'              => 'completename',
           'name'               => Entity::getTypeName(1),
           'massiveaction'      => false,
           'datatype'           => 'dropdown'
        ];

        return $tab;
    }


    /**
     * Duplicate all costs from a contract template to its clone
     *
     * @deprecated 9.5
     * @since 0.84
     *
     * @param $oldid
     * @param $newid
    **/
    public static function cloneContract($oldid, $newid)
    {
        global $DB;

        Toolbox::deprecated('Use clone');
        $iterator = $DB->request([
           'FROM'   => self::getTable(),
           'WHERE'  => ['contracts_id' => $oldid]
        ]);
        while ($data = $iterator->next()) {
            $cd                   = new self();
            unset($data['id']);
            $data['contracts_id'] = $newid;
            $data                 = Toolbox::addslashes_deep($data);
            $cd->add($data);
        }
    }


    /**
     * Init cost for creation based on previous cost
    **/
    public function initBasedOnPrevious()
    {

        $contract = new Contract();
        if (
            !isset($this->fields['contracts_id'])
            || !$contract->getFromDB($this->fields['contracts_id'])
        ) {
            return false;
        }

        $lastdata = $this->getLastCostForContract($this->fields['contracts_id']);

        if (isset($lastdata['end_date'])) {
            $this->fields['begin_date'] = $lastdata['end_date'];
        }
        if (isset($lastdata['cost'])) {
            $this->fields['cost'] = $lastdata['cost'];
        }
        if (isset($lastdata['name'])) {
            $this->fields['name'] = $lastdata['name'];
        }
        if (isset($lastdata['budgets_id'])) {
            $this->fields['budgets_id'] = $lastdata['budgets_id'];
        }
    }

    /**
     * Get last datas for a contract
     *
     * @param $contracts_id        integer  ID of the contract
    **/
    public function getLastCostForContract($contracts_id)
    {
        global $DB;

        $iterator = $DB->request([
           'FROM'   => $this->getTable(),
           'WHERE'  => ['contracts_id' => $contracts_id],
           'ORDER'  => ['end_date DESC', 'id DESC']
        ]);
        if ($result = $iterator->next()) {
            return $result;
        }

        return [];
    }

    /**
     * Print the contract cost form
     *
     * @param $ID        integer  ID of the item
     * @param $options   array    options used
    **/
    public function showForm($ID, $options = [])
    {

        if ($ID > 0) {
            $this->check($ID, READ);
        } else {
            // Create item
            $options['contracts_id'] = $options['parent']->getField('id');
            $this->check(-1, CREATE, $options);
            $this->initBasedOnPrevious();
        }

        $form = [
            'action' => $this->getFormURL(),
            'itemtype' => self::class,
            'content' => [
                $this->getTypeName() => [
                    'visible' => true,
                    'inputs' => [
                        [
                            'type' => 'hidden',
                            'name' => 'contracts_id',
                            'value' => $this->fields['contracts_id']
                        ],
                        __('Name') => [
                            'type' => 'text',
                            'name' => 'name',
                            'value' => $this->fields['name'],
                        ],
                        _n('Cost', 'Costs', 1) => [
                            'type' => 'text',
                            'name' => 'cost',
                            'value' => Html::formatNumber($this->fields["cost"], true),
                        ],
                        Budget::getTypeName(1) => [
                            'type' => 'select',
                            'name' => 'budgets_id',
                            'itemtype' => Budget::class,
                            'value' => $this->fields['budgets_id'],
                        ],
                        __('Begin date') => [
                            'type' => 'date',
                            'name' => 'begin_date',
                            'value' => $this->fields['begin_date'],
                            'col_lg' => 6,
                        ],
                        __('End date') => [
                            'type' => 'date',
                            'name' => 'end_date',
                            'value' => $this->fields['end_date'],
                            'col_lg' => 6,
                        ],
                        __('Comments') => [
                            'type' => 'textarea',
                            'name' => 'comment',
                            'value' => $this->fields["comment"],
                            'col_lg' => 12,
                        ],
                    ]
                ]
            ]
        ];
        if ($ID <= 0) {
            $section = $form['content'][array_key_first($form['content'])];
            $form['content'] = [__('Add a new cost') => $section];
            $form['buttons'] = [['type' => 'submit', 'name' => 'add', 'value' => __('Add a new cost'),
                'class' => 'btn btn-primary', 'icon' => 'fas fa-plus']];
        }
        renderTwigForm($form, '', $this->fields);

        return true;
    }


    /**
     * Print the contract costs
     *
     * @param Contract $contract
     * @param integer  $withtemplate Template or basic item
     *
     * @return void
    **/
    public static function showForContract(Contract $contract, $withtemplate = 0)
    {
        global $DB, $CFG_GLPI;
        $ID = $contract->getID();
        if (!$contract->getFromDB($ID) || !$contract->can($ID, READ)) {
            return false;
        }
        $canedit = $contract->can($ID, UPDATE) && ($withtemplate != 2);
        $container = 'ContractCostEditor_' . $ID;
        if ($canedit) {
            $cost = new self();
            $cost->showForm(-1, ['parent' => $contract]);
            echo '<div id="' . $container . '"></div>';
        }
        $values = [];
        $total = 0;
        Session::initNavigateListItems(__CLASS__, sprintf(__('%1$s = %2$s'), Contract::getTypeName(1), $contract->getName()));
        foreach ($DB->request(['FROM' => self::getTable(), 'WHERE' => ['contracts_id' => $ID], 'ORDER' => ['begin_date', 'id']]) as $data) {
            $name = htmlspecialchars($data['name'] ?: '(' . $data['id'] . ')', ENT_QUOTES, 'UTF-8', false);
            if ($canedit) {
                $function = 'ContractCostEdit_' . $ID . '_' . $data['id'];
                echo '<script>function ' . $function . '() {';
                Ajax::updateItemJsCode($container, $CFG_GLPI['root_doc'] . '/ajax/viewsubitem.php', [
                    'type' => __CLASS__, 'parenttype' => 'Contract', 'contracts_id' => $ID, 'id' => $data['id']]);
                echo '}</script>';
                $name = '<a href="#" onclick="' . $function . '(); return false;">' . $name . '</a>';
            }
            $name .= Html::showToolTip(htmlspecialchars($data['comment'] ?? '', ENT_QUOTES, 'UTF-8', false), ['display' => false]);
            $values[] = [$name, Html::convDate($data['begin_date']), Html::convDate($data['end_date']),
                htmlspecialchars(Dropdown::getDropdownName('glpi_budgets', $data['budgets_id']), ENT_QUOTES, 'UTF-8', false),
                Html::formatNumber($data['cost'])];
            $total += $data['cost'];
            Session::addToNavigateListItems(__CLASS__, $data['id']);
        }
        renderTwigTemplate('table.twig', ['id' => 'ContractCostTable_' . $ID, 'state_key' => 'ContractCost_for_Contract',
            'pageSize' => (int) ($_SESSION['glpilist_limit'] ?? 15),
            'fields' => [__('Name'), __('Begin date'), __('End date'), Budget::getTypeName(1), _n('Cost', 'Costs', 1)],
            'values' => $values]);
        echo '<p class="text-end fw-bold mt-3">' . __('Total cost') . ': ' . Html::formatNumber($total) . '</p>';
    }
}
