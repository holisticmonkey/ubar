<?php
require ("UbarBaseTestCase.php");
class StrTest extends UbarBaseTestCase {

	private $notInitialized;

	function test_nullOrEmpty() {
		$null = null;
		$empty = "";
		$whiteSpace = "    ";
		$notEmpty = "    a    ";
		$this->assertTrue(Str :: nullOrEmpty($null));
		$this->assertTrue(Str :: nullOrEmpty($this->notInitialized));
		$this->assertTrue(Str :: nullOrEmpty($empty));
		$this->assertFalse(Str :: nullOrEmpty($whiteSpace, true));
		$this->assertFalse(Str :: nullOrEmpty($notEmpty, true));
	}

	function test_escapeRegex() {
		$this->assertIdentical(1, preg_match("/\\\\b/", "\\b", $matches));

		$this->assertIdentical("\\.", Str :: escapeRegex("."));
		// prove that the escape works as advertised

		$this->assertIdentical("\\\\b", Str :: escapeRegex("\\b"));
		$this->assertIdentical("\\\\bold\\.", Str :: escapeRegex("\\bold."));
	}

	function test_formatNumber() {
		// TODO: test passing in different locales
		$this->assertIdentical("1,000", Str :: formatNumber(1000));
		$this->assertIdentical("1.123", Str :: formatNumber(1.123));
		$this->assertIdentical("1.1233", Str :: formatNumber(1.123234));
	}

	function test_sanitizeHTML() {}

	function test_stripNewLines() {}
}
?>