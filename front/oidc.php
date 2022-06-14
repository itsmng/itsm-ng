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

include ('../inc/includes.php');
require __DIR__ . '/../vendor/autoload.php';

global $DB;
      
//Get config from DB and use it to setup oidc
$criteria = "SELECT * FROM glpi_oidc_config";
$iterators = $DB->request($criteria);
foreach($iterators as $iterator) {
    $oidc_db['Provider'] = $iterator['Provider'];
    $oidc_db['ClientID'] = $iterator['ClientID'];
    $oidc_db['ClientSecret'] = $iterator['ClientSecret'];
}

$oidc = new Jumbojett\OpenIDConnectClient($iterator['Provider'], $iterator['ClientID'], $iterator['ClientSecret']);
$oidc->setHttpUpgradeInsecureRequests(false);
try {
    $oidc->authenticate();
} catch( Exception $e ) {
    //If something go wrong 
    Html::nullHeader("Login", $CFG_GLPI["root_doc"] . '/index.php');
    echo '<div class="center b">';
    echo __('Missing or wrong fields in open ID connect config');
    echo '<p><a href="'. $CFG_GLPI['root_doc'] . "/index.php" .'">' .__('Log in again') . '</a></p>';
    echo '</div>';
    Html::nullFooter();
    die;
}
$result = $oidc->requestUserInfo();
//Tranform result to an array
$user_array = json_encode($result);
$user_array = json_decode($user_array,true);

//Create and/or authenticated a user
$criteria = "SELECT * FROM glpi_users";
$iterators = $DB->request($criteria);
$newUser = true;

if (isset($user_array["preferred_username"])) {
    foreach($iterators as $iterator)
    if ($user_array['preferred_username'] == $iterator['name']) {
        $ID = $iterator['id'];
        $newUser = false;
    }
   
    $user = new User();
    if ($newUser) {
        $input = ['name'     => $user_array['preferred_username'],
                    '_extauth' => 1,
                    'add'      => 1];
        $ID = $user->add($input);
    }
} else {
    foreach($iterators as $iterator)
    if ($user_array['sub'] == $iterator['name']) {
        $ID = $iterator['id'];
        $newUser = false;
    }
   
    $user = new User();
    if ($newUser) {
        $input = ['name'     => $user_array['sub'],
                    '_extauth' => 1,
                    'add'      => 1];
        $ID = $user->add($input);
    }
}

if (!$user->getFromDB($ID))
    die;

$auth = new Auth();
$auth->auth_succeded = true;
$auth->user = $user;
//Setup a new session and redirect to the main menu
Session::init($auth);
Auth::redirectIfAuthenticated();