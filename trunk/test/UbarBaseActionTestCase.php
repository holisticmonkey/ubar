<?php
/**
 * Base class for testing Actions or functionality that requires a setup context.
 * Note that a simulation of the config, controller, and views are nested in the test directory.
 */
require_once("UbarBaseTestCase.php");
abstract class UbarBaseActionTestCase extends UbarBaseTestCase {

	protected $dispatcher;

	protected $actionDef;

	protected $action;

	protected $resultString;

	protected $resultDef;

	public function __construct() {
		// construct UbarBaseTestCase first since it does most of the setup
		parent::__construct();

		// do the setup required for action context material
		require_once (UBAR_ROOT . "/init.php");

		// override possible 'On' state for html errors since will be in console
		ini_set('html_errors', 'Off');

		// create an instance of the dispatcher
		$this->dispatcher = new Dispatcher(UBAR_ROOT . "/ubar.xml");
	}

	// users may want to set more $_SERVER properties, see
	// http://php.net/manual/en/reserved.variables.server.php
	protected function initAction($actionString) {
		// set up commonly referenced properties by views and templates
		$_SESSION = array();
		// TODO: see http://php.net/manual/en/reserved.variables.server.php for
		// more properties to set
		$_SERVER['REQUEST_URI'] = "http://localhost/" . $actionString . ".php";

		// initiate action
		$this->action = $this->dispatcher->getAction($actionString);
		$this->actionDef = $this->action->getActionDef();
	}

	// note that need to use setUserInput first
	protected function runAction() {
		// execute action, note that body may not execute if user conditions not met
		$this->resultString = $this->dispatcher->generateResult();

		// get result for action and input
		$this->resultDef = $this->dispatcher->getResultDef();
	}

	// note that don't always need this, mostly for confirmation that rendering process didn't produce exceptions
	protected function assertRenderValid() {
		$this->dispatcher->showResult();
	}

	protected function setUserInput($key, $val) {
		$this->action->set($key, $val);
	}

	protected function tearDown() {
		unset($this->actionDef);
		unset($this->action);
		unset($this->resultString);
		unset($this->resultDef);
	}

	// TODO: add helper methods for asserting has error, warning, info
	// '' title, path, etc
}
?>