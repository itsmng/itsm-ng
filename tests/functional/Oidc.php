<?php

/**
 * ---------------------------------------------------------------------
 * ITSM-NG
 * Copyright (C) 2022 ITSM-NG and contributors.
 *
 * https://www.itsm-ng.org/
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ITSM-NG.
 *
 * ITSM-NG is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ITSM-NG is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ITSM-NG. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

namespace tests\units;

class Oidc extends \DbTestCase
{
    private function provider(array $overrides = [])
    {
        $provider = new \OidcProvider();
        $id = $provider->add($overrides + ['name' => '_oidc_source', 'issuer' => 'https://issuer.example.org',
            'client_id' => 'client', 'client_secret' => 'secret', 'is_active' => 1, 'link_method' => 'none',
            'login_claim' => '/preferred_username', 'email_claim' => '/email', 'allow_provisioning' => 0]);
        $this->integer((int) $id)->isGreaterThan(0);
        return \OidcProvider::load($id);
    }

    private function user($name, $type = \Auth::EXTERNAL)
    {
        $user = new \User();
        $id = $user->add(\Toolbox::addslashes_deep(['name' => $name, 'authtype' => $type, 'auths_id' => 0, '_extauth' => 1]));
        $this->integer((int) $id)->isGreaterThan(0);
        $user->getFromDB($id);
        return $user;
    }

    private function claims(array $provider, $sub, array $values = [])
    {
        return ['id_token' => ['iss' => $provider['issuer'], 'sub' => $sub],
            'userinfo' => ['sub' => $sub] + $values];
    }

    private function mapping(array $provider, $target, $path, array $extra = [])
    {
        $mapping = new \OidcMapping();
        $id = $mapping->add($extra + ['oidcproviders_id' => $provider['id'], 'target' => $target, 'claim_path' => $path]);
        $this->integer((int) $id)->isGreaterThan(0);
        return $id;
    }

    public function testSecretStorageAndBlankUpdate()
    {
        $this->login();
        $provider = $this->provider();
        $this->string($provider['client_secret'])->isNotEqualTo('secret');
        $this->string(\Toolbox::sodiumDecrypt($provider['client_secret']))->isIdenticalTo('secret');
        $item = new \OidcProvider();
        $item->getFromDB($provider['id']);
        $this->boolean($item->update(['id' => $provider['id'], 'name' => 'Renamed', 'client_secret' => '']))->isTrue();
        $item->getFromDB($provider['id']);
        $this->string($item->fields['client_secret'])->isIdenticalTo($provider['client_secret']);
        $this->exception(function () {
            $this->provider(['issuer' => 'http://issuer.example.org']);
        })->isInstanceOf(\InvalidArgumentException::class);
    }

    public function testClaimPaths()
    {
        $claims = ['address' => (object) ['locality' => 'Paris'], 'chorusdt:matricule' => '123', 'a/b~c' => 0];
        $this->string(\OidcMapping::claim($claims, '/address/locality', $found))->isIdenticalTo('Paris');
        $this->boolean($found)->isTrue();
        $this->string(\OidcMapping::claim($claims, 'chorusdt:matricule'))->isIdenticalTo('123');
        $this->integer(\OidcMapping::claim($claims, '/a~1b~0c'))->isIdenticalTo(0);
        $this->variable(\OidcMapping::claim($claims, '/missing', $found))->isNull();
        $this->boolean($found)->isFalse();
        $this->exception(function () {
            \OidcMapping::claim([], '/bad~path');
        })->isInstanceOf(\InvalidArgumentException::class);
    }

    public function testMappingTargetValidation()
    {
        $this->login();
        $provider = $this->provider();
        foreach (['password', 'api_token', 'authtype', 'auths_id', 'profiles_id', 'entities_id', 'id'] as $target) {
            $this->exception(function () use ($provider, $target) {
                $this->mapping($provider, $target, '/value');
            })->isInstanceOf(\InvalidArgumentException::class);
        }
        $this->exception(function () use ($provider) {
            $this->mapping($provider, 'name', '/value', ['missing_policy' => 'clear']);
        })->isInstanceOf(\InvalidArgumentException::class);
    }

    public function testProviderAndSubjectIsolation()
    {
        $this->login();
        $a = $this->provider(['link_method' => 'username']);
        $b = $this->provider(['name' => 'Other', 'issuer' => 'https://other.example.org', 'link_method' => 'username']);
        $alice = $this->user('_oidc_alice');
        $bob = $this->user('_oidc_bob');
        $ca = $this->claims($a, 'shared-sub', ['preferred_username' => '_oidc_alice']);
        $cb = $this->claims($b, 'shared-sub', ['preferred_username' => '_oidc_bob']);
        $this->integer((int) \Oidc::authenticateClaims($a, $ca)->getID())->isIdenticalTo((int) $alice->getID());
        $this->integer((int) \Oidc::authenticateClaims($b, $cb)->getID())->isIdenticalTo((int) $bob->getID());
        $ca['userinfo']['preferred_username'] = '_oidc_bob';
        $this->integer((int) \Oidc::authenticateClaims($a, $ca)->getID())->isIdenticalTo((int) $alice->getID());
        $this->variable(\OidcIdentity::findUser($a, ['iss' => $a['issuer'], 'sub' => 'SHARED-SUB']))->isNull();
    }

    public function testEmailLinksSecondaryAddressAndPreservesLdap()
    {
        $this->login();
        $provider = $this->provider(['link_method' => 'email']);
        $user = $this->user('_oidc_ldap', \Auth::LDAP);
        $email = new \UserEmail();
        $email->add(['users_id' => $user->getID(), 'email' => 'primary@example.org', 'is_default' => 1]);
        $email->add(['users_id' => $user->getID(), 'email' => 'secondary@example.org']);
        $claims = $this->claims($provider, 'opaque-sub', ['email' => 'secondary@example.org', 'email_verified' => true]);
        $resolved = \Oidc::authenticateClaims($provider, $claims);
        $this->integer((int) $resolved->getID())->isIdenticalTo((int) $user->getID());
        $this->integer((int) $resolved->fields['authtype'])->isIdenticalTo(\Auth::LDAP);
        $this->string($resolved->fields['name'])->isIdenticalTo('_oidc_ldap');
        $claims['userinfo']['email'] = 'changed@example.org';
        $this->integer((int) \Oidc::authenticateClaims($provider, $claims)->getID())->isIdenticalTo((int) $user->getID());
    }

    public function testUnverifiedAndAmbiguousEmailAreRejected()
    {
        $this->login();
        $provider = $this->provider(['link_method' => 'email']);
        foreach (['_oidc_mail_a', '_oidc_mail_b'] as $name) {
            $user = $this->user($name);
            (new \UserEmail())->add(['users_id' => $user->getID(), 'email' => 'duplicate@example.org']);
        }
        $claims = $this->claims($provider, 'new', ['email' => 'duplicate@example.org', 'email_verified' => false]);
        $this->exception(function () use ($provider, $claims) {
            \Oidc::authenticateClaims($provider, $claims);
        })->hasMessage('Initial email linking requires a verified email claim.');
        $claims['userinfo']['email_verified'] = true;
        $this->exception(function () use ($provider, $claims) {
            \Oidc::authenticateClaims($provider, $claims);
        })->hasMessage('OIDC account matching is ambiguous.');
    }

    public function testInactiveAndInvalidIdentitiesAreRejected()
    {
        $this->login();
        $provider = $this->provider(['link_method' => 'username']);
        $user = $this->user('_oidc_disabled');
        $user->update(['id' => $user->getID(), 'is_active' => 0]);
        $claims = $this->claims($provider, 'disabled', ['preferred_username' => '_oidc_disabled']);
        $this->exception(function () use ($provider, $claims) {
            \Oidc::authenticateClaims($provider, $claims);
        })->hasMessage('The OIDC account is inactive or deleted.');
        $claims['userinfo']['sub'] = 'other';
        $this->exception(function () use ($provider, $claims) {
            \Oidc::authenticateClaims($provider, $claims);
        })->hasMessage('OIDC UserInfo subject does not match the ID token.');
        $claims = $this->claims($provider, 'x');
        $claims['id_token']['iss'] = strtoupper($provider['issuer']);
        $this->exception(function () use ($provider, $claims) {
            \Oidc::authenticateClaims($provider, $claims);
        })->hasMessage('Invalid OIDC issuer.');
    }

    public function testProvisioningPolicyAndUsernameCollision()
    {
        global $CFG_GLPI;
        $this->login();
        $provider = $this->provider(['allow_provisioning' => 1]);
        $claims = $this->claims($provider, 'new-user', ['preferred_username' => '_oidc_new']);
        $saved = $CFG_GLPI['is_users_auto_add'];
        try {
            $CFG_GLPI['is_users_auto_add'] = 0;
            $this->exception(function () use ($provider, $claims) {
                \Oidc::authenticateClaims($provider, $claims);
            })->hasMessage('OIDC user creation is disabled.');
            $CFG_GLPI['is_users_auto_add'] = 1;
            $user = \Oidc::authenticateClaims($provider, $claims);
            $this->string($user->fields['name'])->isIdenticalTo('_oidc_new');
            $this->integer((int) $user->fields['authtype'])->isIdenticalTo(\Auth::EXTERNAL);
            $other = $this->provider(['name' => 'Other', 'allow_provisioning' => 1]);
            $otherClaims = $this->claims($other, 'different-sub', ['preferred_username' => '_oidc_new']);
            $this->exception(function () use ($other, $otherClaims) {
                \Oidc::authenticateClaims($other, $otherClaims);
            })->hasMessage('The OIDC username already belongs to an unlinked account.');
        } finally {
            $CFG_GLPI['is_users_auto_add'] = $saved;
        }
    }

    public function testMappingsApplyBySourceAndPreserveMissingValues()
    {
        $this->login();
        $provider = $this->provider(['link_method' => 'username']);
        $user = $this->user('_oidc_mapping');
        $this->mapping($provider, 'firstname', '/person/given', ['transform' => 'trim']);
        $this->mapping($provider, 'realname', '/family', ['claim_source' => 'id_token']);
        $this->mapping($provider, 'phone', '/phone', ['missing_policy' => 'clear']);
        $claims = $this->claims($provider, 'mapping-sub', ['preferred_username' => '_oidc_mapping', 'person' => ['given' => '  Alice  ']]);
        $claims['id_token']['family'] = 'Example';
        $resolved = \Oidc::authenticateClaims($provider, $claims);
        $this->string($resolved->fields['firstname'])->isIdenticalTo('Alice');
        $this->string($resolved->fields['realname'])->isIdenticalTo('Example');
        $this->variable($resolved->fields['phone'])->isNull();
        unset($claims['userinfo']['person']);
        $resolved = \Oidc::authenticateClaims($provider, $claims);
        $this->string($resolved->fields['firstname'])->isIdenticalTo('Alice');
    }

    public function testInvalidMappingRollsBackProvisioning()
    {
        global $CFG_GLPI, $DB;
        $this->login();
        $provider = $this->provider(['allow_provisioning' => 1]);
        $this->mapping($provider, 'firstname', '/given', ['missing_policy' => 'required']);
        $saved = $CFG_GLPI['is_users_auto_add'];
        try {
            $CFG_GLPI['is_users_auto_add'] = 1;
            $claims = $this->claims($provider, 'new', ['preferred_username' => '_oidc_invalid']);
            $this->exception(function () use ($provider, $claims) {
                \Oidc::authenticateClaims($provider, $claims);
            })->hasMessage('A required OIDC claim is missing.');
            $this->integer(count($DB->request(['FROM' => 'glpi_users', 'WHERE' => ['name' => '_oidc_invalid']])))->isIdenticalTo(0);
            $this->integer(count($DB->request(['FROM' => 'glpi_oidcidentities', 'WHERE' => ['oidcproviders_id' => $provider['id']]])))->isIdenticalTo(0);
        } finally {
            $CFG_GLPI['is_users_auto_add'] = $saved;
        }
    }

    public function testImportDoesNotRecordLogin()
    {
        $this->login();
        $provider = $this->provider();
        $user = $this->user('_oidc_import');
        $this->mapping($provider, 'firstname', '/given');
        $previous = $user->fields['last_login'];
        \Oidc::addUserData(['given' => 'Imported'], $user->getID(), $provider['id']);
        $user->getFromDB($user->getID());
        $this->string($user->fields['firstname'])->isIdenticalTo('Imported');
        $this->variable($user->fields['last_login'])->isIdenticalTo($previous);
        $identity = ['iss' => $provider['issuer'], 'sub' => 'imported-sub'];
        \OidcIdentity::bind($provider, $identity, $user->getID());
        \Oidc::recordLogin($provider, $identity, $user->getID());
        $user->getFromDB($user->getID());
        $this->string($user->fields['last_login'])->isIdenticalTo($_SESSION['glpi_currenttime']);
    }

    public function testConcurrentAttemptIsolationAndRedirects()
    {
        $_SESSION['_oidc_attempts'] = ['a' => ['state' => 'state-a', 'provider' => 1, 'expires' => time() + 60],
            'b' => ['state' => 'state-b', 'provider' => 2, 'expires' => time() + 60]];
        $this->string(\Oidc::attempt('state-a')[0])->isIdenticalTo('a');
        $this->string(\Oidc::attempt('state-b')[0])->isIdenticalTo('b');
        $this->exception(function () {
            \Oidc::attempt('invalid');
        })->isInstanceOf(\RuntimeException::class);
        $_SESSION['_oidc_attempts']['a']['expires'] = time() - 1;
        $this->exception(function () {
            \Oidc::attempt('state-a');
        })->isInstanceOf(\RuntimeException::class);
        foreach (['https://evil.example/', '//evil.example/', '/%2fevil.example/', '/a%0d%0ab', '/\\evil.example/'] as $redirect) {
            $this->variable(\Oidc::sanitizeRedirect($redirect))->isNull();
        }
        $this->string(\Oidc::sanitizeRedirect('/front/ticket.form.php?id=12'))->isIdenticalTo('/front/ticket.form.php?id=12');
        $this->string(\Oidc::sanitizeRedirect('ticket_12'))->isIdenticalTo('ticket_12');
    }

    public function testGroupsPreserveManualAndOtherSourceMemberships()
    {
        global $DB;
        $this->login();
        $a = $this->provider(['link_method' => 'username']);
        $b = $this->provider(['name' => 'B', 'link_method' => 'username']);
        $user = $this->user('_oidc_groups');
        $manual = (new \Group())->add(['name' => '_oidc_manual', 'entities_id' => 0]);
        (new \Group_User())->add(['users_id' => $user->getID(), 'groups_id' => $manual, 'is_dynamic' => 0]);
        foreach ([$a, $b] as $source) {
            $this->mapping($source, 'groups', '/groups', ['lookup_mode' => 'create', 'missing_policy' => 'clear']);
            $claims = $this->claims($source, 'groups-sub', ['preferred_username' => '_oidc_groups', 'groups' => ['_oidc_shared']]);
            \Oidc::authenticateClaims($source, $claims);
        }
        $this->integer(count($DB->request(['FROM' => 'glpi_groups_users', 'WHERE' => ['users_id' => $user->getID()]])))->isIdenticalTo(2);
        \Oidc::authenticateClaims($a, $this->claims($a, 'groups-sub', ['groups' => []]));
        $this->integer(count($DB->request(['FROM' => 'glpi_groups_users', 'WHERE' => ['users_id' => $user->getID()]])))->isIdenticalTo(2);
        \Oidc::authenticateClaims($b, $this->claims($b, 'groups-sub', ['groups' => []]));
        $remaining = iterator_to_array($DB->request(['FROM' => 'glpi_groups_users', 'WHERE' => ['users_id' => $user->getID()]]));
        $this->integer(count($remaining))->isIdenticalTo(1);
        $this->integer((int) reset($remaining)['groups_id'])->isIdenticalTo((int) $manual);
    }

    public function testPluginResolutionAndCustomMapping()
    {
        global $PLUGIN_HOOKS;
        $this->login();
        $this->boolean(\Plugin::isPluginActive('tester'))->isTrue();
        $provider = $this->provider(['link_method' => 'plugin', 'required_plugin' => 'tester']);
        $user = $this->user('_oidc_plugin', \Auth::LDAP);
        $saved = $PLUGIN_HOOKS;
        try {
            $PLUGIN_HOOKS['oidc_resolve_user']['tester'] = function ($context) use ($user) {
                $context['decision'] = 'match';
                $context['users_id'] = $user->getID();
                return $context;
            };
            $PLUGIN_HOOKS['oidc_mapping_targets']['tester'] = function ($targets) {
                $targets['organisation'] = ['label' => 'Organisation', 'type' => 'string',
                    'write' => function ($user, $value) {
                        $user->update(['id' => $user->getID(), 'comment' => $value]);
                    }];
                return $targets;
            };
            $this->mapping($provider, 'organisation', '/organisation');
            $claims = $this->claims($provider, 'plugin-sub', ['organisation' => 'Administration']);
            $resolved = \Oidc::authenticateClaims($provider, $claims);
            $this->integer((int) $resolved->getID())->isIdenticalTo((int) $user->getID());
            $this->string($resolved->fields['comment'])->isIdenticalTo('Administration');
            $PLUGIN_HOOKS['oidc_resolve_user']['tester'] = function ($context) {
                $context['decision'] = 'deny';
                return $context;
            };
            $otherClaims = $this->claims($provider, 'different-sub');
            $this->exception(function () use ($provider, $otherClaims) {
                \Oidc::authenticateClaims($provider, $otherClaims);
            })->hasMessage('OIDC login was denied by the source plugin.');
        } finally {
            $PLUGIN_HOOKS = $saved;
        }
    }

    public function testSourceAvailabilityAndReferenceScoping()
    {
        $this->login();
        $provider = $this->provider();
        $this->mapping($provider, 'locations_id', '/location', ['lookup_mode' => 'name']);
        $entity = getItemByTypeName('Entity', '_test_child_1', true);
        (new \Location())->add(['name' => '_oidc_scoped_location', 'entities_id' => $entity]);
        $claims = $this->claims($provider, 'sub', ['location' => '_oidc_scoped_location']);
        $this->exception(function () use ($provider, $claims) {
            \OidcMapping::values($provider['id'], $claims, ['entities_id' => 0], false);
        })->isInstanceOf(\RuntimeException::class);
        $this->array(\OidcMapping::values($provider['id'], $claims, ['entities_id' => $entity], false))->hasKey('locations_id');
        $item = new \OidcProvider();
        $item->getFromDB($provider['id']);
        $item->update(['id' => $provider['id'], 'is_active' => 0]);
        $this->exception(function () use ($provider) {
            \OidcProvider::load($provider['id']);
        })->isInstanceOf(\RuntimeException::class);
        $item->update(['id' => $provider['id'], 'is_active' => 1, 'required_plugin' => 'oidc_missing_plugin']);
        $this->exception(function () use ($provider) {
            \OidcProvider::load($provider['id']);
        })->isInstanceOf(\RuntimeException::class);
    }
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

        $provider = $this->provider();
        $this->mapping($provider, 'groups', '/groups', ['lookup_mode' => 'name']);
        $this->mapping($provider, 'email', '/email');
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
                \Oidc::addUserData(['groups' => ['_oidc_rules_group_' . $authtype], 'email' => 'oidc@example.org'], $id, $provider['id']);
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
            \Oidc::addUserData([], $id, $provider['id']);
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

    public function testAuthorizationRulesCanSelectAnOidcSource()
    {
        global $DB;
        $this->login();
        $a = $this->provider(['link_method' => 'username']);
        $b = $this->provider(['name' => 'Other', 'link_method' => 'username']);
        $user = $this->user('_oidc_source_rules', \Auth::LDAP);
        $entity = getItemByTypeName('Entity', '_test_child_2', true);
        $manual = new \Profile_User();
        $manualId = $manual->add(['users_id' => $user->getID(), 'entities_id' => 0, 'profiles_id' => 1, 'is_dynamic' => 0]);
        $rule = new \RuleRight();
        $ruleId = $rule->add(['name' => '_oidc_source_rule', 'sub_type' => 'RuleRight', 'match' => 'AND', 'is_active' => 1, 'entities_id' => 0]);
        (new \RuleCriteria())->add(['rules_id' => $ruleId, 'criteria' => 'OIDC_SOURCE', 'condition' => \Rule::PATTERN_IS, 'pattern' => $a['id']]);
        foreach (['entities_id' => $entity, 'profiles_id' => 5] as $field => $value) {
            (new \RuleAction())->add(['rules_id' => $ruleId, 'action_type' => 'assign', 'field' => $field, 'value' => $value]);
        }
        $rights = ['users_id' => $user->getID(), 'entities_id' => $entity, 'profiles_id' => 5, 'is_dynamic' => 1];
        try {
            $this->resetRules();
            \Oidc::authenticateClaims($a, $this->claims($a, 'source-sub', ['preferred_username' => '_oidc_source_rules']));
            $this->integer(countElementsInTable('glpi_profiles_users', $rights))->isIdenticalTo(1);
            \Oidc::authenticateClaims($b, $this->claims($b, 'source-sub', ['preferred_username' => '_oidc_source_rules']));
            $this->integer(countElementsInTable('glpi_profiles_users', $rights))->isIdenticalTo(0);
            $this->boolean($manual->getFromDB($manualId))->isTrue();
        } finally {
            $this->resetRules();
        }
    }

    public function testCreationAndUpdateMappingModes()
    {
        global $CFG_GLPI;
        $this->login();
        $provider = $this->provider(['allow_provisioning' => 1]);
        $this->mapping($provider, 'firstname', '/given', ['sync_mode' => 'create']);
        $this->mapping($provider, 'phone', '/phone', ['sync_mode' => 'update']);
        $saved = $CFG_GLPI['is_users_auto_add'];
        try {
            $CFG_GLPI['is_users_auto_add'] = 1;
            $claims = $this->claims($provider, 'mode-sub', ['preferred_username' => '_oidc_modes', 'given' => 'Initial', 'phone' => '0101010101']);
            $user = \Oidc::authenticateClaims($provider, $claims);
            $this->string($user->fields['firstname'])->isIdenticalTo('Initial');
            $this->variable($user->fields['phone'])->isNull();
            $claims['userinfo']['given'] = 'Changed';
            $user = \Oidc::authenticateClaims($provider, $claims);
            $this->string($user->fields['firstname'])->isIdenticalTo('Initial');
            $this->string($user->fields['phone'])->isIdenticalTo('0101010101');
        } finally {
            $CFG_GLPI['is_users_auto_add'] = $saved;
        }
    }

    public function testSourceAndUserPurgeCleanOidcOwnership()
    {
        global $DB;
        $this->login();
        $provider = $this->provider(['link_method' => 'username']);
        $user = $this->user('_oidc_purge');
        $this->mapping($provider, 'groups', '/groups', ['lookup_mode' => 'create']);
        \Oidc::authenticateClaims($provider, $this->claims($provider, 'purge-sub', ['preferred_username' => '_oidc_purge', 'groups' => ['_oidc_purge_group']]));
        $source = new \OidcProvider();
        $source->getFromDB($provider['id']);
        $this->boolean($source->delete(['id' => $source->getID()], true))->isTrue();
        $this->integer(count($DB->request(['FROM' => 'glpi_oidcidentities', 'WHERE' => ['users_id' => $user->getID()]])))->isIdenticalTo(0);
        $this->integer(count($DB->request(['FROM' => 'glpi_oidcmappings', 'WHERE' => ['oidcproviders_id' => $provider['id']]])))->isIdenticalTo(0);
        $this->integer(count($DB->request(['FROM' => 'glpi_groups_users', 'WHERE' => ['users_id' => $user->getID()]])))->isIdenticalTo(0);
        $this->integer(count($DB->request(['FROM' => 'glpi_oidcgroupusers', 'WHERE' => ['oidcproviders_id' => $provider['id']]])))->isIdenticalTo(0);
        $other = $this->provider();
        \OidcIdentity::bind($other, ['iss' => $other['issuer'], 'sub' => 'user-purge'], $user->getID());
        $this->boolean($user->delete(['id' => $user->getID()], true))->isTrue();
        $this->integer(count($DB->request(['FROM' => 'glpi_oidcidentities', 'WHERE' => ['users_id' => $user->getID()]])))->isIdenticalTo(0);
    }
    public function testInitialLinkingCanUseIdTokenClaims()
    {
        $this->login();
        $provider = $this->provider(['link_method' => 'username', 'login_claim_source' => 'id_token']);
        $user = $this->user('_oidc_token_login');
        $claims = $this->claims($provider, 'token-login');
        $claims['id_token']['preferred_username'] = '_oidc_token_login';
        $this->integer((int) \Oidc::authenticateClaims($provider, $claims)->getID())->isIdenticalTo((int) $user->getID());
        $emailSource = $this->provider(['name' => 'Token email', 'link_method' => 'email', 'email_claim_source' => 'id_token']);
        (new \UserEmail())->add(['users_id' => $user->getID(), 'email' => 'token@example.org']);
        $claims = $this->claims($emailSource, 'token-email');
        $claims['id_token']['email'] = 'token@example.org';
        $claims['id_token']['email_verified'] = true;
        $this->integer((int) \Oidc::authenticateClaims($emailSource, $claims)->getID())->isIdenticalTo((int) $user->getID());
    }

    public function testQuotedClaimsRemainLiteralValues()
    {
        global $DB;
        $this->login();
        $provider = $this->provider(['link_method' => 'username']);
        $user = $this->user("_oidc_o'connor");
        $this->mapping($provider, 'groups', '/groups', ['lookup_mode' => 'create']);
        $this->mapping($provider, 'email', '/email');
        $claims = $this->claims($provider, 'NULL', ['preferred_username' => "_oidc_o'connor", 'groups' => ["O'Connor team"], 'email' => "o'connor@example.org"]);
        foreach ([1, 2] as $login) {
            $resolved = \Oidc::authenticateClaims($provider, $claims);
            $this->integer((int) $resolved->getID())->isIdenticalTo((int) $user->getID());
            $this->string($resolved->fields['name'])->isIdenticalTo("_oidc_o'connor");
        }
        $row = $DB->request(['FROM' => 'glpi_oidcidentities', 'WHERE' => ['oidcproviders_id' => $provider['id']]])->next();
        $this->string($row['subject'])->isIdenticalTo('NULL');
        $this->integer(count($DB->request(['FROM' => 'glpi_groups', 'WHERE' => ['name' => \Oidc::literal("O'Connor team")]])))->isIdenticalTo(1);
        $this->integer(count($DB->request(['FROM' => 'glpi_useremails', 'WHERE' => ['users_id' => $user->getID(), 'email' => \Oidc::literal("o'connor@example.org")]])))->isIdenticalTo(1);
        $claims = $this->claims($provider, "sub'with\\quotes", ['preferred_username' => "_oidc_o'connor", 'groups' => ["O'Connor team"], 'email' => "o'connor@example.org"]);
        \Oidc::authenticateClaims($provider, $claims);
        $this->integer((int) \OidcIdentity::findUser($provider, $claims['id_token']))->isIdenticalTo((int) $user->getID());
        $attack = $this->claims($provider, 'unlinked-attack', ['preferred_username' => "x' OR 1-- "]);
        $this->exception(function () use ($provider, $attack) {
            \Oidc::authenticateClaims($provider, $attack);
        })->hasMessage('OIDC user creation is disabled.');
    }

}
