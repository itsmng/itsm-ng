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

class RuleAction extends CommonDBChild
{
    // From CommonDBChild
    public static $itemtype        = "Rule";
    public static $items_id        = 'rules_id';
    public $dohistory              = true;
    public $auto_message_on_action = false;

    /**
     * @since 0.84
    **/
    public function getForbiddenStandardMassiveAction()
    {

        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    /**
     * @param $rule_type
    **/
    public function __construct($rule_type = 'Rule')
    {
        static::$itemtype = $rule_type;
    }


    /**
     * @since 0.84.3
     *
     * @see CommonDBTM::post_getFromDB()
     */
    public function post_getFromDB()
    {

        // Get correct itemtype if defult one is used
        if (static::$itemtype == 'Rule') {
            $rule = new Rule();
            if ($rule->getFromDB($this->fields['rules_id'])) {
                static::$itemtype = $rule->fields['sub_type'];
            }
        }
    }


    /**
     * Get title used in rule
     *
     * @param $nb  integer  (default 0)
     *
     * @return Title of the rule
    **/
    public static function getTypeName($nb = 0)
    {
        return _n('Action', 'Actions', $nb);
    }

    protected function computeFriendlyName()
    {

        if ($rule = getItemForItemtype(static::$itemtype)) {
            return Html::clean($rule->getMinimalActionText($this->fields));
        }
        return '';
    }

    /**
     * @since 0.84
     *
     * @see CommonDBChild::post_addItem()
    **/
    public function post_addItem()
    {

        parent::post_addItem();
        if (
            isset($this->input['rules_id'])
            && ($realrule = Rule::getRuleObjectByID($this->input['rules_id']))
        ) {
            $realrule->update(['id'       => $this->input['rules_id'],
                                    'date_mod' => $_SESSION['glpi_currenttime']]);
        }
    }


    /**
     * @since 0.84
     *
     * @see CommonDBTM::post_purgeItem()
    **/
    public function post_purgeItem()
    {

        parent::post_purgeItem();
        if (
            isset($this->fields['rules_id'])
            && ($realrule = Rule::getRuleObjectByID($this->fields['rules_id']))
        ) {
            $realrule->update(['id'       => $this->fields['rules_id'],
                                    'date_mod' => $_SESSION['glpi_currenttime']]);
        }
    }


    /**
     * @since 0.84
    **/
    public function prepareInputForAdd($input)
    {

        if (!isset($input['field']) || empty($input['field'])) {
            return false;
        }
        return parent::prepareInputForAdd($input);
    }


    public function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
           'id'                 => '1',
           'table'              => $this->getTable(),
           'field'              => 'action_type',
           'name'               => self::getTypeName(1),
           'massiveaction'      => false,
           'datatype'           => 'specific',
           'additionalfields'   => ['rules_id']
        ];

        $tab[] = [
           'id'                 => '2',
           'table'              => $this->getTable(),
           'field'              => 'field',
           'name'               => _n('Field', 'Fields', Session::getPluralNumber()),
           'massiveaction'      => false,
           'datatype'           => 'specific',
           'additionalfields'   => ['rules_id']
        ];

        $tab[] = [
           'id'                 => '3',
           'table'              => $this->getTable(),
           'field'              => 'value',
           'name'               => __('Value'),
           'massiveaction'      => false,
           'datatype'           => 'specific',
           'additionalfields'   => ['rules_id'],
           'autocomplete'       => true,
        ];

