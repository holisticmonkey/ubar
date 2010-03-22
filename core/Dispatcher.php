<?php
class Dispatcher {

	// TODO: allow override of action identifier
	const ACTION_REGEX = '/([^\/]+)\.action/u';

	// multiline, extra analysis, utf-8, ungreedy
	// get anything like ${foo} and match the 'foo' part for action method call
	const RESULT_EXPRESSION_REGEX = '/\${([^$]*)}/mSuU';

	private $actionMapper;

	private $action;

	private $actionDef;

	public function __construct($configPath) {
		$this->actionMapper = new ActionMapper($configPath);
	}

	public function dispatch($actionString = null) {
		// clear transient values in case part of an action forward
		$this->action = null;
		$this->view = null;
		$this->actionDef = null;

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
			$actionRealPath = UBAR_ROOT . '/core/' . GlobalConstants :: DUMMY_ACTION . '.php';
		} else {
			$actionRealPath = BASE_ACTION_PATH . $this->actionDef->getActionLocation();
		}
		if (!file_exists($actionRealPath)) {
			throw new ActionNotFoundException($this->actionDef);
		} else {
			require_once ($actionRealPath);
		}

		// instantiate specific action
		$actionClassName = $this->actionDef->getClassName();
		$this->action = new $actionClassName ($this->actionDef);

		// populate action from get and post
		$this->populateUserInput();

		// execute action, note that body may not execute if user conditions not met
		$resultString = $this->action->execute();

		$resultDef = $this->actionDef->getResult($resultString);

		// if null, look in global results
		if ($resultDef == null) {
			$resultDef = $this->actionMapper->getGlobalResult($resultString);
		}

		// no def and not default, look for it in global results
		if ($resultDef == null) {
			switch ($resultString) {
				case GlobalConstants :: SUCCESS :
					// only ok to have no result if has view in action def
					if (is_null($this->actionDef->getViewLocation())) {
						throw new Exception("To use the default result for an action, you must have a view specified");
					}
					// create a dummy result that uses the defaults
					$resultDef = Result :: makeResult(GlobalConstants :: SUCCESS, GlobalConstants :: PAGE_TYPE);
					break;
				case GlobalConstants :: JSON :
					// to render json, action must extend JSONAction
					echo $this->action->getJSONString();
					return;
				case GlobalConstants :: ERROR :
					die("render default error representation, overridable by having a global result for ERROR");
					break;
				default :
					throw new Exception("No definition found for result string $resultString for the action definition $actionClassName");
			}
		}

		// guaranteed to have a result def, switch on type
		switch ($resultDef->getType()) {
			case GlobalConstants :: ACTION_TYPE :

				// make a new request so there is no redeclaration or confusion with request params
				// NOTE: this is less efficient but safer
				// TODO: get possible override for action identifier instead of hardcoding ".action"
				header('Location: ' . $resultDef->getTarget() . ".action");
				break;
			case GlobalConstants :: PAGE_TYPE :
				// pass in possible overrides for page and template
				$this->renderPage($resultDef);
				break;
			case GlobalConstants :: FILE_TYPE :
				require_once (BASE_VIEW_PATH . $resultDef->getTarget());
				return;
			case GlobalConstants :: URL_TYPE :
				if (Str :: nullOrEmpty($resultDef->getTarget())) {
					throw new Exception("With a url result type, you must specify a location to redirect to");
				}

				// url types (and possibly others?) may have expressions embedded in target, evaluate
				$target = $this->evaluateResultString($resultDef->getTarget());

				header('Location: ' . $target);
				return;
			case GlobalConstants :: JSON_TYPE :
				// to render json, action must extend JSONAction
				echo $this->action->getJSONString();
				return;
			default :
				throw new Exception("Unknown result type ," . $resultDef->getType());
		}
	}

	private function initView(Result $result) {
		$viewPath = $result->getViewLocation();
		if(is_null($viewPath)) {
			$viewPath = $this->actionDef->getViewLocation();
		}
		if (!is_null($viewPath)) {
			$viewRealPath = BASE_VIEW_PATH . $viewPath;
			if (!file_exists($viewRealPath) || !is_file($viewRealPath)) {
				throw new ViewNotFoundException($viewRealPath);
			} else {
				$this->action->setView($viewRealPath);
			}
		} else {
			throw new Exception("No view defined for action definition or result.");
		}
	}

	public function renderPage(Result $result) {
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

			$templateRealPath = BASE_VIEW_PATH . $templateDef->getPath();
			if (!file_exists($templateRealPath) || is_dir($templateRealPath)) {
				throw new Exception("The template file \"" . $templateRealPath . "\", from template def, \"" . $templateName . "\", is not a valid file.");
			}
			// TODO: insert properties of template def into scope
			$this->action->setTemplateDef($templateDef);
			// init of values into action separate since only applicable to View for now
			$this->action->initTemplateValues();

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
	public function renderBody() {
		require_once ($this->action->getView());
	}

	// try to set each get and post param in the action
	private function populateUserInput() {
		foreach ($_GET as $key => $val) {
			$this->action->set($key, $val);
		}
		foreach ($_POST as $key => $val) {
			$this->action->set($key, $val);
		}
	}

	private function evaluateResultString($result) {
		// find all instances of ${XXXXX} and replace with getXXXXX
		// while you find something that looks like a directive, try to process it
			while(preg_match(self::RESULT_EXPRESSION_REGEX, $result, $match)) {
				// get the key which should match a public method in the action
				$key = trim($match[1]);

				$methodName = $this->action->findMethodName($key);
				$value = $this->action->$methodName();
				$result = str_replace($match[0], $value, $result);
			}

		return $result;
	}
}
?>