<?php

// TODO: make this abstract?
// TODO: make action interface
// TODO: have method that declares function defs so they're avail to page? do just before rendering page
class ActionManager {

	const DEFAULT_TYPE = "page";

	private $actionLocation;
	private $actionClassName;
	private $viewLocation;
	private $permissions;
	private $results;
	private $className;
	private $type;
	private $template;

	public function __construct($actionXML) {
		$this->actionLocation = FileUtils :: dotToPath((string) $actionXML['path']);
		$this->template = (string) $actionXML['template'];
		$this->actionClassName = FileUtils :: classFromFile((string) $actionXML['path']);
		$this->type = is_null($actionXML['type']) ? DEFAULT_TYPE : (string) $actionXML['type'];
		// TODO: have types be enum or at least constants
		if ($this->type == "page") {
			$this->viewLocation = FileUtils :: dotToPath((string) $actionXML['view']);
		}
		$this->results = $actionXML->results;
		$this->permissionGroup = $actionXML->permissionGroup;
		$this->permissions = $actionXML->permissions;
		//debug($this, true);
	}

	public function getTemplate() {
		return $this->template;
	}

	public function getActionLocation() {
		return $this->actionLocation;
	}

	public function getViewLocation() {
		return $this->viewLocation;
	}

	public function getType() {
		return $this->type;
	}

	public function getClassName() {
		return $this->actionClassName;
	}

	public function isAllowed($permissionArgs) {
		// is string? - assume permission group
		// no permission group defined? return true else check case insensitive with trimming

		// is array? - assume permissions
		// no permissions listed? return true else check case insensitive with trimming
		return true;
	}

	// TODO: do the dispatching work here or send up to dispatcher to handle?
	public function getResult($resultString) {
		// find result object
		//$result = null;
		foreach ($this->results as $result) {
			if ((string) $result['name'] == $resultString) {
				// TODO: make a result object
				return new Result($result);
			}
		}

		// not found? check global defs
		// not found? result is error

		// switch on result type
		// forward - new action, do cleanup and new dispatch
		// view - get view class from def, assume naming convention if not defined, render
		// json - get json parent object and convert to json, do header conversion etc
		return null;
	}

	

}
?>
