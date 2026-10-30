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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

class Oidc extends CommonDBTM
{
    /** Quote strings explicitly so values such as "NULL" remain strings. */
    public static function literal($value)
    {
        global $DB;
        return new QueryExpression("'" . $DB->escape((string) $value) . "'");
    }

    public static function client(array $provider, $attempt)
    {
        global $CFG_GLPI;
        $context = Plugin::doHookFunction('oidc_create_client', ['provider' => $provider, 'attempt' => $attempt,
            'client' => new OidcClient($provider, $attempt)]);
        $client = $context['client'] ?? null;
        if (!$client instanceof OidcClient) {
            throw new RuntimeException('Invalid OIDC client adapter.');
        }
        $client->setRedirectURL(rtrim($CFG_GLPI['url_base'], '/') . '/front/oidc.php');
        $client->addScope(preg_split('/[\s,]+/', trim($provider['scope'] ?? ''), -1, PREG_SPLIT_NO_EMPTY));
        $client->setHttpUpgradeInsecureRequests(false);
        if (!empty($provider['use_pkce'])) {
            $client->setCodeChallengeMethod('S256');
        }
        if (!empty($provider['proxy'])) {
            $client->setHttpProxy($provider['proxy']);
        }
        if (!empty($provider['cert'])) {
            $client->setCertPath($provider['cert']);
        }
        return $client;
    }

    public static function attempt($state)
    {
        if (!is_string($state) || $state === '') {
            throw new RuntimeException('Missing OIDC state.');
        }
        self::pruneAttempts();
        foreach ($_SESSION['_oidc_attempts'] ?? [] as $key => $attempt) {
            if (isset($attempt['state']) && hash_equals($attempt['state'], $state)) {
                return [$key, $attempt];
            }
        }
        throw new RuntimeException('Invalid or expired OIDC state.');
    }

    private static function pruneAttempts()
    {
        foreach ($_SESSION['_oidc_attempts'] ?? [] as $key => $attempt) {
            if (($attempt['expires'] ?? 0) < time()) {
                unset($_SESSION['_oidc_attempts'][$key], $_SESSION['_oidc_protocol'][$key]);
            }
        }
    }

    public static function loginSources()
    {
        global $CFG_GLPI;
        $sources = OidcProvider::all(true);
        $redirect = self::sanitizeRedirect($_GET['redirect'] ?? null);
        foreach ($sources as &$source) {
            $source['login_url'] = $CFG_GLPI['root_doc'] . '/front/oidc.php?provider=' . (int) $source['id']
                . ($redirect ? '&redirect=' . rawurlencode($redirect) : '');
        }
        unset($source);
        $automatic = (int) ($CFG_GLPI['oidc_auto_login_provider'] ?? 0);
        if ($automatic && !isset($_GET['noAUTO'])) {
            foreach ($sources as $source) {
                if ((int) $source['id'] === $automatic && ($source['required_plugin'] === '' || Plugin::isPluginActive($source['required_plugin']))) {
                    Html::redirect($source['login_url']);
                }
            }
        }
        return $sources;
    }

    public static function refreshIfRequired()
    {
        global $DB, $CFG_GLPI;
        if (isCommandLine() || isset($_GET['noAUTO'])) {
            return;
        }
        if (empty($_SESSION['itsm_is_oidc']) || empty($_SESSION['glpiID'])) {
            return;
        }
        if (in_array(basename($_SERVER['PHP_SELF'] ?? ''), ['oidc.php', 'logout.php'], true)) {
            return;
        }
        $providerId = (int) ($_SESSION['itsm_oidc_provider'] ?? 0);
        if (!$providerId) {
            $providerId = OidcProvider::legacyId();
            $_SESSION['itsm_oidc_provider'] = $providerId;
        }
        $where = ['oidcproviders_id' => $providerId, 'users_id' => (int) $_SESSION['glpiID'], 'needs_sync' => 1];
        if (!empty($_SESSION['itsm_oidc_identity_key'])) {
            $where['identity_key'] = $_SESSION['itsm_oidc_identity_key'];
        }
        if (count($DB->request(['FROM' => 'glpi_oidcidentities', 'WHERE' => $where]))) {
            $redirect = self::sanitizeRedirect($_SERVER['REQUEST_URI'] ?? null);
            Html::redirect($CFG_GLPI['root_doc'] . '/front/oidc.php?provider=' . $providerId
                . ($redirect ? '&redirect=' . rawurlencode($redirect) : ''));
        }
    }

