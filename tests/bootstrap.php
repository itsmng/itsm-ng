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

ini_set('display_errors', 'On');
error_reporting(E_ALL);

define('GLPI_ROOT', __DIR__ . '/../');
define('GLPI_CONFIG_DIR', getenv('GLPI_CONFIG_DIR') ?: __DIR__ . '/config');
define('GLPI_VAR_DIR', getenv('GLPI_VAR_DIR') ?: __DIR__ . '/files');
define('GLPI_URI', getenv('GLPI_URI') ?: 'http://localhost:8088');
define('ITSM_MIN_PHP', '8.1');

define(
    'PLUGINS_DIRECTORIES',
    [
      GLPI_ROOT . '/plugins',
      GLPI_ROOT . '/tests/fixtures/plugins',
    ]
);

define('TU_USER', '_test_user');
define('TU_PASS', 'PhpUnit_4');

global $CFG_GLPI, $GLPI_CACHE;

include(GLPI_ROOT . "/inc/based_config.php");

if (!file_exists(GLPI_CONFIG_DIR . '/config_db.php')) {
    die("\nConfiguration file for tests not found\n\nrun: bin/console itsmng:database:install --config-dir=" . GLPI_CONFIG_DIR . " ...\n\n");
}

// Create subdirectories of GLPI_VAR_DIR based on defined constants
foreach (get_defined_constants() as $constant_name => $constant_value) {
    if (
        preg_match('/^GLPI_[\w]+_DIR$/', $constant_name)
        && preg_match('/^' . preg_quote(GLPI_VAR_DIR, '/') . '\//', $constant_value)
    ) {
        is_dir($constant_value) or mkdir($constant_value, 0755, true);
    }
}

//init cache
$GLPI_CACHE = Config::getCache('cache_db');

include_once __DIR__ . '/../inc/includes.php';
include_once __DIR__ . '/GLPITestCase.php';
include_once __DIR__ . '/DbTestCase.php';
include_once __DIR__ . '/APIBaseClass.php';

// check folder exists instead of class_exists('\GuzzleHttp\Client'), to prevent global includes
if (file_exists(__DIR__ . '/../vendor/autoload.php') && !file_exists(__DIR__ . '/../vendor/guzzlehttp/guzzle')) {
    die("\nDevelopment dependencies not found\n\nrun: composer install -o\n\n");
}

class GlpitestPHPerror extends Exception
{
}
class GlpitestPHPwarning extends Exception
{
}
class GlpitestPHPnotice extends Exception
{
}
class GlpitestSQLError extends Exception
{
}

