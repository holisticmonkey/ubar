<?

/**
* Ubar Framework Setup Script
*
* This script sets up the Ubar Framework each requeest. Initialization,
* flow control and dispatching is handled on separate scripts launched from
* here. All requests are funneled through this  mod_rewrite and .htaccess.
*
* @author     Josh Ganderson <jag@josh.com>
* @link       http://www.holisticmonkey.com
* @license    http://opensource.org/licenses/bsd-license.php BSD License
* @copyright  Copyright 2007 Joshua Ganderson
* @since      2007
* @version    $Rev$
* @package    Core
*/

## Check for PHP Version Information
define("MIN_PHP_VERSION", "5.1");
if (version_compare(phpversion(), MIN_PHP_VERSION, "<")) {
	die("PHP version " . MIN_PHP_VERSION . " or above required. Your current version is " . phpversion() . ".");
}

## Check that the .htaccess file that enables the action mappings exists
if (!file_exists('.htaccess')) {
	die("Could not find .htaccess file. This file is required to enable action mapping in the Ubar Framwork.");
}

## define web root
define('WEB_ROOT', dirname(__FILE__) . "/");

## Load config file which must be contained in this directory and try to initialize framework
define("SETUP_FILE", "setup.conf");
// verify that file exists
if (!file_exists(SETUP_FILE)) {
	die("Could not find " . SETUP_FILE . " config file. This file is required for the Ubar Framwork.");
}
// get contents of file
$setup = file_get_contents(SETUP_FILE);
// define regex for getting property - done manually since have not yet loaded any property reader classes
define("PROPS_PATH_REGEX", '/^\s*ubar.path\s*=\s*([^\n\r]*)\s*$/m');
// run regex on file contents
preg_match(PROPS_PATH_REGEX, $setup, $matches);
// property not found in setup file, die
if (!isset ($matches[1])) {
	die("Could not find property \"ubar.path\" in " . SETUP_FILE . " config file. This location is required for the Ubar Framwork.");
	// property found, try to retrieve file
} else {
	// try to load the init script
	$initScript = $matches[1] . "init.php";
	if (!file_exists($initScript)) {
		die("The Ubar Framwork intialization class was not found. Please verify that you have installed the framework correctly.");
	} else {
		// load init script
		require_once ($initScript);
	}
}



// pass the action to the controller
// controller needs config file for class, method, and outcome mapping
// figure out how the controller sets up access to things that the view can consume
// in the case that there's no controller... see if you can just show the view. might be useful if the page has no work
// set up nice failures for non existent action, bad permissions, not authorized
?>