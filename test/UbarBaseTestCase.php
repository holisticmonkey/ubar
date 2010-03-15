<?
/**
 * These tests are written for PHPUnit version 3.4 under the assumption they will be run
 * using an external configuration with eclipse and/or the Ant build script associated
 * with this project.
 *
 * Documentation: http://www.phpunit.de/manual/3.4/en/index.html
 * Eclipse Setup: http://www.phpunit.de/wiki/Eclipse
 */
// TODO: figure out how to deal with duplicate bootstrapping between here and UbarBaseActionTestCase
class UbarBaseTestCase extends PHPUnit_Framework_TestCase {

	public function __construct($name = NULL, array $data = array(), $dataName = '') {
		require_once (dirname(__FILE__) . "/../functions/misc.php");

		// allow the following folders of classes to be auto-loaded
		if (!defined('UBAR_ROOT')) {
			define('UBAR_ROOT', realpath(dirname(__FILE__) . "/../") . DIRECTORY_SEPARATOR);
			getClassPaths(UBAR_ROOT . "constants", TRUE);
			getClassPaths(UBAR_ROOT . "exception", TRUE);
			getClassPaths(UBAR_ROOT . "core", TRUE);
		}

		// defualt to us english, if you need to change locally, might need to reset when done with test
		setlocale(LC_ALL, 'english-usa', 'en_US.utf8');
		parent :: __construct($name, $data, $dataName);
	}

	// NOTE: just as an example
	protected function assert10($actual, $message = '%s') {
		return $this->assert(new EqualExpectation($actual), 10, $message);
	}

}
?>