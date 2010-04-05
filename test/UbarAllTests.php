<?php
require_once("UbarTestSuite.php");
class UbarAllTests extends UbarTestSuite {

	public static function suite() {
		$suite = new UbarTestSuite('masterSuite');

		// core
		$suite->addTestSuite('FileUtilsTest');
		$suite->addTestSuite('LocalizedPropertiesTest');
		$suite->addTestSuite('MessageFormatTest');
		$suite->addTestSuite('MessageTest');
		$suite->addTestSuite('ObjectUtilsTest');
		$suite->addTestSuite('PropertiesTest');
		$suite->addTestSuite('ResultTest');
		$suite->addTestSuite('StrTest');
		$suite->addTestSuite('TemplateDefTest');

		// sample - currently tests against src, not dist code
		// TODO: modify tests to require building sample app with dist version
		// of lib. Possibly just put tests into the sample?
		$suite->addTestSuite('BasicTest');

		return $suite;
	}
}
?>