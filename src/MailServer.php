<?php

namespace itsmng;

use Config;
use Plugin;
use Toolbox;

class MailServer
{
    /**
     * Retuns available mail servers protocols.
     *
     * For each returned element:
     *  - key is type used in connection string;
     *  - 'label' field is the label to display;
     *  - 'protocol_class' field is the protocol class to use (see Laminas\Mail\Protocol\Imap | Laminas\Mail\Protocol\Pop3);
     *  - 'storage_class' field is the storage class to use (see Laminas\Mail\Storage\Imap | Laminas\Mail\Storage\Pop3).
     *
     * @return array
     */
    private static function getMailServerProtocols(): array
    {
        $protocols = [
            'imap' => [
                //TRANS: IMAP mail server protocol
                'label'    => __('IMAP'),
                'protocol' => 'Laminas\Mail\Protocol\Imap',
                'storage'  => 'Laminas\Mail\Storage\Imap',
            ],
            'pop'  => [
                //TRANS: POP3 mail server protocol
                'label'    => __('POP'),
                'protocol' => 'Laminas\Mail\Protocol\Pop3',
                'storage'  => 'Laminas\Mail\Storage\Pop3',
            ]
        ];

        $additionnal_protocols = Plugin::doHookFunction('mail_server_protocols', []);
        if (is_array($additionnal_protocols)) {
            foreach ($additionnal_protocols as $key => $additionnal_protocol) {
                if (array_key_exists($key, $protocols)) {
                    trigger_error(
                        sprintf('Protocol "%s" is already defined and cannot be overwritten.', $key),
                        E_USER_WARNING
                    );
                    continue; // already exists, do not overwrite
                }

                if (
                    !array_key_exists('label', $additionnal_protocol)
                    || !array_key_exists('protocol', $additionnal_protocol)
                    || !array_key_exists('storage', $additionnal_protocol)
                ) {
                    trigger_error(
                        sprintf('Invalid specs for protocol "%s".', $key),
                        E_USER_WARNING
                    );
                    continue;
                }
                $protocols[$key] = $additionnal_protocol;
            }
        } else {
            trigger_error(
                'Invalid value returned by "mail_server_protocols" hook.',
                E_USER_WARNING
            );
        }

        return $protocols;
    }

    /**
     * Parse imap open connect string
     *
     * @since ITSM 2.0
     *
     * @param string  $value      connect string
     * @param boolean $forceport  force compute port if not set
     * @param boolean $allow_plugins_protocols allow plugins protocols
     *
     * @return array  parsed arguments (address, port, mailbox, type, ssl, tls, validate-cert
     *                norsh, secure and debug) : options are empty if not set
     *                and options have boolean values if set
     **/
    public static function parseMailServerConnectString($value, $forceport = false, bool $allow_plugins_protocols = true): array
    {

        $tab = [];
        if (strstr($value, ":")) {
            $tab['address'] = str_replace("{", "", preg_replace("/:.*/", "", $value));
            $tab['port']    = preg_replace("/.*:/", "", preg_replace("/\/.*/", "", $value));
        } else {
            if (strstr($value, "/")) {
                $tab['address'] = str_replace("{", "", preg_replace("/\/.*/", "", $value));
            } else {
                $tab['address'] = str_replace("{", "", preg_replace("/}.*/", "", $value));
            }
            $tab['port'] = "";
        }
        $tab['mailbox'] = preg_replace("/.*}/", "", $value);

        // type follows first found "/" and ends on next "/" (or end of server string)
        // server string is surrounded by "{}" and can be followed by a folder name
        // i.e. "{mail.domain.org/imap/ssl}INBOX", or "{mail.domain.org/pop}"
        $type = preg_replace('/^\{[^\/]+\/([^\/]+)(?:\/.+)*\}.*/', '$1', $value);
        $tab['type'] = in_array($type, array_keys(self::getMailServerProtocols($allow_plugins_protocols))) ? $type : '';

        $tab['ssl'] = false;
        if (strstr($value, "/ssl")) {
            $tab['ssl'] = true;
        }

        if ($forceport && empty($tab['port'])) {
            if ($tab['type'] == 'pop') {
                if ($tab['ssl']) {
                    $tab['port'] = 110;
                } else {
                    $tab['port'] = 995;
                }
            }
            if ($tab['type'] = 'imap') {
                if ($tab['ssl']) {
                    $tab['port'] = 993;
                } else {
                    $tab['port'] = 143;
                }
            }
        }
        $tab['tls'] = '';
        if (strstr($value, "/tls")) {
            $tab['tls'] = true;
        }
        if (strstr($value, "/notls")) {
            $tab['tls'] = false;
        }
        $tab['validate-cert'] = '';
        if (strstr($value, "/validate-cert")) {
            $tab['validate-cert'] = true;
        }
        if (strstr($value, "/novalidate-cert")) {
            $tab['validate-cert'] = false;
        }
        $tab['norsh'] = '';
        if (strstr($value, "/norsh")) {
            $tab['norsh'] = true;
        }
        $tab['secure'] = '';
        if (strstr($value, "/secure")) {
            $tab['secure'] = true;
        }
        $tab['debug'] = '';
        if (strstr($value, "/debug")) {
            $tab['debug'] = true;
        }

        return $tab;
    }

