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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

use Sabre\VObject;
use Glpi\Exception\ForgetPasswordException;
use Glpi\Exception\PasswordTooWeakException;

class User extends CommonDBTM
{
    // From CommonDBTM
    public $dohistory         = true;
    public $history_blacklist = ['date_mod', 'date_sync', 'last_login',
                                      'publicbookmarkorder', 'privatebookmarkorder'];

    // NAME FIRSTNAME ORDER TYPE
    public const REALNAME_BEFORE   = 0;
    public const FIRSTNAME_BEFORE  = 1;

    public const IMPORTEXTAUTHUSERS  = 1024;
    public const READAUTHENT         = 2048;
    public const UPDATEAUTHENT       = 4096;

    public static $rightname = 'user';

    public static $undisclosedFields = [
       'password',
       'personal_token',
       'api_token',
       'cookie_token',
    ];

    private $entities = null;


    public static function getTypeName($nb = 0)
    {
        return _n('User', 'Users', $nb);
    }

    public static function getMenuShorcut()
    {
        return 'u';
    }

    public static function getAdditionalMenuOptions()
    {

        if (Session::haveRight('user', self::IMPORTEXTAUTHUSERS)) {
            return [
               'ldap' => [
                  'title' => AuthLDAP::getTypeName(Session::getPluralNumber()),
                  'page'  => '/front/ldap.php',
               ],
            ];
        }
        return false;
    }


    public function canViewItem()
    {
        if (
            Session::canViewAllEntities()
            || Session::haveAccessToOneOfEntities($this->getEntities())
        ) {
            return true;
        }
        return false;
    }


    public function canCreateItem()
    {

        // Will be created from form, with selected entity/profile
        if (
            isset($this->input['_profiles_id']) && ($this->input['_profiles_id'] > 0)
            && Profile::currentUserHaveMoreRightThan([$this->input['_profiles_id']])
            && isset($this->input['_entities_id'])
            && Session::haveAccessToEntity($this->input['_entities_id'])
        ) {
            return true;
        }
        // Will be created with default value
        if (
            Session::haveAccessToEntity(0) // Access to root entity (required when no default profile)
            || (Profile::getDefault() > 0)
        ) {
            return true;
        }

        if (
            ($_SESSION['glpiactive_entity'] > 0)
            && (Profile::getDefault() == 0)
        ) {
            echo "<div class='tab_cadre_fixe warning'>" .
                   __('You must define a default profile to create a new user') . "</div>";
        }

        return false;
    }


    public function canUpdateItem()
    {

        $entities = Profile_User::getUserEntities($this->fields['id'], false);
        if (
            Session::canViewAllEntities()
            || Session::haveAccessToOneOfEntities($entities)
        ) {
            return true;
        }
        return false;
    }


    public function canDeleteItem()
    {
        if (
            Session::canViewAllEntities()
            || Session::haveAccessToAllOfEntities($this->getEntities())
        ) {
            return true;
        }
        return false;
    }


    public function canPurgeItem()
    {
        return $this->canDeleteItem();
    }


    public function isEntityAssign()
    {
        // glpi_users.entities_id is only a pref.
        return false;
    }


    /**
     * Compute preferences for the current user mixing config and user data.
     *
     * @return void
     */
    public function computePreferences()
    {
        global $CFG_GLPI;

        if (isset($this->fields['id'])) {
            foreach ($CFG_GLPI['user_pref_field'] as $f) {
                if (is_null($this->fields[$f])) {
                    $this->fields[$f] = $CFG_GLPI[$f];
                }
            }
        }
        /// Specific case for show_count_on_tabs : global config can forbid
        if ($CFG_GLPI['show_count_on_tabs'] == -1) {
            $this->fields['show_count_on_tabs'] = 0;
        }
    }


