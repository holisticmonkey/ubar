<?php
# ALLOW INITIALIZATION OF CLASSES
// TODO: consider having ubar_resources.properties for error messages and similar

// test cases call this directly instead of through index.php so need to define root
if(!defined('UBAR_ROOT')) {
	define('UBAR_ROOT', dirname(__FILE__) . "/");
}

// include functions
// TODO: require everything the functions folder?
require_once (UBAR_ROOT . "/functions/misc.php");

// allow the following folders of classes to be auto-loaded
getClassPaths(UBAR_ROOT . "/constants", TRUE);
getClassPaths(UBAR_ROOT . "/exception", TRUE);
getClassPaths(UBAR_ROOT . "/core", TRUE);

# GET PROPERTIES
$props = new Properties(UBAR_ROOT . "ubar_config.properties", true);

# DEFINE CONSTANTS FROM PROPERTIES FILE
// get dev mode first to know which property name to use
define('DEV_MODE', $props->getBool('DEV_MODE', GlobalConstants :: DEV_MODE));
define('PROP_APPEND', DEV_MODE ? '_DEV_MODE' : '');
// TODO: use PROP_APPEND to get right property and get rid of separate dev mode defs...
define('LOCALE_DEFAULT', $props->get('LOCALE_DEFAULT', GlobalConstants :: LOCALE_DEFAULT));
define('DISPLAY_ERRORS', $props->getBool('DISPLAY_ERRORS', GlobalConstants :: DISPLAY_ERRORS));
define('HTML_ERRORS', $props->getBool('HTML_ERRORS', GlobalConstants :: HTML_ERRORS));
define('ERROR_LEVEL', $props->get('ERROR_LEVEL' . PROP_APPEND, GlobalConstants :: ERROR_LEVEL));
define('LOG_ERRORS', $props->getBool('LOG_ERRORS' . PROP_APPEND, GlobalConstants :: LOG_ERRORS));
define('MAGIC_QUOTES', $props->getBool('MAGIC_QUOTES', GlobalConstants :: MAGIC_QUOTES));
define('SESSION_LIFETIME', $props->get('SESSION_LIFETIME', GlobalConstants :: SESSION_LIFETIME));
define('CHARSET', $props->get('CHARSET', GlobalConstants :: CHARSET));
define('USE_XHTML', $props->getBool('USE_XHTML', GlobalConstants :: USE_XHTML));
define('PROPERTIES_PATH', UBAR_ROOT . "/" . $props->get('PROPERTIES_PATH' . PROP_APPEND, GlobalConstants :: BASE_PROPERTIES_PATH));
define('PROPERTIES_ROOT', $props->get('PROPERTIES_ROOT', GlobalConstants :: PROPERTIES_ROOT));

// try to define base action path and base view path from configuration
// note, defaults to expected installation struction
define('BASE_ACTION_PATH', UBAR_ROOT . "/" . $props->get('BASE_ACTION_PATH' . PROP_APPEND, GlobalConstants :: BASE_ACTION_PATH));
if (!is_dir( BASE_ACTION_PATH)) {
	throw new Exception("Unable to find specified action root path at \"" . BASE_ACTION_PATH . "\".");
}
getClassPaths(BASE_ACTION_PATH, TRUE);

define('BASE_VIEW_PATH', UBAR_ROOT . "/" . $props->get('BASE_VIEW_PATH' . PROP_APPEND, GlobalConstants :: BASE_VIEW_PATH));
if (!is_dir(BASE_VIEW_PATH)) {
	throw new Exception("Unable to find specified view root path at \"" . BASE_VIEW_PATH . "\".");
}

// define default timezone
try {
	define('TIMEZONE_DEFAULT', $props->get('TIMEZONE_DEFAULT'));
} catch (Exception $e) {
	// none defined, skip
}

# DEFINE DB SETTINGS
define('DB_USE', $props->getBool('DB_USE', GlobalConstants :: DB_USE));
if (DB_USE) {
	try {
		define('DB_SERVER', $props->get('DB_SERVER' . PROP_APPEND));
		define('DB_USERNAME', $props->get('DB_USERNAME' . PROP_APPEND));
		define('DB_PASSWORD', $props->get('DB_PASSWORD' . PROP_APPEND));
		define('DB_NAME', $props->get('DB_NAME' . PROP_APPEND));
		define('DB_ALWAYS_CONNECT', $props->getBool('DB_ALWAYS_CONNECT', GlobalConstants :: DB_ALWAYS_CONNECT));
	} catch (Exception $e) {
		throw new Exception("This application is configured to connect to a database but one or more required configurations was missing");
	}
}

