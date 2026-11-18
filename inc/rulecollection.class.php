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

class RuleCollection extends CommonDBTM
{
    /// Rule type
    public $sub_type;
    /// process collection stop on first matched rule
    public $stop_on_first_match                   = false;
    /// field used to order rules
    public $orderby                               = "ranking";
    /// Processing several rules : use result of the previous one to computer the current one
    public $use_output_rule_process_as_next_input = false;
    /// Rule collection can be replay (for dictionnary)
    public $can_replay_rules                      = false;
    /// List of rules of the rule collection
    public $RuleList                              = null;
    /// Menu type
    public $menu_type                             = "rule";
    /// Menu option
    public $menu_option                           = "";

    public $entity                                = 0;

    public static $rightname                             = 'config';



    /// Tab orientation : horizontal or vertical
    public $taborientation = 'horizontal';

    public static function getTable($classname = null)
    {
        return parent::getTable('Rule');
    }


    /**
     * @param $entity (default 0)
     **/
    public function setEntity($entity = 0)
    {
        $this->entity = $entity;
    }


    public function canList()
    {
        return static::canView();
    }


    public function isEntityAssign()
    {
        return false;
    }


    /**
     * Get Collection Size : retrieve the number of rules
     *
     * @param boolean $recursive (true by default)
     * @param integer $condition (0 by default)
     * @param integer $children (0 by default)
     *
     * @return : number of rules
     **/
    public function getCollectionSize(
        $recursive = true,
        $condition = 0,
        $children = 0
    ) {
        global $DB;

        $restrict = $this->getRuleListCriteria([
            'condition' => $condition,
            'active'    => false,
            'inherited' => $recursive,
            'childrens' => $children,
        ]);

        unset($restrict['ORDER']);
        $restrict['SELECT'] = ['COUNT' => 'glpi_rules.id AS total'];
        return (int) $DB->request($restrict)->next()['total'];
    }


    /**
     * Get rules list criteria
     *
     * @param array $options Options
     *
     * @return array
     **/
    public function getRuleListCriteria($options = [])
    {
        $p['active']    = true;
        $p['start']     = 0;
        $p['limit']     = 0;
        $p['inherited'] = 1;
        $p['childrens'] = 0;
        $p['condition'] = 0;

        foreach ($options as $key => $value) {
            $p[$key] = $value;
        }

        $criteria = [
            'SELECT' => Rule::getTable() . '.*',
            'FROM'   => Rule::getTable(),
            'ORDER'  => [
                $this->orderby . ' ASC'
            ]
        ];

        $where = [];
        if ($p['active']) {
            $where['is_active'] = 1;
        }

        if ($p['condition'] > 0) {
            $where['condition'] = ['&', (int)$p['condition']];
        }

        //Select all the rules of a different type
        $where['sub_type'] = $this->getRuleClassName();
        if ($this->isRuleRecursive()) {
            $criteria['LEFT JOIN'] = [
                Entity::getTable() => [
                    'ON' => [
                        Entity::getTable()   => 'id',
                        Rule::getTable()     => 'entities_id'
                    ]
                ]
            ];

            if (!$p['childrens']) {
                $where += getEntitiesRestrictCriteria(
                    Rule::getTable(),
                    'entities_id',
                    $this->entity,
                    $p['inherited']
                );
            } else {
                $sons = getSonsOf('glpi_entities', $this->entity);
                $where[Rule::getTable() . '.entities_id'] = $sons;
            }

            $criteria['ORDER'] = [
                Entity::getTable() . '.level ASC',
                $this->orderby . ' ASC'
            ];
        }

        if ($p['limit']) {
            $criteria['LIMIT'] = (int)$p['limit'];
            $criteria['START'] = (int)$p['start'];
        }
        $criteria['WHERE'] = $where;

        return $criteria;
    }

    /**
     * Get Collection Part : retrieve descriptions of a range of rules
     *
     * @param $options array of options may be :
     *         - start : first rule (in the result set - default 0)
     *         - limit : max number of rules to retrieve (default 0)
     *         - recursive : boolean get recursive rules
     *         - childirens : boolean get childrens rules
     **/
    public function getCollectionPart($options = [])
    {
        global $DB;

        $p['start']     = 0;
        $p['limit']     = 0;
        $p['recursive'] = true;
        $p['childrens'] = 0;
        $p['condition'] = 0;

        foreach ($options as $key => $value) {
            $p[$key] = $value;
        }

        // no need to use SingletonRuleList::getInstance because we read only 1 page
        $this->RuleList       = new SingletonRuleList();
        $this->RuleList->list = [];

        //Select all the rules of a different type
        $criteria   = $this->getRuleListCriteria($p);
        $iterator   = $DB->request($criteria);

        while ($data = $iterator->next()) {
            //For each rule, get a Rule object with all the criterias and actions
            $tempRule               = $this->getRuleClass();
            $tempRule->fields       = $data;
            $this->RuleList->list[] = $tempRule;
        }
    }


    /**
     * Get Collection Datas : retrieve descriptions and rules
     *
     * @param $retrieve_criteria  Retrieve the criterias of the rules ? (default 0)
     * @param $retrieve_action    Retrieve the action of the rules ? (default 0)
     * @param $condition          Retrieve with a specific condition
     **/
    public function getCollectionDatas($retrieve_criteria = 0, $retrieve_action = 0, $condition = 0)
    {
        global $DB;

        if ($this->RuleList === null) {
            $this->RuleList = SingletonRuleList::getInstance(
                $this->getRuleClassName(),
                $this->entity
            );
        }
        $need = 1 + ($retrieve_criteria ? 2 : 0) + ($retrieve_action ? 4 : 0) + (8 * $condition);

        // check if load required
        if (($need & $this->RuleList->load) != $need) {
            //Select all the rules of a different type
            $criteria = $this->getRuleListCriteria(['condition' => $condition]);
            $iterator = $DB->request($criteria);

            if (count($iterator)) {
                $this->RuleList->list = [];

                while ($rule = $iterator->next()) {
                    //For each rule, get a Rule object with all the criterias and actions
                    $tempRule = $this->getRuleClass();

                    if (
                        $tempRule->getRuleWithCriteriasAndActions(
                            $rule["id"],
                            $retrieve_criteria,
                            $retrieve_action
                        )
                    ) {
                        //Add the object to the list of rules
                        $this->RuleList->list[] = $tempRule;
                    }
                }

                $this->RuleList->load = $need;
            }
        }
    }


    public function getRuleClassName()
    {

        if (preg_match('/(.*)Collection/', get_class($this), $rule_class)) {
            return $rule_class[1];
        }
        return "";
    }


    /**
     * Get a instance of the class to manipulate rule of this collection
     **/
    public function getRuleClass()
    {

        $name = $this->getRuleClassName();
        if ($name !=  '') {
            return new $name();
        }
        return null;
    }


    /**
     * Is a confirmation needed before replay on DB ?
     * If needed need to send 'replay_confirm' in POST
     *
     * @param $target filename : where to go when done
     *
     * @return  true if confirmtion is needed, else false
     **/
    public function warningBeforeReplayRulesOnExistingDB($target)
    {
        return false;
    }


    /**
     * Replay Collection on DB
     *
     * @param $offset             first row to work on (default 0)
     * @param $maxtime   float    max system time to stop working (default 0)
     * @param $items     array    containg items to replay. If empty -> all
     * @param $params    array    additional parameters if needed
     *
     * @return -1 if all rows done, else offset for next run
     **/
    public function replayRulesOnExistingDB($offset = 0, $maxtime = 0, $items = [], $params = [])
    {
    }


    /**
     * Get title used in list of rules
     *
     * @return Title of the rule collection
     **/
    public function getTitle()
    {
        return __('Rules list');
    }


