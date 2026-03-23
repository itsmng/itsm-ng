<?php

session_start();
define('GLPI_ROOT', realpath('..'));

include_once(GLPI_ROOT . "/inc/based_config.php");
include_once(GLPI_ROOT . "/inc/db.function.php");
include_once(GLPI_ROOT . "/inc/dbmysql.class.php");
include_once(GLPI_ROOT . "/inc/dbpgsql.class.php");
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

function normalizeInstallDbType(?string $db_type): string
{
    return match (strtolower(trim((string) $db_type))) {
        'pgsql', 'postgres', 'postgresql' => 'pgsql',
        default                           => 'mysql',
    };
}

function getInstallAdminDatabaseName(string $db_type): string
{
    return $db_type === 'pgsql' ? 'postgres' : '';
}

function createInstallDatabaseConnection(
    string $db_type,
    string $db_host,
    string $db_user,
    string $db_pass,
    string $db_name = ''
) {
    $class = $db_type === 'pgsql' ? DBpgsql::class : DBmysql::class;

    return new $class([
        'dbhost'     => $db_host,
        'dbuser'     => $db_user,
        'dbpassword' => rawurlencode($db_pass),
        'dbdefault'  => $db_name,
        'dbtype'     => $db_type,
    ]);
}

function getInstallDatabasesInfo($db_connection, string $db_type): array
{
    $databases_info = [];

    if ($db_type === 'pgsql') {
        $result = $db_connection->query(
            "SELECT datname AS name
            FROM pg_database
            WHERE datistemplate = false
            ORDER BY datname"
        );

        if ($result) {
            while ($row = $db_connection->fetchAssoc($result)) {
                $databases_info[] = [
                    'name'          => $row['name'],
                    'table_count'   => '',
                    'creation_date' => '',
                    'last_update'   => '',
                ];
            }
        }

        return $databases_info;
    }

    $result = $db_connection->query(
        "SELECT S.schema_name AS name,
                COUNT(T.table_name) AS table_count,
                DATE(MIN(T.create_time)) AS creation_date,
                DATE(MAX(T.update_time)) AS last_update
        FROM information_schema.tables AS T
        RIGHT JOIN information_schema.schemata AS S
            ON S.schema_name = T.table_schema
        GROUP BY S.schema_name
        ORDER BY S.schema_name"
    );

    if ($result) {
        while ($row = $db_connection->fetchAssoc($result)) {
            if (!in_array($row['name'], ["information_schema", "mysql", "performance_schema", "sys"])) {
                $databases_info[] = $row;
            }
        }
    }

    return $databases_info;
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
        $db_type = normalizeInstallDbType($_SESSION['db_type'] ?? 'mysql');

        $twig_vars = ['host' => $host, 'user' => $user, 'db_type' => $db_type];
        break;

    case "5":
        if (isset($_POST['db_host'])) {
            $_SESSION['db_host'] = $_POST['db_host'];
            $_SESSION['db_user'] = $_POST['db_user'];
            $_SESSION['db_pass'] = $_POST['db_pass'];
            $_SESSION['db_type'] = normalizeInstallDbType($_POST['db_type'] ?? 'mysql');
        }

        $db_type = normalizeInstallDbType($_SESSION['db_type'] ?? 'mysql');
        $_SESSION['db_type'] = $db_type;

        $version = '';
        $ver_too_old = false;
        $databases_info = [];
        $connect_error = '';

        $link = createInstallDatabaseConnection(
            $db_type,
            $_SESSION['db_host'],
            $_SESSION['db_user'],
            $_SESSION['db_pass'],
            getInstallAdminDatabaseName($db_type)
        );
        $connect_error = $link->connected ? '' : $link->error();
        if (!$connect_error) {
            $version = $link->getVersion();
            $result = Config::checkDbEngine($version, $db_type);
            $version = key($result);
            $db_ver = $result[$version];
            if (!$db_ver) {
                $ver_too_old = true;
            } else {
                $databases_info = getInstallDatabasesInfo($link, $db_type);
            }
            $link->close();
        }
        $twig_vars = [  'host' =>           $_SESSION['db_host'],   'user' =>       $_SESSION['db_user'],
                        'connect_error' =>  $connect_error,         'version' =>    $version,
                        'ver_too_old' =>    $ver_too_old,           'action' =>     $_SESSION['action'],
                        'databases' =>      $databases_info,        'db_type' =>    $db_type];
        break;

    case "6":
        $db_type = normalizeInstallDbType($_SESSION['db_type'] ?? 'mysql');
        $_SESSION['db_type'] = $db_type;

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
                $link = createInstallDatabaseConnection(
                    $db_type,
                    $_SESSION['db_host'],
                    $_SESSION['db_user'],
                    $_SESSION['db_pass'],
                    getInstallAdminDatabaseName($db_type)
                );

                if (!$link->connected) {
                    $sql_error = $link->error();
                    $error = "use";
                } else {
                    $DB_selected = createInstallDatabaseConnection(
                        $db_type,
                        $_SESSION['db_host'],
                        $_SESSION['db_user'],
                        $_SESSION['db_pass'],
                        $_SESSION['databasename']
                    );

                    if (!$DB_selected->connected && $new_db) {
                        $database_exists = $link->databaseExists($_SESSION['databasename']);
                        if ($link->createDatabase($_SESSION['databasename'])) {
                            $DB_selected = createInstallDatabaseConnection(
                                $db_type,
                                $_SESSION['db_host'],
                                $_SESSION['db_user'],
                                $_SESSION['db_pass'],
                                $_SESSION['databasename']
                            );
                            $db_created = !$database_exists;
                        } else {
                            $sql_error = $link->error();
                            $error = "create_db";
                        }
                    }

                    if ($error === "") {
                        if (!$DB_selected->connected) {
                            $sql_error = $DB_selected->error();
                            $error = "use";
                        } elseif (!DBConnection::createMainConfig($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['databasename'], $db_type)) {
                            $error = "setup";
                        } else {
                            $DB_selected->close();
                        }
                    }

                    if ($link->connected) {
                        $link->close();
                    }
                    if (isset($DB_selected) && $DB_selected instanceof DBmysql && $DB_selected->connected) {
                        $DB_selected->close();
                    }
                }
            } else {
                $error = "select";
            }
        } elseif ($_SESSION['action'] == 'update') {
            if (DBConnection::createMainConfig($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['databasename'], $db_type)) {
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
    case "8":
        $db_type = normalizeInstallDbType($_SESSION['db_type'] ?? 'mysql');
        include_once(GLPI_CONFIG_DIR . "/config_db.php");
        $DB = new DB();

        if ($step === "7") {
            Toolbox::createSchema($_SESSION['language'], $DB);
        }

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
        break;
}

try {
    renderTwigTemplate('install/index.twig', [
        'step' => ['number' => $step, 'progress' => $step / count($steps), 'name' => $steps_name[$step]],
        'header_data' => $header_data] + $twig_vars);
} catch (\Exception $e) {
    echo $e->getMessage();
}
