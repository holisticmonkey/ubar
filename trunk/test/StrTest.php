<?php
require_once("UbarBaseTestCase.php");
class StrTest extends UbarBaseTestCase {

	private $notInitialized;

	public function __construct() {
		parent :: __construct("StrTest");
	}

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
		$this->assertEquals(1, preg_match("/\\\\b/", "\\b", $matches));

		$this->assertEquals("\\.", Str :: escapeRegex("."));
		// prove that the escape works as advertised

		$this->assertEquals("\\\\b", Str :: escapeRegex("\\b"));
		$this->assertEquals("\\\\bold\\.", Str :: escapeRegex("\\bold."));
	}

	function test_formatNumber() {
		// TODO: test passing in different locales
		$this->assertEquals("1,000", Str :: formatNumber(1000));
		$this->assertEquals("1.123", Str :: formatNumber(1.123));
		$this->assertEquals("1.1232", Str :: formatNumber(1.123234));
		$this->assertEquals("100.", Str :: formatNumber(100.));
		$this->assertEquals(".0000", Str :: formatNumber(.000001));
	}

	function test_sanitizeHTML() {}

	function test_stripNewLines() {}
}
?>