    /**
     * Indicates if the rule can be affected to an entity or if it's global
     **/
    public function isRuleEntityAssigned()
    {

        $rule = $this->getRuleClass();
        return $rule->isEntityAssign();
    }


    /**
     * Indicates if the rule can be affected to an entity or if it's global
     **/
    public function isRuleRecursive()
    {

        $rule = $this->getRuleClass();
        return $rule->maybeRecursive();
    }

    /**
     * Indicates if the rule use conditions
     **/
    public function isRuleUseConditions()
    {

        $rule = $this->getRuleClass();
        return $rule->useConditions();
    }

    /**
     * Indicates if the rule use conditions
     **/
    public function getDefaultRuleConditionForList()
    {

        $rule = $this->getRuleClass();
        $cond = $rule->getConditionsArray();
        // Get max value
        if (count($cond)) {
            return max(array_keys($cond));
        }
        return 0;
    }

    public function showEngineSummary()
    {
        echo "<div class='alert alert-info'>";
        echo $this->stop_on_first_match ? __('The engine stops on the first checked rule.') : __('The engine treats all the rules.');
        if ($this->use_output_rule_process_as_next_input) {
            echo '<br>' . __('The engine passes the result of a rule to the following one.');
        }
        if ($this->isRuleUseConditions()) {
            echo '<br>' . __('Rules are conditionals. Each one can be used on multiple actions.');
        }
        echo '</div>';
    }


    /**
     * Show the list of rules
     *
     * @param $target
     * @param $options   array
     *
     * @return void
     **/
    public function showListRules($target, $options = [])
    {
        global $CFG_GLPI;

        $p['inherited'] = 1;
        $p['childrens'] = 0;
        $p['active']    = false;
        $p['condition'] = 0;
        $rand           = mt_rand();

        foreach (['inherited','childrens', 'condition'] as $param) {
            if (
                isset($options[$param])
                && $this->isRuleRecursive()
            ) {
                $p[$param] = $options[$param];
            }
        }

        $rule             = $this->getRuleClass();
        $display_entities = ($this->isRuleRecursive()
            && ($p['inherited'] || $p['childrens']));

        // Do not know what it is ?
        $canedit    = (self::canUpdate()
            && !$display_entities);

        $use_conditions = false;
        if ($rule->useConditions()) {
            // First get saved option
            $p['condition'] = Session::getSavedOption($this->getType(), 'condition', 0);
            if ($p['condition'] == 0) {
                $p['condition'] = $this->getDefaultRuleConditionForList();
            }
            $use_conditions = true;
            // Mini Search engine
            echo "<div class='d-flex align-items-center gap-3 mb-3'><label>" . __('Rules used for') . "</label>";
            $rule->dropdownConditions(['value' => $p['condition'],
                'on_change'  => 'reloadTab("start=0&inherited=' . $p['inherited']
                . '&childrens=' . $p['childrens'] . '&condition="+this.value)']);
            echo "</div>";
        }

        $nb         = $this->getCollectionSize($p['inherited'], $p['condition'], $p['childrens']);

        if ($canedit && $nb) {
            $massiveactionparams = [
                'container'     => 'ruleCollectionTable',
                'extraparams'   => [
                    'entity' => $this->entity,
                    'condition' => $p['condition'],
                    'rule_class_name' => $this->getRuleClassName()
                ],
                'display_arrow' => false,
            ];
            Html::showMassiveActions($massiveactionparams);
        }

        $fields = [
            __('Name'),
            __('Description'),
        ];
        if ($use_conditions) {
            $fields[] = __('Use rule for');
        }
        $fields[] = __('Active');
        if ($display_entities) {
            $fields[] = Entity::getTypeName(1);
        } elseif ($rule->can_sort && $canedit) {
            $fields[] = __('Actions');
        }

        renderTwigTemplate('table.twig', [
            'id' => 'ruleCollectionTable',
            'state_key' => $this->getType() . '_' . $p['inherited'] . '_' . $p['childrens']
                . '_' . $p['condition'],
            'fields' => $fields,
            'url' => $CFG_GLPI['root_doc'] . '/ajax/v2/rulecollection.php?' . http_build_query([
                'collection' => $this->getType(),
                'inherited' => $p['inherited'],
                'childrens' => $p['childrens'],
                'condition' => $p['condition'],
            ]),
            'pageSize' => (int) $_SESSION['glpilist_limit'],
        ] + ($canedit ? ['massive_action' => []] : []));

        echo "<div class='spaced center'>";

        if ($plugin = isPluginItemType($this->getType())) {
            $url = $CFG_GLPI["root_doc"] . "/plugins/" . strtolower((string) $plugin['plugin']);
        } else {
            $url = $CFG_GLPI["root_doc"];
        }

        echo "<a class='btn btn-secondary' href='#' onClick=\"" .
            Html::jsGetElementbyID('allruletest' . $rand) . ".dialog('open'); return false;\">" .
            __('Test rules engine') . "</a>";
        Ajax::createIframeModalWindow(
            'allruletest' . $rand,
            $url . "/front/rulesengine.test.php?" .
            "sub_type=" . $this->getRuleClassName() .
            "&condition=" . $p['condition'],
            ['title' => __('Test rules engine')]
        );
        echo "</div>";

        if ($this->can_replay_rules) {
            echo "<div class='spaced center'>";
            echo "<a class='vsubmit' href='" . $rule->getSearchURL() . "?replay_rule=replay_rule'>" .
                __s('Replay the dictionary rules') . "</a>";
            echo "</div>";
        }

        echo "<div class='spaced'>";
        $this->showAdditionalInformationsInForm($target);
        echo "</div>";
    }


    /**
     * Return one page for the rule list's remote table data source.
     */
    public function getPaginatedRules(array $options = [])
    {
        global $DB, $CFG_GLPI;

        $this->checkGlobal(READ);
        if ($this->isRuleEntityAssigned()) {
            $this->setEntity($_SESSION['glpiactive_entity']);
        }
        $rule = $this->getRuleClass();
        $p = [
            'active' => false,
            'inherited' => $this->isRuleRecursive() ? (int) !empty($options['inherited']) : 1,
            'childrens' => $this->isRuleRecursive() ? (int) !empty($options['childrens']) : 0,
            'condition' => $rule->useConditions() ? max(0, (int) ($options['condition'] ?? 0)) : 0,
        ];
        $display_entities = $this->isRuleRecursive() && ($p['inherited'] || $p['childrens']);
        $canedit = self::canUpdate() && !$display_entities;
        $use_conditions = $rule->useConditions();
        $target = $rule->getSearchURL();
        $criteria = $this->getRuleListCriteria($p);
        $count_criteria = $criteria;
        unset($count_criteria['ORDER']);
        $count_criteria['SELECT'] = [
            'COUNT' => 'glpi_rules.id AS total',
            'MIN' => 'glpi_rules.ranking AS first_rank',
            'MAX' => 'glpi_rules.ranking AS last_rank',
        ];
        $summary = $DB->request($count_criteria)->next();

        $sort_fields = ['glpi_rules.name', 'glpi_rules.description'];
        if ($use_conditions) {
            $sort_fields[] = 'glpi_rules.condition';
        }
        $sort_fields[] = 'glpi_rules.is_active';
        if ($display_entities) {
            $sort_fields[] = 'glpi_entities.completename';
        }
        $sort = (string) ($options['sort'] ?? '');
        if (ctype_digit($sort) && isset($sort_fields[(int) $sort])) {
            $criteria['ORDER'] = [
                $sort_fields[(int) $sort] . (strtolower($options['order'] ?? 'asc') === 'desc' ? ' DESC' : ' ASC'),
            ];
        }
        $criteria['ORDER'][] = 'glpi_rules.id ASC';
        $criteria['START'] = max(0, (int) ($options['offset'] ?? 0));
        $criteria['LIMIT'] = max(1, min(10000, (int) ($options['limit'] ?? $_SESSION['glpilist_limit'])));

        $values = [];
        foreach ($DB->request($criteria) as $data) {
            $newValue = [];
            $rule = $this->getRuleClass();
            $rule->fields = $data;
            $newValue[] = $rule->getLink(['withtype' => true]);
            $newValue[] = $rule->fields['description'];
            if ($use_conditions) {
                $newValue[] = $rule->getConditionName($rule->fields['condition']);
            }
            $newValue[] = $rule->fields['is_active'];
            if ($display_entities) {
                $newValue[] = Dropdown::getDropdownName(
                    "glpi_entities",
                    $rule->fields['entities_id']
                );
            } elseif ($rule->can_sort && $canedit) {
                $active_condition = $rule->fields['condition'];
                ob_start();
                if ($rule->fields['ranking'] > $summary['first_rank']) {
                    Html::showSimpleForm(
                        $target,
                        ['action' => 'up',
                        'condition' => $active_condition],
                        '',
                        ['type' => $rule->fields["sub_type"],
                        'id'   => $rule->fields["id"],],
                        $CFG_GLPI["root_doc"]."/pics/deplier_up.png"
                    );
                }
                if ($rule->fields['ranking'] < $summary['last_rank']) {
                    Html::showSimpleForm(
                        $target,
                        ['action' => 'down',
                        'condition' => $active_condition],
                        '',
                        ['type' => $rule->fields["sub_type"],
                        'id'   => $rule->fields["id"]],
                        $CFG_GLPI["root_doc"]."/pics/deplier_down.png"
                    );
                }
                $newValue[] = ob_get_clean();
            }

            if ($canedit) {
                $newValue['value'] = sprintf('item[%s][%s]', $rule->getType(), $rule->getID());
            }
            $values[] = $newValue;
        }

        return ['total' => (int) $summary['total'], 'rows' => $values];
    }


