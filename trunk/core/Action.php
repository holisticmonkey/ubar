<?php
abstract class Action {

	protected $properties;
	protected $locale;
	protected $templateDef;
	protected $actionDef;

	// errors that occured in this dispatch, needed in case of errors prior to forward or un-rendered errors
	private $localErrors = 0;
	// TODO: consider if similar to above needed for warnings and errors

	public function __construct($viewPath, $def) {
		global $action, $view, $actionDef;
		$action = $this;
		$view = $viewPath;
		$actionDef = $def;

		function get($methodName) {
			global $action;
			$methodName = $action->findMethodName($methodName);
			return $action-> $methodName ();
		}

		function getTxt($key, $arguments = array ()) {
			global $action;
			// TODO: use dynamic arg retrieval and inspection
			//$args = func_num_args();
			return $action->getProperties()->get($key, $arguments, DEV_MODE);
		}

		function getTemplateVal($key) {
			global $action;
			return $action->getTemplateVal($key);
		}

		// ACTION CONFIG INFO
		// get name of action
		function getActionName() {
			global $actionDef;
			return $actionDef->getName();
		}

		// get class name defined for action
		function getActionClassName() {
			global $actionDef;
			return $actionDef->getClassName();
		}

		// get view path
		function getViewPath() {
			global $view;
			return $view;
		}

		function hasErrors() {
			global $action;
			return $action->hasErrors();
		}

		function getErrors() {
			global $action;
			return $action->getErrors();
		}

		function hasWarnings() {
			global $action;
			return $action->hasWarnings();
		}

		function getWarnings() {
			global $action;
			return $action->getWarnings();
		}

		function hasNotices() {
			global $action;
			return $action->hasNotices();
		}

		function getNotices() {
			global $action;
			return $action->getNotices();
		}

		// TODO: expose template config info?

		function renderBody() {
			global $view;
			require_once ($view);
		}
	}

	protected function setUserLocale($locale) {
		$this->locale = $locale;
		$this->properties = null;
	}

	public function setTemplateDef($templateDef) {
		$this->templateDef = $templateDef;

		// only override values if not already set
		if (is_null($this->section)) {
			$this->section = $templateDef->getSection();
		}
		if (is_null($this->subSection)) {
			$this->subSection = $templateDef->getSubSection();
		}
	}

	public function getProperties() {
		if (is_null($this->properties)) {
			$this->properties = new LocalizedProperties($this->locale);
		}
		return $this->properties;
	}

	public function getTemplateVal($key) {
		if (!is_null($this->templateDef)) {
			return $this->templateDef->getParam($key);
		}
		return null;
	}

	public function findMethodName($original) {
		$capName = ucfirst($original);
		$methodNames = array (
			$original,
			"get" . $capName,
			"is" . $capName
		);
		foreach ($methodNames as $methodName) {
			if (method_exists($this, $methodName)) {
				return $methodName;
			}
		}
		throw new Exception("Method ,\"" . $original . "\", was not found in the action");
	}

	public function set($original, $value) {
		$capName = ucfirst($original);
		$methodNames = array (
			$original,
			"set" . $capName
		);
		foreach ($methodNames as $methodName) {
			if (method_exists($this, $methodName)) {
				$this-> $methodName ($value);
				return;
			}
		}
		if (DEV_MODE) {
			throw new Exception("Method ,\"set" . $capName . "()\", was not found in the action");
		}
	}

	// TODO: make sure is scalar value
	private function addMessage($message, $type) {
		if (!isset ($_SESSION[$type])) {
			$_SESSION[$type] = array ();
		}
		array_push($_SESSION[$type], $message);
	}

	private function hasMessages($type) {
		return isset ($_SESSION[$type]) && count($_SESSION[$type]) > 0;
	}

	private function getMessages($type) {
		// copy values to a temp array
		$tempMessages = $_SESSION[$type];
		// re-init messages array to empty it
		$_SESSION[$type] = array ();
		return $tempMessages;
	}

	// error management - blocking errors such as trying to view an object by id where the id does not exist
	public function addError($message) {
		$this->localErrors++;
		$this->addMessage($message, "errors");
	}

	public function hasErrors() {
		return $this->localErrors > 0;
	}

	public function getErrors() {
		return $this->getMessages("errors");
	}

	// warning management - non-blocking errors such as trying to log in when already logged in
	public function addWarning($message) {
		$this->addMessage($message, "warnings");
	}

	public function hasWarnings() {
		return $this->hasMessages("warnings");
	}

	public function getWarnings() {
		return $this->getMessages("warnings");
	}

	// notice management - non-issue type messages
	public function addNotice($message) {
		$this->addMessage($message, "notices");
	}

	public function hasNotices() {
		return $this->hasMessages("notices");
	}

	public function getNotices() {
		return $this->getMessages("notices");
	}

	// common submit button names so that dev mode doesn't throw errors on missing setters
	public function setSubmit() {}

	// execute action body
	public abstract function execute();

	// validate and get or post data for errors prior to execute
	public function validateUserInput() {
		// noop - override to use user input validation
	}

}
?>