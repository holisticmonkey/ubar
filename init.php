<?php
/**
 * Initialize the Ubar framework. This is comprised of getting an instance of
 * Properties using ubar_config.properties, defining application constants,
 * creating a new instance of the Dispatcher and dispatching.
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	setup
 *
 */

/**
 * Define the root of the framework as the current folder.
 */
if(!defined('UBAR_ROOT')) {
	define('UBAR_ROOT', dirname(__FILE__) . "/");
}

/**
 * Define the library folder location. This is a helper for calls to other
 * libray folders.
 */
define('LIB_ROOT', UBAR_ROOT . "../");

/**
 * Require necessary application functions.
 * TODO: When more function files, require the entire folder recursively.
 */
require_once (UBAR_ROOT . "/functions/misc.php");

/**
 * Add the constants, exception, and core folders recursively
 */
getClassPaths(UBAR_ROOT . "constants", TRUE);
getClassPaths(UBAR_ROOT . "exception", TRUE);
getClassPaths(UBAR_ROOT . "core", TRUE);

/**
 * Allow recognition of all line ending types, required to recognize mac
 * property files
 */
ini_set("auto_detect_line_endings", true);

/**
 * Get an instance of the config properties. Allow path to be overridden for
 * testing purposes.
 */
if(defined('UBAR_CONFIG_OVERRIDE')) {
	$props = new Properties(UBAR_CONFIG_OVERRIDE, true);
} else {
	$props = new Properties(UBAR_ROOT . "ubar_config.properties", true);
}

# DEFINE CONSTANTS FROM PROPERTIES FILE
/**
 * Define DEV_MODE, defaulting to the value found in DEV_MODE if not set.
 * NOTE: This must be defined prior to other defines due to their values
 * switching on DEV_MODE
 *
 * NOTE: Some test classes may define this before init script loaded.
 *
 * @see GlobalConstants::DEV_MODE
 */
if(!defined('DEV_MODE')) {
	define('DEV_MODE', $props->getBool('DEV_MODE', GlobalConstants :: DEV_MODE));
}

/**
 * Define a property appender based on dev mode to simplify the dev mode value
 * retrieval
 */
define('PROP_APPEND', DEV_MODE ? '_DEV_MODE' : '');

/**
 * Define the default locale.
 * @see GlobalConstants::LOCALE_DEFAULT
 */
define('LOCALE_DEFAULT', $props->get('LOCALE_DEFAULT', GlobalConstants :: LOCALE_DEFAULT));

/**
 * Define display errors flag.
 * @see GlobalConstants::DISPLAY_ERRORS
 */
define('DISPLAY_ERRORS', $props->getBool('DISPLAY_ERRORS', GlobalConstants :: DISPLAY_ERRORS));

/**
 * Define display html errors (vs plain text).
 * @see GlobalConstants::HTML_ERRORS
 */
define('HTML_ERRORS', $props->getBool('HTML_ERRORS', GlobalConstants :: HTML_ERRORS));

/**
 * Define error display level.
 * @see GlobalConstants::ERROR_LEVEL
 */
define('ERROR_LEVEL', $props->get('ERROR_LEVEL' . PROP_APPEND, GlobalConstants :: ERROR_LEVEL));

/**
 * Define log errors flag. Currently unused
 * @see GlobalConstants::LOG_ERRORS
 */
define('LOG_ERRORS', $props->getBool('LOG_ERRORS' . PROP_APPEND, GlobalConstants :: LOG_ERRORS));

/**
 * Define magic quotes flag.
 * @see GlobalConstants::MAGIC_QUOTES
 */
define('MAGIC_QUOTES', $props->getBool('MAGIC_QUOTES', GlobalConstants :: MAGIC_QUOTES));

/**
 * Define session lifetime in seconds.
 * @see GlobalConstants::SESSION_LIFETIME
 */
define('SESSION_LIFETIME', $props->get('SESSION_LIFETIME', GlobalConstants :: SESSION_LIFETIME));

/**
 * Define default charset.
 * @see GlobalConstants::CHARSET
 */
define('CHARSET', $props->get('CHARSET', GlobalConstants :: CHARSET));

/**
 * Define xhtml (vs html) flag.
 * @see GlobalConstants::USE_XHTML
 */
define('USE_XHTML', $props->getBool('USE_XHTML', GlobalConstants :: USE_XHTML));

/**
 * Define path to properties folder relative to UBAR_ROOT.
 * @see GlobalConstants::BASE_PROPERTIES_PATH
 */
define('PROPERTIES_PATH', UBAR_ROOT . "/" . $props->get('PROPERTIES_PATH' . PROP_APPEND, GlobalConstants :: BASE_PROPERTIES_PATH));

/**
 * Define path to properties root name.
 * @see GlobalConstants::PROPERTIES_ROOT
 */
