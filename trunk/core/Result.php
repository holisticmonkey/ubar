<?php
class Result {

	private $type;
	private $name;
	private $target;
	private $viewLocation;
	private $templateName;

	public function __construct($xmlObj) {
		$this->type = isset ($xmlObj['type']) ? (string) $xmlObj['type'] : GlobalConstants :: DEFAULT_TYPE;
		$this->name = isset ($xmlObj['name']) ? (string) $xmlObj['name'] : GlobalConstants :: DEFAULT_NAME;
		$this->target = (string) $xmlObj;
		if ($this->type == GlobalConstants :: PAGE_TYPE && $this->target != '') {
			$this->viewLocation = FileUtils :: dotToPath($this->target);
		}
		if (isset($xmlObj['template'])) {
			$this->templateName = (string) $xmlObj['template'];
		}
	}

	// if you need to fabricate a result manually
	public static function makeResult( $name = null, $type = null, $target = null) {
		$resultString = "<result name=\"$name\" type=\"$type\">$target</result>";
		$xmlObj = new SimpleXMLElement($resultString);
		return new Result($xmlObj);
	}

	public function getName() {
		return $this->name;
	}

	public function getType() {
		return $this->type;
	}

	public function getTarget() {
		return $this->target;
	}

	public function getTemplateName() {
		return $this->templateName;
	}

	public function getViewLocation() {
		return $this->viewLocation;
	}
}
?>
