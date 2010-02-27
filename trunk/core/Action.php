<?php
// TODO: abstract or interface? depending on implementation.
abstract class Action {

	protected $properties;
	protected $locale;
	protected $templateDef;
	protected $actionDef;

	public function __construct($viewPath, $def) {
		global $action, $view, $actionDef;
		$action = $this;
		$view = $viewPath;
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

		// TODO: expose template config info?

		function renderBody() {
			global $view;
			require_once ($view);
		}
	}

	protected function setUserLocale($locale) {
		$this->locale = $locale;
		$this->properties = null;
	}

	public function setTemplateDef($templateDef) {
		$this->templateDef = $templateDef;

		// only override values if not already set
		if(is_null($this->section)) {
			$this->section = $templateDef->getSection();
		}
		if(is_null($this->subSection)) {
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
		if(!is_null($this->templateDef)) {
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

	public abstract function execute();

}
?>