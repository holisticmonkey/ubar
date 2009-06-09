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
		//$actionDefsXML = simplexml_load_file($file, "SimpleXMLElement", LIBXML_DTDVALID);
$actionDefsXML = simplexml_load_file($file);
		if (libxml_get_last_error()) {
			die('Error validating / loading XML');
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

		//print_r($this);
		//die("die!!!");
	}

	public function getAction($actionName) {
		foreach ($this->actions as $action) {
			if ((string) $action['name'] == $actionName) {
				return new ActionManager($action);
			}
		}
		// TODO: make more specific exception
		//throw new ActionNotFoundException($actionString);
		throw new Exception("No action definition was found with the name \"" . $actionName . "\".");
	}

	public function getTemplate($templateName) {
		foreach ($this->templates as $template) {
			if ((string) $template['name'] == $templateName) {
				return FileUtils :: dotToPath((string) $template['path']);
			}
		}
		return null;
	}

	public function getDefaultAction() {
		return $this->getAction($this->defaultActionName);
	}
}
?>
