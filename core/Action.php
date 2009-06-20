<?php
// TODO: abstract or interface? depending on implementation.
abstract class Action {

	private $properties;

	private $locale;

	public function __construct($viewPath) {
		global $action, $view;
		$action = $this;
		$view = $viewPath;

		function get($methodName) {
			global $action;
			//var_dump($action);
			$methodName = $action->findMethodName($methodName);
			return $action->$methodName ();
		}

		function getTxt($key, $arguments = array()) {
			global $action;
			// TODO: use dynamic arg retrieval and inspection
			//$args = func_num_args();
			return $action->getProperties()->get($key, $arguments, DEV_MODE);
		}

		function renderBody() {
			global $view;
			require_once($view);
		}
	}

	protected function setUserLocale($locale) {
		$this->locale = $locale;
		$this->properties = null;
	}

	public function getProperties() {
		if(is_null($this->properties)) {
			$this->properties = new LocalizedProperties($this->locale);
		}
		return $this->properties;
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
		throw new Exception("method \"" . $original . "\" was not found in the action");
	}

	public abstract function execute();

}
?>