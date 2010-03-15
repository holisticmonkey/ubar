<?php
class Result {

	private $type;
	private $name;
	private $target;

	public function __construct($xmlObj) {
		$this->type = isset ($xmlObj['type']) ? (string) $xmlObj['type'] : GlobalConstants :: DEFAULT_TYPE;
		$this->name = isset ($xmlObj['name']) ? (string) $xmlObj['name'] : GlobalConstants :: DEFAULT_NAME;
		$this->target = (string) $xmlObj;
	}

	// if you need to fabricate a result manually
	public static function makeResult( $name = null, $type = null, $target = null) {
		$xmlObj = array();
		if(!is_null($target)) {
			$xmlObj[0] = $target;
		}
		if(!is_null($type)) {
			$xmlObj['type'] = $type;
		}
		if(!is_null($name)) {
			$xmlObj['name'] = $name;
		}
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
}
?>