    /**
     * Display a mail server configuration form
     *
     * @param string $value  host connect string ex {localhost:993/imap/ssl}INBOX
     * @param boolean $allow_plugins_protocols
     *
     * @return array
     **/
    public static function showMailServerConfig($value, bool $allow_plugins_protocols): array
    {
        $data = array();

        if (!Config::canUpdate()) {
            return false;
        }

        $tab = self::parseMailServerConnectString($value);

        // Server Address
        $data['address'] = $tab['address'];

        $values = [];
        $protocols = self::getMailServerProtocols($allow_plugins_protocols);
        $data['protocols'] = $protocols;

        foreach ($protocols as $key => $params) {
            $values['/' . $key] = $params['label'];
        }
        $svalue = (!empty($tab['type']) ? '/' . $tab['type'] : '');

        $data['types'] = $values;
        $data['select_type'] = $svalue;

        $values = [ //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
            '' => '-----',
            '/ssl' => __('SSL')
        ];

        $svalue = ($tab['ssl'] ? '/ssl' : '');
        $data['ssl'] = $values;
        $data['select_ssl'] = $svalue;

        $values = [ //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
            '/tls' => __('TLS'),
            //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
            '/notls' => __('NO-TLS'),
        ];

        $data['tls_types'] = $values;

        $svalue = '';
        if (($tab['tls'] === true)) {
            $svalue = '/tls';
        }
        if (($tab['tls'] === false)) {
            $svalue = '/notls';
        }

        $data['select_tls'] = $svalue;

        $values = [ //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
            '/novalidate-cert' => __('NO-VALIDATE-CERT'),
            //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
            '/validate-cert' => __('VALIDATE-CERT'),
        ];

        $svalue = '';
        if (($tab['validate-cert'] === false)) {
            $svalue = '/novalidate-cert';
        }
        if (($tab['validate-cert'] === true)) {
            $svalue = '/validate-cert';
        }

        $data['validate_cert'] = $values;
        $data['select_validate_cert'] = $svalue;

        $values = [ //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
            '/rsh' => '-----',
            '/norsh' => __('NORSH')
        ];

        $svalue = ($tab['norsh'] === true ? '/norsh' : '');


        $data['norsh'] = $values;
        $data['select_norsh'] = $svalue;

        $values = [ //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
            '/nosecure' => '-----',
            '/secure' => __('SECURE')
        ];

        $svalue = ($tab['secure'] === true ? '/secure' : '');

        $data['secure'] = $values;
        $data['select_secure'] = $svalue;

        $values = [ //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
            '' => 'No ' . __('DEBUG'),
            '/debug' => __('DEBUG')
        ];

        $svalue = ($tab['debug'] === true ? '/debug' : '');

        $data['debug'] = $values;
        $data['select_debug'] = $svalue;

        return $data;
    }

