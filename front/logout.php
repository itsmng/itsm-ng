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

/**
 * @since 0.85
 */

include('../inc/includes.php');

//@session_start();

if (
    $CFG_GLPI["ssovariables_id"] > 0
    && strlen($CFG_GLPI['ssologout_url']) > 0
) {
    Html::redirect($CFG_GLPI["ssologout_url"]);
}

if (
    !isset($_SESSION["noAUTO"])
    && isset($_SESSION["glpiauthtype"])
    && $_SESSION["glpiauthtype"] == Auth::CAS
    && Toolbox::canUseCAS()
) {
    $has_service_base_url_arg = version_compare(phpCAS::getVersion(), '1.6.0', '>=')
        || count((new ReflectionMethod(phpCAS::class, 'client'))->getParameters()) > 6;
    if (!$has_service_base_url_arg) {
        phpCAS::client(
            $CFG_GLPI["cas_version"],
            $CFG_GLPI["cas_host"],
            intval($CFG_GLPI["cas_port"]),
            $CFG_GLPI["cas_uri"],
            false
        );
    } else {
        $url_base = parse_url($CFG_GLPI["url_base"]);
        $service_base_url = $url_base["scheme"] . "://" . $url_base["host"] . (isset($url_base["port"]) ? ":" . $url_base["port"] : "");
        phpCAS::client(
            $CFG_GLPI["cas_version"],
            $CFG_GLPI["cas_host"],
            intval($CFG_GLPI["cas_port"]),
            $CFG_GLPI["cas_uri"],
            $service_base_url,
            false
        );
    }
    phpCAS::setServerLogoutURL(strval($CFG_GLPI["cas_logout"]));
    phpCAS::logout();
}

$toADD = "";

// Redirect management
if (isset($_POST['redirect']) && (strlen($_POST['redirect']) > 0)) {
    $toADD = "?redirect=" . $_POST['redirect'];
} elseif (isset($_GET['redirect']) && (strlen($_GET['redirect']) > 0)) {
    $toADD = "?redirect=" . $_GET['redirect'];
}

if (isset($_SESSION["noAUTO"]) || isset($_GET['noAUTO'])) {
    if (empty($toADD)) {
        $toADD .= "?";
    } else {
        $toADD .= "&";
    }
    $toADD .= "noAUTO=1";
}

if (isset($_SESSION["itsm_is_oidc"]) && $_SESSION["itsm_is_oidc"] == 1) {
    //Get config from DB and use it to setup oidc
    $criteria = "SELECT * FROM glpi_oidc_config";
    $iterators = $DB->request($criteria);
    foreach ($iterators as $iterator) {
        $oidc_db['Provider'] = $iterator['Provider'];
        $oidc_db['ClientID'] = $iterator['ClientID'];
        $oidc_db['ClientSecret'] = Toolbox::sodiumDecrypt($iterator['ClientSecret']);
        $oidc_db['scope'] = explode(',', addslashes($iterator['scope']));
        $oidc_db['logout'] = empty($iterator['logout']) ? null : $iterator['logout'];
        $oidc_db['proxy'] = $iterator['proxy'];
        $oidc_db['cert'] = $iterator['cert'];
    }

    if (isset($oidc_db)) {
        $oidc = new Jumbojett\OpenIDConnectClient($oidc_db['Provider'], $oidc_db['ClientID'], $oidc_db['ClientSecret']);
        if (is_array($oidc_db['scope'])) {
            $oidc->addScope($oidc_db['scope']);
        }
        if (isset($oidc_db['proxy']) && $oidc_db['proxy'] != '') {
            $oidc->setHttpProxy($oidc_db['proxy']);
        }
        if (isset($oidc_db['cert']) && $oidc_db['proxy'] != '' && file_exists($oidc_db['cert'])) {
            $oidc->setCertPath($oidc_db['cert']);
        }
        $sid = $_SESSION['itsm_oidc_idtoken'];

        Session::destroy();
        Auth::setRememberMeCookie('');

        $oidc->signOut($sid, $oidc_db['logout'] ?? $CFG_GLPI["url_base"] . "/index.php" . $toADD);
    }
}

Session::destroy();

//Remove cookie to allow new login
Auth::setRememberMeCookie('');

// Redirect to the login-page
Html::redirect($CFG_GLPI["root_doc"] . "/index.php" . $toADD);
