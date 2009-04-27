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
				die($e->getMessage());
			}
		}

		// if not found or re-cast to error, look for default
		if (is_null($actionDef)) {
			$actionDef = $this->actionMapper->getDefaultAction();
		}

		// if still null die saying can't do action mapping
		if (is_null($actionDef)) {
			die("unable to find a default action");
		}

		// verify that action exists
		$actionRealPath = BASE_ACTION_PATH . $actionDef->getActionLocation();
		if (!file_exists($actionRealPath)) {
			throw new ActionNotFoundException($actionRealPath);
		} else {
			require_once ($actionRealPath);
		}

		// verify that view exists if view was specified
		$hasView = false;
		$viewRealPath = BASE_VIEW_PATH . $actionDef->getViewLocation();
		if (!is_null($actionDef->getViewLocation()) && (!file_exists($viewRealPath) || !is_file($viewRealPath))) {
			throw new Exception("view not found " . $viewRealPath);
		} else {
			$hasView = true;
			$this->view = $viewRealPath;
		}
		$actionClassName = $actionDef->getClassName();
		$this->action = new $actionClassName ($this->view);
		$resultString = $this->action->execute();
		$resultDef = $actionDef->getResult($resultString);
		if ($resultDef == null && $resultString != "SUCCESS") {
			// look for it in global defs
			debug("looking for " . $resultString . " in global results");
		}
		// if still no result definiton, fail

		// before rendering, make sure localized properties are avail
		//TODO: consider moving this 

		// if there's a view, try to render it
		if ($hasView && ($resultDef == null || $resultDef->getType() == "page" || $resultDef->getType() == null)) {
			// is there a template referenced to render the view inside?
			$templateName = $actionDef->getTemplate();
			if (!Str :: nullOrEmpty($templateName)) {
				$templatePath = $this->actionMapper->getTemplate($templateName);
				// is there a template def with the given name? no? fatal 
				if (is_null($templatePath)) {
					die("the referenced template \"" . $templatePath . "\" does not exist where the template name was $templateName.");
				}
				$templateRealPath = BASE_VIEW_PATH . $templatePath;
				if (!file_exists($templateRealPath)) {
					die("the template file \"" . $templateRealPath . "\" does not exist");
				}
				require_once ($templateRealPath);

				// just render the view
			} else {
				$this->renderBody();
			}
			// TODO: instead of requring here, allow render inside template with simple call to renderBody() or similar

		}

		//} catch (Exception $e) {
		//die();
		// action not found - try to send to error page
		//}
		// create new instance of action class
		// attempt to populate with get and post params
		// if no method override, use "execute"
		// lookup result for action def

		// if doesn't match, look for error action def

		// if error action def not found, return 404
		//echo $actionString;
	}

	

	// TODO: consider only allowing calling once, moving to Action
	public function renderBody() {		
		require_once ($this->view);
	}
}
?>