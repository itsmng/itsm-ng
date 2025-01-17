<?php

// Check PHP version not to have trouble
// Need to be the very fist step before any include
if (version_compare(PHP_VERSION, '8.0.0') < 0) {
    die('PHP >= 8.0.0 required');
}
//Load GLPI constants
define('GLPI_ROOT', __DIR__);
include(GLPI_ROOT . "/inc/based_config.php");

define('DO_NOT_CHECK_HTTP_REFERER', 1);

// If config_db doesn't exist -> start installation
if (!file_exists(GLPI_CONFIG_DIR . "/config_db.php")) {
    Html::redirect("install/install.php");
    die();
}

$TRY_OLD_CONFIG_FIRST = true;
include(GLPI_ROOT . "/inc/includes.php");
$_SESSION["glpicookietest"] = 'testcookie';

// For compatibility reason
if (isset($_GET["noCAS"])) {
    $_GET["noAUTO"] = $_GET["noCAS"];
}

if (!isset($_GET["noAUTO"])) {
    Auth::redirectIfAuthenticated();
}
Auth::checkAlternateAuthSystems(true, isset($_GET["redirect"]) ? $_GET["redirect"] : "");
// Appel CSS
$theme = isset($_SESSION['glpipalette']) ? $_SESSION['glpipalette'] : 'itsmng';

$entity = new Entity(); // Custom CSS for root entity
$entity->getFromDB('0');

$css = [
    Html::scss('css/styles'),
    Html::scss('css/palettes/' . $theme),
    Html::css('public/lib/base.css'), // external libs CSS
    Html::scss('css/itsm2.scss'),
    $entity->getCustomCssTag(), // Custom CSS for root entity
];
if (isset($_SESSION['glpihighcontrast_css']) && $_SESSION['glpihighcontrast_css']) {
    $css[] = Html::scss('css/highcontrast');
}
$javascript = [
    Html::script("public/lib/base.js"),
    Html::script("public/lib/fuzzy.js"),
    Html::script('js/common.js'),
    Html::getCoreVariablesForJavascript() // CFG

];

//get header data, raw in TWIG
$header_data = [
    "javacript" => $javascript,
    "css" => $css
];
// Initialize Twig variables
$twig_vars = [];

$twig_vars["text_login"] = nl2br(Toolbox::unclean_html_cross_side_scripting_deep(htmlspecialchars($CFG_GLPI['text_login'])));


// Display oidc login
global $DB;
$criteria = "SELECT * FROM glpi_oidc_config";
$iterators = $DB->request($criteria);
foreach ($iterators as $iterator) {
    $is_activate = $iterator['is_activate'];
    $is_forced = $iterator['is_forced'];
}

if (isset($is_activate) && $is_activate) {
    if ($is_forced && !isset($_GET["noAUTO"])) {
        Html::redirect("front/oidc.php");
    }
    $twig_vars["is_activate"] = $is_activate;
    if (isset($_POST["login_oidc"])) {
        Html::redirect("front/oidc.php"
            . ((isset($_POST['redirect']) && !empty($_POST['redirect'])) ? "?redirect=" . Html::entities_deep($_POST['redirect']) : ""));
    }
}

$_SESSION['namfield'] = $twig_vars["namfield"] = uniqid('fielda');
$_SESSION['pwdfield'] = $twig_vars["pwdfield"] = uniqid('fieldb');
$_SESSION['rmbfield'] = $twig_vars["rmbfield"] = uniqid('fieldc');

// Other case
if (isset($_GET["noAUTO"])) {
    $twig_vars["noAUTO_set"] = true;
}

// redirect to ticket
if (isset($_GET["redirect"])) {
    Toolbox::manageRedirect($_GET["redirect"]);
    $twig_vars["redirect_set"] = true;
    $twig_vars["redirect"] = $_GET["redirect"];
}

if (GLPI_DEMO_MODE) {
    //lang selector
    require_once GLPI_ROOT . "/src/languages/language.class.php";
    $twig_vars["demo_mode"] = true;
    $twig_vars["languages"] = Language::showLanguages('language', [
        'display_emptychoice'   => true,
        'emptylabel'            => __('Default (from user profile)'),
        'width'                 => '100%'
     ]);
    $twig_vars["current_language"] = $_SESSION["glpilanguage"];
}

// Add dropdown for auth (local, LDAPxxx, LDAPyyy, imap...)
if ($CFG_GLPI['display_login_source']) {
    $twig_vars["display_login_source"] = true;
    $twig_vars["auth_dropdown"] = Auth::dropdownLogin();
    $twig_vars["auth_dropdown_default"] = Auth::getDefaultLoginAuthSource();
}

if ($CFG_GLPI["login_remember_time"]) {
    $twig_vars["login_remember_time"] = true;
    $twig_vars["login_remember_default"] = $CFG_GLPI["login_remember_default"];
}

$twig_vars["login_input_value"] = _sx('button', 'Post');

if (
    $CFG_GLPI["notifications_mailing"]
    && countElementsInTable(
        'glpi_notifications',
        [
         'itemtype'  => 'User',
         'event'     => 'passwordforget',
         'is_active' => 1
        ]
    )
) {
    $twig_vars["show_password_forget"] = true;
}

if (isset($_GET['error']) && isset($_GET['redirect'])) {
    $twig_vars['error'] = $_GET['error'];
}

// Display FAQ is enable
if ($CFG_GLPI["use_public_faq"]) {
    $twig_vars["use_public_faq"] = true;
}

$twig_vars["copyright_message"] = Html::getCopyrightMessage(false);

$twig_vars["csrf_token"] = $_SESSION['_glpi_csrf_token'];

ob_start();
Plugin::doHook("display_login");
$twig_vars['pluginHook'] = ob_get_clean();

// call cron
if (!GLPI_DEMO_MODE) {
    CronTask::callCronForce();
}

renderTwigTemplate('index.twig', [
    "root_doc" => $CFG_GLPI['root_doc'],
    'header_data' => $header_data
] + $twig_vars);
