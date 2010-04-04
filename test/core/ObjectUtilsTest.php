<?php
require_once (__DIR__ . "/../UbarBaseTestCase.php");
class ObjectUtilsTest extends UbarBaseTestCase {

	private $notInitialized;

	function testToBoolean() {

		// valid true values
		$this->assertTrue(ObjectUtils :: toBoolean(true));
		$this->assertTrue(ObjectUtils :: toBoolean(TRUE));
		$this->assertTrue(ObjectUtils :: toBoolean("true"));
		$this->assertTrue(ObjectUtils :: toBoolean("1"));
		$this->assertTrue(ObjectUtils :: toBoolean(1));

		// valid false values
		$this->assertFalse(ObjectUtils :: toBoolean(false));
		$this->assertFalse(ObjectUtils :: toBoolean(FALSE));
		$this->assertFalse(ObjectUtils :: toBoolean("false"));
		$this->assertFalse(ObjectUtils :: toBoolean("0"));
		$this->assertFalse(ObjectUtils :: toBoolean(0));
	}

	function testToBooleanException() {
		// invalid values
		$invalid = array (
			" true ",
			2
		);

		foreach ($invalid as $val) {
			try {
				ObjectUtils :: toBoolean(" true ");
				$this->fail("The value, \"$val\", should not have been able to be cast to a boolean");
			} catch (Exception $e) {
				// noop
			}
		}
	}

	function testIsNull() {
		$null = null;

		// valid true values
		$this->assertTrue(ObjectUtils :: isNull(null));
		$this->assertTrue(ObjectUtils :: isNull(NULL));
		$this->assertTrue(ObjectUtils :: isNull($this->notInitialized));
		$this->assertTrue(ObjectUtils :: isNull($null));

		// valid false values
		$this->assertFalse(ObjectUtils :: isNull(false));
		$this->assertFalse(ObjectUtils :: isNull(0));
	}
}
?>