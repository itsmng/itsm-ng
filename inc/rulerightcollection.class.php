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


/// Rule collection class for Rights management
class RuleRightCollection extends RuleCollection
{
    // From RuleCollection
    public $stop_on_first_match = false;
    public static $rightname           = 'rule_ldap';
    public $orderby             = "name";
    public $menu_option         = 'right';

    // Specific ones
    /// Array containing results : entity + right
    public $rules_entity_rights = [];
    /// Array containing results : only entity
    public $rules_entity        = [];
    /// Array containing results : only right
    public $rules_rights        = [];


    public function getTitle()
    {
        return __('Authorizations assignment rules');
    }


    /**
     * @see RuleCollection::cleanTestOutputCriterias()
    */
    public function cleanTestOutputCriterias(array $output)
    {

        if (isset($output["_rule_process"])) {
            unset($output["_rule_process"]);
        }
        return $output;
    }


    /**
     * @see RuleCollection::showTestResults()
    */
    public function showTestResults($rule, array $output, $global_result)
    {
        $actions = $rule->getActions();
        $values = [['group' => __('Rule results'), 'field' => _n('Validation', 'Validations', 1),
            'value' => Dropdown::getYesNo($global_result)]];
        $add = static function ($group, $field, $value) use (&$values) {
            switch ($field) {
                case 'entity':
                    $label = Entity::getTypeName(1);
                    $display = Dropdown::getDropdownName('glpi_entities', $value);
                    break;
                case 'profile':
                    $label = _n('Profile', 'Profiles', Session::getPluralNumber());
                    $display = Dropdown::getDropdownName('glpi_profiles', $value);
                    break;
                default:
                    $label = __('Recursive');
                    $display = Dropdown::getYesNo($value);
            }
            $values[] = ['group' => $group, 'field' => $label, 'value' => $display];
        };
        foreach ($output['_ldap_rules']['rules_entities'] ?? [] as $entities) {
            foreach ($entities as $entity) {
                $add(__('Entities assignment'), 'entity', $entity[0]);
                if (isset($entity[1])) {
                    $add(__('Entities assignment'), 'is_recursive', $entity[1]);
                }
            }
        }
        foreach ($output['_ldap_rules']['rules_rights'] ?? [] as $assignment) {
            $add(__('Rights assignment'), 'profile', $assignment[0]);
        }
        foreach ($output['_ldap_rules']['rules_entities_rights'] ?? [] as $assignment) {
            foreach ((array) $assignment[0] as $entity) {
                $add(__('Rights and entities assignment'), 'entity', $entity);
            }
            if (isset($assignment[1])) {
                $add(__('Rights and entities assignment'), 'profile', $assignment[1]);
            }
            if (isset($assignment[2])) {
                $add(__('Rights and entities assignment'), 'is_recursive', $assignment[2]);
            }
        }
        unset($output['_ldap_rules']);
        foreach ($output as $field => $value) {
            if (isset($actions[$field])) {
                $values[] = ['group' => __('Rule results'), 'field' => $actions[$field]['name'],
                    'value' => $rule->getActionValue($field, $actions[$field]['action_type'] ?? '', $value)];
            }
        }
        renderTwigTemplate('table.twig', [
            'id' => 'ruleRightResults', 'state_key' => 'ruleRightResults',
            'fields' => ['group' => __('Type'), 'field' => _n('Field', 'Fields', 1), 'value' => __('Value')],
            'values' => $values, 'pageSize' => max(1, (int) ($_SESSION['glpilist_limit'] ?? 15)),
        ]);
    }


    /**
     * Display action using its name
     *
     * @param $name   action name
     * @param $value  default value
    **/
    public function displayActionByName($name, $value)
    {

        echo "<tr class='tab_bg_2'>";
        switch ($name) {
            case "entity":
                echo "<td class='center'>" . Entity::getTypeName(1) . " </td>\n";
                echo "<td class='center'>" . Dropdown::getDropdownName("glpi_entities", $value) . "</td>";
                break;

            case "profile":
                echo "<td class='center'>" . _n('Profile', 'Profiles', Session::getPluralNumber()) . " </td>\n";
                echo "<td class='center'>" . Dropdown::getDropdownName("glpi_profiles", $value) . "</td>";
                break;

            case "is_recursive":
                echo "<td class='center'>" . __('Recursive') . " </td>\n";
                echo "<td class='center'>" . Dropdown::getYesNo($value) . "</td>";
                break;
        }
        echo "</tr>";
    }