    /**
     * Return a mail server, mail collector config form
     *
     * @return void
     **/
    public static function showMailServerConfigForm($action, $fields, $is_ID, $ID, $allow_plugins_protocols = true)
    {
        $data = self::parseMailServerConnectString($fields["connect_string"] ?? '', $allow_plugins_protocols);

        $FromMailServerConfig = self::showMailServerConfig($fields["connect_string"] ?? '', false);
        foreach ($FromMailServerConfig['protocols'] as $key => $params) {
            $protocols['/' . $key] = $params['label'];
        }

        $authmail = false;
        $mailcollector = false;

        if ($action == 'authmail') {
            $authmail = true;
            $title = __('Email server');
        } elseif ($action == 'mailcollector') {
            $mailcollector = true;
            $title = __('Receiver');

            if (!empty($fields['host'])) {
                $field = self::getMailCollectorConfig($fields['host']);
            }
        }

        $form = [
            'action' => Toolbox::getItemTypeFormURL($action),
            'buttons' => [
                [
                    'type' => 'submit',
                    'name' => $is_ID ? 'add' : 'update',
                    'value' => $is_ID ? __('Add') : __('Update'),
                    'class' => 'btn btn-secondary',
                ],
                $is_ID ? [] : [
                    'type' => 'submit',
                    'name' => 'purge',
                    'value' => __('Delete permanently'),
                    'class' => 'btn btn-secondary'
                ]
            ],
            'content' => [
                ($title) => [
                    'visible' => true,
                    'inputs' => [
                        $is_ID ? [] : [
                            'type' => 'hidden',
                            'name' => 'id',
                            'value' => $ID
                        ],
                        __('Name') => [
                            'name' => 'name',
                            'type' => 'text',
                            'value' => $fields['name'] ?? '',
                        ],
                        __('Active') => [
                            'name' => 'is_active',
                            'type' => 'checkbox',
                            'value' => $fields['is_active'] ?? '',
                        ],
                        __('Email domain Name (users email will be login@domain)') => $mailcollector ? [] : [
                            'type' => 'text',
                            'name' => 'host',
                            'value' => $fields['host'] ?? ''
                        ],
                        __('Server') => [
                            'name' => 'mail_server',
                            'type' => 'text',
                            'value' => $field['host'] ?? $FromMailServerConfig['address'] ?? '',
                            'required' => true,
                        ],
                        __('Protocol') => [
                            'name' => 'server_type',
                            'type' => 'select',
                            'values' => $protocols,
                            'value' => $field['protocol'] ?? $FromMailServerConfig['select_type'] ?? '',
                        ],
                        __('Security') => [
                            'name' => 'server_ssl',
                            'type' => 'select',
                            'values' => $FromMailServerConfig['ssl'],
                            'value' => $field['security'] ?? $FromMailServerConfig['select_ssl'] ?? '',
                        ],
                        __('Encryption') => [
                            'name' => 'server_tls',
                            'type' => 'select',
                            'values' => $FromMailServerConfig['tls_types'],
                            'value' => $field['tls'] ?? $FromMailServerConfig['select_tls'] ?? '',
                        ],
                        __('Verify Certificat') => [
                            'name' => 'server_cert',
                            'type' => 'select',
                            'values' => $FromMailServerConfig['validate_cert'],
                            'value' => $field['cert-validation'] ?? $FromMailServerConfig['select_validate_cert'] ?? '',
                        ],
                        __('RSH') => [
                            'name' => 'server_rsh',
                            'type' => 'select',
                            'values' => $FromMailServerConfig['norsh'],
                            'value' => $field['norsh'] ?? $FromMailServerConfig['select_norsh'] ?? '',
                        ],
                        __('Secure') => [
                            'name' => 'server_secure',
                            'type' => 'select',
                            'values' => $FromMailServerConfig['secure'],
                            'value' => $field['secure'] ?? $FromMailServerConfig['select_secure'] ?? '',
                        ],
                        __('Debug') => [
                            'name' => 'server_debug',
                            'type' => 'select',
                            'values' => $FromMailServerConfig['debug'],
                            'value' => $field['debug'] ?? $FromMailServerConfig['select_debug'] ?? '',
                        ],
                        __('Incoming mail folder (optional, often INBOX)') => [
                            'name' => 'server_mailbox',
                            'type' => 'text',
                            'value' => $field['mailbox'] ?? $data['mailbox'] ?? '',
                        ],
                        __('Port') => [
                            'name' => 'server_port',
                            'type' => 'number',
                            'value' => $field['port'] ?? $data['port'] ?? '993',
                            'required' => true,
                        ],
                        __('Connection string') => [
                            'name' => 'imap_string',
                            'type' => 'text',
                            'disabled' => true,
                            'value' => ($fields["connect_string"]) ?? $fields['host'] ?? '',
                        ],
                        __('Login') => $authmail ? [] : [
                            'name' => 'login',
                            'type' => 'text',
                            'value' => $fields['login'] ?? ''
                        ],
                        __('Password') => $authmail ? [] : [
                            'name' => 'passwd',
                            'type' => 'password'
                        ],
                        __('Accepted mail archive folder (optional)') => $authmail ? [] : [
                            'name' => 'accepted',
                            'type' => 'text',
                            'value' => $fields['accepted'] ?? ''
                        ],
                        __('Refused mail archive folder (optional)') => $authmail ? [] : [
                            'name' => 'refused',
                            'type' => 'text',
                            'value' => $fields['refused'] ?? ''
                        ],
                        __('Maximum size of each file imported by the mails receiver') => $authmail ? [] : [
                            'name' => 'filesize_max',
                            'type' => 'select',
                            'values' => self::dropdown_upload_size(),
                            'value' => $fields['filesize_max'] ?? ''
                        ],
                        __('Use mail date, instead of collect one') => $authmail ? [] : [
                            'name' => 'use_mail_date',
                            'type' => 'checkbox',
                            'value' => $fields['use_mail_date'] ?? ''
                        ],
                        __('Use Reply-To as requester (when available)') => $authmail ? [] : [
                            'name' => 'requester_field',
                            'type' => 'checkbox',
                            'value' => $fields['requester_field'] ?? ''
                        ],
                        __('Add CC users as observer') => $authmail ? [] : [
                            'name' => 'add_cc_to_observer',
                            'type' => 'checkbox',
                            'value' => $fields['add_cc_to_observer'] ?? ''
                        ],
                        __('Collect only unread mail') => $authmail ? [] : [
                            'name' => 'collect_only_unread',
                            'type' => 'checkbox',
                            'value' => $fields['collect_only_unread'] ?? ''
                        ],
                        __('Comments') => [
                            'name' => 'comment',
                            'type' => 'textarea',
                            'value' => $fields['comment'] ?? ''
                        ],

                    ],
                ]
            ]
        ];

        renderTwigForm($form);
    }

