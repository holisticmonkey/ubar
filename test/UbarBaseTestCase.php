<?
/**
 * Assertions built into SimpleTest's UnitTestCase:
 *
 * assertTrue($result, $message = false) - true or a true value
 * assertFalse($result, $message = '%s') - false or a false value such as 0
 * assertNull($value, $message = '%s') - null
 * assertNotNull($value, $message = '%s') - not null
 * assertIsA($object, $type, $message = '%s') - an object of a given type
 * assertNotA($object, $type, $message = '%s') - not an object of a given type
 * assertEqual($first, $second, $message = '%s') - equal value but not necessarily type 0 and false are equal here
 * assertNotEqual($first, $second, $message = '%s') - not equal value
 * assertWithinMargin($first, $second, $margin, $message = '%s') - inside a range
 * assertOutsideMargin($first, $second, $margin, $message = '%s') - outside a range
 * assertIdentical($first, $second, $message = '%s') - same object type and value
 * assertNotIdentical($first, $second, $message = '%s') - not same object type and value
 * assertReference(&$first, &$second, $message = '%s') - must refer to same object
 * assertClone(&$first, &$second, $message = '%s') - different objects but same type and value
 * assertPattern($pattern, $subject, $message = '%s') - regex test on a string
 * assertNoPattern($pattern, $subject, $message = '%s') - regex fail test on a string
 *
 * expectError($expected = false, $message = '%s') - plan on error occurring
 * expectException($expected = false, $message = '%s') - plan on exception being thrown
 */

// TODO: figure out how to deal with duplicate bootstrapping between here and UbarBaseActionTestCase
class UbarBaseTestCase extends UnitTestCase {

	public function __construct() {
		require_once (dirname(__FILE__) . "/../functions/misc.php");

		// allow the following folders of classes to be auto-loaded
		// TODO: determine why this may already be defined, looks like simpletest artifact
		if (!defined('UBAR_ROOT')) {
			define('UBAR_ROOT', realpath(dirname(__FILE__) . "/../") . DIRECTORY_SEPARATOR);
			getClassPaths(UBAR_ROOT . "constants", TRUE);
			getClassPaths(UBAR_ROOT . "exception", TRUE);
			getClassPaths(UBAR_ROOT . "core", TRUE);
		}

		// defualt to us english, if you need to change locally, might need to reset when done with test
		setlocale(LC_ALL, 'english-usa', 'en_US.utf8');
		parent :: __construct();
	}

	// NOTE: just as an example
	protected function assert10($actual, $message = '%s') {
		return $this->assert(new EqualExpectation($actual), 10, $message);
	}

}
?>