    /**
     * Load minimal session for user.
     *
     * @param integer $entities_id  Entity to use
     * @param boolean $is_recursive Whether to load entities recursivly or not
     *
     * @return void
     *
     * @since 0.83.7
     */
    public function loadMinimalSession($entities_id, $is_recursive)
    {
        global $CFG_GLPI;

        if (isset($this->fields['id']) && !isset($_SESSION["glpiID"])) {
            Session::destroy();
            Session::start();
            $_SESSION["glpiID"]                      = $this->fields['id'];
            $_SESSION["glpi_use_mode"]               = Session::NORMAL_MODE;
            Session::loadEntity($entities_id, $is_recursive);
            $this->computePreferences();
            foreach ($CFG_GLPI['user_pref_field'] as $field) {
                if (isset($this->fields[$field])) {
                    $_SESSION["glpi$field"] = $this->fields[$field];
                }
            }
            Session::loadGroups();
            Session::loadLanguage();
        }
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case __CLASS__:
                $ong    = [];
                $ong[1] = __('Used items');
                $ong[2] = __('Managed items');
                return $ong;

            case 'Preference':
                return __('Main');
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        global $CFG_GLPI;

        switch ($item->getType()) {
            case __CLASS__:
                $item->showItems($tabnum == 2);
                return true;

            case 'Preference':
                $user = new self();
                $user->showMyForm(
                    $CFG_GLPI['root_doc'] . "/front/preference.php",
                    Session::getLoginUserID()
                );
                return true;
        }
        return false;
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addImpactTab($ong, $options);
        $this->addStandardTab('Profile_User', $ong, $options);
        $this->addStandardTab('Group_User', $ong, $options);
        $this->addStandardTab('Config', $ong, $options);
        $this->addStandardTab('Accessibility', $ong, $options);
        $this->addStandardTab(__CLASS__, $ong, $options);
        $this->addStandardTab('Ticket', $ong, $options);
        $this->addStandardTab('Item_Problem', $ong, $options);
        $this->addStandardTab('Change_Item', $ong, $options);
        $this->addStandardTab('Document_Item', $ong, $options);
        $this->addStandardTab('Reservation', $ong, $options);
        $this->addStandardTab('Auth', $ong, $options);
        $this->addStandardTab('Link', $ong, $options);
        $this->addStandardTab('Certificate_Item', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    public function post_getEmpty()
    {
        global $CFG_GLPI;

        $this->fields["is_active"] = 1;
        if (isset($CFG_GLPI["language"])) {
            $this->fields['language'] = $CFG_GLPI["language"];
        } else {
            $this->fields['language'] = "en_GB";
        }
    }


    public function pre_deleteItem()
    {
        global $DB;

        $entities = $this->getEntities();
        $view_all = Session::canViewAllEntities();
        // Have right on all entities ?
        $all      = true;
        if (!$view_all) {
            foreach ($entities as $ent) {
                if (!Session::haveAccessToEntity($ent)) {
                    $all = false;
                }
            }
        }
        if ($all) { // Mark as deleted
            return true;
        }
        // only delete profile
        foreach ($entities as $ent) {
            if (Session::haveAccessToEntity($ent)) {
                $all   = false;
                $DB->delete(
                    'glpi_profiles_users',
                    [
                      'users_id'     => $this->fields['id'],
                      'entities_id'  => $ent
                    ]
                );
            }
            return false;
        }
    }


    public function cleanDBonPurge()
    {

        global $DB;

        // ObjectLock does not extends CommonDBConnexity
        $ol = new ObjectLock();
        $ol->deleteByCriteria(['users_id' => $this->fields['id']]);

        // Reminder does not extends CommonDBConnexity
        $r = new Reminder();
        $r->deleteByCriteria(['users_id' => $this->fields['id']]);

        // Delete private bookmark
        $ss = new SavedSearch();
        $ss->deleteByCriteria(
            [
              'users_id'   => $this->fields['id'],
              'is_private' => 1,
            ]
        );

        // Set no user to public bookmark
        $DB->update(
            SavedSearch::getTable(),
            [
              'users_id' => 0
            ],
            [
              'users_id' => $this->fields['id']
            ]
        );

        // Set no user to consumables
        $DB->update(
            'glpi_consumables',
            [
              'items_id' => 0,
              'itemtype' => 'NULL',
              'date_out' => 'NULL'
            ],
            [
              'items_id' => $this->fields['id'],
              'itemtype' => 'User'
            ]
        );

        $this->deleteChildrenAndRelationsFromDb(
            [
              Certificate_Item::class,
              Change_User::class,
              Group_User::class,
              KnowbaseItem_User::class,
              Problem_User::class,
              Profile_User::class,
              ProjectTaskTeam::class,
              ProjectTeam::class,
              Reminder_User::class,
              RSSFeed_User::class,
              SavedSearch_User::class,
              Ticket_User::class,
              UserEmail::class,
            ]
        );

        if ($this->fields['id'] > 0) { // Security
            // DisplayPreference does not extends CommonDBConnexity
            $dp = new DisplayPreference();
            $dp->deleteByCriteria(['users_id' => $this->fields['id']]);
        }

        unlink($this->fields['picture']);

        // Ticket rules use various _users_id_*
        Rule::cleanForItemAction($this, '_users_id%');
        Rule::cleanForItemCriteria($this, '_users_id%');

        // Alert does not extends CommonDBConnexity
        $alert = new Alert();
        $alert->cleanDBonItemDelete($this->getType(), $this->fields['id']);
    }


    /**
     * Retrieve a user from the database using its login.
     *
     * @param string $name Login of the user
     *
     * @return boolean
     */
    public function getFromDBbyName($name)
    {
        return $this->getFromDBByCrit(['name' => $name]);
    }

    /**
     * Retrieve a user from the database using its login.
     *
     * @param string  $name     Login of the user
     * @param integer $authtype Auth type (see Auth constants)
     * @param integer $auths_id ID of auth server
     *
     * @return boolean
     */
    public function getFromDBbyNameAndAuth($name, $authtype, $auths_id)
    {
        return $this->getFromDBByCrit([
           'name'     => $name,
           'authtype' => $authtype,
           'auths_id' => $auths_id
           ]);
    }

    /**
     * Retrieve a user from the database using value of the sync field.
     *
     * @param string $value Value of the sync field
     *
     * @return boolean
     */
    public function getFromDBbySyncField($value)
    {
        return $this->getFromDBByCrit(['sync_field' => $value]);
    }

    /**
     * Retrieve a user from the database using it's dn.
     *
     * @since 0.84
     *
     * @param string $user_dn dn of the user
     *
     * @return boolean
     */
    public function getFromDBbyDn($user_dn)
    {
        return $this->getFromDBByCrit(['user_dn' => $user_dn]);
    }

    /**
     * Get users ids matching the given email
     *
     * @param string $email     Email to search for
     * @param array  $condition Extra conditions
     *
     * @return array Found users ids
     */
    public static function getUsersIdByEmails(
        string $email,
        array $condition = []
    ): array {
        global $DB;

        $query = [
           'SELECT'    => self::getTable() . '.id',
           'FROM'      => self::getTable(),
           'LEFT JOIN' => [
              UserEmail::getTable() => [
                 'FKEY' => [
                    self::getTable()      => 'id',
                    UserEmail::getTable() => self::getForeignKeyField()
                 ]
              ]
           ],
           'WHERE' => [UserEmail::getTable() . '.email' => $email] + $condition
        ];

        $data = iterator_to_array($DB->request($query));
        return array_column($data, 'id');
    }

    /**
     * Get the number of users using the given email
     *
     * @param string $email     Email to search for
     * @param array  $condition Extra conditions
     *
     * @return int Number of users found
     */
    public static function countUsersByEmail($email, $condition = []): int
    {
        return count(self::getUsersIdByEmails($email, $condition));
    }

    /**
     * Retrieve a user from the database using its email.
     *
     * @since 9.3 Can pass condition as a parameter
     *
     * @param string $email     user email
     * @param array  $condition add condition
     *
     * @return boolean
     */
    public function getFromDBbyEmail($email, $condition = [])
    {
        $ids = self::getUsersIdByEmails($email, $condition);

        if (count($ids) == 1) {
            return $this->getFromDB(current($ids));
        }

        return false;
    }


    /**
     * Get the default email of the user.
     *
     * @return string
     */
    public function getDefaultEmail()
    {

        if (!isset($this->fields['id'])) {
            return '';
        }

        return UserEmail::getDefaultForUser($this->fields['id']);
    }


    /**
     * Get all emails of the user.
     *
     * @return string[]
     */
    public function getAllEmails()
    {

        if (!isset($this->fields['id'])) {
            return [];
        }
        return UserEmail::getAllForUser($this->fields['id']);
    }


    /**
     * Check if the email is attached to the current user.
     *
     * @param string $email
     *
     * @return boolean
     */
    public function isEmail($email)
    {

        if (!isset($this->fields['id'])) {
            return false;
        }
        return UserEmail::isEmailForUser($this->fields['id'], $email);
    }


    /**
     * Retrieve a user from the database using its personal token.
     *
     * @param string $token user token
     * @param string $field the field storing the token
     *
     * @return boolean
     */
    public function getFromDBbyToken($token, $field = 'personal_token')
    {
        if (!is_string($token)) {
            trigger_error(
                sprintf('Unexpected token value received: "string" expected, received "%s".', gettype($token)),
                E_USER_WARNING
            );
            return false;
        }

        $fields = ['personal_token', 'api_token'];
        if (!in_array($field, $fields)) {
            Toolbox::logWarning('User::getFromDBbyToken() can only be called with $field parameter with theses values: \'' . implode('\', \'', $fields) . '\'');
            return false;
        }

        return $this->getFromDBByCrit([$this->getTable() . ".$field" => $token]);
    }


    public function prepareInputForAdd($input)
    {
        global $DB;

        if (isset($input['_stop_import'])) {
            return false;
        }

        if (!Auth::isValidLogin(stripslashes($input['name']))) {
            Session::addMessageAfterRedirect(
                __('The login is not valid. Unable to add the user.'),
                false,
                ERROR
            );
            return false;
        }

        // avoid xss (picture field is autogenerated)
        if (isset($input['picture'])) {
            $input['picture'] = 'NULL';
        }

        if (!isset($input["authtype"])) {
            $input["authtype"] = Auth::DB_GLPI;
        }

        if (!isset($input["auths_id"])) {
            $input["auths_id"] = 0;
        }

        // Check if user does not exists
        $iterator = $DB->request([
           'FROM'   => $this->getTable(),
           'WHERE'  => [
              'name'      => $input['name'],
              'authtype'  => $input['authtype'],
              'auths_id'  => $input['auths_id']
           ],
           'LIMIT'  => 1
        ]);

        if (count($iterator)) {
            Session::addMessageAfterRedirect(
                __('Unable to add. The user already exists.'),
                false,
                ERROR
            );
            return false;
        }

        if (isset($input["password2"])) {
            if (empty($input["password"])) {
                unset($input["password"]);
            } else {
                if ($input["password"] == $input["password2"]) {
                    if (Config::validatePassword($input["password"])) {
                        $input["password"]
                           = Auth::getPasswordHash(Toolbox::unclean_cross_side_scripting_deep(stripslashes($input["password"])));

                        $input['password_last_update'] = $_SESSION['glpi_currenttime'];
                    } else {
                        unset($input["password"]);
                    }
                    unset($input["password2"]);
                } else {
                    Session::addMessageAfterRedirect(
                        __('Error: the two passwords do not match'),
                        false,
                        ERROR
                    );
                    return false;
                }
            }
        }

        if (isset($input["_extauth"])) {
            $input["password"] = "";
        }

        // Force DB default values : not really needed
        if (!isset($input["is_active"])) {
            $input["is_active"] = 1;
        }

        if (!isset($input["is_deleted"])) {
            $input["is_deleted"] = 0;
        }

        if (!isset($input["entities_id"])) {
            $input["entities_id"] = 0;
        }

        if (!isset($input["profiles_id"])) {
            $input["profiles_id"] = 0;
        }

        return $input;
    }

    public function computeCloneName(
        string $current_name,
        ?int $copy_index = null
    ): string {
        return Toolbox::slugify(
            parent::computeCloneName($current_name, $copy_index)
        );
    }

    public function post_addItem()
    {

        $this->updateUserEmails();
        $this->syncLdapGroups();
        $this->syncDynamicEmails();

        $this->applyGroupsRules();
        $rulesplayed = $this->applyRightRules();
        $picture     = $this->syncLdapPhoto();

        //add picture in user fields
        if (!empty($picture)) {
            $this->update(['id'      => $this->fields['id'],
                                'picture' => $picture]);
        }

        // Add default profile
        if (!$rulesplayed) {
            $affectation = [];
            if (
                isset($this->input['_profiles_id']) && $this->input['_profiles_id']
                && Profile::currentUserHaveMoreRightThan([$this->input['_profiles_id']])
            ) {
                $profile                   = $this->input['_profiles_id'];
                // Choosen in form, so not dynamic
                $affectation['is_dynamic'] = 0;
            } else {
                $profile                   = Profile::getDefault();
                // Default right as dynamic. If dynamic rights are set it will disappear.
                $affectation['is_dynamic'] = 1;
                $affectation['is_default_profile'] = 1;
            }

            if ($profile) {
                if (isset($this->input["_entities_id"])) {
                    // entities_id (user's pref) always set in prepareInputForAdd
                    // use _entities_id for default right
                    $affectation["entities_id"] = $this->input["_entities_id"];
                } elseif (isset($_SESSION['glpiactive_entity'])) {
                    $affectation["entities_id"] = $_SESSION['glpiactive_entity'];
                } else {
                    $affectation["entities_id"] = 0;
                }
                if (isset($this->input["_is_recursive"])) {
                    $affectation["is_recursive"] = $this->input["_is_recursive"];
                } else {
                    $affectation["is_recursive"] = 0;
                }

                $affectation["profiles_id"]  = $profile;
                $affectation["users_id"]     = $this->fields["id"];
                $right                       = new Profile_User();
                $right->add($affectation);
            }
        }
    }


    public function prepareInputForUpdate($input)
    {
        global $CFG_GLPI;

        // avoid xss (picture name is autogenerated when uploading/synchronising the picture)
        if (isset($input['picture']) && !empty($input['picture'])) {
            $picture = json_decode(stripslashes($input['picture']), true)[0];
            unset($input['picture']);
        }

        //picture manually uploaded by user
        if (isset($input["_blank_picture"]) && $input["_blank_picture"]) {
            unlink($this->fields['picture']);
            $input['picture'] = 'NULL';
        } else {
            if (isset($picture) && !empty($picture) && $picture['path'] != $this->fields['picture']) {
                if (Document::isImage($picture['path'])) {
                    unlink(GLPI_PICTURE_DIR . '/' . $this->fields['picture']);
                    $uploadedPath = str_replace(GLPI_DOC_DIR . '/', '', GLPI_PICTURE_DIR) .
                         '/' . ItsmngUploadHandler::uploadFile($picture['path'], $picture['name'], ItsmngUploadHandler::PICTURE);
                    $input['picture'] = $uploadedPath;
                } else {
                    Session::addMessageAfterRedirect(
                        __('The file is not an image file.'),
                        false,
                        ERROR
                    );
                    @unlink($fullpath);
                }
            } else {
                //ldap jpegphoto synchronisation.
                $picture = $this->syncLdapPhoto();
                if (!empty($picture)) {
                    $input['picture'] = $picture;
                }
            }
        }

        if (isset($input["password2"])) {
            // Empty : do not update
            if (empty($input["password"])) {
                unset($input["password"]);
            } else {
                if ($input["password"] == $input["password2"]) {
                    // Check right : my password of user with lesser rights
                    if (
                        isset($input['id'])
                        && !Auth::checkPassword($input['password'], $this->fields['password']) // Validate that password is not same as previous
                        && Config::validatePassword($input["password"])
                        && (($input['id'] == Session::getLoginUserID())
                            || $this->currentUserHaveMoreRightThan($input['id'])
                            // Permit to change password with token and email
                            || (($input['password_forget_token'] == $this->fields['password_forget_token'])
                                && (abs(strtotime($_SESSION["glpi_currenttime"])
                                    - strtotime($this->fields['password_forget_token_date'])) < DAY_TIMESTAMP)))
                    ) {
                        $input["password"]
                           = Auth::getPasswordHash(Toolbox::unclean_cross_side_scripting_deep(stripslashes($input["password"])));

                        $input['password_last_update'] = $_SESSION["glpi_currenttime"];
                    } else {
                        unset($input["password"]);
                    }
                    unset($input["password2"]);
                } else {
                    Session::addMessageAfterRedirect(
                        __('Error: the two passwords do not match'),
                        false,
                        ERROR
                    );
                    return false;
                }
            }
        } elseif (isset($input["password"])) { // From login
            unset($input["password"]);
        }

        // prevent changing tokens and emails from users with lower rights
        if (Session::getLoginUserID() !== false
            && ((int) $input['id'] !== Session::getLoginUserID())) {
            $protected_input_keys = [
                'api_token',
                '_reset_api_token',
                'cookie_token',
                'password_forget_token',
                'personal_token',
                '_reset_personal_token',
                '_emails',
                '_useremails',
                'is_active',
            ];
            if (count(array_intersect($protected_input_keys, array_keys($input))) > 0
                && !$this->currentUserHaveMoreRightThan($input['id'])
            ) {
                foreach ($protected_input_keys as $input_key) {
                    unset($input[$input_key]);
                }
            }
        }

        // blank password when authtype changes
        if (
            isset($input["authtype"])
            && $input["authtype"] != Auth::DB_GLPI
            && $input["authtype"] != $this->getField('authtype')
        ) {
            $input["password"] = "";
        }

        // Update User in the database
        if (
            !isset($input["id"])
            && isset($input["name"])
        ) {
            if ($this->getFromDBbyName($input["name"])) {
                $input["id"] = $this->fields["id"];
            }
        }

        if (
            isset($input["entities_id"])
            && (Session::getLoginUserID() == $input['id'])
        ) {
            $_SESSION["glpidefault_entity"] = $input["entities_id"];
        }

        // Security on default profile update
        if (isset($input['profiles_id'])) {
            if (!in_array($input['profiles_id'], Profile_User::getUserProfiles($input['id']))) {
                unset($input['profiles_id']);
            }
        }

        // Security on default entity  update
        if (isset($input['entities_id'])) {
            if (!in_array($input['entities_id'], Profile_User::getUserEntities($input['id']))) {
                unset($input['entities_id']);
            }
        }

        // Security on default group  update
        if (
            isset($input['groups_id'])
            && !Group_User::isUserInGroup($input['id'], $input['groups_id'])
        ) {
            unset($input['groups_id']);
        }

        if (
            isset($input['_reset_personal_token'])
            && $input['_reset_personal_token']
        ) {
            $input['personal_token']      = self::getUniqueToken('personal_token');
            $input['personal_token_date'] = $_SESSION['glpi_currenttime'];
        }

        if (
            isset($input['_reset_api_token'])
            && $input['_reset_api_token']
        ) {
            $input['api_token']      = self::getUniqueToken('api_token');
            $input['api_token_date'] = $_SESSION['glpi_currenttime'];
        }

        // Manage preferences fields
        if (Session::getLoginUserID() == $input['id']) {
            if (
                isset($input['use_mode'])
                && ($_SESSION['glpi_use_mode'] !=  $input['use_mode'])
            ) {
                $_SESSION['glpi_use_mode'] = $input['use_mode'];
                unset($_SESSION['glpimenu']); // Force menu regeneration
                //Session::loadLanguage();
            }
        }

        foreach ($CFG_GLPI['user_pref_field'] as $f) {
            if (isset($input[$f])) {
                if (Session::getLoginUserID() == $input['id']) {
                    if ($_SESSION["glpi$f"] != $input[$f]) {
                        $_SESSION["glpi$f"] = $input[$f];
                        // reinit translations
                        if ($f == 'language') {
                            $_SESSION['glpi_dropdowntranslations'] = DropdownTranslation::getAvailableTranslations($_SESSION["glpilanguage"]);
                            unset($_SESSION['glpimenu']);
                        }
                    }
                }
                if ($input[$f] == $CFG_GLPI[$f]) {
                    $input[$f] = "NULL";
                }
            }
        }

        if (isset($input['language']) && GLPI_DEMO_MODE) {
            unset($input['language']);
        }

        if (array_key_exists('timezone', $input) && empty($input['timezone'])) {
            $input['timezone'] = 'NULL';
        }

        return $input;
    }


    public function post_updateItem($history = 1)
    {
        //handle timezone change for current user
        if ($this->fields['id'] == Session::getLoginUserID()) {
            if (null == $this->fields['timezone'] || 'null' === strtolower($this->fields['timezone'])) {
                unset($_SESSION['glpi_tz']);
            } else {
                $_SESSION['glpi_tz'] = $this->fields['timezone'];
            }
        }

        $this->updateUserEmails();
        $this->syncLdapGroups();
        $this->syncDynamicEmails();
        $this->applyGroupsRules();
        $this->applyRightRules();

        if (in_array('password', $this->updates)) {
            $alert = new Alert();
            $alert->deleteByCriteria(
                [
                  'itemtype' => $this->getType(),
                  'items_id' => $this->fields['id'],
                ],
                true
            );
        }
    }



    /**
     * Apply rules to determine dynamic rights of the user.
     *
     * @return boolean true if rules are applied, false otherwise
     */
    public function applyRightRules()
    {

        $return = false;

        if (
            isset($this->fields['_ruleright_process'])
            || isset($this->input['_ruleright_process'])
        ) {
            $dynamic_profiles = Profile_User::getForUser($this->fields["id"], true);

            if (
                isset($this->fields["id"])
                && ($this->fields["id"] > 0)
                && isset($this->input["_ldap_rules"])
                && count($this->input["_ldap_rules"])
            ) {
                //and add/update/delete only if it's necessary !
                if (isset($this->input["_ldap_rules"]["rules_entities_rights"])) {
                    $entities_rules = $this->input["_ldap_rules"]["rules_entities_rights"];
                } else {
                    $entities_rules = [];
                }

                if (isset($this->input["_ldap_rules"]["rules_entities"])) {
                    $entities = $this->input["_ldap_rules"]["rules_entities"];
                } else {
                    $entities = [];
                }

                if (isset($this->input["_ldap_rules"]["rules_rights"])) {
                    $rights = $this->input["_ldap_rules"]["rules_rights"];
                } else {
                    $rights = [];
                }

                $retrieved_dynamic_profiles = [];

                //For each affectation -> write it in DB
                foreach ($entities_rules as $entity) {
                    //Multiple entities assignation
                    if (is_array($entity[0])) {
                        foreach ($entity[0] as $ent) {
                            $retrieved_dynamic_profiles[] = [
                               'entities_id'  => $ent,
                               'profiles_id'  => $entity[1],
                               'is_recursive' => $entity[2],
                               'users_id'     => $this->fields['id'],
                               'is_dynamic'   => 1,
                            ];
                        }
                    } else {
                        $retrieved_dynamic_profiles[] = [
                           'entities_id'  => $entity[0],
                           'profiles_id'  => $entity[1],
                           'is_recursive' => $entity[2],
                           'users_id'     => $this->fields['id'],
                           'is_dynamic'   => 1,
                        ];
                    }
                }

                if (
                    (count($entities) > 0)
                    && (count($rights) == 0)
                ) {
                    if ($def_prof = Profile::getDefault()) {
                        $rights[] = $def_prof;
                    }
                }

                if (
                    (count($rights) > 0)
                    && (count($entities) > 0)
                ) {
                    foreach ($rights as $right) {
                        foreach ($entities as $entity) {
                            $retrieved_dynamic_profiles[] = [
                               'entities_id'  => $entity[0],
                               'profiles_id'  => $right,
                               'is_recursive' => $entity[1],
                               'users_id'     => $this->fields['id'],
                               'is_dynamic'   => 1,
                            ];
                        }
                    }
                }

                // Compare retrived profiles to existing ones : clean arrays to do purge and add
                if (count($retrieved_dynamic_profiles)) {
                    foreach ($retrieved_dynamic_profiles as $keyretr => $retr_profile) {
                        $found = false;

                        foreach ($dynamic_profiles as $keydb => $db_profile) {
                            // Found existing profile : unset values in array
                            if (
                                !$found
                                && ($db_profile['entities_id']  == $retr_profile['entities_id'])
                                && ($db_profile['profiles_id']  == $retr_profile['profiles_id'])
                                && ($db_profile['is_recursive'] == $retr_profile['is_recursive'])
                            ) {
                                unset($retrieved_dynamic_profiles[$keyretr]);
                                unset($dynamic_profiles[$keydb]);
                            }
                        }
                    }
                }

                // Add new dynamic profiles
                if (count($retrieved_dynamic_profiles)) {
                    $right = new Profile_User();
                    foreach ($retrieved_dynamic_profiles as $keyretr => $retr_profile) {
                        $right->add($retr_profile);
                    }
                }

                //Unset all the temporary tables
                unset($this->input["_ldap_rules"]);

                $return = true;
            } elseif (count($dynamic_profiles) == 1) {
                $dynamic_profile = reset($dynamic_profiles);

                // If no rule applied and only one dynamic profile found, check if
                // it is the default profile
                if ($dynamic_profile['is_default_profile'] == true) {
                    $default_profile = Profile::getDefault();

                    // Remove from to be deleted list
                    $dynamic_profiles = [];

                    // Update profile if need to match the current default profile
                    if ($dynamic_profile['profiles_id'] !== $default_profile) {
                        $pu = new Profile_User();
                        $dynamic_profile['profiles_id'] = $default_profile;
                        $pu->add($dynamic_profile);
                        $pu->delete([
                           'id' => $dynamic_profile['id']
                        ]);
                    }
                }
            }

            // Delete old dynamic profiles
            if (count($dynamic_profiles)) {
                $right = new Profile_User();
                foreach ($dynamic_profiles as $keydb => $db_profile) {
                    $right->delete($db_profile);
                }
            }
        }
        return $return;
    }


    /**
     * Synchronise LDAP group of the user.
     *
     * @return void
     */
    public function syncLdapGroups()
    {
        global $DB;

        // input["_groups"] not set when update from user.form or preference
        if (
            isset($this->fields["authtype"])
            && isset($this->input["_groups"])
            && (($this->fields["authtype"] == Auth::LDAP)
                || Auth::isAlternateAuth($this->fields['authtype']))
        ) {
            if (isset($this->fields["id"]) && ($this->fields["id"] > 0)) {
                $authtype = Auth::getMethodsByID($this->fields["authtype"], $this->fields["auths_id"]);

                if (count($authtype)) {
                    // Clean groups
                    $this->input["_groups"] = array_unique($this->input["_groups"]);

                    // Delete not available groups like to LDAP
                    $iterator = $DB->request([
                       'SELECT'    => [
                          'glpi_groups_users.id',
                          'glpi_groups_users.groups_id',
                          'glpi_groups_users.is_dynamic'
                       ],
                       'FROM'      => 'glpi_groups_users',
                       'LEFT JOIN' => [
                          'glpi_groups'  => [
                             'FKEY'   => [
                                'glpi_groups_users'  => 'groups_id',
                                'glpi_groups'        => 'id'
                             ]
                          ]
                       ],
                       'WHERE'     => [
                          'glpi_groups_users.users_id' => $this->fields['id']
                       ]
                    ]);

                    $groupuser = new Group_User();
                    while ($data =  $iterator->next()) {
                        if (in_array($data["groups_id"], $this->input["_groups"])) {
                            // Delete found item in order not to add it again
                            unset($this->input["_groups"][array_search(
                                $data["groups_id"],
                                $this->input["_groups"]
                            )]);
                        } elseif ($data['is_dynamic']) {
                            $groupuser->delete(['id' => $data["id"]]);
                        }
                    }

                    //If the user needs to be added to one group or more
                    if (count($this->input["_groups"]) > 0) {
                        foreach ($this->input["_groups"] as $group) {
                            $groupuser->add(['users_id'   => $this->fields["id"],
                                                  'groups_id'  => $group,
                                                  'is_dynamic' => 1]);
                        }
                        unset($this->input["_groups"]);
                    }
                }
            }
        }
    }


    /**
     * Synchronize picture (photo) of the user.
     *
     * @since 0.85
     *
     * @return string|boolean Filename to be stored in user picture field, false if no picture found
     */
    public function syncLdapPhoto()
    {

        if (
            isset($this->fields["authtype"])
            && (($this->fields["authtype"] == Auth::LDAP)
                 || ($this->fields["authtype"] == Auth::NOT_YET_AUTHENTIFIED
                     && !empty($this->fields["auths_id"]))
                 || Auth::isAlternateAuth($this->fields['authtype']))
        ) {
            if (isset($this->fields["id"]) && ($this->fields["id"] > 0)) {
                $config_ldap = new AuthLDAP();
                $ds          = false;

                //connect ldap server
                if ($config_ldap->getFromDB($this->fields['auths_id'])) {
                    $ds = $config_ldap->connect();
                }

                if ($ds) {
                    //get picture fields
                    $picture_field = $config_ldap->fields['picture_field'];
                    if (empty($picture_field)) {
                        return false;
                    }

                    //get picture content in ldap
                    $info = AuthLDAP::getUserByDn(
                        $ds,
                        $this->fields['user_dn'],
                        [$picture_field],
                        false
                    );

                    //getUserByDn returns an array. If the picture is empty,
                    //$info[$picture_field][0] is null
                    if (!isset($info[$picture_field][0]) || empty($info[$picture_field][0])) {
                        return "";
                    }
                    //prepare paths
                    $img       = array_pop($info[$picture_field]);
                    $filename  = uniqid($this->fields['id'] . '_');
                    $sub       = substr($filename, -2); /* 2 hex digit */
                    $file      = GLPI_PICTURE_DIR . "/$sub/{$filename}.jpg";

                    if (array_key_exists('picture', $this->fields)) {
                        $oldfile = GLPI_PICTURE_DIR . "/" . $this->fields["picture"];
                    } else {
                        $oldfile = null;
                    }

                    // update picture if not exist or changed
                    if (
                        empty($this->fields["picture"])
                        || !file_exists($oldfile)
                        || sha1_file($oldfile) !== sha1($img)
                    ) {
                        if (!is_dir(GLPI_PICTURE_DIR . "/$sub")) {
                            mkdir(GLPI_PICTURE_DIR . "/$sub");
                        }

                        //save picture
                        $outjpeg = fopen($file, 'wb');
                        fwrite($outjpeg, $img);
                        fclose($outjpeg);

                        //save thumbnail
                        $thumb = GLPI_PICTURE_DIR . "/$sub/{$filename}_min.jpg";
                        Toolbox::resizePicture($file, $thumb);

                        return "$sub/{$filename}.jpg";
                    }
                    return $this->fields["picture"];
                }
            }
        }

        return false;
    }


    /**
     * Update emails of the user.
     * Uses _useremails set from UI, not _emails set from LDAP.
     *
     * @return void
     */
    public function updateUserEmails()
    {
        // Update emails  (use _useremails set from UI, not _emails set from LDAP)

        $userUpdated = false;

        if (isset($this->input['_useremails']) && count($this->input['_useremails'])) {
            foreach ($this->input['_useremails'] as $id => $email) {
                $email = trim($email);

                // existing email
                $useremail = new UserEmail();
                if ($id > 0 && $useremail->getFromDB($id) && $useremail->fields['users_id'] === $this->getID()) {
                    $params = ['id' => $id];

                    if (strlen($email) === 0) {
                        // Empty email, delete it
                        $deleted = $useremail->delete($params);
                        $userUpdated = $userUpdated || $deleted;
                    } else { // Update email
                        $params['email'] = $email;
                        $params['is_default'] = $this->input['_default_email'] == $id ? 1 : 0;

                        $existingUserEmail = new UserEmail();
                        $existingUserEmail->getFromDB($id);
                        if (
                            $existingUserEmail->getFromDB($id)
                            && $params['email'] == $existingUserEmail->fields['email']
                            && $params['is_default'] == $existingUserEmail->fields['is_default']
                        ) {
                            // Do not update if email has not changed
                            continue;
                        }

                        $updated = $useremail->update($params);
                        $userUpdated = $userUpdated || $updated;
                    }
                } else {
                    // New email
                    $email_input = [
                       'email'    => $email,
                       'users_id' => $this->fields['id']
                    ];
                    if (
                        isset($this->input['_default_email'])
                        && ($this->input['_default_email'] == $id)
                    ) {
                        $email_input['is_default'] = 1;
                    } else {
                        $email_input['is_default'] = 0;
                    }
                    $added = $useremail->add($email_input);
                    $userUpdated = $userUpdated || $added;
                }
            }
        }

        if ($userUpdated) {
            // calling $this->update() here leads to loss in $this->input
            $user = new User();
            $user->update(['id' => $this->fields['id'], 'date_mod' => $_SESSION['glpi_currenttime']]);
        }
    }


    /**
     * Synchronise Dynamics emails of the user.
     * Uses _emails (set from getFromLDAP), not _usermails set from UI.
     *
     * @return void
     */
    public function syncDynamicEmails()
    {
        global $DB;

        $userUpdated = false;

        // input["_emails"] not set when update from user.form or preference
        if (
            isset($this->fields["authtype"])
            && isset($this->input["_emails"])
            && (($this->fields["authtype"] == Auth::LDAP)
                || Auth::isAlternateAuth($this->fields['authtype'])
                || ($this->fields["authtype"] == Auth::MAIL))
        ) {
            if (isset($this->fields["id"]) && ($this->fields["id"] > 0)) {
                $authtype = Auth::getMethodsByID($this->fields["authtype"], $this->fields["auths_id"]);

                if (
                    count($authtype)
                    || $this->fields["authtype"] == Auth::EXTERNAL
                ) {
                    // Clean emails
                    // Do a case insensitive comparison as it seems that some LDAP servers
                    // may return same email with different case sensitivity.
                    $unique_emails = [];
                    foreach ($this->input["_emails"] as $email) {
                        if (!in_array(strtolower($email), array_map('strtolower', $unique_emails))) {
                            $unique_emails[] = $email;
                        }
                    }
                    $this->input["_emails"] = $unique_emails;

                    // Delete not available groups like to LDAP
                    $iterator = $DB->request([
                       'SELECT' => [
                          'id',
                          'users_id',
                          'email',
                          'is_dynamic'
                       ],
                       'FROM'   => 'glpi_useremails',
                       'WHERE'  => ['users_id' => $this->fields['id']]
                    ]);

                    $useremail = new UserEmail();
                    while ($data = $iterator->next()) {
                        // Do a case insensitive comparison as email may be stored with a different case
                        $i = array_search(strtolower($data["email"]), array_map('strtolower', $this->input["_emails"]));
                        if ($i !== false) {
                            // Delete found item in order not to add it again
                            unset($this->input["_emails"][$i]);
                        } elseif ($data['is_dynamic']) {
                            // Delete not found email
                            $deleted = $useremail->delete(['id' => $data["id"]]);
                            $userUpdated = $userUpdated || $deleted;
                        }
                    }

                    //If the email need to be added
                    if (count($this->input["_emails"]) > 0) {
                        foreach ($this->input["_emails"] as $email) {
                            $added = $useremail->add(['users_id'   => $this->fields["id"],
                                                      'email'      => $email,
                                                      'is_dynamic' => 1]);
                            $userUpdated = $userUpdated || $added;
                        }
                        unset($this->input["_emails"]);
                    }
                }
            }
        }

        if ($userUpdated) {
            // calling $this->update() here leads to loss in $this->input
            $user = new User();
            $user->update(['id' => $this->fields['id'], 'date_mod' => $_SESSION['glpi_currenttime']]);
        }
    }

    protected function computeFriendlyName()
    {
        global $CFG_GLPI;

        if (isset($this->fields["id"]) && ($this->fields["id"] > 0)) {
            //computeFriendlyName should not add ID
            $bkp_conf = $CFG_GLPI['is_ids_visible'];
            $CFG_GLPI['is_ids_visible'] = 0;
            $bkp_sessconf = (isset($_SESSION['glpiis_ids_visible']) ? $_SESSION["glpiis_ids_visible"] : 0);
            $_SESSION["glpiis_ids_visible"] = 0;
            $name = formatUserName(
                $this->fields["id"],
                $this->fields["name"],
                (isset($this->fields["realname"]) ? $this->fields["realname"] : ''),
                (isset($this->fields["firstname"]) ? $this->fields["firstname"] : '')
            );

            $CFG_GLPI['is_ids_visible'] = $bkp_conf;
            $_SESSION["glpiis_ids_visible"] = $bkp_sessconf;
            return $name;
        }
        return '';
    }


    /**
     * Function that tries to load the user membership from LDAP
     * by searching in the attributes of the User.
     *
     * @param resource $ldap_connection LDAP connection
     * @param array    $ldap_method     LDAP method
     * @param string   $userdn          Basedn of the user
     * @param string   $login           User login
     *
     * @return string|boolean Basedn of the user / false if not found
     */
    private function getFromLDAPGroupVirtual($ldap_connection, array $ldap_method, $userdn, $login)
    {
        global $DB;

        // Search in DB the ldap_field we need to search for in LDAP
        $iterator = $DB->request([
           'SELECT'          => 'ldap_field',
           'DISTINCT'        => true,
           'FROM'            => 'glpi_groups',
           'WHERE'           => ['NOT' => ['ldap_field' => '']],
           'ORDER'           => 'ldap_field'
        ]);
        $group_fields = [];

        while ($data = $iterator->next()) {
            $group_fields[] = Toolbox::strtolower($data["ldap_field"]);
        }
        if (count($group_fields)) {
            //Need to sort the array because edirectory don't like it!
            sort($group_fields);

            // If the groups must be retrieve from the ldap user object
            $sr = @ ldap_read($ldap_connection, $userdn, "objectClass=*", $group_fields);
            $v  = AuthLDAP::get_entries_clean($ldap_connection, $sr);

            for ($i = 0; $i < $v['count']; $i++) {
                //Try to find is DN in present and needed: if yes, then extract only the OU from it
                if (
                    (($ldap_method["group_field"] == 'dn') || in_array('ou', $group_fields))
                    && isset($v[$i]['dn'])
                ) {
                    $v[$i]['ou'] = [];
                    for ($tmp = $v[$i]['dn']; count($tmptab = explode(',', $tmp, 2)) == 2; $tmp = $tmptab[1]) {
                        $v[$i]['ou'][] = $tmptab[1];
                    }

                    // Search in DB for group with ldap_group_dn
                    if (
                        ($ldap_method["group_field"] == 'dn')
                        && (count($v[$i]['ou']) > 0)
                    ) {
                        $group_iterator = $DB->request([
                           'SELECT' => 'id',
                           'FROM'   => 'glpi_groups',
                           'WHERE'  => ['ldap_group_dn' => Toolbox::addslashes_deep($v[$i]['ou'])]
                        ]);

                        while ($group = $group_iterator->next()) {
                            $this->fields["_groups"][] = $group['id'];
                        }
                    }

                    // searching with ldap_field='OU' and ldap_value is also possible
                    $v[$i]['ou']['count'] = count($v[$i]['ou']);
                }

                // For each attribute retrieve from LDAP, search in the DB
                foreach ($group_fields as $field) {
                    if (
                        isset($v[$i][$field])
                        && isset($v[$i][$field]['count'])
                        && ($v[$i][$field]['count'] > 0)
                    ) {
                        unset($v[$i][$field]['count']);
                        $lgroups = [];
                        foreach (Toolbox::addslashes_deep($v[$i][$field]) as $lgroup) {
                            $lgroups[] = [
                               new \QueryExpression($DB::quoteValue($lgroup) .
                                                    " LIKE " .
                                                    $DB::quoteName('ldap_value'))
                            ];
                        }
                        $group_iterator = $DB->request([
                           'SELECT' => 'id',
                           'FROM'   => 'glpi_groups',
                           'WHERE'  => [
                              'ldap_field' => $field,
                              'OR'         => $lgroups
                           ]
                        ]);

                        while ($group = $group_iterator->next()) {
                            $this->fields["_groups"][] = $group['id'];
                        }
                    }
                }
            } // for each ldapresult
        } // count($group_fields)
    }


    /**
     * Function that tries to load the user membership from LDAP
     * by searching in the attributes of the Groups.
     *
     * @param resource $ldap_connection    LDAP connection
     * @param array    $ldap_method        LDAP method
     * @param string   $userdn             Basedn of the user
     * @param string   $login              User login
     *
     * @return boolean true if search is applicable, false otherwise
     */
    private function getFromLDAPGroupDiscret($ldap_connection, array $ldap_method, $userdn, $login)
    {
        global $DB;

        // No group_member_field : unable to get group
        if (empty($ldap_method["group_member_field"])) {
            return false;
        }

        if ($ldap_method["use_dn"]) {
            $user_tmp = $userdn;
        } else {
            //Don't add $ldap_method["login_field"]."=", because sometimes it may not work (for example with posixGroup)
            $user_tmp = $login;
        }

        $v = $this->ldap_get_user_groups(
            $ldap_connection,
            $ldap_method["basedn"],
            $user_tmp,
            $ldap_method["group_condition"],
            $ldap_method["group_member_field"],
            $ldap_method["use_dn"],
            $ldap_method["login_field"]
        );
        foreach ($v as $result) {
            if (
                isset($result[$ldap_method["group_member_field"]])
                && is_array($result[$ldap_method["group_member_field"]])
                && (count($result[$ldap_method["group_member_field"]]) > 0)
            ) {
                $iterator = $DB->request([
                  'SELECT' => 'id',
                  'FROM'   => 'glpi_groups',
                  'WHERE'  => ['ldap_group_dn' => Toolbox::addslashes_deep($result[$ldap_method["group_member_field"]])]
                ]);

                while ($group = $iterator->next()) {
                    $this->fields["_groups"][] = $group['id'];
                }
            }
        }
        return true;
    }


    /**
     * Function that tries to load the user information from LDAP.
     *
     * @param resource $ldap_connection LDAP connection
     * @param array    $ldap_method     LDAP method
     * @param string   $userdn          Basedn of the user
     * @param string   $login           User Login
     * @param boolean  $import          true for import, false for update
     *
     * @return boolean true if found / false if not
     */
    public function getFromLDAP($ldap_connection, array $ldap_method, $userdn, $login, $import = true)
    {
        global $DB, $CFG_GLPI;

        // we prevent some delay...
        if (empty($ldap_method["host"])) {
            return false;
        }

        if ($ldap_connection !== false) {
            //Set all the search fields
            $this->fields['password'] = "";

            $fields  = AuthLDAP::getSyncFields($ldap_method);

            //Hook to allow plugin to request more attributes from ldap
            $fields = Plugin::doHookFunction("retrieve_more_field_from_ldap", $fields);

            $fields  = array_filter($fields);
            $f       = self::getLdapFieldNames($fields);

            $sr      = @ ldap_read($ldap_connection, $userdn, "objectClass=*", $f);
            $v       = AuthLDAP::get_entries_clean($ldap_connection, $sr);

            if (
                !is_array($v)
                || (count($v) == 0)
                || empty($v[0][$fields['name']][0])
            ) {
                return false;
            }

            //Store user's dn
            $this->fields['user_dn']    = addslashes($userdn);
            //Store date_sync
            $this->fields['date_sync']  = $_SESSION['glpi_currenttime'];
            // Empty array to ensure than syncDynamicEmails will be done
            $this->fields["_emails"]    = [];
            // force authtype as we retrieve this user by ldap (we could have login with SSO)
            $this->fields["authtype"] = Auth::LDAP;

            foreach ($fields as $k => $e) {
                $val = AuthLDAP::getFieldValue(
                    [$e => self::getLdapFieldValue($e, $v)],
                    $e
                );
                if (empty($val)) {
                    switch ($k) {
                        case "language":
                            // Not set value : managed but user class
                            break;

                        case "usertitles_id":
                        case "usercategories_id":
                        case 'locations_id':
                        case 'users_id_supervisor':
                            $this->fields[$k] = 0;
                            break;

                        default:
                            $this->fields[$k] = "";
                    }
                } else {
                    $val = Toolbox::addslashes_deep($val);
                    switch ($k) {
                        case "email1":
                        case "email2":
                        case "email3":
                        case "email4":
                            // Manage multivaluable fields
                            if (!empty($v[0][$e])) {
                                foreach ($v[0][$e] as $km => $m) {
                                    if (!preg_match('/count/', $km)) {
                                        $this->fields["_emails"][] = addslashes($m);
                                    }
                                }
                                // Only get them once if duplicated
                                $this->fields["_emails"] = array_unique($this->fields["_emails"]);
                            }
                            break;

                        case "language":
                            $language = Config::getLanguage($val);
                            if ($language != '') {
                                $this->fields[$k] = $language;
                            }
                            break;

                        case "usertitles_id":
                            $this->fields[$k] = Dropdown::importExternal('UserTitle', $val);
                            break;

                        case 'locations_id':
                            // use import to build the location tree
                            $this->fields[$k] = Dropdown::import(
                                'Location',
                                ['completename' => $val,
                                                                  'entities_id'  => 0,
                                                                  'is_recursive' => 1]
                            );
                            break;

                        case "usercategories_id":
                            $this->fields[$k] = Dropdown::importExternal('UserCategory', $val);
                            break;

                        case 'users_id_supervisor':
                            $this->fields[$k] = self::getIdByField('user_dn', $val, false);
                            break;

                        default:
                            $this->fields[$k] = $val;
                    }
                }
            }

            // Empty array to ensure than syncLdapGroups will be done
            $this->fields["_groups"] = [];

            ///The groups are retrieved by looking into an ldap user object
            if (
                ($ldap_method["group_search_type"] == 0)
                || ($ldap_method["group_search_type"] == 2)
            ) {
                $this->getFromLDAPGroupVirtual($ldap_connection, $ldap_method, $userdn, $login);
            }

            ///The groups are retrived by looking into an ldap group object
            if (
                ($ldap_method["group_search_type"] == 1)
                || ($ldap_method["group_search_type"] == 2)
            ) {
                $this->getFromLDAPGroupDiscret($ldap_connection, $ldap_method, $userdn, $login);
            }

            ///Only process rules if working on the master database
            if (!$DB->isSlave()) {
                //Instanciate the affectation's rule
                $rule = new RuleRightCollection();

                //Process affectation rules :
                //we don't care about the function's return because all
                //the datas are stored in session temporary
                if (isset($this->fields["_groups"])) {
                    $groups = $this->fields["_groups"];
                } else {
                    $groups = [];
                }

                $this->fields = $rule->processAllRules($groups, Toolbox::stripslashes_deep($this->fields), [
                   'type'        => Auth::LDAP,
                   'ldap_server' => $ldap_method["id"],
                   'connection'  => $ldap_connection,
                   'userdn'      => $userdn,
                   'login'       => $this->fields['name'],
                   'mail_email'  => $this->fields['_emails']
                ]);

                $this->fields['_ruleright_process'] = true;

                //If rule  action is ignore import
                if (
                    $import
                    && isset($this->fields["_stop_import"])
                ) {
                    return false;
                }
                //or no rights found & do not import users with no rights
                if (
                    $import
                    && !$CFG_GLPI["use_noright_users_add"]
                ) {
                    $ok = false;
                    if (
                        isset($this->fields["_ldap_rules"])
                        && count($this->fields["_ldap_rules"])
                    ) {
                        if (
                            isset($this->fields["_ldap_rules"]["rules_entities_rights"])
                            && count($this->fields["_ldap_rules"]["rules_entities_rights"])
                        ) {
                            $ok = true;
                        }
                        if (!$ok) {
                            $entity_count = 0;
                            $right_count  = 0;
                            if (Profile::getDefault()) {
                                $right_count++;
                            }
                            if (isset($this->fields["_ldap_rules"]["rules_entities"])) {
                                $entity_count += count($this->fields["_ldap_rules"]["rules_entities"]);
                            }
                            if (isset($this->input["_ldap_rules"]["rules_rights"])) {
                                $right_count += count($this->fields["_ldap_rules"]["rules_rights"]);
                            }
                            if ($entity_count && $right_count) {
                                $ok = true;
                            }
                        }
                    }
                    if (!$ok) {
                        $this->fields["_stop_import"] = true;
                        return false;
                    }
                }

                // Add ldap result to data send to the hook
                $this->fields['_ldap_result'] = $v;
                $this->fields['_ldap_conn']   = $ldap_connection;
                //Hook to retrieve more information for ldap
                $this->fields = Plugin::doHookFunction("retrieve_more_data_from_ldap", $this->fields);
                unset($this->fields['_ldap_result']);
            }
            return true;
        }
        return false;
    } // getFromLDAP()


    /**
     * Get all groups a user belongs to.
     *
     * @param resource $ds                 ldap connection
     * @param string   $ldap_base_dn       Basedn used
     * @param string   $user_dn            Basedn of the user
     * @param string   $group_condition    group search condition
     * @param string   $group_member_field group field member in a user object
     * @param boolean  $use_dn             search dn of user ($login_field=$user_dn) in group_member_field
     * @param string   $login_field        user login field
     *
     * @return array Groups of the user located in [0][$group_member_field] in returned array
     */
    public function ldap_get_user_groups(
        $ds,
        $ldap_base_dn,
        $user_dn,
        $group_condition,
        $group_member_field,
        $use_dn,
        $login_field
    ) {

        $groups     = [];
        $listgroups = [];

        //User dn may contain ( or ), need to espace it!
        $user_dn = str_replace(
            ["(", ")", "\,", "\+"],
            ["\(", "\)", "\\\,", "\\\+"],
            $user_dn
        );

        //Only retrive cn and member attributes from groups
        $attrs = ['dn'];

        if (!$use_dn) {
            $filter = "(& $group_condition (|($group_member_field=$user_dn)
                                          ($group_member_field=$login_field=$user_dn)))";
        } else {
            $filter = "(& $group_condition ($group_member_field=$user_dn))";
        }

        //Perform the search
        $filter = Toolbox::unclean_cross_side_scripting_deep($filter);
        $sr     = ldap_search($ds, $ldap_base_dn, $filter, $attrs);

        //Get the result of the search as an array
        $info = AuthLDAP::get_entries_clean($ds, $sr);
        //Browse all the groups
        $info_count = count($info);
        for ($i = 0; $i < $info_count; $i++) {
            //Get the cn of the group and add it to the list of groups
            if (isset($info[$i]["dn"]) && ($info[$i]["dn"] != '')) {
                $listgroups[$i] = $info[$i]["dn"];
            }
        }

        //Create an array with the list of groups of the user
        $groups[0][$group_member_field] = $listgroups;
        //Return the groups of the user
        return $groups;
    }


