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

/** Isolate protocol state for each login attempt. */
class OidcClient extends \Jumbojett\OpenIDConnectClient
{
    private $attempt;
    private $expectedIssuer;
    private $expectedAudience;

    public function __construct(array $provider, $attempt)
    {
        $this->attempt = $attempt;
        $this->expectedIssuer = $provider['issuer'];
        $this->expectedAudience = $provider['client_id'];
        parent::__construct($provider['issuer'], $provider['client_id'], Toolbox::sodiumDecrypt($provider['client_secret'] ?? ''));
    }

    public function authenticate(): bool
    {
        if (!parent::authenticate()) {
            return false;
        }
        $this->validateIdTokenClaims((array) $this->getVerifiedClaims());
        return true;
    }

    /** Reject missing identity claims, expired tokens and incorrect audiences. */
    public function validateIdTokenClaims(array $claims)
    {
        $this->validateExpectedIssuer($claims);
        self::validateSubject($claims['sub'] ?? null);
        $this->validateExpiration($claims['exp'] ?? null);
        $this->validateIssuedAt($claims['iat'] ?? null);
        $this->validateAudience($claims);
    }

    private function validateExpectedIssuer(array $claims)
    {
        if (($claims['iss'] ?? null) !== $this->expectedIssuer) {
            throw new RuntimeException('Invalid OIDC issuer.');
        }
    }

    public static function validateSubject($subject)
    {
        if (!is_string($subject) || $subject === '' || strlen($subject) > 255) {
            throw new RuntimeException('Invalid OIDC subject.');
        }
    }

    private function validateExpiration($expires)
    {
        if (!is_int($expires) || $expires <= time() - $this->getLeeway()) {
            throw new RuntimeException('Invalid OIDC token expiration.');
        }
    }

    private function validateIssuedAt($issuedAt)
    {
        if (!is_int($issuedAt) || $issuedAt > time() + $this->getLeeway()) {
            throw new RuntimeException('Invalid OIDC token issue time.');
        }
    }

    private function validateAudience(array $claims)
    {
        $audiences = (array) ($claims['aud'] ?? []);
        if (!in_array($this->expectedAudience, $audiences, true)) {
            throw new RuntimeException('Invalid OIDC token audience.');
        }
        if (count($audiences) > 1 && !isset($claims['azp'])) {
            throw new RuntimeException('Missing OIDC authorized party.');
        }
        if (isset($claims['azp']) && $claims['azp'] !== $this->expectedAudience) {
            throw new RuntimeException('Invalid OIDC authorized party.');
        }
    }

    protected function getSessionKey($key)
    {
        $this->startSession();
        return $_SESSION['_oidc_protocol'][$this->attempt][$key] ?? false;
    }

    protected function setSessionKey($key, $value)
    {
        $this->startSession();
        $_SESSION['_oidc_protocol'][$this->attempt][$key] = $value;
        if ($key === 'openid_connect_state') {
            $_SESSION['_oidc_attempts'][$this->attempt]['state'] = $value;
        }
    }

    protected function unsetSessionKey($key)
    {
        $this->startSession();
        unset($_SESSION['_oidc_protocol'][$this->attempt][$key]);
    }

    /** Fetch UserInfo and require its subject to match the verified ID token. */
    public function requestUserInfo($attribute = null)
    {
        $response = $this->fetchURL(
            $this->getProviderConfigValue('userinfo_endpoint'),
            null,
            ['Authorization: Bearer ' . $this->getAccessToken(), 'Accept: application/json, application/jwt']
        );
        if ($this->getResponseCode() !== 200) {
            throw new RuntimeException('The OIDC UserInfo endpoint failed.');
        }
        $data = substr(ltrim($response), 0, 1) === '{'
            ? json_decode($response)
            : $this->decodeSignedUserInfo($response);
        if (!is_object($data)) {
            throw new RuntimeException('Invalid OIDC UserInfo response.');
        }
        if (($data->sub ?? null) !== $this->getVerifiedClaims('sub')) {
            throw new RuntimeException('OIDC UserInfo subject does not match the ID token.');
        }
        return $attribute === null ? $data : ($data->$attribute ?? null);
    }

    private function decodeSignedUserInfo($response)
    {
        $header = $this->decodeJWT($response);
        if (isset($header->enc) || ($header->alg ?? 'none') === 'none') {
            throw new RuntimeException('Invalid OIDC UserInfo signature.');
        }
        if (!$this->verifyJWTSignature($response)) {
            throw new RuntimeException('Invalid OIDC UserInfo signature.');
        }
        $claims = (array) $this->decodeJWT($response, 1);
        $this->validateExpectedIssuer($claims);
        $audiences = (array) ($claims['aud'] ?? []);
        if (!in_array($this->expectedAudience, $audiences, true)) {
            throw new RuntimeException('Invalid OIDC UserInfo audience.');
        }
        if (isset($claims['exp'])) {
            $this->validateExpiration($claims['exp']);
        }
        if (isset($claims['nbf'])) {
            if (!is_int($claims['nbf']) || $claims['nbf'] > time()) {
                throw new RuntimeException('OIDC UserInfo is not yet valid.');
            }
        }
        return (object) $claims;
    }

    public function logoutUrl($idToken, $redirect, $state, array $parameters = [])
    {
        $endpoint = $this->getProviderConfigValue('end_session_endpoint');
        return $endpoint . (strpos($endpoint, '?') === false ? '?' : '&') . http_build_query(array_merge($parameters, [
            'id_token_hint' => $idToken, 'post_logout_redirect_uri' => $redirect, 'state' => $state,
        ]), '', '&', PHP_QUERY_RFC3986);
    }
}