# CONFIGURE APPLICATION BASED ON PROPERTIES

# SET HTML IN ERRORS
ini_set('html_errors', HTML_ERRORS ? GLobalConstants :: INI_OFF : GLobalConstants :: INI_ON);

# CONFIGURE ERROR DISPLAY
ini_set('display_errors', (DEV_MODE || DISPLAY_ERRORS) ? GLobalConstants :: INI_ON : GLobalConstants :: INI_OFF);
ini_set('error_reporting', E_ALL | E_STRICT);

# SET CUSTOM ERROR HANDLER
if (function_exists("errorHandler")) {
	set_error_handler("errorHandler", E_ALL);
}
if (function_exists("exceptionHandler")) {
	set_exception_handler("exceptionHandler");
}


# SET CACHE RELATED HEADERS

# SET CONDITIONAL HEADERS FOR XHTML
$contentType = "text/html";
if (USE_XHTML && stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
	$contentType = 'application/xhtml+xml';
}
header("Content-Type: $contentType;charset=" . CHARSET);

# set session to 1 day
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

# start session
session_start();
/*
# db connection params
$db_server = DEV_MODE ? DB_SERVER_DEV_MODE : DB_SERVER;
$db_user = DEV_MODE ? DB_USERNAME_DEV_MODE : DB_USERNAME;
$db_password = DEV_MODE ? DB_PASSWORD_DEV_MODE : DB_PASSWORD;
$db_name = DEV_MODE ? DB_NAME_DEV_MODE : DB_NAME;

# connect to db if necessary
// TODO: move this out into the controller since there may be page configuration on the connection
if(ALWAYS_CONNECT || (isset($usesDB) && $usesDB === true)){
	if(!mysql_connect($db_server, $db_user, $db_password)){
		addError("Unable to connect to database: " . mysql_error());
	}
	// select database
	if(!mysql_select_db($db_name)) {
		// TODO: externalize all these queries
		// if it doesn't exist, create it and create necessary tables
		mysql_query("CREATE DATABASE " . $db_name);
		mysql_select_db($db_name);
		// create users table
			// id, name, email, password, isAdmin, isConfirmed
		// create blog table
			// title, body, date
		// create links and resources table
			// title, description, date
		// create email list table
	}
}

# if user, get them and init user object
$_SESSION['userID'] = 1;
if(isset($_SESSION['userID'])) {
	// TODO: replace this query with a call to query provider
	$query = "SELECT * FROM Users WHERE userID=" . 1;
	if(!$result = mysql_query($query)){
		//addError("Error running query: " . mysql_error());
	}
	// init User object using id, class knows what queries to run to populate self. throws InvalidUserError if not found
}
*/
# determine if user allowed to see this page, redirect if not
// if page requires user and user not logged in - fail
// if page requires admin and user not admin - fail

# set timezone
// get from user if present and set
// default if no value or value from user not valid
// TODO: consider moving this to controller and allowing project to override?
if (defined('TIMEZONE_DEFAULT')) {
	date_default_timezone_set(TIMEZONE_DEFAULT);
}

# set locale
// TODO: consider moving this to controller and allowing project to override?
// $_SERVER['HTTP_ACCEPT_LANGUAGE']
// get from user if present and set
// else get from cookie if present
// else get from headers if something useful set
// default if no value or value from user not valid<br />

//http://us.php.net/setlocale
// temp assignment of locale to default for dev purposes
$localeNameArray = LOCALE_DEFAULT;
// TODO: should I put a fallback here?
// ex $loc_de = setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
// set locale and retrieve locale string
//TODO: strip whitespace before doing explode
$currentLocale = setlocale(LC_ALL, explode(",", $localeNameArray));
// set locale string to a constant to be used by property and resource retrieval
define('LOCALE', $currentLocale);

// set up localized properties? pick up from session since may be from user settings?

// get action definitions
// TODO: get dtd hosted somewhere and allow ubar.xml to be moved
$dispatcher = new Dispatcher(UBAR_ROOT . "/ubar.xml");

// not that framework is setup, let the controller dispatch the request
$dispatcher->dispatch();


?>