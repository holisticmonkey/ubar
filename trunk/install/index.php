<?php
/**
 * Ubar Framework Setup Script
 *
 * This script sets up the Ubar Framework each request. Initialization,
 * flow control and dispatching is handled on separate scripts launched from
 * here. All requests are funneled through this mod_rewrite and .htaccess.
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
*/

############## USER EDIT ##############
/**
 * Uncomment the items below and provide relative or absolute paths to the
 * requested files or folders. Note that if left blank, it will assume the
 * directory structure described in the install notes.
 */

// location of the ubar library folder
$ubarRoot = "../WEB-INF/lib/ubar";
//TODO: allow ubarRoot to be commented out and still work
#######################################

global $UBAR_GLOB;
if(!isset($UBAR_GLOB)) {
	$UBAR_GLOB = array();
}

## Check for PHP Version Information
define("MIN_PHP_VERSION", "5.1");
if (version_compare(phpversion(), MIN_PHP_VERSION, "<")) {
	throw new Exception("PHP version " . MIN_PHP_VERSION . " or above required. Your current version is " . phpversion() . ".");
}

## Start by displaying errors and let later be overriden if it makes it that far
ini_set('display_errors', true);

## Check that the .htaccess file that enables the action mappings exists
if (!file_exists('.htaccess')) {
	throw new Exception("Could not find .htaccess file. This file is required to enable action mapping in the Ubar Framwork.");
}

## define web root
$UBAR_GLOB['WEB_ROOT'] = dirname(__FILE__) . "/";

## Set Ubar up
// test that config file exists
// test that action mappings are found
$initScript = $ubarRoot . "/init.php";
if (!file_exists($initScript)) {
	throw new Exception("The Ubar Framwork intialization class was not found. Please verify that you have installed the framework correctly.");
}
// load init script
require_once ($initScript);

// get action definitions
// TODO: get dtd hosted somewhere and allow ubar.xml to be moved
$dispatcher = new Dispatcher($UBAR_GLOB['UBAR_ROOT'] . "/ubar.xml");

// not that framework is setup, let the controller dispatch the request
$dispatcher->dispatch();
?>