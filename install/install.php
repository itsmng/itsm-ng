<?php

session_start();
define('GLPI_ROOT', realpath('..'));

include_once(GLPI_ROOT . "/inc/based_config.php");
include_once(GLPI_ROOT . "/inc/db.function.php");
include_once(GLPI_ROOT . "/src/twig/twig.utils.php");

$GLPI = new GLPI();
$GLPI->initLogger();
$GLPI->initErrorHandler();

Config::detectRootDoc();
require_once '../vendor/autoload.php';
use Glpi\System\RequirementsManager;

require_once GLPI_ROOT . "/src/languages/language.class.php";

//allow previous page action
header("Cache-Control: private, max-age=10800, pre-check=10800");
header("Pragma: private");
header("Expires: " . date(DATE_RFC822, strtotime("+2 day")));

//get header data, raw in TWIG
$header_data = [
    "javascript"    =>  [
        Html::script("public/lib/base.js"),
        Html::script("public/lib/fuzzy.js"),
        Html::script("js/common.js"),
        Html::script("js/tableExport.min.js"),
        Html::script("vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"),
        Html::script("vendor/wenzhixin/bootstrap-table/dist/bootstrap-table.min.js"),
        Html::script("js/bootstrap-table-export.min.js"),
        Html::script("node_modules/@tanstack/table-core/build/umd/index.production.js"),
        Html::script("public/lib/nanostores.js"),
        Html::script("node_modules/htm/dist/htm.umd.js"),
        Html::script("node_modules/vhtml/dist/vhtml.min.js"),
        Html::script("js/table.js"),
        ],
    "css"     =>  [
        Html::css('vendor/twbs/bootstrap/dist/css/bootstrap.min.css'),
        Html::css('vendor/wenzhixin/bootstrap-table/dist/bootstrap-table.min.css'),
        Html::css('public/lib/base.css'),
        Html::css("css/style_install.css"),
        ]
];

$steps =   ['0', '1', '2', '3', '4', '5', '6','7','8', 'error'];
$steps_name = ['languages', 'license', 'install', 'requirement', 'login', 'databases', 'initialization', 'initialized', 'done', 'error'];

$header_data["steps_name"] = $steps_name;
global $CFG_GLPI;

//checks if the step is valid
if (isset($_GET['step']) and in_array($_GET['step'], $steps)) {
    $step = $_GET['step'];
} else {
    $step = '0';
}