function loadDataset()
{
    global $CFG_GLPI, $DB;

    // Unit test data definition
    $data = [
       // bump this version to force reload of the full dataset, when content change
       '_version' => '4.7',

       // Type => array of entries
       'Entity' => [
          [
             'name'        => '_test_root_entity',
             'entities_id' => 0,
          ], [
             'name'        => '_test_child_1',
             'entities_id' => '_test_root_entity',
          ], [
             'name'        => '_test_child_2',
             'entities_id' => '_test_root_entity',
          ]
       ], 'Computer' => [
          [
             'name'        => '_test_pc01',
             'entities_id' => '_test_root_entity',
             'comment'     => 'Comment for computer _test_pc01',
          ], [
             'name'        => '_test_pc02',
             'entities_id' => '_test_root_entity',
             'comment'     => 'Comment for computer _test_pc02',
          ], [
             'name'        => '_test_pc03',
             'entities_id' => '_test_root_entity',
             'comment'     => 'Comment for computer _test_pc03',
             'contact'     => 'johndoe',
          ], [
             'name'        => '_test_pc11',
             'entities_id' => '_test_child_1',
          ], [
             'name'        => '_test_pc12',
             'entities_id' => '_test_child_1',
          ], [
             'name'        => '_test_pc13',
             'entities_id' => '_test_child_1',
             'comment'     => 'Comment for computer _test_pc13',
             'contact'     => 'johndoe',
          ], [
             'name'        => '_test_pc21',
             'entities_id' => '_test_child_2',
          ], [
             'name'        => '_test_pc22',
             'entities_id' => '_test_child_2',
          ]
       ], 'ComputerModel' => [
          [
             'name'           => '_test_computermodel_1',
             'product_number' => 'CMP_ADEAF5E1',
          ], [
             'name'           => '_test_computermodel_2',
             'product_number' => 'CMP_567AEC68',
          ],
       ], 'Monitor' => [
          [
             'name'           => '_test_monitor_1',
             'entities_id' => '_test_root_entity',
          ], [
             'name'           => '_test_monitor_2',
             'entities_id' => '_test_root_entity',
          ],
       ], 'Software' => [
          [
             'name'         => '_test_soft',
             'entities_id'  => '_test_root_entity',
             'is_recursive' => 1,
          ], [
             'name'         => '_test_soft2',
             'entities_id'  => '_test_child_2',
             'is_recursive' => 0,
          ], [
             'name'         => '_test_soft_3',
             'entities_id'  => '_test_root_entity',
             'is_recursive' => 1,
          ]

       ], 'SoftwareVersion' => [
          [
             'name'        => '_test_softver_1',
             'entities_id' => '_test_root_entity',
             'is_recursive' => 1,
             'softwares_id' => '_test_soft',
          ], [
             'name'        => '_test_softver_2',
             'entities_id' => '_test_root_entity',
             'is_recursive' => 1,
             'softwares_id' => '_test_soft',
          ]
       ], 'NetworkEquipment' => [
          [
             'name'           => '_test_networkequipment_1',
             'entities_id' => '_test_root_entity',
          ], [
             'name'           => '_test_networkequipment_2',
             'entities_id' => '_test_root_entity',
          ],
       ], 'Peripheral' => [
          [
             'name'           => '_test_peripheral_1',
             'entities_id' => '_test_root_entity',
          ], [
             'name'           => '_test_peripheral_2',
             'entities_id' => '_test_root_entity',
          ],
       ], 'Printer' => [
          [
             'name'         => '_test_printer_all',
             'entities_id'  => '_test_root_entity',
             'is_recursive' => 1,
          ], [
             'name'         => '_test_printer_ent0',
             'entities_id'  => '_test_root_entity',
             'is_recursive' => 0,
          ], [
             'name'         => '_test_printer_ent1',
             'entities_id'  => '_test_child_1',
             'is_recursive' => 0,
          ], [
             'name'         => '_test_printer_ent2',
             'entities_id'  => '_test_child_2',
             'is_recursive' => 0,
          ]
       ], 'Phone' => [
          [
             'name'           => '_test_phone_1',
             'entities_id' => '_test_root_entity',
          ], [
             'name'           => '_test_phone_2',
             'entities_id' => '_test_root_entity',
          ],
       ], 'User' => [
          [
             'name'          => TU_USER,
             'password'      => TU_PASS,
             'password2'     => TU_PASS,
             'entities_id'   => '_test_root_entity',
             'profiles_id'   => 4, // TODO manage test profiles
             '_entities_id'  => 0,
             '_profiles_id'  => 4,
             '_is_recursive' => 1,
          ]
       ], 'TaskCategory' => [
          [
             'is_recursive' => 1,
             'name'         => '_cat_1',
             'completename' => '_cat_1',
             'comment'      => 'Comment for category _cat_1',
             'level'        => 1,
          ],
          [
             'is_recursive' => 1,
             'taskcategories_id' => '_cat_1',
             'name'         => '_subcat_1',
             'completename' => '_cat_1 > _subcat_1',
             'comment'      => 'Comment for sub-category _subcat_1',
             'level'        => 2,
          ]
       ], 'DropdownTranslation' => [
          [
             'items_id'   => '_cat_1',
             'itemtype'   => 'TaskCategory',
             'language'   => 'fr_FR',
             'field'      => 'name',
             'value'      => 'FR - _cat_1'
          ],
          [
             'items_id'   => '_cat_1',
             'itemtype'   => 'TaskCategory',
             'language'   => 'fr_FR',
             'field'      => 'comment',
             'value'      => 'FR - Commentaire pour catégorie _cat_1'
          ],
          [
             'items_id'   => '_subcat_1',
             'itemtype'   => 'TaskCategory',
             'language'   => 'fr_FR',
             'field'      => 'name',
             'value'      => 'FR - _subcat_1'
          ],
          [
             'items_id'   => '_subcat_1',
             'itemtype'   => 'TaskCategory',
             'language'   => 'fr_FR',
             'field'      => 'comment',
             'value'      => 'FR - Commentaire pour sous-catégorie _subcat_1'
          ]
       ], 'Contact' => [
          [
             'name'         => '_contact01_name',
             'firstname'    => '_contact01_firstname',
             'phone'        => '0123456789',
             'phone2'       => '0123456788',
             'mobile'       => '0623456789',
             'fax'          => '0123456787',
             'email'        => '_contact01_firstname._contact01_name@glpi.com',
             'comment'      => 'Comment for contact _contact01_name',
             'entities_id'  => '_test_root_entity'
          ]
       ], 'Supplier' => [
          [
             'name'         => '_suplier01_name',
             'phonenumber'  => '0123456789',
             'fax'          => '0123456787',
             'email'        => 'info@_supplier01_name.com',
             'comment'      => 'Comment for supplier _suplier01_name',
             'entities_id'  => '_test_root_entity'
          ]
       ], 'Location' => [
          [
             'name'         => '_location01',
             'comment'      => 'Comment for location _location01'
          ],
          [
             'name'         => '_location01 > _sublocation01',
             'comment'      => 'Comment for location _sublocation01'
          ],
          [
             'name'         => '_location02',
             'comment'      => 'Comment for location _sublocation02'
          ]
       ], 'Netpoint' => [
          [
             'name'         => '_netpoint01',
             'locations_id' => '_location01',
             'comment'      => 'Comment for netpoint _netpoint01'
          ]
       ], 'BudgetType' => [
          [
             'name'         => '_budgettype01',
             'comment'      => 'Comment for budgettype _budgettype01'
          ]
       ], 'Budget' => [
          [
             'name'           => '_budget01',
             'comment'        => 'Comment for budget _budget01',
             'locations_id'   => '_location01',
             'budgettypes_id' => '_budgettype01',
             'begin_date'     => '2016-10-18',
             'end_date'       => '2016-12-31',
             'entities_id'     => '_test_root_entity'
          ]
       ], 'Ticket' => [
          [
             'name'           => '_ticket01',
             'content'        => 'Content for ticket _ticket01',
             'recipient_users_id' => TU_USER,
             'entities_id'    => '_test_root_entity'
          ],
          [
             'name'           => '_ticket02',
             'content'        => 'Content for ticket _ticket02',
             'recipient_users_id' => TU_USER,
             'entities_id'    => '_test_root_entity'
          ],
          [
             'name'           => '_ticket03',
             'content'        => 'Content for ticket _ticket03',
             'recipient_users_id' => TU_USER,
             'entities_id'    => '_test_child_1'
          ],
          [
             'id'             => 100, // Force ID that will be used in imap test suite fixtures
             'name'           => '_ticket100',
             'content'        => 'Content for ticket _ticket100',
             'recipient_users_id' => TU_USER,
             'entities_id'    => '_test_root_entity'
          ],
          [
             'id'             => 101, // Force ID that will be used in imap test suite fixtures
             'name'           => '_ticket101',
             'content'        => 'Content for ticket _ticket101',
             'recipient_users_id' => TU_USER,
             'entities_id'    => '_test_root_entity'
          ],
       ], 'TicketTask' => [
          [
             'tickets_id'         => '_ticket01',
             'taskcategories_id'  => '_subcat_1',
             'users_id'           => TU_USER,
             'content'            => 'Task to be done',
             'is_private'         => 0,
             'users_id_tech'      => TU_USER,
             'date'               => '2016-10-19 11:50:50'
          ]
       ], 'UserEmail' => [
          [
             'users_id'     => TU_USER,
             'is_default'   => '1',
             'is_dynamic'   => '0',
             'email'        => TU_USER . '@glpi.com'
          ]
       ], 'KnowbaseItem' => [
          [
             'name'     => '_knowbaseitem01',
             'answer'   => 'Answer for Knowledge base entry _knowbaseitem01',
             'is_faq'   => 0,
             'users_id' => TU_USER,
             'date'     => '2016-11-17 12:27:48',
          ],
          [
             'name'     => '_knowbaseitem02',
             'answer'   => 'Answer for Knowledge base entry _knowbaseitem02',
             'is_faq'   => 0,
             'users_id' => TU_USER,
             'date'     => '2016-11-17 12:27:48',
          ]
       ], 'KnowbaseItem_Item' => [
          [
             'knowbaseitems_id' => '_knowbaseitem01',
             'itemtype'         => 'Ticket',
             'items_id'         => '_ticket01',
          ],
          [
             'knowbaseitems_id' => '_knowbaseitem01',
             'itemtype'         => 'Ticket',
             'items_id'         => '_ticket02',
          ],
          [
             'knowbaseitems_id' => '_knowbaseitem01',
             'itemtype'         => 'Ticket',
             'items_id'         => '_ticket03',
          ],
          [
             'knowbaseitems_id' => '_knowbaseitem02',
             'itemtype'         => 'Ticket',
             'items_id'         => '_ticket03',
          ],
          [
             'knowbaseitems_id' => '_knowbaseitem02',
             'itemtype'         => 'Computer',
             'items_id'         => '_test_pc21',
          ]
       ], 'Entity_KnowbaseItem' => [
          [
             'knowbaseitems_id' => '_knowbaseitem01',
             'entities_id'      => '_test_root_entity'
          ],
          [
             'knowbaseitems_id' => '_knowbaseitem02',
             'entities_id'      => '_test_child_1'
          ]
       ], 'DocumentType' => [
          [
             'name'          => 'markdown',
             'is_uploadable' => '1',
             'ext'           => 'md'
          ]
       ], 'Manufacturer' => [
          [
             'name'          => 'My Manufacturer',
          ]
       ], 'SoftwareLicense' => [
          [
             'name'         => '_test_softlic_1',
             'completename' => '_test_softlic_1',
             'level'        => 0,
             'entities_id'  => '_test_root_entity',
             'is_recursive' => 1,
             'number'       => 2,
             'softwares_id' => '_test_soft',
          ],
          [
             'name'         => '_test_softlic_2',
             'completename' => '_test_softlic_2',
             'level'        => 0,
             'entities_id'  => '_test_root_entity',
             'is_recursive' => 1,
             'number'       => 3,
             'softwares_id' => '_test_soft',
          ],
          [
             'name'         => '_test_softlic_3',
             'completename' => '_test_softlic_3',
             'level'        => 0,
             'entities_id'  => '_test_root_entity',
             'is_recursive' => 1,
             'number'       => 5,
             'softwares_id' => '_test_soft',
          ],
          [
             'name'         => '_test_softlic_4',
             'completename' => '_test_softlic_4',
             'level'        => 0,
             'entities_id'  => '_test_root_entity',
             'is_recursive' => 1,
             'number'       => 2,
             'softwares_id' => '_test_soft',
          ],
          [
             'name'         => '_test_softlic_child',
             'completename' => '_test_softlic_child',
             'level'        => 0,
             'entities_id'  => '_test_root_entity',
             'is_recursive' => 1,
             'number'       => 1,
             'softwares_id' => '_test_soft',
             'softwarelicenses_id' => '_test_softlic_1',
          ],
       ], 'Item_SoftwareLicense' => [
          [
             'softwarelicenses_id'   => '_test_softlic_1',
             'items_id'              => '_test_pc21',
             'itemtype'              => 'Computer',
          ], [
             'softwarelicenses_id'   => '_test_softlic_1',
             'items_id'              => '_test_pc01',
             'itemtype'              => 'Computer',
          ], [
             'softwarelicenses_id'   => '_test_softlic_1',
             'items_id'              => '_test_pc02',
             'itemtype'              => 'Computer',
          ], [
             'softwarelicenses_id'   => '_test_softlic_2',
             'items_id'              => '_test_pc02',
             'itemtype'              => 'Computer',
          ], [
             'softwarelicenses_id'   => '_test_softlic_3',
             'items_id'              => '_test_pc02',
             'itemtype'              => 'Computer',
          ], [
             'softwarelicenses_id'   => '_test_softlic_3',
             'items_id'              => '_test_pc21',
             'itemtype'              => 'Computer',
          ], [
             'softwarelicenses_id'   => '_test_softlic_2',
             'items_id'              => '_test_pc21',
             'itemtype'              => 'Computer',
          ]
       ], 'devicesimcard' => [
          [
             'designation'         => '_test_simcard_1',
             'entities_id'         => '_test_root_entity',
             'is_recursive'        => 1,
          ]
       ], 'DeviceSensor' => [
          [
             'designation'  => '_test_sensor_1',
             'entities_id'  => '_test_root_entity',
             'is_recursive' => 1
          ]
       ], 'AuthLDAP' => [
          [
             'name'            => '_local_ldap',
             'host'            => 'openldap',
             'basedn'          => 'dc=glpi,dc=org',
             'rootdn'          => 'cn=Manager,dc=glpi,dc=org',
             'port'            => '3890',
             'condition'       => '(objectclass=inetOrgPerson)',
             'login_field'     => 'uid',
             'rootdn_passwd'   => 'insecure',
             'is_default'      => 1,
             'is_active'       => 0,
             'use_tls'         => 0,
             'email1_field'    => 'mail',
             'realname_field'  => 'cn',
             'firstname_field' => 'sn',
             'phone_field'     => 'telephonenumber',
             'comment_field'   => 'description',
             'title_field'     => 'title',
             'category_field'  => 'businesscategory',
             'language_field'  => 'preferredlanguage',
             'group_search_type'  => \AuthLDAP::GROUP_SEARCH_GROUP,
             'group_condition' => '(objectclass=groupOfNames)',
             'group_member_field' => 'member'
          ]
       ], 'Holiday'   => [
          [
             'name'         => 'X-Mas',
             'entities_id'  => '_test_root_entity',
             'is_recursive' => 1,
             'begin_date'   => '2018-12-29',
             'end_date'     => '2019-01-06'
          ]
       ], 'Plugin' => [
          [
             'directory'    => 'tester',
             'name'         => 'tester',
             'version'      => '1.0.0',
             'state'        => 1,
          ]
       ],
    ];


    // To bypass various right checks
    $session_bak = $_SESSION;
    $_SESSION['glpishowallentities'] = 1;
    $_SESSION['glpicronuserrunning'] = "cron_phpunit";
    $_SESSION['glpi_use_mode']       = Session::NORMAL_MODE;
    $_SESSION['glpiactive_entity']   = 0;
    $_SESSION['glpiactiveentities']  = [0];
    $_SESSION['glpiactiveentities_string'] = "'0'";
    $CFG_GLPI['root_doc']            = '/glpi';

    $DB->beginTransaction();

    Config::setConfigurationValues('core', ['url_base'     => GLPI_URI,
                                            'url_base_api' => GLPI_URI . '/apirest.php']);
    $CFG_GLPI['url_base']      = GLPI_URI;
    $CFG_GLPI['url_base_api']  = GLPI_URI . '/apirest.php';

    // make all caldav component available for tests (for default usage we don't VTODO)
    $CFG_GLPI['caldav_supported_components']  = ['VEVENT', 'VJOURNAL', 'VTODO'];

    $conf = Config::getConfigurationValues('phpunit');
    if (isset($conf['dataset']) && $conf['dataset'] == $data['_version']) {
        printf("\nGLPI dataset version %s already loaded\n\n", $data['_version']);
    } else {
        printf("\nLoading GLPI dataset version %s\n", $data['_version']);

        $ids = [];
        foreach ($data as $type => $inputs) {
            if ($type[0] == '_') {
                continue;
            }
            foreach ($inputs as $input) {
                // Resolve FK
                foreach ($input as $k => $v) {
                    // $foreigntype = $type; // by default same type than current type (is the case of the dropdowns)
                    $foreigntype = false;
                    $match = [];
                    if (isForeignKeyField($k) && (preg_match("/(.*s)_id$/", $k, $match) || preg_match("/(.*s)_id_/", $k, $match))) {
                        $foreigntypetxt = array_pop($match);
                        if (substr($foreigntypetxt, 0, 1) !== '_') {
                            $foreigntype = getItemTypeForTable("glpi_$foreigntypetxt");
                        }
                    }
                    if ($foreigntype && isset($ids[$foreigntype][$v]) && !is_numeric($v)) {
                        $input[$k] = $ids[$foreigntype][$v];
                    } elseif ($k == 'items_id'  &&  isset($input['itemtype']) && isset($ids[$input['itemtype']][$v]) && !is_numeric($v)) {
                        $input[$k] = $ids[$input['itemtype']][$v];
                    } elseif ($foreigntype && $foreigntype != 'UNKNOWN' && !is_numeric($v)) {
                        // not found in ids array, then must get it from DB
                        if ($obj = getItemByTypeName($foreigntype, $v)) {
                            $input[$k] = $obj->getID();
                        }
                    }
                }
                if (isset($input['name']) && $item = getItemByTypeName($type, $input['name'])) {
                    $input['id'] = $ids[$type][$input['name']] = $item->getField('id');
                    $item->update($input);
                    echo ".";
                } else {
                    // Not found, create it
                    $item = getItemForItemtype($type);
                    $id = $item->add($input);
                    echo "+";
                    if (isset($input['name'])) {
                        $ids[$type][$input['name']] = $id;
                    }
                }
            }
        }
        Search::$search = [];
        echo "\nDone\n\n";
        Config::setConfigurationValues('phpunit', ['dataset' => $data['_version']]);
    }
    $DB->commit();

    $_SESSION = $session_bak; // Unset force session variables
}

/**
 * Test helper, search an item from its type and name
 *
 * @param string  $type
 * @param string  $name
 * @param boolean $onlyid
 * @return CommonDBTM|false the item, or its id
 */
function getItemByTypeName($type, $name, $onlyid = false)
{

    $item = getItemForItemtype($type);
    $nameField = $type::getNameField();
    if ($item->getFromDBByCrit([$nameField => $name])) {
        return ($onlyid ? $item->getField('id') : $item);
    }
    return false;
}

loadDataset();
