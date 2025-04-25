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

require __DIR__ . '/../vendor/autoload.php';

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}


/**
 * OpenID connect Class
 **/
class Oidc extends CommonDBTM
{
    public static $_user_data;

    public static function auth()
    {

        global $DB, $CFG_GLPI;

        //Get config from DB and use it to setup oidc
        $criteria = ["SELECT * FROM glpi_oidc_config"];
        $requests = self::getAdapter()->request($criteria);
        foreach ($requests as $request) {
            $oidc_db['Provider'] = $request['Provider'];
            $oidc_db['ClientID'] = $request['ClientID'];
            $oidc_db['ClientSecret'] = Toolbox::sodiumDecrypt($request['ClientSecret']);
            $oidc_db['scope'] = explode(',', addslashes(str_replace(' ', '', $request['scope'])));
            $oidc_db['proxy'] = $request['proxy'];
            $oidc_db['cert'] = $request['cert'];
            $oidc_db['sso_link_users'] = $request['sso_link_users'];
        }

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
        if (isset($_REQUEST['redirect'])) {
            if (isset($_SERVER['HTTPS'])) {
                $redirect = 'https://';
            } else {
                $redirect = 'http://';
            }
            $redirect .= $_SERVER['SERVER_NAME']
               . $CFG_GLPI['root_doc'] . '/front/oidc.php?redirect=' . $_REQUEST['redirect'];
            $oidc->setRedirectURL($redirect);
        }
        $oidc->setHttpUpgradeInsecureRequests(false);
        try {
            $oidc->authenticate();
        } catch (Exception $e) {
            //If something go wrong
            Html::nullHeader("Login", $CFG_GLPI["root_doc"] . '/index.php');
            echo '<div class="center b">';
            echo __('Missing or wrong fields in open ID connect config');
            echo '<p><a href="' . $CFG_GLPI['root_doc'] . "/index.php" . '">' . __('Log in again') . '</a></p>';
            echo '<div>' . $e->getMessage() . '</div>';
            echo '</div>';
            Html::nullFooter();
            die;
        }

        $result = $oidc->requestUserInfo();
        //Tranform result to an array
        $user_array = json_encode($result);
        $user_array = json_decode($user_array, true);
        self::$_user_data = $user_array;
        //var_dump(self::$_user_data);
        //die;
        //Create and/or authenticated a user
        $criteria = ["SELECT * FROM glpi_users"];
        $requests = self::getAdapter()->request($criteria);
        $newUser = true;

        if (isset($user_array["name"])) {
            foreach ($requests as $request) {
                $canLink = $oidc_db['sso_link_users'] || $request['authtype'] == Auth::EXTERNAL;
                if ($user_array['name'] == $request['name'] && $canLink) {
                    $ID = $request['id'];
                    $newUser = false;
                }
            }

            $user = new User();
            if ($newUser) {
                $input = [
                    'name'     => $user_array['name'],
                    '_extauth' => 1,
                    'add'      => 1
                ];
                $ID = $user->add($input);
            }
        } else {
            foreach ($requests as $request) {
                $canLink = $oidc_db['sso_link_users'] || $request['authtype'] == Auth::EXTERNAL;
                if ($user_array['sub'] == $request['name'] && $canLink) {
                    $ID = $request['id'];
                    $newUser = false;
                }
            }

            $user = new User();
            if ($newUser) {
                $input = [
                    'name'     => $user_array['sub'],
                    '_extauth' => 1,
                    'add'      => 1
                ];
                $ID = $user->add($input);
            }
        }

        if (!$user->getFromDB($ID)) {
            die;
        }

        $request = self::getAdapter()->request([
            'FROM' => 'glpi_oidc_mapping', 
        ]);
        $results = $request->fetchAllAssociative();

        foreach ($results as $data) {
            $mapping_date_mod = $data["date_mod"];
        }
        $request = self::getAdapter()->request([
            'FROM'  => 'glpi_users', 
            'WHERE' => ['id' => $ID] 
        ]);
        $results = $request->fetchAllAssociative();
        foreach ($results as $data) {
            $user_date_mod = $data["date_mod"];
        }

        //if ($mapping_date_mod > $user_date_mod)
        self::addUserData($user_array, $ID);

        $auth = new Auth();
        $auth->auth_succeded = true;
        $auth->user = $user;
        //Setup a new session and redirect to the main menu
        Session::init($auth);
        $_SESSION['itsm_is_oidc'] = 1;
        $_SESSION['itsm_oidc_idtoken'] = $oidc->getIdToken();
        Auth::redirectIfAuthenticated($_REQUEST['redirect'] ?? null);
    }

