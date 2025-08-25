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

class Telemetry extends CommonGLPI
{
    public static function getTypeName($nb = 0)
    {
        return __('Telemetry');
    }

    /**
     * Grab telemetry information
     *
     * @return array
     */
    public static function getTelemetryInfos(bool $hide_sensitive_data = false): array
    {
        $data = [
           'glpi'   => self::grabGlpiInfos($hide_sensitive_data),
           'system' => [
              'db'           => self::grabDbInfos($hide_sensitive_data),
              'web_server'   => self::grabWebserverInfos($hide_sensitive_data),
              'php'          => self::grabPhpInfos($hide_sensitive_data),
              'os'           => self::grabOsInfos($hide_sensitive_data)
           ]
        ];

        return $data;
    }

    /**
     * Grab GLPI part information
     *
     * @return array
     */
    public static function grabGlpiInfos($hide_sensitive_data = false)
    {
        global $CFG_GLPI;

        $glpi = [
           'uuid'               => $hide_sensitive_data ? 'REDACTED' : self::getInstanceUuid(),
           'version'            => $hide_sensitive_data ? 'REDACTED' : ITSM_VERSION,
           'plugins'            => [],
           'default_language'   => $CFG_GLPI['language'],
           'install_mode'       => GLPI_INSTALL_MODE,
           'usage'              => [
              'avg_entities'          => self::getAverage('Entity'),
              'avg_computers'         => self::getAverage('Computer'),
              'avg_networkequipments' => self::getAverage('NetworkEquipment'),
              'avg_tickets'           => self::getAverage('Ticket'),
              'avg_problems'          => self::getAverage('Problem'),
              'avg_changes'           => self::getAverage('Change'),
              'avg_projects'          => self::getAverage('Project'),
              'avg_users'             => self::getAverage('User'),
              'avg_groups'            => self::getAverage('Group'),
              'ldap_enabled'          => AuthLDAP::useAuthLdap(),
              'mailcollector_enabled' => (MailCollector::countActiveCollectors() > 0),
              'notifications_modes'   => []
           ]
        ];

        $plugins = new Plugin();
        foreach ($plugins->getList(['directory', 'version']) as $plugin) {
            $glpi['plugins'][] = [
               'key'       => $plugin['directory'],
               'version'   => $hide_sensitive_data ? 'REDACTED' : $plugin['version']
            ];
        }

        if ($CFG_GLPI['use_notifications']) {
            foreach (array_keys(\Notification_NotificationTemplate::getModes()) as $mode) {
                if ($CFG_GLPI['notifications_' . $mode]) {
                    $glpi['usage']['notifications'][] = $mode;
                }
            }
        }

        return $glpi;
    }

    /**
     * Grab DB part information
     *
     * @return array
     */
    public static function grabDbInfos($hide_sensitive_data = false)
    {
        global $DB;

        $dbinfos = $DB->getInfo();

        $size_res = config::getAdapter()->request([
           'SELECT' => new \QueryExpression("ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS dbsize"),
           'FROM'   => 'information_schema.tables',
           'WHERE'  => ['table_schema' => $DB->dbdefault]
        ])->fetchAssociative();

        $db = [
           'engine'    => $dbinfos['Server Software'],
           'version'   => $hide_sensitive_data ? 'REDACTED' : $dbinfos['Server Version'],
           'size'      => $size_res['dbsize'],
           'log_size'  => '',
           'sql_mode'  => $dbinfos['Server SQL Mode']
        ];

        return $db;
    }

