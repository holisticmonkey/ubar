<?php
/**
 * Base class for testing Actions or functionality that requires a setup context.
 * Note that a simulation of the config, controller, and views are nested in the test directory.
 */
require_once("UbarBaseTestCase.php");
abstract class UbarBaseActionTestCase extends UbarBaseTestCase {

	private $actionMapper;

	public function __construct() {
		require_once (UBAR_ROOT . "/init.php");
		// override possible 'On' state for html errors since will be in console
		ini_set('html_errors', 'Off');
		$this->actionMapper = new ActionMapper(UBAR_ROOT . "/ubar.xml");
		parent::__construct();
	}

	// TODO: figure out how to consolidate some of this functionality with stuff going on in the dispatcher
	protected function createAction($actionString) {
		// get view path from config
		$actionDef = $this->actionMapper->getAction($actionString);
		$viewRealPath = BASE_VIEW_PATH . $actionDef->getViewLocation();
		$actionClassName = $actionDef->getClassName();
		$action = new $actionClassName($viewRealPath);
		// TODO: how do we want to surface action definiion so you can test the result wiring?
		// do we even need to test that since it's part of the config stuff... maybe because of chain testing
		//$resultDef = $actionDef->getResult($resultString);
		return $action;
	}
}
?>