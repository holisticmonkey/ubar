<?php
/**
 * Class definition for Action
 * @package		core
 */

/**
 * Dispatcher for all requests
 *
 * This class, coupled with an Action, does most of the work in this
 * framework. It parses the requested action out of the URL, instantiates the
 * action, populates user input, executes the action and performes the
 * appropriate response.
 *
 * @author		Joshua A. Ganderson <jag@josh.com>
 * @link		http://www.holisticmonkey.com/Framework.action
 * @copyright	Copyright (c) 2010, Joshua A. Ganderson
 * @license		http://www.gnu.org/licenses/gpl.html GNU General Public License v3
 * @package		core
 *
 * @todo Allow override of action extension, currently fixed at ".action"
 */
class Dispatcher {

	/**
	 * Regular expression to parse the action name out of the request URL.
	 *
	 * EXAMPLE: match "Foo" in /Foo.action in order to load the "Foo"
	 * action definition.
	 *
	 * OPTIONS: utf-8
	 */
	const ACTION_REGEX = '/([^\/]+)\.action/u';

	/**
	 * Regular expression to parse expression calls out of result strings.
	 * This is typically used to direct the user to a page that requires a GET
	 * param, such as postID, to be displayed correctly.
	 *
	 * EXAMPLE: match "referringPage" and substitute with value from
	 * getReferringPage() in action when evaluating the result,
	 * <result name="USER_ERROR" type="url">Login.action?ref=${refPage}</result>
	 *
	 * NOTE: Right now this requires using the url result type. In future
	 * iterations, results will be able to take params and auto-populate GET
	 * params.
	 *
	 * OPTIONS: multiline, extra analysis, utf-8, ungreedy
	 */
	const RESULT_EXPRESSION_REGEX = '/\${([^$]*)}/mSuU';

	/**
	 * Instance of a convenience class that provides a action name to action
	 * lookup.
	 *
	 * @see ActionMapper
	 */
	private $actionMapper;

	/**
	 * Current action instance being executed. This will extend Action and is
	 * found by parsing the action name from the requested URL and looking up
	 * the action class associated with that name.
	 *
	 * @see Action
	 */
	private $action;

	/**
	 * Definition for the current action instance being executed. This contains
	 * information about the action class, asociated views, templates, etc.
	 * The definition is created from ubar.xml and is found using the
	 * ActionMapper.
	 *
	 * @see ActionMapper
	 * @see $actionMapper
	 */
	private $actionDef;

	/**
	 * Result string returned from executed action. This is the result name
	 * used to lookup a result definition in the Action or ActionMapper if not
	 * found in the Action instance.
	 *
	 * @see $action
	 * @see ActionMapper
	 * @see $actionMapper
	 */
	private $resultString;

	/**
	 * Construct the dispatcher. This just sets up the dispatcher for
	 * definition retrieval.
	 *
	 * @see Dispatcher::$actionMapper
	 * @see ActionMapper
	 */
	public function __construct($configPath) {
		$this->actionMapper = new ActionMapper($configPath);
	}

	/**
	 * Dispatch request to appropriate result. Typical usage is to retrieve the
	 * action name from the requested URL, however, this may be overriden for
	 * testing purposes.
	 *
	 * Dispatching renders the appropriate view, file, or forwards to another
	 * action or url. Further result types may be supported in the future.
	 * Behaviors associated with a given action name are defined in ubar.xml.
	 *
	 * NOTE: This is a public method so that it may be called from the init
	 * script or tests. It is not recommended that you call it directly
	 * elsewhere.
	 *
	 * @param string $actionString Overriding action string. Use not
	 * recommended outside of testing.
	 */
	public function dispatch($actionString = null) {

		// initiate action
		$this->getAction($actionString);

		// populate action from get and post
		$this->populateUserInput();

		// execute action, note that body may not execute if user conditions not met
		$this->generateResult();

		// get result for action and input
		$this->getResultDef();

		// show result
		$this->showResult();
	}