    /**
     * Grab web server part information
     *
     * @return array
     */
    public static function grabWebserverInfos($hide_sensitive_data = false)
    {
        global $CFG_GLPI;

        $server = [
           'engine'  => '',
           'version' => '',
        ];

        if (!filter_var(gethostbyname(parse_url($CFG_GLPI['url_base'], PHP_URL_HOST)), FILTER_VALIDATE_IP)) {
            // Do not try to get headers if hostname cannot be resolved
            return $server;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $CFG_GLPI['url_base']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        // Issue #3180 - disable SSL certificate validation (wildcard, self-signed)
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($response = curl_exec($ch)) {
            $headers = substr($response, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
            $header_matches = [];
            if (preg_match('/^Server: (?<engine>[^ ]+)\/(?<version>[^ ]+)/im', $headers, $header_matches)) {
                $server['engine']  = $header_matches['engine'];
                $server['version'] = $hide_sensitive_data ? 'REDACTED' : $header_matches['version'];
            }
        }

        return $server;
    }

    /**
     * Grab PHP part information
     *
     * @return array
     */
    public static function grabPhpInfos($hide_sensitive_data = false)
    {
        $php = [
           'version'   => $hide_sensitive_data ? 'REDACTED' : str_replace(PHP_EXTRA_VERSION, '', PHP_VERSION),
           'modules'   => get_loaded_extensions(),
           'setup'     => [
              'max_execution_time'    => ini_get('max_execution_time'),
              'memory_limit'          => ini_get('memory_limit'),
              'post_max_size'         => ini_get('post_max_size'),
              'safe_mode'             => ini_get('safe_mode'),
              'session'               => ini_get('session.save_handler'),
              'upload_max_filesize'   => ini_get('upload_max_filesize')
           ]
        ];

        return $php;
    }

    /**
     * Grab OS part information
     *
     * @return array
     */
    public static function grabOsInfos($hide_sensitive_data = false)
    {
        $distro = false;
        if (file_exists('/etc/redhat-release')) {
            $distro = preg_replace('/\s+$/S', '', file_get_contents('/etc/redhat-release'));
        }
        $os = [
           'family'       => php_uname('s'),
           'distribution' => ($distro ?: ''),
           'version'      => $hide_sensitive_data ? 'REDACTED' : php_uname('r')
        ];
        return $os;
    }


    /**
     * Calculate average for itemtype
     *
     * @param string $itemtype Item type
     *
     * @return string
     */
    public static function getAverage($itemtype)
    {
        $count = (int)countElementsInTable(getTableForItemType($itemtype));

        if ($count <= 500) {
            return '0-500';
        } elseif ($count <= 1000) {
            return '500-1000';
        } elseif ($count <= 2500) {
            return '1000-2500';
        } elseif ($count <= 5000) {
            return '2500-5000';
        } elseif ($count <= 10000) {
            return '5000-10000';
        } elseif ($count <= 50000) {
            return '10000-50000';
        } elseif ($count <= 100000) {
            return '50000-100000';
        } elseif ($count <= 500000) {
            return '100000-500000';
        }
        return '500000+';
    }

    public static function cronInfo($name)
    {
        switch ($name) {
            case 'telemetry':
                return ['description' => __('Send telemetry information')];
        }
        return [];
    }

    /**
     * Send telemetry information
     *
     * @param CronTask $task CronTask instance
     *
     * @return void
     */
    public static function cronTelemetry($task)
    {
        $data = self::getTelemetryInfos();
        $infos = json_encode(['data' => $data]);

        $url = GLPI_TELEMETRY_URI . '/telemetry';
        $opts = [
           CURLOPT_POSTFIELDS      => $infos,
           CURLOPT_HTTPHEADER      => ['Content-Type:application/json']
        ];

        $errstr = null;
        $content = json_decode(Toolbox::callCurl($url, $opts, $errstr));

        if ($content && property_exists($content, 'message')) {
            //all is OK!
            return 1;
        } else {
            $message = 'Something went wrong sending telemetry information';
            if ($errstr != '') {
                $message .= ": $errstr";
            }
            Toolbox::logError($message);
            return null; // null = Action aborted
        }
    }

    /**
     * Get instance UUID
     *
     * @return string
     */
    final public static function getInstanceUuid()
    {
        return Config::getUuid('instance');
    }

    /**
     * Get registration UUID
     *
     * @return string
     */
    final public static function getRegistrationUuid()
    {
        return Config::getUuid('registration');
    }

    /**
     * Generates an unique identifier for current instance and store it
     *
     * @return string
     */
    final public static function generateInstanceUuid()
    {
        return Config::generateUuid('instance');
    }

    /**
     * Generates an unique identifier for current instance and store it
     *
     * @return string
     */
    final public static function generateRegistrationUuid()
    {
        return Config::generateUuid('registration');
    }


    /**
     * Get view data link along with popup script
     *
     * @return string
     */
    public static function getViewLink()
    {
        global $CFG_GLPI;

        $out = "<a id='view_telemetry' href='{$CFG_GLPI['root_doc']}/ajax/telemetry.php'>" . __('See what would be sent...') . "</a>";
        $out .= Html::scriptBlock("
         $('#view_telemetry').on('click', function(e) {
            e.preventDefault();

            $.ajax({
               url:  $(this).attr('href'),
               success: function(data) {
                  var _elt = $('<div></div>');
                  _elt.append(data);
                  $('body').append(_elt);

                  _elt.dialog({
                     title: '" . addslashes(__('Telemetry data')) . "',
                     buttons: {
                        " . addslashes(__('OK')) . ": function() {
                           $(this).dialog('close');
                        }
                     },
                     dialogClass: 'glpi_modal',
                     maxHeight: $(window).height(),
                     open: function(event, ui) {
                        $(this).dialog('option', 'maxHeight', $(window).height());
                        $(this).parent().prev('.ui-widget-overlay').addClass('glpi_modal');
                     },
                     close: function(){
                        $(this).remove();
                     },
                     draggable: true,
                     modal: true,
                     resizable: true,
                     width: '50%'
                  });
               }

            });
         });");
        return $out;
    }

    /**
     * Enable telemetry
     *
     * @return void
     */
    public static function enable()
    {
        $crontask = new CronTask();
        $crontask->update(
            ['state' => 1],
            ['name' => 'telemetry']
        );
    }

    /**
     * Is telemetry currently enabled
     *
     * @return boolean
     */
    public static function isEnabled()
    {
        global $DB;
        $request = config::getAdapter()->request([
           'SELECT' => ['state'],
           'FROM'   => 'glpi_crontasks',
           'WHERE'  => [
              'name'   => 'telemetry',
              'state' => 1
           ]

        ]);
        $results = $request->fetchAllAssociative();
        return count($results) > 0;
    }


    /**
     * Display telemetry information
     *
     * @return string
     */
    public static function showTelemetry()
    {
        $out = "<h4><input type='checkbox' checked='checked' value='1' name='send_stats' id='send_stats'/>";
        $out .= "<label for='send_stats'>" . __('Send "usage statistics"')  . "</label></h4>";
        $out .= "<p><strong>" . __("We need your help to improve ITSM-NG and the plugins ecosystem!") . "</strong></p>";
        $out .= __("Once sent, usage statistics are aggregated and made available to a broad range of ITSM-NG developers.") . "</p>";
        $out .= "<p>" . __("Let us know your usage to improve future versions of ITSM-NG and its plugins!") . "</p>";

        $out .= "<p>" . self::getViewLink() . "</p>";
        return $out;
    }

    /**
     * Display reference information
     *
     * @return string
     */
    public static function showReference()
    {
        $out = "<hr/>";
        $out .= "<h4>" . __('Reference your ITSM-NG') . "</h4>";
        $out .= "<p>" . sprintf(
            __("Besides, if you appreciate GLPI and its community, " .
            "please take a minute to reference your organization by filling %1\$s."),
            sprintf(
                "<a href='" . GLPI_TELEMETRY_URI . "/reference?showmodal&uuid=" .
                self::getRegistrationUuid() . "' target='_blank'>%1\$s</a>",
                __('the following form')
            )
        ) . "</p>";
        return $out;
    }
}
