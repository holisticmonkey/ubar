<?php
/**
 * Class definition for Action
 * @package		core
 */

/**
 * Base controller class
 *
 * This class, coupled with the Dispatcher, does most of the work in this
 * framework. It is the base controller that your controller classes,
 * Actions, should extend. The following tasks are handled by this class:
 *
 * <ul>
 * <li>Provide access from your view to your public action methods.</li>
 * <li>Ingest user submitted data.</li>
 * <li>Manage messages such as errors, warnings, and notices.</li>
 * <li>Expose common "site" parameters such as title, section, page, etc.</li>
 * <li>Provide a structure for validating user input.</li>
 * <li>Expose configuration parameters.</li>
 * <li>Provide hooks for flow control</li>
 * </ul>
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 *
 * @todo Consider adding type or severity.
 */
abstract class Action {

	/**
	 * Session key for errors
	 */
	const ERRORS_KEY = 'errors';
	/**
	 * Session key for warnings (non-blocking errors)
	 */
	const WARNINGS_KEY = 'warnings';
	/**
	 * Session key for notices (confirmations, background info, etc)
	 */
	const NOTICES_KEY = 'notices';
	/**
	 * Session key for user submitted input
	 */
	const USER_INPUT_KEY = 'userinput';

	/**
	 * @var array A list of message types
	 * @static
	 */
	private static $messageTypes = array (
		self :: ERRORS_KEY,
		self :: WARNINGS_KEY,
		self :: NOTICES_KEY
	);

	/**
	 * @var class Properties instance.
	 */
	protected $properties;

	/**
	 * @var class Overriding locale.
	 */
	protected $locale;

	/**
	 * @var class Template definition for this action, if present.
	 */
	protected $templateDef;

	/**
	 * @var class Action definition.
	 */
	protected $actionDef;

	/**
	 * @var string Name of the clicked button if a form was submitted.
	 * Often used as something to switch on in executeInner().
	 */
	protected $buttonName;

	/**
	 * @var class Caught exception, used in display on error page.
	 */
	protected $exception;

	// view specific elements
	/**
	 * @var string Title of the page, if any.
	 */
	public $title;

	/**
	 * @var string Page name, same as name property in action definition.
	 */
	public $page;

	/**
	 * @var string Section page resides in, if defined.
	 */
	public $section;

	/**
	 * @var string Sub-section page resides in, if defined.
	 */
	public $subSection;

	/**
	 * @var string Path to view file, if defined.
	 */
	public $view;

	/**
	 * @var integer Number of errors found in this action's execution. Used
	 * instead of counting errors in session so as not to confuse with errors
	 * from last action execution that have not been rendered and removed yet.
	 */
	private $localErrors = 0;

	/**
	 * @var array User submitted data. Used for repopulation of form data on error.
	 */
	private $userInput = array ();