    public static function auth()
    {
        global $CFG_GLPI;
        $key = null;
        $sessionInitialized = false;
        try {
            if (isset($_GET['logout'])) {
                self::finishLogout($_GET['state'] ?? null);
                return;
            }
            $callback = isset($_GET['code']) || isset($_GET['state']) || isset($_GET['error']) || isset($_GET['id_token']);
            if ($callback) {
                list($key, $attempt) = self::attempt($_GET['state'] ?? null);
                $provider = OidcProvider::load($attempt['provider']);
            } else {
                $providerId = (int) ($_GET['provider'] ?? 0);
                if (!$providerId) {
                    $active = OidcProvider::all(true);
                    if (count($active) !== 1) {
                        throw new RuntimeException('Select an OIDC source on the login page.');
                    }
                    $providerId = (int) reset($active)['id'];
                }
                $provider = OidcProvider::load($providerId);
                self::pruneAttempts();
                if (count($_SESSION['_oidc_attempts'] ?? []) >= 10) {
                    throw new RuntimeException('Too many pending OIDC logins.');
                }
                $key = bin2hex(random_bytes(16));
                $attempt = ['provider' => $providerId, 'expires' => time() + 600,
                    'redirect' => self::sanitizeRedirect($_GET['redirect'] ?? null) ?: '/'];
                $_SESSION['_oidc_attempts'][$key] = $attempt;
            }
            $client = self::client($provider, $key);
            if (!$client->authenticate()) {
                throw new RuntimeException('OIDC authentication was not completed.');
            }
            if (!$callback) {
                throw new RuntimeException('An OIDC callback is required.');
            }
            $claims = ['id_token' => (array) $client->getVerifiedClaims(), 'userinfo' => (array) $client->requestUserInfo()];
            $user = self::authenticateClaims($provider, $claims);
            $idToken = $client->getIdToken();
            unset($_SESSION['_oidc_attempts'][$key], $_SESSION['_oidc_protocol'][$key]);
            $auth = new Auth();
            $auth->auth_succeded = true;
            $auth->user = $user;
            $auth->extauth = true;
            $sessionInitialized = true;
            Session::init($auth);
            if (!$auth->auth_succeded) {
                throw new RuntimeException('The account is not authorized to log in.');
            }
            $_SESSION['itsm_is_oidc'] = 1;
            $_SESSION['itsm_oidc_provider'] = (int) $provider['id'];
            $_SESSION['itsm_oidc_identity_key'] = OidcIdentity::key($claims['id_token']['iss'], $claims['id_token']['sub']);
            $_SESSION['itsm_oidc_idtoken'] = $idToken;
            self::recordLogin($provider, $claims['id_token'], $user->getID());
            Plugin::doHook('oidc_login', ['provider' => $provider, 'users_id' => $user->getID()]);
            Auth::redirectIfAuthenticated($attempt['redirect']);
        } catch (Throwable $e) {
            if ($sessionInitialized) {
                Session::destroy();
                Auth::setRememberMeCookie('');
            }
            if ($key !== null) {
                unset($_SESSION['_oidc_attempts'][$key], $_SESSION['_oidc_protocol'][$key]);
            }
            Toolbox::logInFile('oidc', get_class($e) . ': ' . $e->getMessage() . "\n");
            Html::nullHeader(__('Login'), $CFG_GLPI['root_doc'] . '/index.php');
            echo '<div class="center"><p>' . __('OIDC login failed. Check the source configuration or contact your administrator.') . '</p><a href="'
                . OidcProvider::escape($CFG_GLPI['root_doc'] . '/index.php?noAUTO=1') . '">' . __('Log in again') . '</a></div>';
            Html::nullFooter();
        }
    }

    /** Accept claims only after the OIDC client has verified the authentication response. */
    public static function authenticateClaims(array $provider, array $claims)
    {
        return self::transaction(function () use ($provider, $claims) {
            return self::synchronizeAuthenticatedUser($provider, $claims);
        });
    }

