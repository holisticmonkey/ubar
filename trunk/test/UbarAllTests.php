<?php
require_once("UbarTestSuite.php");
class UbarAllTests extends UbarTestSuite {

	public static function suite() {
		$suite = new UbarTestSuite('masterSuite');
		$suite->addTestSuite('StrTest');
		$suite->addTestSuite('PropertiesTest');
		return $suite;
	}
}
?>