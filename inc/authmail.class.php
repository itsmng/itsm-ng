<?php

use itsmng\MailServer;

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
 *  Class used to manage Auth mail config
 */
class AuthMail extends CommonDBTM
{


    // From CommonDBTM
    public $dohistory = true;

    static $rightname = 'config';

    static function getTypeName($nb = 0)
    {
        return _n('Mail server', 'Mail servers', $nb);
    }

    function prepareInputForUpdate($input)
    {



        if (isset($input['mail_server']) && !empty($input['mail_server'])) {
            $input["connect_string"] = Toolbox::constructMailServerConfig($input);
        }
        return $input;
    }

    static function canCreate()
    {
        return static::canUpdate();
    }

    static function canPurge()
    {
        return static::canUpdate();
    }

    function prepareInputForAdd($input)
    {

        if (isset($input['mail_server']) && !empty($input['mail_server'])) {
            $input["connect_string"] = Toolbox::constructMailServerConfig($input);
        }
        return $input;
    }

    function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab(__CLASS__, $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }

    function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id'                 => 'common',
            'name'               => __('Email server')
        ];

        $tab[] = [
            'id'                 => '1',
            'table'              => $this->getTable(),
            'field'              => 'name',
            'name'               => __('Name'),
            'datatype'           => 'itemlink',
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '2',
            'table'              => $this->getTable(),
            'field'              => 'id',
            'name'               => __('ID'),
            'datatype'           => 'number',
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '3',
            'table'              => $this->getTable(),
            'field'              => 'host',
            'name'               => __('Server'),
            'datatype'           => 'string'
        ];

        $tab[] = [
            'id'                 => '4',
            'table'              => $this->getTable(),
            'field'              => 'connect_string',
            'name'               => __('Connection string'),
            'massiveaction'      => false,
            'datatype'           => 'string'
        ];

        $tab[] = [
            'id'                 => '6',
            'table'              => $this->getTable(),
            'field'              => 'is_active',
            'name'               => __('Active'),
            'datatype'           => 'bool'
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
            'id'                 => '16',
            'table'              => $this->getTable(),
            'field'              => 'comment',
            'name'               => __('Comments'),
            'datatype'           => 'text'
        ];

        return $tab;
    }

    /**
     * Print the auth mail form
     *
     * @param integer $ID      ID of the item
     * @param array   $options Options
     *
     * @return void|boolean (display) Returns false if there is a rights error.
     */
    function showForm($ID)
    {

        if (!Config::canUpdate()) {
            return false;
        }
        if (empty($ID)) {
            $this->getEmpty();
        } else {
            $this->getFromDB($ID);
        }

        $FromMailServerConfig = MailServer::showMailServerConfig($this->fields["connect_string"]);
        $data = MailServer::parseMailServerConnectString($this->fields["connect_string"]);

        foreach ($FromMailServerConfig['protocols'] as $key => $params) {
            $protocols['/' . $key] = $params['label'];
        }

        $form = [
            'action' => Toolbox::getItemTypeFormURL('authmail'),
            'buttons' => [
                [
                    'type' => 'submit',
                    'name' => $this->isNewID($ID) ? 'add' : 'update',
                    'value' => $this->isNewID($ID) ? __('Add') : __('Update'),
                    'class' => 'btn btn-secondary',
                ],
                $this->isNewID($ID) ? [] : [
                    'type' => 'submit',
                    'name' => 'purge',
                    'value' => __('Delete permanently'),
                    'class' => 'btn btn-secondary'
                ]
            ],
            'content' => [
                __('Test connection to email server') => [
                    'visible' => true,
                    'inputs' => [
                        $this->isNewID($ID) ? [] : [
                            'type' => 'hidden',
                            'name' => 'id',
                            'value' => $ID
                        ],
                        __('Name') => [
                            'name' => 'name',
                            'type' => 'text',
                            'value' => $this->fields['name'] ?? '',
                        ],
                        __('Active') => [
                            'name' => 'is_active',
                            'type' => 'checkbox',
                            'value' => $this->fields['is_active'] ?? '',
                        ],
                        __('Email domain Name (users email will be login@domain)') => [
                            'name' => 'host',
                            'type' => 'text',
                            'value' => $this->fields['host'] ?? '',
                        ],
                        __('Server') => [
                            'name' => 'mail_server',
                            'type' => 'text',
                            'value' => $FromMailServerConfig['address'] ?? '',
                        ],
                        __('Protocol') => [
                            'name' => 'server_type',
                            'type' => 'select',
                            'values' => $protocols,
                            'value' => $FromMailServerConfig['select_type'],
                        ],
                        __('Security') => [
                            'name' => 'server_ssl',
                            'type' => 'select',
                            'values' => $FromMailServerConfig['ssl'],
                            'value' => $FromMailServerConfig['select_ssl'],
                        ],
                        __('Encryption') => [
                            'name' => 'server_tls',
                            'type' => 'select',
                            'values' => $FromMailServerConfig['tls_types'],
                            'value' => $FromMailServerConfig['select_tls'],
                        ],
                        __('Verify Certificat') => [
                            'name' => 'server_cert',
                            'type' => 'select',
                            'values' => $FromMailServerConfig['validate_cert'],
                            'value' => $FromMailServerConfig['select_validate_cert'],
                        ],
                        __('RSH') => [
                            'name' => 'server_rsh',
                            'type' => 'select',
                            'values' => $FromMailServerConfig['norsh'],
                            'value' => $FromMailServerConfig['select_norsh'],
                        ],
                        __('Secure') => [
                            'name' => 'server_secure',
                            'type' => 'select',
                            'values' => $FromMailServerConfig['secure'],
                            'value' => $FromMailServerConfig['select_secure'],
                        ],
                        __('Debug') => [
                            'name' => 'server_debug',
                            'type' => 'select',
                            'values' => $FromMailServerConfig['debug'],
                            'value' => $FromMailServerConfig['select_debug'],
                        ],
                        __('Incoming mail folder (optional, often INBOX)') => [
                            'name' => 'server_mailbox',
                            'type' => 'text',
                            'value' => $data['mailbox'] ?? '',
                        ],
                        __('Port') => [
                            'name' => 'server_port',
                            'type' => 'text',
                            'value' => $data['port'] ?? '',
                        ],
                        __('Connection string') => [
                            'name' => 'imap_string',
                            'type' => 'text',
                            'disabled' => true,
                            'value' => ($this->fields["connect_string"]) ?? '',
                        ],
                    ],
                ]
            ]
        ];

        renderTwigForm($form);
    }

    /**
     * Show test mail form
     *
     * @return void
     */
    function showFormTestMail()
    {

        $ID = $this->getField('id');

        if ($this->getFromDB($ID)) {
            $form = [
                'action' => Toolbox::getItemTypeFormURL('authmail'),
                'buttons' => [
                    [
                        'type' => 'submit',
                        'name' => 'test',
                        'value' => __('Test'),
                        'class' => 'btn btn-secondary'
                    ]
                ],
                'content' => [
                    __('Test connection to email server') => [
                        'visible' => true,
                        'inputs' => [
                            ('') => [
                                'name' => 'imap_string',
                                'type' => 'hidden',
                                'value' => $this->fields['connect_string']
                            ],
                            __('Login') => [
                                'name' => 'imap_login',
                                'type' => 'text',
                            ],
                            __('Password') => [
                                'name' => 'imap_password',
                                'type' => 'password',
                            ]
                        ]
                    ]
                ]
            ];

            renderTwigForm($form);
        }
    }


    /**
     * Is the Mail authentication used?
     *
     * @return boolean
     */
    static function useAuthMail()
    {
        return (countElementsInTable('glpi_authmails', ['is_active' => 1]) > 0);
    }


    /**
     * Test a connexion to the IMAP/POP server
     *
     * @param string $connect_string mail server
     * @param string $login          user login
     * @param string $password       user password
     *
     * @return boolean Authentication succeeded?
     */
    static function testAuth($connect_string, $login, $password)
    {

        $auth = new Auth();
        return $auth->connection_imap(
            $connect_string,
            Toolbox::decodeFromUtf8($login),
            Toolbox::decodeFromUtf8($password)
        );
    }


    /**
     * Authenticate a user by checking a specific mail server
     *
     * @param object $auth        identification object
     * @param string $login       user login
     * @param string $password    user password
     * @param string $mail_method mail_method array to use
     *
     * @return object identification object
     */
    static function mailAuth($auth, $login, $password, $mail_method)
    {

        if (isset($mail_method["connect_string"]) && !empty($mail_method["connect_string"])) {
            $auth->auth_succeded = $auth->connection_imap(
                $mail_method["connect_string"],
                $login,
                $password
            );
            if ($auth->auth_succeded) {
                $auth->extauth      = 1;
                $auth->user_present = $auth->user->getFromDBbyName(addslashes($login));
                $auth->user->getFromIMAP($mail_method, Toolbox::decodeFromUtf8($login));
                //Update the authentication method for the current user
                $auth->user->fields["authtype"] = Auth::MAIL;
                $auth->user->fields["auths_id"] = $mail_method["id"];
            }
        }
        return $auth;
    }


    /**
     * Try to authenticate a user by checking all the mail server
     *
     * @param object  $auth     identification object
     * @param string  $login    user login
     * @param string  $password user password
     * @param integer $auths_id auths_id already used for the user (default 0)
     * @param boolean $break    if user is not found in the first directory,
     *                          stop searching or try the following ones (true by default)
     *
     * @return object identification object
     */
    static function tryMailAuth($auth, $login, $password, $auths_id = 0, $break = true)
    {

        if ($auths_id <= 0) {
            foreach ($auth->authtypes["mail"] as $mail_method) {
                if (!$auth->auth_succeded && $mail_method['is_active']) {
                    $auth = self::mailAuth($auth, $login, $password, $mail_method);
                } else {
                    if ($break) {
                        break;
                    }
                }
            }
        } else if (array_key_exists($auths_id, $auth->authtypes["mail"])) {
            //Check if the mail server indicated as the last good one still exists !
            $auth = self::mailAuth($auth, $login, $password, $auth->authtypes["mail"][$auths_id]);
        }
        return $auth;
    }

    function cleanDBonPurge()
    {
        Rule::cleanForItemCriteria($this, 'MAIL_SERVER');
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate && $item->can($item->getField('id'), READ)) {
            $ong = [];
            $ong[1] = _sx('button', 'Test');    // test connexion

            return $ong;
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($tabnum) {
            case 1:
                $item->showFormTestMail();
                break;
        }
        return true;
    }
}
