<?php
/**
 * Base class for testing Actions or functionality that requires a setup context.
 * Note that a simulation of the config, controller, and views are nested in the test directory.
 */
/**
 * Class definition for UbarBaseActionTestCase
 * @package core
 */

/**
 * Test case for ubar action tests.
 *
 * The class should be used for writing phpunit tests for ubar action classes.
 * It does the required setup to run an action in a test environment.
 *
 * These tests are written for PHPUnit version 3.4 under the assumption they
 * will be run using an external configuration with eclipse and/or the Ant
 * build script associated with this project.
 *
 * @link http://www.phpunit.de/manual/3.4/en/index.html Documentation
 * @link Setup: http://www.phpunit.de/wiki/Eclipse Eclipse
 * @link http://www.phpunit.de/manual/3.4/en/api.html#api.assert Valid Assertions
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 * @subpackage	test
 */
require_once("UbarBaseTestCase.php");
abstract class UbarBaseActionTestCase extends UbarBaseTestCase {

	/**
	 * @var class Instance of dispatcher
	 */
	protected $dispatcher;

	/**
	 * @var class Instance of action definition
	 */
	protected $actionDef;

	/**
	 * @var class Instance of action
	 */
	protected $action;

	/**
	 * @var string Result string returned by action
	 */
	protected $resultString;

	/**
	 * @var class Result definition for given result string and action def
	 */
	protected $resultDef;

	/**
	 * @var array A copy of errors, warnings, notices so that it can be
	 * confirmed that all messages were tested for without atually removing
	 * them from the action instance.
	 */
	protected $messagesCopy = array();

	/**
	 * Do the required setup to run an action. Allows for an alternate
	 * location for action config file.
	 *
	 * @param string Path to overriding action configuration
	 */
	public function __construct($xmlOverride = null) {
		global $UBAR_GLOB;

		// construct UbarBaseTestCase first since it does most of the setup
		parent::__construct();

		// do the setup required for action context material
		require_once ($UBAR_GLOB['UBAR_ROOT'] . "/init.php");

		// override possible 'On' state for html errors since will be in console
		ini_set('html_errors', 'Off');

		// create an instance of the dispatcher, may be overridden for use of
		// a test action config file
		if(is_null($xmlOverride)) {
			$this->dispatcher = new Dispatcher($UBAR_GLOB['UBAR_ROOT'] . "/ubar.xml");
		} else {
			$this->dispatcher = new Dispatcher($xmlOverride);
		}
	}

	/**
	 * Initialize the action that matches the provided action name.
	 * Note that this sets the $_SERVER param for REQUEST_URI to simulate
	 * the appropriate request being made. Other properties may need to be
	 * set in the future.
	 *
	 * @link http://php.net/manual/en/reserved.variables.server.php $_SERVER Properties
	 *
	 * @param string $actionString The name of the action to initialize.
	 */
	final protected function initAction($actionString) {
		// set up commonly referenced properties by views and templates
		$_SESSION = array();
		// TODO: see http://php.net/manual/en/reserved.variables.server.php for
		// more properties to set
		$_SERVER['REQUEST_URI'] = "http://localhost/" . $actionString . ".php";

		// initiate action
		$this->action = $this->dispatcher->getAction($actionString);
		$this->actionDef = $this->action->getActionDef();
	}

	/**
	 * Run the action. Note that setUserInput() must be called prior to this
	 * if your action requires user input.
	 *
	 * @see UbarBaseActionTestCase::setUserInput()
	 */
	final protected function runAction() {
		// execute action, note that body may not execute if user conditions not met
		$this->resultString = $this->dispatcher->generateResult();

		// get result for action and input
		$this->resultDef = $this->dispatcher->getResultDef();

		// copy over messages so you can verify all messages were tested for
		// without modifying the message stack
		$this->messagesCopy[Action::ERRORS_KEY] = $this->action->getMessages(Action::ERRORS_KEY);
		$this->messagesCopy[Action::WARNINGS_KEY] = $this->action->getMessages(Action::WARNINGS_KEY);
		$this->messagesCopy[Action::NOTICES_KEY] = $this->action->getMessages(Action::NOTICES_KEY);
	}

	/**
	 * Assert that no exceptions were thrown during the render process. This
	 * will test for items in the template and view that are otherwise not
	 * tested during this process. It is not necessary that this be called for
	 * every action but it is helpful in views that have a large amount of code.
	 *
	 * Note that this will output the page contents to console.
	 */
	final protected function assertRenderValid() {
		$this->dispatcher->showResult();
	}

	/**
	 * Set user input simulating GET or POST params.
	 *
	 * @param string $key Input name
	 * @param mixed  $val Input value, may sometimes be an array
	 */
	final protected function setUserInput($key, $val) {
		$this->action->set($key, $val);
	}

