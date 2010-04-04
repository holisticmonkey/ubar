<?php
require_once(__DIR__ . "/../UbarBaseTestCase.php");
class TemplateDefTest extends UbarBaseTestCase {

	private $fullTemplateString = "
<template name=\"default\" path=\"templates.default\">
	<param name=\"title\" value=\"my title\" />
	<param name=\"titleKey\" value=\"title.key\" />
	<param name=\"section\" value=\"portfolio\" />
	<param name=\"subSection\" value=\"programming\" />
	<param name=\"displaySidebar\" value=\"true\" />
</template>
";

	private $minimalTemplateString = "<template name=\"minimal\" path=\"templates.minimal\" />";

	private $noPathTemplateString = "<template name=\"minimal\" />";

	function testFull() {
		$templateXML = simplexml_load_string($this->fullTemplateString, "SimpleXMLElement");
		$template = new TemplateDef($templateXML);

		$this->assertEquals("templates/default.php", $template->getPath());
		$this->assertEquals("my title", $template->getTitle());
		$this->assertEquals("title.key", $template->getTitleKey());
		$this->assertEquals("portfolio", $template->getSection());
		$this->assertEquals("programming", $template->getSubSection());
		$this->assertEquals("true", $template->getParam("displaySidebar"));
	}

	function testMinimal() {
		$templateXML = simplexml_load_string($this->minimalTemplateString, "SimpleXMLElement");
		$template = new TemplateDef($templateXML);

		$this->assertEquals("templates/minimal.php", $template->getPath());
		$this->assertEquals("", $template->getTitle());
		$this->assertEquals("", $template->getTitleKey());
		$this->assertEquals("", $template->getSection());
		$this->assertEquals("", $template->getSubSection());
		$this->assertEquals("", $template->getParam("displaySidebar"));
	}

	function testSetters() {
		$templateXML = simplexml_load_string($this->fullTemplateString, "SimpleXMLElement");
		$template = new TemplateDef($templateXML);

		// test param merger
		$this->assertEquals(5, count($template->getParams()));

		$template->addParam("newKey", "newValue");

		$this->assertEquals(6, count($template->getParams()));
		$this->assertEquals("newValue", $template->getParam("newKey"));

		// test original path
		$this->assertEquals("templates/default.php", $template->getPath());
		$template->setPath("templates/minimal.php");

		// should not change due to already being set
		$this->assertEquals("templates/default.php", $template->getPath());

		// test override when nothing orginally set
		$templateXML = simplexml_load_string($this->noPathTemplateString, "SimpleXMLElement");
		$template = new TemplateDef($templateXML);
		$this->assertEquals("", $template->getPath());
		$template->setPath("templates/minimal.php");
		$this->assertEquals("templates/minimal.php", $template->getPath());
	}
}
?>