	/**
	 * Get action definition and instance using the requested URL or using an
	 * overriding action name. The override is typically only used for testing
	 * purposes and it is not recommended that it be used elsewhere.
	 *
	 * NOTE: This is a public method so that it may be called from tests. It is
	 * not recommended that you call it directly elsewhere.
	 *
	 * @param string $actionString Overriding action string. Use not
	 * recommended outside of testing.
	 *
	 * @returns class The action instance.
	 *
	 * @throws ActionNotFoundException If the action file was not found.
	 * @throws Exception if the action def was not found and no default was
	 * found.
	 */
	public function getAction($actionString = null) {
		global $UBAR_GLOB;

		// no override to action name, get from url
		if (is_null($actionString)) {
			// match action part of the URI
			preg_match(self :: ACTION_REGEX, $_SERVER['REQUEST_URI'], $matches);

			// get action string from url
			if (isset ($matches[1])) {
				$actionString = $matches[1];
			} else {
				// didn't match format despite .htaccess rule, assume empty and get default
				$this->actionDef = $this->actionMapper->getDefaultAction();
			}
		}

		// get action from action name if not already set to default
		if (is_null($this->actionDef)) {
			try {
				$this->actionDef = $this->actionMapper->getAction($actionString);
			} catch (Exception $e) {
				// TODO: look for error def, render that after setting error in session
				throw new Exception($e->getMessage());
			}
		}

		// if still null die saying can't do action mapping
		if (is_null($this->actionDef)) {
			throw new Exception("Unable to find a default action");
		}

		// verify that action exists
		$actionRealPath = null;
		// if no action name was provided, use a dummy to always return success
		if ($this->actionDef->getActionLocation() == '') {
			// see if overriding dummy provided, used to expose common methods
			// to tempates and similar
			$dummyOverride = $this->actionMapper->getDummyActionPath();
			if(is_null($dummyOverride)) {
				$actionRealPath = $UBAR_GLOB['UBAR_ROOT'] . '/core/' . GlobalConstants :: DUMMY_ACTION . '.php';
			} else {
				// mostly set for debugging purposes
				$this->actionDef->setActionLocation($dummyOverride);
				$actionRealPath = $UBAR_GLOB['BASE_ACTION_PATH'] . $dummyOverride;
				$actionClassName = FileUtils::classFromFile($dummyOverride);
				$this->actionDef->setClassName($actionClassName);
			}
		} else {
			$actionRealPath = $UBAR_GLOB['BASE_ACTION_PATH'] . $this->actionDef->getActionLocation();
		}
		if (!file_exists($actionRealPath)) {
			throw new ActionNotFoundException($this->actionDef);
		} else {
			require_once ($actionRealPath);
		}

		// instantiate specific action - class name may have been overridden
		$actionClassName = $this->actionDef->getClassName();
		$this->action = new $actionClassName ($this->actionDef);
		return $this->action;
	}

	/**
	 * Execute the action and get the name of the result to lookup.
	 *
	 * NOTE: This is a public method so that it may be called from tests. It is
	 * not recommended that you call it directly elsewhere.
	 *
	 * @return string The result string.
	 */
	public function generateResult() {
		$this->resultString = $this->action->execute();
		return $this->resultString;
	}

	/**
	 * Get the result definition using the result string. Normally the result
	 * is found in the action or uses a generated "dummy" result (for page
	 * views that do not require any processing). Global results are typically
	 * used for error pages and similar.
	 *
	 * NOTE: This is a public method so that it may be called from tests. It is
	 * not recommended that you call it directly elsewhere.
	 *
	 * @return class Result definition.
	 *
	 * @throws Exception when no result definition was found and it was unable
	 * to default to showing an associated page.
	 */
	public function getResultDef() {
		// try to find result def from action def
		$resultDef = $this->actionDef->getResult($this->resultString);

		// if null, look in global results
		if ($resultDef == null) {
			$resultDef = $this->actionMapper->getGlobalResult($this->resultString);
		}

		// if still null, see if it should use a dummy result
		if ($resultDef == null && $this->resultString == GlobalConstants::SUCCESS) {
			if (is_null($this->actionDef->getViewLocation())) {
				throw new Exception("To use the default result for an action, you must have a view specified");
			}
			// create a dummy result that uses the defaults
			$resultDef = Result :: makeResult(GlobalConstants :: SUCCESS, GlobalConstants :: PAGE_TYPE);
		}

		$this->resultDef = $resultDef;
		return $this->resultDef;
	}

	/**
	 * Render the result or redirect to the appropriate location.
	 *
	 * NOTE: This is a public method so that it may be called from tests. It is
	 * not recommended that you call it directly elsewhere.
	 *
	 * NOTE: Currently a type of ERROR without a global result just dumps the
	 * errors to screen.
	 *
	 * @throws Exception If now result defwas found and no default handling
	 * could be found.
	 *
	 * @todo Do error presentation for ERROR type.
	 *
	 */
	public function showResult() {
		global $UBAR_GLOB;

		// no def and not default, look for it in global results
		if ($this->resultDef == null) {
			switch ($this->resultString) {
				case GlobalConstants :: JSON :
					// to render json, action must extend JSONAction
					echo $this->action->getJSONString();
					return;
				case GlobalConstants :: ERROR :
					// TODO: build in a default error representation
					// since no error result found, just dump the errors
					echo("Unable to find default error representation, overridable by having a global result for ERROR<br />");
					die(print_r($this->action->getErrors(), true));
					break;
				default :
					throw new Exception("No definition found for result string $resultString for the action definition $actionClassName");
			}
		}

		// guaranteed to have a result def, switch on type
		switch ($this->resultDef->getType()) {
			case GlobalConstants :: ACTION_TYPE :
				// make a new request so there is no redeclaration or confusion with request params
				// NOTE: this is less efficient but safer
				// TODO: get possible override for action identifier instead of hardcoding ".action"
				header('Location: ' . $this->resultDef->getTarget() . ".action");
				break;
			case GlobalConstants :: PAGE_TYPE :
				// pass in possible overrides for page and template
				$this->renderPage($this->resultDef);
				break;
			case GlobalConstants :: FILE_TYPE :
				require_once ($UBAR_GLOB['BASE_VIEW_PATH'] . $this->resultDef->getTarget());
				return;
			case GlobalConstants :: URL_TYPE :
				if (Str :: nullOrEmpty($this->resultDef->getTarget())) {
					throw new Exception("With a url result type, you must specify a location to redirect to");
				}

				// url types (and possibly others?) may have expressions embedded in target, evaluate
				$target = $this->evaluateResultString($this->resultDef->getTarget());

				header('Location: ' . $target);
				return;
			case GlobalConstants :: JSON_TYPE :
				// to render json, action must extend JSONAction
				echo $this->action->getJSONString();
				return;
			default :
				throw new Exception("Unknown result type ," . $this->resultDef->getType());
		}
	}