    public static function dropdown_upload_size(): array
    {
        $i = 0;
        $size = 0;
        $data = array();

        $data[$i] = __('No import');
        for ($i = 1; $i < 100; $i++) {
            $size += 1048576;
            $data[$size] = $i . ' Mio';
        }
        return $data;
    }

    public static function getMailCollectorConfig($entry): bool|array
    {

        if (empty($entry)) {
            return false;
        }

        $data = array();

        $pattern = '/{([^:]+):(\d+)\/([^\/]+)\/([^\/]+)\/([^\/]+)\/([^\/]+)\/([^}]+)/';
        preg_match($pattern, $entry, $matches);

        $key = ['host', 'port', 'protocol', 'security', 'cert-validation', 'tls', 'norsh'];
        foreach ($key as $k => $v) {
            if ($v == 'protocol' || $v == 'security' || $v == 'cert-validation' || $v == 'tls' || $v == 'norsh') {
                $data[$v] = '/' . $matches[$k + 1] ?? '';
            } else {
                $data[$v] = $matches[$k + 1] ?? '';
            }
        }

        if (strstr($entry, '/secure')) {
            $data['secure'] = '/secure';
            $data['norsh'] = str_replace('/secure', '', $data['norsh']);
        }
        if (strstr($entry, '/debug')) {
            $data['debug'] = '/debug';
            $data['norsh'] = str_replace('/debug', '', $data['norsh']);
        }

        $mailbox = explode('}', $entry, 2);
        $data['mailbox'] = $mailbox[1];

        return $data;
    }
}