    /**
     * Show the list of rules
     *
     * @param $target
     *
     * @return void
     **/
    public function showAdditionalInformationsInForm($target)
    {
    }


    /**
     * Modify rule's ranking and automatically reorder all rules
     *
     * @param $ID     the rule ID whose ranking must be modified
     * @param $action up or down
     * @param $condition action on a specific condition
     **/
    public function changeRuleOrder($ID, $action, $condition = 0)
    {
        global $DB;

        $criteria = [
            'SELECT' => 'ranking',
            'FROM'   => 'glpi_rules',
            'WHERE'  => ['id' => $ID]
        ];

        $add_condition = [];
        if ($condition > 0) {
            $add_condition = ['condition' => ['&', (int)$condition]];
        }

        $iterator = $DB->request($criteria);
        if (count($iterator) == 1) {
            $result = $iterator->next();
            $current_rank = $result['ranking'];
            // Search rules to switch
            $criteria = [
                'SELECT' => ['id', 'ranking'],
                'FROM'   => 'glpi_rules',
                'WHERE'  => [
                    'sub_type'  => $this->getRuleClassName()
                ] + $add_condition,
                'LIMIT'  => 1
            ];

            switch ($action) {
                case "up":
                    $criteria['WHERE']['ranking'] = ['<', $current_rank];
                    $criteria['ORDERBY'] = 'ranking DESC';
                    break;

                case "down":
                    $criteria['WHERE']['ranking'] = ['>', $current_rank];
                    $criteria['ORDERBY'] = 'ranking ASC';
                    break;

                default:
                    return false;
            }

            $iterator2 = $DB->request($criteria);
            if (count($iterator2) == 1) {
                $result2 = $iterator2->next();
                $other_ID = $result2['id'];
                $new_rank = $result2['ranking'];
                echo $current_rank . ' ' . $ID . '<br>';
                echo $new_rank . ' ' . $other_ID . '<br>';

                $rule = $this->getRuleClass();
                $result = false;
                $criteria = [
                    'SELECT' => ['id', 'ranking'],
                    'FROM'   => 'glpi_rules',
                    'WHERE'  => ['sub_type' => $this->getRuleClassName()]
                ];
                $diff = $new_rank - $current_rank;
                switch ($action) {
                    case "up":
                        $criteria['WHERE'] = array_merge(
                            $criteria['WHERE'],
                            [
                                ['ranking' => ['>', $new_rank]],
                                ['ranking' => ['<=', $current_rank]]
                            ]
                        );
                        $diff += 1;
                        break;

                    case "down":
                        $criteria['WHERE'] = array_merge(
                            $criteria['WHERE'],
                            [
                                ['ranking' => ['>=', $current_rank]],
                                ['ranking' => ['<', $new_rank]]
                            ]
                        );
                        $diff -= 1;
                        break;

                    default:
                        return false;
                }

                if ($diff != 0) {
                    // Move several rules
                    $iterator3 = $DB->request($criteria);
                    while ($data = $iterator3->next()) {
                        $data['ranking'] += $diff;
                        $result = $rule->update($data);
                    }
                } else {
                    // Only move one
                    $result = $rule->update([
                        'id'      => $ID,
                        'ranking' => $new_rank
                    ]);
                }

                // Update reference
                if ($result) {
                    $result = $rule->update([
                        'id'      => $other_ID,
                        'ranking' => $current_rank
                    ]);
                }
                return $result;
            }
        }
        return false;
    }


    /**
     * Update Rule Order when deleting a rule
     *
     * @param $ranking rank of the deleted rule
     *
     * @return true if all ok
     **/
    public function deleteRuleOrder($ranking)
    {
        global $DB;

        $result = $DB->update(
            'glpi_rules',
            [
                'ranking' => new \QueryExpression($DB->quoteName('ranking') . ' - 1')
            ],
            [
                'sub_type'  => $this->getRuleClassName(),
                'ranking'   => ['>', $ranking]
            ]
        );
        return $result;
    }


    /**
     * Move a rule in an ordered collection
     *
     * @param $ID        of the rule to move
     * @param $ref_ID    of the rule position  (0 means all, so before all or after all)
     * @param $type      of move : after or before ( default 'after')
     *
     * @return true if all ok
     **/
    public function moveRule($ID, $ref_ID, $type = 'after')
    {
        global $DB;

        $ruleDescription = new Rule();

        // Get actual ranking of Rule to move
        $ruleDescription->getFromDB($ID);
        $old_rank = $ruleDescription->fields["ranking"];

        // Compute new ranking
        if ($ref_ID) { // Move after/before an existing rule
            $ruleDescription->getFromDB($ref_ID);
            $rank = $ruleDescription->fields["ranking"];
        } elseif ($type == "after") {
            // Move after all
            $result = $DB->request([
                'SELECT' => ['MAX' => 'ranking AS maxi'],
                'FROM'   => 'glpi_rules',
                'WHERE'  => ['sub_type' => $this->getRuleClassName()]
            ])->next();
            $rank   = $result['maxi'];
        } else {
            // Move before all
            $rank = 1;
        }

        $rule   = $this->getRuleClass();

        $result = false;

        // Move others rules in the collection
        if ($old_rank < $rank) {
            if ($type == "before") {
                $rank--;
            }

            // Move back all rules between old and new rank
            $iterator = $DB->request([
                'SELECT' => ['id', 'ranking'],
                'FROM'   => 'glpi_rules',
                'WHERE'  => [
                    'sub_type'  => $this->getRuleClassName(),
                    ['ranking'  => ['>', $old_rank]],
                    ['ranking'  => ['<=', $rank]]
                ]
            ]);
            while ($data = $iterator->next()) {
                $data['ranking']--;
                $result = $rule->update($data);
            }
        } elseif ($old_rank > $rank) {
            if ($type == "after") {
                $rank++;
            }

            // Move forward all rule  between old and new rank
            $iterator = $DB->request([
                'SELECT' => ['id', 'ranking'],
                'FROM'   => 'glpi_rules',
                'WHERE'  => [
                    'sub_type'  => $this->getRuleClassName(),
                    ['ranking'  => ['>=', $rank]],
                    ['ranking'  => ['<', $old_rank]]
                ]
            ]);
            while ($data = $iterator->next()) {
                $data['ranking']++;
                $result = $rule->update($data);
            }
        } else { // $old_rank == $rank : nothing to do
            $result = false;
        }

        // Move the rule
        if (
            $result
            && ($old_rank != $rank)
        ) {
            $result = $rule->update(['id'      => $ID,
                'ranking' => $rank]);
        }
        return ($result ? true : false);
    }


