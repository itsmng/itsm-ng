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

// Notice problem  for date function :
$tz = ini_get('date.timezone');
if (!empty($tz)) {
    date_default_timezone_set($tz);
} else {
    date_default_timezone_set(@date_default_timezone_get());
}

include_once(GLPI_ROOT . "/inc/autoload.function.php");

(function () {
    // Define GLPI_* constants that can be customized by admin.
    //
    // Use a self-invoking anonymous function to:
    // - prevent any global variables/functions definition from `local_define.php` and `downstream.php` files;
    // - prevent any global variables definition from current function logic.

    $constants = [
       // Constants related to system paths
       'GLPI_CONFIG_DIR'      => GLPI_ROOT . '/config', // Path for configuration files (db, security key, ...)
       'GLPI_VAR_DIR'         => GLPI_ROOT . '/files',  // Path for all files
       'GLPI_DOC_DIR'         => '{GLPI_VAR_DIR}', // Path for documents storage
       'GLPI_CACHE_DIR'       => '{GLPI_VAR_DIR}/_cache', // Path for cache
       'GLPI_CRON_DIR'        => '{GLPI_VAR_DIR}/_cron', // Path for cron storage
       'GLPI_DUMP_DIR'        => '{GLPI_VAR_DIR}/_dumps', // Path for backup dump
       'GLPI_GRAPH_DIR'       => '{GLPI_VAR_DIR}/_graphs', // Path for graph storage
       'GLPI_LOCAL_I18N_DIR'  => '{GLPI_VAR_DIR}/_locales', // Path for local i18n files
       'GLPI_LOCK_DIR'        => '{GLPI_VAR_DIR}/_lock', // Path for lock files storage (used by cron)
       'GLPI_LOG_DIR'         => '{GLPI_VAR_DIR}/_log', // Path for log storage
       'GLPI_PICTURE_DIR'     => '{GLPI_VAR_DIR}/_pictures', // Path for picture storage
       'GLPI_PLUGIN_DOC_DIR'  => '{GLPI_VAR_DIR}/_plugins', // Path for plugins documents storage
       'GLPI_RSS_DIR'         => '{GLPI_VAR_DIR}/_rss', // Path for rss storage
       'GLPI_SESSION_DIR'     => '{GLPI_VAR_DIR}/_sessions', // Path for sessions storage
       'GLPI_TMP_DIR'         => '{GLPI_VAR_DIR}/_tmp', // Path for temp storage
       'GLPI_UPLOAD_DIR'      => '{GLPI_VAR_DIR}/_uploads', // Path for upload storage

       // Security constants
       'GLPI_USE_CSRF_CHECK'            => '1',
       'GLPI_CSRF_EXPIRES'              => '7200',
       'GLPI_CSRF_MAX_TOKENS'           => '100',
       'GLPI_USE_IDOR_CHECK'            => '1',
       'GLPI_IDOR_EXPIRES'              => '7200',
       'GLPI_ALLOW_IFRAME_IN_RICH_TEXT' => false,
       'GLPI_SERVERSIDE_URL_ALLOWLIST'  => [
          // Based on https://github.com/symfony/symfony/blob/7.3/src/Symfony/Component/Validator/Constraints/UrlValidator.php
          '~^
                        (http|https|feed)://                                                # protocol
                        (
                            (?:
                                (?:xn--[a-z0-9-]++\.)*+xn--[a-z0-9-]++                      # a domain name using punycode
                                    |
                                (?:[\pL\pN\pS\pM\-\_]++\.)+[\pL\pN\pM]++                    # a multi-level domain name
                                    |
                                [a-z0-9\-\_]++                                              # a single-level domain name
                            )\.?
                                |                                                           # or
                            \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                              # an IP address
                                |                                                           # or
                            \[
                                (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                            \]                                                              # an IPv6 address
                        )
                        (?::[0-9]+)?                                                        # a port
                        (?:/ (?:[\pL\pN\pS\pM\-._\~!$&\'()*+,;=:@]|%[0-9A-Fa-f]{2})* )*     # a path
                        (?:\? (?:[\pL\pN\-._\~!$&\'\[\]()*+,;=:@/?]|%[0-9A-Fa-f]{2})* )?    # a query (optional)
                    $~ixuD',
       ],

       // Constants related to GLPI Project / GLPI Network external services
       'GLPI_TELEMETRY_URI'                => 'https://telemetry.glpi-project.org', // Telemetry project URL
       'GLPI_INSTALL_MODE'                 => is_dir(GLPI_ROOT . '/.git') ? 'GIT' : 'TARBALL', // Install mode for telemetry
       'GLPI_NETWORK_MAIL'                 => 'glpi@teclib.com',
       'GLPI_NETWORK_SERVICES'             => 'https://services.glpi-network.com', // GLPI Network services project URL
       'GLPI_NETWORK_REGISTRATION_API_URL' => '{GLPI_NETWORK_SERVICES}/api/registration/',
       // TODO set false before final release of 9.5.0 and remove this comment
       'GLPI_USER_AGENT_EXTRA_COMMENTS'    => '', // Extra comment to add to GLPI User-Agent

       // Other constants
       'GLPI_AJAX_DASHBOARD'         => '1',
       'GLPI_CALDAV_IMPORT_STATE'    => 0, // external events created from a caldav client will take this state by default (0 = Planning::INFO)
       'GLPI_DEMO_MODE'              => '0',
       // TODO GLPI_FORCE_EMPTY_SQL_MODE need to be set to 0 after review of all sql queries
       'GLPI_FORCE_EMPTY_SQL_MODE'   => '1', // for compatibility with mysql 5.7
    ];

    if (defined('TU_USER')) {
        // calendar mockups
        $constants['GLPI_SERVERSIDE_URL_ALLOWLIST'][] = '/^file:\/\/.*\.ics$/';

        // Fork-specific RSS feed fixture server.
        $constants['GLPI_SERVERSIDE_URL_ALLOWLIST'][] = '~^http://127\.0\.0\.1:\d+/tests/fixtures/rssfeed\.xml$~';
    }

    // Define constants values based on server env variables (i.e. defined using apache SetEnv directive)
    foreach (array_keys($constants) as $name) {
        if (!defined($name) && ($value = getenv($name)) !== false) {
            define($name, $value);
        }
    }

    // Define constants values from local configuration file
    if (file_exists(GLPI_ROOT . '/config/local_define.php') && !defined('TU_USER')) {
        require_once GLPI_ROOT . '/config/local_define.php';
    }

    // Define constants values from downstream distribution file
    if (file_exists(GLPI_ROOT . '/inc/downstream.php')) {
        include_once(GLPI_ROOT . '/inc/downstream.php');
    }

    // Define constants values from defaults
    // 1. First, define constants that does not inherit from another one.
    // 2. Second, define constants that inherits from another one.
    // This logic is quiet simple and is not made to handle chain inheritance.
    $inherit_pattern = '/\{(?<name>GLPI_[\w]+)\}/';
    foreach ($constants as $key => $value) {
        if (!defined($key) && (!is_string($value) || !preg_match($inherit_pattern, $value))) {
            define($key, $value);
        }
    }
    foreach ($constants as $key => $value) {
        if (!defined($key)) {
            // Replace {GLPI_*} by value of corresponding constant
            if (is_string($value)) {
                $value = preg_replace_callback(
                    '/\{(?<name>GLPI_[\w]+)\}/',
                    function ($matches) {
                        return defined($matches['name']) ? constant($matches['name']) : '';
                    },
                    $value
                );
            }

            define($key, $value);
        }
    }

    // Where to load plugins.
    // Order in this array is important (priority to first found).
    if (!defined('PLUGINS_DIRECTORIES')) {
        define('PLUGINS_DIRECTORIES', [
           GLPI_ROOT . '/plugins',
        ]);
    } elseif (!is_array(PLUGINS_DIRECTORIES)) {
        throw new \Exception('PLUGINS_DIRECTORIES constant value must be an array');
    }
})();

define('GLPI_I18N_DIR', GLPI_ROOT . "/locales");

include_once(GLPI_ROOT . "/inc/define.php");