    /**
     * Function that tries to load the user information from IMAP.
     *
     * @param array  $mail_method  mail method description array
     * @param string $name         login of the user
     *
     * @return boolean true if method is applicable, false otherwise
     */
    public function getFromIMAP(array $mail_method, $name)
    {
        global $DB;

        // we prevent some delay..
        if (empty($mail_method["host"])) {
            return false;
        }

        // some defaults...
        $this->fields['password']  = "";
        // Empty array to ensure than syncDynamicEmails will be done
        $this->fields["_emails"]   = [];
        $email                     = '';
        if (strpos($name, "@")) {
            $email = $name;
        } else {
            $email = $name . "@" . $mail_method["host"];
        }
        $this->fields["_emails"][] = $email;

        $this->fields['name']      = $name;
        //Store date_sync
        $this->fields['date_sync'] = $_SESSION['glpi_currenttime'];
        // force authtype as we retrieve this user by imap (we could have login with SSO)
        $this->fields["authtype"] = Auth::MAIL;

        if (!$DB->isSlave()) {
            //Instanciate the affectation's rule
            $rule = new RuleRightCollection();

            //Process affectation rules :
            //we don't care about the function's return because all the datas are stored in session temporary
            if (isset($this->fields["_groups"])) {
                $groups = $this->fields["_groups"];
            } else {
                $groups = [];
            }
            $this->fields = $rule->processAllRules(
                $groups,
                Toolbox::stripslashes_deep($this->fields),
                [
                'type'        => Auth::MAIL,
                'mail_server' => $mail_method["id"],
                'login'       => $name,
                'email'       => $email]
            );
            $this->fields['_ruleright_process'] = true;
        }
        return true;
    }


    /**
     * Function that tries to load the user information from the SSO server.
     *
     * @since 0.84
     *
     * @return boolean true if method is applicable, false otherwise
     */
    public function getFromSSO()
    {
        global $DB, $CFG_GLPI;

        $a_field = [];
        foreach ($CFG_GLPI as $key => $value) {
            if (
                !is_array($value) && !empty($value)
                && strstr($key, "_ssofield")
            ) {
                $key = str_replace('_ssofield', '', $key);
                $a_field[$key] = $value;
            }
        }

        if (count($a_field) == 0) {
            return true;
        }
        $this->fields['_ruleright_process'] = true;
        foreach ($a_field as $field => $value) {
            if (
                !isset($_SERVER[$value])
                || empty($_SERVER[$value])
            ) {
                switch ($field) {
                    case "title":
                        $this->fields['usertitles_id'] = 0;
                        break;

                    case "category":
                        $this->fields['usercategories_id'] = 0;
                        break;

                    default:
                        $this->fields[$field] = "";
                }
            } else {
                switch ($field) {
                    case "email1":
                    case "email2":
                    case "email3":
                    case "email4":
                        // Manage multivaluable fields
                        if (!preg_match('/count/', $_SERVER[$value])) {
                            $this->fields["_emails"][] = addslashes($_SERVER[$value]);
                        }
                        // Only get them once if duplicated
                        $this->fields["_emails"] = array_unique($this->fields["_emails"]);
                        break;

                    case "language":
                        $language = Config::getLanguage($_SERVER[$value]);
                        if ($language != '') {
                            $this->fields[$field] = $language;
                        }
                        break;

                    case "title":
                        $this->fields['usertitles_id']
                              = Dropdown::importExternal('UserTitle', addslashes($_SERVER[$value]));
                        break;

                    case "category":
                        $this->fields['usercategories_id']
                              = Dropdown::importExternal('UserCategory', addslashes($_SERVER[$value]));
                        break;

                    default:
                        $this->fields[$field] = $_SERVER[$value];
                        break;
                }
            }
        }
        ///Only process rules if working on the master database
        if (!$DB->isSlave()) {
            //Instanciate the affectation's rule
            $rule = new RuleRightCollection();

            $this->fields = $rule->processAllRules([], Toolbox::stripslashes_deep($this->fields), [
               'type'   => Auth::EXTERNAL,
               'email'  => $this->fields["_emails"],
               'login'  => $this->fields["name"]
            ]);

            //If rule  action is ignore import
            if (isset($this->fields["_stop_import"])) {
                return false;
            }
        }
        return true;
    }


    /**
     * Blank passwords field of a user in the DB.
     * Needed for external auth users.
     *
     * @return void
     */
    public function blankPassword()
    {
        global $DB;

        if (!empty($this->fields["name"])) {
            $DB->update(
                $this->getTable(),
                [
                  'password' => ''
                ],
                [
                  'name' => $this->fields['name']
                ]
            );
        }
    }


    /**
     * Print a good title for user pages.
     *
     * @return void
     */
    public function title()
    {
        global $CFG_GLPI;

        $buttons = [];
        $title   = self::getTypeName(Session::getPluralNumber());

        if (static::canCreate()) {
            $buttons["user.form.php"] = __('Add user...');
            $title                    = "";

            if (
                Auth::useAuthExt()
                && Session::haveRight("user", self::IMPORTEXTAUTHUSERS)
            ) {
                // This requires write access because don't use entity config.
                $buttons["user.form.php?new=1&amp;ext_auth=1"] = __('... From an external source');
            }
        }
        if (
            Session::haveRight("user", self::IMPORTEXTAUTHUSERS)
            && (static::canCreate() || static::canUpdate())
        ) {
            if (AuthLDAP::useAuthLdap()) {
                $buttons["ldap.php"] = __('LDAP directory link');
            }
        }
        Html::displayTitle(
            $CFG_GLPI["root_doc"] . "/pics/users.png",
            self::getTypeName(Session::getPluralNumber()),
            $title,
            $buttons
        );
    }


    /**
     * Check if current user have more right than the specified one.
     *
     * @param integer $ID ID of the user
     *
     * @return boolean
     */
    public function currentUserHaveMoreRightThan($ID)
    {

        $user_prof = Profile_User::getUserProfiles($ID);
        return Profile::currentUserHaveMoreRightThan($user_prof);
    }


