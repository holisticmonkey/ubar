<?php
/**
 * Class definition for UbarBaseTestCase
 * @package core
 */

/**
 * Test case for ubar type tests.
 *
 * The class should be used for writing phpunit tests related to this
 * framework. It does the required setup for tests in the framework
 * context.
 *
 * These tests are written for PHPUnit version 3.4 under the assumption they
 * will be run using an external configuration with eclipse and/or the Ant
 * build script associated with this project.
 *
 * @link http://www.phpunit.de/manual/3.4/en/index.html Documentation
 * @link Setup: http://www.phpunit.de/wiki/Eclipse Eclipse
 * @link http://www.phpunit.de/manual/3.4/en/api.html#api.assert Valid Assertions
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	test
 *
 * @todo determine how to deal with bootstrapping between here and
 * UbarBaseActionTestCase
 */
ini_set("auto_detect_line_endings", true);
class UbarBaseTestCase extends PHPUnit_Framework_TestCase {

	/**
	 * Do test setup, simulates the bootstrapping that is required for
	 * framework functionality.
	 *
	 * Dev mode is overridden to true and locale set to english.
	 *
	 * @param string $name Pass through for argument in PHPUnit_Framework_TestCase
	 * @param array $data Pass through for argument in PHPUnit_Framework_TestCase
	 * @param string $dataName Pass through for argument in PHPUnit_Framework_TestCase
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '') {
		global $UBAR_GLOB;

		$UBAR_GLOB['DEV_MODE'] = true;

		require_once (dirname(__FILE__) . "/../functions/misc.php");

		// allow the following folders of classes to be auto-loaded
		if (!isset($UBAR_GLOB['UBAR_ROOT'])) {
			$UBAR_GLOB['UBAR_ROOT'] = realpath(dirname(__FILE__) . "/../") . DIRECTORY_SEPARATOR;
			getClassPaths($UBAR_GLOB['UBAR_ROOT'] . "constants", TRUE);
			getClassPaths($UBAR_GLOB['UBAR_ROOT'] . "exception", TRUE);
			getClassPaths($UBAR_GLOB['UBAR_ROOT'] . "core", TRUE);
		}

		// defualt to us english, if you need to change locally, might need to reset when done with test
		setlocale(LC_ALL, 'english-usa', 'en_US.utf8');
		parent :: __construct($name, $data, $dataName);
	}

}
?>