define('PROPERTIES_ROOT', $props->get('PROPERTIES_ROOT', GlobalConstants :: PROPERTIES_ROOT));

/**
 * Define path to action folder. It is not recommended that you alter this.

 * @see GlobalConstants::BASE_ACTION_PATH
 */
define('BASE_ACTION_PATH', UBAR_ROOT . "/" . $props->get('BASE_ACTION_PATH' . PROP_APPEND, GlobalConstants :: BASE_ACTION_PATH));
if (!is_dir( BASE_ACTION_PATH)) {
	throw new Exception("Unable to find specified action root path at \"" . BASE_ACTION_PATH . "\".");
}
getClassPaths(BASE_ACTION_PATH, TRUE);

/**
 * Define path to view folder. It is not recommended that you alter this.

 * @see GlobalConstants::BASE_VIEW_PATH
 */
define('BASE_VIEW_PATH', UBAR_ROOT . "/" . $props->get('BASE_VIEW_PATH' . PROP_APPEND, GlobalConstants :: BASE_VIEW_PATH));
if (!is_dir(BASE_VIEW_PATH)) {
	throw new Exception("Unable to find specified view root path at \"" . BASE_VIEW_PATH . "\".");
}

/**
 * Define path to model folder. This is an optional convenience for autoloading
 * model classes.
 *
 * @todo Consider just having an autoload directory or easier addition of autoload folders.

 * @see GlobalConstants::BASE_MODEL_PATH
 */
define('BASE_MODEL_PATH', UBAR_ROOT . "/" . $props->get('BASE_MODEL_PATH' . PROP_APPEND, GlobalConstants :: BASE_MODEL_PATH));
if (is_dir(BASE_MODEL_PATH)) {
	getClassPaths(BASE_MODEL_PATH, TRUE);
}

// define default timezone
try {
	/**
	 * Define default timezone. If not defined, it will throw an exception that is ignored.
	 */
	define('TIMEZONE_DEFAULT', $props->get('TIMEZONE_DEFAULT'));
} catch (Exception $e) {
	// none defined, skip
}

# DEFINE DB SETTINGS
/**
 * Define flag for database usage. You may obviously use database connectivity
 * without this, this merely indicates an intention to use the built in
 * DBManager and errors will be thrown if configuration does not support
 * database connectivity.
 *
 * @see GlobalConstants::DB_USE
 */
define('DB_USE', $props->getBool('DB_USE', GlobalConstants :: DB_USE));
if (DB_USE) {
	try {
		/**
		 * Define database server name.
		 */
		define('DB_SERVER', $props->get('DB_SERVER' . PROP_APPEND));
		/**
		 * Define database username.
		 */
		define('DB_USERNAME', $props->get('DB_USERNAME' . PROP_APPEND));
		/**
		 * Define database password for user.
		 */
		define('DB_PASSWORD', $props->get('DB_PASSWORD' . PROP_APPEND));
		/**
		 * Define default database to connect to.
		 */
		define('DB_NAME', $props->get('DB_NAME' . PROP_APPEND));
	} catch (Exception $e) {
		throw new Exception("This application is configured to connect to a database but one or more required configurations was missing");
	}
	try {
		/**
		 * Define current schema version, used for automatic schema migration.
		 */
		define('SCHEMA_VERSION', $props->get('SCHEMA_VERSION'));
		/**
		 * Define schema naming convention for .sql files used for automatic
		 * schema migration.
		 */
		define('SCHEMA_PATH', $props->get('SCHEMA_PATH'));
	} catch (Exception $e) {
		// values allowed to be undefined
	}
}

# CONFIGURE APPLICATION BASED ON PROPERTIES

# SET HTML IN ERRORS
ini_set('html_errors', HTML_ERRORS ? GLobalConstants :: INI_OFF : GLobalConstants :: INI_ON);

# CONFIGURE ERROR DISPLAY
ini_set('display_errors', (DEV_MODE || DISPLAY_ERRORS) ? GLobalConstants :: INI_ON : GLobalConstants :: INI_OFF);
ini_set('error_reporting', E_ALL | E_STRICT);

# SET CUSTOM ERROR HANDLERS
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

# set session lifetime
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

# start session
session_start();


// TODO: store permissions object in session? have things that invalidate it flush it?
// TODO: drop this and have UserManager that gets called in dispatcher to determine if should return GlobalConstants::PERMISSION_DENIED?
// TODO: have base action call to get user props and have isAdmin() on it so can display more info on page if admin...
# if user, get them and init user object
if(isset($_SESSION['userid'])) {
	// TODO: replace this query with a call to query provider
	$query = "SELECT * FROM users WHERE userid=" . $_SESSION['userid'];
	if(!$result = mysql_query($query)){
		//addError("Error running query: " . mysql_error());
	}
	// init User object using id, class knows what queries to run to populate self. throws InvalidUserError if not found
}

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
?>