    /**
     * Add oidc data to user's db via a mapping
     *
     * @return void
     */
    public static function addUserData($user_array, $id)
    {
        global $DB;

        $criteria = ["SELECT * FROM glpi_oidc_mapping"];
        $request = self::getAdapter()->request($criteria);

        while ($data = $request->fetchAssociative()) {
            $result[] = $data;
        }

        if (isset($result)) {
            if (isset($user_array[$result[0]["name"]])) {
                $DB->updateOrInsert("glpi_users", ['name' => $DB->escape($user_array[$result[0]["name"]])], ['id' => $id]);
            }

            if (isset($user_array[$result[0]["given_name"]])) {
                $DB->updateOrInsert("glpi_users", ['firstname' => $DB->escape($user_array[$result[0]["given_name"]])], ['id' => $id]);
            }

            if (isset($user_array[$result[0]["family_name"]])) {
                $DB->updateOrInsert("glpi_users", ['realname' => $DB->escape($user_array[$result[0]["family_name"]])], ['id' => $id]);
            }

            if (isset($user_array[$result[0]["picture"]])) {
                $DB->updateOrInsert("glpi_users", ['picture' => $DB->escape($user_array[$result[0]["picture"]])], ['id' => $id]);
            }

            if (isset($user_array[$result[0]["email"]])) {
                $querry = "INSERT IGNORE INTO `glpi_useremails` (`id`, `users_id`, `is_default`, `is_dynamic`, `email`) VALUES ('0', '$id', '0', '0', '" . $user_array[$result[0]["email"]] . "');";
                $DB->queryOrDie($querry);
            }

            if (isset($user_array[$result[0]["locale"]])) {
                $DB->updateOrInsert("glpi_users", ['language' => $DB->escape($user_array[$result[0]["locale"]])], ['id' => $id]);
            }

            if (isset($user_array[$result[0]["phone_number"]])) {
                $DB->updateOrInsert("glpi_users", ['phone' => $DB->escape($user_array[$result[0]["phone_number"]])], ['id' => $id]);
            }

            $DB->updateOrInsert("glpi_users", ['date_mod' => $_SESSION["glpi_currenttime"]], ['id' => $id]);


            if (isset($user_array[$result[0]["group"]])) {
                foreach ($data = $user_array[$result[0]["group"]] as $value) {
                    $id_group_create = 0;
                    $request = self::getAdapter()->request(['FROM' => 'glpi_groups']);

                    while ($data = $request->fetchAssociative()) {
                        if ($data['name'] == $value) {
                            $id_group_create = $data['id'];
                            break;
                        }
                    }

                    $querry = "INSERT IGNORE INTO `glpi_groups` (`id`, `name`, `completename`) VALUES ($id_group_create, '$value', '$value');";
                    $DB->queryOrDie($querry);
                    $request = self::getAdapter()->request(['FROM' =>'glpi_groups']);

                    while ($data = $request->fetchAssociative()) {
                        $id_group = $data['id'];
                        if ($data['name'] == $value) {
                            break;
                        }
                    }

                    $querry = "INSERT IGNORE INTO `glpi_groups_users` (`id`, `users_id`, `groups_id`) VALUES ('0', '$id', '$id_group');";
                    $DB->queryOrDie($querry);
                }
            }
        }

        $request = self::getAdapter()->request(['FROM' => 'glpi_oidc_users']);

        while ($data = $request->fetchAssociative()) {
            $user_id = $data['id'];

            if ($data['user_id'] == $id) {
                $find = true;
            }
        }

        if (!isset($find)) {
            $DB->updateOrInsert("glpi_oidc_users", ['user_id' => $id, 'update' => 1], ['id' => 0]);
        } else {
            $DB->updateOrInsert("glpi_oidc_users", ['user_id' => $id, 'update' => 1], ['id' => $user_id]);
        }
    }

