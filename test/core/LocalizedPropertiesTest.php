<?php
require_once (__DIR__ . "/../UbarBaseTestCase.php");
class LocalizedPropertiesTest extends UbarBaseTestCase {

	function testFileRetrieval() {
		// define required properties
		define("PROPERTIES_ROOT", "sample");
		define("LOCALE_DEFAULT", "english-usa,en_US.utf8");

		$goodPath = __DIR__ . "/../data/";
		$badPath = $goodPath . "bad/";

		// path override with valid
		$properties = new LocalizedProperties(null, $goodPath);

		// path ovoerride with invalid
		try {
			new LocalizedProperties(null, $badPath);
			$this->fail("The path to the props file is invalid and should have raised an exception");
		} catch (Exception $e) {
			// noop
		}

		// path override valid with locale
		$german = new LocalizedProperties("de", $goodPath);

		// define path and use default
		define("PROPERTIES_PATH", __DIR__ . "/../data/");
		new LocalizedProperties(null, null);

		// define path and locale and use defaults
		define("LOCALE", "de");
		// confirm used set locale
		$germanDefault = new LocalizedProperties(null, null);
		$this->assertEquals("this is overridden with the given locale", $germanDefault->get("sample.overridden"));

		// bad locale - currently defaults but does not complain
		$badLocale = new LocalizedProperties("af", null);
		$this->assertEquals("This is a default value that should be overridden by a more specific locale resource.", $badLocale->get("sample.overridden"));

	}

	/**
	 * @depends testFileRetrieval
	 */
	function testCommentsStripping() {
		$properties = new LocalizedProperties();
		$this->assertEquals("sample.commentedOut", $properties->get("sample.commentedOut"));

	}

	/**
	 * @depends testFileRetrieval
	 */
	function testMissingKey() {
		$properties = new LocalizedProperties();
		try {
			$this->assertEquals("sample.commentedOut", $properties->get("sample.commentedOut", array (), true));
			$this->fail("Since the property was commented out it should have raised an exception when requesting it.");
		} catch (Exception $e) {
			$this->assertEquals("The key \"sample.commentedOut\" was not found.", $e->getMessage());
		}
	}

	/**
	 * @depends testFileRetrieval
	 */
	function testDefault() {
		$properties = new LocalizedProperties();
		$this->assertEquals("This is a simple message string with no substitutions.", $properties->get("sample.simple"));
	}

	/**
	 * @depends testFileRetrieval
	 */
	function testArguments() {
		$properties = new LocalizedProperties();
		$this->assertEquals("1 dog should be pluralized and number formatted correctly. The provided argument was '1'.", $properties->get("sample.plural", array(1)));

		$this->assertEquals("1 dog should be pluralized and number formatted correctly. The provided argument was '1'.", $properties->get("sample.plural", 1));
	}

	/**
	 * @depends testFileRetrieval
	 */
	function testOverride() {
		$properties = new LocalizedProperties("de");
		$this->assertEquals("this is overridden with the given locale", $properties->get("sample.overridden"));
	}
}
?>