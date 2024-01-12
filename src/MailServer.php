<?php

namespace itsmng;

use Config;
use Plugin;

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
     *
     * @return array  parsed arguments (address, port, mailbox, type, ssl, tls, validate-cert
     *                norsh, secure and debug) : options are empty if not set
     *                and options have boolean values if set
     **/
    static function parseMailServerConnectString($value, $forceport = false) : array
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
        $tab['type'] = in_array($type, array_keys(self::getMailServerProtocols())) ? $type : '';

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
     *
     * @return array
     **/
    static function showMailServerConfig($value): array
    {
        $data = array();

        if (!Config::canUpdate()) {
            return false;
        }

        $tab = self::parseMailServerConnectString($value);

        // Server Address
        $data['address'] = $tab['address'];

        $values = [];
        $protocols = self::getMailServerProtocols();
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
            '' => '-----',
            '/norsh' => __('NORSH')
        ];

        $svalue = ($tab['norsh'] === true ? '/norsh' : '');


        $data['norsh'] = $values;
        $data['select_norsh'] = $svalue;

        $values = [ //TRANS: imap_open option see http://www.php.net/manual/en/function.imap-open.php
            '' => '-----',
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
}
