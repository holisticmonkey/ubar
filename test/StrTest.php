<?php
require("UbarBaseTestCase.php");
class StrTest extends UbarBaseTestCase {

	private $notInitialized;

	function test_nullOrEmpty() {
		$null = null;
		$empty = "";
		$whiteSpace = "    ";
		$notEmpty = "    a    ";
		$this->assertTrue(Str::nullOrEmpty($null));
		$this->assertTrue(Str::nullOrEmpty($this->notInitialized));
		$this->assertTrue(Str::nullOrEmpty($empty));
		$this->assertFalse(Str::nullOrEmpty($whiteSpace, true));
		$this->assertFalse(Str::nullOrEmpty($notEmpty, true));
	}
}
?>