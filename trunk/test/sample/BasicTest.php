<?php
// TOOD: Suggest that users externalize the two require statements below
require_once(__DIR__ . "/UbarSampleActionTestCase.php");

class BasicTest extends UbarSampleActionTestCase {

	public function testDefault() {
		// create the action
		$this->initAction("Home");

		// verify basic properties of action
		$this->assertTitleEquals("");
		$this->assertSectionEquals("");
		$this->assertSubsectionEquals("");
		$this->assertActionClassEquals("AlwaysSuccess");
		$this->assertActionNameEquals("Home");
		$this->assertViewEquals("pages/home.php");

		// generate the results
		$this->runAction();

		// validate that result string was expected
		$this->assertResultEquals(GlobalConstants::SUCCESS);

		// validate that result def is as expected
		$this->assertResultTypeEquals(GlobalConstants::PAGE_TYPE);

		// run rendering process to make sure no exceptions occurred
		$this->assertRenderValid();
	}

	// test that this is the default action
	public function testDefaultAction() {
		// pass in a null action name so that it uses the default
		$this->initAction(null);

		// confirm that the default is Home
		$this->assertActionNameEquals("Home");
	}

	// test that putting in unknown input gives a warning - this is not specific to Home
	public function testUnknownInput() {
		// create the action
		$this->initAction("Home");

		// add unknown input
		$this->setUserInput("foo", "bar");

		// generate the results
		$this->runAction();

		// check that there is a complaint about invalid input
		$this->assertHasWarningSimple("Method ,\"setFoo()\", was not found in the action");

		// check that all messages were checked
		$this->assertAllMessagesTested();

		// validate that result string was expected
		$this->assertResultEquals(GlobalConstants::SUCCESS);
	}

	public function testNoView() {

	}
}
?>