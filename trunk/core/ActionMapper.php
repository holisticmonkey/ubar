<?php
class ActionMapper {

	private $defaultActionName;
	private $actions;
	private $globalResults;
	private $permissionGroups;
	private $templates;

	// TODO: make parsing failure be more graceful and provide meaningful feedback
	function __construct($file) {
		// convert xml of action definitions to an xml object
		libxml_clear_errors();
		$actionDefsXML = simplexml_load_file($file, "SimpleXMLElement", LIBXML_DTDVALID);
		if (libxml_get_last_error()) {
			throw new Exception('Error validating / loading XML');
		}

		// get the name of the default action
		$this->defaultActionName = (string) $actionDefsXML->defaultAction['name'];

		// assign actions as a local variable
		$this->actions = $actionDefsXML->actions->action;

		// assign results as a local variable
		$this->globalResults = $actionDefsXML->globalResults->result;

		// assign permission groups as a local variable
		$this->permissionGroups = $actionDefsXML->permissionGroups;

		// assign permission groups as a local variable
		$this->templates = $actionDefsXML->templates->template;
	}

	public function getAction($actionName) {
		foreach ($this->actions as $action) {
			if ((string) $action['name'] == $actionName) {
				return new ActionDef($action);
			}
		}
		// TODO: make more specific exception
		//throw new ActionNotDefinedException($actionString);
		throw new Exception("No action definition was found with the name \"" . $actionName . "\".");
	}

	// TODO: make template errors indicate process for resolution like dot path stuff
	public function getTemplate($templateName) {
		$returnTemplate = null;
		foreach ($this->templates as $template) {
			if ((string) $template['name'] == $templateName) {
				$templateDef = new TemplateDef($template);

				// if extends other template, use that path
				if (!is_null($template['extends'])) {
					$extendedTemplate = $this->getTemplate($template['extends']);

					// merge defs
					if (is_null($templateDef->getPath())) {
						$templateDef->setPath($extendedTemplate->getPath());
					}
					foreach ($extendedTemplate->getParams() as $name => $value) {
						$templateDef->addParam($name, $value);
					}
				}
				return $templateDef;
			}
		}
		return null;
	}

	public function getDefaultAction() {
		return $this->getAction($this->defaultActionName);
	}
}
?>
