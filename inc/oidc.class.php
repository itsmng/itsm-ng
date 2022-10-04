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
class Oidc extends CommonDBTM {

   static $_user_data;

   static function auth()
   {

      global $DB, $CFG_GLPI;

      //Get config from DB and use it to setup oidc
      $criteria = "SELECT * FROM glpi_oidc_config";
      $iterators = $DB->request($criteria);
      foreach($iterators as $iterator) {
          $oidc_db['Provider'] = $iterator['Provider'];
          $oidc_db['ClientID'] = $iterator['ClientID'];
          $oidc_db['ClientSecret'] = $iterator['ClientSecret'];
          $oidc_db['scope'] = explode(',', addslashes(str_replace(' ', '', $iterator['scope'])));
      }

      $oidc = new Jumbojett\OpenIDConnectClient($iterator['Provider'], $iterator['ClientID'], $iterator['ClientSecret']);
      if (is_array($oidc_db['scope'])) {
         $oidc->addScope($oidc_db['scope']);
      }
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
      self::$_user_data = $user_array;
      //var_dump(self::$_user_data);
      //die;
      //Create and/or authenticated a user
      $criteria = "SELECT * FROM glpi_users";
      $iterators = $DB->request($criteria);
      $newUser = true;

      if (isset($user_array["name"])) {
          foreach($iterators as $iterator)
          if ($user_array['name'] == $iterator['name']) {
              $ID = $iterator['id'];
              $newUser = false;
          }
       
          $user = new User();
          if ($newUser) {
              $input = ['name'     => $user_array['name'],
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
         
      $request = $DB->request('glpi_oidc_mapping');
      while ($data = $request->next()) {
         $mapping_date_mod = $data["date_mod"];
      }
      $request = $DB->request('glpi_users', ["id" => $ID]);
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
      $_SESSION['itsm_is_oidc'] = 1;
      $_SESSION['itsm_oidc_idtoken'] = $oidc->getIdToken();
      Auth::redirectIfAuthenticated();
   }

   /**
    * Add oidc data to user's db via a mapping
    *
    * @return void
    */
   static function addUserData($user_array, $id)
   {
      global $DB;
      $criteria = "SELECT * FROM glpi_oidc_mapping";
      $iterators = $DB->request($criteria);
      while ($data = $iterators->next())
         $result[] = $data;
      
      if (isset($user_array[$result[0]["name"]]))
         $DB->updateOrInsert("glpi_users", ['name'   => $user_array[$result[0]["name"]]], ['id'   => $id]);
      if (isset($user_array[$result[0]["given_name"]]))
         $DB->updateOrInsert("glpi_users", ['firstname'   => $user_array[$result[0]["given_name"]]], ['id'   => $id]);
      if (isset($user_array[$result[0]["family_name"]]))
         $DB->updateOrInsert("glpi_users", ['realname'   => $user_array[$result[0]["family_name"]]], ['id'   => $id]);
      if (isset($user_array[$result[0]["picture"]]))
         $DB->updateOrInsert("glpi_users", ['picture'   => $user_array[$result[0]["picture"]]], ['id'   => $id]);
      if (isset($user_array[$result[0]["email"]])) {
         $querry = "INSERT IGNORE INTO `glpi_useremails` (`id`, `users_id`, `is_default`, `is_dynamic`, `email`) VALUES ('0', '$id', '0', '0', '". $user_array[$result[0]["email"]] ."');";
         $DB->queryOrDie($querry);
               
      }
      if (isset($user_array[$result[0]["locale"]]))
         $DB->updateOrInsert("glpi_users", ['language'   => $user_array[$result[0]["locale"]]], ['id'   => $id]);
      if (isset($user_array[$result[0]["phone_number"]]))
         $DB->updateOrInsert("glpi_users", ['phone'   => $user_array[$result[0]["phone_number"]]], ['id'   => $id]);
      $DB->updateOrInsert("glpi_users", ['date_mod'   => $_SESSION["glpi_currenttime"]], ['id'   => $id]);
      if (isset($user_array[$result[0]["group"]])) {
         
         foreach ($data = $user_array[$result[0]["group"]] as $value) {
            $id_group_create = 0;
            $request = $DB->request('glpi_groups');
            while ($data = $request->next()) {
               if ($data['name'] == $value) {
                  $id_group_create = $data['id'];
                  break;
               }
            }
            $querry = "INSERT IGNORE INTO `glpi_groups` (`id`, `name`, `completename`) VALUES ($id_group_create, '$value', '$value');";
            $DB->queryOrDie($querry);
            $request = $DB->request('glpi_groups');
            while ($data = $request->next()) {
               $id_group = $data['id'];
               if ($data['name'] == $value) {
                  break;
               }
            }
            $querry = "INSERT IGNORE INTO `glpi_groups_users` (`id`, `users_id`, `groups_id`) VALUES ('0', '$id', '$id_group');";
            $DB->queryOrDie($querry);
         }
      }
      $request = $DB->request('glpi_oidc_users');
      while ($data = $request->next()) {
         $user_id = $data['id'];
         //$update = $data['update'];
         if ($data['user_id'] == $id)
            $find = true;
      }

      if (!isset($find)) {

         $DB->updateOrInsert("glpi_oidc_users", ['user_id'   => $id, 
                                                 'update'   => 1], ['id'   => 0]);
      } else {
         $DB->updateOrInsert("glpi_oidc_users", ['user_id'   => $id, 
                                                 'update'   => 1], ['id'   => $user_id]);
      }
   }

   /**
    * Show user config form
    *
    * @return void
    */
   static function showFormUserConfig() {

      global $DB;

      if (isset($_POST["config"])) {
         Html::redirect("auth.oidc.php");
      }

      if (isset($_POST["update"])) {
         $oidc_result = [
            'name'   => $_POST["name"],
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

      $criteria = "SELECT * FROM glpi_oidc_mapping";
      $iterators = $DB->request($criteria);
      $oidc_db = [];

      foreach($iterators as $iterator) {
         $oidc_db['name']   = $iterator["name"];
         $oidc_db['given_name']  = $iterator["given_name"];
         $oidc_db['family_name']  = $iterator["family_name"];
         $oidc_db['picture']  = $iterator["picture"];
         $oidc_db['email']  = $iterator["email"];
         $oidc_db['locale']  = $iterator["locale"];
         $oidc_db['phone_number']  = $iterator["phone_number"];
         $oidc_db['group']  = $iterator["group"];
         $oidc_db['date_mod']  = $iterator["date_mod"];
      }

      echo "<div class='center'>";
      echo "<form method='post' action='./auth.oidc_profile.php'>";
      echo "<input type='hidden' name='id' value='JAAJ'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr class='tab_bg_1'>";
      echo "<th class='center' colspan='4'>" . __('Mapping of fields according to provider') . "</th></tr>";

      echo "<tr class='tab_bg_2'><td>" . __('Email') . "</td>";
      if (array_key_exists('email', $oidc_db)) {
         echo "<td><input type='text' name='email' value='". $oidc_db['email'] ."'></td>";
      } else {
         echo "<td><input type='text' name='email' value='". "" ."'></td>";
      }
      echo "<td>" . __('Name') . "</td>";
      if (array_key_exists('name', $oidc_db)) {
         echo "<td><input type='text' name='name' value='". $oidc_db['name'] ."'></td>";
      } else {
         echo "<td><input type='text' name='name' value='". "" ."'></td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_2'><td>" . __('Surname') . "</td>";
      if (array_key_exists('family_name', $oidc_db)) {
         echo "<td><input type='text' name='family_name' value='". $oidc_db['family_name'] ."'></td>";
      } else {
         echo "<td><input type='text' name='family_name' value='". "" ."'></td>";
      }
      echo "<td>" . __('First name') . "</td>";
      if (array_key_exists('given_name', $oidc_db)) {
         echo "<td><input type='text' name='given_name' value='". $oidc_db['given_name'] ."'></td>";
      } else {
         echo "<td><input type='text' name='given_name' value='". "" ."'></td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_2'><td>" . __('Locale') . "</td>";
      if (array_key_exists('locale', $oidc_db)) {
         echo "<td><input type='text' name='locale' value='". $oidc_db['locale'] ."'></td>";
      } else {
         echo "<td><input type='text' name='locale' value='". "" ."'></td>";
      }
      echo "<td>" . __('Phone') . "</td>";
      if (array_key_exists('phone_number', $oidc_db)) {
         echo "<td><input type='text' name='phone_number' value='". $oidc_db['phone_number'] ."'></td>";
      } else {
         echo "<td><input type='text' name='phone_number' value='". "" ."'></td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_2'><td>" . __('Group') . "</td>";
      if (array_key_exists('group', $oidc_db)) {
         echo "<td><input type='text' name='group' value='". $oidc_db['group'] ."'></td>";
      } else {
         echo "<td><input type='text' name='group' value='". "" ."'></td>";
      }
      echo "<td>" . __('Picture') . "</td>";
      if (array_key_exists('picture', $oidc_db)) {
         echo "<td><input type='text' name='picture' value='". $oidc_db['picture'] ."'></td>";
      } else {
         echo "<td><input type='text' name='picture' value='". "" ."'></td>";
      }
      echo "</tr>";
      
      echo "<tr class='tab_bg_2'><td>" . __('Last update') . "</td>";
      if (array_key_exists('date_mod', $oidc_db)) {
         echo "<td><input type='text' name='date_mod' value='". $oidc_db['date_mod'] ."'></td>";
      } else {
         echo "<td><input type='text' name='date_mod' value='". "" ."'></td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_2'><td class='center' colspan='4'>";
      echo "<input type='submit' name='update' class='submit' value=\"".__s('Save')."\">" . '&nbsp;';
      echo "<input type='submit' name='config' class='submit' value=\"".__s('Configuration')."\" >";
      echo "</td></tr>";
      echo "</table>";
      Html::closeForm();

      echo "</div>";
   }
}
