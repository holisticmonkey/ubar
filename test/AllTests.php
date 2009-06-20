<?php
require ("UbarTestSuite.php");
class AllTests extends UbarTestSuite {

	// TODO: make AllTests just include other test suites (tools, actions, etc)
	function AllTests() {
		$this->TestSuite('All Tests');
		$this->addFile(UBAR_ROOT . "/test/StrTest.php");
		$this->addFile(UBAR_ROOT . "/test/PropertiesTest.php");
		$this->addFile(UBAR_ROOT . "/test/LocalizedPropertiesTest.php");
		$this->addFile(UBAR_ROOT . "/test/OGNLTest.php");
		$this->addFile(UBAR_ROOT . "/test/DBTest.php");
	}
}
?>
