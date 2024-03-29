<?php
require_once(__DIR__ . "/../UbarBaseTestCase.php");
class StrTest extends UbarBaseTestCase {

	private $notInitialized;

	function testCtor() {
		new Str();
	}

	function testNullOrEmpty() {
		$null = null;
		$empty = "";
		$whiteSpace = "    ";
		$notEmpty = "    a    ";
		$zero = array();
		$result = new Result("");

		$this->assertTrue(Str :: nullOrEmpty($null));
		$this->assertTrue(Str :: nullOrEmpty($this->notInitialized));
		$this->assertTrue(Str :: nullOrEmpty($empty));
		$this->assertTrue(Str :: nullOrEmpty($zero));

		$this->assertFalse(Str :: nullOrEmpty($whiteSpace, true));
		$this->assertFalse(Str :: nullOrEmpty($notEmpty, true));
		$this->assertFalse(Str :: nullOrEmpty($result, true));
	}

	function testEscapeRegex() {
		$this->assertEquals(1, preg_match("/\\\\b/", "\\b", $matches));

		$this->assertEquals("\\.", Str :: escapeRegex("."));
		$this->assertEquals("\\\\b", Str :: escapeRegex("\\b"));
		$this->assertEquals("\\\\bold\\.", Str :: escapeRegex("\\bold."));
	}

	function testFormatNumber() {
		// TODO: test passing in different locales
		$this->assertEquals("1,000", Str :: formatNumber(1000));
		$this->assertEquals("1.123", Str :: formatNumber(1.123));
		$this->assertEquals("1.1232", Str :: formatNumber(1.123234));
		$this->assertEquals("100.", Str :: formatNumber(100.));
		$this->assertEquals(".0000", Str :: formatNumber(.000001));
		$this->assertEquals("1.4", Str :: formatNumber(1.4));
	}

	function testSanitizeHTML() {
		// strip properties from anchor tags
		$this->assertEquals("<a href=\"test.html\" target=\"_blank\">asdf</a>", Str :: sanitizeHTML("<a href=\"test.html\" style=\"font-size: 100px;\">asdf</a>"));
		// strip tags that aren't allowed
		$this->assertEquals("asdf", Str :: sanitizeHTML("<pre>asdf</pre>"));
		// strip javascript type anchor tags
		$this->assertEquals("asdf", Str :: sanitizeHTML("<a href=\"javascript:installVirus()\">asdf</a>"));
		// convert newlines to breaks
		$this->assertEquals("line1<br />\n<br />\nline2", Str :: sanitizeHTML("line1\n\nline2"));
		// convert multiple tags
		$this->assertEquals("<b><u>asdf</u></b>", Str :: sanitizeHTML("<b style=\"font-weight: normal\"><pre><u>asdf</u></pre></b>"));
	}

	function testStripNewLines() {
		$cr = "I have CR\r";
		$lf = "I have LF\n";
		$crlf = "I have CRLF\r\n";

		$this->assertEquals("I have CR", Str::stripNewLines($cr));
		$this->assertEquals("I have LF", Str::stripNewLines($lf));
		$this->assertEquals("I have CRLF", Str::stripNewLines($crlf));
	}
}
?>