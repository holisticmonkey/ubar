<?php
/**
 * Class definition for UbarTestSuite
 * @package core
 */

/**
 * Test suite for ubar type tests.
 *
 * The class should be used for assembling phpunit tests related to this
 * framework. It does the required setup for tests in the framework
 * context.
 *
 * Example:
 * <code>
 * class UbarAllTests extends UbarTestSuite {
 *
 *	public static function suite() {
 *		$suite = new UbarTestSuite('mySuiteName');
 *
 *		$suite->addTestSuite('MyTestClass');
 *		$suite->addTestSuite('AnotherTestClass');
 *
 *		return $suite;
 *	}
 *}
 * </code>
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	test
 */
class UbarTestSuite extends PHPUnit_Framework_TestSuite {

	/**
	 * Do required initialization for tests.
	 */
	public function __construct() {
		global $UBAR_GLOB;
		require_once (dirname(__FILE__) . "/../functions/misc.php");

		// allow the following folders of classes to be auto-loaded
		if (!isset($UBAR_GLOB['UBAR_ROOT'])) {
			$UBAR_GLOB['UBAR_ROOT'] =  realpath(dirname(__FILE__) . "/../") . DIRECTORY_SEPARATOR;
			getClassPaths($UBAR_GLOB['UBAR_ROOT'] . "constants", TRUE);
			getClassPaths($UBAR_GLOB['UBAR_ROOT'] . "exception", TRUE);
			getClassPaths($UBAR_GLOB['UBAR_ROOT'] . "core", TRUE);
			getClassPaths($UBAR_GLOB['UBAR_ROOT'] . "test", TRUE);
		}
		parent :: __construct();
	}
}
?>