    /**
     * Print the user form.
     *
     * @param integer $ID    ID of the user
     * @param array $options Options
     *     - string   target        Form target
     *     - boolean  withtemplate  Template or basic item
     *
     * @return boolean true if user found, false otherwise
     */
    public function showForm($ID, array $options = [])
    {
        global $CFG_GLPI, $DB;

        // Affiche un formulaire User
        if (($ID != Session::getLoginUserID()) && !self::canView()) {
            return false;
        }

        $this->initForm($ID, $options);

        $ismyself = $ID == Session::getLoginUserID();
        $higherrights = $this->currentUserHaveMoreRightThan($ID);
        if ($ID) {
            $caneditpassword = $higherrights || ($ismyself && Session::haveRight('password_update', 1));
        } else {
            // can edit on creation form
            $caneditpassword = true;
        }

        $extauth = !(($this->fields["authtype"] == Auth::DB_GLPI)
                     || (($this->fields["authtype"] == Auth::NOT_YET_AUTHENTIFIED)
                         && !empty($this->fields["password"])));

        $formtitle = $this->getTypeName(1);

        if ($ID > 0) {
            $formtitle .= "&nbsp;<a class='pointer far fa-address-card fa-lg' target='_blank' href='" .
                          User::getFormURLWithID($ID) . "&amp;getvcard=1' title='" . __s('Download user VCard') .
                          "'><span class='sr-only'>" . __('Vcard') . "</span></a>";
            if (Session::canImpersonate($ID)) {
                $formtitle .= '<button type="button" class="pointer btn-linkstyled btn-impersonate" aria-label="Impersonate" name="impersonate" value="1">'
                   . '<i class="fas fa-user-secret fa-lg" title="' . __s('Impersonate') . '"></i> '
                   . '<span class="sr-only">' . __s('Impersonate') . '</span>'
                   . '</button>';

                // "impersonate" button type is set to "button" on form display to prevent it to be used
                // by default (as it is the first found in current form) when pressing "enter" key.
                // When clicking it, switch to "submit" type to make it submit current user form.
                $impersonate_js = <<<JS
               (function($) {
                  $('button[type="button"][name="impersonate"]').click(
                     function () {
                        $(this).attr('type', 'submit');
                     }
                  );
               })(jQuery);
            JS;
                $formtitle .= Html::scriptBlock($impersonate_js);
            }
        }

        $tz_warning = '';
        $tz_available = $DB->areTimezonesAvailable($tz_warning);
        if ($tz_available) {
            $timezones = $DB->getTimezones();
        }

        $emails = iterator_to_array($DB->request([
           'SELECT' => [
              'id',
              'is_default',
              'email',
           ],
           'FROM'   => 'glpi_useremails',
           'WHERE'  => ['users_id' => $ID]
        ]));
        $emailsValues = [];
        $defaultEmailTitle = __('Default email');
        foreach ($emails as $email) {
            $emailsValues[] = [
               "<input type='radio' class='form-check mx-1' title='{$defaultEmailTitle}' name='_default_email' value='" . $email['id'] . "' " . ($email['is_default'] ? 'checked' : '') . ">",
               "<input type='email' class='form-control' name='_useremails[{$email['id']}]' value='" . $email['email'] . "'>",
            ];
        }
        $groupUser = [];
        foreach (Group_User::getUserGroups($this->fields['id']) as $group) {
            $groupUser[$group['id']] = $group['completename'];
        }

        $profileUser = [];
        foreach (Profile_User::getUserProfiles($this->fields['id']) as $profile) {
            $profileTmp = new Profile();
            $profileTmp->getFromDB($profile);
            $profileUser[$profile] = $profileTmp->fields['name'];
        }

        $entityUser = [];
        foreach (Profile_User::getUserEntities($this->fields['id']) as $entity) {
            $entityTmp = new Entity();
            $entityTmp->getFromDB($entity);
            $entityUser[$entity] = $entityTmp->fields['completename'];
        }

        $form = [
           'action' => $this->getFormURL(),
           'itemtype' => self::class,
           'content' => [
             $formtitle => [
                'visible' => true,
                'inputs' => [
                   empty($ID) => [
                      'type' => 'hidden',
                      'name' => 'authtype',
                      'value' => 1,
                   ],
                   __('Login') => [
                      'type' => ($this->fields["name"] == ""
                      || !empty($this->fields["password"])
                      || ($this->fields["authtype"] == Auth::DB_GLPI))
                      ? 'text'
                      : 'hidden',
                      'name' => 'name',
                      'value' => $this->fields['name'],
                   ],
                   $this->isNewID($ID) ? [] : [
                      'type' => 'hidden',
                      'name' => 'id',
                      'value' => $ID,
                   ],
                   __('Synchronization field') => ($extauth
                   && $this->fields['auths_id']
                   && AuthLDAP::isSyncFieldConfigured($this->fields['auths_id']))
                   ? [
                      'type' => 'text',
                         'name' => 'sync_field',
                         'value' => $this->fields['sync_field'],
                         (self::canUpdate() && (!$extauth || empty($ID))) ? 'disabled' : '',
                   ] : [],
                   __('Surname') => [
                      'type' => 'text',
                      'name' => 'realname',
                      'value' => $this->fields['realname'],
                   ],
                   __('First name') => [
                      'type' => 'text',
                      'name' => 'firstname',
                      'value' => $this->fields['firstname'],
                   ],
                   __('Password') => (self::canUpdate()
                      && (!$extauth || empty($ID))
                      && $caneditpassword)
                         ? [
                            'type' => 'password',
                            'name' => 'password',
                            'value' => '',
                            'size' => '20',
                            'col_lg' => 6,
                   ] : [],
                   __('Password confirmation') => (self::canUpdate()
                      && (!$extauth || empty($ID))
                      && $caneditpassword)
                         ? [
                            'type' => 'password',
                            'name' => 'password2',
                            'value' => '',
                            'size' => '20',
                            'col_lg' => 6,
                   ] : [],
                   __('Time zone') => ($tz_available || Session::haveRight("config", READ)) ? ($tz_available ? [
                      'type' => 'select',
                      'name' => 'timezone',
                      'values' => array_merge([__('Use server configuration')], $timezones),
                      'value' => $this->fields["timezone"],
                   ] : [
                      'content' => "<img src=\"{$CFG_GLPI['root_doc']}/pics/warning_min.png\">"
                   ]) : [],
                   __('Active') => (!GLPI_DEMO_MODE) ? [
                      'type' => 'checkbox',
                      'name' => 'is_active',
                      'value' => $this->fields['is_active'],
                   ] : [],
                   _n('Email', 'Emails', Session::getPluralNumber()) => [
                      'type' => 'multiSelect',
                      'name' => '_useremails',
                      'inputs' => [
                         [
                            'name' => 'current_useremails',
                            'type' => 'email',
                         ]
                      ],
                      'values' => $emailsValues,
                      'getInputAdd' => <<<JS
                    function () {
                       let re = /\S+@\S+\.\S+/;
                       if (!$('input[name="current_useremails"]').val() || !re.test($('input[name="current_useremails"]').val())) {
                          return;
                       }
                       var values = {};
                       var title = "<input type='radio' class='form-check-input mx-1' title='{$defaultEmailTitle}' name='_default_email' value='-1'>" +
                                   "<input type='email' class='form-control' name='_useremails[-1]' value='" + $('input[name="current_useremails"]').val() + "'>";
                       return {values, title};
                    }
                    JS,
                   ],
                   __('Valid since') => (!GLPI_DEMO_MODE) ? [
                      'type' => 'datetime-local',
                      'name' => 'begin_date',
                      'id' => 'BeginDatePicker',
                      'value' => $this->fields['begin_date'],
                      'col_lg' => 6,
                   ] : [],
                   __('Valid until') => (!GLPI_DEMO_MODE) ? [
                      'type' => 'datetime-local',
                      'name' => 'end_date',
                      'id' => 'EndDatePicker',
                      'value' => $this->fields['end_date'],
                      'col_lg' => 6,
                   ] : [],
                   Phone::getTypeName(1) => [
                      'type' => 'text',
                      'name' => 'phone',
                      'value' => $this->fields['phone'],
                   ],
                   __('Phone 2') => [
                      'type' => 'text',
                      'name' => 'phone2',
                      'value' => $this->fields['phone2'],
                   ],
                   __('Mobile phone') => [
                      'type' => 'text',
                      'name' => 'mobile',
                      'value' => $this->fields['mobile'],
                   ],
                   __('Authentication') => (!empty($ID)
                      && Session::haveRight(self::$rightname, self::READAUTHENT)) ? [
                         'content' => Auth::getMethodName($this->fields["authtype"], $this->fields["auths_id"]),
                   ] : [],
                   __('Last synchronization') => (!empty($ID)
                      && Session::haveRight(self::$rightname, self::READAUTHENT
                      && !empty($this->fields["date_sync"]))) ? [
                         'content' => Html::convDateTime($this->fields["date_sync"]),
                   ] : [],
                   __('User DN') => (!empty($ID)
                      && Session::haveRight(self::$rightname, self::READAUTHENT
                      && !empty($this->fields["user_dn"]))) ? [
                         'content' => $this->fields["user_dn"],
                   ] : [],
                   __('LDAP Directory') => (!empty($ID)
                      && Session::haveRight(self::$rightname, self::READAUTHENT
                      && $this->fields['is_deleted_ldap'])) ? [
                         'content' => 'MISSING',
                   ] : [],
                   __('Administrative number') => [
                      'type' => 'text',
                      'name' => 'registration_number',
                      'value' => $this->fields['registration_number'],
                   ],
                   _x('person', 'Title') => [
                      'type' => 'select',
                      'name' => 'usertitles_id',
                      'values' => getOptionForItems('UserTitle'),
                      'value' => $this->fields['usertitles_id'],
                      'actions' => getItemActionButtons(['info', 'add'], 'UserTitle'),
                   ],
                   Location::getTypeName(1) => (!empty($ID)) ? [
                      'type' => 'select',
                      'name' => 'locations_id',
                      'itemtype' => Location::class,
                      'value' => $this->fields['locations_id'],
                      'actions' => getItemActionButtons(['info', 'add'], 'Location'),
                   ] : [],
                ]
             ],
             _n('Authorization', 'Authorizations', 1) =>  [
                'visible' => true,
                'inputs' => ($this->isNewID($ID)) ? [
                   Profile::getTypeName(1) => [
                      'type' => 'select',
                      'name' => '_profiles_id',
                      'values' => getOptionForItems('Profile'),
                      'value' => Profile::getDefault(),
                      'actions' => getItemActionButtons(['info', 'add'], 'Profile'),
                   ],
                   Entity::getTypeName(1) => [
                      'type' => 'select',
                      'name' => '_entities_id',
                      'values' => getOptionForItems('Entity'),
                      'actions' => getItemActionButtons(['info', 'add'], 'Entity'),
                   ],
                   __('Recursive') => [
                      'type' => 'checkbox',
                      'name' => '_is_recursive',
                      'value' => 0,
                   ],
                ] : [
                   __('Default profile') => ($higherrights || $ismyself) ? [
                      'type' => 'select',
                      'name' => 'profiles_id',
                      'values' => [Dropdown::EMPTY_VALUE] + $profileUser,
                      'value' => $this->fields['profiles_id'],
                      'col_lg' => 6,
                   ] : [],
                   __('Default entity') => ($higherrights) ? [
                      'type' => 'select',
                      'name' => 'entities_id',
                      'values' => [-1 => Dropdown::EMPTY_VALUE] + $entityUser,
                      'value' => $this->fields['entities_id'],
                      'col_lg' => 6,
                      ] : [],
                   __('Default group') => ($higherrights) ? [
                      'type' => 'select',
                      'name' => 'groups_id',
                      'values' => [Dropdown::EMPTY_VALUE] + $groupUser,
                      'value' => $this->fields['groups_id'],
                      'col_lg' => 6,
                   ] : [],
                   __('Responsible') => ($higherrights) ? [
                      'type' => 'select',
                      'name' => 'users_id_supervisor',
                      'values' => getOptionsForUsers('all'),
                      'value' => $this->fields['users_id_supervisor'],
                      'col_lg' => 6,
                   ] : [],
                ]
             ],
             __('Remote access keys') => ($caneditpassword && !empty($ID)) ? [
                'visible' => true,
                'inputs' => [
                   __("Personal token") => (!empty($this->fields["personal_token"])) ? [
                      'type' => 'text',
                      'name' => '_personal_token',
                      'value' => $this->fields["personal_token"],
                      'after' => sprintf(
                          __('generated on %s'),
                          Html::convDateTime($this->fields["personal_token_date"])
                      ),
                      'col_lg' => 8,
                      'col_md' => 8,
                   ] : [
                      'content' => '',
                      'col_lg' => 8,
                      'col_md' => 8,
                   ],
                   __('Regenerate') . ' ' . __("Personal token") => [
                      'type' => 'checkbox',
                      'name' => '_reset_personal_token',
                      'value' => '',
                      'col_lg' => 4,
                      'col_md' => 4,
                   ],
                   __("API token") => (!empty($this->fields["api_token"])) ? [
                      'type' => 'text',
                      'name' => '_api_token',
                      'value' => $this->fields["api_token"],
                      'after' => sprintf(
                          __('generated on %s'),
                          Html::convDateTime($this->fields["api_token_date"])
                      ),
                      'col_lg' => 8,
                      'col_md' => 8,
                   ] : [
                      'content' => '',
                      'col_lg' => 8,
                      'col_md' => 8,
                   ],
                   __('Regenerate') . ' ' . __("API token") => [
                      'type' => 'checkbox',
                      'name' => '_reset_api_token',
                      'value' => '',
                      'col_lg' => 4,
                      'col_md' => 4,
                   ],
                ]
             ] : []
           ]
        ];
        renderTwigForm($form, '', $this->fields + ['noEntity' => true]);
        return true;
    }


    /** Print the user personnal information for check.
     *
     * @param integer $userid ID of the user
     *
     * @return void|boolean false if user is not the current user, otherwise print form
     *
     * @since 0.84
     */
    public static function showPersonalInformation($userid)
    {
        global $CFG_GLPI;

        $user = new self();
        if (
            !$user->can($userid, READ)
            && ($userid != Session::getLoginUserID())
        ) {
            return false;
        }
        echo "<table class='tab_glpi left' width='100%' aria-label='Personnal information'>";
        echo "<tr class='tab_bg_1'>";
        echo "<td class='b' width='20%'>";
        echo __('Name');
        echo "</td><td width='30%'>";
        echo getUserName($userid);
        echo "</td>";
        echo "<td class='b'  width='20%'>";
        echo Phone::getTypeName(1);
        echo "</td><td width='30%'>";
        echo $user->getField('phone');
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td class='b'>";
        echo __('Phone 2');
        echo "</td><td>";
        echo $user->getField('phone2');
        echo "</td>";
        echo "<td class='b'>";
        echo __('Mobile phone');
        echo "</td><td>";
        echo $user->getField('mobile');
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td class='b'>";
        echo _n('Email', 'Emails', 1);
        echo "</td><td>";
        echo $user->getDefaultEmail();
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td class='b'>";
        echo Location::getTypeName(1);
        echo "</td><td>";
        echo Dropdown::getDropdownName('glpi_locations', $user->getField('locations_id'));
        echo "</td>";
        echo "<td colspan='2' class='center'>";
        if ($userid == Session::getLoginUserID()) {
            echo "<a href='" . $CFG_GLPI['root_doc'] . "/front/preference.php' class='vsubmit'>" .
                  __('Edit') . "</a>";
        } else {
            echo "&nbsp;";
        }
        echo "</td>";
        echo "</tr>";
        echo "</table>";
    }


    /**
     * Print the user preference form.
     *
     * @param string  $target Form target
     * @param integer $ID     ID of the user
     *
     * @return boolean true if user found, false otherwise
     */
    public function showMyForm($target, $ID)
    {
        global $CFG_GLPI, $DB;

        $user_id = Session::getLoginUserID();

        // Affiche un formulaire User
        if (
            ($ID != Session::getLoginUserID())
            && !$this->currentUserHaveMoreRightThan($ID)
        ) {
            return false;
        }
        if ($this->getFromDB($ID)) {
            $extauth  = !(($this->fields["authtype"] == Auth::DB_GLPI)
                          || (($this->fields["authtype"] == Auth::NOT_YET_AUTHENTIFIED)
                              && !empty($this->fields["password"])));

            // Get all available profile of user
            $query = "SELECT DISTINCT `glpi_profiles`.`id`, `glpi_profiles`.`name`
                   FROM `glpi_profiles`
                   JOIN `glpi_profiles_users`
                     ON (`glpi_profiles_users`.`profiles_id` = `glpi_profiles`.`id`)
                   WHERE `glpi_profiles_users`.`users_id` = '$user_id'
                   ORDER BY `glpi_profiles`.`id`";
            $result = $DB->query($query);
            // Initialize an empty array to store the results
            $User_profile = array();

            // Fetch all rows
            while ($row = $DB->fetchRow($result)) {
                // Use the 'id' column as the key and 'name' column as the value
                $User_profile[$row[0]] = $row[1];
            }

            $emails = iterator_to_array($DB->request([
               'SELECT' => [
                  'id',
                  'is_default',
                  'email',
               ],
               'FROM'   => 'glpi_useremails',
               'WHERE'  => ['users_id' => $ID]
            ]));
            $emailsValues = [];
            $defaultEmailTitle = __('Default email');
            foreach ($emails as $email) {
                $emailsValues[] = [
                   "<input type='radio' class='mx-1' title='{$defaultEmailTitle}' name='_default_email' value='" . $email['id'] . "' " . ($email['is_default'] ? 'checked' : '') . ">",
                   "<input type='email' class='form-control' name='_useremails[{$email['id']}]' value='" . $email['email'] . "'>",
                ];
            }

            $tz_warning = '';
            $tz_available = $DB->areTimezonesAvailable($tz_warning);
            if ($tz_available) {
                $timezones = $DB->getTimezones();
            }


            // Display the result
            $form = [
               'action' => $CFG_GLPI['root_doc'] . '/front/preference.php',
               'buttons' => [
                  [
                     'type' => 'submit',
                     'name' => 'update',
                     'value' => __('Update'),
                     'class' => 'btn btn-secondary',
                  ]
               ],
               'content' => [
                  __('Login') . ' : ' . $this->fields["name"] => [
                     'visible' => true,
                     'inputs' => [
                        $this->isNewID($ID) ? [] : [
                           'type' => 'hidden',
                           'name' => 'id',
                           'value' => $ID
                        ],
                        __('Surname') => [
                           'name' => 'realname',
                           'type' => 'text',
                           'value' => $this->fields['realname'] ?? '',
                        ],
                        __('First name') => [
                           'name' => 'firstname',
                           'type' => 'text',
                           'value' => $this->fields['firstname'] ?? '',
                        ],
                        __('Picture') => [
                           'id' => 'pictureFilePicker',
                           'name' => 'picture',
                           'type' => 'imageUpload',
                           'accept' => 'image/*',
                           'value' => $this->fields['picture'] ?? '',
                        ],
                        __('Language') => [
                           'name' => 'language',
                           'type' => 'select',
                           'values' => Language::getLanguages(),
                           'value' => $this->fields['language'] ?? Session::getPreferredLanguage(),
                        ],
                        __('Password') => (!$extauth && Session::haveRight("password_update", "1")) ? [
                           'name' => 'password',
                           'type' => 'password'
                        ] : [],
                        __('Password confirmation') => (!$extauth && Session::haveRight("password_update", "1")) ? [
                           'name' => 'password2',
                           'type' => 'password',
                        ] : [],
                        __('Time zone') => ($tz_available || Session::haveRight("config", READ)) ? ($tz_available ? [
                           'type' => 'select',
                           'name' => 'timezone',
                           'values' => array_merge([__('Use server configuration')], $timezones),
                           'value' => $this->fields["timezone"],
                        ] : [
                           'content' => "<img src=\"{$CFG_GLPI['root_doc']}/pics/warning_min.png\">"
                        ]) : [],
                        __('Phone') => [
                           'name' => 'phone',
                           'type' => 'text',
                           'value' => $this->fields['phone'] ?? '',
                        ],
                        __('Phone 2') => [
                           'name' => 'phone2',
                           'type' => 'text',
                           'value' => $this->fields['phone2'] ?? '',
                        ],
                        __('Mobile phone') => [
                           'name' => 'mobile',
                           'type' => 'text',
                           'value' => $this->fields['mobile'] ?? '',
                        ],
                        __('Administrative number') => [
                           'name' => 'registration_number',
                           'type' => 'text',
                           'value' => $this->fields['registration_number'] ?? '',
                        ],
                        __('Location') => [
                           'name' => 'locations_id',
                           'type' => 'select',
                           'itemtype' => Location::class,
                           'value' => $this->fields['locations_id'] ?? '',
                           'actions' => getItemActionButtons(['info', 'add'], "Location"),
                        ],
                        _n('Email', 'Emails', Session::getPluralNumber()) => [
                           'type' => 'multiSelect',
                           'name' => '_useremails',
                           'inputs' => [
                              [
                                 'name' => 'current_useremails',
                                 'type' => 'email',
                              ]
                           ],
                           'values' => $emailsValues,
                           'getInputAdd' => <<<JS
                        function () {
                           let re = /\S+@\S+\.\S+/;
                           if (!$('input[name="current_useremails"]').val() || !re.test($('input[name="current_useremails"]').val())) {
                              return;
                           }
                           var values = {};
                           var title = "<input type='radio' class='form-check-input mx-1' title='{$defaultEmailTitle}' name='_default_email' value='-1'>" +
                                       "<input type='email' class='form-control' name='_useremails[-1]' value='" + $('input[name="current_useremails"]').val() + "'>";
                           return {values, title};
                        }
                        JS,
                        ],
                        __('Default profile') => [
                           'name' => 'profiles_id',
                           'type' => 'select',
                           'values' => $User_profile,
                           'value' => $this->fields['profiles_id'] ?? '',
                        ],
                        __('Use ITSM-NG in mode') => (Session::haveRight("config", "1")) ? [
                           'name' => 'use_mode',
                           'type' => 'select',
                           'values' => [
                              Session::NORMAL_MODE => __('Normal'),
                              Session::DEBUG_MODE  => __('Debug'),
                           ],
                           'value' => $this->fields['use_mode'] ?? '',
                        ] : [],
                     ]
                  ],
                  __('Remote access keys') => [
                     'visible' => true,
                     'inputs' => [
                        __('Personal token') => [
                           'name' => '_personal_token',
                           'type' => 'text',
                           'value' => $this->fields['personal_token'] ?? '',
                           'disabled' => true,
                        ],
                        __('Reset personal token') => [
                           'name' => '_reset_personal_token',
                           'type' => 'checkbox',
                        ],
                        '' => [
                           'name' => '',
                           'type' => '',
                           'value' => '<br>',
                        ],
                        __('API token') => [
                           'name' => '_api_token',
                           'type' => 'text',
                           'value' => $this->fields['api_token'] ?? '',
                           'disabled' => true,
                        ],
                        __('Reset API token') => [
                           'name' => '_reset_api_token',
                           'type' => 'checkbox',
                        ],
                     ]
                  ]
               ]
            ];
        }
        renderTwigForm($form);
        return true;
    }



    /**
     * Get all the authentication method parameters for the current user.
     *
     * @return array
     */
    public function getAuthMethodsByID()
    {
        return Auth::getMethodsByID($this->fields["authtype"], $this->fields["auths_id"]);
    }