$twig_vars = [];
Session::loadLanguage('', false);
switch ($step) {
    case "0":
        $regions = Language::getLanguagesByRegion();

        if (isset($_SESSION['language'])) {
            $language = $_SESSION['language'];
        } else {
            $language = Session::getPreferredLanguage();
        }
        $twig_vars = ['languages' => $regions , 'preferred_language' =>  $language];
        break;

    case "1":
        if (isset($_POST['language'])) {
            $_SESSION['language'] = $_POST['language'];
            $_SESSION['glpilanguage'] = $_SESSION['language']; //required due to the way Session::loadLanguage works
        }
        Session::loadLanguage($_SESSION['language'], false);
        $license = file_get_contents("../COPYING.txt");
        $twig_vars = ['license' => $license];
        break;

    case "3":
        if (isset($_POST['install'])) {
            $_SESSION['action'] = 'install';
        } elseif (isset($_POST['update'])) {
            $_SESSION['action'] = 'update';
        }
        $raw_requirements = (new RequirementsManager())->getCoreRequirementList(null);
        $requirements = [];
        foreach ($raw_requirements as $raw_requirement) {
            if (!$raw_requirement->isOutOfContext()) { // skips raw_requirement if not relevant
                $title = $raw_requirement->getTitle();
                $required = $raw_requirement->isMissing() && !$raw_requirement->isOptional();
                $optional = $raw_requirement->isMissing() && $raw_requirement->isOptional();
                $validated = $raw_requirement->isValidated();
                $message = implode('. ', Html::entities_deep($raw_requirement->getValidationMessages()));
                $requirement = ['title' => $title, 'required' => $required, 'validated' => $validated, 'optional' => $optional, 'message' => $message];
                $requirements[] = $requirement;
            }
        }
        if ($raw_requirements->hasMissingMandatoryRequirements()) {
            $missing_requirements = "mandatory";
        } elseif ($raw_requirements->hasMissingOptionalRequirements()) {
            $missing_requirements = "optional";
        } else {
            $missing_requirements = "none";
        }

        $twig_vars = ['requirements' => $requirements, 'missing_requirements' => $missing_requirements ];
        break;

    case "4":
        $host = isset($_SESSION['db_host']) ? $_SESSION['db_host'] : "";
        $user = isset($_SESSION['db_user']) ? $_SESSION['db_user'] : "";

        $twig_vars = ['host' => $host, 'user' => $user];
        break;

    case "5":
        if (isset($_POST['db_host'])) {
            $_SESSION['db_host'] = $_POST['db_host'];
            $_SESSION['db_user'] = $_POST['db_user'];
            $_SESSION['db_pass'] = $_POST['db_pass'];
        }
        error_reporting(16);
        mysqli_report(MYSQLI_REPORT_OFF);
        $hostport = explode(":", $_SESSION['db_host']);
        if (count($hostport) < 2) {
            $link = new mysqli($hostport[0], $_SESSION['db_user'], $_SESSION['db_pass']);
        } else {
            $link = new mysqli($hostport[0], $_SESSION['db_user'], $_SESSION['db_pass'], '', $hostport[1]);
        }
        $connect_error = $link->connect_error;
        if (!$connect_error) {
            $DB_ver = $link->query("SELECT version()");
            $row = $DB_ver->fetch_array();
            $version = $row[0];
            $result = Config::checkDbEngine($version);
            $version = key($result);
            $db_ver = $result[$version];
            if (!$db_ver) {
                $ver_too_old = true;
            } else {
                $ver_too_old = false;
                $databases_info = [];
                $db_info = [];
                if ($DB_list = $link->query(
                    "SELECT S.schema_name AS 'name', COUNT(T.table_name) AS 'table_count', DATE(MIN(T.create_time)) AS 'table_create', DATE(MAX(T.update_time)) AS 'table_update'
                    FROM information_schema.tables AS T
                    RIGHT JOIN information_schema.schemata AS S
                    ON S.schema_name = T.table_schema
                    GROUP BY S.schema_name;"
                )) {
                    while ($row = $DB_list->fetch_array(MYSQLI_NUM)) {
                        if (!in_array($row[0], ["information_schema","mysql","performance_schema","sys"])) {
                            $databases_info[] = array_combine(["name", "table_count", "creation_date", "last_update"], $row);
                        }
                    }
                }
            }
            $link->close();
        }
        $twig_vars = [  'host' =>           $_SESSION['db_host'],   'user' =>       $_SESSION['db_user'],
                        'connect_error' =>  $connect_error,         'version' =>    $version,
                        'ver_too_old' =>    $ver_too_old,           'action' =>     $_SESSION['action'],
                        'databases' =>      $databases_info];
        break;

    case "6":
        if (isset($_POST['newdatabasename']) and $_POST['newdatabasename'] != "") {
            $new_db = true;
            $_SESSION["databasename"] = $_POST['newdatabasename'];
        } else {
            $new_db = false;
            $_SESSION["databasename"] = json_decode($_POST["database"], true)[0]["name"];
        }
        $db_created = false;
        $sql_error = "";
        $error = "";
        $db_state = "";
        if ($_SESSION['action'] == 'install') {
            $glpikey = new GLPIKey();
            $secured = $glpikey->keyExists();
            if (!$secured) {
                $secured = $glpikey->generate();
                $error = "secured";
            }
            if ($secured) {
                mysqli_report(MYSQLI_REPORT_OFF);
                $hostport = explode(":", $_SESSION['db_host']);
                if (count($hostport) < 2) {
                    $link = new mysqli($hostport[0], $_SESSION['db_user'], $_SESSION['db_pass']);
                } else {
                    $link = new mysqli($hostport[0], $_SESSION['db_user'], $_SESSION['db_pass'], '', $hostport[1]);
                }
                $databasename = $link->real_escape_string($_SESSION['databasename']);// use db already created
                $DB_selected = $link->select_db($databasename);
                if ($new_db && !$DB_selected) {
                    if ($link->query("CREATE DATABASE IF NOT EXISTS `".$databasename."`")) {
                        $DB_selected = $link->select_db($databasename);
                        $db_created = true;
                    } else {
                        $error = "create_db";
                    }
                }
                if (!$DB_selected) {
                    $sql_error = $link->error;
                    $error = "use";
                } else {
                    if (DBConnection::createMainConfig($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['databasename'])) {
                    } else {
                        $error = "setup";
                    }
                }
            } else {
                $error = "select";
            }
        } elseif ($_SESSION['action'] == 'update') {
            if (DBConnection::createMainConfig($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['databasename'])) {
                global $DB;
                $_SESSION['can_process_update'] = true;
                $update = [
                    'db' => $_SESSION['databasename'],
                ];
            } else { // can't create config_db file
                $error = "create_config";
            }
        }

        if (!isset($secured)) {
            $secured = false;
        }

        $twig_vars = [  'action'    => $_SESSION['action'],
                        'created'   =>  $db_created,
                        'error'     => $error,
                        'secured'   => $secured,
                        'sql_error' => $sql_error,
                        'update'    => isset($update) ? $update : null,
                    ];
        break;
    case "7":
        Toolbox::createSchema($_SESSION['language']);
        // no break
    case "8":
        include_once(GLPI_ROOT . "/inc/dbmysql.class.php");
        include_once(GLPI_CONFIG_DIR . "/config_db.php");
        $DB = new DB();

        $url_base = str_replace("/install/install.php", "", $_SERVER['HTTP_REFERER']);
        $DB->update(
            'glpi_configs',
            ['value' => $DB->escape($url_base)],
            [
                'context'   => 'core',
                'name'      => 'url_base'
            ]
        );

        $url_base_api = "$url_base/apirest.php/";
        $DB->update(
            'glpi_configs',
            ['value' => $DB->escape($url_base_api)],
            [
                'context'   => 'core',
                'name'      => 'url_base_api'
            ]
        );
}

try {
    renderTwigTemplate('install/index.twig', [
        'step' => ['number' => $step, 'progress' => $step / count($steps), 'name' => $steps_name[$step]],
        'header_data' => $header_data] + $twig_vars);
} catch (\Exception $e) {
    echo $e->getMessage();
}
