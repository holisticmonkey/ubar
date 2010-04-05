<?php
require_once (__DIR__ . "/../UbarBaseTestCase.php");
class LocalizedPropertiesTest extends UbarBaseTestCase {

	private function init() {
		global $UBAR_GLOB;

		$UBAR_GLOB['PROPERTIES_ROOT'] = "sample";
		$UBAR_GLOB['LOCALE_DEFAULT'] = "english-usa,en_US.utf8";
		$UBAR_GLOB['PROPERTIES_PATH'] = __DIR__ . "/../data/";
		$UBAR_GLOB['LOCALE'] = 'de';
	}

	function testFileRetrieval() {
		global $UBAR_GLOB;

		// define required properties
		$UBAR_GLOB['PROPERTIES_ROOT'] = "sample";
		$UBAR_GLOB['LOCALE_DEFAULT'] = "english-usa,en_US.utf8";

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
		$UBAR_GLOB['PROPERTIES_PATH'] = __DIR__ . "/../data/";
		new LocalizedProperties(null, null);

		// define path and locale and use defaults
		$UBAR_GLOB['LOCALE'] = 'de';
		// confirm used set locale
		$germanDefault = new LocalizedProperties(null, null);
		$this->assertEquals("this is overridden with the given locale", $germanDefault->get("sample.overridden"));

		// bad locale - currently defaults but does not complain
		$badLocale = new LocalizedProperties("af", null);
		$this->assertEquals("This is a default value that should be overridden by a more specific locale resource.", $badLocale->get("sample.overridden"));

	}

	function testCommentsStripping() {
		$this->init();

		$properties = new LocalizedProperties();
		$this->assertEquals("sample.commentedOut", $properties->get("sample.commentedOut"));

	}

	function testMissingKey() {
		$this->init();

		$properties = new LocalizedProperties();
		try {
			$this->assertEquals("sample.commentedOut", $properties->get("sample.commentedOut", array (), true));
			$this->fail("Since the property was commented out it should have raised an exception when requesting it.");
		} catch (Exception $e) {
			$this->assertEquals("The key \"sample.commentedOut\" was not found.", $e->getMessage());
		}
	}

	function testDefault() {
		$this->init();

		$properties = new LocalizedProperties();
		$this->assertEquals("This is a simple message string with no substitutions.", $properties->get("sample.simple"));
	}

	function testArguments() {
		$this->init();

		$properties = new LocalizedProperties();
		$this->assertEquals("1 dog should be pluralized and number formatted correctly. The provided argument was '1'.", $properties->get("sample.plural", array(1)));

		$this->assertEquals("1 dog should be pluralized and number formatted correctly. The provided argument was '1'.", $properties->get("sample.plural", 1));
	}

	function testOverride() {
		$this->init();

		$properties = new LocalizedProperties("de");
		$this->assertEquals("this is overridden with the given locale", $properties->get("sample.overridden"));
	}
}
?>