    public function pre_updateInDB()
    {
        global $DB;

        if (($key = array_search('name', $this->updates)) !== false) {
            /// Check if user does not exists
            $iterator = $DB->request([
               'FROM'   => $this->getTable(),
               'WHERE'  => [
                  'name'   => $this->input['name'],
                  'id'     => ['<>', $this->input['id']]
               ]
            ]);

            if (count($iterator)) {
                //To display a message
                $this->fields['name'] = $this->oldvalues['name'];
                unset($this->updates[$key]);
                unset($this->oldvalues['name']);
                Session::addMessageAfterRedirect(
                    __('Unable to update login. A user already exists.'),
                    false,
                    ERROR
                );
            }

            if (!Auth::isValidLogin(stripslashes($this->input['name']))) {
                $this->fields['name'] = $this->oldvalues['name'];
                unset($this->updates[$key]);
                unset($this->oldvalues['name']);
                Session::addMessageAfterRedirect(
                    __('The login is not valid. Unable to update login.'),
                    false,
                    ERROR
                );
            }
        }

        // ## Security system except for login update:
        //
        // An **external** (ldap, mail) user without User::UPDATE right
        // should not be able to update its own fields
        // (for example, fields concerned by ldap synchronisation)
        // except on login action (which triggers synchronisation).
        if (
            Session::getLoginUserID() === (int)$this->input['id']
            && !Session::haveRight("user", UPDATE)
            && !strpos($_SERVER['PHP_SELF'], "/front/login.php")
            && isset($this->fields["authtype"])
        ) {
            // extauth ldap case
            if (
                $_SESSION["glpiextauth"]
                && ($this->fields["authtype"] == Auth::LDAP
                    || Auth::isAlternateAuth($this->fields["authtype"]))
            ) {
                $authtype = Auth::getMethodsByID(
                    $this->fields["authtype"],
                    $this->fields["auths_id"]
                );
                if (count($authtype)) {
                    $fields = AuthLDAP::getSyncFields($authtype);
                    foreach ($fields as $key => $val) {
                        if (
                            !empty($val)
                              && (($key2 = array_search($key, $this->updates)) !== false)
                        ) {
                            unset($this->updates[$key2]);
                            unset($this->oldvalues[$key]);
                        }
                    }
                }
            }

            if (($key = array_search("is_active", $this->updates)) !== false) {
                unset($this->updates[$key]);
                unset($this->oldvalues['is_active']);
            }

            if (($key = array_search("comment", $this->updates)) !== false) {
                unset($this->updates[$key]);
                unset($this->oldvalues['comment']);
            }
        }
    }

    public function getSpecificMassiveActions($checkitem = null)
    {

        $isadmin = static::canUpdate();
        $actions = parent::getSpecificMassiveActions($checkitem);
        if ($isadmin) {
            $actions['Group_User' . MassiveAction::CLASS_ACTION_SEPARATOR . 'add']
                                                            = "<i class='ma-icon fas fa-users' aria-hidden='true'></i>" .
                                                              __('Associate to a group');
            $actions['Group_User' . MassiveAction::CLASS_ACTION_SEPARATOR . 'remove']
                                                            = __('Dissociate from a group');
            $actions['Profile_User' . MassiveAction::CLASS_ACTION_SEPARATOR . 'add']
                                                            = "<i class='ma-icon fas fa-user-shield' aria-hidden='true'></i>" .
                                                              __('Associate to a profile');
            $actions['Profile_User' . MassiveAction::CLASS_ACTION_SEPARATOR . 'remove']
                                                            = __('Dissociate from a profile');
            $actions['Group_User' . MassiveAction::CLASS_ACTION_SEPARATOR . 'change_group_user']
                                                            = "<i class='ma-icon fas fa-users-cog' aria-hidden='true'></i>" .
                                                              __("Move to group");
            $actions[__CLASS__.MassiveAction::CLASS_ACTION_SEPARATOR.'change_timezone']
                                                            = "<i class='ma-icon fas fa-clock'></i>".
                                                              __('Change the time zone');
        }

        if (Session::haveRight(self::$rightname, self::UPDATEAUTHENT)) {
            $prefix                                    = __CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR;
            $actions[$prefix . 'change_authtype']        = "<i class='ma-icon fas fa-user-cog' aria-hidden='true'></i>" .
                                                         _x('button', 'Change the authentication method');
            $actions[$prefix . 'force_user_ldap_update'] = "<i class='ma-icon fas fa-sync' aria-hidden='true'></i>" .
                                                         __('Force synchronization');
        }
        return $actions;
    }

    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        global $CFG_GLPI, $DB;

