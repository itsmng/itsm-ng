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

/**
 *  Update class
**/
class Update extends CommonGLPI
{
    private $args = [];
    private $DB;
    private $migration;
    private $version;
    private $dbversion;
    private $language;
    private $itsmversion;
    private $itsmdbversion;

    /**
     * Constructor
     *
     * @param object $DB   Database instance
     * @param array  $args Command line arguments; default to empty array
     */
    public function __construct($DB, $args = [])
    {
        $this->DB = $DB;
        $this->args = $args;
        $this->declareOldItems();
    }

    /**
     * Initialize session for update
     *
     * @return void
     */
    public function initSession()
    {
        if (is_writable(GLPI_SESSION_DIR)) {
            Session::setPath();
        } else {
            if (isCommandLine()) {
                die("Can't write in " . GLPI_SESSION_DIR . "\n");
            }
        }
        Session::start();

        if (isCommandLine()) {
            // Init debug variable
            $_SESSION = ['glpilanguage' => (isset($this->args['lang']) ? $this->args['lang'] : 'en_GB')];
            $_SESSION["glpi_currenttime"] = date("Y-m-d H:i:s");
        }
    }

    /**
     * Get current values (versions, lang, ...)
     *
     * @return array
     */
    public function getCurrents()
    {
        $currents = [];
        $DB = $this->DB;

        if (!$DB->tableExists('glpi_config') && !$DB->tableExists('glpi_configs')) {
            //very, very old version!
            $currents = [
               'version'   => '0.1',
               'dbversion' => '0.1',
               'language'  => 'en_GB'
            ];
        } elseif (!$DB->tableExists("glpi_configs")) {
            // < 0.78
            // Get current version
            $result = $DB->request([
               'SELECT' => ['version', 'language'],
               'FROM'   => 'glpi_config'
            ])->next();

            $currents['version']    = trim($result['version']);
            $currents['dbversion']  = $currents['version'];
            $currents['language']   = trim($result['language']);
        } elseif ($DB->fieldExists('glpi_configs', 'version')) {
            // < 0.85
            // Get current version and language
            $result = $DB->request([
               'SELECT' => ['version', 'language'],
               'FROM'   => 'glpi_configs'
            ])->next();

            $currents['version']    = trim($result['version']);
            $currents['dbversion']  = $currents['version'];
            $currents['language']   = trim($result['language']);
        } else {
            $currents = Config::getConfigurationValues(
                'core',
                ['version', 'dbversion', 'language', 'itsmversion', 'itsmdbversion']
            );

            if (!isset($currents['dbversion'])) {
                $currents['dbversion'] = $currents['version'];
            }

            // Init ITSM-NG version
            if (!isset($currents['itsmversion'])) {
                $currents['itsmversion'] = "1.0.0";
            }

            if (!isset($currents['itsmdbversion'])) {
                $currents['itsmdbversion'] = $currents['itsmversion'];
            }
        }

        $this->version       = $currents['version'];
        $this->dbversion     = $currents['dbversion'];
        $this->language      = $currents['language'];

        // Init ITSM-NG version
        $this->itsmversion   = $currents['itsmversion'] ?? '1.0.0';
        $this->itsmdbversion = $currents['itsmdbversion'] ?? '1.0.0';

        return $currents;
    }


