<?php
abstract class Action {

	const ERRORS_KEY = 'errors';
	const WARNINGS_KEY = 'warnings';
	const NOTICES_KEY = 'notices';
	const USER_INPUT_KEY = 'userinput';

	private static $messageTypes = array( self::ERRORS_KEY, self::WARNINGS_KEY, self::NOTICES_KEY);

	protected $properties;
	protected $locale;
	protected $templateDef;
	protected $actionDef;
	// name of button clicked, often used to as something to switch on in executeInner method
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

	// locally stored user input, not pushed to session until teardown, after old data removed, so that only available for one page render
	private $userInput = array();

	public function __construct($def) {
		global $action, $actionDef;
		$this->actionDef = $def;
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

		function getParam($key) {
			global $action;
			return $action->getParam($key);
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

		// MESSAGE UTILS PASS THROUGH
		function hasErrors() {
			global $action;
			return $action->hasErrors();
		}

		function hasErrorsForField($fieldName) {
			global $action;
			return $action->hasErrorsForField($fieldName);
		}

		function hasErrorsOrWarningsForField($fieldName) {
			global $action;
			return $action->hasErrorsOrWarningsForField($fieldName);
		}

		function getErrorsForField($fieldName) {
			global $action;
			return $action->getErrorsForField($fieldName);
		}

		function getErrorsOrWarningsForField($fieldName) {
			global $action;
			return $action->getErrorsOrWarningsForField($fieldName);
		}

		function getErrors() {
			global $action;
			return $action->getErrors();
		}

		function hasWarnings() {
			global $action;
			return $action->hasWarnings();
		}

		function hasWarningsForField($fieldName) {
			global $action;
			return $action->hasWarningsForField($fieldName);
		}

		function getWarningsForField($fieldName) {
			global $action;
			return $action->getWarningsForField($fieldName);
		}

		function getWarnings() {
			global $action;
			return $action->getWarnings();
		}

		function hasNotices() {
			global $action;
			return $action->hasNotices();
		}

		function hasNoticesForField($fieldName) {
			global $action;
			return $action->hasNoticesForField($fieldName);
		}

		function getNoticesForField($fieldName) {
			global $action;
			return $action->getNoticesForField($fieldName);
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

		function getUserInput($key) {
			global $action;
			return $action->getUserInput($key);
		}
	}

	public final function setView($viewPath) {
		$this->view = $viewPath;
	}

	public final function getView() {
		return $this->view;
	}

	protected final function setUserLocale($locale) {
		$this->locale = $locale;
		$this->properties = null;
	}

	public final function setTemplateDef($templateDef) {
		$this->templateDef = $templateDef;

		// only override values if not already set
		if (is_null($this->section)) {
			$this->section = $templateDef->getSection();
		}
		if (is_null($this->subSection)) {
			$this->subSection = $templateDef->getSubSection();
		}
	}

	public final function getProperties() {
		if (is_null($this->properties)) {
			$this->properties = new LocalizedProperties($this->locale);
		}
		return $this->properties;
	}

	public final function getParam($key) {
		$param = NULL;
		// first check in action def
		if (!is_null($this->actionDef)) {
			$param = $this->actionDef->getParam($key);
			if (!is_null($param)) {
				return $param;
			}
		}

		// TODO: also allow results to have params?

		// if not found in action def, check template def
		if (!is_null($this->templateDef)) {
			return $this->templateDef->getParam($key);
		}
		return null;
	}

	public final function findMethodName($original) {
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

	public final function set($original, $value) {
		$capName = ucfirst($original);
		$methodNames = array (
			$original,
			"set" . $capName
		);
		foreach ($methodNames as $methodName) {
			if (method_exists($this, $methodName)) {
				$this-> $methodName ($value);
				// store user submitted input until next view IF storeUserInput() is called
				$this->addUserInput($original, $value);
				return;
			}
		}
		if (DEV_MODE) {
			throw new Exception("Method ,\"set" . $capName . "()\", was not found in the action");
		}
	}

	// TODO: make sure is scalar value
	private final function addMessage($messageKey, array $arguments = array(), $type, $fieldName = null) {
		if (!isset ($_SESSION[$type])) {
			$_SESSION[$type] = array ();
		}
		$message = $this->getProperties()->get($messageKey, $arguments);
		$this->addMessageSimple($message, $type, $fieldName);
	}

	private final function addMessageSimple($message, $type, $fieldName = null) {
		$messageObj = new Message($message, $fieldName);
		array_push($_SESSION[$type], $messageObj);
	}

	private final function hasMessages($type) {
		return isset ($_SESSION[$type]) && count($_SESSION[$type]) > 0;
	}

	public final function getMessagesForField($fieldName, $type = null) {
		$types = is_null($type) ? $this->messageTypes : array($type);
		$messages = array();
		foreach($types as $type) {
			if(isset($_SESSION[$type])) {
				foreach($_SESSION[$type] as $message) {
					if($message->getFieldName() == $fieldName) {
						array_push($messages, $message);
					}
				}
			}
		}
		return $messages;
	}

	public final function hasMessagesForField($fieldName, $type = null) {
		return count($this->getMessagesForField($fieldName, $type)) > 0;
	}

	// return empty array rather than null so that you don't need to check for null before iterating
	private final function getMessages($type) {
		return isset($_SESSION[$type]) ? $_SESSION[$type] : array();
	}

	// error management - blocking errors such as trying to view an object by id where the id does not exist
	public final function addError($messageKey, array $arguments = array(), $fieldName = null) {
		$this->localErrors++;
		$this->addMessage($messageKey, $arguments, self::ERRORS_KEY, $fieldName);
	}

	public final function addErrorSimple($message, $fieldName = null) {
		$this->addMessageSimple($message, self::ERRORS_KEY, $fieldName);
	}

	public final function hasErrorsLocal() {
		return $this->localErrors > 0;
	}

	public final function hasErrors() {
		return $this->hasMessages(self::ERRORS_KEY);
	}

	public final function getErrors() {
		return $this->getMessages(self::ERRORS_KEY);
	}

	public final function hasErrorsForField($fieldName) {
		return $this->hasMessagesForField($fieldName, self::ERRORS_KEY);
	}

	public final function getErrorsForField($fieldName) {
		return $this->getMessagesForField($fieldName, self::ERRORS_KEY);
	}

	public final function hasErrorsOrWarningsForField($fieldName) {
		return $this->hasErrorsForField($fieldName) || $this->hasWarningsForField($fieldName);
	}

	public final function getErrorsOrWarningsForField($fieldName) {
		$messages = $this->getErrorsForField($fieldName);
		array_merge($messages, $this->getWarningsForField($fieldName));
		return $messages;
	}

	// warning management - non-blocking errors such as trying to log in when already logged in
	public final function addWarning($messageKey, array $arguments = array(), $fieldName = null) {
		$this->addMessage($messageKey, $arguments, self::WARNINGS_KEY, $fieldName);
	}

	public final function addWarningSimple($message, $fieldName = null) {
		$this->addMessageSimple($message, self::WARNINGS_KEY, $fieldName);
	}

	public final function hasWarnings() {
		return $this->hasMessages(self::WARNINGS_KEY);
	}

	public final function getWarnings() {
		return $this->getMessages(self::WARNINGS_KEY);
	}

	public final function hasWarningsForField($fieldName) {
		return $this->hasMessagesForField($fieldName, self::WARNINGS_KEY);
	}

	public final function getWarningsForField($fieldName) {
		return $this->getMessagesForField($fieldName, self::WARNINGS_KEY);
	}

	// notice management - non-issue type messages
	public final function addNotice($messageKey, array $arguments = array(), $fieldName = null) {
		$this->addMessage($messageKey, $arguments, self::NOTICES_KEY, $fieldName);
	}

	public final function addNoticeSimple($message, $fieldName = null) {
		$this->addMessageSimple($message, self::NOTICES_KEY, $fieldName);
	}

	public final function hasNotices() {
		return $this->hasMessages(self::NOTICES_KEY);
	}

	public final function getNotices() {
		return $this->getMessages(self::NOTICES_KEY);
	}

	public final function hasNoticesForField($fieldName) {
		return $this->hasMessagesForField($fieldName, self::NOTICES_KEY);
	}

	public final function getNoticesForField($fieldName) {
		return $this->getMessagesForField($fieldName, self::NOTICES_KEY);
	}

	// VIEW SPECIFIC FUNCTIONALITY
	public final function initTemplateValues() {
		// only override values if not already set
		if (is_null($this->section) || $this->section == '') {
			$this->section = $this->templateDef->getSection();
		}
		if (is_null($this->subSection) || $this->subSection == '') {
			$this->subSection = $this->templateDef->getSubSection();
		}

	}

	protected final function setException($exception) {
		$this->exception = $exception;
	}

	public final function getException() {
		return $this->exception;
	}

	public final function getTitle() {
		return $this->title;
	}

	public final function getPage() {
		return $this->page;
	}

	public final function getSection() {
		return $this->section;
	}

	public final function getSubSection() {
		return $this->subSection;
	}

	// COMMON BUTTON NAMES
	// common submit button names so that dev mode doesn't throw errors on missing setters
	// TODO: note these as reserved function names
	// submit cancel update delete edit save next back last
	public final function setSubmit() {
		$this->buttonName = 'submit';
	}
	public final function setCancel() {
		$this->buttonName = 'cancel';
	}
	public final function setUpdate() {
		$this->buttonName = 'update';
	}
	public final function setDelete() {
		$this->buttonName = 'delete';
	}
	public final function setEdit() {
		$this->buttonName = 'edit';
	}
	public final function setSave() {
		$this->buttonName = 'save';
	}
	public final function setNext() {
		$this->buttonName = 'next';
	}
	public final function setBack() {
		$this->buttonName = 'back';
	}
	public final function setLast() {
		$this->buttonName = 'last';
	}

	public final function getButtonName() {
		return $this->buttonName;
	}

	public final function execute() {
		try {
			// validate user input if method exists
			if (!$this->isUserInputValid()) {
				// not valid, push back any stored user input for re-display
				$this->storeUserInput();
				return GlobalConstants :: USER_ERROR;
			}
			$result = $this->executeInner();
			// check for user error again in case secondary check performed in execute body
			if ($result == GlobalConstants :: USER_ERROR) {
				$this->storeUserInput();
			}
			return $result;
		} catch (Exception $e) {
			$this->setException($e);
			return GlobalConstants :: ERROR;
		}
	}

	// execute action body
	protected abstract function executeInner();

	// validate and get or post data for errors prior to execute
	// NOTE: not abstract so that actions without user input can ignore function
	protected function validateUserInput() {
		// noop - override to use user input validation
	}

	// do validation, if any, on action and return true if no errors found
	private final function isUserInputValid() {
		// run validation method on action
		$this->validateUserInput();

		// check to see if errors (not warnings, they are non-blocking)
		return !$this->hasErrorsLocal();
	}

	private final function addUserInput($key, $value) {
		$this->userInput[$key] = $value;
	}

	// get user input from session (or locally if result on USER_INPUT is a page or file rather than new action)
	public final function getUserInput($key) {
		if(isset( $_SESSION[self::USER_INPUT_KEY])) {
			$userInput = $_SESSION[self::USER_INPUT_KEY];
			return isset($userInput[$key]) ? $userInput[$key] : null;
		}
		return null;
	}

	// stores locally registered user input into the session for retrieval in next action
	// NOTE: this is called automatically if validateUserInput() adds an error or if result is USER_INPUT,
	// it may be called manually if neither case applies but original input still required. ex, you have
	// to differentiate two user input failures in the results so you can't have the name for both results
	// be USER_INPUT, instead you may have a result of custom_user_input_1 and ..._2 and manually store
	// the input
	private final function storeUserInput() {
		$_SESSION[self::USER_INPUT_KEY] = $this->userInput;
	}

	// to be called after a view is rendered, not included in tearDown so that non-view pages don't clear
	public final function clearTempData() {
		// clear out errors
		$_SESSION[self::ERRORS_KEY] = array ();

		// clear out warnings
		$_SESSION[self::WARNINGS_KEY] = array ();

		// clear out info
		$_SESSION[self::NOTICES_KEY] = array ();

		// clear out user input
		$_SESSION[self::USER_INPUT_KEY] = array ();
	}

}
?>