    /**
     * Print a title for backup rules
     *
     * @since 0.85
     *
     * @return void
     **/
    public static function titleBackup()
    {
        global $CFG_GLPI;

        $buttons = [];
        $title   = "";

        $buttons["{$CFG_GLPI["root_doc"]}/front/rule.backup.php?action=import"] = _x('button', 'Import');
        $buttons["{$CFG_GLPI["root_doc"]}/front/rule.backup.php?action=export"] = _x('button', 'Export');

        echo "<div class='center mb-3'><table class='tab_glpi' aria-label='Key'><tr>";
        echo "<td><i class='far fa-save fa-3x' aria-hidden='true'></i></td>";
        foreach ($buttons as $key => $val) {
            echo "<td><a class='btn btn-secondary ' href='" . $key . "'>" . $val . "</a></td>";
        }
        echo "</tr></table></div>";
    }


    /**
     * Export rules in a xml format
     *
     * @param items array the input data to transform to xml
     *
     * @since 0.85
     *
     * @return void send attachment to browser
     **/
    public static function exportRulesToXML($items = [])
    {

        if (!count($items)) {
            return false;
        }

        $rulecollection = new self();
        $rulecritera    = new RuleCriteria();
        $ruleaction     = new RuleAction();

        //create xml
        $xmlE           = new SimpleXMLElement('<rules/>');

        //parse all rules
        foreach ($items as $key => $ID) {
            $rulecollection->getFromDB($ID);
            if (!class_exists($rulecollection->fields['sub_type'])) {
                continue;
            }
            $rule = new $rulecollection->fields['sub_type']();
            unset($rulecollection->fields['id']);
            unset($rulecollection->fields['date_mod']);

            $name = Dropdown::getDropdownName(
                "glpi_entities",
                $rulecollection->fields['entities_id']
            );
            $rulecollection->fields['entities_id'] = $name;

            //add root node
            $xmlERule = $xmlE->addChild('rule');

            //convert rule direct indexes in XML
            foreach ($rulecollection->fields as $key => $val) {
                $xmlERule->$key = $val;
            }

            //find criterias
            $criterias = $rulecritera->find(['rules_id' => $ID]);
            foreach ($criterias as &$criteria) {
                unset($criteria['id']);
                unset($criteria['rules_id']);

                $available_criteria = $rule->getCriterias();
                $crit               = $criteria['criteria'];
                if (self::isCriteraADropdown($available_criteria, $criteria['condition'], $crit)) {
                    $criteria['pattern']
                        = Html::clean(Dropdown::getDropdownName(
                            $available_criteria[$crit]['table'],
                            $criteria['pattern']
                        ));
                }

                //convert criterias in XML
                $xmlECritiera = $xmlERule->addChild('rulecriteria');
                foreach ($criteria as $key => $val) {
                    $xmlECritiera->$key = $val;
                }
            }

            //find actions
            $actions = $ruleaction->find(['rules_id' => $ID]);
            foreach ($actions as &$action) {
                unset($action['id']);
                unset($action['rules_id']);

                //process FK (just in case of "assign" action)
                if (
                    ($action['action_type'] == "assign")
                    && (strpos((string) $action['field'], '_id') !== false)
                    && !(($action['field'] == "entities_id")
                    && ($action['value'] == 0))
                ) {
                    $field = $action['field'];
                    if ($action['field'][0] == "_") {
                        $field = substr((string) $action['field'], 1);
                    }
                    $table = getTableNameForForeignKeyField($field);

                    $action['value'] = Html::clean(Dropdown::getDropdownName($table, $action['value']));
                }

                //convert actions in XML
                $xmlEAction = $xmlERule->addChild('ruleaction');
                foreach ($action as $key => $val) {
                    $xmlEAction->$key = $val;
                }
            }
        }

        //convert SimpleXMLElement to xml string
        $xml = $xmlE->asXML();

        //send attachment to browser
        header('Content-type: application/xml');
        header('Content-Disposition: attachment; filename="rules.xml"');
        echo $xml;

        //exit;
    }


    /**
     * Print a form to select a xml file for import rules
     *
     * @since 0.85
     *
     * @return void
     **/
    public static function displayImportRulesForm()
    {
        renderTwigForm([
            'action' => 'rule.backup.php',
            'buttons' => [['type' => 'submit', 'name' => 'import', 'value' => _x('button', 'Import'), 'class' => 'btn btn-primary']],
            'content' => [__('Import rules from a XML file') => ['visible' => true, 'inputs' => [
                ['type' => 'hidden', 'name' => 'action', 'value' => 'preview_import'],
                __('File') => ['content' => "<input class='form-control' type='file' name='xml_file' accept='.xml,text/xml,application/xml'>"],
            ]]],
        ]);
    }


    /**
     *
     * Check if a criterion is a dropdown or not
     *
     * @since 0.85
     *
     * @param $available_criteria    available criterai for this rule
     * @param $condition             the rulecriteria condition
     * @param $criterion             the criterion
     *
     * @return true if a criterion is a dropdown, false otherwise
     **/
    public static function isCriteraADropdown($available_criteria, $condition, $criterion)
    {

        if (isset($available_criteria[$criterion]['type'])) {
            $type = $available_criteria[$criterion]['type'];
        } else {
            $type = false;
        }
        return (in_array(
            $condition,
            [Rule::PATTERN_IS, Rule::PATTERN_IS_NOT, Rule::PATTERN_UNDER]
        )
        && ($type == 'dropdown'));
    }


