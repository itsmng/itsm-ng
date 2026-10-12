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

namespace tests\units;

class Oidc extends \DbTestCase
{
    protected function accountTypes()
    {
        return [[null], [\Auth::EXTERNAL], [\Auth::DB_GLPI], [\Auth::LDAP]];
    }

    /**
     * @dataProvider accountTypes
     */
    public function testAuthorizationRules($authtype)
    {
        global $DB;

        $this->login();
        $user = new \User();
        $input = ['name' => '_oidc_rules_user_' . $authtype];
        if ($authtype === null) {
            $input += ['_extauth' => 1, 'add' => 1];
        } else {
            $input += ['authtype' => $authtype, 'auths_id' => 0];
        }
        $id = $user->add($input);
        $this->boolean($user->getFromDB($id))->isTrue();
        $storedAuthType = (int) $user->fields['authtype'];
        $this->integer($id)->isGreaterThan(0);
        $entity = getItemByTypeName('Entity', '_test_child_1', true);
        $group = new \Group();
        $groupId = $group->add(['name' => '_oidc_rules_group_' . $authtype, 'entities_id' => 0]);
        $this->integer($groupId)->isGreaterThan(0);
        $manual = new \Profile_User();
        $manualId = $manual->add([
            'users_id' => $id, 'profiles_id' => 1, 'entities_id' => 0, 'is_dynamic' => 0,
        ]);
        $this->integer($manualId)->isGreaterThan(0);

        $DB->delete('glpi_oidc_mapping', [1 => 1]);
        $DB->insert('glpi_oidc_mapping', ['group' => 'groups', 'email' => 'email']);
        $rule = new \RuleRight();
        $ruleId = $rule->add([
            'name' => '_oidc_group_rule_' . $authtype, 'sub_type' => 'RuleRight',
            'match' => 'AND', 'is_active' => 1, 'entities_id' => 0, 'is_recursive' => 1,
        ]);
        foreach (['_groups_id' => $groupId, 'TYPE' => \Auth::EXTERNAL,
                  'LOGIN' => '_oidc_rules_user_' . $authtype, 'MAIL_EMAIL' => 'oidc@example.org'] as $field => $value) {
            (new \RuleCriteria())->add([
                'rules_id' => $ruleId, 'criteria' => $field,
                'condition' => \Rule::PATTERN_IS, 'pattern' => $value,
            ]);
        }
        foreach (['entities_id' => $entity, 'profiles_id' => 5, 'is_recursive' => 1] as $field => $value) {
            (new \RuleAction())->add([
                'rules_id' => $ruleId, 'action_type' => 'assign', 'field' => $field, 'value' => $value,
            ]);
        }
        $rights = ['users_id' => $id, 'entities_id' => $entity, 'profiles_id' => 5, 'is_dynamic' => 1];
        $emailRule = new \RuleRight();
        $emailRuleId = $emailRule->add([
            'name' => '_oidc_email_rule_' . $authtype, 'sub_type' => 'RuleRight',
            'match' => 'AND', 'is_active' => 1, 'entities_id' => 0, 'is_recursive' => 1,
        ]);
        (new \RuleCriteria())->add([
            'rules_id' => $emailRuleId, 'criteria' => 'MAIL_EMAIL',
            'condition' => \Rule::PATTERN_IS, 'pattern' => 'oidc@example.org',
        ]);
        $emailEntity = getItemByTypeName('Entity', '_test_child_2', true);
        foreach (['entities_id' => $emailEntity, 'profiles_id' => 5] as $field => $value) {
            (new \RuleAction())->add([
                'rules_id' => $emailRuleId, 'action_type' => 'assign', 'field' => $field, 'value' => $value,
            ]);
        }
        $emailRights = array_replace($rights, ['entities_id' => $emailEntity]);
        $this->integer(countElementsInTable('glpi_profiles_users', $rights))->isIdenticalTo(0);
        $this->integer(countElementsInTable('glpi_profiles_users', $emailRights))->isIdenticalTo(0);
        $this->array(\Group_User::getUserGroups($id))->isEmpty();
        try {
            $this->resetRules();
            foreach ([1, 2] as $login) {
                \Oidc::addUserData(['groups' => ['_oidc_rules_group_' . $authtype], 'email' => 'oidc@example.org'], $id);
                $this->integer(countElementsInTable('glpi_profiles_users', $rights))->isIdenticalTo(1);
                $this->integer(countElementsInTable('glpi_profiles_users', $emailRights))->isIdenticalTo(1);
                $this->boolean($user->getFromDB($id))->isTrue();
                $this->integer((int) $user->fields['authtype'])->isIdenticalTo($storedAuthType);
                $auth = new \Auth();
                $auth->auth_succeded = true;
                $auth->user = $user;
                \Session::init($auth);
                $sessionEntities = array_column($_SESSION['glpiprofiles'][5]['entities'], 'id');
                $this->array($sessionEntities)->contains($entity)->contains($emailEntity);
            }
            $rule->update(['id' => $ruleId, 'is_active' => 0]);
            $emailRule->update(['id' => $emailRuleId, 'is_active' => 0]);
            $this->resetRules();
            \Oidc::addUserData([], $id);
            $this->integer(countElementsInTable('glpi_profiles_users', $rights))->isIdenticalTo(0);
            $this->boolean($manual->getFromDB($manualId))->isTrue();
        } finally {
            $this->resetRules();
        }
    }

    private function resetRules()
    {
        $rules = \SingletonRuleList::getInstance('RuleRight', 0);
        $rules->load = 0;
        $rules->list = [];
    }
}