        switch ($ma->getAction()) {
            case 'change_authtype':
                $rand             = Auth::dropdown(['name' => 'authtype']);
                $paramsmassaction = ['authtype' => '__VALUE__'];
                Ajax::updateItemOnSelectEvent(
                    "dropdown_authtype$rand",
                    "show_massiveaction_field",
                    $CFG_GLPI["root_doc"] .
                                                 "/ajax/dropdownMassiveActionAuthMethods.php",
                    $paramsmassaction
                );
                echo "<span id='show_massiveaction_field'><br><br>";
                echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']) . "</span>";
                return true;
            case 'change_timezone':
                $tz_warning = '';
                $tz_available = $DB->areTimezonesAvailable($tz_warning);
                if ($tz_available) {
                    $timezones = $DB->getTimezones();
                    $timezones[null] = __('Use server configuration');
                    Dropdown::showFromArray('timezone', $timezones, [
                        'display_emptychoice' => true,
                        'emptylabel'          => __('Use server configuration')
                    ]);
                    echo "<br><br>";
                    echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                    return true;
                }
                echo "<div class='error'>";
                echo ($tz_warning == '') ? __('No time zones are available. Please check your database configuration.') : $tz_warning;
                echo "</div>";
                return false;
        }
        return parent::showMassiveActionsSubForm($ma);
    }

    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $item,
        array $ids
    ) {

        switch ($ma->getAction()) {
            case 'force_user_ldap_update':
                foreach ($ids as $id) {
                    if ($item->can($id, UPDATE)) {
                        if (
                            ($item->fields["authtype"] == Auth::LDAP)
                            || ($item->fields["authtype"] == Auth::EXTERNAL)
                        ) {
                            if (AuthLDAP::forceOneUserSynchronization($item, false)) {
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                            } else {
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                                $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                            }
                        } else {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                            $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                        }
                    } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_NORIGHT);
                        $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
                    }
                }
                return;

            case 'change_authtype':
                $input = $ma->getInput();
                if (
                    !isset($input["authtype"])
                    || !isset($input["auths_id"])
                ) {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                    $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                    return;
                }
                if (Session::haveRight(self::$rightname, self::UPDATEAUTHENT)) {
                    if (User::changeAuthMethod($ids, $input["authtype"], $input["auths_id"])) {
                        $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_OK);
                    } else {
                        $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                    }
                } else {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_NORIGHT);
                    $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
                }
                return;

            case 'change_timezone':
                $input = $ma->getInput();
                if (!isset($input["timezone"])) {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                    $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                    return;
                }

                $timezone = $input["timezone"];
                // Convert empty string to NULL for database storage
                if ($timezone === '') {
                    $timezone = 'NULL';
                }

                foreach ($ids as $id) {
                    if ($item->can($id, UPDATE)) {
                        // Update the user's timezone
                        if ($item->update(['id' => $id, 'timezone' => $timezone])) {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                        } else {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                            $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                        }
                    } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_NORIGHT);
                        $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
                    }
                }
                return;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }


    public function rawSearchOptions()
    {
        // forcegroup by on name set force group by for all items
        $tab = [];

        $tab[] = [
           'id'                 => 'common',
           'name'               => __('Characteristics')
        ];

        $tab[] = [
           'id'                 => '1',
           'table'              => $this->getTable(),
           'field'              => 'name',
           'name'               => __('Login'),
           'datatype'           => 'itemlink',
           'forcegroupby'       => true,
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '2',
           'table'              => $this->getTable(),
           'field'              => 'id',
           'name'               => __('ID'),
           'massiveaction'      => false,
           'datatype'           => 'number'
        ];

        $tab[] = [
           'id'                 => '34',
           'table'              => $this->getTable(),
           'field'              => 'realname',
           'name'               => __('Last name'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '9',
           'table'              => $this->getTable(),
           'field'              => 'firstname',
           'name'               => __('First name'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '5',
           'table'              => 'glpi_useremails',
           'field'              => 'email',
           'name'               => _n('Email', 'Emails', Session::getPluralNumber()),
           'datatype'           => 'email',
           'joinparams'         => [
              'jointype'           => 'child'
           ],
           'forcegroupby'       => true,
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '150',
           'table'              => $this->getTable(),
           'field'              => 'picture',
           'name'               => __('Picture'),
           'datatype'           => 'specific',
           'nosearch'           => true,
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '28',
           'table'              => $this->getTable(),
           'field'              => 'sync_field',
           'name'               => __('Synchronization field'),
           'massiveaction'      => false,
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

        $tab[] = [
           'id'                 => '8',
           'table'              => $this->getTable(),
           'field'              => 'is_active',
           'name'               => __('Active'),
           'datatype'           => 'bool'
        ];

        $tab[] = [
           'id'                 => '6',
           'table'              => $this->getTable(),
           'field'              => 'phone',
           'name'               => Phone::getTypeName(1),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '10',
           'table'              => $this->getTable(),
           'field'              => 'phone2',
           'name'               => __('Phone 2'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '11',
           'table'              => $this->getTable(),
           'field'              => 'mobile',
           'name'               => __('Mobile phone'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '13',
           'table'              => 'glpi_groups',
           'field'              => 'completename',
           'name'               => Group::getTypeName(Session::getPluralNumber()),
           'forcegroupby'       => true,
           'datatype'           => 'itemlink',
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_groups_users',
                 'joinparams'         => [
                    'jointype'           => 'child'
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '14',
           'table'              => $this->getTable(),
           'field'              => 'last_login',
           'name'               => __('Last login'),
           'datatype'           => 'datetime',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '15',
           'table'              => $this->getTable(),
           'field'              => 'authtype',
           'name'               => __('Authentication'),
           'massiveaction'      => false,
           'datatype'           => 'specific',
           'searchtype'         => 'equals',
           'additionalfields'   => [
              '0'                  => 'auths_id'
           ]
        ];

        $tab[] = [
           'id'                 => '30',
           'table'              => 'glpi_authldaps',
           'field'              => 'name',
           'linkfield'          => 'auths_id',
           'name'               => __('LDAP directory for authentication'),
           'massiveaction'      => false,
           'joinparams'         => [
               'condition'          => 'AND REFTABLE.`authtype` = ' . Auth::LDAP
           ],
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '31',
           'table'              => 'glpi_authmails',
           'field'              => 'name',
           'linkfield'          => 'auths_id',
           'name'               => __('Email server for authentication'),
           'massiveaction'      => false,
           'joinparams'         => [
              'condition'          => 'AND REFTABLE.`authtype` = ' . Auth::MAIL
           ],
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '16',
           'table'              => $this->getTable(),
           'field'              => 'comment',
           'name'               => __('Comments'),
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '17',
           'table'              => $this->getTable(),
           'field'              => 'language',
           'name'               => __('Language'),
           'datatype'           => 'language',
           'display_emptychoice' => true,
           'emptylabel'         => 'Default value'
        ];

        $tab[] = [
           'id'                 => '19',
           'table'              => $this->getTable(),
           'field'              => 'date_mod',
           'name'               => __('Last update'),
           'datatype'           => 'datetime',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '121',
           'table'              => $this->getTable(),
           'field'              => 'date_creation',
           'name'               => __('Creation date'),
           'datatype'           => 'datetime',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '20',
           'table'              => 'glpi_profiles',
           'field'              => 'name',
           'name'               => sprintf(
               __('%1$s (%2$s)'),
               Profile::getTypeName(Session::getPluralNumber()),
               Entity::getTypeName(1)
           ),
           'forcegroupby'       => true,
           'massiveaction'      => false,
           'datatype'           => 'dropdown',
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_profiles_users',
                 'joinparams'         => [
                    'jointype'           => 'child'
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '21',
           'table'              => $this->getTable(),
           'field'              => 'user_dn',
           'name'               => __('User DN'),
           'massiveaction'      => false,
           'datatype'           => 'text'
        ];

        $tab[] = [
           'id'                 => '22',
           'table'              => $this->getTable(),
           'field'              => 'registration_number',
           'name'               => __('Administrative number'),
           'datatype'           => 'string',
           'autocomplete'       => true,
        ];

        $tab[] = [
           'id'                 => '23',
           'table'              => $this->getTable(),
           'field'              => 'date_sync',
           'datatype'           => 'datetime',
           'name'               => __('Last synchronization'),
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '24',
           'table'              => $this->getTable(),
           'field'              => 'is_deleted_ldap',
           'name'               => __('Deleted user in LDAP directory'),
           'datatype'           => 'bool',
           'massiveaction'      => false
        ];

        $tab[] = [
           'id'                 => '80',
           'table'              => 'glpi_entities',
           'linkfield'          => 'entities_id',
           'field'              => 'completename',
           'name'               => sprintf(
               __('%1$s (%2$s)'),
               Entity::getTypeName(Session::getPluralNumber()),
               Profile::getTypeName(1)
           ),
           'forcegroupby'       => true,
           'datatype'           => 'dropdown',
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_profiles_users',
                 'joinparams'         => [
                    'jointype'           => 'child'
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '81',
           'table'              => 'glpi_usertitles',
           'field'              => 'name',
           'name'               => __('Title'),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '82',
           'table'              => 'glpi_usercategories',
           'field'              => 'name',
           'name'               => __('Category'),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '79',
           'table'              => 'glpi_profiles',
           'field'              => 'name',
           'name'               => __('Default profile'),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '77',
           'table'              => 'glpi_entities',
           'field'              => 'name',
           'massiveaction'      => true,
           'name'               => __('Default entity'),
           'datatype'           => 'dropdown'
        ];

        $tab[] = [
           'id'                 => '62',
           'table'              => $this->getTable(),
           'field'              => 'begin_date',
           'name'               => __('Begin date'),
           'datatype'           => 'datetime'
        ];

        $tab[] = [
           'id'                 => '63',
           'table'              => $this->getTable(),
           'field'              => 'end_date',
           'name'               => __('End date'),
           'datatype'           => 'datetime'
        ];

        $tab[] = [
           'id'                 => '60',
           'table'              => 'glpi_tickets',
           'field'              => 'id',
           'name'               => __('Number of tickets as requester'),
           'forcegroupby'       => true,
           'usehaving'          => true,
           'datatype'           => 'count',
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_tickets_users',
                 'joinparams'         => [
                    'jointype'           => 'child',
                    'condition'          => 'AND NEWTABLE.`type` = ' . CommonITILActor::REQUESTER
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '61',
           'table'              => 'glpi_tickets',
           'field'              => 'id',
           'name'               => __('Number of written tickets'),
           'forcegroupby'       => true,
           'usehaving'          => true,
           'datatype'           => 'count',
           'massiveaction'      => false,
           'joinparams'         => [
              'jointype'           => 'child',
              'linkfield'          => 'users_id_recipient'
           ]
        ];

        $tab[] = [
           'id'                 => '64',
           'table'              => 'glpi_tickets',
           'field'              => 'id',
           'name'               => __('Number of assigned tickets'),
           'forcegroupby'       => true,
           'usehaving'          => true,
           'datatype'           => 'count',
           'massiveaction'      => false,
           'joinparams'         => [
              'beforejoin'         => [
                 'table'              => 'glpi_tickets_users',
                 'joinparams'         => [
                    'jointype'           => 'child',
                    'condition'          => 'AND NEWTABLE.`type` = ' . CommonITILActor::ASSIGN
                 ]
              ]
           ]
        ];

        $tab[] = [
           'id'                 => '99',
           'table'              => 'glpi_users',
           'field'              => 'name',
           'linkfield'          => 'users_id_supervisor',
           'name'               => __('Responsible'),
           'datatype'           => 'dropdown',
           'massiveaction'      => false,
        ];

        // add objectlock search options
        $tab = array_merge($tab, ObjectLock::rawSearchOptionsToAdd(get_class($this)));

        return $tab;
    }

    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'authtype':
                $auths_id = 0;
                if (isset($values['auths_id']) && !empty($values['auths_id'])) {
                    $auths_id = $values['auths_id'];
                }
                return Auth::getMethodName($values[$field], $auths_id);
            case 'picture':
                if (isset($options['html']) && $options['html']) {
                    return Html::image(
                        self::getThumbnailURLForPicture($values['picture']),
                        ['class' => 'user_picture_small', 'alt' => __('Picture')]
                    );
                }
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }

    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {

        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;
        switch ($field) {
            case 'authtype':
                $options['name'] = $name;
                $options['value'] = $values[$field];
                return Auth::dropdown($options);
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }


    /**
     * Get all groups where the current user have delegating.
     *
     * @since 0.83
     *
     * @param integer|string $entities_id ID of the entity to restrict
     *
     * @return integer[]
     */
    public static function getDelegateGroupsForUser($entities_id = '')
    {
        global $DB;

        $iterator = $DB->request([
           'SELECT'          => 'glpi_groups_users.groups_id',
           'DISTINCT'        => true,
           'FROM'            => 'glpi_groups_users',
           'INNER JOIN'      => [
              'glpi_groups'  => [
                 'FKEY'   => [
                    'glpi_groups_users'  => 'groups_id',
                    'glpi_groups'        => 'id'
                 ]
              ]
           ],
           'WHERE'           => [
              'glpi_groups_users.users_id'        => Session::getLoginUserID(),
              'glpi_groups_users.is_userdelegate' => 1
           ] + getEntitiesRestrictCriteria('glpi_groups', '', $entities_id, 1)
        ]);

        $groups = [];
        while ($data = $iterator->next()) {
            $groups[$data['groups_id']] = $data['groups_id'];
        }
        return $groups;
    }


    /**
     * Execute the query to select box with all glpi users where select key = name
     *
     * Internaly used by showGroup_Users, dropdownUsers and ajax/getDropdownUsers.php
     *
     * @param boolean         $count            true if execute an count(*) (true by default)
     * @param string|string[] $right            limit user who have specific right (default 'all')
     * @param integer         $entity_restrict  Restrict to a defined entity (default -1)
     * @param integer         $value            default value (default 0)
     * @param integer[]       $used             Already used items ID: not to display in dropdown
     * @param string          $search           pattern (default '')
     * @param integer         $start            start LIMIT value (default 0)
     * @param integer         $limit            limit LIMIT value (default -1 no limit)
     * @param boolean         $inactive_deleted true to retreive also inactive or deleted users
     *
     * @return mysqli_result|boolean
     */
    public static function getSqlSearchResult(
        $count = true,
        $right = "all",
        $entity_restrict = -1,
        $value = 0,
        array $used = [],
        $search = '',
        $start = 0,
        $limit = -1,
        $inactive_deleted = 0,
        $with_no_right = 0
    ) {
        global $DB;

        // No entity define : use active ones
        if ($entity_restrict < 0) {
            $entity_restrict = $_SESSION["glpiactiveentities"];
        }

        $joinprofile      = false;
        $joinprofileright = false;
        $WHERE = [];

        switch ($right) {
            case "interface":
                $joinprofile = true;
                $WHERE = [
                   'glpi_profiles.interface' => 'central'
                ] + getEntitiesRestrictCriteria('glpi_profiles_users', '', $entity_restrict, 1);
                break;

            case "id":
                $WHERE = ['glpi_users.id' => Session::getLoginUserID()];
                break;

            case "delegate":
                $groups = self::getDelegateGroupsForUser($entity_restrict);
                $users  = [];
                if (count($groups)) {
                    $iterator = $DB->request([
                       'SELECT'    => 'glpi_users.id',
                       'FROM'      => 'glpi_groups_users',
                       'LEFT JOIN' => [
                          'glpi_users'   => [
                             'FKEY'   => [
                                'glpi_groups_users'  => 'users_id',
                                'glpi_users'         => 'id'
                             ]
                          ]
                       ],
                       'WHERE'     => [
                          'glpi_groups_users.groups_id' => $groups,
                          'glpi_groups_users.users_id'  => ['<>', Session::getLoginUserID()]
                       ]
                    ]);
                    while ($data = $iterator->next()) {
                        $users[$data["id"]] = $data["id"];
                    }
                }
                // Add me to users list for central
                if (Session::getCurrentInterface() == 'central') {
                    $users[Session::getLoginUserID()] = Session::getLoginUserID();
                }

                if (count($users)) {
                    $WHERE = ['glpi_users.id' => $users];
                }
                break;

            case "groups":
                $groups = [];
                if (isset($_SESSION['glpigroups'])) {
                    $groups = $_SESSION['glpigroups'];
                }
                $users  = [];
                if (count($groups)) {
                    $iterator = $DB->request([
                       'SELECT'    => 'glpi_users.id',
                       'FROM'      => 'glpi_groups_users',
                       'LEFT JOIN' => [
                          'glpi_users'   => [
                             'FKEY'   => [
                                'glpi_groups_users'  => 'users_id',
                                'glpi_users'         => 'id'
                             ]
                          ]
                       ],
                       'WHERE'     => [
                          'glpi_groups_users.groups_id' => $groups,
                          'glpi_groups_users.users_id'  => ['<>', Session::getLoginUserID()]
                       ]
                    ]);
                    while ($data = $iterator->next()) {
                        $users[$data["id"]] = $data["id"];
                    }
                }
                // Add me to users list for central
                if (Session::getCurrentInterface() == 'central') {
                    $users[Session::getLoginUserID()] = Session::getLoginUserID();
                }

                if (count($users)) {
                    $WHERE = ['glpi_users.id' => $users];
                }

                break;

            case "all":
                $WHERE = [
                   'glpi_users.id' => ['>', 0],
                   'OR' => getEntitiesRestrictCriteria('glpi_profiles_users', '', $entity_restrict, 1)
                ];

                if ($with_no_right) {
                    $WHERE['OR'][] = ['glpi_profiles_users.entities_id' => null];
                }
                break;

            default:
                $joinprofile = true;
                $joinprofileright = true;
                if (!is_array($right)) {
                    $right = [$right];
                }
                $forcecentral = true;

                $ORWHERE = [];
                foreach ($right as $r) {
                    switch ($r) {
                        case 'own_ticket':
                            $ORWHERE[] = [
                               [
                                  'glpi_profilerights.name'     => 'ticket',
                                  'glpi_profilerights.rights'   => ['&', Ticket::OWN]
                               ] + getEntitiesRestrictCriteria('glpi_profiles_users', '', $entity_restrict, 1)
                            ];
                            break;

                        case 'create_ticket_validate':
                            $ORWHERE[] = [
                               [
                                  'glpi_profilerights.name'  => 'ticketvalidation',
                                  'OR'                       => [
                                     ['glpi_profilerights.rights'   => ['&', TicketValidation::CREATEREQUEST]],
                                     ['glpi_profilerights.rights'   => ['&', TicketValidation::CREATEINCIDENT]]
                                  ]
                               ] + getEntitiesRestrictCriteria('glpi_profiles_users', '', $entity_restrict, 1)
                            ];
                            $forcecentral = false;
                            break;

                        case 'validate_request':
                            $ORWHERE[] = [
                               [
                                  'glpi_profilerights.name'     => 'ticketvalidation',
                                  'glpi_profilerights.rights'   => ['&', TicketValidation::VALIDATEREQUEST]
                               ] + getEntitiesRestrictCriteria('glpi_profiles_users', '', $entity_restrict, 1)
                            ];
                            $forcecentral = false;
                            break;

                        case 'validate_incident':
                            $ORWHERE[] = [
                               [
                                  'glpi_profilerights.name'     => 'ticketvalidation',
                                  'glpi_profilerights.rights'   => ['&', TicketValidation::VALIDATEINCIDENT]
                               ] + getEntitiesRestrictCriteria('glpi_profiles_users', '', $entity_restrict, 1)
                            ];
                            $forcecentral = false;
                            break;

                        case 'validate':
                            $ORWHERE[] = [
                               [
                                  'glpi_profilerights.name'     => 'changevalidation',
                                  'glpi_profilerights.rights'   => ['&', ChangeValidation::VALIDATE]
                               ] + getEntitiesRestrictCriteria('glpi_profiles_users', '', $entity_restrict, 1)
                            ];
                            break;

                        case 'create_validate':
                            $ORWHERE[] = [
                               [
                                  'glpi_profilerights.name'     => 'changevalidation',
                                  'glpi_profilerights.rights'   => ['&', ChangeValidation::CREATE]
                               ] + getEntitiesRestrictCriteria('glpi_profiles_users', '', $entity_restrict, 1)
                            ];
                            break;

                        case 'see_project':
                            $ORWHERE[] = [
                               [
                                  'glpi_profilerights.name'     => 'project',
                                  'glpi_profilerights.rights'   => ['&', Project::READMY]
                               ] + getEntitiesRestrictCriteria('glpi_profiles_users', '', $entity_restrict, 1)
                            ];
                            break;

                        case 'faq':
                            $ORWHERE[] = [
                               [
                                  'glpi_profilerights.name'     => 'knowbase',
                                  'glpi_profilerights.rights'   => ['&', KnowbaseItem::READFAQ]
                               ] + getEntitiesRestrictCriteria('glpi_profiles_users', '', $entity_restrict, 1)
                            ];

                            // no break
                        default:
                            // Check read or active for rights
                            $ORWHERE[] = [
                               [
                                  'glpi_profilerights.name'     => $r,
                                  'glpi_profilerights.rights'   => [
                                     '&',
                                     READ | CREATE | UPDATE | DELETE | PURGE
                                  ]
                               ] + getEntitiesRestrictCriteria('glpi_profiles_users', '', $entity_restrict, 1)
                            ];
                    }
                    if (in_array($r, Profile::$helpdesk_rights)) {
                        $forcecentral = false;
                    }
                }

                if (count($ORWHERE)) {
                    $WHERE[] = ['OR' => $ORWHERE];
                }

                if ($forcecentral) {
                    $WHERE['glpi_profiles.interface'] = 'central';
                }
        }

        if (!$inactive_deleted) {
            $WHERE = array_merge(
                $WHERE,
                [
                  'glpi_users.is_deleted' => 0,
                  'glpi_users.is_active'  => 1,
                  [
                     'OR' => [
                        ['glpi_users.begin_date' => null],
                        ['glpi_users.begin_date' => ['<', new QueryExpression('NOW()')]]
                     ]
                  ],
                  [
                     'OR' => [
                        ['glpi_users.end_date' => null],
                        ['glpi_users.end_date' => ['>', new QueryExpression('NOW()')]]
                     ]
                  ]

                ]
            );
        }

        if (
            (is_numeric($value) && $value)
            || count($used)
        ) {
            $WHERE[] = [
               'NOT' => [
                  'glpi_users.id' => $used
               ]
            ];
        }

        $criteria = [
           'FROM'            => 'glpi_users',
           'LEFT JOIN'       => [
              'glpi_useremails'       => [
                 'ON' => [
                    'glpi_useremails' => 'users_id',
                    'glpi_users'      => 'id'
                 ]
              ],
              'glpi_profiles_users'   => [
                 'ON' => [
                    'glpi_profiles_users'   => 'users_id',
                    'glpi_users'            => 'id'
                 ]
              ]
           ]
        ];
        if ($count) {
            $criteria['SELECT'] = ['COUNT' => 'glpi_users.id AS CPT'];
            $criteria['DISTINCT'] = true;
        } else {
            $criteria['SELECT'] = 'glpi_users.*';
            $criteria['DISTINCT'] = true;
        }

        if ($joinprofile) {
            $criteria['LEFT JOIN']['glpi_profiles'] = [
               'ON' => [
                  'glpi_profiles_users'   => 'profiles_id',
                  'glpi_profiles'         => 'id'
               ]
            ];
            if ($joinprofileright) {
                $criteria['LEFT JOIN']['glpi_profilerights'] = [
                   'ON' => [
                      'glpi_profilerights' => 'profiles_id',
                      'glpi_profiles'      => 'id'
                   ]
                ];
            }
        }

        if (!$count) {
            if ((strlen($search ?? '') > 0)) {
                $txt_search = Search::makeTextSearchValue($search);

                $firstname_field = $DB->quoteName(self::getTableField('firstname'));
                $realname_field = $DB->quoteName(self::getTableField('realname'));
                $fields = $_SESSION["glpinames_format"] == self::FIRSTNAME_BEFORE
                   ? [$firstname_field, $realname_field]
                   : [$realname_field, $firstname_field];

                $concat = new \QueryExpression(
                    'CONCAT(' . implode(',' . $DB->quoteValue(' ') . ',', $fields) . ')'
                    . ' LIKE ' . $DB->quoteValue($txt_search)
                );
                $WHERE[] = [
                   'OR' => [
                      'glpi_users.name'       => ['LIKE', $txt_search],
                      'glpi_users.realname'   => ['LIKE', $txt_search],
                      'glpi_users.firstname'  => ['LIKE', $txt_search],
                      'glpi_users.phone'      => ['LIKE', $txt_search],
                      'glpi_useremails.email' => ['LIKE', $txt_search],
                      $concat
                   ]
                ];
            }

            if ($_SESSION["glpinames_format"] == self::FIRSTNAME_BEFORE) {
                $criteria['ORDERBY'] = [
                   'glpi_users.firstname',
                   'glpi_users.realname',
                   'glpi_users.name'
                ];
            } else {
                $criteria['ORDERBY'] = [
                   'glpi_users.realname',
                   'glpi_users.firstname',
                   'glpi_users.name'
                ];
            }

            if ($limit > 0) {
                $criteria['LIMIT'] = $limit;
                $criteria['START'] = $start;
            }
        }
        $criteria['WHERE'] = $WHERE;
        return $DB->request($criteria);
    }


    /**
     * Make a select box with all glpi users where select key = name
     *
     * @param $options array of possible options:
     *    - name             : string / name of the select (default is users_id)
     *    - value
     *    - values           : in case of select[multiple], pass the array of multiple values
     *    - right            : string / limit user who have specific right :
     *                             id -> only current user (default case);
     *                             interface -> central;
     *                             all -> all users;
     *                             specific right like Ticket::READALL, CREATE.... (is array passed one of all passed right is needed)
     *    - comments         : boolean / is the comments displayed near the dropdown (default true)
     *    - entity           : integer or array / restrict to a defined entity or array of entities
     *                          (default -1 : no restriction)
     *    - entity_sons      : boolean / if entity restrict specified auto select its sons
     *                          only available if entity is a single value not an array(default false)
     *    - all              : Nobody or All display for none selected
     *                             all=0 (default) -> Nobody
     *                             all=1 -> All
     *                             all=-1-> nothing
     *    - rand             : integer / already computed rand value
     *    - toupdate         : array / Update a specific item on select change on dropdown
     *                          (need value_fieldname, to_update, url
     *                          (see Ajax::updateItemOnSelectEvent for information)
     *                          and may have moreparams)
     *    - used             : array / Already used items ID: not to display in dropdown (default empty)
     *    - ldap_import
     *    - on_change        : string / value to transmit to "onChange"
     *    - display          : boolean / display or get string (default true)
     *    - width            : specific width needed (default 80%)
     *    - specific_tags    : array of HTML5 tags to add to the field
     *    - url              : url of the ajax php code which should return the json data to show in
     *                         the dropdown (default /ajax/getDropdownUsers.php)
     *    - inactive_deleted : retreive also inactive or deleted users
     *
     * @return integer|string Random value if displayed, string otherwise
     */
    public static function dropdown($options = [])
    {
        global $CFG_GLPI;

        // Default values
        $p = [
           'name'                => 'users_id',
           'value'               => '',
           'values'              => [],
           'right'               => 'id',
           'all'                 => 0,
           'display_emptychoice' => true,
           'placeholder'         => '',
           'on_change'           => '',
           'comments'            => 1,
           'width'               => '80%',
           'entity'              => -1,
           'entity_sons'         => false,
           'used'                => [],
           'ldap_import'         => false,
           'toupdate'            => '',
           'rand'                => mt_rand(),
           'display'             => true,
           '_user_index'         => 0,
           'specific_tags'       => [],
           'url'                 => $CFG_GLPI['root_doc'] . "/ajax/getDropdownUsers.php",
           'inactive_deleted'    => 0,
           'with_no_right'       => 0,
        ];

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $p[$key] = $val;
            }
        }

        // check default value (in case of multiple observers)
        if (is_array($p['value'])) {
            $p['value'] = $p['value'][$p['_user_index']] ?? 0;
        }

        // Check default value for dropdown : need to be a numeric
        if ((strlen($p['value']) == 0) || !is_numeric($p['value'])) {
            $p['value'] = 0;
        }

        $output = '';
        if (!($p['entity'] < 0) && $p['entity_sons']) {
            if (is_array($p['entity'])) {
                $output .= "entity_sons options is not available with array of entity";
            } else {
                $p['entity'] = getSonsOf('glpi_entities', $p['entity']);
            }
        }

        // Make a select box with all glpi users
        $user = getUserName($p['value'], 2);

        $view_users = self::canView();

        if (!empty($p['value']) && ($p['value'] > 0)) {
            $default = $user["name"];
        } else {
            if ($p['all']) {
                $default = __('All');
            } else {
                $default = Dropdown::EMPTY_VALUE;
            }
        }

        // get multiple values name
        $valuesnames = [];
        foreach ($p['values'] as $value) {
            if (!empty($value) && ($value > 0)) {
                $user = getUserName($value, 2);
                $valuesnames[] = $user["name"];
            }
        }

        $field_id = Html::cleanId("dropdown_" . $p['name'] . $p['rand']);
        $param    = [
           'value'               => $p['value'],
           'values'              => $p['values'],
           'valuename'           => $default,
           'valuesnames'         => $valuesnames,
           'width'               => $p['width'],
           'all'                 => $p['all'],
           'display_emptychoice' => $p['display_emptychoice'],
           'placeholder'         => $p['placeholder'],
           'right'               => $p['right'],
           'on_change'           => $p['on_change'],
           'used'                => $p['used'],
           'inactive_deleted'    => $p['inactive_deleted'],
           'with_no_right'       => $p['with_no_right'],
           'entity_restrict'     => ($entity_restrict = (is_array($p['entity']) ? json_encode(array_values($p['entity'])) : $p['entity'])),
           'specific_tags'       => $p['specific_tags'],
           '_idor_token'         => Session::getNewIDORToken(__CLASS__, [
              'right'           => $p['right'],
              'entity_restrict' => $entity_restrict,
           ]),
        ];

        $output   = Html::jsAjaxDropdown(
            $p['name'],
            $field_id,
            $p['url'],
            $param
        );

        // Display comment
        if ($p['comments']) {
            $comment_id = Html::cleanId("comment_" . $p['name'] . $p['rand']);
            $link_id = Html::cleanId("comment_link_" . $p["name"] . $p['rand']);
            if (!$view_users) {
                $user["link"] = '';
            } elseif (empty($user["link"])) {
                $user["link"] = $CFG_GLPI['root_doc'] . "/front/user.php";
            }

            if (empty($user['comment'])) {
                $user['comment'] = Toolbox::ucfirst(
                    sprintf(
                        __('Show %1$s'),
                        self::getTypeName(Session::getPluralNumber())
                    )
                );
            }
            $output .= "&nbsp;" . Html::showToolTip(
                $user["comment"],
                ['contentid' => $comment_id,
                                               'display'   => false,
                                               'link'      => $user["link"],
                                               'linkid'    => $link_id]
            );

            $paramscomment = [
               'value'    => '__VALUE__',
               'itemtype' => User::getType()
            ];

            if ($view_users) {
                $paramscomment['withlink'] = $link_id;
            }
            $output .= Ajax::updateItemOnSelectEvent(
                $field_id,
                $comment_id,
                $CFG_GLPI["root_doc"] . "/ajax/comments.php",
                $paramscomment,
                false
            );
        }
        $output .= Ajax::commonDropdownUpdateItem($p, false);

        if (
            Session::haveRight('user', self::IMPORTEXTAUTHUSERS)
            && $p['ldap_import']
            && Entity::isEntityDirectoryConfigured($_SESSION['glpiactive_entity'])
        ) {
            $output .= "<span title=\"" . __s('Import a user') . "\" class='fa fa-plus pointer'" .
                        " onClick=\"" . Html::jsGetElementbyID('userimport' . $p['rand']) . ".dialog('open');\">
                     <span class='sr-only'>" . __s('Import a user') . "</span></span>";
            $output .= Ajax::createIframeModalWindow(
                'userimport' . $p['rand'],
                $CFG_GLPI["root_doc"] .
                                                         "/front/ldap.import.php?entity=" .
                                                         $_SESSION['glpiactive_entity'],
                ['title'   => __('Import a user'),
                                                           'display' => false]
            );
        }

        if ($p['display']) {
            echo $output;
            return $p['rand'];
        }
        return $output;
    }


    /**
     * Show simple add user form for external auth.
     *
     * @return void|boolean false if user does not have rights to import users from external sources,
     *    print form otherwise
     */
    public static function showAddExtAuthForm()
    {

        if (!Session::haveRight("user", self::IMPORTEXTAUTHUSERS)) {
            return false;
        }

        echo "<div class='center'>\n";
        echo "<form aria-label='External Authentication' method='post' action='" . Toolbox::getItemTypeFormURL('User') . "'>\n";

        echo "<table class='tab_cadre' aria-label='External Auth form'>\n";
        echo "<tr><th colspan='4'>" . __('Automatically add a user of an external source') . "</th></tr>\n";

        echo "<tr class='tab_bg_1'><td>" . __('Login') . "</td>\n";
        echo "<td><input type='text' name='login'></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td class='tab_bg_2 center' colspan='2'>\n";
        echo "<input type='submit' name='add_ext_auth_ldap' value=\"" . __s('Import from directories') . "\"
             class='submit'>\n";
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td class='tab_bg_2 center' colspan='2'>\n";
        echo "<input type='submit' name='add_ext_auth_simple' value=\"" . __s('Import from other sources') . "\"
             class='submit'>\n";
        echo "</td></tr>\n";

        echo "</table>";
        Html::closeForm();
        echo "</div>\n";
    }


    /**
     * Change auth method for given users.
     *
     * @param integer[] $IDs      IDs of users
     * @param integer   $authtype Auth type (see Auth constants)
     * @param integer   $server   ID of auth server
     *
     * @return boolean
     */
    public static function changeAuthMethod(array $IDs = [], $authtype = 1, $server = -1)
    {
        global $DB;

        if (!Session::haveRight(self::$rightname, self::UPDATEAUTHENT)) {
            return false;
        }

        if (
            !empty($IDs)
            && in_array($authtype, [Auth::DB_GLPI, Auth::LDAP, Auth::MAIL, Auth::EXTERNAL])
        ) {
            $result = $DB->update(
                self::getTable(),
                [
                  'authtype'        => $authtype,
                  'auths_id'        => $server,
                  'password'        => '',
                  'is_deleted_ldap' => 0
                ],
                [
                  'id' => $IDs
                ]
            );
            if ($result) {
                foreach ($IDs as $ID) {
                    $changes = [
                       0,
                       '',
                       addslashes(
                           sprintf(
                               __('%1$s: %2$s'),
                               __('Update authentification method to'),
                               Auth::getMethodName($authtype, $server)
                           )
                       )
                    ];
                    Log::history($ID, __CLASS__, $changes, '', Log::HISTORY_LOG_SIMPLE_MESSAGE);
                }

                return true;
            }
        }
        return false;
    }


    /**
     * Generate vcard for the current user.
     *
     * @return void
     */
    public function generateVcard()
    {

        // prepare properties for the Vcard
        if (
            !empty($this->fields["realname"])
            || !empty($this->fields["firstname"])
        ) {
            $name = [$this->fields["realname"], $this->fields["firstname"], "", "", ""];
        } else {
            $name = [$this->fields["name"], "", "", "", ""];
        }

        // create vcard
        $vcard = new VObject\Component\VCard([
           'N'     => $name,
           'EMAIL' => $this->getDefaultEmail(),
           'NOTE'  => $this->fields["comment"],
        ]);
        $vcard->add('TEL', $this->fields["phone"], ['type' => 'PREF;WORK;VOICE']);
        $vcard->add('TEL', $this->fields["phone2"], ['type' => 'HOME;VOICE']);
        $vcard->add('TEL', $this->fields["mobile"], ['type' => 'WORK;CELL']);

        // send the  VCard
        $output   = $vcard->serialize();
        $filename = implode("_", array_filter($name)) . ".vcf";

        @header("Content-Disposition: attachment; filename=\"$filename\"");
        @header("Content-Length: " . Toolbox::strlen($output));
        @header("Connection: close");
        @header("content-type: text/x-vcard; charset=UTF-8");

        echo $output;
    }


    /**
     * Show items of the current user.
     *
     * @param boolean $tech false to display items owned by user, true to display items managed by user
     *
     * @return void
     */
    public function showItems($tech)
    {
        global $DB, $CFG_GLPI;

        $ID = $this->getField('id');

        if ($tech) {
            $type_user   = $CFG_GLPI['linkuser_tech_types'];
            $type_group  = $CFG_GLPI['linkgroup_tech_types'];
            $field_user  = 'users_id_tech';
            $field_group = 'groups_id_tech';
        } else {
            $type_user   = $CFG_GLPI['linkuser_types'];
            $type_group  = $CFG_GLPI['linkgroup_types'];
            $field_user  = 'users_id';
            $field_group = 'groups_id';
        }

        $group_where = "";
        $groups      = [];

        $iterator = $DB->request([
           'SELECT'    => [
              'glpi_groups_users.groups_id',
              'glpi_groups.name'
           ],
           'FROM'      => 'glpi_groups_users',
           'LEFT JOIN' => [
              'glpi_groups' => [
                 'FKEY' => [
                    'glpi_groups_users'  => 'groups_id',
                    'glpi_groups'        => 'id'
                 ]
              ]
           ],
           'WHERE'     => ['glpi_groups_users.users_id' => $ID]
        ]);
        $number = count($iterator);

        $group_where = [];
        while ($data = $iterator->next()) {
            $group_where[$field_group][] = $data['groups_id'];
            $groups[$data["groups_id"]] = $data["name"];
        }

        echo "<div class='spaced'><table class='tab_cadre_fixehov' aria-label='items information'>";
        $header = "<tr><th>" . _n('Type', 'Types', 1) . "</th>";
        $header .= "<th>" . Entity::getTypeName(1) . "</th>";
        $header .= "<th>" . __('Name') . "</th>";
        $header .= "<th>" . __('Serial number') . "</th>";
        $header .= "<th>" . __('Inventory number') . "</th>";
        $header .= "<th>" . __('Status') . "</th>";
        $header .= "<th>&nbsp;</th></tr>";
        echo $header;

        foreach ($type_user as $itemtype) {
            if (!($item = getItemForItemtype($itemtype))) {
                continue;
            }
            if ($item->canView()) {
                $itemtable = getTableForItemType($itemtype);
                $iterator_params = [
                   'FROM'   => $itemtable,
                   'WHERE'  => [$field_user => $ID]
                ];

                if ($item->maybeTemplate()) {
                    $iterator_params['WHERE']['is_template'] = 0;
                }
                if ($item->maybeDeleted()) {
                    $iterator_params['WHERE']['is_deleted'] = 0;
                }

                $item_iterator = $DB->request($iterator_params);

                $type_name = $item->getTypeName();

                while ($data = $item_iterator->next()) {
                    $cansee = $item->can($data["id"], READ);
                    if (!isset($data["name"])) {
                        $linked_component = new ($itemtype::getDeviceType())();
                        $linked_component->getFromDB($data[getForeignKeyFieldForItemType($itemtype::getDeviceType())]);
                        $link = $linked_component->fields['designation'] . " (" . $data['id'] . ")";
                    } else {
                        $link   = $data["name"];
                    }
                    if ($cansee) {
                        $link_item = $item::getFormURLWithID($data['id']);
                        if ($_SESSION["glpiis_ids_visible"] || empty($link)) {
                            $link = sprintf(__('%1$s (%2$s)'), $link, $data["id"]);
                        }
                        $link = "<a href='" . $link_item . "'>" . $link . "</a>";
                    }
                    $linktype = "";
                    if ($data[$field_user] == $ID) {
                        $linktype = self::getTypeName(1);
                    }
                    echo "<tr class='tab_bg_1'><td class='center'>$type_name</td>";
                    echo "<td class='center'>" . Dropdown::getDropdownName(
                        "glpi_entities",
                        $data["entities_id"]
                    ) . "</td>";
                    echo "<td class='center'>$link</td>";
                    echo "<td class='center'>";
                    if (isset($data["serial"]) && !empty($data["serial"])) {
                        echo $data["serial"];
                    } else {
                        echo '&nbsp;';
                    }
                    echo "</td><td class='center'>";
                    if (isset($data["otherserial"]) && !empty($data["otherserial"])) {
                        echo $data["otherserial"];
                    } else {
                        echo '&nbsp;';
                    }
                    echo "</td><td class='center'>";
                    if (isset($data["states_id"])) {
                        echo Dropdown::getDropdownName("glpi_states", $data['states_id']);
                    } else {
                        echo '&nbsp;';
                    }

                    echo "</td><td class='center'>$linktype</td></tr>";
                }
            }
        }
        if ($number) {
            echo $header;
        }
        echo "</table></div>";

        if (count($group_where)) {
            echo "<div class='spaced'><table class='tab_cadre_fixehov' aria-label='Items Informations'>";
            $header = "<tr>" .
                  "<th>" . _n('Type', 'Types', 1) . "</th>" .
                  "<th>" . Entity::getTypeName(1) . "</th>" .
                  "<th>" . __('Name') . "</th>" .
                  "<th>" . __('Serial number') . "</th>" .
                  "<th>" . __('Inventory number') . "</th>" .
                  "<th>" . __('Status') . "</th>" .
                  "<th>&nbsp;</th></tr>";
            echo $header;
            $nb = 0;
            foreach ($type_group as $itemtype) {
                if (!($item = getItemForItemtype($itemtype))) {
                    continue;
                }
                if ($item->canView() && $item->isField($field_group)) {
                    $itemtable = getTableForItemType($itemtype);
                    $iterator_params = [
                       'FROM'   => $itemtable,
                       'WHERE'  => ['OR' => $group_where]
                    ];

                    if ($item->maybeTemplate()) {
                        $iterator_params['WHERE']['is_template'] = 0;
                    }
                    if ($item->maybeDeleted()) {
                        $iterator_params['WHERE']['is_deleted'] = 0;
                    }

                    $group_iterator = $DB->request($iterator_params);

                    $type_name = $item->getTypeName();

                    while ($data = $group_iterator->next()) {
                        $nb++;
                        $cansee = $item->can($data["id"], READ);
                        $link   = $data["name"];
                        if ($cansee) {
                            $link_item = $item::getFormURLWithID($data['id']);
                            if ($_SESSION["glpiis_ids_visible"] || empty($link)) {
                                $link = sprintf(__('%1$s (%2$s)'), $link, $data["id"]);
                            }
                            $link = "<a href='" . $link_item . "'>" . $link . "</a>";
                        }
                        $linktype = "";
                        if (isset($groups[$data[$field_group]])) {
                            $linktype = sprintf(
                                __('%1$s = %2$s'),
                                Group::getTypeName(1),
                                $groups[$data[$field_group]]
                            );
                        }
                        echo "<tr class='tab_bg_1'><td class='center'>$type_name</td>";
                        echo "<td class='center'>" . Dropdown::getDropdownName(
                            "glpi_entities",
                            $data["entities_id"]
                        );
                        echo "</td><td class='center'>$link</td>";
                        echo "<td class='center'>";
                        if (isset($data["serial"]) && !empty($data["serial"])) {
                            echo $data["serial"];
                        } else {
                            echo '&nbsp;';
                        }
                        echo "</td><td class='center'>";
                        if (isset($data["otherserial"]) && !empty($data["otherserial"])) {
                            echo $data["otherserial"];
                        } else {
                            echo '&nbsp;';
                        }
                        echo "</td><td class='center'>";
                        if (isset($data["states_id"])) {
                            echo Dropdown::getDropdownName("glpi_states", $data['states_id']);
                        } else {
                            echo '&nbsp;';
                        }

                        echo "</td><td class='center'>$linktype</td></tr>";
                    }
                }
            }
            if ($nb) {
                echo $header;
            }
            echo "</table></div>";
        }
    }


    /**
     * Get user by email, importing it from LDAP if not existing.
     *
     * @param string $email
     *
     * @return integer ID of user, 0 if not found nor imported
     */
    public static function getOrImportByEmail($email = '')
    {
        global $DB, $CFG_GLPI;

        $iterator = $DB->request([
           'SELECT'    => 'users_id AS id',
           'FROM'      => 'glpi_useremails',
           'LEFT JOIN' => [
              'glpi_users' => [
                 'FKEY' => [
                    'glpi_useremails' => 'users_id',
                    'glpi_users'      => 'id'
                 ]
              ]
           ],
           'WHERE'     => [
              'glpi_useremails.email' => $DB->escape(stripslashes($email))
           ],
           'ORDER'     => ['glpi_users.is_active DESC', 'is_deleted ASC']
        ]);

        //User still exists in DB
        if (count($iterator)) {
            $result = $iterator->next();
            return $result['id'];
        } else {
            if ($CFG_GLPI["is_users_auto_add"]) {
                //Get all ldap servers with email field configured
                $ldaps = AuthLDAP::getServersWithImportByEmailActive();
                //Try to find the user by his email on each ldap server

                foreach ($ldaps as $ldap) {
                    $params = [
                       'method' => AuthLDAP::IDENTIFIER_EMAIL,
                       'value'  => $email,
                    ];
                    $res = AuthLDAP::ldapImportUserByServerId(
                        $params,
                        AuthLDAP::ACTION_IMPORT,
                        $ldap
                    );

                    if (isset($res['id'])) {
                        return $res['id'];
                    }
                }
            }
        }
        return 0;
    }


    /**
     * Handle user deleted in LDAP using configured policy.
     *
     * @param integer $users_id
     *
     * @return void
     */
    public static function manageDeletedUserInLdap($users_id)
    {
        global $CFG_GLPI;

        //The only case where users_id can be null if when a user has been imported into GLPI
        //it's dn still exists, but doesn't match the connection filter anymore
        //In this case, do not try to process the user
        if (!$users_id) {
            return;
        }

        //User is present in DB but not in the directory : it's been deleted in LDAP
        $tmp = [
           'id'              => $users_id,
           'is_deleted_ldap' => 1,
        ];
        $myuser = new self();
        $myuser->getFromDB($users_id);

        //User is already considered as delete from ldap
        if ($myuser->fields['is_deleted_ldap'] == 1) {
            return;
        }

        switch ($CFG_GLPI['user_deleted_ldap']) {
            //DO nothing
            default:
            case AuthLDAP::DELETED_USER_PRESERVE:
                $myuser->update($tmp);
                break;

                //Put user in trashbin
            case AuthLDAP::DELETED_USER_DELETE:
                $myuser->delete($tmp);
                break;

                //Delete all user dynamic habilitations and groups
            case AuthLDAP::DELETED_USER_WITHDRAWDYNINFO:
                Profile_User::deleteRights($users_id, true);
                Group_User::deleteGroups($users_id, true);
                $myuser->update($tmp);
                break;

                //Deactivate the user
            case AuthLDAP::DELETED_USER_DISABLE:
                $tmp['is_active'] = 0;
                $myuser->update($tmp);
                break;

                //Deactivate the user+ Delete all user dynamic habilitations and groups
            case AuthLDAP::DELETED_USER_DISABLEANDWITHDRAWDYNINFO:
                $tmp['is_active'] = 0;
                $myuser->update($tmp);
                Profile_User::deleteRights($users_id, true);
                Group_User::deleteGroups($users_id, true);
                break;
        }
        /*
        $changes[0] = '0';
        $changes[1] = '';
        $changes[2] = __('Deleted user in LDAP directory');
        Log::history($users_id, 'User', $changes, 0, Log::HISTORY_LOG_SIMPLE_MESSAGE);*/
    }

    /**
     * Get user ID from its name.
     *
     * @param string $name User name
     *
     * @return integer
     */
    public static function getIdByName($name)
    {
        return self::getIdByField('name', $name);
    }


    /**
     * Get user ID from a field
     *
     * @since 0.84
     *
     * @param string $field Field name
     * @param string $value Field value
     *
     * @return integer
     */
    public static function getIdByField($field, $value, $escape = true)
    {
        global $DB;

        if ($escape) {
            $value = addslashes($value);
        }

        $iterator = $DB->request([
           'SELECT' => 'id',
           'FROM'   => self::getTable(),
           'WHERE'  => [$field => $value]
        ]);

        if (count($iterator) == 1) {
            $row = $iterator->next();
            return (int)$row['id'];
        }
        return false;
    }


    /**
     * Show password update form for current user.
     *
     * @param array $error_messages
     *
     * @return void
     */
    public function showPasswordUpdateForm(array $error_messages = [])
    {
        global $CFG_GLPI;

        echo '<form aria-label="Password Update" method="post" action="' . $CFG_GLPI['root_doc'] . '/front/updatepassword.php">';
        echo '<table class="tab_cadre" aria-label="Password Update">';
        echo '<tr><th colspan="2">' . __('Password update') . '</th></tr>';

        if (Session::mustChangePassword()) {
            echo '<tr class="tab_bg_2 center">';
            echo '<td colspan="2" class="red b">';
            echo __('Your password has expired. You must change it to be able to login.');
            echo '</td>';
            echo '</tr>';
        }

        echo '<tr class="tab_bg_1">';
        echo '<td>';
        echo __('Login');
        echo '</td>';
        echo '<td>';
        echo '<input type="text" name="name" value="' . $this->fields['name'] . '" readonly="readonly" />';
        echo '</td>';
        echo '</tr>';

        echo '<tr class="tab_bg_1">';
        echo '<td>';
        echo '<label for="current_password">' . __('Current password') . '</label>';
        echo '</td>';
        echo '<td>';
        echo '<input type="password" id="current_password" name="current_password" />';
        echo '</td>';
        echo '</tr>';

        echo '<tr class="tab_bg_1">';
        echo '<td>';
        echo '<label for="password">' . __('New password') . '</label>';
        echo '</td>';
        echo '<td>';
        echo '<input type="password" id="password" name="password" autocomplete="new-password" onkeyup="return passwordCheck();" />';
        echo '</td>';
        echo '</tr>';

        echo '<tr class="tab_bg_1">';
        echo '<td>';
        echo '<label for="password2">' . __('New password confirmation') . '</label>';
        echo '</td>';
        echo '<td>';
        echo '<input type="password" id="password2" name="password2" autocomplete="new-password" />';
        echo '</td>';
        echo '</tr>';

        if ($CFG_GLPI['use_password_security']) {
            echo '<tr class="tab_bg_1">';
            echo '<td>' . __('Password security policy') . '</td>';
            echo '<td>';
            Config::displayPasswordSecurityChecks();
            echo '</td>';
            echo '</tr>';
        }

        echo '<tr class="tab_bg_2 center">';
        echo '<td colspan="2">';
        echo '<input type="submit" name="update" value="' . __s('Save') . '" class="submit" />';
        echo '</td>';
        echo '</tr>';

        if (!empty($error_messages)) {
            echo '<tr class="tab_bg_2 center">';
            echo '<td colspan="2" class="red b">';
            echo implode('<br/>', $error_messages);
            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';
        Html::closeForm();
    }


    /**
     * Show new password form of password recovery process.
     *
     * @param $token
     *
     * @return void
     */
    public static function showPasswordForgetChangeForm($token)
    {
        global $CFG_GLPI;

        if (!User::getUserByForgottenPasswordToken($token)) {
            echo "<div class='center'>";
            echo __('Your password reset request has expired or is invalid. Please renew it.');
            echo "</div>";

            return;
        }

        echo "<div class='center'>";
        echo "<form aria-label='Forget Password' method='post' name='forgetpassword' action='" . $CFG_GLPI['root_doc'] .
                 "/front/lostpassword.php'>";
        echo "<table class='tab_cadre' aria-label='Forgot Password'>";
        echo "<tr><th colspan='2'>" . __('Forgotten password?') . "</th></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'>" . __('Please enter your new password.') . "</td></tr>";

        echo "<tr class='tab_bg_1'><td>" . __('Password') . "</td>";
        echo "<td><input id='password' type='password' name='password' value='' size='20'
                  autocomplete='new-password' onkeyup=\"return passwordCheck();\">";
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'><td>" . __('Password confirmation') . "</td>";
        echo "<td><input type='password' name='password2' value='' size='20' autocomplete='new-password'>";
        echo "</td></tr>";

        if (Config::arePasswordSecurityChecksEnabled()) {
            echo "<tr class='tab_bg_1'><td>" . __('Password security policy') . "</td>";
            echo "<td>";
            Config::displayPasswordSecurityChecks();
            echo "</td></tr>";
        }

        echo "<tr class='tab_bg_2 center'><td colspan='2'>";
        echo "<input type='hidden' name='password_forget_token' value='$token'>";
        echo "<input type='submit' name='update' value=\"" . __s('Save') . "\" class='submit'>";
        echo "</td></tr>";

        echo "</table>";
        Html::closeForm();
        echo "</div>";
    }


    /**
     * Show request form of password recovery process.
     *
     * @return void
     */
    public static function showPasswordForgetRequestForm()
    {
        global $CFG_GLPI;

        $form = [
          'action' => $CFG_GLPI['root_doc'] . '/front/lostpassword.php',
          'buttons' => [
              [
                  'name' => 'update',
                  'value' => __s('Save'),
                  'class' => 'btn btn-secondary'
              ],
          ],
          'content' => [
              __('Forgotten password?') => [
                  'visible' => true,
                  'inputs' => [
                      __('Please enter your email address. An email will be sent to you and you will be able to choose a new password.') => [
                          'type' => 'text',
                          'name' => 'email',
                          'value' => '',
                          'size' => 60,
                          'col_lg' => 12,
                          'col_md' => 12,
                      ]
                  ],
              ]
          ]
        ];
        renderTwigForm($form);
    }


    /**
     * Handle password recovery form submission.
     *
     * @param array $input Submitted HTML form content
     *
     * @throws ForgetPasswordException  When the password reset request is invalid
     * @throws PasswordTooWeakException When the new password does not comply
     *                                  with the security checks
     *
     * @return boolean True if the password was successfully changed, false otherwise
     */
    public function updateForgottenPassword(array $input)
    {
        // Get user by token
        $token = $input['password_forget_token'] ?? "";
        $user = self::getUserByForgottenPasswordToken($token);

        // Invalid token
        if (!$user) {
            throw new ForgetPasswordException(
                __('Your password reset request has expired or is invalid. Please renew it.')
            );
        }

        // Check if the user is no longer active, it might happen if for some
        // reasons the user is disabled manually after requesting a password reset
        if ($user->fields['is_active'] == 0 || $user->fields['is_deleted'] == 1) {
            throw new ForgetPasswordException(
                __("Unable to reset password, please contact your administrator")
            );
        }

        // Same check but for the account activation dates
        if (
            (
                $user->fields['begin_date'] !== null
              && $user->fields['begin_date'] < $_SESSION['glpi_currenttime']
            ) || (
                $user->fields['end_date'] !== null
            && $user->fields['end_date'] > $_SESSION['glpi_currenttime']
            )
        ) {
            throw new ForgetPasswordException(
                __("Unable to reset password, please contact your administrator")
            );
        }

        // Safety check that the user authentication method support passwords changes
        if ($user->fields["authtype"] !== Auth::DB_GLPI && Auth::useAuthExt()) {
            throw new ForgetPasswordException(
                __("The authentication method configuration doesn't allow you to change your password.")
            );
        }

        $input['id'] = $user->fields['id'];

        // Check new password validity, throws exception on failure
        Config::validatePassword($input["password"], false);

        // Try to set new password
        if (!$user->update($input)) {
            return false;
        }

        // Clear password reset token data
        $user->update([
           'id'                         => $user->fields['id'],
           'password_forget_token'      => '',
           'password_forget_token_date' => 'NULL',
        ]);

        $this->getFromDB($user->fields['id']);

        return true;
    }


    /**
     * Displays password recovery result.
     *
     * @param array $input
     *
     * @return void
     */
    public function showUpdateForgottenPassword(array $input)
    {
        global $CFG_GLPI;

        echo "<div class='center'>";
        try {
            if (!$this->updateForgottenPassword($input)) {
                Html::displayMessageAfterRedirect();
            } else {
                echo __('Reset password successful.');
            }
        } catch (ForgetPasswordException $e) {
            echo $e->getMessage();
        } catch (PasswordTooWeakException $e) {
            // Force display on error
            foreach ($e->getMessages() as $message) {
                Session::addMessageAfterRedirect($message);
            }
            Html::displayMessageAfterRedirect();
        }

        echo "<br>";
        echo "<a href=\"" . $CFG_GLPI['root_doc'] . "/index.php\">" . __s('Back') . "</a>";
        echo "</div>";
    }


    /**
     * Send password recovery for a user and display result message.
     *
     * @param string $email email of the user
     *
     * @return void
     */
    public function showForgetPassword($email)
    {

        echo "<div class='center'>";
        try {
            $this->forgetPassword($email);
        } catch (ForgetPasswordException $e) {
            echo $e->getMessage();
            return;
        }
        echo __('If the given email address match an exisiting ITSM-NG user, you will receive an email containing the informations required to reset your password. Please contact your administrator if you do not receive any email.');
    }

    /**
     * Send password recovery email for a user.
     *
     * @param string $email
     *
     * @throws ForgetPasswordException If the process failed and the user should
     *                                 be aware of it (e.g. incorrect email)
     *
     * @return bool Return true if the password reset notification was sent,
     *              false if the process failed but the user should not be aware
     *              of it to avoid exposing whether or not the given email exist
     *              in our database.
     */
    public function forgetPassword(string $email): bool
    {
        $condition = [
           'glpi_users.is_active'  => 1,
           'glpi_users.is_deleted' => 0, [
              'OR' => [
                 ['glpi_users.begin_date' => null],
                 ['glpi_users.begin_date' => ['<', new QueryExpression('NOW()')]]
              ],
           ], [
              'OR'  => [
                 ['glpi_users.end_date'   => null],
                 ['glpi_users.end_date'   => ['>', new QueryExpression('NOW()')]]
              ]
           ]
        ];

        // Randomly increase the response time to prevent an attacker to be able to detect whether
        // a notification was sent (a longer response time could correspond to a SMTP operation).
        sleep(rand(1, 3));


        // Try to find a single user matching the given email
        if (!$this->getFromDBbyEmail($email, $condition)) {
            $count = self::countUsersByEmail($email, $condition);
            trigger_error(
                "Failed to find a single user for '$email', $count user(s) found.",
                E_USER_WARNING
            );

            return false;
        }

        // Check that the configuration allow this user to change his password
        if ($this->fields["authtype"] !== Auth::DB_GLPI && Auth::useAuthExt()) {
            trigger_error(
                __("The authentication method configuration doesn't allow the user '$email' to change his password."),
                E_USER_WARNING
            );

            return false;
        }

        // Check that the given email is valid
        if (!NotificationMailing::isUserAddressValid($email)) {
            throw new ForgetPasswordException(__('Invalid email address'));
        }

        // Store password reset token and date
        $input = [
           'password_forget_token'      => sha1(Toolbox::getRandomString(30)),
           'password_forget_token_date' => $_SESSION["glpi_currenttime"],
           'id'                         => $this->fields['id'],
        ];
        $this->update($input);

        // Notication on root entity (glpi_users.entities_id is only a pref)
        NotificationEvent::raiseEvent('passwordforget', $this, ['entities_id' => 0]);
        QueuedNotification::forceSendFor($this->getType(), $this->fields['id']);

        return true;
    }


    /**
     * Display information from LDAP server for user.
     *
     * @return void
     */
    private function showLdapDebug()
    {

        if ($this->fields['authtype'] != Auth::LDAP) {
            return false;
        }
        echo "<div class='spaced'>";
        echo "<table class='tab_cadre_fixe' aria-label='LDAP Debug'>";
        echo "<tr><th colspan='4'>" . AuthLDAP::getTypeName(1) . "</th></tr>";

        echo "<tr class='tab_bg_2'><td>" . __('User DN') . "</td>";
        echo "<td>" . $this->fields['user_dn'] . "</td></tr>\n";

        if ($this->fields['user_dn']) {
            echo "<tr class='tab_bg_2'><td>" . __('User information') . "</td><td>";
            $config_ldap = new AuthLDAP();
            $ds          = false;

            if ($config_ldap->getFromDB($this->fields['auths_id'])) {
                $ds = $config_ldap->connect();
            }

            if ($ds) {
                $info = AuthLDAP::getUserByDn(
                    $ds,
                    $this->fields['user_dn'],
                    ['*', 'createTimeStamp', 'modifyTimestamp']
                );
                if (is_array($info)) {
                    Html::printCleanArray($info);
                } else {
                    echo __('No item to display');
                }
            } else {
                echo __('Connection failed');
            }

            echo "</td></tr>\n";
        }

        echo "</table></div>";
    }


    /**
     * Display debug information for current object.
     *
     * @return void
     */
    public function showDebug()
    {

        NotificationEvent::debugEvent($this);
        $this->showLdapDebug();
    }

    public function getUnicityFieldsToDisplayInErrorMessage()
    {

        return ['id'          => __('ID'),
                     'entities_id' => Entity::getTypeName(1)];
    }


    public function getUnallowedFieldsForUnicity()
    {

        return array_merge(
            parent::getUnallowedFieldsForUnicity(),
            ['auths_id', 'date_sync', 'entities_id', 'last_login', 'profiles_id']
        );
    }


    /**
     * Get a unique generated token.
     *
     * @param string $field Field storing the token
     *
     * @return string
     */
    public static function getUniqueToken($field = 'personal_token')
    {
        global $DB;

        $ok = false;
        do {
            $key    = Toolbox::getRandomString(40);
            $row = $DB->request([
               'COUNT'  => 'cpt',
               'FROM'   => self::getTable(),
               'WHERE'  => [$field => $key]
            ])->next();

            if ($row['cpt'] == 0) {
                return $key;
            }
        } while (!$ok);
    }


    /**
     * Get token of a user. If not exists generate it.
     *
     * @param integer $ID    User ID
     * @param string  $field Field storing the token
     *
     * @return string|boolean User token, false if user does not exist
     */
    public static function getToken($ID, $field = 'personal_token')
    {

        $user = new self();
        if ($user->getFromDB($ID)) {
            return $user->getAuthToken($field);
        }

        return false;
    }

    /**
     * Get token of a user. If it does not exists  then generate it.
     *
     * @since 9.4
     *
     * @param string $field the field storing the token
     * @param boolean $force_new force generation of a new token
     *
     * @return string|false token or false in case of error
     */
    public function getAuthToken($field = 'personal_token', $force_new = false)
    {
        global $CFG_GLPI;

        if ($this->isNewItem()) {
            return false;
        }

        // check date validity for cookie token
        $outdated = false;
        if ($field === 'cookie_token') {
            $date_create = new DateTime($this->fields[$field . "_date"]);
            $date_expir  = $date_create->add(new DateInterval('PT' . $CFG_GLPI["login_remember_time"] . 'S'));

            if ($date_expir < new DateTime()) {
                $outdated = true;
            }
        }

        // token exists, is not oudated, and we may use it
        if (!empty($this->fields[$field]) && !$force_new && !$outdated) {
            return $this->fields[$field];
        }

        // else get a new token
        $token = self::getUniqueToken($field);

        // for cookie token, we need to store it hashed
        $hash = $token;
        if ($field === 'cookie_token') {
            $hash = Auth::getPasswordHash($token);
        }

        // save this token in db
        $this->update(['id'             => $this->getID(),
                       $field           => $hash,
                       $field . "_date" => $_SESSION['glpi_currenttime']]);

        return $token;
    }


    /**
     * Get name of users using default passwords
     *
     * @return string[]
     */
    public static function checkDefaultPasswords()
    {
        global $DB;

        $passwords = ['itsm'      => 'itsm',
                           'tech'      => 'tech',
                           'normal'    => 'normal',
                           'post-only' => 'postonly'];
        $default_password_set = [];

        $crit = ['FIELDS'     => ['name', 'password'],
                      'is_active'  => 1,
                      'is_deleted' => 0,
                      'name'       => array_keys($passwords)];

        foreach ($DB->request('glpi_users', $crit) as $data) {
            if (Auth::checkPassword($passwords[strtolower($data['name'])], $data['password'])) {
                $default_password_set[] = $data['name'];
            }
        }

        return $default_password_set;
    }


    /**
     * Get picture URL from picture field.
     *
     * @since 0.85
     *
     * @param string $picture Picture field value
     *
     * @return string
     */
    public static function getURLForPicture($picture)
    {
        global $CFG_GLPI;

        $url = Toolbox::getPictureUrl($picture);
        if (null !== $url) {
            return $url;
        }

        return $CFG_GLPI["root_doc"] . "/pics/picture.png";
    }


    /**
     * Get thumbnail URL from picture field.
     *
     * @since 0.85
     *
     * @param string $picture Picture field value
     *
     * @return string
     */
    public static function getThumbnailURLForPicture($picture)
    {
        global $CFG_GLPI;

        // prevent xss
        $picture = Html::cleanInputText($picture);

        if (!empty($picture)) {
            $tmp = explode(".", $picture);
            if (count($tmp) == 2) {
                return $CFG_GLPI["root_doc"] . "/front/document.send.php?file=" . $tmp[0] .
                       "_min." . $tmp[1];
            }
            return $CFG_GLPI["root_doc"] . "/pics/picture_min.png";
        }
        return $CFG_GLPI["root_doc"] . "/pics/picture_min.png";
    }


    /**
     * Drop existing files for user picture.
     *
     * @since 0.85
     * @deprecated 2.0.0
     *
     * @param string $picture Picture field value
     *
     * @return void
     */
    public static function dropPictureFiles($picture)
    {

        if (!empty($picture)) {
            // unlink main file
            if (file_exists(GLPI_PICTURE_DIR . "/$picture")) {
                @unlink(GLPI_PICTURE_DIR . "/$picture");
            }
            // unlink Thunmnail
            $tmp = explode(".", $picture);
            if (count($tmp) == 2) {
                if (file_exists(GLPI_PICTURE_DIR . "/" . $tmp[0] . "_min." . $tmp[1])) {
                    @unlink(GLPI_PICTURE_DIR . "/" . $tmp[0] . "_min." . $tmp[1]);
                }
            }
        }
    }

    public function getRights($interface = 'central')
    {

        $values = parent::getRights();
        //TRANS: short for : Add users from an external source
        $values[self::IMPORTEXTAUTHUSERS] = ['short' => __('Add external'),
                                                  'long'  => __('Add users from an external source')];
        //TRANS: short for : Read method for user authentication and synchronization
        $values[self::READAUTHENT]        = ['short' => __('Read auth'),
                                                  'long'  => __('Read user authentication and synchronization method')];
        //TRANS: short for : Update method for user authentication and synchronization
        $values[self::UPDATEAUTHENT]      = ['short' => __('Update auth and sync'),
                                                  'long'  => __('Update method for user authentication and synchronization')];

        return $values;
    }


    /**
     * Retrieve the list of LDAP field names from a list of fields
     * allow pattern substitution, e.g. %{name}.
     *
     * @since 9.1
     *
     * @param string[] $map array of fields
     *
     * @return string[]
     */
    private static function getLdapFieldNames(array $map)
    {

        $ret =  [];
        foreach ($map as $v) {
            /** @var array $reg */
            if (preg_match_all('/%{(.*)}/U', $v, $reg)) {
                // e.g. "%{country} > %{city} > %{site}"
                foreach ($reg [1] as $f) {
                    $ret [] = $f;
                }
            } else {
                // single field name
                $ret [] = $v;
            }
        }
        return $ret;
    }


    /**
     * Retrieve the value of a fields from a LDAP result applying needed substitution of %{value}.
     *
     * @since 9.1
     *
     * @param string $map String with field format
     * @param array  $res LDAP result
     *
     * @return string
     */
    private static function getLdapFieldValue($map, array $res)
    {

        $map = Toolbox::unclean_cross_side_scripting_deep($map);
        $ret = preg_replace_callback(
            '/%{(.*)}/U',
            function ($matches) use ($res) {
                return (isset($res[0][$matches[1]][0]) ? $res[0][$matches[1]][0] : '');
            },
            $map
        );

        return $ret == $map ? (isset($res[0][$map][0]) ? $res[0][$map][0] : '') : $ret;
    }

    /**
     * Get/Print the switch language form.
     *
     * @param boolean $display Whether to display or return output
     * @param array   $options Options
     *    - string   value       Selected language value
     *    - boolean  showbutton  Whether to display or not submit button
     *
     * @return void|string Nothing if displayed, string to display otherwise
     */
    public function showSwitchLangForm($display = true, array $options = [])
    {

        $params = [
           'value'        => $_SESSION["glpilanguage"],
           'display'      => false,
           'showbutton'   => true
        ];

        foreach ($options as $key => $value) {
            $params[$key] = $value;
        }

        $out = '';
        $out .= "<form aria-label='Switch Language' method='post' name='switchlang' action='" . User::getFormURL() . "' autocomplete='off'>";
        $out .= "<p class='center'>";
        $out .= Dropdown::showLanguages("language", $params);
        if ($params['showbutton'] === true) {
            $out .= "&nbsp;<input type='submit' name='update' value=\"" . _sx('button', 'Save') . "\" class='submit'>";
        }
        $out .= "</p>";
        $out .= Html::closeForm(false);

        if ($display === true) {
            echo $out;
        } else {
            return $out;
        }
    }

    /**
     * Get list of entities ids for current user.
     *
     * @return integer[]
     */
    private function getEntities()
    {
        //get user entities
        if ($this->entities == null) {
            $this->entities = Profile_User::getUserEntities($this->fields['id'], true);
        }
        return $this->entities;
    }


    /**
     * Give cron information.
     *
     * @param string $name Task's name
     *
     * @return array
     */
    public static function cronInfo(string $name): array
    {

        $info = [];
        switch ($name) {
            case 'passwordexpiration':
                $info = [
                   'description' => __('Handle users passwords expiration policy'),
                   'parameter'   => __('Maximum expiration notifications to send at once'),
                ];
                break;
        }
        return $info;
    }

    /**
     * Cron that notify users about when their password expire and deactivate their account
     * depending on password expiration policy.
     *
     * @param CronTask $task
     *
     * @return integer
     */
    public static function cronPasswordExpiration(CronTask $task)
    {
        global $CFG_GLPI, $DB;

        $expiration_delay   = (int)$CFG_GLPI['password_expiration_delay'];
        $notice_time        = (int)$CFG_GLPI['password_expiration_notice'];
        $notification_limit = (int)$task->fields['param'];
        $lock_delay         = (int)$CFG_GLPI['password_expiration_lock_delay'];

        if (-1 === $expiration_delay || (-1 === $notice_time && -1 === $lock_delay)) {
            // Nothing to do if passwords does not expire
            // or if password expires without notice and with no lock delay
            return 0;
        }

        // Notify users about expiration of their password.
        $to_notify_count = 0;
        if (-1 !== $notice_time) {
            $notification_request = [
               'FROM'      => self::getTable(),
               'LEFT JOIN' => [
                  Alert::getTable() => [
                     'ON' => [
                        Alert::getTable() => 'items_id',
                        self::getTable()  => 'id',
                        [
                           'AND' => [
                              Alert::getTableField('itemtype') => self::getType(),
                           ]
                        ],
                     ]
                  ]
               ],
               'WHERE'     => [
                  self::getTableField('is_deleted') => 0,
                  self::getTableField('is_active')  => 1,
                  self::getTableField('authtype')   => Auth::DB_GLPI,
                  new QueryExpression(
                      sprintf(
                          'NOW() > ADDDATE(%s, INTERVAL %s DAY)',
                          $DB->quoteName(self::getTableField('password_last_update')),
                          $expiration_delay - $notice_time
                      )
                  ),
                  // Get only users that has not yet been notified within last day
                  'OR'                              => [
                     [Alert::getTableField('date') => null],
                     [Alert::getTableField('date') => ['<', new QueryExpression('CURRENT_TIMESTAMP() - INTERVAL 1 day')]],
                  ],
               ],
            ];

            $to_notify_count_request = array_merge(
                $notification_request,
                [
                  'COUNT'  => 'cpt',
                ]
            );
            $to_notify_count = $DB->request($to_notify_count_request)->next()['cpt'];

            $notification_data_request  = array_merge(
                $notification_request,
                [
                  'SELECT'    => [
                     self::getTableField('id as user_id'),
                     Alert::getTableField('id as alert_id'),
                  ],
                  'LIMIT'     => $notification_limit,
                ]
            );
            $notification_data_iterator = $DB->request($notification_data_request);

            foreach ($notification_data_iterator as $notification_data) {
                $user_id  = $notification_data['user_id'];
                $alert_id = $notification_data['alert_id'];

                $user = new User();
                $user->getFromDB($user_id);

                $is_notification_send = NotificationEvent::raiseEvent(
                    'passwordexpires',
                    $user,
                    ['entities_id' => 0] // Notication on root entity (glpi_users.entities_id is only a pref)
                );
                if (!$is_notification_send) {
                    continue;
                }

                $task->addVolume(1);

                $alert = new Alert();

                // Delete existing alert if any
                if (null !== $alert_id) {
                    $alert->delete(['id' => $alert_id]);
                }

                // Add an alert to not warn user for at least one day
                $alert->add(
                    [
                      'itemtype' => 'User',
                      'items_id' => $user_id,
                      'type'     => Alert::NOTICE,
                    ]
                );
            }
        }

        // Disable users if their password has expire for too long.
        if (-1 !== $lock_delay) {
            $DB->update(
                self::getTable(),
                [
                  'is_active'         => 0,
                  'cookie_token'      => null,
                  'cookie_token_date' => null,
                ],
                [
                  'is_deleted' => 0,
                  'is_active'  => 1,
                  'authtype'   => Auth::DB_GLPI,
                  new QueryExpression(
                      sprintf(
                          'NOW() > ADDDATE(ADDDATE(%s, INTERVAL %d DAY), INTERVAL %s DAY)',
                          $DB->quoteName(self::getTableField('password_last_update')),
                          $expiration_delay,
                          $lock_delay
                      )
                  ),
                ]
            );
        }

        return -1 !== $notice_time && $to_notify_count > $notification_limit
           ? -1 // -1 for partial process (remaining notifications to send)
           : 1; // 1 for fully process
    }

    /**
     * Get password expiration time.
     *
     * @return null|int Password expiration time, or null if expiration mechanism is not active.
     */
    public function getPasswordExpirationTime()
    {
        global $CFG_GLPI;

        if (!array_key_exists('id', $this->fields) || $this->fields['id'] < 1) {
            return null;
        }

        $expiration_delay = (int)$CFG_GLPI['password_expiration_delay'];

        if (-1 === $expiration_delay) {
            return null;
        }

        return strtotime(
            '+ ' . $expiration_delay . ' days',
            strtotime($this->fields['password_last_update'])
        );
    }

    /**
     * Check if password should be changed (if it expires soon).
     *
     * @return boolean
     */
    public function shouldChangePassword()
    {
        global $CFG_GLPI;

        if ($this->hasPasswordExpired()) {
            return true; // too late to change password, but returning false would not be logical here
        }

        $expiration_time = $this->getPasswordExpirationTime();
        if (null === $expiration_time) {
            return false;
        }

        $notice_delay    = (int)$CFG_GLPI['password_expiration_notice'];
        if (-1 === $notice_delay) {
            return false;
        }

        $notice_time = strtotime('- ' . $notice_delay . ' days', $expiration_time);

        return $notice_time < time();
    }

    /**
     * Check if password expired.
     *
     * @return boolean
     */
    public function hasPasswordExpired()
    {

        $expiration_time = $this->getPasswordExpirationTime();
        if (null === $expiration_time) {
            return false;
        }

        return $expiration_time < time();
    }

    public static function getFriendlyNameSearchCriteria(string $filter): array
    {
        $table     = self::getTable();
        $login     = DBmysql::quoteName("$table.name");
        $firstname = DBmysql::quoteName("$table.firstname");
        $lastname  = DBmysql::quoteName("$table.realname");

        $filter = strtolower($filter);
        $filter_no_spaces = str_replace(" ", "", $filter);

        return [
           'OR' => [
              ['RAW' => ["LOWER($login)" => ['LIKE', "%$filter%"]]],
              ['RAW' => ["LOWER(REPLACE(CONCAT($firstname, $lastname), ' ', ''))" => ['LIKE', "%$filter_no_spaces%"]]],
              ['RAW' => ["LOWER(REPLACE(CONCAT($lastname, $firstname), ' ', ''))" => ['LIKE', "%$filter_no_spaces%"]]],
           ]
        ];
    }

    public static function getFriendlyNameFields(string $alias = "name")
    {
        $config = Config::getConfigurationValues('core');
        if ($config['names_format'] == User::FIRSTNAME_BEFORE) {
            $first = "firstname";
            $second = "realname";
        } else {
            $first = "realname";
            $second = "firstname";
        }

        $table  = self::getTable();
        $first  = DB::quoteName("$table.$first");
        $second = DB::quoteName("$table.$second");
        $alias  = DB::quoteName($alias);
        $name   = DB::quoteName(self::getNameField());

        return new QueryExpression(
            "IF(
            $first <> '' && $second <> '',
            CONCAT($first, ' ', $second),
            $name
         ) AS $alias"
        );
    }

    public static function getIcon()
    {
        return "fas fa-user";
    }

    /**
     * Add groups stored in "_ldap_rules/groups_id" special input
     */
    public function applyGroupsRules()
    {
        if (!isset($this->input["_ldap_rules"]['groups_id'])) {
            return;
        }

        $group_ids = array_unique($this->input["_ldap_rules"]['groups_id']);
        foreach ($group_ids as $group_id) {
            $group_user = new Group_User();

            $data = [
               'groups_id' => $group_id,
               'users_id'  => $this->getId()
            ];

            if (!$group_user->getFromDBByCrit($data)) {
                $group_user->add($data);
            }
        }
    }

    /**
     * Find one user which match the given token and asked for a password reset
     * less than one day ago
     *
     * @param string $token password_forget_token
     *
     * @return User|null The matching user or null if zero or more than one user
     *                   were found
     */
    public static function getUserByForgottenPasswordToken(
        string $token
    ): ?User {
        global $DB;

        if (empty($token)) {
            return null;
        }

        // Find users which match the given token and asked for a password reset
        // less than one day ago
        $iterator = $DB->request([
           'SELECT' => 'id',
           'FROM'   => self::getTable(),
           'WHERE'  => [
              'password_forget_token'       => $token,
              new \QueryExpression('NOW() < ADDDATE(' . $DB->quoteName('password_forget_token_date') . ', INTERVAL 1 DAY)')
           ]
        ]);

        // Check that we found exactly one user
        if (count($iterator) !== 1) {
            return null;
        }

        // Get first row, should use current() when updated to GLPI 10
        $data = iterator_to_array($iterator);
        $data = array_pop($data);

        // Try to load the user
        $user = new self();
        if (!$user->getFromDB($data['id'])) {
            return null;
        }

        return $user;
    }
}