    /**
     * Run updates
     *
     * @param string $current_version Current version
     *
     * @return void
     */
    public function doUpdates($current_version = null)
    {
        if ($current_version === null) {
            if ($this->version === null) {
                throw new \RuntimeException('Cannot process updates without any version specified!');
            }
            $current_version = $this->version;
        }

        $DB = $this->DB;

        // To prevent problem of execution time
        ini_set("max_execution_time", "0");

        $updir = __DIR__ . "/../install/";

        if (isCommandLine() && version_compare($current_version, '0.72.3', 'lt')) {
            echo 'Upgrade from command line is not supported before 0.72.3!';
            die(1);
        }

        // Update process desactivate all plugins
        $plugin = new Plugin();
        $plugin->unactivateAll();

        switch ($current_version) {
            case '0.1':
                include_once("update_to_031.php");
                updateDbTo031();
                // no break
            case "0.31":
                $ret = [];
                include_once "{$updir}update_031_04.php";
                update031to04();
                if (!empty($ret) && $ret["adminchange"]) {
                    echo "<div class='center'> <h2>" . __("All users having administrators rights have have been updated to 'super-admin' rights with the creation of his new user type.") . "<h2></div>";
                }
                // no break
            case "0.4":
            case "0.41":
                include_once("{$updir}update_04_042.php");
                update04to042();

                // no break
            case "0.42":
                showLocationUpdateForm();
                include_once("{$updir}update_042_05.php");
                update042to05();

                // no break
            case "0.5":
                include_once("{$updir}update_05_051.php");
                update05to051();

                // no break
            case "0.51":
            case "0.51a":
                include_once("{$updir}update_051_06.php");
                update051to06();

                // no break
            case "0.6":
                include_once("{$updir}update_06_065.php");
                update06to065();

                // no break
            case "0.65":
                include_once("{$updir}update_065_068.php");
                update065to068();

                // no break
            case "0.68":
                include_once("{$updir}update_068_0681.php");
                update068to0681();

                // no break
            case "0.68.1":
            case "0.68.2":
            case "0.68.3":
                // Force update content
                if (showLocationUpdateForm()) {
                    $DB->updateOrDie(
                        'glpi_config',
                        ['version' => ' 0.68.3x'],
                        [0],
                        '0.68.3'
                    );

                    showContentUpdateForm();
                    exit();
                }
                // no break
            case "0.68.3x": // Special version for replay upgrade process from here
                include_once("{$updir}update_0681_07.php");
                update0681to07();

                // no break
            case "0.7":
            case "0.70.1":
            case "0.70.2":
                include_once("{$updir}update_07_071.php");
                update07to071();

                // no break
            case "0.71":
            case "0.71.1":
                include_once("{$updir}update_071_0712.php");
                update071to0712();

                // no break
            case "0.71.2":
                include_once("{$updir}update_0712_0713.php");
                update0712to0713();

                // no break
            case "0.71.3":
            case "0.71.4":
            case "0.71.5":
            case "0.71.6":
                include_once("{$updir}update_0713_072.php");
                update0713to072();

                // no break
            case "0.72":
                include_once("{$updir}update_072_0721.php");
                update072to0721();

                // no break
            case "0.72.1":
                include_once("{$updir}update_0721_0722.php");
                update0721to0722();

                // no break
            case "0.72.2":
            case "0.72.21":
                include_once("{$updir}update_0722_0723.php");
                update0722to0723();

                // no break
            case "0.72.3":
            case "0.72.4":
                include_once("{$updir}update_0723_078.php");
                update0723to078();

                // no break
            case "0.78":
                include_once("{$updir}update_078_0781.php");
                update078to0781();

                // no break
            case "0.78.1":
                include_once("{$updir}update_0781_0782.php");
                update0781to0782();

                // no break
            case "0.78.2":
            case "0.78.3":
            case "0.78.4":
            case "0.78.5":
                include_once("{$updir}update_0782_080.php");
                update0782to080();

                // no break
            case "0.80":
                include_once("{$updir}update_080_0801.php");
                update080to0801();

                // no break
            case "0.80.1":
            case "0.80.2":
                include_once("{$updir}update_0801_0803.php");
                update0801to0803();

                // no break
            case "0.80.3":
            case "0.80.4":
            case "0.80.5":
            case "0.80.6":
            case "0.80.61":
            case "0.80.7":
                include_once("{$updir}update_0803_083.php");
                update0803to083();

                // no break
            case "0.83":
                include_once("{$updir}update_083_0831.php");
                update083to0831();

                // no break
            case "0.83.1":
            case "0.83.2":
                include_once("{$updir}update_0831_0833.php");
                update0831to0833();

                // no break
            case "0.83.3":
            case "0.83.31":
            case "0.83.4":
            case "0.83.5":
            case "0.83.6":
            case "0.83.7":
            case "0.83.8":
            case "0.83.9":
            case "0.83.91":
                include_once("{$updir}update_0831_084.php");
                update0831to084();

                // no break
            case "0.84":
                include_once("{$updir}update_084_0841.php");
                update084to0841();

                // no break
            case "0.84.1":
            case "0.84.2":
                include_once("{$updir}update_0841_0843.php");
                update0841to0843();

                // no break
            case "0.84.3":
                include_once("{$updir}update_0843_0844.php");
                update0843to0844();

                // no break
            case "0.84.4":
            case "0.84.5":
                include_once("{$updir}update_0845_0846.php");
                update0845to0846();

                // no break
            case "0.84.6":
            case "0.84.7":
            case "0.84.8":
            case "0.84.9":
                include_once("{$updir}update_084_085.php");
                update084to085();

                // no break
            case "0.85":
            case "0.85.1":
            case "0.85.2":
                include_once("{$updir}update_085_0853.php");
                update085to0853();

                // no break
            case "0.85.3":
            case "0.85.4":
                include_once("{$updir}update_0853_0855.php");
                update0853to0855();

                // no break
            case "0.85.5":
                include_once("{$updir}update_0855_090.php");
                update0855to090();

                // no break
            case "0.90":
                include_once("{$updir}update_090_0901.php");
                update090to0901();

                // no break
            case "0.90.1":
            case "0.90.2":
            case "0.90.3":
            case "0.90.4":
                include_once("{$updir}update_0901_0905.php");
                update0901to0905();

                // no break
            case "0.90.5":
                include_once("{$updir}update_0905_91.php");
                update0905to91();

                // no break
            case "9.1":
            case "0.91":
                include_once("{$updir}update_91_911.php");
                update91to911();

                // no break
            case "9.1.1":
            case "9.1.2":
                include_once("{$updir}update_911_913.php");
                update911to913();

                // no break
            case "9.1.3":
            case "9.1.4":
            case "9.1.5":
            case "9.1.6":
            case "9.1.7":
            case "9.1.7.1":
            case "9.1.8":
            case "9.2-dev":
                include_once("{$updir}update_91_92.php");
                update91to92();

                // no break
            case "9.2":
                include_once("{$updir}update_92_921.php");
                update92to921();

                // no break
            case "9.2.1":
                include_once("{$updir}update_921_922.php");
                update921to922();

                // no break
            case "9.2.2":
                //9.2.2 upgrade script was not run from the release, see https://github.com/glpi-project/glpi/issues/3659
                //see https://github.com/glpi-project/glpi/issues/3659
                include_once("{$updir}update_921_922.php");
                update921to922();
                include_once("{$updir}update_922_923.php");
                update922to923();

                // no break
            case "9.2.3":
            case "9.2.4":
            case "9.3-dev":
                include_once("{$updir}update_92_93.php");
                update92to93();

                // no break
            case "9.3":
            case "9.3.0":
                include_once "{$updir}update_930_931.php";
                update930to931();

                // no break
            case "9.3.1":
                include_once "{$updir}update_931_932.php";
                update931to932();

                // no break
            case "9.3.2":
            case "9.3.3":
            case "9.3.4":
            case "9.3.5":
            case "9.4.0-dev":
                include_once("{$updir}update_93_94.php");
                update93to94();

                // no break
            case "9.4.0":
                include_once "{$updir}update_940_941.php";
                update940to941();

                // no break
            case "9.4.1":
            case "9.4.1.1":
                include_once "{$updir}update_941_942.php";
                update941to942();

                // no break
            case "9.4.2":
                include_once "{$updir}update_942_943.php";
                update942to943();

                // no break
            case "9.4.3":
            case "9.4.4":
                include_once "{$updir}update_943_945.php";
                update943to945();

                // no break
            case "9.4.5":
                include_once "{$updir}update_945_946.php";
                update945to946();

                // no break
            case "9.4.6":
                include_once "{$updir}update_946_947.php";
                update946to947();

                // no break
            case "9.4.7":
            case "9.4.8":
            case "9.4.9":
            case "9.5.0-dev":
                include_once "{$updir}update_94_95.php";
                update94to95();

                // no break
            case "9.5.0":
            case "9.5.1":
                include_once "{$updir}update_951_952.php";
                update951to952();

                // no break
            case "9.5.2":
                include_once "{$updir}update_952_953.php";
                update952to953();

                // no break
            case "9.5.3":
                include_once "{$updir}update_953_954.php";
                update953to954();

                // no break
            case "9.5.4":
                include_once "{$updir}update_954_955.php";
                update954to955();

                // no break
            case "9.5.5":
                include_once "{$updir}update_955_956.php";
                update955to956();

                // no break
            case "9.5.6":
                include_once "{$updir}update_956_957.php";
                update956to957();

                // no break
            case "9.5.7":
            case "9.5.8":
            case "9.5.9":
            case "9.5.10":
            case "9.5.11":
            case "9.5.12":
            case "9.5.13":
                include_once "{$updir}itsm_update_100_101.php";
                update100to101();

                // no break
            case "1.0.1":
                include_once "{$updir}itsm_update_101_110.php";
                update101to110();

                // no break
            case "1.1.0":
                include_once "{$updir}itsm_update_110_120.php";
                update110to120();

                // no break
            case "1.2.0":
                include_once "{$updir}itsm_update_120_130.php";
                update120to130();

                // no break
            case "1.3.0":
            case "1.4.0":
            case "1.5.0":
            case "1.5.1":
            case "1.6.0":
            case "1.6.1":
            case "1.6.2":
            case "1.6.3":
            case "1.6.4":
            case "1.6.5":
            case "1.6.6":
                include_once "{$updir}itsm_update_150_151.php";
                update150to151();
                // no break
            case "2.0.0":
            case "2.0.1":
            case "2.0.2":
            case "2.0.3":
                include_once "{$updir}itsm_update_151_200.php";
                update151to200();
            case "2.0.4":
                include_once "{$updir}itsm_update_200_204.php";
                update200to204();

                // no break
            case ITSM_VERSION:
            case ITSM_SCHEMA_VERSION:
                break;

            default:
                $message = sprintf(
                    __('Unsupported version (%1$s)'),
                    $current_version
                );
                if (isCommandLine()) {
                    echo "$message\n";
                    die(1);
                } else {
                    $this->migration->displayWarning($message, true);
                    die(1);
                }
        }

        // Update version number and default langage and new version_founded ---- LEAVE AT THE END
        Config::setConfigurationValues('core', ['version'             => ITSM_VERSION,
                                                'dbversion'           => ITSM_SCHEMA_VERSION,
                                                'itsmversion'         => ITSM_VERSION,
                                                'itsmdbversion'       => ITSM_SCHEMA_VERSION,
                                                'language'            => $this->language,
                                                'founded_new_version' => '']);

        if (defined('GLPI_SYSTEM_CRON')) {
            // Downstream packages may provide a good system cron
            $DB->updateOrDie(
                'glpi_crontasks',
                [
                  'mode'   => 2
                ],
                [
                  'name'      => ['!=', 'watcher'],
                  'allowmode' => ['&', 2]
                ]
            );
        }

        // Reset telemetry if its state is running, assuming it remained stuck due to telemetry service issue (see #7492).
        $crontask_telemetry = new CronTask();
        $crontask_telemetry->getFromDBbyName("Telemetry", "telemetry");
        if ($crontask_telemetry->fields['state'] === CronTask::STATE_RUNNING) {
            $crontask_telemetry->resetDate();
            $crontask_telemetry->resetState();
        }

        //generate security key if missing, and update db
        $glpikey = new GLPIKey();
        if (!$glpikey->keyExists() && !$glpikey->generate()) {
            $this->migration->displayWarning(__('Unable to create security key file! You have to run "php bin/console itsmng:security:change_key" command to manually create this file.'), true);
        }
    }

