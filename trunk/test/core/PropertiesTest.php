<?php
require_once(__DIR__ . "/../UbarBaseTestCase.php");
class PropertiesTest extends UbarBaseTestCase {

	function test_construct() {
		// need to use DIRECTORY_SEPARATOR since real path is part of messaging
		$bad = UBAR_ROOT . "test" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "sample_missing.properties";
		$good = UBAR_ROOT . "test/data/sample.properties";

		$pathToPropertiesClass = UBAR_ROOT . "core" . DIRECTORY_SEPARATOR . "Properties.php";

		$expectedMessage = "Path \"$bad\" to properties file does not exist. File: " . $pathToPropertiesClass . " on line: 66";

		// test that it fails if you give a bad file
		try {
			new Properties($bad);
			$this->fail("Expected exception trying to get nonexistant file.");
		} catch (Exception $e) {
			$this->assertEquals($expectedMessage, $e->getMessage());
		}

		// test that it fails silently and returns default if fail silent on and default provided
		$badProps = new Properties($bad, true);
		$this->assertEquals("", $badProps->get("foo", ""));

		// test that all is well if you give it a real file
		$goodProps = new Properties($good);
		$goodProps->get("sample.simple");
	}

	function test_get() {
		$path = UBAR_ROOT . "test" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "sample.properties";
		$pathToPropertiesClass = UBAR_ROOT . "core" . DIRECTORY_SEPARATOR . "Properties.php";
		$props = new Properties($path);

		// test normal
		$this->assertEquals("This is a simple message string with no substitutions.", $props->get("sample.simple"));

		// test normal with default
		$this->assertEquals("This is a simple message string with no substitutions.", $props->get("sample.simple", "foo"));

		// test missing
		try {
			$props->get("sample.missing");
			$this->fail("Expected exception trying to get nonexistant property.");
		} catch (Exception $e) {
			$expectedMessage = "No property was found with the key \"sample.missing\" in the file \"$path\".";
			$this->assertEquals($expectedMessage, $e->getMessage());
		}

		// test missing with default
		$this->assertEquals("foo", $props->get("sample.missing", "foo"));

		// test preserve extra space
		$expected = "   hi   ";
		$this->assertEquals("   hi   ", $props->get("sample.whitespace", null, true));
		$this->assertEquals("hi", $props->get("sample.whitespace"));

		// test getting something commented out
		try {
			$props->get("sample.commentedOut");
			$this->fail("Expected exception trying to get commented out property.");
		} catch (Exception $e) {
			$expectedMessage = "No property was found with the key \"sample.commentedOut\" in the file \"$path\".";
			$this->assertEquals($expectedMessage, $e->getMessage());
		}

	}

	function test_getBool() {
		$path = UBAR_ROOT . "test" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "sample.properties";
		$pathToPropertiesClass = UBAR_ROOT . "core" . DIRECTORY_SEPARATOR . "Properties.php";
		$props = new Properties($path);

		// test valid
		$this->assertEquals(TRUE, $props->getBool("sample.boolean"));

		// test unable to convert (other errors covered in test_get()
		try {
			$props->getBool("sample.badboolean");
			$this->fail("Expected exception trying to get property you can't convert to bool.");
		} catch (Exception $e) {
			$value = $props->get("sample.badboolean");
			$expectedMessage = "The property found, " . $value . ", with the key \"sample.badboolean\" could not be converted to a boolean value in the file \"$path\".";
			$this->assertEquals($expectedMessage, $e->getMessage());
		}
	}
}
?>