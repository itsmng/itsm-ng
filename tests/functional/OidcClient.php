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

namespace tests\fixtures;

class OidcProtocolClient extends \OidcClient
{
    public $response = '';
    public $signatureValid = true;
    public $requestUrl;

    protected function fetchURL($url, $postBody = null, $headers = [])
    {
        $this->requestUrl = $url;
        return $this->response;
    }

    protected function getProviderConfigValue($key, $default = null)
    {
        return 'https://issuer.example.org/' . $key;
    }

    public function getResponseCode(): int
    {
        return 200;
    }

    public function getVerifiedClaims($attribute = null)
    {
        return $attribute === 'sub' ? 'verified-sub' : (object) ['iss' => 'https://issuer.example.org', 'sub' => 'verified-sub'];
    }

    public function getIdTokenHeader()
    {
        return (object) ['alg' => 'RS256'];
    }

    public function verifyJWTSignature($jwt): bool
    {
        return $this->signatureValid;
    }

    public function store($key, $value)
    {
        $this->setSessionKey($key, $value);
    }

    public function read($key)
    {
        return $this->getSessionKey($key);
    }
}

class OidcHmacClient extends OidcProtocolClient
{
    public function verifyJWTSignature($jwt): bool
    {
        return \Jumbojett\OpenIDConnectClient::verifyJWTSignature($jwt);
    }
}

namespace tests\units;

class OidcClient extends \GLPITestCase
{
    private function client($attempt = 'test')
    {
        return new \tests\fixtures\OidcProtocolClient(['issuer' => 'https://issuer.example.org', 'client_id' => 'client',
            'client_secret' => \Toolbox::sodiumEncrypt('secret')], $attempt);
    }

    public function testUserInfoMustMatchVerifiedSubject()
    {
        $client = $this->client();
        $client->response = json_encode(['sub' => 'verified-sub', 'email' => 'person@example.org']);
        $this->string($client->requestUserInfo('email'))->isIdenticalTo('person@example.org');
        $this->string($client->requestUrl)->isIdenticalTo('https://issuer.example.org/userinfo_endpoint');
        $client->response = json_encode(['sub' => 'other']);
        $this->exception(function () use ($client) {
            $client->requestUserInfo();
        })->hasMessage('OIDC UserInfo subject does not match the ID token.');
    }

    private function jwt(array $claims, $algorithm = 'RS256')
    {
        $encode = function ($value) {
            return rtrim(strtr(base64_encode(json_encode($value)), '+/', '-_'), '=');
        };
        return $encode(['alg' => $algorithm]) . '.' . $encode($claims) . '.testsignature';
    }

    public function testSignedUserInfoValidation()
    {
        $client = $this->client();
        $claims = ['iss' => 'https://issuer.example.org', 'aud' => 'client', 'exp' => time() + 60, 'sub' => 'verified-sub'];
        $client->response = $this->jwt($claims);
        $this->string($client->requestUserInfo()->sub)->isIdenticalTo('verified-sub');
        $withoutExpiry = $claims;
        unset($withoutExpiry['exp']);
        $client->response = $this->jwt($withoutExpiry);
        $this->string($client->requestUserInfo()->sub)->isIdenticalTo('verified-sub');
        foreach ([['exp' => time() - 3600], ['iss' => 'https://other.example.org'], ['aud' => 'other'], ['sub' => 'other']] as $change) {
            $client->response = $this->jwt(array_replace($claims, $change));
            $this->exception(function () use ($client) {
                $client->requestUserInfo();
            })->isInstanceOf(\RuntimeException::class);
        }
        $client->response = $this->jwt($claims, 'none');
        $this->exception(function () use ($client) {
            $client->requestUserInfo();
        })->hasMessage('Invalid OIDC UserInfo signature.');
        $client->response = $this->jwt($claims);
        $client->signatureValid = false;
        $this->exception(function () use ($client) {
            $client->requestUserInfo();
        })->hasMessage('Invalid OIDC UserInfo signature.');
    }