    /**
     * Print a form to inform user when conflicts appear during the import of rules from a xml file
     *
     * @since 0.85
     *
     * @return true if all ok
     **/
    public static function previewImportRules()
    {
        global $DB;

        if (!isset($_FILES["xml_file"]) || ($_FILES["xml_file"]["size"] == 0)) {
            return false;
        }

        if ($_FILES["xml_file"]["error"] != UPLOAD_ERR_OK) {
            Session::addMessageAfterRedirect(__("No file was uploaded"));
            return false;
        }
        //get xml file content
        $xml           = file_get_contents($_FILES["xml_file"]["tmp_name"]);
        //convert a xml string into a SimpleXml object
        if (!$xmlE = simplexml_load_string($xml)) {
            Session::addMessageAfterRedirect(__('Unauthorized file type'), false, ERROR);
        }
        //convert SimpleXml object into an array and store it in session
        $rules         = json_decode(json_encode((array) $xmlE), true);
        //check rules (check if entities, criterias and actions is always good in this glpi)
        $entity        = new Entity();
        $rules_refused = [];

        //In case there's only one rule to import, recreate an array with key => value
        if (isset($rules['rule']['entities_id'])) {
            $rules['rule'] = [0 => $rules['rule']];
        }

        foreach ($rules['rule'] as $k_rule => &$rule) {
            $tmprule = new $rule['sub_type']();
            //check entities
            if ($tmprule->isEntityAssign()) {
                $entities_found = $entity->find(['completename' => $rule['entities_id']]);
                if (empty($entities_found)) {
                    $rules_refused[$k_rule]['entity'] = true;
                }
            }

            //process direct attributes
            foreach ($rule as &$val) {
                if (
                    is_array($val)
                    && empty($val)
                ) {
                    $val = "";
                }
            }

            //check criterias
            if (isset($rule['rulecriteria'])) {
                //check and correct criterias array format
                if (isset($rule['rulecriteria']['criteria'])) {
                    $rule['rulecriteria'] = [$rule['rulecriteria']];
                }

                foreach ($rule['rulecriteria'] as $k_crit => $criteria) {
                    $available_criteria = $tmprule->getCriterias();
                    $crit               = $criteria['criteria'];
                    //check FK (just in case of "is", "is_not" and "under" criteria)
                    if (
                        self::isCriteraADropdown(
                            $available_criteria,
                            $criteria['condition'],
                            $crit
                        )
                    ) {
                        //escape pattern
                        $criteria['pattern'] = $DB->escape(Html::entity_decode_deep($criteria['pattern']));
                        $itemtype = getItemTypeForTable($available_criteria[$crit]['table']);
                        $item     = new $itemtype();
                        if ($item instanceof CommonTreeDropdown) {
                            $found = $item->find(['completename' => $criteria['pattern']]);
                        } else {
                            $found = $item->find(['name' => $criteria['pattern']]);
                        }
                        if (empty($found)) {
                            $rules_refused[$k_rule]['criterias'][] = $k_crit;
                        } else {
                            $tmp = array_pop($found);
                            $rules['rule'][$k_rule]['rulecriteria'][$k_crit]['pattern'] = $tmp['id'];
                        }
                    }
                }
            }

            //check actions
            if (isset($rule['ruleaction'])) {
                //check and correct actions array format
                if (isset($rule['ruleaction']['field'])) {
                    $rule['ruleaction'] = [$rule['ruleaction']];
                }

                foreach ($rule['ruleaction'] as $k_action => $action) {
                    $available_actions = $tmprule->getActions();
                    $act               = $action['field'];

                    if (
                        ($action['action_type'] == "assign")
                        && (isset($available_actions[$act]['type'])
                        && ($available_actions[$act]['type'] == 'dropdown'))
                    ) {
                        //pass root entity and empty array (N/A value)
                        if (
                            ($action['field'] == "entities_id")
                            && (($action['value'] == 0)
                            || ($action['value'] == []))
                        ) {
                            continue;
                        }

                        //escape value
                        $action['value'] = $DB->escape(Html::entity_decode_deep($action['value']));
                        $itemtype = getItemTypeForTable($available_actions[$act]['table']);
                        $item     = new $itemtype();
                        if ($item instanceof CommonTreeDropdown) {
                            $found = $item->find(['completename' => $action['value']]);
                        } else {
                            $found = $item->find(['name' => $action['value']]);
                        }
                        if (empty($found)) {
                            $rules_refused[$k_rule]['actions'][] = $k_action;
                        } else {
                            $tmp = array_pop($found);
                            $rules['rule'][$k_rule]['ruleaction'][$k_action]['value'] = $tmp['id'];
                        }
                    }
                }
            }
        }

        //save rules for ongoing processing
        $_SESSION['glpi_import_rules']         = $rules;
        $_SESSION['glpi_import_rules_refused'] = $rules_refused;

        //if no conflict detected, we can directly process the import
        if (!count($rules_refused)) {
            Html::redirect("rule.backup.php?action=process_import");
        }

        $values = [];
        $inputs = [['type' => 'hidden', 'name' => 'action', 'value' => 'process_import']];
        $entity_inputs = [];
        foreach ($rules_refused as $key => $refused) {
            $data = $rules['rule'][$key];
            $item = new $data['sub_type']();
            $reasons = [];
            if (!isset($refused['criterias']) && !isset($refused['actions']) && isset($refused['entity'])) {
                $reasons[] = sprintf(__('%1$s (%2$s)'), __('Entity not found'),
                    htmlspecialchars((string) $data['entities_id'], ENT_QUOTES, 'UTF-8', false));
                ob_start();
                Dropdown::show('Entity', ['comments' => false, 'name' => 'new_entities[' . $data['uuid'] . ']']);
                $control = ob_get_clean();
                ob_start();
                renderTwigTemplate('macros/wrappedInput.twig', [
                    'title' => htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8', false) . ' — ' . __('Select the desired entity'),
                    'input' => ['content' => $control, 'col_lg' => 6, 'col_md' => 6],
                ]);
                $entity_inputs[] = ob_get_clean();
            }
            foreach ($refused['criterias'] ?? [] as $index) {
                $criterion = $data['rulecriteria'][$index];
                $reasons[] = __('Criteria refused') . ': ' . $item->getCriteriaName($criterion['criteria']) . ' '
                    . RuleCriteria::getConditionByID($criterion['condition'], get_class($item), $criterion['criteria']) . ' '
                    . htmlspecialchars((string) $criterion['pattern'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
            }
            foreach ($refused['actions'] ?? [] as $index) {
                $action = $data['ruleaction'][$index];
                $reasons[] = __('Actions refused') . ': ' . $item->getActionName($action['field']) . ' '
                    . RuleAction::getActionByID($action['action_type']) . ' '
                    . htmlspecialchars((string) ($action['value'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
            }
            $values[$key] = ['type' => $item->getTitle(),
                'name' => htmlspecialchars($data['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false),
                'reason' => implode('<br>', $reasons)];
        }
        if ($entity_inputs) {
            $inputs[''] = ['content' => '<div class="row">' . implode('', $entity_inputs) . '</div>', 'col_lg' => 12, 'col_md' => 12];
        }
        renderTwigForm([
            'action' => 'rule.backup.php',
            'buttons' => [['type' => 'submit', 'name' => 'import', 'value' => _x('button', 'Post'), 'class' => 'btn btn-primary']],
            'content' => [__('Rules refused') => ['visible' => true, 'inputs' => $inputs]],
        ]);
        renderTwigTemplate('table.twig', [
            'id' => 'ruleImportRefused', 'state_key' => 'ruleImportRefused',
            'fields' => ['type' => _n('Type', 'Type', 1), 'name' => __('Name'), 'reason' => __('Reason of rejection')],
            'values' => $values, 'pageSize' => max(1, (int) ($_SESSION['glpilist_limit'] ?? 15)),
        ]);

        return true;
    }


    /**
     * import rules in glpi after user validation
     *
     * @since 0.85
     *
     * @return true if all ok
     **/
    public static function processImportRules()
    {
        $ruleCriteria = new RuleCriteria();
        $ruleAction   = new RuleAction();
        $entity       = new Entity();

        //get session vars
        $rules         = $_SESSION['glpi_import_rules'];
        $rules_refused = $_SESSION['glpi_import_rules_refused'];
        $rr_keys       = array_keys($rules_refused);
        unset($_SESSION['glpi_import_rules']);
        unset($_SESSION['glpi_import_rules_refused']);

        // unset all refused rules
        foreach ($rules['rule'] as $k_rule => &$rule) {
            if (in_array($k_rule, $rr_keys)) {
                //Do not process rule with actions or criterias refused
                if (
                    isset($rules_refused[$k_rule]['criterias'])
                    || isset($rules_refused[$k_rule]['actions'])
                ) {
                    unset($rules['rule'][$k_rule]);
                } else {// accept rule with only entity not found (change entity)
                    $rule['entities_id'] = $_REQUEST['new_entities'][$rule['uuid']];
                }
            }
        }

        //import all right rules
        while (!empty($rules['rule'])) {
            $current_rule             = array_shift($rules['rule']);
            $add_criteria_and_actions = false;
            $params                   = [];
            $itemtype                 = $current_rule['sub_type'];
            $item                     = new $itemtype();

            //Find a rule by it's uuid
            $found    = $item->find(['uuid' => $current_rule['uuid']]);
            $params   = Toolbox::addslashes_deep($current_rule);
            unset($params['rulecriteria']);
            unset($params['ruleaction']);

            if (!$item->isEntityAssign()) {
                $params['entities_id'] = 0;
            } else {
                $entities_found = $entity->find(['completename' => $rule['entities_id']]);
                if (!empty($entities_found)) {
                    $entity_found          = array_shift($entities_found);
                    $params['entities_id'] = $entity_found['id'];
                } else {
                    $params['entities_id'] = 0;
                }
            }
            foreach (['is_recursive', 'is_active'] as $field) {
                //Should not be necessary but without it there's an sql error...
                if (!isset($params[$field]) || ($params[$field] == '')) {
                    $params[$field] = 0;
                }
            }

            //if uuid not exist, create rule
            if (empty($found)) {
                //Manage entity
                $params['_add'] = true;
                $rules_id       = $item->add($params);
                if ($rules_id) {
                    Event::log(
                        $rules_id,
                        "rules",
                        4,
                        "setup",
                        sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $rules_id)
                    );
                    $add_criteria_and_actions = true;
                }
            } else { //if uuid exists, then update the rule
                $tmp               = array_shift($found);
                $params['id']      = $tmp['id'];
                $params['_update'] = true;
                $rules_id          = $tmp['id'];
                if ($item->update($params)) {
                    Event::log(
                        $rules_id,
                        "rules",
                        4,
                        "setup",
                        sprintf(__('%s updates an item'), $_SESSION["glpiname"])
                    );

                    //remove all dependent criterias and action
                    $ruleCriteria->deleteByCriteria(["rules_id" => $rules_id]);
                    $ruleAction->deleteByCriteria(["rules_id" => $rules_id]);
                    $add_criteria_and_actions = true;
                }
            }

            if ($add_criteria_and_actions) {
                //Add criteria
                if (isset($current_rule['rulecriteria'])) {
                    foreach ($current_rule['rulecriteria'] as $criteria) {
                        $criteria['rules_id'] = $rules_id;
                        //fix array in value key
                        //(simplexml bug, empty xml node are converted in empty array instead of null)
                        if (is_array($criteria['pattern'])) {
                            $criteria['pattern'] = null;
                        }
                        $criteria = Toolbox::addslashes_deep($criteria);
                        $ruleCriteria->add($criteria);
                    }
                }

                //Add actions
                if (isset($current_rule['ruleaction'])) {
                    foreach ($current_rule['ruleaction'] as $action) {
                        $action['rules_id'] = $rules_id;
                        //fix array in value key
                        //(simplexml bug, empty xml node are converted in empty array instead of null)
                        if (is_array($action['value'])) {
                            $action['value'] = null;
                        }
                        $action = Toolbox::addslashes_deep($action);
                        $ruleAction->add($action);
                    }
                }
            }
        }

        Session::addMessageAfterRedirect(__('Successful importation'));

        return true;
    }


    /**
     * Process all the rules collection
     *
     * @param array $input            array the input data used to check criterias (need to be clean slashes)
     * @param array $output           array the initial ouput array used to be manipulate by actions (need to be clean slashes)
     * @param array $params           array parameters for all internal functions (need to be clean slashes)
     * @param array $options          array options :
     *                            - condition : specific condition to limit rule list
     *                            - only_criteria : only react on specific criteria
     *
     * @return array|string the output array updated by actions (addslashes datas)
     **/
    public function processAllRules($input = [], $output = [], $params = [], $options = [])
    {

        $p['condition']     = 0;
        $p['only_criteria'] = null;

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $p[$key] = $val;
            }
        }

        // Get Collection datas
        $this->getCollectionDatas(1, 1, $p['condition']);
        $input                      = $this->prepareInputDataForProcessWithPlugins($input, $params);
        $output["_no_rule_matches"] = true;
        //Store rule type being processed (for plugins)
        $params['rule_itemtype']    = $this->getRuleClassName();

        if (count($this->RuleList->list)) {
            foreach ($this->RuleList->list as $rule) {
                //If the rule is active, process it

                if ($rule->fields["is_active"]) {
                    $output["_rule_process"] = false;
                    $rule->process($input, $output, $params, $p);

                    if ($output["_rule_process"] && $this->stop_on_first_match) {
                        unset($output["_rule_process"]);
                        $output["_ruleid"] = $rule->fields["id"];
                        return Toolbox::addslashes_deep($output);
                    }
                }

                if ($this->use_output_rule_process_as_next_input) {
                    $output = $this->prepareInputDataForProcessWithPlugins($output, $params);
                    $input  = $output;
                }
            }
        }

        return Toolbox::addslashes_deep($output);
    }


    /**
     * Show form displaying results for rule collection preview
     *
     * @param $target          where to go
     * @param $values    array of data
     * @param $condition       condition to limit rules (default 0)
     **/
    public function showRulesEnginePreviewCriteriasForm($target, array $values, $condition = 0)
    {
        $input = $this->prepareInputDataForTestProcess($condition);
        if (!$input) {
            echo "<p class='text-muted'>" . __('No element to be tested') . '</p>';
            return $input;
        }
        $rule = $this->getRuleClass();
        $criteria = $rule->getAllCriteria();
        $inputs = [
            ['type' => 'hidden', 'name' => 'sub_type', 'value' => $this->getRuleClassName()],
            ['type' => 'hidden', 'name' => 'condition', 'value' => $condition],
        ];
        $controls = [];
        foreach ($input as $field) {
            ob_start();
            $rule->displayCriteriaSelectPattern($field, $field, Rule::PATTERN_IS, $values[$field] ?? '');
            $control = ob_get_clean();
            ob_start();
            renderTwigTemplate('macros/wrappedInput.twig', [
                'title' => htmlspecialchars($criteria[$field]['name'] ?? $field, ENT_QUOTES, 'UTF-8', false),
                'input' => ['content' => $control, 'col_lg' => 6, 'col_md' => 6],
            ]);
            $controls[] = ob_get_clean();
        }
        $rule->criterias = [];
        foreach ($input as $field) {
            $rule->criterias[] = (object) ['fields' => ['criteria' => $field]];
        }
        ob_start();
        $rule->showSpecificCriteriasForPreview($_POST);
        $inputs[''] = ['content' => '<div class="row">' . implode('', $controls) . '</div>' . ob_get_clean(), 'col_lg' => 12, 'col_md' => 12];
        renderTwigForm([
            'action' => $target, 'attributes' => ['name' => 'testrule_form', 'id' => 'testrulesengine_form'],
            'buttons' => [['type' => 'submit', 'name' => 'test_all_rules', 'value' => _x('button', 'Test'), 'class' => 'btn btn-primary']],
            'content' => [_n('Criterion', 'Criteria', Session::getPluralNumber()) => ['visible' => true, 'inputs' => $inputs]],
        ]);
        return $input;
    }


    /**
     * Test all the rules collection
     *
     * @param input      array the input data used to check criterias
     * @param output     array the initial ouput array used to be manipulate by actions
     * @param params     array parameters for all internal functions
     * @param $condition       condition to limit rules (DEFAULT 0)
     *
     * @return the output array updated by actions
     **/
    public function testAllRules($input = [], $output = [], $params = [], $condition = 0)
    {

        // Get Collection data
        $this->getCollectionDatas(1, 1, $condition);

        $output["_no_rule_matches"] = true;

        if (count($this->RuleList->list)) {
            foreach ($this->RuleList->list as $rule) {
                //If the rule is active, process it
                if ($rule->fields["is_active"]) {
                    $output["_rule_process"]                     = false;
                    $output["result"][$rule->fields["id"]]["id"] = $rule->fields["id"];
                    $rule->process($input, $output, $params);

                    if (
                        $output["_rule_process"]
                        && $this->stop_on_first_match
                    ) {
                        unset($output["_rule_process"]);
                        $output["result"][$rule->fields["id"]]["result"] = 1;
                        $output["_ruleid"]                               = $rule->fields["id"];
                        return $output;
                    } elseif ($output["_rule_process"]) {
                        $output["result"][$rule->fields["id"]]["result"] = 1;
                    } else {
                        $output["result"][$rule->fields["id"]]["result"] = 0;
                    }
                } else {
                    //Rule is inactive
                    $output["result"][$rule->fields["id"]]["result"] = 2;
                }

                if ($this->use_output_rule_process_as_next_input) {
                    $input = $output;
                }
            }
        }

        return $output;
    }


    /**
     * Prepare input datas for the rules collection
     *
     * @param $input  the input data used to check criterias
     * @param $params parameters
     *
     * @return the updated input datas
     **/
    public function prepareInputDataForProcess($input, $params)
    {
        return $input;
    }


    /**
     * Prepare input datas for the rules collection, also using plugins values
     *
     * @since 0.84
     *
     * @param $input  the input data used to check criterias
     * @param $params parameters
     *
     * @return the updated input datas
     **/
    public function prepareInputDataForProcessWithPlugins($input, $params)
    {
        global $PLUGIN_HOOKS;

        $input = $this->prepareInputDataForProcess($input, $params);
        if (isset($PLUGIN_HOOKS['use_rules'])) {
            foreach ($PLUGIN_HOOKS['use_rules'] as $plugin => $val) {
                if (!Plugin::isPluginActive($plugin)) {
                    continue;
                }
                if (is_array($val) && in_array($this->getRuleClassName(), $val)) {
                    $results = Plugin::doOneHook(
                        $plugin,
                        'ruleCollectionPrepareInputDataForProcess',
                        ['rule_itemtype' => $this->getRuleClassName(),
                        'values'        => ['input' => $input,
                        'params' => $params]]
                    );
                    if (is_array($results)) {
                        foreach ($results as $id => $result) {
                            $input[$id] = $result;
                        }
                    }
                }
            }
        }
        return $input;
    }


    /**
     * Prepare input datas for the rules collection
     *
     * @param $condition condition to limit rules (DEFAULT 0)
     *
     * @return the updated input datas
     **/
    public function prepareInputDataForTestProcess($condition = 0)
    {
        global $DB;

        $limit = [];
        if ($condition > 0) {
            $limit = ['glpi_rules.condition' => ['&', (int)$condition]];
        }
        $input = [];

        $iterator = $DB->request([
            'SELECT'          => 'glpi_rulecriterias.criteria',
            'DISTINCT'        => true,
            'FROM'            => 'glpi_rulecriterias',
            'INNER JOIN'      => [
                'glpi_rules'   => [
                    'ON' => [
                        'glpi_rulecriterias' => 'rules_id',
                        'glpi_rules'         => 'id'
                    ]
                ]
            ],
            'WHERE'           => [
                'glpi_rules.is_active'  => 1,
                'glpi_rules.sub_type'   => $this->getRuleClassName()
            ] + $limit
        ]);

        while ($data = $iterator->next()) {
            $input[] = $data["criteria"];
        }
        return $input;
    }


    /**
     * Show form displaying results for rule engine preview
     *
     * @param $target          where to go
     * @param $input     array of data
     * @param $condition       condition to limit rules (DEFAULT 0)
     **/
    public function showRulesEnginePreviewResultsForm($target, array $input, $condition = 0)
    {
        $output = $this->testAllRules($input, $this->use_output_rule_process_as_next_input ? $input : [], $input, $condition);
        $rule = $this->getRuleClass();
        if (isset($output['result'])) {
            $values = [];
            foreach ($output['result'] as $ID => $result) {
                $rule->getFromDB($ID);
                $values[$ID] = ['name' => htmlspecialchars($rule->fields['name'], ENT_QUOTES, 'UTF-8', false),
                    'result' => $result['result'] == 2 ? __('Inactive') : Dropdown::getYesNo($result['result'])];
            }
            $id = 'ruleEnginePreview_' . $this->getType();
            renderTwigTemplate('table.twig', [
                'id' => $id, 'state_key' => $id,
                'fields' => ['name' => __('Name'), 'result' => _n('Validation', 'Validations', 1)],
                'values' => $values, 'pageSize' => max(1, (int) ($_SESSION['glpilist_limit'] ?? 15)),
            ]);
        }
        $output = $this->cleanTestOutputCriterias($output);
        unset($output['result']);
        $this->showTestResults($rule, $output, count($output) ? 1 : 0);
    }


    /**
     * Unset criterias from the rule's ouput results (begins by _)
     *
     * @param $output    array clean output array to clean
     *
     * @return cleaned array
     **/
    public function cleanTestOutputCriterias(array $output)
    {

        $rule   = $this->getRuleClass();
        $actions = $rule->getAllActions();

        //If output array contains keys begining with _ : drop it
        foreach ($output as $criteria => $value) {
            if ($criteria[0] == '_' && !isset($actions[$criteria])) {
                unset($output[$criteria]);
            }
        }
        return $output;
    }


    /**
     * Show test results for a rule
     *
     * @param $rule                     rule object
     * @param $output          array    output data array
     * @param $global_result   boolean  global result
     *
     * @return cleaned array
     **/
    public function showTestResults($rule, array $output, $global_result)
    {
        $actions = $rule->getAllActions();
        $values = [['field' => _n('Validation', 'Validations', 1), 'value' => Dropdown::getYesNo($global_result)]];
        foreach ($this->preProcessPreviewResults($output) as $field => $value) {
            if (isset($actions[$field])) {
                $values[] = ['field' => $actions[$field]['name'],
                    'value' => $rule->getActionValue($field, $actions[$field]['action_type'] ?? '', $value)];
            }
        }
        $id = 'ruleEngineResults_' . $this->getType();
        renderTwigTemplate('table.twig', [
            'id' => $id, 'state_key' => $id,
            'fields' => ['field' => _n('Field', 'Fields', 1), 'value' => __('Value')],
            'values' => $values, 'pageSize' => max(1, (int) ($_SESSION['glpilist_limit'] ?? 15)),
        ]);
    }


    /**
     * @param $output
     **/
    public function preProcessPreviewResults($output)
    {
        global $PLUGIN_HOOKS;

        if (isset($PLUGIN_HOOKS['use_rules'])) {
            $params['rule_itemtype'] = $this->getType();
            foreach ($PLUGIN_HOOKS['use_rules'] as $plugin => $val) {
                if (!Plugin::isPluginActive($plugin)) {
                    continue;
                }
                if (is_array($val) && in_array($this->getType(), $val)) {
                    $results = Plugin::doOneHook(
                        $plugin,
                        "preProcessRuleCollectionPreviewResults",
                        ['output' => $output,
                        'params' => $params]
                    );
                    if (is_array($results)) {
                        foreach ($results as $id => $result) {
                            $output[$id] = $result;
                        }
                    }
                }
            }
        }
        return $this->cleanTestOutputCriterias($output);
    }


    /**
     * Print a title if needed which will be displayed above list of rules
     *
     * @return void
     **/
    public function title()
    {
    }


    /**
     * Get rulecollection classname by giving his itemtype
     *
     * @param $itemtype                 itemtype
     * @param $check_dictionnary_type   check if the itemtype is a dictionnary or not
     *                                  (false by default)
     *
     * @return the rulecollection class or null
     */
    public static function getClassByType($itemtype, $check_dictionnary_type = false)
    {
        global $CFG_GLPI;

        if ($plug = isPluginItemType($itemtype)) {
            $typeclass = 'Plugin' . $plug['plugin'] . $plug['class'] . 'Collection';
        } else {
            if (in_array($itemtype, $CFG_GLPI["dictionnary_types"])) {
                $typeclass = 'RuleDictionnary' . $itemtype . "Collection";
            } else {
                $typeclass = $itemtype . "Collection";
            }
        }

        if (
            ($check_dictionnary_type && in_array($itemtype, $CFG_GLPI["dictionnary_types"]))
            || !$check_dictionnary_type
        ) {
            if ($item = getItemForItemtype($typeclass)) {
                return $item;
            }
            return null;
        }
    }


    public function showInheritedTab()
    {
        return false;
    }


    public function showChildrensTab()
    {
        return false;
    }


    /**
     * Get all the fields needed to perform the rule
     **/
    public function getFieldsToLookFor()
    {
        global $DB;

        $params = [];

        $iterator = $DB->request([
            'SELECT'          => 'glpi_rulecriterias.criteria',
            'DISTINCT'        => true,
            'FROM'            => 'glpi_rulecriterias',
            'INNER JOIN'      => [
                'glpi_rules'   => [
                    'ON' => [
                        'glpi_rulecriterias' => 'rules_id',
                        'glpi_rules'         => 'id'
                    ]
                ]
            ],
            'WHERE'           => [
                'glpi_rules.is_active'  => 1,
                'glpi_rules.sub_type'   => $this->getRuleClassName()
            ]
        ]);

        while ($data = $iterator->next()) {
            $params[] = Toolbox::strtolower($data["criteria"]);
        }
        return $params;
    }


    /**
     * For tabs management : force isNewItem
     *
     * @since 0.83
     **/
    public function isNewItem()
    {
        return false;
    }


    /**
     * @see CommonGLPI::defineTabs()
     **/
    public function defineTabs($options = [])
    {

        $ong               = [];
        $this->addStandardTab(__CLASS__, $ong, $options);
        $ong['no_all_tab'] = true;
        return $ong;
    }


    /**
     * @see CommonGLPI::getTabNameForItem()
     **/
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if ($item instanceof RuleCollection) {
            $ong = [];
            if ($item->showInheritedTab()) {
                //TRANS: %s is the entity name
                $ong[1] = sprintf(
                    __('Rules applied: %s'),
                    Dropdown::getDropdownName(
                        'glpi_entities',
                        $_SESSION['glpiactive_entity']
                    )
                );
            }
            $title = _n('Rule', 'Rules', Session::getPluralNumber());
            if ($item->isRuleRecursive()) {
                //TRANS: %s is the entity name
                $title = sprintf(
                    __('Local rules: %s'),
                    Dropdown::getDropdownName(
                        'glpi_entities',
                        $_SESSION['glpiactive_entity']
                    )
                );
            }
            $ong[2] = $title;
            if ($item->showChildrensTab()) {
                $ong[3] = __('Rules applicable in the sub-entities');
            }
            return $ong;
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item instanceof RuleCollection) {
            $options = $_GET;
            switch ($tabnum) {
                case 1:
                    $options['inherited'] = 1;
                    break;

                case 2:
                    $options['inherited'] = 0;
                    break;

                case 3:
                    $options['inherited'] = 0;
                    $options['childrens'] = 1;
                    break;
            }
            if ($item->isRuleEntityAssigned()) {
                $item->setEntity($_SESSION['glpiactive_entity']);
            }
            $item->title();
            $item->showEngineSummary();
            $item->showListRules(Toolbox::cleanTarget($_GET['_target'] ?? ''), $options);
            return true;
        }
        return false;
    }

    /**
     * Get list of dictionnaries
     *
     * @return array
     */
    public static function getDictionnaries()
    {
        $dictionnaries = [];

        $entries = [];

        if (Session::haveRight("rule_dictionnary_software", READ)) {
            $entries[] = [
                'label'  => _n('Software', 'Software', Session::getPluralNumber()),
                'link'   => 'ruledictionnarysoftware.php'
            ];
        }

        if (Session::haveRight("rule_dictionnary_dropdown", READ)) {
            $entries[] = [
                'label'  => _n('Manufacturer', 'Manufacturers', Session::getPluralNumber()),
                'link'   => 'ruledictionnarymanufacturer.php'
            ];
        }

        if (Session::haveRight("rule_dictionnary_printer", READ)) {
            $entries[] = [
                'label'  => _n('Printer', 'Printers', Session::getPluralNumber()),
                'link'   => 'ruledictionnaryprinter.php'
            ];
        }

        if (count($entries)) {
            $dictionnaries[] = [
                'type'      => __('Global dictionary'),
                'entries'   => $entries
            ];
        }

        if (Session::haveRight("rule_dictionnary_dropdown", READ)) {
            $dictionnaries[] = [
                'type'      => _n('Model', 'Models', Session::getPluralNumber()),
                'entries'   => [
                    [
                        'label'  => _n('Computer model', 'Computer models', Session::getPluralNumber()),
                        'link'   => 'ruledictionnarycomputermodel.php'
                    ], [
                        'label'  => _n('Monitor model', 'Monitor models', Session::getPluralNumber()),
                        'link'   => 'ruledictionnarymonitormodel.php'
                    ], [
                        'label'  => _n('Printer model', 'Printer models', Session::getPluralNumber()),
                        'link'   => 'ruledictionnaryprintermodel.php'
                    ], [
                        'label'  => _n('Device model', 'Device models', Session::getPluralNumber()),
                        'link'   => 'ruledictionnaryperipheralmodel.php'
                    ], [
                        'label'  => _n('Network equipment model', 'Network equipment models', Session::getPluralNumber()),
                        'link'   => 'ruledictionnarynetworkequipmentmodel.php'
                    ], [
                        'label'  => _n('Phone model', 'Phone models', Session::getPluralNumber()),
                        'link'   => 'ruledictionnaryphonemodel.php'
                    ]
                ]
            ];
        }

        if (Session::haveRight("rule_dictionnary_dropdown", READ)) {
            $dictionnaries[] = [
                'type'      => _n('Type', 'Types', Session::getPluralNumber()),
                'entries'   => [
                    [
                        'label'  => _n('Computer type', 'Computer types', Session::getPluralNumber()),
                        'link'   => 'ruledictionnarycomputertype.php'
                    ], [
                        'label'  => _n('Monitor type', 'Monitor types', Session::getPluralNumber()),
                        'link'   => 'ruledictionnarymonitortype.php'
                    ], [
                        'label'  => _n('Printer type', 'Printer types', Session::getPluralNumber()),
                        'link'   => 'ruledictionnaryprintertype.php'
                    ], [
                        'label'  => _n('Device type', 'Device types', Session::getPluralNumber()),
                        'link'   => 'ruledictionnaryperipheraltype.php'
                    ], [
                        'label'  => _n('Network equipment type', 'Network equipment types', Session::getPluralNumber()),
                        'link'   => 'ruledictionnarynetworkequipmenttype.php'
                    ], [
                        'label'  => _n('Phone type', 'Phone types', Session::getPluralNumber()),
                        'link'   => 'ruledictionnaryphonetype.php'
                    ]
                ]
            ];
        }

        if (Session::haveRight("rule_dictionnary_dropdown", READ)) {
            $dictionnaries[] = [
                'type'      => OperatingSystem::getTypeName(Session::getPluralNumber()),
                'entries'   => [
                    [
                        'label'  => OperatingSystem::getTypeName(Session::getPluralNumber()),
                        'link'   => 'ruledictionnaryoperatingsystem.php'
                    ], [
                        'label'  => _n('Service pack', 'Service packs', Session::getPluralNumber()),
                        'link'   => 'ruledictionnaryoperatingsystemservicepack.php'
                    ], [
                        'label'  => _n('Version', 'Versions', Session::getPluralNumber()),
                        'link'   => 'ruledictionnaryoperatingsystemversion.php'
                    ], [
                        'label'  => _n('Architecture', 'Architectures', Session::getPluralNumber()),
                        'link'   => 'ruledictionnaryoperatingsystemarchitecture.php'
                    ]
                ]
            ];
        }

        return $dictionnaries;
    }
}
