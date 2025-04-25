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

/// Criteria Rule class
class RuleCriteria extends CommonDBChild
{
    // From CommonDBChild
    public static $itemtype        = 'Rule';
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
     * @param $rule_type (default 'Rule)
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
     * @param $nb  integer  for singular or plural (default 0)
     *
     * @return Title of the rule
    **/
    public static function getTypeName($nb = 0)
    {
        return _n('Criterion', 'Criteria', $nb);
    }

    protected function computeFriendlyName()
    {

        if ($rule = getItemForItemtype(static::$itemtype)) {
            return Html::clean($rule->getMinimalCriteriaText($this->fields));
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

        if (!isset($input['criteria']) || empty($input['criteria'])) {
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
           'field'              => 'criteria',
           'name'               => __('Name'),
           'massiveaction'      => false,
           'datatype'           => 'specific',
           'additionalfields'   => ['rules_id']
        ];

        $tab[] = [
           'id'                 => '2',
           'table'              => $this->getTable(),
           'field'              => 'conditions',
           'name'               => __('Condition'),
           'massiveaction'      => false,
           'datatype'           => 'specific',
           'additionalfields'   => ['rules_id', 'criteria']
        ];

        $tab[] = [
           'id'                 => '3',
           'table'              => $this->getTable(),
           'field'              => 'pattern',
           'name'               => __('Reason'),
           'massiveaction'      => false,
           'datatype'           => 'specific',
           'additionalfields'   => ['rules_id', 'criteria', 'conditions'],
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
            case 'criteria':
                $generic_rule = new Rule();
                if (
                    isset($values['rules_id'])
                    && !empty($values['rules_id'])
                    && $generic_rule->getFromDB($values['rules_id'])
                ) {
                    if ($rule = getItemForItemtype($generic_rule->fields["sub_type"])) {
                        return $rule->getCriteriaName($values[$field]);
                    }
                }
                break;

            case 'conditions':
                $generic_rule = new Rule();
                if (
                    isset($values['rules_id'])
                    && !empty($values['rules_id'])
                    && $generic_rule->getFromDB($values['rules_id'])
                ) {
                    if (isset($values['criteria']) && !empty($values['criteria'])) {
                        $criterion = $values['criteria'];
                    }
                    return self::getConditionByID($values[$field], $generic_rule->fields["sub_type"], $criterion);
                }
                break;

            case 'pattern':
                if (!isset($values["criteria"]) || !isset($values["conditions"])) {
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
                            $values["conditions"],
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
            case 'criteria':
                $generic_rule = new Rule();
                if (
                    isset($values['rules_id'])
                    && !empty($values['rules_id'])
                    && $generic_rule->getFromDB($values['rules_id'])
                ) {
                    if ($rule = getItemForItemtype($generic_rule->fields["sub_type"])) {
                        $options['value'] = $values[$field];
                        $options['name']  = $name;
                        return $rule->dropdownCriteria($options);
                    }
                }
                break;

            case 'conditions':
                $generic_rule = new Rule();
                if (
                    isset($values['rules_id'])
                    && !empty($values['rules_id'])
                    && $generic_rule->getFromDB($values['rules_id'])
                ) {
                    if (isset($values['criteria']) && !empty($values['criteria'])) {
                        $options['criterion'] = $values['criteria'];
                    }
                    $options['value'] = $values[$field];
                    $options['name']  = $name;
                    return $rule->dropdownConditions($generic_rule->fields["sub_type"], $options);
                }
                break;

            case 'pattern':
                if (!isset($values["criteria"]) || !isset($values["conditions"])) {
                    return NOT_AVAILABLE;
                }
                $generic_rule = new Rule();
                if (
                    isset($values['rules_id'])
                    && !empty($values['rules_id'])
                    && $generic_rule->getFromDB($values['rules_id'])
                ) {
                    if ($rule = getItemForItemtype($generic_rule->fields["sub_type"])) {
                        /// TODO : manage display param to this function : need to send ot to all under functions
                        $rule->displayCriteriaSelectPattern(
                            $name,
                            $values["criteria"],
                            $values["conditions"],
                            $values[$field]
                        );
                    }
                }
                break;
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }


    /**
     * Get all criterias for a given rule
     *
     * @param $rules_id the rule ID
     *
     * @return an array of RuleCriteria objects
    **/
    public function getRuleCriterias($rules_id)
    {
        global $DB;

        $rules_list = [];
        $params = ['FROM'  => $this->getTable(),
                   'WHERE' => [static::$items_id => $rules_id],
                   'ORDER' => 'id'
                  ];
        foreach ($DB->request($params) as $rule) {
            $tmp          = new self();
            $tmp->fields  = $rule;
            $rules_list[] = $tmp;
        }
        return $rules_list;
    }


    /**
     * Try to match a defined rule
     *
     * @param &$criterion         RuleCriteria object
     * @param $field              the field to match
     * @param &$criterias_results
     * @param &$regex_result
     *
     * @return true if the field match the rule, false if it doesn't match
    **/
    public static function match(RuleCriteria &$criterion, $field, &$criterias_results, &$regex_result)
    {

        $condition = $criterion->fields['conditions'];
        $pattern   = $criterion->fields['pattern'];
        $criteria  = $criterion->fields['criteria'];

        //If pattern is wildcard, don't check the rule and return true
        //or if the condition is "already present in GLPI" : will be processed later
        if (
            ($pattern == Rule::RULE_WILDCARD)
            || ($condition == Rule::PATTERN_FIND)
        ) {
            return true;
        }

        $pattern = trim($pattern);

        switch ($condition) {
            case Rule::PATTERN_EXISTS:
                return (!empty($field));

            case Rule::PATTERN_DOES_NOT_EXISTS:
                return (empty($field));

            case Rule::PATTERN_IS:
                if (is_array($field)) {
                    // Special case (used only by UNIQUE_PROFILE, for now)
                    // $pattern is an ID
                    if (in_array($pattern, $field)) {
                        $criterias_results[$criteria] = $pattern;
                        return true;
                    }
                } else {
                    //Perform comparison with fields in lower case
                    $field                        = Toolbox::strtolower($field);
                    $pattern                      = Toolbox::strtolower($pattern);
                    if ($field == $pattern) {
                        $criterias_results[$criteria] = $pattern;
                        return true;
                    }
                }
                return false;

            case Rule::PATTERN_IS_NOT:
                //Perform comparison with fields in lower case
                $field   = Toolbox::strtolower($field);
                $pattern = Toolbox::strtolower($pattern);
                if ($field != $pattern) {
                    $criterias_results[$criteria] = $pattern;
                    return true;
                }
                return false;

            case Rule::PATTERN_UNDER:
                $table  = getTableNameForForeignKeyField($criteria);
                $values = getSonsOf($table, $pattern);
                if (isset($values[$field])) {
                    return true;
                }
                return false;

            case Rule::PATTERN_NOT_UNDER:
                $table  = getTableNameForForeignKeyField($criteria);
                $values = getSonsOf($table, $pattern);
                if (isset($values[$field])) {
                    return false;
                }
                return true;

            case Rule::PATTERN_END:
                $value = "/" . $pattern . "$/i";
                if (preg_match($value, $field) > 0) {
                    $criterias_results[$criteria] = $pattern;
                    return true;
                }
                return false;

            case Rule::PATTERN_BEGIN:
                if (empty($pattern)) {
                    return false;
                }
                $value = mb_stripos($field, $pattern, 0, 'UTF-8');
                if (($value !== false) && ($value == 0)) {
                    $criterias_results[$criteria] = $pattern;
                    return true;
                }
                return false;

            case Rule::PATTERN_CONTAIN:
                if (empty($pattern)) {
                    return false;
                }
                $value = mb_stripos($field, $pattern, 0, 'UTF-8');
                if (($value !== false) && ($value >= 0)) {
                    $criterias_results[$criteria] = $pattern;
                    return true;
                }
                return false;

            case Rule::PATTERN_NOT_CONTAIN:
                if (empty($pattern)) {
                    return false;
                }
                $value = mb_stripos($field, $pattern, 0, 'UTF-8');
                if ($value === false) {
                    $criterias_results[$criteria] = $pattern;
                    return true;
                }
                return false;

            case Rule::REGEX_MATCH:
                $results = [];
                // Permit use < and >
                $pattern = Toolbox::unclean_cross_side_scripting_deep($pattern);
                if (preg_match_all($pattern . "i", $field, $results) > 0) {
                    // Drop $result[0] : complete match result
                    array_shift($results);
                    // And add to $regex_result array
                    $res = [];
                    foreach ($results as $data) {
                        foreach ($data as $val) {
                            $res[] = $val;
                        }
                    }
                    $regex_result[]               = $res;
                    $criterias_results[$criteria] = $pattern;
                    return true;
                }
                return false;

            case Rule::REGEX_NOT_MATCH:
                // Permit use < and >
                $pattern = Toolbox::unclean_cross_side_scripting_deep($pattern);
                if (preg_match($pattern . "i", $field) == 0) {
                    $criterias_results[$criteria] = $pattern;
                    return true;
                }
                return false;

            case Rule::PATTERN_FIND:
            case Rule::PATTERN_IS_EMPTY:
                // Global criteria will be evaluated later
                return true;
        }
        return false;
    }


    /**
     * Return the condition label by giving his ID
     *
     * @param $ID        condition's ID
     * @param $itemtype  itemtype
     * @param $criterion (default '')
     *
     * @return condition's label
    **/
    public static function getConditionByID($ID, $itemtype, $criterion = '')
    {

        $conditions = self::getConditions($itemtype, $criterion);
        if (isset($conditions[$ID])) {
            return $conditions[$ID];
        }
        return "";
    }


    /**
     * @param $itemtype  itemtype
     * @param $criterion (default '')
     *
     * @return array of criteria
    **/
    public static function getConditions($itemtype, $criterion = '')
    {

        $criteria =  [Rule::PATTERN_IS              => __('is'),
                           Rule::PATTERN_IS_NOT          => __('is not'),
                           Rule::PATTERN_CONTAIN         => __('contains'),
                           Rule::PATTERN_NOT_CONTAIN     => __('does not contain'),
                           Rule::PATTERN_BEGIN           => __('starting with'),
                           Rule::PATTERN_END             => __('finished by'),
                           Rule::REGEX_MATCH             => __('regular expression matches'),
                           Rule::REGEX_NOT_MATCH         => __('regular expression does not match'),
                           Rule::PATTERN_EXISTS          => __('exists'),
                           Rule::PATTERN_DOES_NOT_EXISTS => __('does not exist')];

        $extra_criteria = call_user_func([$itemtype, 'addMoreCriteria'], $criterion);

        foreach ($extra_criteria as $key => $value) {
            $criteria[$key] = $value;
        }

        /// Add Under criteria if tree dropdown table used
        if ($item = getItemForItemtype($itemtype)) {
            $crit = $item->getCriteria($criterion);

            if (isset($crit['type']) && ($crit['type'] == 'dropdown')) {
                $crititemtype = getItemTypeForTable($crit['table']);

                if (
                    ($item = getItemForItemtype($crititemtype))
                    && $item instanceof CommonTreeDropdown
                ) {
                    $criteria[Rule::PATTERN_UNDER]     = __('under');
                    $criteria[Rule::PATTERN_NOT_UNDER] = __('not under');
                }
            }
        }

        return $criteria;
    }


    /**
     * Display a dropdown with all the criterias
     *
     * @param $itemtype
     * @param $params    array
    **/
    public static function dropdownConditions($itemtype, $params = [])
    {

        $p['name']             = 'conditions';
        $p['criterion']        = '';
        $p['allow_conditions'] = [];
        $p['value']            = '';
        $p['display']          = true;

        foreach ($params as $key => $value) {
            $p[$key] = $value;
        }
        $elements = [];
        foreach (self::getConditions($itemtype, $p['criterion']) as $pattern => $label) {
            if (
                empty($p['allow_conditions'])
                || (!empty($p['allow_conditions']) && in_array($pattern, $p['allow_conditions']))
            ) {
                $elements[$pattern] = $label;
            }
        }
        return Dropdown::showFromArray($p['name'], $elements, ['value' => $p['value']]);
    }


    /** form for rule criteria
     *
     * @since 0.85
     *
     * @param $ID      integer  Id of the criteria
     * @param $options array    of possible options:
     *     - rule Object : the rule
    **/
    public function showForm($ID, $options = [])
    {
        global $CFG_GLPI;

        // Yllen: you always have parent for criteria
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

        $group      = [];
        $groupname  = _n('Criterion', 'Criteria', Session::getPluralNumber());
        foreach ($rule->getAllCriteria() as $id => $crit) {
            // Manage group system
            if (!is_array($crit)) {
                if (count($group)) {
                    asort($group);
                    $items[$groupname] = $group;
                }
                $group     = [];
                $groupname = $crit;
            } else {
                $group[$id] = $crit['name'];
            }
        }
        if (count($group)) {
            asort($group);
            $items[$groupname] = $group;
        }

        $form = [
          'action' => Toolbox::getItemTypeFormURL(__CLASS__),
          'itemtype' => self::class,
          'content' => [
              $this->getTypeName() => [
                  'visible' => true,
                  'inputs' => [
                      $this->isNewID($ID) ? [] : [
                          'type'  => 'hidden',
                          'name'  => 'id',
                          'value' => $ID,
                      ],
                      [
                          'type'  => 'hidden',
                          'name'  => $rule->getRuleIdField(),
                          'value' => $this->fields[$rule->getRuleIdField()],
                      ],
                      _n('Criterion', 'Criteria', 1) => [
                          'type'  => 'select',
                          'name'  => 'criteria',
                          'value' => $this->fields['criteria'],
                          'values' => [Dropdown::EMPTY_VALUE] + $items,
                          'actions' => getItemActionButtons(['add'], $this::class),
                          'col_lg' => 12,
                          'col_md' => 12,
                          'hooks' => [
                              'change' => <<<JS
                            var criteria = $(this).val();
                            var params = {'criteria': criteria, 'sub_type': '{$rule->getType()}'};
                            $.post('{$CFG_GLPI['root_doc']}/ajax/rulecriteria.php', params, function(data) {
                                $('#criteria_span').html(data);
                            });
                            JS,
                          ]
                      ],
                      '' => [
                          'content' => '<span id="criteria_span"></span>',
                          'col_lg' => 12,
                          'col_md' => 12,
                      ],
                  ],
              ]
          ]
        ];
        renderTwigForm($form, '', $this->fields);
    }
}