    public function testProtocolStateIsNamespacedByAttempt()
    {
        $a = $this->client('a');
        $b = $this->client('b');
        $a->store('openid_connect_state', 'state-a');
        $b->store('openid_connect_state', 'state-b');
        $a->store('openid_connect_nonce', 'nonce-a');
        $b->store('openid_connect_nonce', 'nonce-b');
        $this->string($a->read('openid_connect_state'))->isIdenticalTo('state-a');
        $this->string($b->read('openid_connect_state'))->isIdenticalTo('state-b');
        $this->string($a->read('openid_connect_nonce'))->isIdenticalTo('nonce-a');
        $this->string($b->read('openid_connect_nonce'))->isIdenticalTo('nonce-b');
        $this->string($_SESSION['_oidc_attempts']['a']['state'])->isIdenticalTo('state-a');
    }

    public function testUserInfoCanUseADifferentVerifiedSigningAlgorithm()
    {
        $client = new \tests\fixtures\OidcHmacClient(['issuer' => 'https://issuer.example.org', 'client_id' => 'client',
            'client_secret' => \Toolbox::sodiumEncrypt('secret')], 'signature');
        $this->string($client->getIdTokenHeader()->alg)->isIdenticalTo('RS256');
        $jwt = $this->jwt(['iss' => 'https://issuer.example.org', 'aud' => 'client', 'exp' => time() + 60, 'sub' => 'verified-sub'], 'HS256');
        $payload = substr($jwt, 0, strrpos($jwt, '.'));
        $signature = rtrim(strtr(base64_encode(hash_hmac('sha256', $payload, 'secret', true)), '+/', '-_'), '=');
        $client->response = $payload . '.' . $signature;
        $this->string($client->requestUserInfo()->sub)->isIdenticalTo('verified-sub');
        $client->response = $payload . '.' . substr($signature, 0, -2) . 'xx';
        $this->exception(function () use ($client) {
            $client->requestUserInfo();
        })->hasMessage('Invalid OIDC UserInfo signature.');
    }

    public function testLogoutStateIsSingleUse()
    {
        $client = $this->client();
        $url = $client->logoutUrl('id-token', 'https://app.example.org/front/oidc.php?logout=1', 'logout-state', ['client_id' => 'client']);
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        $this->string($params['state'])->isIdenticalTo('logout-state');
        $this->string($params['id_token_hint'])->isIdenticalTo('id-token');
        $_SESSION['_oidc_logout'] = ['state' => 'logout-state', 'expires' => time() + 60, 'redirect' => '/index.php?noAUTO=1'];
        $this->string(\Oidc::consumeLogoutState('logout-state'))->isIdenticalTo('/index.php?noAUTO=1');
        $this->exception(function () {
            \Oidc::consumeLogoutState('logout-state');
        })->isInstanceOf(\RuntimeException::class);
        $_SESSION['_oidc_logout'] = ['state' => 'logout-state', 'expires' => time() - 1, 'redirect' => '/index.php'];
        $this->exception(function () {
            \Oidc::consumeLogoutState('logout-state');
        })->isInstanceOf(\RuntimeException::class);
    }
    public function testRequiredIdTokenClaims()
    {
        $client = $this->client();
        $claims = ['iss' => 'https://issuer.example.org', 'aud' => 'client', 'exp' => time() + 60, 'iat' => time(), 'sub' => 'verified-sub'];
        $this->variable($client->validateIdTokenClaims($claims))->isNull();
        foreach (['iss', 'aud', 'exp', 'iat', 'sub'] as $key) {
            $missing = $claims;
            unset($missing[$key]);
            $this->exception(function () use ($client, $missing) {
                $client->validateIdTokenClaims($missing);
            })->isInstanceOf(\RuntimeException::class);
        }
        foreach ([['exp' => time() - 3600], ['iat' => time() + 3600], ['iss' => 'https://other.example.org'], ['aud' => ['client', 'other']], ['azp' => 'other']] as $change) {
            $invalid = array_replace($claims, $change);
            $this->exception(function () use ($client, $invalid) {
                $client->validateIdTokenClaims($invalid);
            })->isInstanceOf(\RuntimeException::class);
        }
        $claims['aud'] = ['client', 'other'];
        $claims['azp'] = 'client';
        $this->variable($client->validateIdTokenClaims($claims))->isNull();
    }

}