    /**
     * Show user config form
     *
     * @return void
     */
    public static function showFormUserConfig()
    {
        global $DB, $CFG_GLPI;

        if (isset($_POST["config"])) {
            Html::redirect("auth.oidc.php");
        }

        if (isset($_POST["update"])) {
            $oidc_result = [
                'name' => $_POST["name"],
                'given_name'  => $_POST["given_name"],
                'family_name'  => $_POST["family_name"],
                'picture'  => $_POST["picture"],
                'email'  => $_POST["email"],
                'locale'  => $_POST["locale"],
                'phone_number'  => $_POST["phone_number"],
                'group'  => $_POST["group"],
                'date_mod' => $_SESSION["glpi_currenttime"],
            ];
            $DB->updateOrInsert("glpi_oidc_mapping", $oidc_result, ['id'   => 0]);
        }

        $criteria = ["SELECT * FROM glpi_oidc_mapping"];
        $requests = self::getAdapter()->request($criteria);
        $oidc_db = [
            'name' => null,
            'given_name'  => null,
            'family_name'  => null,
            'picture'  => null,
            'email'  => null,
            'locale'  => null,
            'phone_number'  => null,
            'group'  => null,
            'date_mod' => null,
        ];
        $results = $requests->fetchAllAssociative();
        foreach ($results as $result) {
            $oidc_db['name'] = $result["name"];
            $oidc_db['given_name']  = $result["given_name"];
            $oidc_db['family_name']  = $result["family_name"];
            $oidc_db['picture']  = $result["picture"];
            $oidc_db['email']  = $result["email"];
            $oidc_db['locale']  = $result["locale"];
            $oidc_db['phone_number']  = $result["phone_number"];
            $oidc_db['group']  = $result["group"];
            $oidc_db['date_mod']  = $result["date_mod"];
        }

        $form = [
            'action' => $CFG_GLPI['root_doc'] . '/front/auth.oidc_profile.php',
            'buttons' => [
                [
                    'type' => 'submit',
                    'name' => 'update',
                    'value' => __s('Save'),
                    'class' => 'btn btn-secondary',
                ],
                [
                    'type' => 'submit',
                    'name' => 'config',
                    'value' => __s('Configuration'),
                    'class' => 'btn btn-secondary'
                ]
            ],
            'content' => [
                __('Mapping of fields according to provider') => [
                    'visible' => true,
                    'inputs' => [
                        ('') => [
                            'name' => 'id',
                            'type' => 'hidden',
                            'value' => '',
                        ],
                        __('Name') => [
                            'name' => 'name',
                            'type' => 'text',
                            'value' => $oidc_db['name'],
                        ],
                        __('Surname') => [
                            'name' => 'family_name',
                            'type' => 'text',
                            'value' => $oidc_db['family_name'],
                        ],
                        __('First name') => [
                            'name' => 'given_name',
                            'type' => 'text',
                            'value' => $oidc_db['given_name'],
                        ],
                        __('Email') => [
                            'name' => 'email',
                            'type' => 'text',
                            'value' => $oidc_db['email'],
                        ],
                        __('Phone') => [
                            'name' => 'phone_number',
                            'type' => 'text',
                            'value' => $oidc_db['phone_number'],
                        ],
                        __('Locale') => [
                            'name' => 'locale',
                            'type' => 'text',
                            'value' => $oidc_db['locale'],
                        ],
                        __('Picture') => [
                            'name' => 'picture',
                            'type' => 'text',
                            'value' => $oidc_db['picture'],
                        ],
                        __('Group') => [
                            'name' => 'group',
                            'type' => 'text',
                            'value' => $oidc_db['group'],
                        ],
                        __('Last update') => [
                            'name' => 'date_mod',
                            'type' => 'text',
                            'disabled' => '',
                            'value' => $oidc_db['date_mod'],
                        ]
                    ]
                ]
            ]
        ];

        renderTwigForm($form);
    }
}
