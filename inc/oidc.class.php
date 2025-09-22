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

        global $CFG_GLPI;

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
        $criteria = ["SELECT * FROM glpi_oidc_mapping"];
        $request = self::getAdapter()->request($criteria);
        $results = $request->fetchAllAssociative();

        if (!empty($results)) {
            $result = $results[0];
            $user_data = ['id' => $id];
            $fields_to_update = [
                'name'      => 'name',
                'given_name' => 'firstname',
                'family_name' => 'realname',
                'picture'   => 'picture',
                'locale'    => 'language',
                'phone_number' => 'phone'
            ];
            foreach ($fields_to_update as $oidc_field => $db_field) {
                if (isset($user_array[$result[$oidc_field]])) {
                    $user_data[$db_field] = $user_array[$result[$oidc_field]];
                }
            }
            $user_data['date_mod'] = $_SESSION["glpi_currenttime"];

            if (count($user_data) > 1) {
                self::getAdapter()->save(['glpi_users'], $user_data);
            }
            if (isset($user_array[$result["email"]])) {
                $email_data = [
                    'id' => 0,
                    'users_id' => $id,
                    'is_default' => 0,
                    'is_dynamic' => 0,
                    'email' => $user_array[$result["email"]]
                ];
                $email_exists = self::getAdapter()->request([
                    'COUNT' => 'cpt',
                    'FROM' => 'glpi_useremails',
                    'WHERE' => [
                        'users_id' => $id,
                        'email' => $user_array[$result["email"]]
                    ]
                ])->fetchAssociative();

                if ($email_exists['cpt'] == 0) {
                    self::getAdapter()->save(['glpi_useremails'], $email_data);
                }
            }
            if (isset($user_array[$result["group"]])) {
                foreach ($user_array[$result["group"]] as $value) {
                    $group_request = self::getAdapter()->request([
                        'FROM' => 'glpi_groups',
                        'WHERE' => ['name' => $value]
                    ]);
                    $group_results = $group_request->fetchAllAssociative();

                    $id_group = 0;
                    if (count($group_results) > 0) {
                        $id_group = $group_results[0]['id'];
                    } else {
                        $group_data = [
                            'name' => $value,
                            'completename' => $value
                        ];
                        self::getAdapter()->save(['glpi_groups'], $group_data);
                        $new_group = self::getAdapter()->request([
                            'FROM' => 'glpi_groups',
                            'WHERE' => ['name' => $value]
                        ])->fetchAllAssociative();

                        if (count($new_group) > 0) {
                            $id_group = $new_group[0]['id'];
                        }
                    }
                    if ($id_group > 0) {
                        $group_user_exists = self::getAdapter()->request([
                            'COUNT' => 'cpt',
                            'FROM' => 'glpi_groups_users',
                            'WHERE' => [
                                'users_id' => $id,
                                'groups_id' => $id_group
                            ]
                        ])->fetchAssociative();
                        if ($group_user_exists['cpt'] == 0) {
                            $group_user_data = [
                                'users_id' => $id,
                                'groups_id' => $id_group
                            ];
                            self::getAdapter()->save(['glpi_groups_users'], $group_user_data);
                        }
                    }
                }
            }
        }
        $oidc_users = self::getAdapter()->request([
            'FROM' => 'glpi_oidc_users',
            'WHERE' => ['user_id' => $id]
        ])->fetchAllAssociative();

        $oidc_user_data = [
            'user_id' => $id,
            'update' => 1
        ];
        if (count($oidc_users) > 0) {
            $oidc_user_data['id'] = $oidc_users[0]['id'];
        }
        self::getAdapter()->save(['glpi_oidc_users'], $oidc_user_data);
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
                'id' => 0,
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
            self::getAdapter()->save(['glpi_oidc_mapping'], $oidc_result);
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