    /**
     * Get all the fields needed to perform the rule
     *
     * @see RuleCollection::getFieldsToLookFor()
    **/
    public function getFieldsToLookFor()
    {
        global $DB;

        $params = [];
        $iterator = $DB->request([
           'SELECT'          => 'value',
           'DISTINCT'        => true,
           'FROM'            => 'glpi_rulerightparameters',
           'LEFT JOIN'       => [
              'glpi_rulecriterias' => [
                 'ON' => [
                    'glpi_rulerightparameters' => 'value',
                    'glpi_rulecriterias'       => 'criteria'
                 ]
              ],
              'glpi_rules'         => [
                 'ON' => [
                    'glpi_rulecriterias' => 'rules_id',
                    'glpi_rules'         => 'id'
                 ]
              ]
           ],
           'WHERE'           => ['glpi_rules.sub_type' => 'RuleRight']
        ]);

        while ($param = $iterator->next()) {
            //Dn is alwsays retreived from ldap : don't need to ask for it !
            if ($param["value"] != "dn") {
                $params[] = Toolbox::strtolower($param["value"]);
            }
        }
        return $params;
    }


    /**
     * Get the attributes needed for processing the rules
     *
     * @see RuleCollection::prepareInputDataForProcess()
     *
     * @param $input  input datas
     * @param $params extra parameters given
     *
     * @return an array of attributes
    **/
    public function prepareInputDataForProcess($input, $params)
    {
        $groups = [];
        if (isset($input) && is_array($input)) {
            $groups = $input;
        }

        //common parameters
        $rule_parameters = [
           'TYPE'       => $params["type"] ?? "",
           'OIDC_SOURCE' => $params['oidc_provider'] ?? 0,
           'LOGIN'      => $params["login"] ?? "",
           'MAIL_EMAIL' => $params["email"] ?? $params["mail_email"] ?? "",
           '_groups_id' => $groups
        ];

        //IMAP/POP login method
        if ($params["type"] == Auth::MAIL) {
            $rule_parameters["MAIL_SERVER"] = $params["mail_server"] ?? "";
        }

        //LDAP type method
        if ($params["type"] == Auth::LDAP) {
            //Get all the field to retrieve to be able to process rule matching
            $rule_fields = $this->getFieldsToLookFor();

            //Get all the datas we need from ldap to process the rules
            $sz         = @ldap_read(
                $params["connection"],
                $params["userdn"],
                "objectClass=*",
                $rule_fields
            );
            $rule_input = AuthLDAP::get_entries_clean($params["connection"], $sz);

            if (count($rule_input)) {
                $rule_input = $rule_input[0];
                //Get all the ldap fields
                $fields = $this->getFieldsForQuery();
                foreach ($fields as $field) {
                    switch (Toolbox::strtoupper($field)) {
                        case "LDAP_SERVER":
                            $rule_parameters["LDAP_SERVER"] = $params["ldap_server"];
                            break;

                        default: // ldap criteria (added by user)
                            if (isset($rule_input[$field])) {
                                if (!is_array($rule_input[$field])) {
                                    $rule_parameters[$field] = $rule_input[$field];
                                } else {
                                    if (count($rule_input[$field])) {
                                        foreach ($rule_input[$field] as $key => $val) {
                                            if ($key !== 'count') {
                                                $rule_parameters[$field][] = $val;
                                            }
                                        }
                                    }
                                }
                            }
                    }
                }
                return $rule_parameters;
            }
            return $rule_input;
        }

        return $rule_parameters;
    }


    /**
     * Get the list of fields to be retreived to process rules
    **/
    public function getFieldsForQuery()
    {

        $rule      = new RuleRight();
        $criterias = $rule->getCriterias();

        $fields = [];
        foreach ($criterias as $criteria) {
            if (!is_array($criteria)) {
                continue;
            }
            if (isset($criteria['virtual']) && $criteria['virtual']) {
                $fields[] = $criteria['id'];
            } else {
                $fields[] = $criteria['field'];
            }
        }
        return $fields;
    }
}