	/**
	 * Initialize view path if defined. This may be set in the result or
	 * in the action definition.
	 *
	 * @throws ViewNotFoundException if the view is defined but the file does
	 * not exist.
	 * @throws Exception if no view was defined. This method is only called
	 * when a view MUST exist.
	 *
	 * @see Dispatcher::renderPage()
	 */
	private function initView(Result $result) {
		global $UBAR_GLOB;

		$viewPath = $result->getViewLocation();
		if(is_null($viewPath)) {
			$viewPath = $this->actionDef->getViewLocation();
		}
		if (!is_null($viewPath)) {
			$viewRealPath = $UBAR_GLOB['BASE_VIEW_PATH'] . $viewPath;
			if (!file_exists($viewRealPath) || !is_file($viewRealPath)) {
				throw new ViewNotFoundException($viewRealPath);
			} else {
				$this->action->setView($viewRealPath);
			}
		} else {
			throw new Exception("No view defined for action definition or result.");
		}
	}

	/**
	 * Render a page view. This either renders a view file within an action
	 * context or renders a view within it's template. This method is only
	 * called if the result type is a page.
	 *
	 * @param class $result Result definition to get view information from.
	 *
	 * @throws Exception if a template is referenced but no definition was
	 * found or the definition was found but the file was not.
	 *
	 * @see Dispatcher::dispatch()
	 */
	public function renderPage(Result $result) {
		global $UBAR_GLOB;

		// set the view or throw error if not defined
		$this->initView($result);

		// is there a template referenced to render the view inside?
		$templateName = $result->getTemplateName();
		if(is_null($templateName)) {
			$templateName = $this->actionDef->getTemplateName();
		}

		if (!is_null($templateName)) {

			$templateDef = $this->actionMapper->getTemplate($templateName);

			// is there a template def with the given name? no? fatal
			if (is_null($templateDef)) {
				throw new Exception("No template found with the name $templateName.");
			}

			$templateRealPath = $UBAR_GLOB['BASE_VIEW_PATH'] . $templateDef->getPath();
			if (!file_exists($templateRealPath) || is_dir($templateRealPath)) {
				throw new Exception("The template file \"" . $templateRealPath . "\", from template def, \"" . $templateName . "\", is not a valid file.");
			}
			// set template for action and init template values
			$this->action->setTemplateDef($templateDef);

			// NOTE: templates must call renderBody() in order to retrieve page content
			require_once ($templateRealPath);

			// just render the view
		} else {
			$this->renderBody();
		}

		// clear volatile data used in next view after execution, view has been rendered and data no longer pertinent
		$this->action->clearTempData();
	}

	// render the view associated with the action
	/**
	 * Render the page body. This is called if the result type is page and
	 * no template was used.
	 *
	 * @see Dispatcher::renderPage()
	 */
	public function renderBody() {
		require_once ($this->action->getView());
	}

	// try to set each get and post param in the action
	/**
	 * Attempt to populate any GET or POST params in the action.
	 *
	 * @see Action::set()
	 */
	private function populateUserInput() {
		foreach ($_GET as $key => $val) {
			$this->action->set($key, $val);
		}
		foreach ($_POST as $key => $val) {
			$this->action->set($key, $val);
		}
	}

	/**
	 * Perform expression evaluation on the result target. This is currently
	 * only used for url type results. It may be used to insert GET params into
	 * the URL. For instance, your intended location after a submit action is
	 * Blog.action?blogId=34. In that case, you would use
	 * Blog.action?blogId=${blogId} where your action has a public method,
	 * getBlogId().
	 *
	 * @see Dispatcher::RESULT_EXPRESSION_REGEX
	 * @see Dispatcher::dispatcher()
	 */
	private function evaluateResultString($target) {
		// find all instances of ${XXXXX} and replace with getXXXXX
		// while you find something that looks like a directive, try to process it
		while(preg_match(self::RESULT_EXPRESSION_REGEX, $target, $match)) {
			// get the key which should match a public method in the action
			$key = trim($match[1]);

			$methodName = $this->action->findMethodName($key);
			$value = $this->action->$methodName();
			$target = str_replace($match[0], $value, $target);
		}

		return $target;
	}
}
?>