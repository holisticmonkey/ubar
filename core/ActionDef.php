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

	public function __construct($actionXML) {
		$path = ((string) $actionXML['path']);
		// use dummy action if no action defined
		if($path != '') {
			$this->actionLocation = FileUtils :: dotToPath($path);
			$this->actionClassName = FileUtils :: classFromFile($path);
		} else {
			$this->actionClassName = GlobalConstants :: DUMMY_ACTION;
		}
		$this->templateName = (string) $actionXML['template'];
		$this->viewLocation = FileUtils :: dotToPath((string) $actionXML['view']);
		$this->results = $actionXML->results;
		$this->permissionGroup = $actionXML->permissionGroup;
		$this->permissions = $actionXML->permissions;
		$this->name = (string) $actionXML['name'];

		//print_r($actionXML);
		// display values
		$this->title = (string) $actionXML['title'];
		$this->titleKey = (string) $actionXML['titleKey'];
		// TODO: page mostly makes sense as action name, consider decoupling however
		$this->page = (string) $actionXML['name'];
		$this->section = (string) $actionXML['section'];
		$this->subSection = (string) $actionXML['subSection'];
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
