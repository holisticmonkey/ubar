<?php
// TODO: make this less generic? intead of just params, have specific things?
class TemplateDef {

	private $path;
	private $params = array();

	public function __construct($xmlDef) {
		if (!is_null($xmlDef->attributes()->path)) {
			$pathString = (string) $xmlDef->attributes()->path;
			$this->path = FileUtils :: dotToPath($pathString);
		}
		foreach ($xmlDef->param as $param) {
			$attribs = $param->attributes();
			$name = (string) $attribs->name;
			$value = (string) $attribs->value;
			$this->addParam($name, $value);
		}
	}

	public function setPath($path) {
		$this->path = $path;
	}

	// function to generically add properties to template,
	// this is used to merge things in, it should not override
	public function addParam($name, $value) {
		if(!array_key_exists($name, $this->params)) {
			$this->params[$name] = $value;
		}
	}

	// get template path
	public function getPath() {
		return $this->path;
	}

	public function getParams() {
		return $this->params;
	}

	// look for a param in the definition
	public function getParam($paramName) {
		if(array_key_exists($paramName, $this->params)) {
			return $this->params[$paramName];
		}
		return null;
	}

	public function getSection() {
		return $this->getParam("section");
	}

	public function getSubSection() {
		return $this->getParam("subSection");
	}

	public function getTitle() {
		return $this->getParam("title");
	}

	public function getTitleKey() {
		return $this->getParam("titleKey");
	}
}
?>
