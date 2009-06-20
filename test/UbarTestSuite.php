<?
class UbarTestSuite extends TestSuite {
	public function __construct() {
		require_once (dirname(__FILE__) . "/../functions/misc.php");

		// allow the following folders of classes to be auto-loaded
		define('UBAR_ROOT', dirname(__FILE__) . "/../");
		getClassPaths(UBAR_ROOT . "constants", TRUE);
		getClassPaths(UBAR_ROOT . "exception", TRUE);
		getClassPaths(UBAR_ROOT . "core", TRUE);
		parent::__construct();
	}
}
?>