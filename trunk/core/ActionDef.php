<?php
class ActionDef {

	private $actionLocation;
	private $actionClassName;
	private $viewLocation;
	private $permissions;
	private $results;
	private $name;
	private $templateName;
	private $title;
	private $titleKey;
	private $page;
	private $section;
	private $subSection;
	private $params = array();

	public function __construct($actionXML) {
		$path = ((string) $actionXML['path']);
		// use dummy action if no action defined
		if ($path != '') {
			$this->actionLocation = FileUtils :: dotToPath($path);
			$this->actionClassName = FileUtils :: classFromFile($this->actionLocation);
		} else {
			$this->actionClassName = GlobalConstants :: DUMMY_ACTION;
		}
		if (!is_null($actionXML['template'])) {
			$this->templateName = (string) $actionXML['template'];
		}
		if (!is_null($actionXML['view'])) {
			$this->viewLocation = FileUtils :: dotToPath((string) $actionXML['view']);
		}
		$this->results = $actionXML->results->result;
		$this->permissionGroup = $actionXML->permissionGroup;
		$this->permissions = $actionXML->permissions;
		$this->name = (string) $actionXML['name'];

		// add params
		foreach ($actionXML->param as $param) {
			$attribs = $param->attributes();
			$name = (string) $attribs->name;
			$value = (string) $attribs->value;
			$this->addParam($name, $value);
		}

		// display values
		$this->title = (string) $actionXML['title'];
		$this->titleKey = (string) $actionXML['titleKey'];
		// TODO: page mostly makes sense as action name, consider decoupling however
		$this->page = (string) $actionXML['name'];
		$this->section = (string) $actionXML['section'];
		$this->subSection = (string) $actionXML['subSection'];
	}

	public function addParam($name, $value) {
		if(!array_key_exists($name, $this->params)) {
			$this->params[$name] = $value;
		}
	}

	public function getTemplateName() {
		return $this->templateName;
	}

	public function getActionLocation() {
		return $this->actionLocation;
	}

	public function getViewLocation() {
		return $this->viewLocation;
	}

	public function getClassName() {
		return $this->actionClassName;
	}

	public function getName() {
		return $this->name;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getTitleKey() {
		return $this->titleKey;
	}

	public function getPage() {
		return $this->page;
	}

	public function getSection() {
		return $this->section;
	}

	public function getSubSection() {
		return $this->subSection;
	}

	public function isAllowed($permissionArgs) {
		// is string? - assume permission group
		// no permission group defined? return true else check case insensitive with trimming

		// is array? - assume permissions
		// no permissions listed? return true else check case insensitive with trimming
		return true;
	}

	public function getResult($resultString) {
		// find result object
		if (!is_null($this->results)) {
			foreach ($this->results as $result) {
				$name = (string) $result['name'];
				if ($name == $resultString || ($name == '' && $resultString == GlobalConstants :: SUCCESS)) {
					return new Result($result);
				}
			}
		}
		return null;
	}

	public function getParam($paramName) {
		if(array_key_exists($paramName, $this->params)) {
			return $this->params[$paramName];
		}
		return null;
	}

}
?>
