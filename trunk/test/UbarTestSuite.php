<?php
class UbarTestSuite extends PHPUnit_Framework_TestSuite {

	public function __construct() {
		global $UBAR_GLOB;
		require_once (dirname(__FILE__) . "/../functions/misc.php");

		// allow the following folders of classes to be auto-loaded
		if (!isset($UBAR_GLOB['UBAR_ROOT'])) {
			$UBAR_GLOB['UBAR_ROOT'] =  realpath(dirname(__FILE__) . "/../") . DIRECTORY_SEPARATOR;
			getClassPaths($UBAR_GLOB['UBAR_ROOT'] . "constants", TRUE);
			getClassPaths($UBAR_GLOB['UBAR_ROOT'] . "exception", TRUE);
			getClassPaths($UBAR_GLOB['UBAR_ROOT'] . "core", TRUE);
			getClassPaths($UBAR_GLOB['UBAR_ROOT'] . "test", TRUE);
		}
		parent :: __construct();
	}
}
?>