<?php
require_once (__DIR__ . "/../UbarBaseTestCase.php");
class FileUtilsTest extends UbarBaseTestCase {

	private $notInitialized;

	function testDotToPath() {
		$this->assertEquals("this/is/a/path/to/a/file.php", FileUtils::dotToPath("this.is.a.path.to.a.file"));
	}

	function testClassFromFile() {
		$this->assertEquals("Bang", FileUtils::classFromFile("/this/is/a/path/to/a/class/named/Bang.php"));
	}
}
?>