	/**
	 * Action custructor
	 *
	 * This primarily associates the action definition with the instance and
	 * creates functions for use by the view.
	 *
	 * @param class $def The definition for the action.
	 */
	public function __construct($def) {
		global $action, $actionDef;
		$this->actionDef = $def;
		$action = $this;
		$actionDef = $def;

		/**
		 * Pass through to public methods in your action.
		 *
		 * As this section defines global function for page access,
		 * constructing a second action in the same call would cause an error
		 * indicating that the function was already defined. This is typically
		 * not a problem as there is no reason to define two actions in the
		 * same request. However, action tests typically DO setup more than one
		 * action. For this reason, they is protected from re-declaration by
		 * checking for the existance of the "get" function.
		 *
		 * @see UbarBaseActionTestCase
		 */
		if(!function_exists("get")) {
			function get($methodName) {
				global $action;
				$methodName = $action->findMethodName($methodName);
				return $action-> $methodName ();
			}

			function getTxt($key, $arguments = array ()) {
				global $action, $UBAR_GLOB;
				// TODO: use dynamic arg retrieval and inspection?
				//$args = func_num_args();
				return $action->getProperties()->get($key, $arguments, $UBAR_GLOB['DEV_MODE']);
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

			// VIEW SPECIFIC FUNCTIONALITY
			function renderBody() {
				global $action;
				require_once ($action->getView());
			}

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
		$this->page = $def->getPage();
	}

	/**
	 * Set view path, if any, for this action.
	 *
	 * NOTE: This is only public so that it may be called by the Dispatcher.
	 * You should not be calling this yourself.
	 *
	 * @param string $viewPath The path to the view file.
	 *
	 * @see Dispatcher::initView()
	 */
	public final function setView($viewPath) {
		$this->view = $viewPath;
	}

	/**
	 * Get view path, if any, for this action.
	 *
	 * NOTE: This is only public so that it may be called by the Dispatcher.
	 * You should not be calling this yourself.
	 *
	 * @return string View path.
	 * @see Dispatcher::renderBody()
	 */
	public final function getView() {
		return $this->view;
	}

	/**
	 * Override default locale. This impacts which properties file is retreived
	 * and numeric, money and time formatting.
	 *
	 * @param class $locale Locale to use for override.
	 */
	protected final function setUserLocale($locale) {
		$this->locale = $locale;
		$this->properties = null;
	}

	/**
	 * Set the template definition for the action, if any.
	 *
	 * NOTE: This is only public so that it may be called by the Dispatcher.
	 * You should not be calling this yourself.
	 *
	 * @param class $templateDef
	 * @see Dispatcher::renderPage()
	 */
	public final function setTemplateDef($templateDef) {
		$this->templateDef = $templateDef;

		// only override values if not already set
		if (is_null($this->section) || $this->section == '') {
			$this->section = $this->templateDef->getSection();
		}
		if (is_null($this->subSection) || $this->subSection == '') {
			$this->subSection = $this->templateDef->getSubSection();
		}
	}

	/**
	 * Get an instance of your Properties for message retrieval.
	 *
	 * @return class Properties instance.
	 *
	 * @see LocalizedProperties
	 */
	public final function getProperties() {
		if (is_null($this->properties)) {
			$this->properties = new LocalizedProperties($this->locale);
		}
		return $this->properties;
	}

	/**
	 * Get a parameter associated with this action. It will either be from
	 * the action definition or the template definition if not defined in the
	 * action.
	 *
	 * @param string $key Key to lookup in action or template definition.
	 * @return string Parameter value or null if not found.
	 *
	 * @todo Also support params in results?
	 */
	public final function getParam($key) {
		$param = NULL;
		// first check in action def
		if (!is_null($this->actionDef)) {
			$param = $this->actionDef->getParam($key);
			if (!is_null($param)) {
				return $param;
			}
		}

		// if not found in action def, check template def
		if (!is_null($this->templateDef)) {
			return $this->templateDef->getParam($key);
		}
		return null;
	}

	/**
	 * Find a getter method with the given name or root name.
	 *
	 * NOTE: This is only public so that it may be called by the Dispatcher.
	 * You should not be calling this yourself.
	 *
	 * @param string $original Method name to find.
	 *
	 * @return string Found method name.
	 *
	 * @throws Throws an exception when no method found with the given name or root name.
	 *
	 * @see Dispatcher::evaluateResultString()
	 */
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
		throw new Exception("Methods ,\"get" . $original . "\" or \"is" . $original . "\", were not found in the action");
	}

	/**
	 * Set GET or POST data using the name to find the appropriate public
	 * setter method.
	 *
	 * NOTE: This is only public so that it may be called by the Dispatcher.
	 * You should not be calling this yourself.
	 *
	 * @param string $original Method name to find.
	 * @param mixed $value Value to set.
	 * @see Dispatcher::populateUserInput()
	 * @throws If DEV_MODE == true, throws an exception when no method found with the given name or root name.
	 *
	 * @todo Consider more protections on this method such as only allowing
	 * things that start with "set", checking the caller to make sure it is the
	 * Dispatcher, or anything else that will prevent accidental collisions.
	 */
	public final function set($original, $value) {
		global $UBAR_GLOB;

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
		if ($UBAR_GLOB['DEV_MODE']) {
			$this->addWarningSimple("Method ,\"set" . $capName . "()\", was not found in the action");
		}
	}

	/**
	 * Generate a message from key and args, create a message object and store
	 * it in the session.
	 *
	 * @param string $mesageKey Key to use in properties lookup.
	 * @param array $arguments Array of arguments to use for message
	 * substitution or expression evaluation.
	 * @param string $type Message type such as error or warning
	 * @param string $fieldName Optional input field associated with message.
	 *
	 * @todo Make sure that array values are scalars.
	 */
	private final function addMessage($messageKey, array $arguments = array (), $type, $fieldName = null) {
		$message = $this->getProperties()->get($messageKey, $arguments);
		$this->addMessageSimple($message, $type, $fieldName);
	}

	/**
	 * Create a message object and store it in the session.
	 *
	 * @param string $message Message string to store
	 * @param string $type Message type such as error or warning
	 * @param string $fieldName Optional input field associated with message.
	 */
	private final function addMessageSimple($message, $type, $fieldName = null) {
		if (!isset($_SESSION[$type])) {
			$_SESSION[$type] = array ();
		}
		$messageObj = new Message($message, $fieldName);
		array_push($_SESSION[$type], $messageObj);
	}

	/**
	 * Determine if there are messages present for the given type.
	 *
	 * @param string $type Message type to check for.
	 *
	 * @return boolean Indication as to whether messages of that type were
	 * present.
	 */
	public final function hasMessages($type) {
		return isset ($_SESSION[$type]) && count($_SESSION[$type]) > 0;
	}

	/**
	 * Get message objects for a given field name. This may be restricted to a
	 * given type. For instance, if you only want errors for a field so that
	 * you may display inline errors, you may restrict this call to errors.
	 *
	 * @param string $fieldName Field name to lookup in messages.
	 * @param string $type Optional message type to restrict to.
	 *
	 * @return array Messages found for the given field name and type or
	 * an empty array if no messages were found.
	 */
	public final function getMessagesForField($fieldName, $type = null) {
		$types = is_null($type) ? $this->messageTypes : array (
			$type
		);
		$messages = array ();
		foreach ($types as $type) {
			if (isset ($_SESSION[$type])) {
				foreach ($_SESSION[$type] as $message) {
					if ($message->getFieldName() == $fieldName) {
						array_push($messages, $message);
					}
				}
			}
		}
		return $messages;
	}

	/**
	 * Determin if there are messages for a given field name. This may be
	 * restricted to a given type. For instance, if you only want errors for
	 * a field so that you may display inline errors, you may restrict this to
	 * only check for errors.
	 *
	 * @param string $fieldName Field name to lookup in messages.
	 * @param string $type Optional message type to restrict to.
	 *
	 * @return boolean An indication whether there were messages for the given
	 * field name and type.
	 */
	public final function hasMessagesForField($fieldName, $type = null) {
		return count($this->getMessagesForField($fieldName, $type)) > 0;
	}

	/**
	 * Get message objects for a given type.
	 *
	 * @param string $type Type of messages to retrieve.
	 *
	 * @return array A collection of messages for the given type or an empty
	 * array if no messages were found.
	 */
	public final function getMessages($type) {
		return isset ($_SESSION[$type]) ? $_SESSION[$type] : array ();
	}

	/**
	 * Add an error using the given key and arguments optionally assoiciated
	 * with a given input field. Note that these are blocking errors and they
	 * may impact the result of this action.
	 *
	 * @param string $mesageKey Key to use in properties lookup.
	 * @param array $arguments Array of arguments to use for message
	 * substitution or expression evaluation.
	 * @param string $fieldName Optional input field associated with message.
	 *
	 * @see Action::execute()
	 */
	public final function addError($messageKey, array $arguments = array (), $fieldName = null) {
		$this->localErrors++;
		$this->addMessage($messageKey, $arguments, self :: ERRORS_KEY, $fieldName);
	}

	/**
	 * Add an error using the provided message string optionally associated
	 * with a given input field. Note that these are blocking errors and they
	 * may impact the result of this action.
	 *
	 * NOTE: The use of this is discouraged but you may find it cumbersome to add
	 * all of your messages to a properties file.
	 *
	 * @param string $message Message of the error.
	 * @param string $fieldName Optional input field associated with message.
	 *
	 * @see Action::execute()
	 */
	public final function addErrorSimple($message, $fieldName = null) {
		$this->localErrors++;
		$this->addMessageSimple($message, self :: ERRORS_KEY, $fieldName);
	}

	/**
	 * Test whether THIS action produced any errors. This is used to
	 * differentiate between errors that have not yet been rendered from a
	 * previous action and new errors. This impacts whether the result for
	 * USER_INPUT is returned.
	 *
	 * @see Action::execute()
	 * @see GlobalConstants :: USER_ERROR
	 */
	private final function hasErrorsLocal() {
		return $this->localErrors > 0;
	}

	/**
	 * Test for the presence of errors.
	 *
	 * @return boolean An indication to whether errors were found.
	 */
	public final function hasErrors() {
		return $this->hasMessages(self :: ERRORS_KEY);
	}

	/**
	 * Get error messages.
	 *
	 * @return array A collection of error messages or an empty array if no
	 * messages were found.
	 */
	public final function getErrors() {
		return $this->getMessages(self :: ERRORS_KEY);
	}

	/**
	 * Test for the presence of errors for a given field name.
	 *
	 * @param string $fieldName Name of the field to check for associated
	 * errors.
	 *
	 * @return boolean An indication to whether errors were found for the given
	 * field name.
	 */
	public final function hasErrorsForField($fieldName) {
		return $this->hasMessagesForField($fieldName, self :: ERRORS_KEY);
	}

	/**
	 * Get error messages for the given field name.
	 *
	 * @return array A collection of error messages for the given field or an
	 * empty array if no messages were found.
	 */
	public final function getErrorsForField($fieldName) {
		return $this->getMessagesForField($fieldName, self :: ERRORS_KEY);
	}

	/**
	 * Test for the presence of errors OR warnings for a given field name.
	 *
	 * @param string $fieldName Name of the field to check for associated
	 * errors or warnings.
	 *
	 * @return boolean An indication to whether errors or warnings were found
	 * for the given field name.
	 */
	public final function hasErrorsOrWarningsForField($fieldName) {
		return $this->hasErrorsForField($fieldName) || $this->hasWarningsForField($fieldName);
	}

	/**
	 * Get error or warning messages for the given field name.
	 *
	 * @return array A collection of error or warnings messages for the given
	 * field or an empty array if no messages were found.
	 */
	public final function getErrorsOrWarningsForField($fieldName) {
		$messages = $this->getErrorsForField($fieldName);
		array_merge($messages, $this->getWarningsForField($fieldName));
		return $messages;
	}

	/**
	 * Add a warning using the given key and arguments optionally assoiciated
	 * with a given input field. Note that these do not block like errors and
	 * they have no impact on the result.
	 *
	 * @param string $mesageKey Key to use in properties lookup.
	 * @param array $arguments Array of arguments to use for message
	 * substitution or expression evaluation.
	 * @param string $fieldName Optional input field associated with message.
	 */
	public final function addWarning($messageKey, array $arguments = array (), $fieldName = null) {
		$this->addMessage($messageKey, $arguments, self :: WARNINGS_KEY, $fieldName);
	}

	/**
	 * Add an error using the provided message string optionally associated
	 * with a given input field. Note that these are not blocking and they
	 * will have no impact the result of this action.
	 *
	 * NOTE: The use of this is discouraged but you may find it cumbersome to add
	 * all of your messages to a properties file.
	 *
	 * @param string $message Message of the error.
	 * @param string $fieldName Optional input field associated with message.
	 */
	public final function addWarningSimple($message, $fieldName = null) {
		$this->addMessageSimple($message, self :: WARNINGS_KEY, $fieldName);
	}

	/**
	 * Test for the presence of warnings.
	 *
	 * @return boolean An indication to whether warnings were found.
	 */
	public final function hasWarnings() {
		return $this->hasMessages(self :: WARNINGS_KEY);
	}

	/**
	 * Get warning messages.
	 *
	 * @return array A collection of warning messages or an empty array if no
	 * messages were found.
	 */
	public final function getWarnings() {
		return $this->getMessages(self :: WARNINGS_KEY);
	}

	/**
	 * Test for the presence of warnings for a given field name.
	 *
	 * @param string $fieldName Name of the field to check for associated
	 * warnings.
	 *
	 * @return boolean An indication to whether warnings were found for the given
	 * field name.
	 */
	public final function hasWarningsForField($fieldName) {
		return $this->hasMessagesForField($fieldName, self :: WARNINGS_KEY);
	}

	/**
	 * Get warning messages for the given field name.
	 *
	 * @return array A collection of warning messages for the given field or an
	 * empty array if no messages were found.
	 */
	public final function getWarningsForField($fieldName) {
		return $this->getMessagesForField($fieldName, self :: WARNINGS_KEY);
	}

	/**
	 * Add a notice using the given key and arguments optionally assoiciated
	 * with a given input field. Note that these are not blocking  and they
	 * will have no impact the result of this action.
	 *
	 * @param string $mesageKey Key to use in properties lookup.
	 * @param array $arguments Array of arguments to use for message
	 * substitution or expression evaluation.
	 * @param string $fieldName Optional input field associated with message.
	 */
	public final function addNotice($messageKey, array $arguments = array (), $fieldName = null) {
		$this->addMessage($messageKey, $arguments, self :: NOTICES_KEY, $fieldName);
	}

	/**
	 * Add a notice using the provided message string optionally associated
	 * with a given input field. Note that these are not blocking and they
	 * will have no impact the result of this action.
	 *
	 * NOTE: The use of this is discouraged but you may find it cumbersome to add
	 * all of your messages to a properties file.
	 *
	 * @param string $message Message of the error.
	 * @param string $fieldName Optional input field associated with message.
	 */
	public final function addNoticeSimple($message, $fieldName = null) {
		$this->addMessageSimple($message, self :: NOTICES_KEY, $fieldName);
	}

	/**
	 * Test for the presence of notices.
	 *
	 * @return boolean An indication to whether notices were found.
	 */
	public final function hasNotices() {
		return $this->hasMessages(self :: NOTICES_KEY);
	}

	/**
	 * Get notice messages.
	 *
	 * @return array A collection of notice messages or an empty array if no
	 * messages were found.
	 */
	public final function getNotices() {
		return $this->getMessages(self :: NOTICES_KEY);
	}

	/**
	 * Test for the presence of notices for a given field name.
	 *
	 * @param string $fieldName Name of the field to check for associated
	 * notices.
	 *
	 * @return boolean An indication to whether notices were found for the given
	 * field name.
	 */
	public final function hasNoticesForField($fieldName) {
		return $this->hasMessagesForField($fieldName, self :: NOTICES_KEY);
	}

	/**
	 * Get notice messages for the given field name.
	 *
	 * @return array A collection of notice messages for the given field or an
	 * empty array if no messages were found.
	 */
	public final function getNoticesForField($fieldName) {
		return $this->getMessagesForField($fieldName, self :: NOTICES_KEY);
	}

	/**
	 * Set an exception for display in an error page. This is typically used
	 * for unexpected errors where you want to display the stack trace for
	 * debugging purposes.
	 *
	 * NOTE: Action::execute() uses this for any uncaught exceptions and
	 * returns an ERROR result type. If you do not want this behavior, catch
	 * all exception in your executeInner() and validateUserInput() methods.
	 *
	 * @see Action::execute()
	 * @see Action::validateUserInput()
	 */
	protected final function setException($exception) {
		$this->exception = $exception;
	}

	/**
	 * Get exception caught in execute method or set explicitly.
	 *
	 * @return class Caught exception.
	 */
	public final function getException() {
		return $this->exception;
	}

	/**
	 * Get page title if any.
	 *
	 * @return string Title of the page if any.
	 */
	public final function getTitle() {
		return $this->title;
	}

	/**
	 * Get page name, this is the name property of your action definition.
	 *
	 * NOTE: This is primarily used for navigation highlighting in templates or
	 * the hide/display of navigation sections.
	 *
	 * @return string Page name.
	 */
	public final function getPage() {
		return $this->page;
	}

	/**
	 * Get site section if any. This may come from your action definition or
	 * your template definition.
	 *
	 * NOTE: This is primarily used for navigation highlighting in templates or
	 * the hide/display of navigation sections.
	 *
	 * @return string Section of the page if any.
	 */
	public final function getSection() {
		return $this->section;
	}

	/**
	 * Get site subsection if any. This may come from your action definition or
	 * your template definition.
	 *
	 * NOTE: This is primarily used for navigation highlighting in templates or
	 * the hide/display of navigation sections.
	 *
	 * @return string Subsection of the page if any.
	 */
	public final function getSubSection() {
		return $this->subSection;
	}

	/**
	 * Get the action definition associated with this action.
	 *
	 * NOTE: this is currently only used for testing.
	 *
	 * @return class Action definition.
	 */
	public final function getActionDef() {
		return $this->actionDef;
	}

	// COMMON BUTTON NAMES

	/**
	 * Setter for "submit" button.
	 *
	 * This is a convenience method for either suppressing complaints about
	 * missing setters when button name/val pairs are not important OR for
	 * controlling action processing based on which button was pressed.
	 *
	 * @see Action::getButtonName()
	 */
	public final function setSubmit() {
		$this->buttonName = 'submit';
	}

	/**
	 * Setter for "cancel" button.
	 *
	 * This is a convenience method for either suppressing complaints about
	 * missing setters when button name/val pairs are not important OR for
	 * controlling action processing based on which button was pressed.
	 *
	 * @see Action::getButtonName()
	 */
	public final function setCancel() {
		$this->buttonName = 'cancel';
	}

	/**
	 * Setter for "update" button.
	 *
	 * This is a convenience method for either suppressing complaints about
	 * missing setters when button name/val pairs are not important OR for
	 * controlling action processing based on which button was pressed.
	 *
	 * @see Action::getButtonName()
	 */
	public final function setUpdate() {
		$this->buttonName = 'update';
	}

	/**
	 * Setter for "delete" button.
	 *
	 * This is a convenience method for either suppressing complaints about
	 * missing setters when button name/val pairs are not important OR for
	 * controlling action processing based on which button was pressed.
	 *
	 * @see Action::getButtonName()
	 */
	public final function setDelete() {
		$this->buttonName = 'delete';
	}

	/**
	 * Setter for "edit" button.
	 *
	 * This is a convenience method for either suppressing complaints about
	 * missing setters when button name/val pairs are not important OR for
	 * controlling action processing based on which button was pressed.
	 *
	 * @see Action::getButtonName()
	 */
	public final function setEdit() {
		$this->buttonName = 'edit';
	}

	/**
	 * Setter for "save" button.
	 *
	 * This is a convenience method for either suppressing complaints about
	 * missing setters when button name/val pairs are not important OR for
	 * controlling action processing based on which button was pressed.
	 *
	 * @see Action::getButtonName()
	 */
	public final function setSave() {
		$this->buttonName = 'save';
	}

	/**
	 * Setter for "next" button.
	 *
	 * This is a convenience method for either suppressing complaints about
	 * missing setters when button name/val pairs are not important OR for
	 * controlling action processing based on which button was pressed.
	 *
	 * @see Action::getButtonName()
	 */
	public final function setNext() {
		$this->buttonName = 'next';
	}

	/**
	 * Setter for "back" button.
	 *
	 * This is a convenience method for either suppressing complaints about
	 * missing setters when button name/val pairs are not important OR for
	 * controlling action processing based on which button was pressed.
	 *
	 * @see Action::getButtonName()
	 */
	public final function setBack() {
		$this->buttonName = 'back';
	}

	/**
	 * Setter for "last" button.
	 *
	 * This is a convenience method for either suppressing complaints about
	 * missing setters when button name/val pairs are not important OR for
	 * controlling action processing based on which button was pressed.
	 *
	 * @see Action::getButtonName()
	 */
	public final function setLast() {
		$this->buttonName = 'last';
	}

	/**
	 * Get the name of the clicked button in form submission. This is typically
	 * used to control action processing based on which button was clicked.
	 *
	 * NOTE: This only works for methods that have defined setters. You may add
	 * your own to your action by creating a set{mybuttonname} method that sets
	 * $this->buttonName.
	 *
	 * @return string Clicked button name.
	 *
	 * @todo Instead of having a bunch of setters, update Action::set() to
	 * use a list of common button names? This creates an issue when someone
	 * accidentally uses a reserved setter name...
	 */
	public final function getButtonName() {
		return $this->buttonName;
	}

	/**
	 * Process the action. If there is an overriding validateUserInput() method
	 * it will run that first and check for new errors before calling your
	 * action's executeInner() method. IF errors were found OR your result is
	 * GlobalConstants::USER_ERROR, any submitted input will be pushed into the
	 * session until the next action that successfully generates a view has
	 * completed rendering.
	 *
	 * NOTE: This is only public so that it may be called from the Dispatcher
	 * or tests. Its use is highly discouraged elsewhere.
	 *
	 * @return string A result string used for result definition lookup.
	 *
	 * @see Dispatcher::dispatch()
	 */
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

	/**
	 * Execute method for your action. This is where all of the "work" is
	 * performed in your action. This is called from inside Action::execute()
	 * if user input validation passes.
	 *
	 * @return string A result string used for result definition lookup.
	 *
	 * @see Action::execute()
	 */
	protected abstract function executeInner();

	/**
	 * Validate any user submitted input. In the event your action accepts
	 * input from the user in the form of GET or POST data, you may validate
	 * it prior to running your executeInner() method. This is often useful
	 * for making sure values are present, of the right type, in range, etc.
	 * before trying to perform work with them.
	 *
	 * The executeInner() method will not run and GlobalConstants::USER_INPUT
	 * will be the returned result string if errors are added in this method.
	 *
	 * NOTE: This is not abstract so that actions may choose not to implement
	 * validation.
	 *
	 * @see Action::execute()
	 */
	protected function validateUserInput() {
		// noop - override to use user input validation
	}

	/**
	 * Run user input validation and check whether errors were discovered.
	 *
	 * @return boolean Return true if no errors were found.
	 *
	 * @see Action::validateUserInput()
	 * @see Action::execute()
	 * @see Action::hasErrorsLocal()
	 */
	private final function isUserInputValid() {
		// run validation method on action
		$this->validateUserInput();

		// check to see if errors (not warnings, they are non-blocking)
		return !$this->hasErrorsLocal();
	}

	/**
	 * Store user input temporarily. If user input validation fails, this will
	 * be pushed into the session until the next view render is complete.
	 *
	 * @param string $key Name for GET or POST param.
	 * @param mixed $value User submitted value.
	 *
	 * @see Action::storeUserInput()
	 */
	private final function addUserInput($key, $value) {
		$this->userInput[$key] = $value;
	}

	/**
	 * Get user submitted input for a given key.
	 *
	 * @param string $key Name for GET or POST param.
	 *
	 * @return mixed User submitted value.
	 */
	public final function getUserInput($key) {
		if (isset ($_SESSION[self :: USER_INPUT_KEY])) {
			$userInput = $_SESSION[self :: USER_INPUT_KEY];
			return isset ($userInput[$key]) ? $userInput[$key] : null;
		}
		return null;
	}

	// stores locally registered user input into the session for retrieval in next action
	// NOTE: this is called automatically if validateUserInput() adds an error or if result is USER_INPUT,
	//
	/**
	 * Store user input in the event of input validation failure. This is
	 * called by Action::execute() if Action::isUserInputValid() returns false
	 * or Action::executeInner() returns a result string of GlobalConstants::USER_ERROR
	 *
	 * NOTE: This is only kept in the session until after the next successful view render.
	 *
	 * NOTE: It may be called manually if neither case applies but original
	 * input still required. For example you have to differentiate between two
	 * user input failures in executeInner() so you can't return USER_INPUT for
	 * both. In that case, you would manually call this before reurning
	 * something like custom_user_input_A or custom_user_input_B.
	 *
	 * @see Action::execute()
	 * @see Action::isUserInputValid()
	 * @see Action::executeInner()
	 */
	protected final function storeUserInput() {
		$_SESSION[self :: USER_INPUT_KEY] = $this->userInput;
	}

	/**
	 * Clear out temporary data on successful page render. This prevents
	 * accidental build up of messages or submitted data.
	 *
	 * @see Dispatcher::renderPage()
	 */
	public final function clearTempData() {
		// clear out errors
		$_SESSION[self :: ERRORS_KEY] = array ();

		// clear out warnings
		$_SESSION[self :: WARNINGS_KEY] = array ();

		// clear out info
		$_SESSION[self :: NOTICES_KEY] = array ();

		// clear out user input
		$_SESSION[self :: USER_INPUT_KEY] = array ();
	}
}
?>