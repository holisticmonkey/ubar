<?php


// TODO: if always connect OR this action uses db (from xml file) connect to db
class Dispatcher {

	const ACTION_REGEX = '/([^\/]+)\.action/u';

	private $actionMapper;

	private $action;

	private $view;

	public function __construct($configPath) {
		$this->actionMapper = new ActionMapper($configPath);
	}

	// TODO: disallow call from the view stuff ... look for caller?
	public function dispatch() {

		// match action part of the URI
		preg_match(self :: ACTION_REGEX, $_SERVER['REQUEST_URI'], $matches);

		// find action definiton
		$actionDef = null;
		if (isset ($matches[1])) {
			$actionString = $matches[1];
			try {
				$actionDef = $this->actionMapper->getAction($actionString);
			} catch (Exception $e) {
				// TODO: look for error def, render that after setting error in session
				throw new Exception($e->getMessage());
			}
		}

		// if not found or re-cast to error, look for default
		if (is_null($actionDef)) {
			$actionDef = $this->actionMapper->getDefaultAction();
		}

		// if still null die saying can't do action mapping
		if (is_null($actionDef)) {
			throw new Exception("Unable to find a default action");
		}

		// verify that action exists
		$actionRealPath = null;
		// if no action name was provided, use a dummy to always return success
		if($actionDef->getActionLocation() == '') {
			$actionRealPath = UBAR_ROOT . '/core/' . GlobalConstants :: DUMMY_ACTION . '.php';
		} else {
			$actionRealPath = BASE_ACTION_PATH . $actionDef->getActionLocation();
		}
		if (!file_exists($actionRealPath)) {
			throw new ActionNotFoundException($actionDef);
		} else {
			require_once ($actionRealPath);
		}

		// verify that view exists if view was specified
		$hasView = false;
		$viewRealPath = BASE_VIEW_PATH . $actionDef->getViewLocation();
		if (!is_null($actionDef->getViewLocation()) && (!file_exists($viewRealPath) || !is_file($viewRealPath))) {
			throw new ViewNotFoundException($actionDef);
		} else {
			$hasView = true;
			$this->view = $viewRealPath;
		}
		$actionClassName = $actionDef->getClassName();
		$this->action = new $actionClassName ($this->view, $actionDef);
		$resultString = $this->action->execute();

		$resultDef = $actionDef->getResult($resultString);
		if ($resultDef == null && $resultString != "SUCCESS") {
			// look for it in global defs
			debug("looking for " . $resultString . " in global results");
			// TODO: find def
		}
		// if still no result definiton, fail

		// before rendering, make sure localized properties are avail
		//TODO: consider moving this

		// if there's a view, try to render it
		// TODO: figure out why not doing more with resultDef
		if ($hasView && ($resultDef == null || $resultDef->getType() == "page" || $resultDef->getType() == null)) {
			// is there a template referenced to render the view inside?
			$templateName = $actionDef->getTemplateName();

			if (!Str :: nullOrEmpty($templateName)) {

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
		}
	}

	// TODO: consider only allowing calling once, moving to Action
	public function renderBody() {
		require_once ($this->view);
	}
}
?>