        return $tab;
    }


    /**
     * @since 0.84
     *
     * @param $field
     * @param $values
     * @param $options   array
    **/
    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'field':
                $generic_rule = new Rule();
                if (
                    isset($values['rules_id'])
                    && !empty($values['rules_id'])
                    && $generic_rule->getFromDB($values['rules_id'])
                ) {
                    if ($rule = getItemForItemtype($generic_rule->fields["sub_type"])) {
                        return $rule->getAction($values[$field]);
                    }
                }
                break;

            case 'action_type':
                return self::getActionByID($values[$field]);

            case 'value':
                if (!isset($values["field"]) || !isset($values["action_type"])) {
                    return NOT_AVAILABLE;
                }
                $generic_rule = new Rule();
                if (
                    isset($values['rules_id'])
                    && !empty($values['rules_id'])
                    && $generic_rule->getFromDB($values['rules_id'])
                ) {
                    if ($rule = getItemForItemtype($generic_rule->fields["sub_type"])) {
                        return $rule->getCriteriaDisplayPattern(
                            $values["criteria"],
                            $values["condition"],
                            $values[$field]
                        );
                    }
                }
                break;
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
    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;
        switch ($field) {
            case 'field':
                $generic_rule = new Rule();
                if (
                    isset($values['rules_id'])
                    && !empty($values['rules_id'])
                    && $generic_rule->getFromDB($values['rules_id'])
                ) {
                    if ($rule = getItemForItemtype($generic_rule->fields["sub_type"])) {
                        $options['value'] = $values[$field];
                        $options['name']  = $name;
                        return $rule->dropdownActions($options);
                    }
                }
                break;

            case 'action_type':
                $generic_rule = new Rule();
                if (
                    isset($values['rules_id'])
                    && !empty($values['rules_id'])
                    && $generic_rule->getFromDB($values['rules_id'])
                ) {
                    return self::dropdownActions(['subtype'     => $generic_rule->fields["sub_type"],
                                                       'name'        => $name,
                                                       'value'       => $values[$field],
                                                       'alreadyused' => false,
                                                       'display'     => false]);
                }
                break;

            case 'pattern':
                if (!isset($values["field"]) || !isset($values["action_type"])) {
                    return NOT_AVAILABLE;
                }
                $generic_rule = new Rule();
                if (
                    isset($values['rules_id'])
                    && !empty($values['rules_id'])
                    && $generic_rule->getFromDB($values['rules_id'])
                ) {
                    if ($rule = getItemForItemtype($generic_rule->fields["sub_type"])) {
                        /// TODO review it : need to pass display param and others...
                        return $rule->displayActionSelectPattern($values);
                    }
                }
                break;
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }


    /**
     * Get all actions for a given rule
     *
     * @param $ID the rule_description ID
     *
     * @return an array of RuleAction objects
    **/
    public function getRuleActions($ID)
    {
        global $DB;

        $iterator = $DB->request([
           'FROM'   => $this->getTable(),
           'WHERE'  => [static::$items_id => $ID],
           'ORDER'  => 'id'
        ]);

        $rules_actions = [];
        while ($rule = $iterator->next()) {
            $tmp             = new self();
            $tmp->fields     = $rule;
            $rules_actions[] = $tmp;
        }
        return $rules_actions;
    }


    /**
     * Add an action
     *
     * @param $action    action type
     * @param $ruleid    rule ID
     * @param $field     field name
     * @param $value     value
    **/
    public function addActionByAttributes($action, $ruleid, $field, $value)
    {

        $input = [
           'action_type'     => $action,
           'field'           => $field,
           'value'           => $value,
           static::$items_id => $ruleid,
        ];
        $this->add($input);
    }


    /**
     * Display a dropdown with all the possible actions
     *
     * @param $options   array of possible options:
     *    - subtype
     *    - name
     *    - field
     *    - value
     *    - alreadyused
     *    - display
    **/
    public static function dropdownActions($options = [])
    {

        $p = [
           'subtype'     => '',
           'name'        => '',
           'field'       => '',
           'value'       => '',
           'alreadyused' => false,
           'display'     => true,
        ];

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $p[$key] = $val;
            }
        }

        if ($rule = getItemForItemtype($p['subtype'])) {
            $actions_options = $rule->getAllActions();
            $actions         = ["assign"];
            // Manage permit several.
            $field = $p['field'];
            if ($p['alreadyused']) {
                if (!isset($actions_options[$field]['permitseveral'])) {
                    return false;
                }
                $actions = $actions_options[$field]['permitseveral'];
            } else {
                if (isset($actions_options[$field]['force_actions'])) {
                    $actions = $actions_options[$field]['force_actions'];
                }
            }

            $elements = [];
            foreach ($actions as $action) {
                $elements[$action] = self::getActionByID($action);
            }

            return Dropdown::showFromArray($p['name'], $elements, ['value'   => $p['value'],
                                                                        'display' => $p['display']]);
        }
    }


    public static function getActions()
    {

        return ['assign'              => __('Assign'),
                     'append'              => __('Add'),
                     'regex_result'        => __('Assign the value from regular expression'),
                     'append_regex_result' => __('Add the result of regular expression'),
                     'affectbyip'          => __('Assign: equipment by IP address'),
                     'affectbyfqdn'        => __('Assign: equipment by name + domain'),
                     'affectbymac'         => __('Assign: equipment by MAC address'),
                     'compute'             => __('Recalculate'),
                     'do_not_compute'      => __('Do not calculate'),
                     'send'                => __('Send'),
                     'add_validation'      => __('Send'),
                     'fromuser'            => __('Copy from user'),
                     'defaultfromuser'     => __('Copy default from user'),
                     'fromitem'            => __('Copy from item')];
    }


    /**
     * @param $ID
    **/
    public static function getActionByID($ID)
    {

        $actions = self::getActions();
        if (isset($actions[$ID])) {
            return $actions[$ID];
        }
        return '';
    }


    /**
     * @param $action
     * @param $regex_result
    **/
    public static function getRegexResultById($action, $regex_result)
    {

        $results = [];

        if (count($regex_result) > 0) {
            if (preg_match_all("/#([0-9])/", $action, $results) > 0) {
                foreach ($results[1] as $result) {
                    $action = str_replace(
                        "#$result",
                        (isset($regex_result[$result]) ? $regex_result[$result] : ''),
                        $action
                    );
                }
            }
        }
        return $action;
    }


    /**
     * @param $rules_id
     * @param $sub_type
    **/
    public function getAlreadyUsedForRuleID($rules_id, $sub_type)
    {
        global $DB;

        if ($rule = getItemForItemtype($sub_type)) {
            $actions_options = $rule->getAllActions();

            $actions = [];
            $iterator = $DB->request([
               'SELECT' => 'field',
               'FROM'   => $this->getTable(),
               'WHERE'  => [static::$items_id => $rules_id],
            ]);

            while ($action = $iterator->next()) {
                if (
                    isset($actions_options[$action["field"]])
                     && ($action["field"] != 'validate_groups_id')
                     && ($action["field"] != 'validate_users_id')
                     && ($action["field"] != 'affectobject')
                ) {
                    $actions[$action["field"]] = $action["field"];
                }
            }
            return $actions;
        }
    }


    /**
     * @param $options   array
    **/
    public function displayActionSelectPattern($options = [])
    {

        $display = false;

        $param = [
           'value' => '',
        ];
        if (isset($options['value'])) {
            $param['value'] = $options['value'];
        }

        switch ($options["action_type"]) {
            //If a regex value is used, then always display an autocompletiontextfield
            case "regex_result":
            case "append_regex_result":
                renderTwigTemplate('macros/input.twig', [
                    'type' => 'text',
                    'name' => 'value',
                    'value' => $param['value'],
                ]);
                break;

            case 'fromuser':
            case 'defaultfromuser':
            case 'fromitem':
                renderTwigTemplate('macros/input.twig', [
                    'type' => 'checkbox',
                    'name' => 'value',
                    'value' => $param['value'],
                ]);
                $display = true;
                break;

            default:
                $actions = Rule::getActionsByType($options["sub_type"]);
                if (isset($actions[$options["field"]]['type'])) {
                    switch ($actions[$options["field"]]['type']) {
                        case "dropdown":
                            $table   = $actions[$options["field"]]['table'];
                            $param['name'] = "value";
                            if (isset($actions[$options["field"]]['condition'])) {
                                $param['condition'] = $actions[$options["field"]]['condition'];
                            }
                            renderTwigTemplate('macros/input.twig', [
                                'type' => 'select',
                                'name' => 'value',
                                'value' => $param['value'],
                                'values' => getOptionForItems(getItemTypeForTable($table), $param['condition'] ?? []),
                            ]);
                            $display = true;
                            break;

                        case "dropdown_tickettype":
                            renderTwigTemplate('macros/input.twig', [
                                'type' => 'select',
                                'name' => 'value',
                                'value' => $param['value'],
                                'values' => Ticket::getTypes(),
                            ]);
                            $display = true;
                            break;

                        case "dropdown_assign":
                            $param['name']  = 'value';
                            $param['right'] = 'own_ticket';
                            renderTwigTemplate('macros/input.twig', [
                                'type' => 'select',
                                'name' => 'value',
                                'value' => $param['value'],
                                'values' => getOptionsForUsers('own_ticket'),
                            ]);
                            $display = true;
                            break;

                        case "dropdown_users":
                            $param['name']  = 'value';
                            $param['right'] = 'all';
                            renderTwigTemplate('macros/input.twig', [
                                'type' => 'select',
                                'name' => 'value',
                                'value' => $param['value'],
                                'values' => getOptionsForUsers('all'),
                            ]);
                            $display = true;
                            break;

                        case "dropdown_urgency":
                            $param['name']  = 'value';
                            renderTwigTemplate('macros/input.twig', [
                             'type' => 'select',
                             'name' => 'value',
                             'values' => [ 3 => Ticket::getUrgencyName(3) ],
                             'value' => $param['value'],
                            ]);
                            $display = true;
                            break;

                        case "dropdown_impact":
                            $param['name']  = 'value';
                            renderTwigTemplate('macros/input.twig', [
                             'type' => 'select',
                             'name' => 'value',
                             'values' => [ 3 => Ticket::getImpactName(3) ],
                             'value' => $param['value'],
                            ]);
                            $display = true;
                            break;

                        case "dropdown_priority":
                            if ($_POST["action_type"] != 'compute') {
                                $param['name']  = 'value';
                                renderTwigTemplate('macros/input.twig', [
                                 'type' => 'select',
                                 'name' => 'value',
                                 'values' => [
                                     6 => Ticket::getPriorityName(6),
                                     5 => Ticket::getPriorityName(5),
                                     4 => Ticket::getPriorityName(4),
                                     3 => Ticket::getPriorityName(3),
                                     2 => Ticket::getPriorityName(2),
                                     1 => Ticket::getPriorityName(1),
                                 ],
                                 'value' => $param['value'],
                                ]);
                            }
                            $display = true;
                            break;

                        case "dropdown_status":
                            $param['name']  = 'value';
                            renderTwigTemplate('macros/input.twig', [
                             'type' => 'select',
                             'name' => 'value',
                             'values' => Ticket::getAllStatusArray(),
                             'value' => $param['value'],
                            ]);
                            $display = true;
                            break;

                        case "yesonly":
                        case "yesno":
                            renderTwigTemplate('macros/input.twig', [
                             'type' => 'checkbox',
                             'name' => 'value',
                             'value' => $param['value'],
                            ]);
                            $display = true;
                            break;

                        case "dropdown_management":
                            $param['name']                 = 'value';
                            $param['management_restrict']  = 2;
                            $param['withtemplate']         = false;
                            Dropdown::showGlobalSwitch(0, $param);
                            $display = true;
                            break;

                        case "dropdown_users_validate":
                            $used = [];
                            if ($item = getItemForItemtype($options["sub_type"])) {
                                $rule_data = getAllDataFromTable(
                                    self::getTable(),
                                    [
                                      'action_type'           => 'add_validation',
                                      'field'                 => 'validate_users_id',
                                      $item->getRuleIdField() => $options[$item->getRuleIdField()]
                                    ]
                                );

                                foreach ($rule_data as $data) {
                                    $used[] = $data['value'];
                                }
                            }
                            $options = getOptionsForUsers(['validate_incident','validate_request']);
                            foreach ($used as $value) {
                                unset($options[$value]);
                            }
                            renderTwigTemplate('macros/input.twig', [
                                'type' => 'select',
                                'name' => 'value',
                                'values' => $options,
                                'value' => $param['value'],
                            ]);
                            $display        = true;
                            break;

                        case "dropdown_groups_validate":
                            $used = [];
                            if ($item = getItemForItemtype($options["sub_type"])) {
                                $rule_data = getAllDataFromTable(
                                    self::getTable(),
                                    [
                                      'action_type'           => 'add_validation',
                                      'field'                 => 'groups_id_validate',
                                      $item->getRuleIdField() => $options[$item->getRuleIdField()]
                                    ]
                                );
                                foreach ($rule_data as $data) {
                                    $used[] = $data['value'];
                                }
                            }

                            $param['name']      = 'value';
                            $param['condition'] = [new QuerySubQuery([
                               'SELECT' => ['COUNT' => ['users_id']],
                               'FROM'   => 'glpi_groups_users',
                               'WHERE'  => ['groups_id' => new \QueryExpression('glpi_groups.id')]
                            ])];
                            $param['right']     = ['validate_incident', 'validate_request'];
                            $param['used']      = $used;
                            renderTwigTemplate('macros/input.twig', [
                                'type' => 'select',
                                'name' => 'value',
                                'values' => getOptionForItems('Group', $param['condition'] ?? []),
                                'value' => $param['value'],
                            ]);
                            $display            = true;
                            break;

                        case "dropdown_validation_percent":
                            $ticket = new Ticket();
                            echo $ticket->getValueToSelect('validation_percent', 'value', $param['value']);
                            $display       = true;
                            break;

                        default:
                            if ($rule = getItemForItemtype($options["sub_type"])) {
                                $display = $rule->displayAdditionalRuleAction($actions[$options["field"]], $param['value']);
                            }
                            break;
                    }
                }

                if (!$display) {
                    renderTwigTemplate('macros/input.twig', [
                        'type' => 'text',
                        'name' => 'value',
                        'value' => $param['value'],
                    ]);
                }
        }
    }

    /** form for rule action
     *
     * @since 0.85
     *
     * @param $ID      integer : Id of the action
     * @param $options array of possible options:
     *     - rule Object : the rule
    **/
    public function showForm($ID, $options = [])
    {
        global $CFG_GLPI;

        // Yllen: you always have parent for action
        $rule = $options['parent'];

        if ($ID > 0) {
            $this->check($ID, READ);
        } else {
            // Create item
            $options[static::$items_id] = $rule->getField('id');

            //force itemtype of parent
            static::$itemtype = get_class($rule);

            $this->check(-1, CREATE, $options);
        }

        $used = $this->getAlreadyUsedForRuleID($this->fields[static::$items_id], $rule->getType());
        if (
            $ID
            && isset($used[$this->fields['field']])
        ) {
            unset($used[$this->fields['field']]);
        }
        $rand = rand();

        $form = [
           'action' => $this->getFormURL(),
           'buttons' => [
              [
                 'name'  => 'add',
                 'value' => __('Add'),
                 'class' => 'btn btn-secondary',
              ]
           ],
           'content' => [
              '' => [
                 'visible' => true,
                 'inputs' => [
                    [
                       'type' => 'hidden',
                       'name' => $rule->getRuleIdField(),
                       'value' => $this->fields[static::$items_id],
                    ],
                    _n('Action', 'Actions', 1) => [
                       'type' => 'select',
                       'name' => 'field',
                       'id'   => 'dropdown_field' . $rand,
                       'values' => [Dropdown::EMPTY_VALUE] +
                           $rule->dropdownActions(['noHtml' => true, 'used' => $used]),
                       'value' => $this->fields['field'],
                       'col_lg' => 12,
                       'col_md' => 12,
                    ],
                    '' => [
                       'content' => '<span class="w-100" id="action_span"></span>',
                       'col_lg' => 12,
                       'col_md' => 12,
                    ],
                 ]
              ]
           ]
        ];
        renderTwigForm($form);

        $params = ['field'                 => '__VALUE__',
                        'sub_type'              => $rule->getType(),
                        'ruleactions_id'        => $this->getID(),
                        $rule->getRuleIdField() => $this->fields[static::$items_id]];
        Ajax::updateItemOnSelectEvent(
            "dropdown_field$rand",
            "action_span",
            $CFG_GLPI["root_doc"] . "/ajax/ruleaction.php",
            $params
        );

        if (isset($this->fields['field']) && !empty($this->fields['field'])) {
            $params['field']       = $this->fields['field'];
            $params['action_type'] = $this->fields['action_type'];
            $params['value']       = $this->fields['value'];
            echo "<script type='text/javascript' >\n";
            echo "$(function() {";
            Ajax::updateItemJsCode(
                "action_span",
                $CFG_GLPI["root_doc"] . "/ajax/ruleaction.php",
                $params
            );
            echo '});</script>';
        }
    }
}
