<?php
class UbarTestSuite extends PHPUnit_Framework_TestSuite {

	public function __construct() {
		require_once (dirname(__FILE__) . "/../functions/misc.php");

		// allow the following folders of classes to be auto-loaded
		if (!defined('UBAR_ROOT')) {
			define('UBAR_ROOT', realpath(dirname(__FILE__) . "/../") . DIRECTORY_SEPARATOR);
			getClassPaths(UBAR_ROOT . "constants", TRUE);
			getClassPaths(UBAR_ROOT . "exception", TRUE);
			getClassPaths(UBAR_ROOT . "core", TRUE);
			getClassPaths(UBAR_ROOT . "test", TRUE);
		}
		parent :: __construct();
	}
}
?>