    private static function transaction(callable $operation)
    {
        global $DB;
        $nested = $DB->inTransaction();
        $savepoint = 'oidc_' . bin2hex(random_bytes(8));
        $nested ? $DB->queryOrDie('SAVEPOINT ' . $savepoint) : $DB->beginTransaction();
        try {
            $result = $operation();
            $nested ? $DB->queryOrDie('RELEASE SAVEPOINT ' . $savepoint) : $DB->commit();
            return $result;
        } catch (Throwable $e) {
            if ($nested) {
                $DB->queryOrDie('ROLLBACK TO SAVEPOINT ' . $savepoint);
                $DB->queryOrDie('RELEASE SAVEPOINT ' . $savepoint);
            } else {
                $DB->rollback();
            }
            throw $e;
        }
    }

    private static function synchronizeAuthenticatedUser(array $provider, array $claims)
    {
        global $DB, $CFG_GLPI;
        if (!is_array($claims['id_token'] ?? null) || !is_array($claims['userinfo'] ?? null)) {
            throw new RuntimeException('Invalid OIDC claims.');
        }
        $provider = OidcProvider::load($provider['id']);
        $identity = $claims['id_token'];
        if (($identity['iss'] ?? null) !== $provider['issuer']) {
            throw new RuntimeException('Invalid OIDC issuer.');
        }
        OidcClient::validateSubject($identity['sub'] ?? null);
        if (($claims['userinfo']['sub'] ?? null) !== $identity['sub']) {
            throw new RuntimeException('OIDC UserInfo subject does not match the ID token.');
        }
        $normalized = Plugin::doHookFunction('oidc_normalize_claims', ['provider' => $provider, 'claims' => $claims]);
        if (!is_array($normalized) || !is_array($normalized['claims']['userinfo'] ?? null) || !is_array($normalized['claims']['id_token'] ?? null)) {
            throw new RuntimeException('Invalid normalized OIDC claims.');
        }
        $claims = $normalized['claims'];
        $claims['id_token']['iss'] = $identity['iss'];
        $claims['id_token']['sub'] = $identity['sub'];
        $claims['userinfo']['sub'] = $identity['sub'];
        $id = OidcIdentity::resolve($provider, $identity, $claims);
        $user = new User();
        if ($id === null) {
            if (empty($provider['allow_provisioning']) || empty($CFG_GLPI['is_users_auto_add'])) {
                throw new RuntimeException('OIDC user creation is disabled.');
            }
            $username = self::username($provider, $claims);
            if (count($DB->request(['FROM' => 'glpi_users', 'WHERE' => ['name' => self::literal($username)]]))) {
                throw new RuntimeException('The OIDC username already belongs to an unlinked account.');
            }
            $values = OidcMapping::values($provider['id'], $claims, ['entities_id' => 0], true);
            $id = $user->add(Toolbox::addslashes_deep(['name' => $username, 'authtype' => Auth::EXTERNAL, '_extauth' => 1, 'add' => 1]));
        }
        if (!$id || !$user->getFromDB($id)) {
            throw new RuntimeException('Unable to load the OIDC user.');
        }
        self::checkUser($user);
        $values = $values ?? OidcMapping::values($provider['id'], $claims, $user->fields, false);
        OidcMapping::apply($provider['id'], $user, $values);
        self::applyRules($user, $provider['id']);
        self::checkUser($user);
        OidcIdentity::bind($provider, $identity, $user->getID());
        return $user;
    }

    public static function username(array $provider, array $claims)
    {
        $value = OidcMapping::claim($claims[$provider['login_claim_source'] ?? 'userinfo'], $provider['login_claim']);
        if (($value === null || $value === '') && !empty($provider['is_legacy'])) {
            $value = ($provider['legacy_login_fallback'] ?? 'sub') === 'name' ? ($claims['userinfo']['name'] ?? null) : null;
            if (!is_string($value) || $value === '') {
                $value = $claims['id_token']['sub'] ?? null;
            }
        }
        if (!is_string($value) || $value === '' || !Auth::isValidLogin($value)) {
            throw new RuntimeException('Missing or invalid OIDC username.');
        }
        return $value;
    }

    private static function checkUser(User $user)
    {
        $now = $_SESSION['glpi_currenttime'];
        if (!$user->fields['is_active'] || $user->fields['is_deleted']) {
            throw new RuntimeException('The OIDC account is inactive or deleted.');
        }
        if (!empty($user->fields['begin_date']) && $user->fields['begin_date'] > $now) {
            throw new RuntimeException('The OIDC account is not yet valid.');
        }
        if (!empty($user->fields['end_date']) && $user->fields['end_date'] < $now) {
            throw new RuntimeException('The OIDC account has expired.');
        }
    }