    /**
     * Declare old items for compatibility
     *
     * @return void
     */
    public function declareOldItems()
    {
        // Old itemtypes
        define("GENERAL_TYPE", 0);
        define("COMPUTER_TYPE", 1);
        define("NETWORKING_TYPE", 2);
        define("PRINTER_TYPE", 3);
        define("MONITOR_TYPE", 4);
        define("PERIPHERAL_TYPE", 5);
        define("SOFTWARE_TYPE", 6);
        define("CONTACT_TYPE", 7);
        define("ENTERPRISE_TYPE", 8);
        define("INFOCOM_TYPE", 9);
        define("CONTRACT_TYPE", 10);
        define("CARTRIDGEITEM_TYPE", 11);
        define("TYPEDOC_TYPE", 12);
        define("DOCUMENT_TYPE", 13);
        define("KNOWBASE_TYPE", 14);
        define("USER_TYPE", 15);
        define("TRACKING_TYPE", 16);
        define("CONSUMABLEITEM_TYPE", 17);
        define("CONSUMABLE_TYPE", 18);
        define("CARTRIDGE_TYPE", 19);
        define("SOFTWARELICENSE_TYPE", 20);
        define("LINK_TYPE", 21);
        define("STATE_TYPE", 22);
        define("PHONE_TYPE", 23);
        define("DEVICE_TYPE", 24);
        define("REMINDER_TYPE", 25);
        define("STAT_TYPE", 26);
        define("GROUP_TYPE", 27);
        define("ENTITY_TYPE", 28);
        define("RESERVATION_TYPE", 29);
        define("AUTHMAIL_TYPE", 30);
        define("AUTHLDAP_TYPE", 31);
        define("OCSNG_TYPE", 32);
        define("REGISTRY_TYPE", 33);
        define("PROFILE_TYPE", 34);
        define("MAILGATE_TYPE", 35);
        define("RULE_TYPE", 36);
        define("TRANSFER_TYPE", 37);
        define("BOOKMARK_TYPE", 38);
        define("SOFTWAREVERSION_TYPE", 39);
        define("PLUGIN_TYPE", 40);
        define("COMPUTERDISK_TYPE", 41);
        define("NETWORKING_PORT_TYPE", 42);
        define("FOLLOWUP_TYPE", 43);
        define("BUDGET_TYPE", 44);

        // Old devicetypes
        define("MOBOARD_DEVICE", 1);
        define("PROCESSOR_DEVICE", 2);
        define("RAM_DEVICE", 3);
        define("HDD_DEVICE", 4);
        define("NETWORK_DEVICE", 5);
        define("DRIVE_DEVICE", 6);
        define("CONTROL_DEVICE", 7);
        define("GFX_DEVICE", 8);
        define("SND_DEVICE", 9);
        define("PCI_DEVICE", 10);
        define("CASE_DEVICE", 11);
        define("POWER_DEVICE", 12);
    }

    /**
     * Set migration
     *
     * @param Migration $migration Migration instance
     *
     * @return Update
     */
    public function setMigration(Migration $migration)
    {
        $this->migration = $migration;
        return $this;
    }

    /**
     * Check if expected security key file is missing.
     *
     * @return bool
     */
    public function isExpectedSecurityKeyFileMissing(): bool
    {
        $expected_key_path = $this->getExpectedSecurityKeyFilePath();

        if ($expected_key_path === null) {
            return false;
        }

        return !file_exists($expected_key_path);
    }

    /**
     * Returns expected security key file path.
     * Will return null for GLPI versions that was not yet handling a custom security key.
     *
     * @return string|null
     */
    public function getExpectedSecurityKeyFilePath(): ?string
    {
        $glpikey = new GLPIKey();
        return $glpikey->getExpectedKeyPath($this->getCurrents()['version']);
    }
}
