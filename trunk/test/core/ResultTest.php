<?php
require_once(__DIR__ . "/../UbarBaseTestCase.php");
class ResultTest extends UbarBaseTestCase {

	private $urlString = '<result name="USER_ERROR" type="url">Blog.action</result>';

	private $pageString = '<result name="SUCCESS" type="page" template="minimal">pages.test</result>';

	private $fileString = '<result>/test.php</result>';

	function testFull() {
		$XML = simplexml_load_string($this->urlString, "SimpleXMLElement");
		$result = new Result($XML);

		$this->assertEquals("USER_ERROR", $result->getName());
		$this->assertEquals("Blog.action", $result->getTarget());
		$this->assertEquals("", $result->getTemplateName());
		$this->assertEquals("url", $result->getType());
		$this->assertEquals("", $result->getViewLocation());
	}

	function testDefaults() {
		$XML = simplexml_load_string($this->fileString, "SimpleXMLElement");
		$result = new Result($XML);

		$this->assertEquals("SUCCESS", $result->getName());
		$this->assertEquals("/test.php", $result->getTarget());
		$this->assertEquals("", $result->getTemplateName());
		$this->assertEquals("file", $result->getType());
		$this->assertEquals("", $result->getViewLocation());
	}

	function testPage() {
		$XML = simplexml_load_string($this->pageString, "SimpleXMLElement");
		$result = new Result($XML);

		$this->assertEquals("SUCCESS", $result->getName());
		$this->assertEquals("pages.test", $result->getTarget());
		$this->assertEquals("minimal", $result->getTemplateName());
		$this->assertEquals("page", $result->getType());
		$this->assertEquals("pages/test.php", $result->getViewLocation());
	}

	function testMakeResult() {
		$result = Result::makeResult("USER_ERROR", "url", "Blog.action");

		$this->assertEquals("USER_ERROR", $result->getName());
		$this->assertEquals("Blog.action", $result->getTarget());
		$this->assertEquals("", $result->getTemplateName());
		$this->assertEquals("url", $result->getType());
		$this->assertEquals("", $result->getViewLocation());
	}
}
?>