    public static function applyRules(User $user, $providerId = null)
    {
        $rules = new RuleRightCollection();
        $input = $rules->processAllRules(
            array_column(Group_User::getUserGroups($user->getID()), 'id'),
            $user->fields,
            ['type' => Auth::EXTERNAL, 'oidc_provider' => $providerId ?? 0, 'login' => $user->fields['name'], 'email' => UserEmail::getDefaultForUser($user->getID())]
        );
        $input['_ruleright_process'] = true;
        if (!$user->update($input)) {
            throw new RuntimeException('Unable to apply OIDC authorization rules.');
        }
        $user->getFromDB($user->getID());
    }

    public static function recordLogin(array $provider, array $identity, $userId)
    {
        global $DB;
        if (!$DB->update('glpi_users', ['last_login' => $_SESSION['glpi_currenttime']], ['id' => $userId])) {
            throw new RuntimeException('Unable to record the OIDC login.');
        }
        if (!$DB->update(
            'glpi_oidcidentities',
            ['last_login' => $_SESSION['glpi_currenttime'], 'needs_sync' => 0],
            ['oidcproviders_id' => $provider['id'], 'identity_key' => OidcIdentity::key($identity['iss'], $identity['sub'])]
        )) {
            throw new RuntimeException('Unable to record the OIDC identity login.');
        }
    }

    /** Synchronize imported profile data without recording an authentication event. */
    public static function addUserData($userArray, $id, $providerId = null)
    {
        self::transaction(function () use ($userArray, $id, $providerId) {
            $providerId = $providerId ?? OidcProvider::legacyId();
            OidcProvider::load($providerId, false);
            $user = new User();
            if (!$user->getFromDB($id)) {
                throw new RuntimeException('Unable to load the OIDC user.');
            }
            $values = OidcMapping::values($providerId, ['userinfo' => $userArray, 'id_token' => []], $user->fields, false);
            OidcMapping::apply($providerId, $user, $values);
            self::applyRules($user, $providerId);
        });
    }

    public static function logout()
    {
        global $CFG_GLPI;
        try {
            $provider = OidcProvider::load($_SESSION['itsm_oidc_provider'] ?? OidcProvider::legacyId(), false);
            $client = self::client($provider, 'logout');
            $idToken = $_SESSION['itsm_oidc_idtoken'] ?? '';
            $state = bin2hex(random_bytes(32));
            $context = Plugin::doHookFunction('oidc_logout', ['provider' => $provider, 'client' => $client,
                'redirect' => $provider['logout_redirect'] ?: rtrim($CFG_GLPI['url_base'], '/') . '/front/oidc.php?logout=1',
                'parameters' => []]);
            $url = $client->logoutUrl($idToken, $context['redirect'], $state, $context['parameters']);
            Session::destroy();
            Auth::setRememberMeCookie('');
            Session::start();
            $_SESSION['_oidc_logout'] = ['state' => $state, 'expires' => time() + 600,
                'redirect' => $CFG_GLPI['root_doc'] . '/index.php?noAUTO=1'];
            Html::redirect($url);
        } catch (Throwable $e) {
            Toolbox::logInFile('oidc', 'OIDC logout failed: ' . get_class($e) . "\n");
        }
    }

    public static function finishLogout($state)
    {
        Html::redirect(self::consumeLogoutState($state));
    }

    public static function consumeLogoutState($state)
    {
        $pending = $_SESSION['_oidc_logout'] ?? null;
        unset($_SESSION['_oidc_logout']);
        if (!is_array($pending) || !is_string($state)) {
            throw new RuntimeException('Invalid or expired OIDC logout state.');
        }
        if ($pending['expires'] < time() || !hash_equals($pending['state'], $state)) {
            throw new RuntimeException('Invalid or expired OIDC logout state.');
        }
        return $pending['redirect'];
    }

    public static function sanitizeRedirect($value)
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }
        $decoded = rawurldecode(trim($value));
        if (preg_match('/[\x00-\x20\x7f\\\\]/', $decoded) || preg_match('#^(?:[a-z][a-z0-9+.-]*:)?//#i', $decoded)) {
            return null;
        }
        if ($decoded[0] === '/' && \Glpi\Toolbox\URL::isITSMNGRelativeURL($decoded)) {
            return trim($value);
        }
        return preg_match('/^[A-Za-z0-9][A-Za-z0-9_$.-]*(?:_[A-Za-z0-9$.-]+)*$/', $decoded) ? trim($value) : null;
    }
}