	/**
	 * Clear values between tests.
	 *
	 * @todo Determine if this, or more, is necessary with recent code changes
	 */
	final protected function tearDown() {
		unset($this->actionDef);
		unset($this->action);
		unset($this->resultString);
		unset($this->resultDef);
	}

	// TODO: add helper methods for asserting has error, warning, info
	// '' title, path, etc

	// CUSTOM ASSERTIONS - either to indicate what should be tested on each
	// action or to facilitate some of the more fragile things to test. For
	// instance, testing

	final protected function assertTitleEquals($title) {
		$this->assertEquals($title, $this->action->getTitle());
	}

	final protected function assertSectionEquals($section) {
		$this->assertEquals($section, $this->action->getSection());
	}

	final protected function assertSubsectionEquals($subsection) {
		$this->assertEquals($subsection, $this->action->getSubsection());
	}

	final protected function assertActionClassEquals($actionClass) {
		$this->assertEquals($actionClass, getActionClassName());
	}

	final protected function assertActionNameEquals($name) {
		$this->assertEquals($name, getActionName());
	}

	final protected function assertViewEquals($location) {
		$this->assertEquals($location, $this->actionDef->getViewLocation());
	}

	final protected function assertResultEquals($result) {
		$this->assertEquals($result, $this->resultString);
	}

	final protected function assertResultTypeEquals($type) {
		$this->assertEquals($type, $this->resultDef->getType());
	}

	final protected function assertHasError($messageKey, array $arguments = array (), $fieldName = null) {
		$this->assertHasMessage(Action::ERRORS_KEY, $messageKey, $arguments, $fieldName);
	}

	final protected function assertHasErrorSimple($message, $fieldName = null) {
		$this->assertHasMessageSimple(Action::ERRORS_KEY, $message, $fieldName);
	}

	final protected function assertHasWarning($messageKey, array $arguments = array (), $fieldName = null) {
		$this->assertHasMessage(Action::WARNINGS_KEY, $messageKey, $arguments, $fieldName);
	}

	final protected function assertHasWarningSimple($message, $fieldName = null) {
		$this->assertHasMessageSimple(Action::WARNINGS_KEY, $message, $fieldName);
	}

	final protected function assertHasNotice($messageKey, array $arguments = array (), $fieldName = null) {
		$this->assertHasMessage(Action::NOTICES_KEY, $messageKey, $arguments, $fieldName);
	}

	final protected function assertHasNoticeSimple($message, $fieldName = null) {
		$this->assertHasMessageSimple(Action::NOTICES_KEY, $message, $fieldName);
	}

	final private function assertHasMessage($type, $messageKey, array $arguments = array (), $fieldName = null) {
		// create a message string with the given params
		$messageString = $this->action->getProperties()->get($messageKey, $arguments);

		$this->assertHasMessageSimple($type, $messageString, $fieldName);
	}

	// TODO: pull out found message from copy of messages
	final private function assertHasMessageSimple($type, $messageString, $fieldName = null) {
		// create a message object with the message and field
		$message = new Message($messageString, $fieldName);

		// try to find a matching message in the stored messages
		$messages = $this->messagesCopy[$type];
		foreach($messages as $key => $curMessage) {
			if($curMessage->equals($message)) {
				// remove message for copy to later verify all checked
				unset($this->messagesCopy[$type][$key]);
				return;
			}
		}

		// display available messages so you can compare to your message
		print_r($messages);

		$this->fail('Unable to find a message of type, ' . $type . ', with the message string, "' . $messageString . '" and field, "' . $fieldName . '".');
	}

	// each message assertion removes item from duplicate list when found, this
	// tests that all messages have been removed (ie tested in an assertion)
	// when called at the end of your test.
	// TODO: consider putting this in the teardown but using an annotation or
	// explicit ignore call to ignore it.
	final protected function assertAllMessagesTested() {
		$count = 0;
		$count += count($this->messagesCopy[Action::ERRORS_KEY]);
		$count += count($this->messagesCopy[Action::WARNINGS_KEY]);
		$count += count($this->messagesCopy[Action::NOTICES_KEY]);

		if($count > 0) {
			$messageDisplay = "";
			foreach($this->messagesCopy[Action::ERRORS_KEY] as $message) {
				$messageDisplay .= "\tERROR: " . $message . "\n";
			}
			foreach($this->messagesCopy[Action::WARNINGS_KEY] as $message) {
				$messageDisplay .= "\tWARNING: " . $message . "\n";
			}
			foreach($this->messagesCopy[Action::NOTICES_KEY] as $message) {
				$messageDisplay .= "\tNOTICE: " . $message . "\n";
			}

			$this->fail('Not all messages were checked. Number remaining: ' . $count . "\n" . $messageDisplay);
		}
	}
}
?>