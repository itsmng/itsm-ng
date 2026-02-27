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

require __DIR__ . "/../vendor/autoload.php";

if (!defined("GLPI_ROOT")) {
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
        $criteria = "SELECT * FROM glpi_oidc_config";
        $iterators = $DB->request($criteria);
        foreach ($iterators as $iterator) {
            $oidc_db["Provider"] = $iterator["Provider"];
            $oidc_db["ClientID"] = $iterator["ClientID"];
            $oidc_db["ClientSecret"] = @Toolbox::sodiumDecrypt(
                $iterator["ClientSecret"],
            );
            $oidc_db["scope"] = explode(
                ",",
                addslashes(str_replace(" ", "", $iterator["scope"])),
            );
            $oidc_db["proxy"] = $iterator["proxy"];
            $oidc_db["cert"] = $iterator["cert"];
            $oidc_db["sso_link_users"] = $iterator["sso_link_users"];
        }

        $oidc = new Jumbojett\OpenIDConnectClient(
            $oidc_db["Provider"],
            $oidc_db["ClientID"],
            $oidc_db["ClientSecret"],
        );
        if (is_array($oidc_db["scope"])) {
            $oidc->addScope($oidc_db["scope"]);
        }
        if (isset($oidc_db["proxy"]) && $oidc_db["proxy"] != "") {
            $oidc->setHttpProxy($oidc_db["proxy"]);
        }
        if (
            isset($oidc_db["cert"]) &&
            $oidc_db["proxy"] != "" &&
            file_exists($oidc_db["cert"])
        ) {
            $oidc->setCertPath($oidc_db["cert"]);
        }
        $isCallback =
            isset($_GET["code"]) ||
            isset($_GET["id_token"]) ||
            isset($_GET["state"]);
        if (!$isCallback && isset($_REQUEST["redirect"])) {
            $requestedRedirect = self::sanitizeRedirect($_REQUEST["redirect"]);
            if ($requestedRedirect) {
                $cookieOptions = [
                    "expires" => time() + 300,
                    "path" => $CFG_GLPI["root_doc"] ?: "/",
                    "secure" =>
                        !empty($_SERVER["HTTPS"]) &&
                        $_SERVER["HTTPS"] !== "off",
                    "httponly" => true,
                    "samesite" => "Lax",
                ];
                if (PHP_VERSION_ID >= 70300) {
                    setcookie(
                        "itsm_oidc_redirect",
                        $requestedRedirect,
                        $cookieOptions,
                    );
                } else {
                    // Fallback for older PHP versions
                    setcookie(
                        "itsm_oidc_redirect",
                        $requestedRedirect,
                        $cookieOptions["expires"],
                        (string) $cookieOptions["path"],
                        "",
                        $cookieOptions["secure"],
                        true,
                    );
                }
            }
        }
        $oidc->setHttpUpgradeInsecureRequests(false);
        try {
            $oidc->authenticate();
        } catch (Exception $e) {
            //If something go wrong
            Html::nullHeader("Login", $CFG_GLPI["root_doc"] . "/index.php");
            echo '<div class="center b">';
            echo __("Missing or wrong fields in open ID connect config");
            echo '<p><a href="' .
                $CFG_GLPI["root_doc"] .
                "/index.php" .
                '">' .
                __("Log in again") .
                "</a></p>";
            echo "<div>" . $e->getMessage() . "</div>";
            echo "</div>";
            Html::nullFooter();
            die();
        }

        $result = $oidc->requestUserInfo();
        $user_array = (array) $result;
        self::$_user_data = $user_array;

        $redirectTarget = null;
        if (isset($_COOKIE["itsm_oidc_redirect"])) {
            $redirectTarget = self::sanitizeRedirect(
                $_COOKIE["itsm_oidc_redirect"],
            );
        }

        //Create and/or authenticated a user
        $newUser = true;
        $ID = false;

        // Check for custom mapping for the username
        $mapping_iterator = $DB->request("SELECT * FROM glpi_oidc_mapping");
        $mapping = $mapping_iterator->next();

        $auth_username = null;
        if ($mapping && !empty($mapping['name']) && isset($user_array[$mapping['name']])) {
            $auth_username = $user_array[$mapping['name']];
        }

        if (empty($auth_username)) {
            $auth_username = $user_array["name"] ?? $user_array["sub"] ?? null;
        }

        if ($auth_username) {
            $iterator = $DB->request([
                'FROM' => 'glpi_users',
                'WHERE' => ['name' => $auth_username]
            ]);

            foreach ($iterator as $user_data) {
                $canLink = $oidc_db["sso_link_users"] || $user_data["authtype"] == Auth::EXTERNAL;
                if ($canLink) {
                    $ID = $user_data["id"];
                    $newUser = false;
                    break;
                }
            }
        }

        $user = new User();
        if ($newUser && $auth_username) {
            $input = [
                "name" => $auth_username,
                "_extauth" => 1,
                "add" => 1,
            ];
            $ID = $user->add($input);

            if (!$ID) {
                Toolbox::logInFile("oidc", "Failed to create user '$auth_username'. User::add returned false.\n");
            }
        }

        if (!$ID || !$user->getFromDB($ID)) {
            Toolbox::logInFile("oidc", "Login failed: User ID '$ID' could not be retrieved. User Data: " . json_encode($user_array) . "\n");
            Html::nullHeader("Login Error", $CFG_GLPI["root_doc"] . "/index.php");
            echo '<div class="center b">';
            echo __("Login failed. User could not be found or created.");
            echo '<p><a href="' . $CFG_GLPI["root_doc"] . '/index.php">' . __("Log in again") . "</a></p>";
            echo "</div>";
            Html::nullFooter();
            die();
        }

        $request = $DB->request("glpi_oidc_mapping");
        while ($data = $request->next()) {
            $mapping_date_mod = $data["date_mod"];
        }
        $request = $DB->request("glpi_users", ["id" => $ID]);
        while ($data = $request->next()) {
            $user_date_mod = $data["date_mod"];
        }

        //if ($mapping_date_mod > $user_date_mod)
        self::addUserData($user_array, $ID);

        $auth = new Auth();
        $auth->auth_succeded = true;
        $auth->user = $user;
        //Setup a new session and redirect to the main menu
        Session::init($auth);
        $_SESSION["itsm_is_oidc"] = 1;
        $_SESSION["itsm_oidc_idtoken"] = $oidc->getIdToken();
        // Check if we already have a redirect target from the cookie (preserved earlier)
        if (!$redirectTarget && isset($_REQUEST["redirect"])) {
            $redirectTarget = self::sanitizeRedirect($_REQUEST["redirect"]);
        }
        if (!$redirectTarget && isset($_COOKIE["itsm_oidc_redirect"])) {
            $redirectTarget = self::sanitizeRedirect(
                $_COOKIE["itsm_oidc_redirect"],
            );
        }
        // Clear the cookie
        if (isset($_COOKIE["itsm_oidc_redirect"])) {
            $cookiePath = $CFG_GLPI["root_doc"] ?: "/";
            setcookie(
                "itsm_oidc_redirect",
                "",
                time() - 3600,
                (string) $cookiePath,
                "",
                isset($_SERVER["HTTPS"]),
                true,
            );
        }
        if (!$redirectTarget) {
            $redirectTarget = "/"; // fallback root
        }
        Auth::redirectIfAuthenticated($redirectTarget);
    }

    /**
     * Add oidc data to user's db via a mapping
     *
     * @return void
     */
    public static function addUserData($user_array, $id)
    {
        global $DB;

        $criteria = "SELECT * FROM glpi_oidc_mapping";
        $iterators = $DB->request($criteria);

        while ($data = $iterators->next()) {
            $result[] = $data;
        }

        if (isset($result)) {
            if (isset($user_array[$result[0]["name"]])) {
                $DB->updateOrInsert(
                    "glpi_users",
                    ["name" => $DB->escape($user_array[$result[0]["name"]])],
                    ["id" => $id],
                );
            }

            if (isset($user_array[$result[0]["given_name"]])) {
                $DB->updateOrInsert(
                    "glpi_users",
                    [
                        "firstname" => $DB->escape(
                            $user_array[$result[0]["given_name"]],
                        ),
                    ],
                    ["id" => $id],
                );
            }

            if (isset($user_array[$result[0]["family_name"]])) {
                $DB->updateOrInsert(
                    "glpi_users",
                    [
                        "realname" => $DB->escape(
                            $user_array[$result[0]["family_name"]],
                        ),
                    ],
                    ["id" => $id],
                );
            }

            if (isset($user_array[$result[0]["picture"]])) {
                $DB->updateOrInsert(
                    "glpi_users",
                    [
                        "picture" => $DB->escape(
                            $user_array[$result[0]["picture"]],
                        ),
                    ],
                    ["id" => $id],
                );
            }

            if (isset($user_array[$result[0]["email"]])) {
                $email = trim((string) $user_array[$result[0]["email"]]);
                if ($email !== '' && !UserEmail::isEmailForUser($id, $email)) {
                    $useremail = new UserEmail();
                    $useremail->add([
                        'users_id'   => $id,
                        'email'      => $email,
                        'is_dynamic' => 0
                    ]);
                }
            }

            if (isset($user_array[$result[0]["locale"]])) {
                $DB->updateOrInsert(
                    "glpi_users",
                    [
                        "language" => $DB->escape(
                            $user_array[$result[0]["locale"]],
                        ),
                    ],
                    ["id" => $id],
                );
            }

            if (isset($user_array[$result[0]["phone_number"]])) {
                $DB->updateOrInsert(
                    "glpi_users",
                    [
                        "phone" => $DB->escape(
                            $user_array[$result[0]["phone_number"]],
                        ),
                    ],
                    ["id" => $id],
                );
            }

            $DB->updateOrInsert(
                "glpi_users",
                ["date_mod" => $_SESSION["glpi_currenttime"]],
                ["id" => $id],
            );

            if (isset($user_array[$result[0]["group"]])) {
                foreach ($data = $user_array[$result[0]["group"]] as $value) {
                    $id_group_create = 0;
                    $request = $DB->request("glpi_groups");

                    while ($data = $request->next()) {
                        if ($data["name"] == $value) {
                            $id_group_create = $data["id"];
                            break;
                        }
                    }

                    $querry = "INSERT IGNORE INTO `glpi_groups` (`id`, `name`, `completename`) VALUES ($id_group_create, '$value', '$value');";
                    $DB->queryOrDie($querry);
                    $request = $DB->request("glpi_groups");

                    while ($data = $request->next()) {
                        $id_group = $data["id"];
                        if ($data["name"] == $value) {
                            break;
                        }
                    }

                    $querry = "INSERT IGNORE INTO `glpi_groups_users` (`id`, `users_id`, `groups_id`) VALUES ('0', '$id', '$id_group');";
                    $DB->queryOrDie($querry);
                }
            }
        }

        $request = $DB->request("glpi_oidc_users");

        while ($data = $request->next()) {
            $user_id = $data["id"];

            if ($data["user_id"] == $id) {
                $find = true;
            }
        }

        if (!isset($find)) {
            $DB->updateOrInsert(
                "glpi_oidc_users",
                ["user_id" => $id, "update" => 1],
                ["id" => 0],
            );
        } else {
            $DB->updateOrInsert(
                "glpi_oidc_users",
                ["user_id" => $id, "update" => 1],
                ["id" => $user_id],
            );
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
                "name" => $_POST["name"],
                "given_name" => $_POST["given_name"],
                "family_name" => $_POST["family_name"],
                "picture" => $_POST["picture"],
                "email" => $_POST["email"],
                "locale" => $_POST["locale"],
                "phone_number" => $_POST["phone_number"],
                "group" => $_POST["group"],
                "date_mod" => $_SESSION["glpi_currenttime"],
            ];
            $DB->updateOrInsert("glpi_oidc_mapping", $oidc_result, ["id" => 0]);
        }

        $criteria = "SELECT * FROM glpi_oidc_mapping";
        $iterators = $DB->request($criteria);
        $oidc_db = [
            "name" => null,
            "given_name" => null,
            "family_name" => null,
            "picture" => null,
            "email" => null,
            "locale" => null,
            "phone_number" => null,
            "group" => null,
            "date_mod" => null,
        ];

        foreach ($iterators as $iterator) {
            $oidc_db["name"] = $iterator["name"];
            $oidc_db["given_name"] = $iterator["given_name"];
            $oidc_db["family_name"] = $iterator["family_name"];
            $oidc_db["picture"] = $iterator["picture"];
            $oidc_db["email"] = $iterator["email"];
            $oidc_db["locale"] = $iterator["locale"];
            $oidc_db["phone_number"] = $iterator["phone_number"];
            $oidc_db["group"] = $iterator["group"];
            $oidc_db["date_mod"] = $iterator["date_mod"];
        }

        $form = [
            "action" => $CFG_GLPI["root_doc"] . "/front/auth.oidc_profile.php",
            "buttons" => [
                [
                    "type" => "submit",
                    "name" => "update",
                    "value" => __s("Save"),
                    "class" => "btn btn-secondary",
                ],
                [
                    "type" => "submit",
                    "name" => "config",
                    "value" => __s("Configuration"),
                    "class" => "btn btn-secondary",
                ],
            ],
            "content" => [
                __("Mapping of fields according to provider") => [
                    "visible" => true,
                    "inputs" => [
                        "" => [
                            "name" => "id",
                            "type" => "hidden",
                            "value" => "",
                        ],
                        __("Name") => [
                            "name" => "name",
                            "type" => "text",
                            "value" => $oidc_db["name"],
                        ],
                        __("Surname") => [
                            "name" => "family_name",
                            "type" => "text",
                            "value" => $oidc_db["family_name"],
                        ],
                        __("First name") => [
                            "name" => "given_name",
                            "type" => "text",
                            "value" => $oidc_db["given_name"],
                        ],
                        __("Email") => [
                            "name" => "email",
                            "type" => "text",
                            "value" => $oidc_db["email"],
                        ],
                        __("Phone") => [
                            "name" => "phone_number",
                            "type" => "text",
                            "value" => $oidc_db["phone_number"],
                        ],
                        __("Locale") => [
                            "name" => "locale",
                            "type" => "text",
                            "value" => $oidc_db["locale"],
                        ],
                        __("Picture") => [
                            "name" => "picture",
                            "type" => "text",
                            "value" => $oidc_db["picture"],
                        ],
                        __("Group") => [
                            "name" => "group",
                            "type" => "text",
                            "value" => $oidc_db["group"],
                        ],
                        __("Last update") => [
                            "name" => "date_mod",
                            "type" => "text",
                            "disabled" => "",
                            "value" => $oidc_db["date_mod"],
                        ],
                    ],
                ],
            ],
        ];

        renderTwigForm($form);
    }

    /**
     * Sanitize a post-auth redirect path to avoid open redirects.
     * Accept only internal absolute paths (starting with '/') and disallow protocol-relative or external URLs.
     *
     * @param mixed $value
     * @return string|null
     */
    private static function sanitizeRedirect($value)
    {
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);
        if ($value === "") {
            return null;
        }
        if (preg_match("#^(?:[a-z][a-z0-9+.-]*:)?//#i", $value)) {
            return null;
        }
        if ($value[0] !== "/") {
            return null;
        }
        if (strpos($value, "\n") !== false || strpos($value, "\r") !== false) {
            return null;
        }
        return $value;
    }
}
