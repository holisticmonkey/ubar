<?php
abstract class Action {

	protected $properties;
	protected $locale;
	protected $templateDef;
	protected $actionDef;
	// name of button
	protected $buttonName;
	// caught exception, used in display in error page
	protected $exception;

	// view specific elements
	public $title;
	public $page;
	public $section;
	public $subSection;
	public $view;

	// errors that occured in this dispatch, needed in case of errors prior to forward or un-rendered errors
	private $localErrors = 0;
	// TODO: consider if similar to above needed for warnings and errors

	public function __construct( $def) {
		global $action, $actionDef;
		$action = $this;
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
			global $action;
			require_once ($action->getView());
		}

		// VIEW SPECIFIC FUNCTIONALITY - init view properties that may be in action
		// NOTE: we don't have template yet to get section, subsection, etc
		// NOTE: there is overlap between action defs and template defs
		// NOTE: put here instead of in an extended action in expectation of
		// implementers wanting to extend Action with global functionality common
		// to their own implementation of submit, view, etc type actions

		$this->title = $def->getTitle();

		// if no value set, will get back an empty string from the def
		if ($this->title == '') {
			$key = $def->getTitleKey();
			if ($key != '') {
				$this->title = getTxt($key);
			}
		}

		$this->section = $def->getSection();
		$this->subSection = $def->getSubSection();

		function getTitle() {
			global $action;
			return $action->title;
		}

		function getPage() {
			global $action;
			return $action->page;
		}

		function getSubSection() {
			global $action;
			return $action->getSubSection();
		}

		function getSection() {
			global $action;
			return $action->getSection();
		}

		// ERROR SPECIFIC FUNCTIONALITY
		function getException() {
			global $action;
			return $action->getException();
		}
	}

	public function setView($viewPath) {
		$this->view = $viewPath;
	}

	public function getView() {
		return $this->view;
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

	public function hasErrorsLocal() {
		return $this->localErrors > 0;
	}

	public function hasErrors() {
		return $this->hasMessages("errors");
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

	// VIEW SPECIFIC FUNCTIONALITY
	public function initTemplateValues() {
		// only override values if not already set
		if (is_null($this->section) || $this->section == '') {
			$this->section = $this->templateDef->getSection();
		}
		if (is_null($this->subSection) || $this->subSection == '') {
			$this->subSection = $this->templateDef->getSubSection();
		}

	}

	protected function setException($exception) {
		$this->exception = $exception;
	}

	public function getException() {
		return $this->exception;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getPage() {
		return $this->page;
	}

	public function getSection() {
		return $this->section;
	}

	public function getSubSection() {
		return $this->subSection;
	}

	// COMMON BUTTON NAMES
	// common submit button names so that dev mode doesn't throw errors on missing setters
	// TODO: note these as reserved function names
	// submit cancel update delete edit save next back last
	public function setSubmit() {
		$this->buttonName = 'submit';
	}
	public function setCancel() {
		$this->buttonName = 'cancel';
	}
	public function setUpdate() {
		$this->buttonName = 'update';
	}
	public function setDelete() {
		$this->buttonName = 'delete';
	}
	public function setEdit() {
		$this->buttonName = 'edit';
	}
	public function setSave() {
		$this->buttonName = 'save';
	}
	public function setNext() {
		$this->buttonName = 'next';
	}
	public function setBack() {
		$this->buttonName = 'back';
	}
	public function setLast() {
		$this->buttonName = 'last';
	}

	public function getButtonName() {
		return $this->buttonName;
	}

	public function execute() {
		try {
			// validate user input if method exists
			if (!$this->isUserInputValid()) {
				// not valid, push back any stored user input for re-display
				$this->pushBackUserInput();
				return GlobalConstants :: USER_ERROR;
			}
			$result = $this->executeInner();
			// check for user error again in case secondary check performed in execute body
			if ($result == GlobalConstants :: USER_ERROR) {
				$this->pushBackUserInput();
			}
			return $result;
		} catch (Exception $e) {
			$this->setException($e);
			return GlobalConstants :: ERROR;
		}
	}

	// execute action body
	public abstract function executeInner();

	// validate and get or post data for errors prior to execute
	// NOTE: not abstract so that actions without user input can ignore function
	public function validateUserInput() {
		// noop - override to use user input validation
	}

	// do validation, if any, on action and return true if no errors found
	private function isUserInputValid() {
		// run validation method on action
		$this->validateUserInput();

		// check to see if errors (not warnings, they are non-blocking)
		return !$this->hasErrorsLocal();
	}

	private function pushBackUserInput() {
		// stub
	}

}
?>